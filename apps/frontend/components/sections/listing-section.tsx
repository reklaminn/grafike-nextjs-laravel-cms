/**
 * ListingSection — genel "listeleme" bölümü (Faz 2).
 *
 * Tek blok, üç veri kaynağı (content.source):
 *   room_types  → Konaklama oda/daire tipleri (/api/v1/lodging/room-types)
 *   articles    → Yazılar (/api/v1/articles)
 *   pages       → Bu sayfanın alt sayfaları (/api/v1/pages/{id}/children)
 *
 * SİTE-BAŞINA ÖZELLEŞTİRİLEBİLİR: content.item_template dolu ise her öğe o HTML
 * şablonuyla render edilir; boşsa tema-uyumlu varsayılan kart. Placeholder'lar:
 *   {{title}} {{summary}} {{price}} {{link}} {{link_label}} {{date}} {{{image}}}
 *   (+ room_types için {{name}} {{base_price}} {{currency}} {{capacity_max}}
 *    {{size_m2}} {{bedrooms}} {{slug}})
 *
 * content: source, title, subtitle, columns(1-4), limit, item_template,
 *          reserve_label, reserve_target, empty_text
 *
 * `daireler` vb. eski tipler bu bileşene source varsayılanı room_types ile düşer.
 */
import { getArticles, getChildPages, getRoomTypes } from "@/lib/api/client";
import { str } from "@/lib/sections/component-registry";
import type { ArticleListItem, ChildPage, PageSection, RoomType } from "@/lib/types";

type ListingItem = {
  key: string;
  title: string;
  summary: string;
  image: string;
  link: string;
  linkLabel: string;
  price: string;
  badges: string[];
  vars: Record<string, string>;
};

function formatPrice(value: number, currency: string): string {
  const num = Number.isFinite(value) ? value : 0;
  return `${num.toLocaleString("tr-TR", { minimumFractionDigits: 0, maximumFractionDigits: 0 })} ${currency}`.trim();
}

function formatDate(dateStr: string | null): string {
  if (!dateStr) return "";
  const d = new Date(dateStr);
  return Number.isNaN(d.getTime())
    ? ""
    : d.toLocaleDateString("tr-TR", { day: "numeric", month: "long", year: "numeric" });
}

function escapeHtml(value: string): string {
  return value.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
}

function reserveHref(target: string, slug: string): string {
  const t = target || "/rezervasyon";
  if (t.startsWith("#")) return t;
  return `${t}${t.includes("?") ? "&" : "?"}oda=${encodeURIComponent(slug)}`;
}

function renderItemTemplate(tpl: string, vars: Record<string, string>): string {
  return tpl
    .replace(/\{\{\{\s*([a-z0-9_]+)\s*\}\}\}/gi, (_m, k: string) => vars[k] ?? "")
    .replace(/\{\{\s*([a-z0-9_]+)\s*\}\}/gi, (_m, k: string) => escapeHtml(vars[k] ?? ""));
}

// ── Kaynak → normalize ──────────────────────────────────────────────────────────

function fromRoomTypes(rooms: RoomType[], reserveLabel: string, reserveTarget: string): ListingItem[] {
  return rooms.map((rt) => {
    const price = formatPrice(rt.base_price, rt.currency ?? "");
    const image = rt.images?.[0] ?? "";
    const badges: string[] = [];
    if (rt.capacity_max != null) badges.push(`👥 ${rt.capacity_max} kişi`);
    if (rt.size_m2 != null) badges.push(`📐 ${rt.size_m2} m²`);
    if (rt.bedrooms != null) badges.push(`🛏 ${rt.bedrooms} oda`);

    return {
      key: rt.slug,
      title: rt.name,
      summary: rt.summary ?? "",
      image,
      link: reserveHref(reserveTarget, rt.slug),
      linkLabel: reserveLabel,
      price,
      badges,
      vars: {
        title: rt.name, name: rt.name, slug: rt.slug,
        summary: rt.summary ?? "", description: rt.description ?? "",
        price, base_price: price, currency: rt.currency ?? "",
        capacity_max: rt.capacity_max != null ? String(rt.capacity_max) : "",
        size_m2: rt.size_m2 != null ? String(rt.size_m2) : "",
        bedrooms: rt.bedrooms != null ? String(rt.bedrooms) : "",
        image, link: reserveHref(reserveTarget, rt.slug), link_label: reserveLabel, date: "",
      },
    };
  });
}

function fromArticles(articles: ArticleListItem[]): ListingItem[] {
  return articles.map((a) => {
    const href = a.page?.slug ? `/${a.page.slug}/${a.slug}` : `/${a.slug}`;
    const image = a.cover?.thumb ?? a.cover?.url ?? "";
    const date = formatDate(a.display_date ?? a.published_at ?? null);

    return {
      key: `article-${a.id}`,
      title: a.title,
      summary: a.excerpt ?? "",
      image,
      link: href,
      linkLabel: "Devamını Oku",
      price: "",
      badges: date ? [`📅 ${date}`] : [],
      vars: {
        title: a.title, name: a.title, slug: a.slug, summary: a.excerpt ?? "",
        description: a.excerpt ?? "", image, link: href, link_label: "Devamını Oku", date,
        price: "", base_price: "", currency: "",
      },
    };
  });
}

