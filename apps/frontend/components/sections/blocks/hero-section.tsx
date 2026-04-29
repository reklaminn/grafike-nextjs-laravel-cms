import Image from "next/image";
import Link from "next/link";
import type { SectionBlockProps } from "@/lib/sections/component-registry";
import { str, resolveMediaUrl } from "@/lib/sections/component-registry";

/** Hero section — covers type: "hero", all variations. */
export function HeroSection({ section }: SectionBlockProps) {
  const c = (section.content ?? {}) as Record<string, unknown>;

  const eyebrow    = str(c, "eyebrow");
  const title      = str(c, "title");
  const subtitle   = str(c, "subtitle");
  const buttonText = str(c, "button_text");
  const buttonUrl  = str(c, "button_url", "#");
  const bgImage    = resolveMediaUrl(str(c, "bg_image") || str(c, "image_url"));
  const variation  = section.variation ?? "";

  const isSplit = variation.includes("split");

  // ── Split layout (text left, image right) ──────────────────────────────────
  if (isSplit) {
    return (
      <section
        className="hero hero--split"
        style={{
          padding:    "5rem 0",
          background: "var(--color-secondary, #f3ede6)",
        }}
      >
        <div
          className="container"
          style={{
            display:       "grid",
            gridTemplateColumns: "1fr 1fr",
            gap:           "3rem",
            alignItems:    "center",
            maxWidth:      "var(--container-width, 1280px)",
            margin:        "0 auto",
            padding:       "0 1.5rem",
          }}
        >
          {/* Text side */}
          <div>
            {eyebrow && (
              <p
                style={{
                  fontSize:      "0.8rem",
                  fontWeight:    700,
                  letterSpacing: "0.1em",
                  textTransform: "uppercase",
                  color:         "var(--color-primary, #6366f1)",
                  marginBottom:  "0.75rem",
                }}
              >
                {eyebrow}
              </p>
            )}
            {title && (
              <h1
                style={{
                  fontSize:    "clamp(2rem, 5vw, 3.5rem)",
                  fontWeight:  800,
                  lineHeight:  1.15,
                  marginBottom: "1.25rem",
                  color:       "var(--color-heading, #111827)",
                }}
              >
                {title}
              </h1>
            )}
            {subtitle && (
              <p
                style={{
                  fontSize:    "1.1rem",
                  lineHeight:  1.7,
                  color:       "var(--color-text-soft, #374151)",
                  marginBottom: "2rem",
                  maxWidth:    "480px",
                }}
              >
                {subtitle}
              </p>
            )}
            {buttonText && (
              <Link
                href={buttonUrl}
                style={{
                  display:         "inline-block",
                  padding:         "0.85rem 2rem",
                  background:      "var(--color-primary, #6366f1)",
                  color:           "#fff",
                  borderRadius:    "var(--radius-button, 0.5rem)",
                  fontWeight:      600,
                  textDecoration:  "none",
                  fontSize:        "0.95rem",
                  transition:      "opacity 0.2s",
                }}
              >
                {buttonText}
              </Link>
            )}
          </div>

          {/* Image side */}
          {bgImage && (
            <div
              style={{
                position:     "relative",
                borderRadius: "var(--radius-card, 1rem)",
                overflow:     "hidden",
                aspectRatio:  "4/3",
              }}
            >
              <Image
                src={bgImage}
                alt={title || "Hero image"}
                fill
                priority
                sizes="(max-width: 768px) 100vw, 50vw"
                style={{ objectFit: "cover" }}
              />
            </div>
          )}
        </div>
      </section>
    );
  }

  // ── Centered / full-width layout ───────────────────────────────────────────
  return (
    <section
      className="hero hero--centered"
      style={{
        position:       "relative",
        padding:        "6rem 1.5rem",
        textAlign:      "center",
        overflow:       "hidden",
        background:     bgImage
          ? "transparent"
          : "linear-gradient(135deg, var(--color-primary, #6366f1) 0%, var(--color-accent, #4f46e5) 100%)",
        color:          bgImage ? "inherit" : "#fff",
      }}
    >
      {/* Background image */}
      {bgImage && (
        <>
          <Image
            src={bgImage}
            alt=""
            fill
            priority
            sizes="100vw"
            style={{ objectFit: "cover", zIndex: 0 }}
          />
          {/* Dark overlay */}
          <div
            style={{
              position:   "absolute",
              inset:      0,
              background: "rgba(0,0,0,0.45)",
              zIndex:     1,
            }}
          />
        </>
      )}

      {/* Content */}
      <div
        style={{
          position:   "relative",
          zIndex:     2,
          maxWidth:   "700px",
          margin:     "0 auto",
          color:      bgImage ? "#fff" : "inherit",
        }}
      >
        {eyebrow && (
          <p
            style={{
              fontSize:      "0.8rem",
              fontWeight:    700,
              letterSpacing: "0.12em",
              textTransform: "uppercase",
              opacity:       0.85,
              marginBottom:  "0.75rem",
            }}
          >
            {eyebrow}
          </p>
        )}
        {title && (
          <h1
            style={{
              fontSize:    "clamp(2rem, 5vw, 3.5rem)",
              fontWeight:  800,
              lineHeight:  1.15,
              marginBottom: "1.25rem",
            }}
          >
            {title}
          </h1>
        )}
        {subtitle && (
          <p
            style={{
              fontSize:    "1.15rem",
              lineHeight:  1.7,
              opacity:     0.9,
              marginBottom: "2rem",
            }}
          >
            {subtitle}
          </p>
        )}
        {buttonText && (
          <Link
            href={buttonUrl}
            style={{
              display:        "inline-block",
              padding:        "0.9rem 2.25rem",
              background:     "#fff",
              color:          "var(--color-primary, #6366f1)",
              borderRadius:   "var(--radius-button, 0.5rem)",
              fontWeight:     700,
              textDecoration: "none",
              fontSize:       "0.95rem",
              boxShadow:      "0 4px 14px rgba(0,0,0,0.15)",
            }}
          >
            {buttonText}
          </Link>
        )}
      </div>
    </section>
  );
}
