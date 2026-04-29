import Link from "next/link";
import type { SectionBlockProps } from "@/lib/sections/component-registry";
import { str } from "@/lib/sections/component-registry";

type CtaTheme = "brand" | "dark" | "light";

const themeStyles: Record<CtaTheme, { bg: string; color: string; border: string }> = {
  brand: {
    bg:     "linear-gradient(135deg, var(--color-primary, #6366f1) 0%, var(--color-accent, #4f46e5) 100%)",
    color:  "#fff",
    border: "transparent",
  },
  dark: {
    bg:     "#111827",
    color:  "#fff",
    border: "transparent",
  },
  light: {
    bg:     "var(--color-secondary, #f3ede6)",
    color:  "var(--color-heading, #111827)",
    border: "var(--color-border, #e5e7eb)",
  },
};

/** CTA Banner — type: "cta-banner", all variations. */
export function CtaBannerSection({ section }: SectionBlockProps) {
  const c = (section.content ?? {}) as Record<string, unknown>;

  const title             = str(c, "title");
  const body              = str(c, "body");
  const buttonText        = str(c, "button_text");
  const buttonUrl         = str(c, "button_url", "#");
  const secondaryText     = str(c, "secondary_link_text");
  const secondaryUrl      = str(c, "secondary_link");
  const theme             = (str(c, "theme", "brand") as CtaTheme);
  const palette           = themeStyles[theme] ?? themeStyles.brand;

  const isLight = theme === "light";

  return (
    <section
      className="cta-banner"
      style={{
        padding:    "5rem 1.5rem",
        background: palette.bg,
        border:     `1px solid ${palette.border}`,
        color:      palette.color,
      }}
    >
      <div
        style={{
          maxWidth:   "740px",
          margin:     "0 auto",
          textAlign:  "center",
        }}
      >
        {title && (
          <h2
            style={{
              fontSize:    "clamp(1.75rem, 4vw, 2.75rem)",
              fontWeight:  800,
              lineHeight:  1.2,
              marginBottom: body ? "1.25rem" : "2rem",
            }}
          >
            {title}
          </h2>
        )}

        {body && (
          <p
            style={{
              fontSize:    "1.1rem",
              lineHeight:  1.7,
              opacity:     0.88,
              marginBottom: "2.25rem",
              maxWidth:    "560px",
              margin:      "0 auto 2.25rem",
            }}
          >
            {body}
          </p>
        )}

        <div
          style={{
            display:        "flex",
            gap:            "1rem",
            justifyContent: "center",
            flexWrap:       "wrap",
          }}
        >
          {buttonText && (
            <Link
              href={buttonUrl}
              style={{
                display:        "inline-block",
                padding:        "0.875rem 2.25rem",
                background:     isLight
                  ? "var(--color-primary, #6366f1)"
                  : "#fff",
                color:          isLight
                  ? "#fff"
                  : "var(--color-primary, #6366f1)",
                borderRadius:   "var(--radius-button, 0.5rem)",
                fontWeight:     700,
                textDecoration: "none",
                fontSize:       "0.95rem",
                boxShadow:      "0 4px 14px rgba(0,0,0,0.1)",
              }}
            >
              {buttonText}
            </Link>
          )}

          {secondaryText && secondaryUrl && (
            <Link
              href={secondaryUrl}
              style={{
                display:        "inline-block",
                padding:        "0.875rem 2rem",
                background:     "transparent",
                color:          isLight
                  ? "var(--color-heading, #111827)"
                  : "#fff",
                borderRadius:   "var(--radius-button, 0.5rem)",
                fontWeight:     600,
                textDecoration: "none",
                fontSize:       "0.95rem",
                border:         `2px solid ${isLight ? "var(--color-border, #d1d5db)" : "rgba(255,255,255,0.5)"}`,
              }}
            >
              {secondaryText}
            </Link>
          )}
        </div>
      </div>
    </section>
  );
}
