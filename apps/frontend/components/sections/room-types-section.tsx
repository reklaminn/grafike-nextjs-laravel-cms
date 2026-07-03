/**
 * RoomTypesSection — "daireler" / oda-tipleri listeleme bölümü (Lodging).
 *
 * Konaklama modülünün oda/daire tiplerini (/api/v1/lodging/room-types) çeker ve
 * kart olarak listeler. Her kartta "Rezervasyon" butonu bulunur.
 *
 * Site-başına ÖZELLEŞTİRİLEBİLİR: content.item_template dolu ise her kart o HTML
 * şablonuyla render edilir (placeholder'lar otomatik dolar); boşsa tema-uyumlu
 * varsayılan kart kullanılır. Böylece kod değişmeden her sitede farklı tasarım
 * mümkün.
 *
 * content alanları (hepsi opsiyonel):
 *   title           — bölüm başlığı (varsayılan "Daireler")
 *   subtitle        — üst etiket / kısa açıklama
 *   columns         — sütun sayısı "2".."4" (varsayılan otomatik)
 *   reserve_label   — buton metni (varsayılan "Rezervasyon")
 *   reserve_target  — buton hedefi (varsayılan "/rezervasyon"; ?oda=<slug> eklenir)
 *   empty_text      — hiç oda tipi yoksa gösterilecek metin
 *   item_template   — (gelişmiş) kart HTML şablonu; {{name}} {{summary}}
 *                     {{base_price}} {{currency}} {{capacity_max}} {{size_m2}}
 *                     {{bedrooms}} {{bathrooms}} {{slug}} ve {{{image}}} desteklenir
 */
import { getRoomTypes } from "@/lib/api/client";
import { str } from "@/lib/sections/component-registry";
import type { PageSection, RoomType } from "@/lib/types";

function formatPrice(value: number, currency: string): string {
  const num = Number.isFinite(value) ? value : 0;
  const formatted = num.toLocaleString("tr-TR", {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  });
  return `${formatted} ${currency}`.trim();
}

function escapeHtml(value: string): string {
  return value
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
}

function reserveHref(target: string, slug: string): string {
  if (!target) target = "/rezervasyon";
  if (target.startsWith("#")) return target;
  const sep = target.includes("?") ? "&" : "?";
  return `${target}${sep}oda=${encodeURIComponent(slug)}`;
}

/** Fill a per-item HTML template with room-type values (site-editable path). */
function renderItemTemplate(tpl: string, rt: RoomType): string {
  const values: Record<string, string> = {
    name:         rt.name ?? "",
    slug:         rt.slug ?? "",
    summary:      rt.summary ?? "",
    description:  rt.description ?? "",
    capacity_min: rt.capacity_min != null ? String(rt.capacity_min) : "",
    capacity_max: rt.capacity_max != null ? String(rt.capacity_max) : "",
    size_m2:      rt.size_m2 != null ? String(rt.size_m2) : "",
    bedrooms:     rt.bedrooms != null ? String(rt.bedrooms) : "",
    bathrooms:    rt.bathrooms != null ? String(rt.bathrooms) : "",
    base_price:   formatPrice(rt.base_price, rt.currency ?? ""),
    currency:     rt.currency ?? "",
    image:        rt.images?.[0] ?? "",
  };

  return tpl
    // {{{key}}} — ham (kaçışsız), görsel URL gibi
    .replace(/\{\{\{\s*([a-z0-9_]+)\s*\}\}\}/gi, (_m, key: string) => values[key] ?? "")
    // {{key}} — kaçışlı metin
    .replace(/\{\{\s*([a-z0-9_]+)\s*\}\}/gi, (_m, key: string) => escapeHtml(values[key] ?? ""));
}

// ── Varsayılan tema-uyumlu kart ────────────────────────────────────────────────

function MetaBadge({ children }: { children: React.ReactNode }) {
  return (
    <span
      style={{
        display: "inline-flex",
        alignItems: "center",
        gap: ".3rem",
        fontSize: ".8rem",
        color: "var(--color-text-soft, #6b7280)",
      }}
    >
      {children}
    </span>
  );
}

