import type { Metadata } from "next";
import { notFound } from "next/navigation";
import Link from "next/link";
import Image from "next/image";
import { CmsPageContent } from "@/components/pages/cms-page-content";
import { ArticleBlockRenderer } from "@/components/articles/article-block-renderer";
import {
  getArticle,
  getMenusPayload,
  getPagePayload,
  getSettingsPayload,
  getSitePayload,
} from "@/lib/api/client";
import { buildMetadata, canonicalUrl } from "@/lib/seo";

type CatchAllPageProps = {
  params: Promise<{ locale: string; slug?: string[] }>;
};

// ─── Metadata ─────────────────────────────────────────────────────────────────

export async function generateMetadata({ params }: CatchAllPageProps): Promise<Metadata> {
  const resolvedParams = await params;
  const locale   = resolvedParams.locale;
  const segments = resolvedParams.slug ?? [];
  const slug     = segments.join("/") || "home";

  const sitePayload      = await getSitePayload(locale);
  const availableLocales = sitePayload.site.available_locales ?? [];

  // Hreflang: prefer DB-stored tags; fall back to URL-convention from available_locales
  const localeHreflang = Object.fromEntries(
    availableLocales.map((l) => [l.locale, canonicalUrl(`/${l.code}/${slug}`)]),
  );

  const [siteSettings] = await Promise.all([getSettingsPayload()]);
  const googleVerification = siteSettings.settings.services?.google_site_verification;
  const bingVerification   = siteSettings.settings.services?.bing_site_verification;
  const siteName           = siteSettings.settings.site_title || sitePayload.site.name;
  const ogLocale           = sitePayload.site.locale ?? `${locale}_${locale.toUpperCase()}`;

  // ── Try as Page ──────────────────────────────────────────────────────────
  const payload = await getPagePayload(slug, locale);
  if (payload?.seo) {
    const hreflang =
      payload.seo.hreflang_tags && Object.keys(payload.seo.hreflang_tags).length > 0
        ? payload.seo.hreflang_tags
        : localeHreflang;

    return buildMetadata({
      title:       payload.seo.title,
      description: payload.seo.description,
      siteName,
      canonical:   payload.seo.canonical || canonicalUrl(`/${locale}/${slug}`),
      noindex:     payload.seo.noindex ?? false,
      ogType:      (payload.seo.og_type as "website" | "article") ?? "website",
      ogLocale,
      ogImage:     payload.seo.og_image ?? null,
      hreflang,
      googleVerification,
      bingVerification,
    });
  }

  // ── Try as Article ───────────────────────────────────────────────────────
  const lastSegment = segments[segments.length - 1];
  if (lastSegment) {
    const detail = await getArticle(lastSegment, locale);
    if (detail) {
      const hreflang =
        detail.seo?.hreflang_tags && Object.keys(detail.seo.hreflang_tags).length > 0
          ? detail.seo.hreflang_tags
          : localeHreflang;

      return buildMetadata({
        title:         detail.seo?.title       ?? detail.article.title,
        description:   detail.seo?.description ?? detail.article.excerpt ?? "",
        siteName,
        canonical:     detail.seo?.canonical   ?? canonicalUrl(`/${locale}/${segments.join("/")}`),
        noindex:       detail.seo?.noindex     ?? false,
        ogType:        "article",
        ogLocale,
        ogImage:       detail.seo?.og_image    ?? detail.article.cover?.url ?? null,
        ogImageAlt:    detail.article.title,
        publishedTime: detail.article.published_at,
        modifiedTime:  detail.article.updated_at ?? null,
        authorName:    detail.author?.name ?? null,
        hreflang,
        googleVerification,
        bingVerification,
      });
    }
  }

  return {};
}

// ─── Page ─────────────────────────────────────────────────────────────────────

