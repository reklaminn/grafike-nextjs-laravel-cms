"use client";

/**
 * LanguageSwitcher
 *
 * Renders a compact locale selector.  Uses the current pathname to build
 * alternate locale URLs by swapping the first URL segment (the locale code).
 *
 * Usage (Server Component passes available_locales from site payload):
 *
 *   <LanguageSwitcher
 *     locales={sitePayload.site.available_locales ?? []}
 *   />
 */

import { usePathname, useRouter } from "next/navigation";
import { switchLocaleInPath } from "@/lib/i18n";

type LocaleOption = {
  code: string;   // "tr", "en"
  name: string;   // "Türkçe", "English"
  locale: string; // "tr_TR", "en_US"
};

type LanguageSwitcherProps = {
  locales: LocaleOption[];
};

export function LanguageSwitcher({ locales }: LanguageSwitcherProps) {
  const pathname = usePathname();
  const router   = useRouter();

  // Current locale is the first URL segment: "/tr/about" → "tr"
  const currentLocale = pathname.split("/")[1] ?? "";

  if (!locales || locales.length <= 1) return null;

  function handleChange(e: React.ChangeEvent<HTMLSelectElement>) {
    const target = e.currentTarget.value;
    router.push(switchLocaleInPath(pathname, target));
  }

  return (
    <div className="language-switcher" style={{ display: "inline-flex", alignItems: "center", gap: "0.35rem" }}>
      {/* Globe icon */}
      <svg
        aria-hidden="true"
        xmlns="http://www.w3.org/2000/svg"
        width="14"
        height="14"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        strokeWidth="2"
        strokeLinecap="round"
        strokeLinejoin="round"
        style={{ opacity: 0.6, flexShrink: 0 }}
      >
        <circle cx="12" cy="12" r="10" />
        <line x1="2" y1="12" x2="22" y2="12" />
        <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" />
      </svg>

      <select
        value={currentLocale}
        onChange={handleChange}
        aria-label="Dil seçin"
        style={{
          appearance:      "none",
          background:      "transparent",
          border:          "none",
          fontSize:        "0.875rem",
          fontWeight:      500,
          cursor:          "pointer",
          color:           "inherit",
          padding:         "0.15rem 0.5rem 0.15rem 0",
          outline:         "none",
        }}
      >
        {locales.map((l) => (
          <option key={l.code} value={l.code}>
            {l.name}
          </option>
        ))}
      </select>
    </div>
  );
}