function DefaultCard({ rt, reserveLabel, reserveTarget }: { rt: RoomType; reserveLabel: string; reserveTarget: string }) {
  const image = rt.images?.[0] ?? "";

  return (
    <article
      style={{
        display: "flex",
        flexDirection: "column",
        background: "var(--color-surface, #fff)",
        border: "1px solid var(--color-border, #e5e7eb)",
        borderRadius: "var(--radius-card, 0.9rem)",
        overflow: "hidden",
      }}
    >
      <div style={{ position: "relative", aspectRatio: "4 / 3", background: "var(--color-bg, #f3f4f6)" }}>
        {image ? (
          // eslint-disable-next-line @next/next/no-img-element
          <img
            src={image}
            alt={rt.name}
            style={{ width: "100%", height: "100%", objectFit: "cover", display: "block" }}
          />
        ) : (
          <div style={{ width: "100%", height: "100%", display: "flex", alignItems: "center", justifyContent: "center", color: "var(--color-text-soft, #9ca3af)", fontSize: "2rem" }}>
            🏢
          </div>
        )}
      </div>

      <div style={{ display: "flex", flexDirection: "column", gap: ".6rem", padding: "1.25rem", flex: 1 }}>
        <h3 style={{ fontSize: "1.15rem", fontWeight: 700, color: "var(--color-heading, #111827)", margin: 0 }}>
          {rt.name}
        </h3>

        {rt.summary && (
          <p style={{ fontSize: ".9rem", color: "var(--color-text-soft, #6b7280)", margin: 0, lineHeight: 1.5 }}>
            {rt.summary}
          </p>
        )}

        <div style={{ display: "flex", flexWrap: "wrap", gap: ".9rem", marginTop: ".2rem" }}>
          {rt.capacity_max != null && <MetaBadge>👥 {rt.capacity_max} kişi</MetaBadge>}
          {rt.size_m2 != null && <MetaBadge>📐 {rt.size_m2} m²</MetaBadge>}
          {rt.bedrooms != null && <MetaBadge>🛏 {rt.bedrooms} oda</MetaBadge>}
        </div>

        <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", gap: "1rem", marginTop: "auto", paddingTop: ".9rem" }}>
          <div>
            <span style={{ fontSize: "1.15rem", fontWeight: 700, color: "var(--color-heading, #111827)" }}>
              {formatPrice(rt.base_price, rt.currency ?? "")}
            </span>
            <span style={{ display: "block", fontSize: ".72rem", color: "var(--color-text-soft, #9ca3af)" }}>
              gecelik başlangıç
            </span>
          </div>
          <a
            href={reserveHref(reserveTarget, rt.slug)}
            style={{
              flexShrink: 0,
              display: "inline-flex",
              alignItems: "center",
              padding: ".6rem 1.1rem",
              background: "var(--color-primary, #6366f1)",
              color: "#fff",
              fontSize: ".85rem",
              fontWeight: 600,
              borderRadius: "var(--radius-button, .5rem)",
              textDecoration: "none",
            }}
          >
            {reserveLabel}
          </a>
        </div>
      </div>
    </article>
  );
}

// ── Bölüm ───────────────────────────────────────────────────────────────────────

export async function RoomTypesSection({ section }: { section: PageSection }) {
  const content = (section.content ?? {}) as Record<string, unknown>;

  const title         = str(content, "title", "Daireler");
  const subtitle      = str(content, "subtitle");
  const reserveLabel  = str(content, "reserve_label", "Rezervasyon");
  const reserveTarget = str(content, "reserve_target", "/rezervasyon");
  const emptyText     = str(content, "empty_text");
  const itemTemplate  = str(content, "item_template");

  const columnsRaw = parseInt(str(content, "columns", ""), 10);
  const columns    = columnsRaw >= 1 && columnsRaw <= 4 ? columnsRaw : null;

  const rooms = await getRoomTypes();

  const gridTemplate = columns
    ? `repeat(${columns}, minmax(0, 1fr))`
    : "repeat(auto-fill, minmax(280px, 1fr))";

  return (
    <section style={{ padding: "4rem 1.5rem", background: "var(--color-bg, #fff)" }}>
      <div style={{ maxWidth: "var(--container-width, 1200px)", margin: "0 auto" }}>
        {(subtitle || title) && (
          <div style={{ textAlign: "center", marginBottom: "2.5rem" }}>
            {subtitle && (
              <div style={{ fontSize: ".8rem", fontWeight: 700, letterSpacing: ".12em", textTransform: "uppercase", color: "var(--color-primary, #6366f1)", marginBottom: ".6rem" }}>
                {subtitle}
              </div>
            )}
            {title && (
              <h2 style={{ fontSize: "2rem", fontWeight: 700, color: "var(--color-heading, #111827)", margin: 0 }}>
                {title}
              </h2>
            )}
          </div>
        )}

        {rooms.length === 0 ? (
          <p style={{ textAlign: "center", color: "var(--color-text-soft, #9ca3af)" }}>
            {emptyText || "Daire tipleri yakında eklenecek."}
          </p>
        ) : (
          <div style={{ display: "grid", gridTemplateColumns: gridTemplate, gap: "1.5rem" }}>
            {rooms.map((rt) =>
              itemTemplate ? (
                <div key={rt.slug} dangerouslySetInnerHTML={{ __html: renderItemTemplate(itemTemplate, rt) }} />
              ) : (
                <DefaultCard key={rt.slug} rt={rt} reserveLabel={reserveLabel} reserveTarget={reserveTarget} />
              ),
            )}
          </div>
        )}
      </div>
    </section>
  );
}
