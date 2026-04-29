/**
 * i18n utilities shared across Server and Client Components.
 *
 * Supported locales are driven by env vars:
 *   NEXT_PUBLIC_DEFAULT_LOCALE   – default locale code, e.g. "tr"
 *   NEXT_PUBLIC_SUPPORTED_LOCALES – comma-separated list, e.g. "tr,en,de"
 *
 * These must also be mirrored in middleware.ts (Edge Runtime cannot import this module).
 */

export const DEFAULT_LOCALE = process.env.NEXT_PUBLIC_DEFAULT_LOCALE ?? "tr";

export function getSupportedLocales(): string[] {
  const env = process.env.NEXT_PUBLIC_SUPPORTED_LOCALES;
  if (env) return env.split(",").map((l) => l.trim()).filter(Boolean);
  return [DEFAULT_LOCALE];
}

export function isValidLocale(locale: string): boolean {
  return getSupportedLocales().includes(locale);
}

/**
 * Convert a locale code ("tr") to an HTML lang attribute ("tr").
 * If the code looks like a locale string ("tr_TR"), extract the language part.
 */
export function toHtmlLang(code: string): string {
  return code.split("_")[0];
}

/**
 * Swap the locale segment in a pathname.
 *
 * @example
 *   switchLocaleInPath("/tr/about", "en") // → "/en/about"
 *   switchLocaleInPath("/tr",       "en") // → "/en"
 */
export function switchLocaleInPath(pathname: string, targetLocale: string): string {
  const segments = pathname.split("/"); // ["", "tr", ...rest]
  segments[1] = targetLocale;
  return segments.join("/") || "/";
}
