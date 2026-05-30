"use client";

/**
 * FormSection — renders a CMS-managed form.
 *
 * Server wrapper (FormSectionLoader) fetches the form definition,
 * then passes it to this Client Component for interactive submission.
 */
import Script from "next/script";
import { useState, type FormEvent } from "react";
import type { FormField, FormPayload } from "@/lib/types";

const API_BASE = process.env.NEXT_PUBLIC_API_URL ?? process.env.CMS_API_URL ?? "";

// ─── Individual field renderers ───────────────────────────────────────────────

function resolveOptions(field: FormField): Array<{ label: string; value: string }> {
  if (!Array.isArray(field.options) || field.options.length === 0) return [];
  if (typeof field.options[0] === "string") {
    return (field.options as string[]).map((o) => ({ label: o, value: o }));
  }
  return field.options as Array<{ label: string; value: string }>;
}

const fieldBase: React.CSSProperties = {
  display: "block",
  width: "100%",
  padding: "0.625rem 0.875rem",
  border: "1px solid var(--color-border, #d1d5db)",
  borderRadius: "0.5rem",
  fontSize: "0.9rem",
  lineHeight: 1.5,
  background: "#fff",
  color: "inherit",
  outline: "none",
  transition: "border-color 0.15s",
};

function FieldInput({ field }: { field: FormField }) {
  const name = `fields[${field.name}]`;
  const required = field.is_required;
  const placeholder = field.placeholder ?? "";

  switch (field.type) {
    case "textarea":
      return (
        <textarea
          name={name}
          required={required}
          placeholder={placeholder}
          rows={4}
          style={{ ...fieldBase, resize: "vertical" }}
        />
      );

    case "select": {
      const opts = resolveOptions(field);
      return (
        <select name={name} required={required} style={fieldBase}>
          <option value="">{placeholder || "Seçiniz…"}</option>
          {opts.map((o) => (
            <option key={o.value} value={o.value}>
              {o.label}
            </option>
          ))}
        </select>
      );
    }

    case "radio": {
      const opts = resolveOptions(field);
      return (
        <div style={{ display: "flex", flexDirection: "column", gap: "0.4rem", paddingTop: "0.25rem" }}>
          {opts.map((o) => (
            <label key={o.value} style={{ display: "flex", alignItems: "center", gap: "0.5rem", cursor: "pointer" }}>
              <input type="radio" name={name} value={o.value} required={required} />
              <span style={{ fontSize: "0.875rem" }}>{o.label}</span>
            </label>
          ))}
        </div>
      );
    }

    case "checkbox":
      return (
        <label style={{ display: "flex", alignItems: "center", gap: "0.5rem", cursor: "pointer" }}>
          <input type="checkbox" name={name} value="1" required={required} style={{ width: "1rem", height: "1rem" }} />
          <span style={{ fontSize: "0.875rem" }}>{field.label}</span>
        </label>
      );

    case "hidden":
      return <input type="hidden" name={name} value={field.default_value ?? ""} />;

    default:
      return (
        <input
          type={field.type}
          name={name}
          required={required}
          placeholder={placeholder}
          defaultValue={field.default_value ?? ""}
          style={fieldBase}
        />
      );
  }
}

// ─── Main form component ──────────────────────────────────────────────────────

type FormSectionProps = {
  form: FormPayload;
  title?: string;
  description?: string;
  submitLabel?: string;
};

