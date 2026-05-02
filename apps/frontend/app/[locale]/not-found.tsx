import { CmsPageContent } from "@/components/pages/cms-page-content";
import { getMenusPayload, getPagePayload, getSettingsPayload, getSitePayload } from "@/lib/api/client";

export default async function NotFound() {
  const locale = "tr";
  const [sitePayload, settingsPayload, menusPayload, payload] = await Promise.all([
    getSitePayload(locale),
    getSettingsPayload(),
    getMenusPayload(),
    getPagePayload("404", locale),
  ]);

  if (payload?.page) {
    return (
      <CmsPageContent
        payload={payload}
        sitePayload={sitePayload}
        settingsPayload={settingsPayload}
        menusPayload={menusPayload}
        slug="404"
        locale={locale}
      />
    );
  }

  return (
    <main className="container page-stack" style={{ padding: "4rem 1rem" }}>
      <h1>Sayfa bulunamadı</h1>
      <p>Aradığınız sayfa mevcut değil.</p>
    </main>
  );
}
