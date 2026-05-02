import type { Metadata } from "next";
import { notFound } from "next/navigation";
import { CmsPageContent } from "@/components/pages/cms-page-content";
import { getMenusPayload, getPagePayload, getSettingsPayload, getSitePayload } from "@/lib/api/client";
import { buildMetadata, canonicalUrl } from "@/lib/seo";

type LocaleRootProps = {
  params: Promise<{ locale: string }>;
  searchParams?: Promise<Record<string, string | string[] | undefined>>;
};

export async function generateMetadata({ params }: LocaleRootProps): Promise<Metadata> {
  const { locale } = await params;
  const [sitePayload, settingsPayload, payload] = await Promise.all([
    getSitePayload(locale),
    getSettingsPayload(),
    getPagePayload("home", locale),
  ]);

  if (!payload?.seo) {
    return {};
  }

  const siteName = settingsPayload.settings.site_title || sitePayload.site.name;
  const ogLocale = sitePayload.site.locale ?? `${locale}_${locale.toUpperCase()}`;

  return buildMetadata({
    title: payload.seo.title,
    description: payload.seo.description,
    siteName,
    canonical: payload.seo.canonical || canonicalUrl(`/${locale}`),
    noindex: payload.seo.noindex ?? false,
    ogType: (payload.seo.og_type as "website" | "article") ?? "website",
    ogLocale,
    ogImage: payload.seo.og_image ?? null,
    googleVerification: settingsPayload.settings.services?.google_site_verification,
    bingVerification: settingsPayload.settings.services?.bing_site_verification,
  });
}

/** /{locale} renders the CMS-selected homepage without forcing /{locale}/home. */
export default async function LocaleRootPage({ params }: LocaleRootProps) {
  const { locale } = await params;
  const [sitePayload, settingsPayload, menusPayload, payload] = await Promise.all([
    getSitePayload(locale),
    getSettingsPayload(),
    getMenusPayload(),
    getPagePayload("home", locale),
  ]);

  if (!payload?.page) {
    notFound();
  }

  return (
    <CmsPageContent
      payload={payload}
      sitePayload={sitePayload}
      settingsPayload={settingsPayload}
      menusPayload={menusPayload}
      slug="home"
      locale={locale}
    />
  );
}
