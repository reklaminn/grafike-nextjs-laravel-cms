import { redirect } from "next/navigation";
import { DEFAULT_LOCALE } from "@/lib/i18n";

/**
 * Root page (/): redirect to /{defaultLocale}/home.
 * The middleware already handles non-prefixed paths, but this acts as a
 * belt-and-suspenders fallback (and avoids a 404 flash before middleware fires).
 */
export default function RootPage() {
  redirect(`/${DEFAULT_LOCALE}/home`);
}
