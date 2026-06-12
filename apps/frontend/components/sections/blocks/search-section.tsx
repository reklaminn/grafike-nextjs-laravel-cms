"use client";

/**
 * Site içi arama bölümü — type: "search".
 *
 * CMS'in /api/v1/search endpoint'ini kullanır (yayında sayfa + yazılar,
 * başlık öncelikli sıralama). Form gönderimleriyle aynı şekilde same-origin
 * relative fetch yapar (Traefik /api'yi Laravel'e yönlendirir).
 *
 * İçerik alanları (schema, hepsi opsiyonel):
 *   title       — bölüm başlığı
 *   placeholder — input placeholder (yoksa locale varsayılanı)
 */
import { useState } from "react";
import type { SectionBlockProps } from "@/lib/sections/component-registry";
import { str } from "@/lib/sections/component-registry";
import { uiStrings } from "@/lib/ui-strings";

type SearchResult = {
  type: "page" | "article";
  title: string;
  slug: string;
  url_path: string;
  excerpt?: string | null;
};

export function SearchSection({ section, lang }: SectionBlockProps) {
  const c = (section.content ?? {}) as Record<string, unknown>;
  const locale = lang || "tr";
  const t = uiStrings(locale);

  const title       = str(c, "title");
  const placeholder = str(c, "placeholder") || t.searchPlaceholder;

  const [query, setQuery]     = useState("");
  const [results, setResults] = useState<SearchResult[] | null>(null);
  const [loading, setLoading] = useState(false);
  const [message, setMessage] = useState<string | null>(null);

  async function runSearch(e: React.FormEvent) {
    e.preventDefault();

    const q = query.trim();
    if (q.length < 2) {
      setMessage(t.searchMinChars);
      setResults(null);
      return;
    }

    setLoading(true);
    setMessage(null);

    try {
      const response = await fetch(`/api/v1/search?q=${encodeURIComponent(q)}`, {
        headers: { Accept: "application/json" },
      });
      const data = await response.json();

      const found: SearchResult[] = Array.isArray(data?.results) ? data.results : [];
      setResults(found);
      if (found.length === 0) setMessage(t.searchNoResults(q));
    } catch {
      setResults(null);
      setMessage(t.searchError);
    } finally {
      setLoading(false);
    }
  }

  return (
    <section className="search-section" style={{ padding: "4rem 1.5rem", background: "var(--color-bg, #fff)" }}>
      <div style={{ maxWidth: "720px", margin: "0 auto" }}>
        {title && (
          <h2
            style={{
              fontSize: "1.75rem",
              fontWeight: 700,
              textAlign: "center",
              marginBottom: "1.5rem",
              color: "var(--color-heading, #111827)",
            }}
          >
            {title}
          </h2>
        )}

        <form onSubmit={runSearch} style={{ display: "flex", gap: ".6rem" }}>
          <input
            type="search"
            value={query}
            onChange={(e) => setQuery(e.target.value)}
            placeholder={placeholder}
            aria-label={placeholder}
            style={{
              flex: 1,
              padding: ".75rem 1rem",
              border: "1px solid var(--color-border, #d1d5db)",
              borderRadius: "var(--radius-button, .5rem)",
              fontSize: "1rem",
              background: "var(--color-surface, #fff)",
              color: "var(--color-text, #111827)",
            }}
          />
          <button
            type="submit"
            disabled={loading}
            style={{
              padding: ".75rem 1.5rem",
              background: "var(--color-primary, #6366f1)",
              color: "#fff",
              border: "none",
              borderRadius: "var(--radius-button, .5rem)",
              fontWeight: 600,
              fontSize: ".95rem",
              cursor: loading ? "wait" : "pointer",
              opacity: loading ? 0.7 : 1,
              whiteSpace: "nowrap",
            }}
          >
            {loading ? "…" : t.searchButton}
          </button>
        </form>

        {message && (
          <p style={{ marginTop: "1.25rem", textAlign: "center", color: "var(--color-text-soft, #6b7280)", fontSize: ".9rem" }}>
            {message}
          </p>
        )}

        {results && results.length > 0 && (
          <ul style={{ listStyle: "none", margin: "1.75rem 0 0", padding: 0, display: "grid", gap: ".75rem" }}>
            {results.map((result) => (
              <li key={`${result.type}-${result.slug}`}>
                <a
                  href={`/${locale}${result.url_path}`}
                  style={{
                    display: "block",
                    padding: "1rem 1.25rem",
                    background: "var(--color-surface, #f9fafb)",
                    border: "1px solid var(--color-border, #e5e7eb)",
                    borderRadius: "var(--radius-card, .6rem)",
                    textDecoration: "none",
                  }}
                >
                  <span style={{ display: "block", fontWeight: 600, color: "var(--color-heading, #111827)" }}>
                    {result.title}
                  </span>
                  {result.excerpt && (
                    <span style={{ display: "block", marginTop: ".25rem", fontSize: ".85rem", color: "var(--color-text-soft, #6b7280)" }}>
                      {result.excerpt}
                    </span>
                  )}
                  <span style={{ display: "block", marginTop: ".35rem", fontSize: ".75rem", color: "var(--color-primary, #6366f1)" }}>
                    {result.url_path}
                  </span>
                </a>
              </li>
            ))}
          </ul>
        )}
      </div>
    </section>
  );
}