export function FormSection({ form, title, description, submitLabel = "Gönder" }: FormSectionProps) {
  const [status, setStatus] = useState<"idle" | "submitting" | "success" | "error">("idle");
  const [errorMessage, setErrorMessage] = useState<string | null>(null);
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({});

  async function handleSubmit(e: FormEvent<HTMLFormElement>) {
    e.preventDefault();
    setStatus("submitting");
    setErrorMessage(null);
    setFieldErrors({});

    const formData = new FormData(e.currentTarget);
    // Backend expects a nested `fields` object (fields.email …). Inputs are
    // named `fields[email]`, so unwrap them; everything else (honeypot
    // `_hp_url`, Turnstile `cf-turnstile-response`) stays top-level.
    const fields: Record<string, string> = {};
    const body: Record<string, unknown> = { fields };
    formData.forEach((value, key) => {
      const match = key.match(/^fields\[(.+)\]$/);
      if (match) {
        fields[match[1]] = String(value);
      } else {
        body[key] = String(value);
      }
    });

    try {
      const res = await fetch(`${API_BASE}/api/v1/forms/${form.id}/submit`, {
        method: "POST",
        headers: { "Content-Type": "application/json", Accept: "application/json" },
        body: JSON.stringify(body),
      });

      const data = (await res.json()) as {
        success?: boolean;
        message?: string;
        error?: string;
        errors?: Record<string, string[]>;
      };

      if (!res.ok) {
        if (data.errors) {
          setFieldErrors(data.errors);
          setStatus("error");
          setErrorMessage("Lütfen formdaki hataları düzeltin.");
        } else {
          setStatus("error");
          setErrorMessage(data.error ?? "Bir hata oluştu. Lütfen tekrar deneyin.");
        }
        return;
      }

      setStatus("success");
    } catch {
      setStatus("error");
      setErrorMessage("Bağlantı hatası. Lütfen tekrar deneyin.");
    }
  }

  if (status === "success") {
    return (
      <section style={{ padding: "3rem 0", textAlign: "center" }}>
        <div
          style={{
            display: "inline-flex",
            flexDirection: "column",
            alignItems: "center",
            gap: "1rem",
            padding: "2rem",
            background: "var(--color-success-bg, #f0fdf4)",
            border: "1px solid var(--color-success-border, #bbf7d0)",
            borderRadius: "0.75rem",
            maxWidth: "400px",
          }}
        >
          <span style={{ fontSize: "2.5rem" }}>✅</span>
          <p style={{ fontWeight: 600, color: "var(--color-success, #166534)" }}>
            Formunuz başarıyla gönderildi. Teşekkürler!
          </p>
        </div>
      </section>
    );
  }

  const visibleFields = form.fields.filter((f) => f.type !== "hidden");

  return (
    <section style={{ padding: "3rem 0" }}>
      {(title || description) && (
        <div style={{ marginBottom: "2rem", textAlign: "center" }}>
          {title && <h2 style={{ margin: 0, fontSize: "1.75rem", fontWeight: 700 }}>{title}</h2>}
          {description && (
            <p style={{ marginTop: "0.5rem", color: "var(--color-text-soft, #6b7280)" }}>{description}</p>
          )}
        </div>
      )}

      {errorMessage && (
        <div
          style={{
            marginBottom: "1.5rem",
            padding: "0.875rem 1rem",
            background: "var(--color-error-bg, #fef2f2)",
            border: "1px solid var(--color-error-border, #fecaca)",
            borderRadius: "0.5rem",
            color: "var(--color-error, #dc2626)",
            fontSize: "0.875rem",
          }}
        >
          {errorMessage}
        </div>
      )}

      <form
        onSubmit={handleSubmit}
        style={{ display: "flex", flexDirection: "column", gap: "1.25rem", maxWidth: "600px", margin: "0 auto" }}
        noValidate
      >
        {/* Hidden fields */}
        {form.fields
          .filter((f) => f.type === "hidden")
          .map((field) => (
            <FieldInput key={field.id} field={field} />
          ))}

        {/* Honeypot — off-screen; humans never fill it, bots do. */}
        <input
          type="text"
          name="_hp_url"
          tabIndex={-1}
          autoComplete="off"
          aria-hidden="true"
          defaultValue=""
          style={{ position: "absolute", left: "-9999px", width: "1px", height: "1px", opacity: 0 }}
        />

        {visibleFields.map((field) => {
          const errs = fieldErrors[`fields.${field.name}`] ?? [];
          return (
            <div key={field.id}>
              {field.type !== "checkbox" && (
                <label
                  style={{
                    display: "block",
                    marginBottom: "0.35rem",
                    fontSize: "0.875rem",
                    fontWeight: 500,
                    color: "var(--color-heading, #111827)",
                  }}
                >
                  {field.label}
                  {field.is_required && (
                    <span style={{ color: "var(--color-error, #dc2626)", marginLeft: "0.2rem" }}>*</span>
                  )}
                </label>
              )}
              <FieldInput field={field} />
              {errs.length > 0 && (
                <p style={{ marginTop: "0.25rem", fontSize: "0.8rem", color: "var(--color-error, #dc2626)" }}>
                  {errs[0]}
                </p>
              )}
            </div>
          );
        })}

        {/* Cloudflare Turnstile — implicit render: the script injects a hidden
            `cf-turnstile-response` input into this form, picked up by FormData. */}
        {form.requires_captcha && form.turnstile_site_key && (
          <div>
            <Script src="https://challenges.cloudflare.com/turnstile/v0/api.js" strategy="afterInteractive" />
            <div className="cf-turnstile" data-sitekey={form.turnstile_site_key} />
          </div>
        )}

        <div>
          <button
            type="submit"
            disabled={status === "submitting"}
            style={{
              padding: "0.75rem 2rem",
              background: status === "submitting" ? "#9ca3af" : "var(--color-primary, #6366f1)",
              color: "#fff",
              border: "none",
              borderRadius: "0.5rem",
              fontSize: "0.9rem",
              fontWeight: 600,
              cursor: status === "submitting" ? "not-allowed" : "pointer",
              transition: "background 0.2s",
            }}
          >
            {status === "submitting" ? "Gönderiliyor…" : submitLabel}
          </button>
        </div>
      </form>
    </section>
  );
}
