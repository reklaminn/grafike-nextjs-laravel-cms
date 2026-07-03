/**
 * MaintenancePage — tenant sitesi bakım/yakında modundayken public ziyaretçilere
 * gösterilen basit tam-ekran sayfa. Tema token'larıyla (:root) uyumlu.
 *
 * İçerik: logo/ad + başlık + mesaj + (opsiyonel) yayın tarihine geri sayım +
 * (opsiyonel) sosyal linkler + altta iletişim bilgileri.
 * Gizli bypass linki olanlar bu sayfayı görmez.
 */
import { MaintenanceCountdown } from "@/components/layout/maintenance-countdown";
import type { SettingsPayload, SitePayload } from "@/lib/types";

const SOCIAL_META: Record<string, { label: string; path: string }> = {
  facebook:  { label: "Facebook",  path: "M24 12.07C24 5.4 18.63 0 12 0S0 5.4 0 12.07c0 6.02 4.39 11.01 10.13 11.93v-8.44H7.08v-3.49h3.05V9.41c0-3.02 1.79-4.69 4.53-4.69 1.31 0 2.68.24 2.68.24v2.97h-1.51c-1.49 0-1.96.93-1.96 1.89v2.25h3.33l-.53 3.49h-2.8V24C19.61 23.08 24 18.09 24 12.07Z" },
  instagram: { label: "Instagram", path: "M12 2.16c3.2 0 3.58.01 4.85.07 1.17.05 1.8.25 2.23.41.56.22.96.48 1.38.9.42.42.68.82.9 1.38.16.42.36 1.06.41 2.23.06 1.27.07 1.65.07 4.85s-.01 3.58-.07 4.85c-.05 1.17-.25 1.8-.41 2.23-.22.56-.48.96-.9 1.38-.42.42-.82.68-1.38.9-.42.16-1.06.36-2.23.41-1.27.06-1.65.07-4.85.07s-3.58-.01-4.85-.07c-1.17-.05-1.8-.25-2.23-.41a3.7 3.7 0 0 1-1.38-.9 3.7 3.7 0 0 1-.9-1.38c-.16-.42-.36-1.06-.41-2.23C2.17 15.58 2.16 15.2 2.16 12s.01-3.58.07-4.85c.05-1.17.25-1.8.41-2.23.22-.56.48-.96.9-1.38.42-.42.82-.68 1.38-.9.42-.16 1.06-.36 2.23-.41C8.42 2.17 8.8 2.16 12 2.16Zm0 3.68A6.16 6.16 0 1 0 18.16 12 6.16 6.16 0 0 0 12 5.84Zm0 10.16A4 4 0 1 1 16 12a4 4 0 0 1-4 4Zm6.41-10.4a1.44 1.44 0 1 0 1.44 1.44 1.44 1.44 0 0 0-1.44-1.44Z" },
  twitter:   { label: "X",         path: "M18.9 1.5h3.68l-8.04 9.19L24 22.5h-7.4l-5.8-7.58-6.63 7.58H.49l8.6-9.83L0 1.5h7.59l5.24 6.93ZM17.6 20.3h2.04L6.49 3.6H4.3Z" },
  x:         { label: "X",         path: "M18.9 1.5h3.68l-8.04 9.19L24 22.5h-7.4l-5.8-7.58-6.63 7.58H.49l8.6-9.83L0 1.5h7.59l5.24 6.93ZM17.6 20.3h2.04L6.49 3.6H4.3Z" },
  youtube:   { label: "YouTube",   path: "M23.5 6.2a3.02 3.02 0 0 0-2.12-2.14C19.5 3.55 12 3.55 12 3.55s-7.5 0-9.38.51A3.02 3.02 0 0 0 .5 6.2 31.5 31.5 0 0 0 0 12a31.5 31.5 0 0 0 .5 5.8 3.02 3.02 0 0 0 2.12 2.14c1.88.51 9.38.51 9.38.51s7.5 0 9.38-.51a3.02 3.02 0 0 0 2.12-2.14A31.5 31.5 0 0 0 24 12a31.5 31.5 0 0 0-.5-5.8ZM9.6 15.6V8.4l6.2 3.6Z" },
  linkedin:  { label: "LinkedIn",  path: "M20.45 20.45h-3.56v-5.57c0-1.33-.02-3.04-1.85-3.04-1.85 0-2.14 1.45-2.14 2.94v5.67H9.35V9h3.42v1.56h.05c.48-.9 1.64-1.85 3.37-1.85 3.6 0 4.27 2.37 4.27 5.46v6.28ZM5.34 7.43a2.06 2.06 0 1 1 0-4.13 2.06 2.06 0 0 1 0 4.13ZM7.12 20.45H3.55V9h3.57v11.45ZM22.22 0H1.77C.8 0 0 .78 0 1.75v20.5C0 23.2.8 24 1.77 24h20.45c.98 0 1.78-.8 1.78-1.75V1.75C24 .78 23.2 0 22.22 0Z" },
  whatsapp:  { label: "WhatsApp",  path: "M17.47 14.38c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.48-.89-.79-1.49-1.77-1.66-2.07-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.07-.15-.67-1.61-.92-2.21-.24-.58-.49-.5-.67-.51-.17-.01-.37-.01-.57-.01-.2 0-.52.07-.8.37-.27.3-1.04 1.02-1.04 2.48 0 1.46 1.07 2.87 1.22 3.07.15.2 2.1 3.2 5.08 4.49.71.31 1.26.49 1.69.63.71.22 1.36.19 1.87.12.57-.09 1.76-.72 2-1.41.25-.69.25-1.29.17-1.41-.07-.12-.27-.2-.57-.35ZM12.05 21.7a9.6 9.6 0 0 1-4.9-1.34l-.35-.21-3.64.96.97-3.55-.23-.36a9.62 9.62 0 0 1-1.47-5.12c0-5.31 4.33-9.63 9.65-9.63a9.58 9.58 0 0 1 6.81 2.82 9.55 9.55 0 0 1 2.82 6.81c0 5.31-4.33 9.63-9.64 9.63Zm8.2-17.84A11.53 11.53 0 0 0 12.05.5C5.7.5.53 5.66.53 12a11.44 11.44 0 0 0 1.53 5.75L.44 23.5l5.9-1.55a11.5 11.5 0 0 0 5.71 1.46c6.34 0 11.51-5.16 11.51-11.5a11.44 11.44 0 0 0-3.31-8.06Z" },
};

