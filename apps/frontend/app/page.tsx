import { redirect } from "next/navigation";
import { DEFAULT_LOCALE } from "@/lib/i18n";

type RootPageProps = {
  searchParams?: Promise<Record<string, string | string[] | undefined>>;
};

/**
 * Root page (/): redirect to /{defaultLocale}.
 * The middleware already handles non-prefixed paths, but this acts as a
 * belt-and-suspenders fallback (and avoids a 404 flash before middleware fires).
 */
export default async function RootPage({ searchParams }: RootPageProps) {
  const resolvedSearchParams = await searchParams;
  const tenant = resolvedSearchParams?.tenant ?? resolvedSearchParams?.tenant_id;
  const tenantId = Array.isArray(tenant) ? tenant[0] : tenant;
  const qs = tenantId ? `?tenant=${encodeURIComponent(tenantId)}` : "";

  redirect(`/${DEFAULT_LOCALE}${qs}`);
}
