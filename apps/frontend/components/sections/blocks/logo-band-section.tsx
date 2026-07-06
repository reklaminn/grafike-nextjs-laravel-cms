import Image from "next/image";
import type { SectionBlockProps } from "@/lib/sections/component-registry";
import { str, parseJsonArray, extractGroupItems, resolveMediaUrl, isLocalMediaPath } from "@/lib/sections/component-registry";

type LogoItem = { url?: string; alt?: string; href?: string };

/** Logo band / client logos — type: "logo-menu" | "logo-band", all variations. */
export function LogoBandSection({ section }: SectionBlockProps) {
  const c = (section.content ?? {}) as Record<string, unknown>;

  const title = str(c, "title");

  let logos: LogoItem[] = parseJsonArray<LogoItem>(c, "logos");
  if (logos.length === 0) {
    logos = extractGroupItems(c, "logo_", ["url", "alt", "href"], 20)
      .map((item) => ({ url: resolveMediaUrl(item.url ?? ""), alt: item.alt, href: item.href }))
      .filter((l) => l.url);
  }

  if (logos.length === 0) return null;

  return (
    <section
      className="logo-band-section"
      style={{
        padding:    "3rem 1.5rem",
        background: "var(--color-secondary, #f9fafb)",
        borderTop:  "1px solid var(--color-border, #e5e7eb)",
        borderBottom: "1px solid var(--color-border, #e5e7eb)",
      }}
    >
      <div style={{ maxWidth: "var(--container-width, 1280px)", margin: "0 auto" }}>
        {title && (
          <p
            style={{
              textAlign:    "center",
              fontSize:     "0.8rem",
              fontWeight:   600,
              letterSpacing: "0.08em",
              textTransform: "uppercase",
              color:        "var(--color-text-soft, #9ca3af)",
              marginBottom: "2rem",
            }}
          >
            {title}
          </p>
        )}

        <div
          style={{
            display:        "flex",
            flexWrap:       "wrap",
            gap:            "2rem 3rem",
            alignItems:     "center",
            justifyContent: "center",
          }}
        >
          {logos.map((logo, i) => {
            const img = (
              <div
                key={i}
                style={{
                  position: "relative",
                  height:   "40px",
                  width:    "120px",
                  filter:   "grayscale(1) opacity(0.6)",
                  transition: "filter 0.2s",
                }}
              >
                <Image
                  src={resolveMediaUrl(logo.url ?? "")}
                  alt={logo.alt ?? `Logo ${i + 1}`}
                  fill
                  sizes="120px"
                  style={{ objectFit: "contain" }}
                  unoptimized={isLocalMediaPath(resolveMediaUrl(logo.url ?? ""))}
                />
              </div>
            );

            return logo.href ? (
              <a
                key={i}
                href={logo.href}
                target="_blank"
                rel="noopener noreferrer"
                style={{ display: "block" }}
              >
                {img}
              </a>
            ) : (
              img
            );
          })}
        </div>
      </div>
    </section>
  );
}
