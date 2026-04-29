import type { SectionBlockProps } from "@/lib/sections/component-registry";
import { str } from "@/lib/sections/component-registry";

/** Rich text content block — type: "rich-text", all variations. */
export function RichTextSection({ section }: SectionBlockProps) {
  const c = (section.content ?? {}) as Record<string, unknown>;

  const title    = str(c, "title");
  const bodyHtml = str(c, "body_html") || str(c, "body") || str(c, "content");

  if (!title && !bodyHtml) return null;

  return (
    <section
      className="rich-text-section"
      style={{
        padding:    "4rem 1.5rem",
        background: "var(--color-bg, #fff)",
      }}
    >
      <div
        style={{
          maxWidth: "760px",
          margin:   "0 auto",
        }}
      >
        {title && (
          <h2
            style={{
              fontSize:    "clamp(1.5rem, 3vw, 2.25rem)",
              fontWeight:  700,
              lineHeight:  1.25,
              marginBottom: "1.5rem",
              color:       "var(--color-heading, #111827)",
            }}
          >
            {title}
          </h2>
        )}

        {bodyHtml && (
          <div
            className="prose"
            // biome-ignore lint/security/noDangerouslySetInnerHtml: CMS-controlled content
            dangerouslySetInnerHTML={{ __html: bodyHtml }}
            style={{
              lineHeight: 1.75,
              color:      "var(--color-text, #374151)",
              fontSize:   "1rem",
            }}
          />
        )}
      </div>
    </section>
  );
}