export default async function CatchAllPage({ params }: CatchAllPageProps) {
  const resolvedParams = await params;
  const locale   = resolvedParams.locale;
  const segments = resolvedParams.slug ?? [];
  const slug     = segments.join("/") || "home";

  const [sitePayload, settingsPayload, menusPayload] = await Promise.all([
    getSitePayload(locale),
    getSettingsPayload(),
    getMenusPayload(),
  ]);

  // ── 1. Try as a Page ──────────────────────────────────────────────────
  const payload = await getPagePayload(slug, locale);

  if (payload?.page) {
    return (
      <CmsPageContent
        payload={payload}
        sitePayload={sitePayload}
        settingsPayload={settingsPayload}
        menusPayload={menusPayload}
        slug={slug}
        locale={locale}
      />
    );
  }

  // ── 2. Try as Article detail (last path segment = article slug) ───────
  const articleSlug = segments[segments.length - 1];
  if (!articleSlug) notFound();

  const detail = await getArticle(articleSlug, locale);
  if (!detail) notFound();

  const { article, author, page: articlePage } = detail;

  // Parent page slug for "back to list" link
  const parentSlug =
    articlePage?.slug ?? (segments.length > 1 ? segments.slice(0, -1).join("/") : null);

  // Locale-prefixed URL helper
  const localeHref = (path: string) => `/${locale}/${path}`;

  return (
    <main className="container" style={{ maxWidth: "780px", margin: "0 auto", padding: "2rem 1rem" }}>
      {/* Breadcrumb */}
      {parentSlug && (
        <nav style={{ marginBottom: "1.5rem", fontSize: "0.85rem", color: "var(--color-text-soft, #6b7280)" }}>
          <Link href={localeHref("home")} style={{ color: "inherit", textDecoration: "none" }}>
            Ana Sayfa
          </Link>
          {" / "}
          <Link href={localeHref(parentSlug)} style={{ color: "inherit", textDecoration: "none" }}>
            {articlePage?.title ?? parentSlug}
          </Link>
          {" / "}
          <span>{article.title}</span>
        </nav>
      )}

      {/* Cover image */}
      {article.cover?.url && (
        <div
          style={{
            marginBottom: "2rem",
            borderRadius: "0.75rem",
            overflow: "hidden",
            position: "relative",
            aspectRatio: "16/9",
          }}
        >
          <Image
            src={article.cover.url}
            alt={article.cover.alt ?? article.title}
            fill
            priority
            sizes="(max-width: 780px) 100vw, 780px"
            style={{ objectFit: "cover" }}
          />
        </div>
      )}

      {/* Header */}
      <header style={{ marginBottom: "2rem" }}>
        <h1 style={{ fontSize: "2rem", fontWeight: 700, lineHeight: 1.25, marginBottom: "1rem" }}>
          {article.title}
        </h1>

        <div
          style={{
            display: "flex",
            gap: "1rem",
            alignItems: "center",
            fontSize: "0.875rem",
            color: "var(--color-text-soft, #6b7280)",
            flexWrap: "wrap",
          }}
        >
          {author?.name && (
            <span>
              <strong style={{ color: "var(--color-heading, inherit)" }}>{author.name}</strong>
            </span>
          )}
          {article.display_date && (
            <time dateTime={article.display_date}>
              {new Date(article.display_date).toLocaleDateString(`${locale}-${locale.toUpperCase()}`, {
                day:   "numeric",
                month: "long",
                year:  "numeric",
              })}
            </time>
          )}
        </div>

        {article.excerpt && (
          <p
            style={{
              marginTop:   "1rem",
              fontSize:    "1.1rem",
              color:       "var(--color-text-soft, #374151)",
              lineHeight:  1.6,
              fontStyle:   "italic",
              borderLeft:  "3px solid var(--color-primary, #6366f1)",
              paddingLeft: "1rem",
            }}
          >
            {article.excerpt}
          </p>
        )}
      </header>

      {/* Content — prefer content_json blocks, fall back to body HTML */}
      {article.content_json && article.content_json.length > 0 ? (
        <ArticleBlockRenderer blocks={article.content_json} />
      ) : article.body ? (
        <div
          className="article-content"
          // biome-ignore lint/security/noDangerouslySetInnerHtml: CMS-controlled body HTML
          dangerouslySetInnerHTML={{ __html: article.body }}
          style={{ lineHeight: 1.7 }}
        />
      ) : null}

      {/* Back link */}
      {parentSlug && (
        <div
          style={{ marginTop: "3rem", paddingTop: "2rem", borderTop: "1px solid var(--color-border, #e5e7eb)" }}
        >
          <Link
            href={localeHref(parentSlug)}
            style={{
              color:          "var(--color-primary, #6366f1)",
              fontWeight:     600,
              textDecoration: "none",
              fontSize:       "0.9rem",
            }}
          >
            ← {articlePage?.title ?? "Listeye dön"}
          </Link>
        </div>
      )}
    </main>
  );
}
