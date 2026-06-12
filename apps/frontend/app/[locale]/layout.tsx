/**
 * Locale layout — wraps every page under /{locale}/...
 *
 * Responsibilities:
 *  - Fetch site payload for the requested locale
 *  - Inject theme CSS assets + design tokens into <head>
 *  - Emit global JSON-LD: Organization + WebSite schemas
 *  - Generate default title template, full OG, favicon, verification metadata
 *  - Wrap content in <SiteShell>
 *  - Load theme JS assets after interactive
 */
import type { Metadata } from "next";
import { headers } from "next/headers";
import { SiteShell } from "@/components/layout/site-shell";
import { ThemeScripts } from "@/components/layout/theme-scripts";
import { getSitePayload, getSettingsPayload } from "@/lib/api/client";
import { buildTokenStyle } from "@/lib/theme/tokens";
import { buildFaviconMetadata, buildJsonLd, canonicalUrl } from "@/lib/seo";
import { uiStrings } from "@/lib/ui-strings";

type LocaleLayoutProps = {
  children: React.ReactNode;
  params: Promise<{ locale: string }>;
};

function normalizeTenantPreviewId(tenantId: string | null): string | null {
  const tenant = tenantId?.trim();

  return tenant && /^[a-zA-Z0-9_-]+$/.test(tenant) ? tenant : null;
}

function addTenantPreviewToAssetUrl(src: string, tenantId: string | null): string {
  if (!tenantId) return src;

  try {
    const url = src.startsWith("http://") || src.startsWith("https://")
      ? new URL(src)
      : new URL(src, "https://asset.local");

    const isLegacyTenantAsset = url.pathname === "/tenancy/assets"
      || url.pathname.startsWith("/tenancy/assets/");
    const isTenantAsset = isLegacyTenantAsset
      || url.pathname === "/tenant-assets"
      || url.pathname.startsWith("/tenant-assets/");

    if (!isTenantAsset) {
      return src;
    }

    if (isLegacyTenantAsset) {
      url.pathname = url.pathname.replace(/^\/tenancy\/assets/, "/tenant-assets");
    }

    if (!url.searchParams.has("tenant")) {
      url.searchParams.set("tenant", tenantId);
    }

    return src.startsWith("http://") || src.startsWith("https://")
      ? url.toString()
      : `${url.pathname}${url.search}${url.hash}`;
  } catch {
    return src;
  }
}

// ─── Metadata ─────────────────────────────────────────────────────────────────

export async function generateMetadata({ params }: LocaleLayoutProps): Promise<Metadata> {
  const { locale } = await params;

  const [sitePayload, settingsPayload] = await Promise.all([
    getSitePayload(locale),
    getSettingsPayload(),
  ]);

  const site      = sitePayload.site;
  const settings  = settingsPayload.settings;
  const siteName  = settings.site_title || site.name;
  const logoUrl   = settings.logo_url   || null;
  const ogLocale  = site.locale ?? `${locale}_${locale.toUpperCase()}`;

  // Alternate locales for og:locale:alternate
  const availableLocales = site.available_locales ?? [];
  const ogLocaleAlternates = availableLocales
    .map((l) => l.locale)
    .filter((l) => l !== ogLocale);

  // Verification tags (from admin settings)
  const googleVerification = settings.services?.google_site_verification || undefined;
  const bingVerification   = settings.services?.bing_site_verification   || undefined;

  return {
    title: {
      default:  siteName,
      template: `%s | ${siteName}`,
    },
    description: "",
    ...(googleVerification || bingVerification
      ? {
          verification: {
            ...(googleVerification ? { google: googleVerification } : {}),
            ...(bingVerification   ? { other: { "msvalidate.01": bingVerification } } : {}),
          },
        }
      : {}),
    openGraph: {
      siteName,
      type:   "website",
      locale: ogLocale,
      ...(ogLocaleAlternates.length ? { alternateLocale: ogLocaleAlternates } : {}),
      ...(logoUrl
        ? { images: [{ url: logoUrl, width: 1200, height: 630, alt: siteName }] }
        : {}),
    },
    twitter: {
      card:        logoUrl ? "summary_large_image" : "summary",
      ...(logoUrl ? { images: [logoUrl] } : {}),
    },
    ...buildFaviconMetadata(settings.favicon_url),
  };
}

// ─── Layout ───────────────────────────────────────────────────────────────────