function socialEntries(social: Record<string, string> | undefined): Array<{ href: string; label: string; path: string }> {
  if (!social) return [];
  return Object.entries(social)
    .filter(([, url]) => typeof url === "string" && url.trim() !== "")
    .map(([key, url]) => {
      const meta = SOCIAL_META[key.toLowerCase()];
      return { href: url.trim(), label: meta?.label ?? key, path: meta?.path ?? "" };
    })
    .filter((e) => e.path !== "");
}

export function MaintenancePage({
  site,
  settings,
}: {
  site: SitePayload["site"];
  settings: SettingsPayload["settings"];
}) {
  const title    = site.maintenance_title || "Çok Yakında";
  const message  = site.maintenance_message || "Sitemiz şu anda hazırlanıyor. Çok yakında sizlerle olacağız.";
  const until    = site.maintenance_until || null;
  const siteName = settings.site_title || site.name;
  const logo     = settings.logo_url || null;

  const phone   = settings.contact?.phone || null;
  const email   = settings.contact?.email || null;
  const address = settings.contact?.address || null;
  const hasContact = Boolean(phone || email || address);

  const socials = socialEntries(settings.social);

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
          maxWidth: "620px",
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

        {until && <MaintenanceCountdown until={until} />}

        {socials.length > 0 && (
          <div style={{ display: "flex", gap: "0.75rem", justifyContent: "center", marginTop: "0.5rem" }}>
            {socials.map((s) => (
              <a
                key={s.label + s.href}
                href={s.href}
                target="_blank"
                rel="noopener noreferrer"
                aria-label={s.label}
                title={s.label}
                style={{
                  display: "inline-flex",
                  alignItems: "center",
                  justifyContent: "center",
                  width: "40px",
                  height: "40px",
                  borderRadius: "9999px",
                  border: "1px solid var(--color-border, #e5e7eb)",
                  color: "var(--color-primary, #6366f1)",
                }}
              >
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                  <path d={s.path} />
                </svg>
              </a>
            ))}
          </div>
        )}
      </div>

      {hasContact && (
        <footer
          style={{
            borderTop: "1px solid var(--color-border, #e5e7eb)",
            paddingTop: "1.25rem",
            marginTop: "2.5rem",
            width: "100%",
            maxWidth: "620px",
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
