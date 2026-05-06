import { RegionLayoutRenderer } from "@/components/sections/region-layout-renderer";
import { SectionRenderer } from "@/components/sections/section-renderer";
import type { MenusPayload, PagePayload, SettingsPayload, SitePayload } from "@/lib/types";
import { getRenderableSections } from "@/lib/sections/region-sections";
import { buildJsonLd, canonicalUrl } from "@/lib/seo";

type CmsPageContentProps = {
  payload: PagePayload;
  sitePayload: SitePayload;
  settingsPayload: SettingsPayload;
  menusPayload: MenusPayload;
  slug: string;
  locale: string;
};

export function CmsPageContent({
  payload,
  sitePayload,
  settingsPayload,
  menusPayload,
  slug,
  locale,
}: CmsPageContentProps) {
  const pageId = payload.page.id;
  const jsonLdData = payload.seo?.structured_data ?? null;

  const crumbs = payload.page.breadcrumbs ?? [];
  const breadcrumbJsonLd =
    crumbs.length > 1
      ? {
          "@context": "https://schema.org",
          "@type": "BreadcrumbList",
          itemListElement: crumbs.map((c, i) => ({
            "@type": "ListItem",
            position: i + 1,
            name: c.title,
            item: canonicalUrl(c.url),
          })),
        }
      : null;

  const isHomepage = slug === "home";
  const websiteJsonLd = isHomepage
    ? {
        "@context": "https://schema.org",
        "@type": "WebSite",
        name: settingsPayload.settings.site_title || sitePayload.site.name,
        url: canonicalUrl("/"),
        inLanguage: locale,
        potentialAction: {
          "@type": "SearchAction",
          target: {
            "@type": "EntryPoint",
            urlTemplate: canonicalUrl(`/${locale}/search?q={search_term_string}`),
          },
          "query-input": "required name=search_term_string",
        },
      }
    : null;

  const allJsonLd = [jsonLdData, breadcrumbJsonLd, websiteJsonLd].filter(Boolean) as object[];

  const memberOnlyBanner = payload.page.has_member_only_content ? (
    <div
      style={{
        margin:       "1.5rem auto",
        maxWidth:     "680px",
        padding:      "1.25rem 1.5rem",
        background:   "var(--color-surface, #f9fafb)",
        border:       "1px solid var(--color-border, #e5e7eb)",
        borderLeft:   "4px solid var(--color-primary, #6366f1)",
        borderRadius: "0.5rem",
        display:      "flex",
        alignItems:   "center",
        gap:          "1rem",
        flexWrap:     "wrap",
      }}
    >
      <span style={{ fontSize: "1.5rem" }}>🔒</span>
      <div style={{ flex: 1 }}>
        <p style={{ fontWeight: 600, color: "var(--color-heading, #111827)", marginBottom: "0.25rem" }}>
          Üyelere özel içerik
        </p>
        <p style={{ fontSize: "0.875rem", color: "var(--color-text-soft, #6b7280)" }}>
          Bu sayfanın bir kısmı sadece kayıtlı üyelere açıktır.
        </p>
      </div>
      <a
        href={`/${locale}/member/login`}
        style={{
          padding:        "0.5rem 1.25rem",
          background:     "var(--color-primary, #6366f1)",
          color:          "#fff",
          borderRadius:   "0.4rem",
          fontWeight:     600,
          fontSize:       "0.875rem",
          textDecoration: "none",
          whiteSpace:     "nowrap",
        }}
      >
        Giriş Yap
      </a>
    </div>
  ) : null;

  if (payload.page.regions) {
    return (
      <>
        {allJsonLd.length > 0 && (
          <script type="application/ld+json" dangerouslySetInnerHTML={buildJsonLd(allJsonLd)} />
        )}
        <main className="page-stack">
          <RegionLayoutRenderer
            regions={payload.page.regions}
            site={sitePayload.site}
            settings={settingsPayload.settings}
            menus={menusPayload}
            pageId={pageId}
            lang={locale}
          />
          {memberOnlyBanner}
        </main>
      </>
    );
  }

  const sections = getRenderableSections(payload.page.sections, payload.page.regions);

  return (
    <>
      {allJsonLd.length > 0 && (
        <script type="application/ld+json" dangerouslySetInnerHTML={buildJsonLd(allJsonLd)} />
      )}
      <main className="container page-stack">
        {sections.map((section) => (
          <SectionRenderer
            key={section.id}
            section={section}
            site={sitePayload.site}
            settings={settingsPayload.settings}
            menus={menusPayload}
            pageId={pageId}
            lang={locale}
          />
        ))}
        {memberOnlyBanner}
      </main>
    </>
  );
}
