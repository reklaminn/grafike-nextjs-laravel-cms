/**
 * SEO helpers for Next.js metadata generation.
 *
 * Canonical URL base comes from NEXT_PUBLIC_SITE_URL env var.
 */

const SITE_URL = (process.env.NEXT_PUBLIC_SITE_URL?.trim() || "http://localhost:3000").replace(/\/$/, "");

export function canonicalUrl(path: string): string {
  const cleanPath = path.startsWith("/") ? path : `/${path}`;
  return `${SITE_URL}${cleanPath}`;
}

// ─── Types ────────────────────────────────────────────────────────────────────

export type SeoMeta = {
  title: string;
  description: string;
  siteName?: string;
  canonical?: string;
  noindex?: boolean;
  // Open Graph
  ogImage?: string | null;
  ogImageAlt?: string;
  ogType?: "website" | "article" | "product";
  ogLocale?: string;                          // e.g. "tr_TR"
  ogLocaleAlternates?: string[];              // e.g. ["en_US", "de_DE"]
  // Article-specific OG
  publishedTime?: string | null;
  modifiedTime?: string | null;
  authorName?: string | null;
  articleSection?: string | null;
  articleTags?: string[];
  // hreflang alternates
  hreflang?: Record<string, string>;
  // Verification (injected from settings)
  googleVerification?: string;
  bingVerification?: string;
};

// ─── buildMetadata ─────────────────────────────────────────────────────────────

export function buildMetadata(meta: SeoMeta) {
  const {
    title,
    description,
    siteName,
    canonical,
    noindex = false,
    ogImage,
    ogImageAlt,
    ogType = "website",
    ogLocale,
    ogLocaleAlternates,
    publishedTime,
    modifiedTime,
    authorName,
    articleSection,
    articleTags,
    hreflang,
    googleVerification,
    bingVerification,
  } = meta;

  const robots = noindex
    ? { index: false, follow: false, googleBot: { index: false, follow: false } }
    : { index: true,  follow: true,  googleBot: { index: true,  follow: true  } };

  // OG images — full descriptor for rich preview
  const ogImages = ogImage
    ? [{ url: ogImage, width: 1200, height: 630, alt: ogImageAlt ?? title }]
    : [];

  // Article-specific metadata
  const articleMeta =
    ogType === "article"
      ? {
          ...(publishedTime ? { publishedTime } : {}),
          ...(modifiedTime  ? { modifiedTime  } : {}),
          ...(authorName    ? { authors: [authorName] } : {}),
          ...(articleSection ? { section: articleSection } : {}),
          ...(articleTags?.length ? { tags: articleTags } : {}),
        }
      : {};

  // Verification
  const verification: Record<string, string | Record<string, string>> = {};
  if (googleVerification) verification.google = googleVerification;
  if (bingVerification)   verification.other  = { "msvalidate.01": bingVerification };

  return {
    title,
    description,
    robots,
    ...(Object.keys(verification).length > 0 ? { verification } : {}),
    alternates: {
      ...(canonical ? { canonical } : {}),
      ...(hreflang && Object.keys(hreflang).length > 0 ? { languages: hreflang } : {}),
    },
    openGraph: {
      title,
      description,
      type: ogType,
      url:  canonical,
      ...(siteName ? { siteName } : {}),
      ...(ogLocale ? { locale: ogLocale } : {}),
      ...(ogLocaleAlternates?.length ? { alternateLocale: ogLocaleAlternates } : {}),
      ...(ogImages.length ? { images: ogImages } : {}),
      ...articleMeta,
    },
    twitter: {
      card:        ogImage ? ("summary_large_image" as const) : ("summary" as const),
      title,
      description,
      ...(ogImage ? { images: [ogImage] } : {}),
    },
  };
}

// ─── buildFaviconMetadata ─────────────────────────────────────────────────────

/**
 * Generate Next.js icons metadata from a favicon URL.
 * Handles both SVG and raster (png/jpg/ico) favicon URLs.
 */
export function buildFaviconMetadata(faviconUrl?: string | null) {
  if (!faviconUrl) return {};

  const isSvg = faviconUrl.endsWith(".svg");

  return {
    icons: {
      icon: [
        { url: faviconUrl, type: isSvg ? "image/svg+xml" : "image/x-icon" },
        // For raster: also serve as 32×32 and 192×192
        ...(!isSvg
          ? [
              { url: faviconUrl, sizes: "32x32",   type: "image/png" },
              { url: faviconUrl, sizes: "192x192",  type: "image/png" },
            ]
          : []),
      ],
      apple: faviconUrl,   // apple-touch-icon
      shortcut: faviconUrl,
    },
  };
}

// ─── buildJsonLd ──────────────────────────────────────────────────────────────

/**
 * Build a JSON-LD <script> tag payload.
 * Returns `{ __html: string }` for dangerouslySetInnerHTML.
 * Pass an array to emit multiple JSON-LD blocks (joined with newlines).
 */
export function buildJsonLd(data: object | object[]): { __html: string } {
  const blocks = Array.isArray(data) ? data : [data];
  const parts  = blocks
    .filter((b) => b && typeof b === "object" && Object.keys(b).length > 0)
    .map((b) => JSON.stringify(b));
  return { __html: parts.join("\n") };
}
