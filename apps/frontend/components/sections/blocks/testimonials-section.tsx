import Image from "next/image";
import type { SectionBlockProps } from "@/lib/sections/component-registry";
import { str, parseJsonArray, extractGroupItems, resolveMediaUrl } from "@/lib/sections/component-registry";

type TestimonialItem = {
  quote?: string;
  author?: string;
  role?: string;
  avatar?: string;
  rating?: string;
};

/** Testimonials / reviews — type: "testimonials", all variations. */
export function TestimonialsSection({ section }: SectionBlockProps) {
  const c = (section.content ?? {}) as Record<string, unknown>;

  const title       = str(c, "title");
  const description = str(c, "description");

  let items: TestimonialItem[] = parseJsonArray<TestimonialItem>(c, "items");
  if (items.length === 0) {
    items = extractGroupItems(c, "testimonial_", ["quote", "author", "role", "avatar", "rating"], 12);
  }

  return (
    <section
      className="testimonials-section"
      style={{
        padding:    "5rem 1.5rem",
        background: "var(--color-secondary, #f3ede6)",
      }}
    >
      <div
        style={{
          maxWidth: "var(--container-width, 1280px)",
          margin:   "0 auto",
        }}
      >
        {(title || description) && (
          <div
            style={{
              textAlign:    "center",
              marginBottom: "3.5rem",
            }}
          >
            {title && (
              <h2
                style={{
                  fontSize:    "clamp(1.75rem, 3.5vw, 2.5rem)",
                  fontWeight:  700,
                  color:       "var(--color-heading, #111827)",
                  marginBottom: description ? "1rem" : 0,
                }}
              >
                {title}
              </h2>
            )}
            {description && (
              <p style={{ fontSize: "1.05rem", color: "var(--color-text-soft, #374151)" }}>
                {description}
              </p>
            )}
          </div>
        )}

        {items.length > 0 ? (
          <div
            style={{
              display:             "grid",
              gridTemplateColumns: "repeat(auto-fit, minmax(280px, 1fr))",
              gap:                 "1.5rem",
            }}
          >
            {items.map((item, i) => (
              <TestimonialCard key={i} item={item} />
            ))}
          </div>
        ) : (
          <p style={{ textAlign: "center", color: "var(--color-text-soft, #9ca3af)", fontSize: "0.9rem" }}>
            Henüz yorum eklenmemiş.
          </p>
        )}
      </div>
    </section>
  );
}

function StarRating({ rating }: { rating?: string }) {
  const count = Math.min(5, Math.max(1, Number(rating) || 5));
  return (
    <div style={{ display: "flex", gap: "0.2rem", marginBottom: "1rem" }}>
      {Array.from({ length: 5 }).map((_, i) => (
        <span key={i} style={{ color: i < count ? "#f59e0b" : "#d1d5db", fontSize: "0.9rem" }}>
          ★
        </span>
      ))}
    </div>
  );
}

function TestimonialCard({ item }: { item: TestimonialItem }) {
  const avatarUrl = resolveMediaUrl(item.avatar ?? "");

  return (
    <div
      style={{
        background:   "#fff",
        borderRadius: "var(--radius-card, 1rem)",
        padding:      "2rem",
        boxShadow:    "0 2px 12px rgba(0,0,0,0.06)",
        border:       "1px solid var(--color-border, #e5e7eb)",
        display:      "flex",
        flexDirection: "column",
        gap:          "1rem",
      }}
    >
      <StarRating rating={item.rating} />

      {item.quote && (
        <blockquote
          style={{
            margin:     0,
            fontSize:   "0.95rem",
            lineHeight: 1.7,
            color:      "var(--color-text, #374151)",
            fontStyle:  "italic",
          }}
        >
          &ldquo;{item.quote}&rdquo;
        </blockquote>
      )}

      {(item.author || item.role) && (
        <div style={{ display: "flex", alignItems: "center", gap: "0.75rem", marginTop: "auto" }}>
          {avatarUrl && (
            <div
              style={{
                width:        "2.5rem",
                height:       "2.5rem",
                borderRadius: "50%",
                overflow:     "hidden",
                position:     "relative",
                flexShrink:   0,
              }}
            >
              <Image
                src={avatarUrl}
                alt={item.author ?? ""}
                fill
                sizes="40px"
                style={{ objectFit: "cover" }}
              />
            </div>
          )}
          <div>
            {item.author && (
              <p style={{ margin: 0, fontWeight: 600, fontSize: "0.875rem", color: "var(--color-heading, #111827)" }}>
                {item.author}
              </p>
            )}
            {item.role && (
              <p style={{ margin: 0, fontSize: "0.8rem", color: "var(--color-text-soft, #6b7280)" }}>
                {item.role}
              </p>
            )}
          </div>
        </div>
      )}
    </div>
  );
}
