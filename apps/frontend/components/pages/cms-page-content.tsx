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
      </main>
    </>
  );
}
