"use client";

/**
 * Locale segment hata sınırı — sunucu/render hatalarında çıplak Next.js
 * hata ekranı yerine markaya uygun, locale'e duyarlı bir mesaj gösterir.
 */
import { useEffect } from "react";
import { useParams } from "next/navigation";
import { uiStrings } from "@/lib/ui-strings";

export default function LocaleError({
  error,
  reset,
}: {
  error: Error & { digest?: string };
  reset: () => void;
}) {
  const params = useParams<{ locale?: string }>();
  const t = uiStrings(typeof params?.locale === "string" ? params.locale : "tr");

  useEffect(() => {
    // Sentry entegrasyonu varsa otomatik yakalar; konsol kaydı debug için
    console.error("page error boundary:", error);
  }, [error]);

  return (
    <main
      style={{
        display: "flex",
        alignItems: "center",
        justifyContent: "center",
        minHeight: "60vh",
        padding: "2rem 1rem",
      }}
    >
      <div
        style={{
          maxWidth: "480px",
          width: "100%",
          textAlign: "center",
          background: "var(--color-surface,#fff)",
          border: "1px solid var(--color-border,#e5e7eb)",
          borderRadius: "0.75rem",
          padding: "2.5rem 2rem",
          boxShadow: "0 4px 24px rgba(0,0,0,.06)",
        }}
      >
        <div style={{ fontSize: "2.5rem", marginBottom: "1rem" }}>⚠️</div>
        <h1
          style={{
            fontSize: "1.15rem",
            fontWeight: 700,
            marginBottom: ".5rem",
            color: "var(--color-heading,#111827)",
          }}
        >
          {t.errorTitle}
        </h1>
        <p style={{ fontSize: ".875rem", color: "var(--color-text-soft,#6b7280)", marginBottom: "1.25rem" }}>
          {t.errorBody}
          {error.digest ? (
            <span style={{ display: "block", marginTop: ".5rem", fontSize: ".75rem", opacity: 0.6 }}>
              Ref: {error.digest}
            </span>
          ) : null}
        </p>
        <button
          type="button"
          onClick={() => reset()}
          style={{
            padding: ".6rem 1.5rem",
            background: "var(--color-primary,#6366f1)",
            color: "#fff",
            border: "none",
            borderRadius: ".4rem",
            fontWeight: 600,
            fontSize: ".875rem",
            cursor: "pointer",
          }}
        >
          {t.retry}
        </button>
      </div>
    </main>
  );
}
