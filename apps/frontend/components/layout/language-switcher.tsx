"use client";

/**
 * LanguageSwitcher — floating pill dropdown
 *
 * Rendered by SiteShell (locale layout) so it appears on every page
 * regardless of which theme is active.
 *
 * Behaviour:
 *  - Hidden when only one locale is available.
 *  - Current locale shown as a compact pill (globe + code).
 *  - Click opens a small dropdown listing all available locales.
 *  - Selecting a locale swaps the first URL segment and navigates.
 *  - If hreflangMap is provided (page-level alternates), those URLs are
 *    used for accuracy; otherwise falls back to path-segment swapping.
 */

import { usePathname, useRouter } from "next/navigation";
import { useEffect, useRef, useState } from "react";
import { switchLocaleInPath } from "@/lib/i18n";

export type LocaleOption = {
  code: string;    // "tr", "en"
  name: string;    // "Türkçe", "English"
  locale: string;  // "tr_TR", "en_US"
};

type LanguageSwitcherProps = {
  locales: LocaleOption[];
};

export function LanguageSwitcher({ locales }: LanguageSwitcherProps) {
  const pathname = usePathname();
  const router   = useRouter();

  const [open, setOpen]           = useState(false);
  const [hreflangMap, setHreflang] = useState<Record<string, string>>({});
  const containerRef               = useRef<HTMLDivElement>(null);

  // Read hreflang alternates from <head> on every navigation.
  // Next.js already writes these from generateMetadata() — we just harvest them
  // so the switcher links to the exact translated slug instead of just swapping
  // the locale prefix in the current path.
  useEffect(() => {
    const map: Record<string, string> = {};
    document.querySelectorAll<HTMLLinkElement>("link[rel='alternate'][hreflang]").forEach((el) => {
      const lang = el.getAttribute("hreflang");
      const href = el.getAttribute("href");
      if (lang && href && lang !== "x-default") {
        // hreflang is a BCP47 tag like "tr" or "tr-TR"; extract language part
        const code = lang.split("-")[0].split("_")[0];
        map[code] = href;
      }
    });
    setHreflang(map);
  }, [pathname]);

  const currentCode = pathname.split("/")[1] ?? "";
  const current     = locales.find((l) => l.code === currentCode) ?? locales[0];

  // Close on outside click
  useEffect(() => {
    function onOutside(e: MouseEvent) {
      if (containerRef.current && !containerRef.current.contains(e.target as Node)) {
        setOpen(false);
      }
    }
    if (open) document.addEventListener("mousedown", onOutside);
    return () => document.removeEventListener("mousedown", onOutside);
  }, [open]);

  // Close on Escape
  useEffect(() => {
    function onKey(e: KeyboardEvent) {
      if (e.key === "Escape") setOpen(false);
    }
    document.addEventListener("keydown", onKey);
    return () => document.removeEventListener("keydown", onKey);
  }, []);

  if (!locales || locales.length <= 1) return null;

  function navigate(code: string) {
    setOpen(false);
    if (code === currentCode) return;

    // Prefer hreflang alternate (exact translated slug); fall back to
    // simple locale-prefix swap when no alternate is available.
    if (hreflangMap[code]) {
      window.location.href = hreflangMap[code];
      return;
    }

    router.push(switchLocaleInPath(pathname, code));
  }

  return (
    <div
      ref={containerRef}
      style={{
        position: "fixed",
        bottom:   "1.5rem",
        right:    "1.5rem",
        zIndex:   9999,
        fontFamily: "system-ui, -apple-system, sans-serif",
      }}
    >
      {/* Pill trigger */}
      <button
        type="button"
        onClick={() => setOpen((v) => !v)}
        aria-haspopup="listbox"
        aria-expanded={open}
        aria-label="Dil seçin"
        style={{
          display:        "inline-flex",
          alignItems:     "center",
          gap:            "0.4rem",
          padding:        "0.45rem 0.85rem",
          background:     "rgba(255,255,255,0.92)",
          backdropFilter: "blur(8px)",
          WebkitBackdropFilter: "blur(8px)",
          border:         "1px solid rgba(0,0,0,0.12)",
          borderRadius:   "999px",
          boxShadow:      "0 2px 12px rgba(0,0,0,0.12)",
          cursor:         "pointer",
          fontSize:       "0.8125rem",
          fontWeight:     600,
          color:          "#111827",
          letterSpacing:  "0.01em",
          transition:     "box-shadow 0.15s",
          userSelect:     "none",
        }}
      >
        <GlobeIcon />
        <span style={{ textTransform: "uppercase" }}>{current?.code ?? currentCode}</span>
        <ChevronIcon open={open} />
      </button>

      {/* Dropdown */}
      {open && (
        <ul
          role="listbox"
          aria-label="Dil listesi"
          style={{
            position:       "absolute",
            bottom:         "calc(100% + 8px)",
            right:          0,
            margin:         0,
            padding:        "0.35rem",
            listStyle:      "none",
            background:     "rgba(255,255,255,0.97)",
            backdropFilter: "blur(12px)",
            WebkitBackdropFilter: "blur(12px)",
            border:         "1px solid rgba(0,0,0,0.1)",
            borderRadius:   "0.75rem",
            boxShadow:      "0 8px 28px rgba(0,0,0,0.14)",
            minWidth:       "140px",
            overflow:       "hidden",
          }}
        >
          {locales.map((l) => {
            const isActive = l.code === currentCode;
            return (
              <li key={l.code} role="option" aria-selected={isActive}>
                <button
                  type="button"
                  onClick={() => navigate(l.code)}
                  style={{
                    display:        "flex",
                    alignItems:     "center",
                    gap:            "0.5rem",
                    width:          "100%",
                    padding:        "0.5rem 0.75rem",
                    background:     isActive ? "rgba(99,102,241,0.08)" : "transparent",
                    border:         "none",
                    borderRadius:   "0.5rem",
                    cursor:         isActive ? "default" : "pointer",
                    fontSize:       "0.875rem",
                    fontWeight:     isActive ? 700 : 500,
                    color:          isActive ? "#4f46e5" : "#374151",
                    textAlign:      "left",
                    transition:     "background 0.1s",
                  }}
                  onMouseEnter={(e) => {
                    if (!isActive) (e.currentTarget as HTMLButtonElement).style.background = "rgba(0,0,0,0.05)";
                  }}
                  onMouseLeave={(e) => {
                    if (!isActive) (e.currentTarget as HTMLButtonElement).style.background = "transparent";
                  }}
                >
                  <span
                    style={{
                      display:    "inline-flex",
                      alignItems: "center",
                      justifyContent: "center",
                      width:      "22px",
                      height:     "16px",
                      fontSize:   "0.7rem",
                      fontWeight: 700,
                      background: isActive ? "#4f46e5" : "#e5e7eb",
                      color:      isActive ? "#fff" : "#6b7280",
                      borderRadius: "3px",
                      textTransform: "uppercase",
                      letterSpacing: "0.05em",
                      flexShrink: 0,
                    }}
                  >
                    {l.code}
                  </span>
                  <span>{l.name}</span>
                  {isActive && (
                    <span style={{ marginLeft: "auto", color: "#4f46e5" }}>✓</span>
                  )}
                </button>
              </li>
            );
          })}
        </ul>
      )}
    </div>
  );
}

// ─── Icon helpers ─────────────────────────────────────────────────────────────

function GlobeIcon() {
  return (
    <svg
      aria-hidden="true"
      xmlns="http://www.w3.org/2000/svg"
      width="13"
      height="13"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      strokeWidth="2.2"
      strokeLinecap="round"
      strokeLinejoin="round"
      style={{ opacity: 0.7, flexShrink: 0 }}
    >
      <circle cx="12" cy="12" r="10" />
      <line x1="2" y1="12" x2="22" y2="12" />
      <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" />
    </svg>
  );
}

function ChevronIcon({ open }: { open: boolean }) {
  return (
    <svg
      aria-hidden="true"
      xmlns="http://www.w3.org/2000/svg"
      width="11"
      height="11"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      strokeWidth="2.5"
      strokeLinecap="round"
      strokeLinejoin="round"
      style={{
        opacity: 0.6,
        flexShrink: 0,
        transform: open ? "rotate(180deg)" : "rotate(0deg)",
        transition: "transform 0.2s",
      }}
    >
      <polyline points="6 9 12 15 18 9" />
    </svg>
  );
}