export default async function LocaleLayout({ children, params }: LocaleLayoutProps) {
  const { locale } = await params;

  const [sitePayload, settingsPayload] = await Promise.all([
    getSitePayload(locale),
    getSettingsPayload(),
  ]);

  const site            = sitePayload.site;
  const settings        = settingsPayload.settings;
  const tokenStyle      = buildTokenStyle(site.tokens);
  const requestHeaders  = await headers();
  const previewTenantId = normalizeTenantPreviewId(requestHeaders.get("x-tenant-id"));
  const themeCssAssets  = (site.theme.assets?.css ?? [])
    .map((href) => addTenantPreviewToAssetUrl(href, previewTenantId));
  const themeJsAssets   = (site.theme.assets?.js  ?? [])
    .map((src) => addTenantPreviewToAssetUrl(src, previewTenantId));

  const tokenCss = Object.entries(tokenStyle)
    .map(([prop, val]) => `${prop}: ${val}`)
    .join("; ");

  // ── Global JSON-LD: Organization + WebSite ────────────────────────────────
  const siteName  = settings.site_title || site.name;
  const siteUrl   = canonicalUrl("/");
  const biz       = settings.business ?? {};

  const organizationJsonLd: Record<string, unknown> = {
    "@context": "https://schema.org",
    "@type":    biz.type || "Organization",
    name:       biz.name || siteName,
    url:        siteUrl,
  };

  if (settings.logo_url)  organizationJsonLd.logo  = settings.logo_url;
  if (biz.telephone || settings.contact?.phone) {
    organizationJsonLd.telephone = biz.telephone || settings.contact.phone;
  }
  if (biz.email || settings.contact?.email) {
    organizationJsonLd.email = biz.email || settings.contact.email;
  }
  if (biz.address_street || settings.contact?.address) {
    organizationJsonLd.address = {
      "@type":          "PostalAddress",
      streetAddress:    biz.address_street    || settings.contact?.address || "",
      addressLocality:  biz.address_city      || "",
      postalCode:       biz.address_postal_code || "",
      addressCountry:   biz.address_country   || "TR",
    };
  }
  if (biz.geo_lat && biz.geo_lng) {
    organizationJsonLd.geo = {
      "@type":    "GeoCoordinates",
      latitude:   parseFloat(biz.geo_lat),
      longitude:  parseFloat(biz.geo_lng),
    };
  }

  const websiteJsonLd = {
    "@context": "https://schema.org",
    "@type":    "WebSite",
    name:       siteName,
    url:        siteUrl,
    inLanguage: locale,
    publisher:  { "@type": "Organization", name: siteName, url: siteUrl },
  };

  // Admin can supply a pre-built JSON-LD override (from /admin/settings/business)
  const customOrgJsonLd = biz.organization_json_ld
    ? (() => { try { return JSON.parse(biz.organization_json_ld!); } catch { return null; } })()
    : null;

  const globalJsonLd = [customOrgJsonLd ?? organizationJsonLd, websiteJsonLd];

  return (
    <>
      {/*
        React 19 / Next.js 15: <style> and <link rel="stylesheet"> rendered
        in any Server Component are automatically hoisted to the document <head>.
        Do NOT wrap them in a <head> element — nested layouts live inside <body>
        and an explicit <head> there causes a hydration error.
      */}

      {/* Design tokens — applied to :root for global cascade */}
      {tokenCss && <style>{`:root { ${tokenCss} }`}</style>}

      {/* Theme stylesheet bundle(s) */}
      {themeCssAssets.map((href) => (
        <link key={href} rel="stylesheet" href={href} />
      ))}

      {/* Global JSON-LD: Organization + WebSite (on every page) */}
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={buildJsonLd(globalJsonLd)}
      />

      {/* Önizleme modu banner'ı — editör draft içeriğe baktığını bilsin */}
      {previewTenantId && (
        <div
          style={{
            position: "sticky",
            top: 0,
            zIndex: 9999,
            background: "#4f46e5",
            color: "#fff",
            textAlign: "center",
            fontSize: ".8rem",
            fontWeight: 600,
            padding: ".45rem 1rem",
            letterSpacing: ".01em",
          }}
        >
          👁 {uiStrings(locale).previewBanner(previewTenantId)}
        </div>
      )}

      <SiteShell availableLocales={site.available_locales ?? []}>{children}</SiteShell>

      {/*
        Theme JS — loaded sequentially with DOMContentLoaded polyfill.
        ThemeScripts ensures:
          1. Bootstrap loads fully before main.js runs (sequential, not parallel)
          2. Scripts that use addEventListener('DOMContentLoaded', …) still fire
             even though the event has already passed by the time afterInteractive runs.
      */}
      <ThemeScripts scripts={themeJsAssets} />
    </>
  );
}
