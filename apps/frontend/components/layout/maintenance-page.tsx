/**
 * MaintenancePage — tenant sitesi bakım/yakında modundayken public ziyaretçilere
 * gösterilen basit tam-ekran sayfa. Tema token'larıyla (:root) uyumlu; altta
 * iletişim bilgileri (varsa). Gizli bypass linki olanlar bu sayfayı görmez.
 */
import type { SettingsPayload, SitePayload } from "@/lib/types";

export function MaintenancePage({
  site,
  settings,
}: {
  site: SitePayload["site"];
  settings: SettingsPayload["settings"];
}) {
  const title    = site.maintenance_title || "Çok Yakında";
  const message  = site.maintenance_message || "Sitemiz şu anda hazırlanıyor. Çok yakında sizlerle olacağız.";
  const siteName = settings.site_title || site.name;
  const logo     = settings.logo_url || null;

  const phone   = settings.contact?.phone || null;
  const email   = settings.contact?.email || null;
  const address = settings.contact?.address || null;
  const hasContact = Boolean(phone || email || address);

  return (
    <main
      style={{
        minHeight: "100vh",
        display: "flex",
        flexDirection: "column",
        alignItems: "center",
        justifyContent: "center",
        padding: "2rem 1.5rem",
        textAlign: "center",
        background: "var(--color-bg, #faf7f2)",
        color: "var(--color-text, #374151)",
      }}
    >
      <div
        style={{
          flex: 1,
          display: "flex",
          flexDirection: "column",
          alignItems: "center",
          justifyContent: "center",
          gap: "1.25rem",
          maxWidth: "560px",
        }}
      >
        {logo ? (
          // eslint-disable-next-line @next/next/no-img-element
          <img src={logo} alt={siteName} style={{ maxHeight: "64px", maxWidth: "240px", objectFit: "contain" }} />
        ) : (
          <div style={{ fontSize: "1.5rem", fontWeight: 700, color: "var(--color-heading, #111827)" }}>
            {siteName}
          </div>
        )}

        <h1
          style={{
            fontSize: "clamp(1.9rem, 6vw, 2.9rem)",
            fontWeight: 700,
            lineHeight: 1.15,
            color: "var(--color-heading, #111827)",
            margin: 0,
          }}
        >
          {title}
        </h1>

        <p style={{ fontSize: "1.05rem", lineHeight: 1.65, color: "var(--color-text-soft, #6b7280)", margin: 0 }}>
          {message}
        </p>
      </div>

      {hasContact && (
        <footer
          style={{
            borderTop: "1px solid var(--color-border, #e5e7eb)",
            paddingTop: "1.25rem",
            marginTop: "2.5rem",
            width: "100%",
            maxWidth: "560px",
            display: "flex",
            flexWrap: "wrap",
            justifyContent: "center",
            gap: "0.75rem 1.75rem",
            fontSize: "0.92rem",
            color: "var(--color-text-soft, #6b7280)",
          }}
        >
          {phone && (
            <a href={`tel:${phone.replace(/\s+/g, "")}`} style={{ color: "inherit", textDecoration: "none" }}>
              📞 {phone}
            </a>
          )}
          {email && (
            <a href={`mailto:${email}`} style={{ color: "inherit", textDecoration: "none" }}>
              ✉️ {email}
            </a>
          )}
          {address && <span>📍 {address}</span>}
        </footer>
      )}
    </main>
  );
}
