import Link from "next/link";
import type { SectionBlockProps } from "@/lib/sections/component-registry";
import { str, parseJsonArray, extractGroupItems } from "@/lib/sections/component-registry";

type PricingPlan = {
  name?: string;
  price?: string;
  period?: string;
  description?: string;
  features?: string; // newline or comma separated
  button_text?: string;
  button_url?: string;
  is_featured?: string; // "true" | "1" | "false"
};

/** Pricing table — type: "pricing", all variations. */
export function PricingSection({ section }: SectionBlockProps) {
  const c = (section.content ?? {}) as Record<string, unknown>;

  const title       = str(c, "title");
  const description = str(c, "description");

  let plans: PricingPlan[] = parseJsonArray<PricingPlan>(c, "plans");
  if (plans.length === 0) {
    plans = extractGroupItems(c, "plan_", [
      "name", "price", "period", "description", "features", "button_text", "button_url", "is_featured",
    ], 6);
  }

  return (
    <section
      className="pricing-section"
      style={{ padding: "5rem 1.5rem", background: "var(--color-bg, #fff)" }}
    >
      <div style={{ maxWidth: "var(--container-width, 1280px)", margin: "0 auto" }}>
        {(title || description) && (
          <div style={{ textAlign: "center", marginBottom: "3.5rem" }}>
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
              <p style={{ fontSize: "1.05rem", color: "var(--color-text-soft, #374151)", maxWidth: "560px", margin: "0 auto" }}>
                {description}
              </p>
            )}
          </div>
        )}

        <div
          style={{
            display:             "grid",
            gridTemplateColumns: `repeat(auto-fit, minmax(260px, 1fr))`,
            gap:                 "1.5rem",
            alignItems:          "stretch",
          }}
        >
          {plans.map((plan, i) => {
            const featured = plan.is_featured === "true" || plan.is_featured === "1";
            const featureList = (plan.features ?? "")
              .split(/[\n,]/)
              .map((f) => f.trim())
              .filter(Boolean);

            return (
              <div
                key={i}
                style={{
                  display:       "flex",
                  flexDirection: "column",
                  padding:       "2.5rem 2rem",
                  borderRadius:  "var(--radius-card, 1rem)",
                  border:        featured
                    ? `2px solid var(--color-primary, #6366f1)`
                    : "1px solid var(--color-border, #e5e7eb)",
                  background:    featured
                    ? "linear-gradient(135deg, var(--color-primary, #6366f1) 0%, var(--color-accent, #4f46e5) 100%)"
                    : "#fff",
                  color:         featured ? "#fff" : "inherit",
                  boxShadow:     featured ? "0 20px 60px rgba(99,102,241,0.3)" : "none",
                  position:      "relative",
                }}
              >
                {featured && (
                  <span
                    style={{
                      position:   "absolute",
                      top:        "-0.75rem",
                      left:       "50%",
                      transform:  "translateX(-50%)",
                      background: "#f59e0b",
                      color:      "#fff",
                      fontSize:   "0.7rem",
                      fontWeight: 700,
                      padding:    "0.2rem 0.75rem",
                      borderRadius: "999px",
                      letterSpacing: "0.08em",
                      textTransform: "uppercase",
                    }}
                  >
                    Popüler
                  </span>
                )}

                {plan.name && (
                  <p style={{ fontSize: "0.9rem", fontWeight: 600, opacity: 0.8, marginBottom: "0.5rem" }}>
                    {plan.name}
                  </p>
                )}

                {plan.price && (
                  <div style={{ marginBottom: "0.5rem" }}>
                    <span style={{ fontSize: "2.5rem", fontWeight: 800, lineHeight: 1 }}>
                      {plan.price}
                    </span>
                    {plan.period && (
                      <span style={{ fontSize: "0.9rem", opacity: 0.7, marginLeft: "0.25rem" }}>
                        /{plan.period}
                      </span>
                    )}
                  </div>
                )}

                {plan.description && (
                  <p style={{ fontSize: "0.875rem", opacity: 0.75, marginBottom: "1.5rem" }}>
                    {plan.description}
                  </p>
                )}

                {featureList.length > 0 && (
                  <ul
                    style={{
                      listStyle:   "none",
                      padding:     0,
                      margin:      "0 0 2rem",
                      display:     "flex",
                      flexDirection: "column",
                      gap:         "0.6rem",
                      flexGrow:    1,
                    }}
                  >
                    {featureList.map((feature, fi) => (
                      <li
                        key={fi}
                        style={{ display: "flex", alignItems: "center", gap: "0.5rem", fontSize: "0.875rem" }}
                      >
                        <span style={{ fontSize: "0.75rem", color: featured ? "#86efac" : "#22c55e" }}>✓</span>
                        {feature}
                      </li>
                    ))}
                  </ul>
                )}

                {plan.button_text && (
                  <Link
                    href={plan.button_url || "#"}
                    style={{
                      display:        "block",
                      textAlign:      "center",
                      padding:        "0.75rem 1.5rem",
                      borderRadius:   "var(--radius-button, 0.5rem)",
                      fontWeight:     600,
                      fontSize:       "0.9rem",
                      textDecoration: "none",
                      background:     featured ? "#fff" : "var(--color-primary, #6366f1)",
                      color:          featured ? "var(--color-primary, #6366f1)" : "#fff",
                    }}
                  >
                    {plan.button_text}
                  </Link>
                )}
              </div>
            );
          })}
        </div>
      </div>
    </section>
  );
}
