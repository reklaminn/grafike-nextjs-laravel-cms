import type { SectionBlockProps } from "@/lib/sections/component-registry";
import { str, parseJsonArray, extractGroupItems } from "@/lib/sections/component-registry";

type FeatureItem = {
  icon?: string;
  title?: string;
  text?: string;
};

/** Features grid — type: "features", all variations. */
export function FeaturesSection({ section }: SectionBlockProps) {
  const c = (section.content ?? {}) as Record<string, unknown>;

  const title       = str(c, "title");
  const description = str(c, "description");

  // Items: try JSON array → indexed groups → empty
  let items: FeatureItem[] = parseJsonArray<FeatureItem>(c, "items");
  if (items.length === 0) {
    items = extractGroupItems(c, "feature_", ["icon", "title", "text"], 12);
  }

  return (
    <section
      className="features-section"
      style={{
        padding:    "5rem 1.5rem",
        background: "var(--color-bg, #fff)",
      }}
    >
      <div
        style={{
          maxWidth: "var(--container-width, 1280px)",
          margin:   "0 auto",
        }}
      >
        {/* Heading */}
        {(title || description) && (
          <div
            style={{
              textAlign:    "center",
              marginBottom: "3.5rem",
              maxWidth:     "620px",
              margin:       "0 auto 3.5rem",
            }}
          >
            {title && (
              <h2
                style={{
                  fontSize:    "clamp(1.75rem, 3.5vw, 2.5rem)",
                  fontWeight:  700,
                  lineHeight:  1.2,
                  marginBottom: description ? "1rem" : 0,
                  color:       "var(--color-heading, #111827)",
                }}
              >
                {title}
              </h2>
            )}
            {description && (
              <p
                style={{
                  fontSize:   "1.05rem",
                  lineHeight: 1.7,
                  color:      "var(--color-text-soft, #374151)",
                }}
              >
                {description}
              </p>
            )}
          </div>
        )}

        {/* Feature grid */}
        {items.length > 0 && (
          <div
            style={{
              display:             "grid",
              gridTemplateColumns: "repeat(auto-fit, minmax(240px, 1fr))",
              gap:                 "2rem",
            }}
          >
            {items.map((item, i) => (
              <FeatureCard key={i} item={item} index={i} />
            ))}
          </div>
        )}
      </div>
    </section>
  );
}

function FeatureCard({ item, index }: { item: FeatureItem; index: number }) {
  const colors = [
    { bg: "#eff6ff", icon: "#3b82f6" },
    { bg: "#f0fdf4", icon: "#22c55e" },
    { bg: "#fdf4ff", icon: "#a855f7" },
    { bg: "#fff7ed", icon: "#f97316" },
    { bg: "#fef2f2", icon: "#ef4444" },
    { bg: "#f0f9ff", icon: "#0ea5e9" },
  ];
  const palette = colors[index % colors.length];

  return (
    <div
      style={{
        padding:      "2rem",
        borderRadius: "var(--radius-card, 1rem)",
        border:       "1px solid var(--color-border, #e5e7eb)",
        background:   "#fff",
        transition:   "box-shadow 0.2s",
      }}
    >
      {/* Icon */}
      {item.icon && (
        <div
          style={{
            width:        "3rem",
            height:       "3rem",
            borderRadius: "0.75rem",
            background:   palette.bg,
            display:      "flex",
            alignItems:   "center",
            justifyContent: "center",
            marginBottom: "1.25rem",
            fontSize:     "1.35rem",
            color:        palette.icon,
          }}
        >
          {/* Support: FA class (fas fa-star), emoji, or text */}
          {item.icon.startsWith("fa") ? (
            <i className={item.icon} />
          ) : (
            <span>{item.icon}</span>
          )}
        </div>
      )}

      {item.title && (
        <h3
          style={{
            fontSize:    "1.1rem",
            fontWeight:  600,
            marginBottom: "0.5rem",
            color:       "var(--color-heading, #111827)",
          }}
        >
          {item.title}
        </h3>
      )}

      {item.text && (
        <p
          style={{
            fontSize:   "0.9rem",
            lineHeight: 1.65,
            color:      "var(--color-text-soft, #6b7280)",
          }}
        >
          {item.text}
        </p>
      )}
    </div>
  );
}
