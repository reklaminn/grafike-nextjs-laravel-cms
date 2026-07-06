import Link from "next/link";
import Image from "next/image";
import { getArticles } from "@/lib/api/client";
import { isLocalMediaPath } from "@/lib/sections/component-registry";
import type { ArticleListItem, PageSection } from "@/lib/types";

type ArticleListSectionProps = {
  section: PageSection;
  pageId?: number;
  lang?: string;
};

function formatDate(dateStr: string | null): string {
  if (!dateStr) return "";
  return new Date(dateStr).toLocaleDateString("tr-TR", {
    day: "numeric",
    month: "long",
    year: "numeric",
  });
}

function ArticleCard({ article, pageSlug }: { article: ArticleListItem; pageSlug?: string | null }) {
  const href = pageSlug ? `/${pageSlug}/${article.slug}` : `/${article.slug}`;

  return (
    <article
      style={{
        background: "var(--color-surface, #fff)",
        border: "1px solid var(--color-border, #e5e7eb)",
        borderRadius: "0.75rem",
        overflow: "hidden",
        display: "flex",
        flexDirection: "column",
        transition: "box-shadow 0.2s",
      }}
    >
      {article.cover?.url && (
        <Link href={href} style={{ display: "block", overflow: "hidden", position: "relative", aspectRatio: "16/9" }}>
          <Image
            src={article.cover.thumb ?? article.cover.url}
            alt={article.cover.alt ?? article.title}
            fill
            sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw"
            style={{ objectFit: "cover" }}
            unoptimized={isLocalMediaPath(article.cover.thumb ?? article.cover.url)}
          />
        </Link>
      )}

      <div style={{ padding: "1.25rem", display: "flex", flexDirection: "column", gap: "0.5rem", flex: 1 }}>
        {article.display_date && (
          <time
            dateTime={article.display_date}
            style={{ fontSize: "0.75rem", color: "var(--color-text-soft, #9ca3af)" }}
          >
            {formatDate(article.display_date)}
          </time>
        )}

        <h3 style={{ margin: 0, fontSize: "1.05rem", fontWeight: 600, lineHeight: 1.35 }}>
          <Link
            href={href}
            style={{ color: "var(--color-heading, inherit)", textDecoration: "none" }}
          >
            {article.title}
          </Link>
        </h3>

        {article.excerpt && (
          <p
            style={{
              margin: 0,
              fontSize: "0.875rem",
              color: "var(--color-text-soft, #6b7280)",
              lineHeight: 1.5,
              display: "-webkit-box",
              WebkitLineClamp: 3,
              WebkitBoxOrient: "vertical",
              overflow: "hidden",
            }}
          >
            {article.excerpt}
          </p>
        )}

        <div style={{ marginTop: "auto", paddingTop: "0.75rem" }}>
          <Link
            href={href}
            style={{
              fontSize: "0.8rem",
              fontWeight: 600,
              color: "var(--color-primary, #6366f1)",
              textDecoration: "none",
            }}
          >
            Devamını oku →
          </Link>
        </div>
      </div>
    </article>
  );
}

export async function ArticleListSection({ section, pageId, lang }: ArticleListSectionProps) {
  const content = section.content as {
    title?: string;
    description?: string;
    page_id?: number;
    limit?: number;
    featured_only?: boolean;
  };

  const resolvedPageId = content.page_id ?? pageId;

  const payload = await getArticles({
    pageId:      resolvedPageId,
    lang:        lang,
    featuredOnly: Boolean(content.featured_only),
    limit:       content.limit ?? 12,
  });

  const articles = payload.data ?? [];

  return (
    <section style={{ padding: "3rem 0" }}>
      {(content.title || content.description) && (
        <div style={{ marginBottom: "2rem", textAlign: "center" }}>
          {content.title && (
            <h2 style={{ margin: 0, fontSize: "1.75rem", fontWeight: 700 }}>
              {content.title}
            </h2>
          )}
          {content.description && (
            <p style={{ marginTop: "0.5rem", color: "var(--color-text-soft, #6b7280)", fontSize: "1rem" }}>
              {content.description}
            </p>
          )}
        </div>
      )}

      {articles.length === 0 ? (
        <p style={{ textAlign: "center", color: "var(--color-text-soft, #9ca3af)" }}>
          Henüz yazı yok.
        </p>
      ) : (
        <div
          style={{
            display: "grid",
            gridTemplateColumns: "repeat(auto-fill, minmax(280px, 1fr))",
            gap: "1.5rem",
          }}
        >
          {articles.map((article) => (
            <ArticleCard key={article.id} article={article} pageSlug={article.page?.slug} />
          ))}
        </div>
      )}
    </section>
  );
}
