import { LanguageSwitcher, type LocaleOption } from "./language-switcher";

type SiteShellProps = {
  children: React.ReactNode;
  /** Available locales passed from the locale layout via getSitePayload(). */
  availableLocales?: LocaleOption[];
};

/**
 * SiteShell wraps every page under /{locale}/…
 *
 * Responsibilities:
 *  - Pass-through rendering (no structural DOM of its own).
 *  - Mount the floating LanguageSwitcher when the site has 2+ active locales.
 *    The switcher is a fixed-position overlay so it works with any theme.
 */
export function SiteShell({ children, availableLocales = [] }: SiteShellProps) {
  return (
    <>
      {children}
      {/* Floating language switcher — only rendered when > 1 locale is active */}
      <LanguageSwitcher locales={availableLocales} />
    </>
  );
}
