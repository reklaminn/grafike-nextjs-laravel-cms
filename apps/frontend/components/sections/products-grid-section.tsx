/**
 * ProductsGridSection — Estetik Dermal ürün kataloğu (kategori-filtreli).
 *
 * Sunucu bileşeni: `urunler` sayfasına bağlı tüm ürün Article'larını çeker (perPage 50 tavanı
 * nedeniyle 2 sayfa: page 1 + page 2 → ≤100), mevcut kategorileri kanonik sırayla süzer ve
 * istemci-tarafı sekme/filtre bileşenine (ProductsGridClient) devreder. Kategori değişimi
 * tarayıcıda (network yok). Kart tasarımı article-list-section ile aynı token/stil.
 *
 * section-renderer.tsx `type === "products-grid"` için doğrudan bunu çağırır (registry'siz),
 * tıpkı article-list / form gibi.
 */
import { getArticles } from "@/lib/api/client";
import type { ArticleListItem, PageSection } from "@/lib/types";
import { ProductsGridClient } from "@/components/sections/products-grid-client";

type ProductsGridSectionProps = {
  section: PageSection;
  pageId?: number;
  lang?: string;
};

// Kaynak sitedeki 14 kategori — sekme sırası bu düzeni izler (boş olanlar gösterilmez).
const CATEGORY_ORDER = [
  "İp", "Kanül & İğne Ucu", "Kimyasal Peeling", "Kozmetik", "Kremler",
  "Mezoterapi", "Mezoterapi Tabancası", "Micro İğneleme", "Otolog Rejeneratif Terapi",
  "Peeling", "Profesyonel Ürünler", "RRS", "Terapi", "Yüz Maskesi",
];

// 5 distribütör markası (gerçek WooCommerce product_brand taksonomisi — bkz. harvest.php).
const BRAND_ORDER = ["Skin Tech", "Seffiline", "Grand Aespio", "Woorhi", "Mi Medical"];

export async function ProductsGridSection({ section, pageId, lang }: ProductsGridSectionProps) {
  const content = (section.content ?? {}) as Record<string, unknown>;
  const title = typeof content.title === "string" && content.title ? content.title : "Tüm Ürünler";
  const subtitle = typeof content.subtitle === "string" ? content.subtitle : "";

  // Tüm ürünleri çek (perPage 50 tavanı → 2 sayfa).
  const first = await getArticles({ pageId, lang, limit: 50, page: 1 });
  let items: ArticleListItem[] = first.data ?? [];
  if ((first.meta?.last_page ?? 1) >= 2) {
    const second = await getArticles({ pageId, lang, limit: 50, page: 2 });
    items = items.concat(second.data ?? []);
  }

  // Mevcut kategorileri kanonik sırayla süz (boş kategori sekmesi olmasın).
  const presentCategories = new Set(items.map((a) => a.category).filter(Boolean) as string[]);
  const categories = CATEGORY_ORDER.filter((c) => presentCategories.has(c));
  for (const c of presentCategories) if (!categories.includes(c)) categories.push(c);

  // Mevcut markaları kanonik sırayla süz.
  const presentBrands = new Set(items.map((a) => a.brand).filter(Boolean) as string[]);
  const brands = BRAND_ORDER.filter((b) => presentBrands.has(b));
  for (const b of presentBrands) if (!brands.includes(b)) brands.push(b);

  return (
    <section style={{ padding: "clamp(40px,6vw,80px) clamp(16px,4vw,32px)", background: "var(--color-secondary,#FBF4EE)" }}>
      <div style={{ maxWidth: "1240px", margin: "0 auto" }}>
        {(title || subtitle) && (
          <div style={{ textAlign: "center", marginBottom: "clamp(24px,4vw,40px)" }}>
            {subtitle && (
              <div style={{ fontSize: "13px", fontWeight: 700, letterSpacing: "1px", textTransform: "uppercase", color: "var(--color-primary,#E8702A)", marginBottom: "10px" }}>
                {subtitle}
              </div>
            )}
            <h2 style={{ margin: 0, fontSize: "clamp(1.8rem,4vw,2.6rem)", fontWeight: 800, color: "var(--text-main,#2a2a2a)" }}>
              {title}
            </h2>
          </div>
        )}

        <ProductsGridClient items={items} categories={categories} brands={brands} pageSlug="urunler" />
      </div>
    </section>
  );
}
