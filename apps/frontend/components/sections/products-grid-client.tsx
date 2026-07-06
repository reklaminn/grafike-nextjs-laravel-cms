"use client";

/**
 * ProductsGridClient — kategori sekmeleri + istemci-tarafı filtre + ürün kart ızgarası.
 * Tüm ürünler sunucudan gelir (ProductsGridSection); sekme değişimi tarayıcıda anında filtreler
 * (network yok). Kart tasarımı article-list-section'daki ArticleCard ile aynı token/stil.
 */
import { useMemo, useState } from "react";
import Link from "next/link";
import Image from "next/image";
import { isLocalMediaPath } from "@/lib/sections/component-registry";
import type { ArticleListItem } from "@/lib/types";

const ALL = "__all__";

function ProductCard({ item, pageSlug }: { item: ArticleListItem; pageSlug: string }) {
  const href = `/${pageSlug}/${item.slug}`;
  return (
    <article
      style={{
        background: "var(--color-surface,#fff)",
        border: "1px solid var(--border-soft,#ece6df)",
        borderRadius: "14px",
        overflow: "hidden",
        display: "flex",
        flexDirection: "column",
      }}
    >
      <Link href={href} style={{ display: "block", position: "relative", aspectRatio: "1/1", background: "var(--color-secondary,#FBF4EE)" }}>
        {item.cover?.url && (
          <Image
            src={item.cover.thumb ?? item.cover.url}
            alt={item.cover.alt ?? item.title}
            fill
            sizes="(max-width:640px) 50vw, (max-width:1024px) 33vw, 25vw"
            style={{ objectFit: "cover" }}
            unoptimized={isLocalMediaPath(item.cover.thumb ?? item.cover.url)}
          />
        )}
      </Link>
      <div style={{ padding: "16px 16px 18px", display: "flex", flexDirection: "column", gap: "6px", flex: 1 }}>
        {item.category && (
          <span style={{ fontSize: "11px", fontWeight: 700, letterSpacing: ".5px", textTransform: "uppercase", color: "var(--color-primary,#E8702A)" }}>
            {item.category}
          </span>
        )}
        <h3 style={{ margin: 0, fontSize: "1rem", fontWeight: 700, lineHeight: 1.3 }}>
          <Link href={href} style={{ color: "var(--text-main,#2a2a2a)", textDecoration: "none" }}>
            {item.title}
          </Link>
        </h3>
        {item.excerpt && (
          <p style={{ margin: 0, fontSize: "0.82rem", color: "var(--color-text-soft,#6b7280)", lineHeight: 1.5, display: "-webkit-box", WebkitLineClamp: 2, WebkitBoxOrient: "vertical", overflow: "hidden" }}>
            {item.excerpt}
          </p>
        )}
        <div style={{ marginTop: "auto", paddingTop: "10px" }}>
          <Link href={href} style={{ fontSize: "0.8rem", fontWeight: 700, color: "var(--color-primary,#E8702A)", textDecoration: "none" }}>
            İncele →
          </Link>
        </div>
      </div>
    </article>
  );
}

function TabRow({
  tabs,
  active,
  onChange,
}: {
  tabs: Array<{ key: string; label: string }>;
  active: string;
  onChange: (key: string) => void;
}) {
  return (
    <div style={{ display: "flex", flexWrap: "wrap", gap: "10px", justifyContent: "center" }}>
      {tabs.map((t) => {
        const on = active === t.key;
        return (
          <button
            key={t.key}
            type="button"
            onClick={() => onChange(t.key)}
            style={{
              cursor: "pointer",
              border: `1.5px solid ${on ? "var(--color-primary,#E8702A)" : "var(--border-soft,#ece6df)"}`,
              background: on ? "var(--color-primary,#E8702A)" : "#fff",
              color: on ? "#fff" : "var(--text-main,#2a2a2a)",
              borderRadius: "999px",
              padding: "9px 18px",
              fontWeight: 600,
              fontSize: "14px",
              transition: "all .18s",
            }}
          >
            {t.label}
          </button>
        );
      })}
    </div>
  );
}

export function ProductsGridClient({
  items,
  categories,
  brands,
  pageSlug,
}: {
  items: ArticleListItem[];
  categories: string[];
  brands: string[];
  pageSlug: string;
}) {
  const [activeCategory, setActiveCategory] = useState<string>(ALL);
  const [activeBrand, setActiveBrand] = useState<string>(ALL);

  // İki facet AND mantığıyla birlikte filtrelenir (kategori + marka).
  const byCategory = useMemo(
    () => (activeCategory === ALL ? items : items.filter((i) => i.category === activeCategory)),
    [activeCategory, items],
  );
  const filtered = useMemo(
    () => (activeBrand === ALL ? byCategory : byCategory.filter((i) => i.brand === activeBrand)),
    [activeBrand, byCategory],
  );

  const categoryTabs = [{ key: ALL, label: `Tümü (${items.length})` }].concat(
    categories.map((c) => ({ key: c, label: `${c} (${items.filter((i) => i.category === c).length})` })),
  );
  const brandTabs = [{ key: ALL, label: `Tümü (${byCategory.length})` }].concat(
    brands.map((b) => ({ key: b, label: `${b} (${byCategory.filter((i) => i.brand === b).length})` })),
  );

  return (
    <div>
      {/* Kategori sekmeleri */}
      <div style={{ marginBottom: "16px" }}>
        <TabRow tabs={categoryTabs} active={activeCategory} onChange={setActiveCategory} />
      </div>

      {/* Marka sekmeleri (2. facet — kategoriyle AND'lenir) */}
      {brands.length > 0 && (
        <div style={{ marginBottom: "clamp(24px,4vw,36px)" }}>
          <TabRow tabs={brandTabs} active={activeBrand} onChange={setActiveBrand} />
        </div>
      )}

      {/* Kart ızgarası */}
      {filtered.length > 0 ? (
        <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fill,minmax(220px,1fr))", gap: "18px" }}>
          {filtered.map((item) => (
            <ProductCard key={item.id} item={item} pageSlug={pageSlug} />
          ))}
        </div>
      ) : (
        <p style={{ textAlign: "center", color: "var(--color-text-soft,#9ca3af)", padding: "40px 0" }}>
          Bu filtrede ürün bulunmuyor.
        </p>
      )}
    </div>
  );
}