function fromPages(pages: ChildPage[]): ListingItem[] {
  return pages.map((p) => ({
    key: `page-${p.slug}`,
    title: p.title,
    summary: p.summary ?? "",
    image: p.image ?? "",
    link: p.url,
    linkLabel: "İncele",
    price: "",
    badges: [],
    vars: {
      title: p.title, name: p.title, slug: p.slug, summary: p.summary ?? "",
      description: p.summary ?? "", image: p.image ?? "", link: p.url, link_label: "İncele",
      date: "", price: "", base_price: "", currency: "",
    },
  }));
}

// ── Varsayılan tema-uyumlu kart ────────────────────────────────────────────────

function DefaultCard({ item }: { item: ListingItem }) {
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
        {item.image ? (
          // eslint-disable-next-line @next/next/no-img-element
          <img src={item.image} alt={item.title} style={{ width: "100%", height: "100%", objectFit: "cover", display: "block" }} />
        ) : (
          <div style={{ width: "100%", height: "100%", display: "flex", alignItems: "center", justifyContent: "center", color: "var(--color-text-soft, #9ca3af)", fontSize: "2rem" }}>
            🏢
          </div>
        )}
      </div>

      <div style={{ display: "flex", flexDirection: "column", gap: ".6rem", padding: "1.25rem", flex: 1 }}>
        <h3 style={{ fontSize: "1.15rem", fontWeight: 700, color: "var(--color-heading, #111827)", margin: 0 }}>{item.title}</h3>

        {item.summary && (
          <p style={{ fontSize: ".9rem", color: "var(--color-text-soft, #6b7280)", margin: 0, lineHeight: 1.5 }}>{item.summary}</p>
        )}

        {item.badges.length > 0 && (
          <div style={{ display: "flex", flexWrap: "wrap", gap: ".9rem", marginTop: ".2rem", fontSize: ".8rem", color: "var(--color-text-soft, #6b7280)" }}>
            {item.badges.map((b) => <span key={b}>{b}</span>)}
          </div>
        )}

        <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", gap: "1rem", marginTop: "auto", paddingTop: ".9rem" }}>
          {item.price ? (
            <div>
              <span style={{ fontSize: "1.15rem", fontWeight: 700, color: "var(--color-heading, #111827)" }}>{item.price}</span>
              <span style={{ display: "block", fontSize: ".72rem", color: "var(--color-text-soft, #9ca3af)" }}>gecelik başlangıç</span>
            </div>
          ) : <span />}
          {item.link && (
            <a
              href={item.link}
              style={{
                flexShrink: 0, display: "inline-flex", alignItems: "center", padding: ".6rem 1.1rem",
                background: "var(--color-primary, #6366f1)", color: "#fff", fontSize: ".85rem", fontWeight: 600,
                borderRadius: "var(--radius-button, .5rem)", textDecoration: "none",
              }}
            >
              {item.linkLabel}
            </a>
          )}
        </div>
      </div>
    </article>
  );
}

// ── Bölüm ───────────────────────────────────────────────────────────────────────

export async function ListingSection({ section, pageId }: { section: PageSection; pageId?: number }) {
  const content = (section.content ?? {}) as Record<string, unknown>;

  const source        = (str(content, "source") || "room_types").toLowerCase();
  const title         = str(content, "title");
  const subtitle      = str(content, "subtitle");
  const reserveLabel  = str(content, "reserve_label", "Rezervasyon");
  const reserveTarget = str(content, "reserve_target", "/rezervasyon");
  const emptyText     = str(content, "empty_text");
  const itemTemplate  = str(content, "item_template");

  const columnsRaw = parseInt(str(content, "columns", ""), 10);
  const columns    = columnsRaw >= 1 && columnsRaw <= 4 ? columnsRaw : null;
  const limitRaw   = parseInt(str(content, "limit", ""), 10);
  const limit      = limitRaw >= 1 && limitRaw <= 60 ? limitRaw : null;

  let items: ListingItem[] = [];
  if (source === "articles" || source === "yazilar" || source === "yazılar") {
    const res = await getArticles({ pageId, limit: limit ?? 12 });
    items = fromArticles(res.data ?? []);
  } else if (source === "pages" || source === "alt-sayfalar" || source === "sayfalar") {
    items = pageId ? fromPages(await getChildPages(pageId)) : [];
  } else {
    items = fromRoomTypes(await getRoomTypes(), reserveLabel, reserveTarget);
  }

  if (limit) items = items.slice(0, limit);

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
              <h2 style={{ fontSize: "2rem", fontWeight: 700, color: "var(--color-heading, #111827)", margin: 0 }}>{title}</h2>
            )}
          </div>
        )}

        {items.length === 0 ? (
          <p style={{ textAlign: "center", color: "var(--color-text-soft, #9ca3af)" }}>
            {emptyText || "İçerik yakında eklenecek."}
          </p>
        ) : (
          <div style={{ display: "grid", gridTemplateColumns: gridTemplate, gap: "1.5rem" }}>
            {items.map((item) =>
              itemTemplate ? (
                <div key={item.key} dangerouslySetInnerHTML={{ __html: renderItemTemplate(itemTemplate, item.vars) }} />
              ) : (
                <DefaultCard key={item.key} item={item} />
              ),
            )}
          </div>
        )}
      </div>
    </section>
  );
}
