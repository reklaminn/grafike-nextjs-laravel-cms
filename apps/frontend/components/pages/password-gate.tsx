"use client";

import { useState, useTransition } from "react";

type PasswordGateProps = {
  /** The page ID, used to build the Laravel unlock POST URL */
  pageId: number;
  /** Page title to show in the gate heading */
  title: string;
};

/**
 * Renders a password form for password-protected pages.
 *
 * POSTs to Laravel's `pages/{page}/unlock` endpoint (relative URL —
 * Traefik routes PathPrefix('/pages') → Laravel on the same domain).
 * Laravel stores the unlocked page in the session and calls FrontendRevalidator
 * to bust the Next.js ISR cache. On success, the page is hard-reloaded
 * so the now-unlocked content is served fresh from the server.
 */
export function PasswordGate({ pageId, title }: PasswordGateProps) {
  const [password, setPassword] = useState("");
  const [error, setError]       = useState<string | null>(null);
  const [pending, startTransition] = useTransition();

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setError(null);

    startTransition(async () => {
      try {
        const formData = new FormData();
        formData.append("page_password", password);

        // Relative URL — Traefik routes PathPrefix('/pages') to Laravel on this domain
        const res = await fetch(`/pages/${pageId}/unlock`, {
          method:      "POST",
          credentials: "include",           // forward session cookie
          headers:     { "X-Requested-With": "XMLHttpRequest" },
          body:        formData,
        });

        if (res.ok || res.redirected) {
          // Session is now unlocked — hard-reload so Next.js fetches fresh ISR
          window.location.reload();
          return;
        }

        // Try to read Laravel's JSON error (or fall back to generic message)
        const data = await res.json().catch(() => null);
        setError(
          (data?.message as string | undefined) ??
            "Girdiğiniz şifre yanlış. Lütfen tekrar deneyin.",
        );
      } catch {
        setError("Bağlantı hatası. Lütfen sayfayı yenileyip tekrar deneyin.");
      }
    });
  }

  return (
    <div
      style={{
        display:        "flex",
        alignItems:     "center",
        justifyContent: "center",
        minHeight:      "60vh",
        padding:        "2rem 1rem",
      }}
    >
      <div
        style={{
          width:        "100%",
          maxWidth:     "420px",
          background:   "var(--color-surface, #fff)",
          border:       "1px solid var(--color-border, #e5e7eb)",
          borderRadius: "0.75rem",
          padding:      "2rem",
          boxShadow:    "0 4px 24px rgba(0,0,0,.06)",
        }}
      >
        {/* Lock icon */}
        <div style={{ textAlign: "center", marginBottom: "1.25rem", fontSize: "2.5rem" }}>
          🔒
        </div>

        <h1
          style={{
            fontSize:     "1.15rem",
            fontWeight:   700,
            textAlign:    "center",
            marginBottom: ".5rem",
            color:        "var(--color-heading, #111827)",
          }}
        >
          {title}
        </h1>

        <p
          style={{
            fontSize:     ".875rem",
            color:        "var(--color-text-soft, #6b7280)",
            textAlign:    "center",
            marginBottom: "1.5rem",
          }}
        >
          Bu sayfa şifre korumalıdır. Devam etmek için şifreyi girin.
        </p>

        <form onSubmit={handleSubmit}>
          <div style={{ marginBottom: "1rem" }}>
            <label
              htmlFor="page_password"
              style={{
                display:      "block",
                fontSize:     ".875rem",
                fontWeight:   600,
                marginBottom: ".4rem",
                color:        "var(--color-heading, #374151)",
              }}
            >
              Şifre
            </label>
            <input
              id="page_password"
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              required
              autoFocus
              style={{
                width:        "100%",
                padding:      ".6rem .85rem",
                border:       `1px solid ${error ? "#dc2626" : "var(--color-border, #d1d5db)"}`,
                borderRadius: ".4rem",
                fontSize:     "1rem",
                outline:      "none",
                boxSizing:    "border-box",
              }}
            />
            {error && (
              <p style={{ color: "#dc2626", fontSize: ".8rem", marginTop: ".35rem" }}>
                {error}
              </p>
            )}
          </div>

          <button
            type="submit"
            disabled={pending}
            style={{
              width:          "100%",
              padding:        ".7rem",
              background:     "var(--color-primary, #6366f1)",
              color:          "#fff",
              border:         "none",
              borderRadius:   ".4rem",
              fontWeight:     600,
              fontSize:       "1rem",
              cursor:         pending ? "not-allowed" : "pointer",
              opacity:        pending ? 0.7 : 1,
              transition:     "opacity .15s",
            }}
          >
            {pending ? "Kontrol ediliyor…" : "Erişim Sağla"}
          </button>
        </form>
      </div>
    </div>
  );
}
