"use client";

/**
 * FormSection — renders a CMS-managed form.
 *
 * Server wrapper (FormSectionLoader) fetches the form definition,
 * then passes it to this Client Component for interactive submission.
 *
 * Stil: tamamen tema token'larına (--color-*) bağlı. Her tenant kendi
 * renk paletiyle render eder; hiçbir renk sabit kodlanmaz (fallback hariç).
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

function FieldInput({ field }: { field: FormField }) {
  const name = `fields[${field.name}]`;
  const required = field.is_required;
  const placeholder = field.placeholder ?? "";

  switch (field.type) {
    case "textarea":
      return (
        <textarea
          className="cms-field"
          name={name}
          required={required}
          placeholder={placeholder}
          rows={4}
          style={{ resize: "vertical" }}
        />
      );

    case "select": {
      const opts = resolveOptions(field);
      return (
        <select className="cms-field" name={name} required={required}>
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
        <div style={{ display: "flex", flexDirection: "column", gap: "0.5rem", paddingTop: "0.25rem" }}>
          {opts.map((o) => (
            <label key={o.value} className="cms-choice">
              <input type="radio" name={name} value={o.value} required={required} />
              <span>{o.label}</span>
            </label>
          ))}
        </div>
      );
    }

    case "checkbox":
      return (
        <label className="cms-choice">
          <input type="checkbox" name={name} value="1" required={required} />
          <span>{field.label}</span>
        </label>
      );

    case "hidden":
      return <input type="hidden" name={name} value={field.default_value ?? ""} />;

    default:
      return (
        <input
          className="cms-field"
          type={field.type}
          name={name}
          required={required}
          placeholder={placeholder}
          defaultValue={field.default_value ?? ""}
        />
      );
  }
}

// ─── Theme-bound styles (token-driven, scoped to .cms-form) ─────────────────────

const FORM_STYLES = `
.cms-form-wrap{padding:3.5rem 1.25rem}
.cms-form-card{max-width:680px;margin:0 auto;background:#fff;border:1px solid var(--color-border,#e7e5e0);border-top:4px solid var(--color-accent,#C2922E);border-radius:var(--radius-card,8px);box-shadow:0 14px 44px rgba(20,22,28,.08);padding:2.75rem 2.5rem}
.cms-form-head{margin:0 0 1.75rem}
.cms-form-head h2{margin:0;font-size:1.6rem;font-weight:800;color:var(--color-heading,var(--color-primary,#1A1D24));line-height:1.2}
.cms-form-head .cms-rule{width:56px;height:4px;background:var(--color-accent,#C2922E);margin:.9rem 0 0;border-radius:2px}
.cms-form-head p{margin:.85rem 0 0;color:var(--color-text-soft,#6b7280);font-size:.95rem;line-height:1.6}
.cms-form{display:flex;flex-direction:column;gap:1.15rem}
.cms-form label.cms-label{display:block;margin-bottom:.4rem;font-size:.8rem;font-weight:700;letter-spacing:.02em;color:var(--color-heading,var(--color-primary,#1A1D24))}
.cms-form .cms-req{color:var(--color-accent,#C2922E);margin-left:.2rem}
.cms-field{display:block;width:100%;padding:.8rem 1rem;border:1px solid var(--color-border,#d8d4cc);border-radius:var(--radius-button,4px);font-size:.95rem;line-height:1.5;background:#fff;color:var(--color-text,#1f2430);outline:none;transition:border-color .15s,box-shadow .15s;font-family:inherit;box-sizing:border-box}
.cms-field::placeholder{color:#9ca3af}
.cms-field:focus{border-color:var(--color-accent,#C2922E);box-shadow:0 0 0 3px color-mix(in srgb, var(--color-accent,#C2922E) 22%, transparent)}
.cms-choice{display:flex;align-items:center;gap:.55rem;cursor:pointer;font-size:.9rem;color:var(--color-text,#1f2430)}
.cms-choice input{width:1.05rem;height:1.05rem;accent-color:var(--color-accent,#C2922E)}
.cms-field-err{margin-top:.35rem;font-size:.8rem;color:var(--color-error,#dc2626)}
.cms-submit{padding:.95rem 2.75rem;background:var(--color-primary,#1A1D24);color:#fff;border:none;border-radius:var(--radius-button,4px);font-size:.82rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase;cursor:pointer;transition:transform .15s,box-shadow .2s,background .2s;align-self:flex-start}
.cms-submit:hover:not(:disabled){transform:translateY(-2px);box-shadow:0 10px 24px rgba(20,22,28,.18)}
.cms-submit:disabled{background:#9ca3af;cursor:not-allowed}
@media(max-width:600px){.cms-form-card{padding:1.85rem 1.35rem}.cms-submit{width:100%;text-align:center}}
`;

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
      <section className="cms-form-wrap">
        <style>{FORM_STYLES}</style>
        <div
          className="cms-form-card"
          style={{ textAlign: "center", borderTopColor: "var(--color-accent, #C2922E)" }}
        >
          <span style={{ fontSize: "2.75rem" }}>✅</span>
          <p style={{ fontWeight: 700, color: "var(--color-heading, var(--color-primary, #1A1D24))", margin: "1rem 0 0", fontSize: "1.05rem" }}>
            Formunuz başarıyla gönderildi. Teşekkürler!
          </p>
          <p style={{ color: "var(--color-text-soft, #6b7280)", margin: "0.5rem 0 0", fontSize: "0.9rem" }}>
            En kısa sürede size dönüş yapacağız.
          </p>
        </div>
      </section>
    );
  }

  const visibleFields = form.fields.filter((f) => f.type !== "hidden");

  return (
    <section className="cms-form-wrap">
      <style>{FORM_STYLES}</style>
      <div className="cms-form-card">
        {(title || description) && (
          <div className="cms-form-head">
            {title && <h2>{title}</h2>}
            {title && <div className="cms-rule" />}
            {description && <p>{description}</p>}
          </div>
        )}

        {errorMessage && (
          <div
            style={{
              marginBottom: "1.25rem",
              padding: "0.875rem 1rem",
              background: "var(--color-error-bg, #fef2f2)",
              border: "1px solid var(--color-error-border, #fecaca)",
              borderRadius: "var(--radius-button, 4px)",
              color: "var(--color-error, #dc2626)",
              fontSize: "0.875rem",
            }}
          >
            {errorMessage}
          </div>
        )}

        <form onSubmit={handleSubmit} className="cms-form" noValidate>
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
                  <label className="cms-label">
                    {field.label}
                    {field.is_required && <span className="cms-req">*</span>}
                  </label>
                )}
                <FieldInput field={field} />
                {errs.length > 0 && <p className="cms-field-err">{errs[0]}</p>}
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
            <button type="submit" disabled={status === "submitting"} className="cms-submit">
              {status === "submitting" ? "Gönderiliyor…" : submitLabel}
            </button>
          </div>
        </form>
      </div>
    </section>
  );
}
