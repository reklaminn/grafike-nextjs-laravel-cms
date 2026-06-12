import { headers } from "next/headers";
import { CmsPageContent } from "@/components/pages/cms-page-content";
import { getMenusPayload, getPagePayload, getSettingsPayload, getSitePayload } from "@/lib/api/client";
import { DEFAULT_LOCALE, isValidLocale } from "@/lib/i18n";
import { uiStrings } from "@/lib/ui-strings";

export default async function NotFound() {
  // middleware her yanıta x-locale damgalar; not-found segment params alamaz
  const h = await headers();
  const headerLocale = h.get("x-locale") ?? DEFAULT_LOCALE;
  const locale = isValidLocale(headerLocale) ? headerLocale : DEFAULT_LOCALE;
  const t = uiStrings(locale);

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
    <main className="container page-stack" style={{ padding: "4rem 1rem", textAlign: "center" }}>
      <h1>{t.notFoundTitle}</h1>
      <p>{t.notFoundBody}</p>
      <p style={{ marginTop: "1.5rem" }}>
        <a
          href={`/${locale}`}
          style={{
            display: "inline-block",
            padding: ".6rem 1.5rem",
            background: "var(--color-primary,#6366f1)",
            color: "#fff",
            borderRadius: ".4rem",
            fontWeight: 600,
            fontSize: ".875rem",
            textDecoration: "none",
          }}
        >
          {t.backHome}
        </a>
      </p>
    </main>
  );
}
