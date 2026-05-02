import { redirect } from "next/navigation";

type LocaleRootProps = {
  params: Promise<{ locale: string }>;
  searchParams?: Promise<Record<string, string | string[] | undefined>>;
};

/** /{locale} → redirect to /{locale}/home */
export default async function LocaleRootPage({ params, searchParams }: LocaleRootProps) {
  const { locale } = await params;
  const resolvedSearchParams = await searchParams;
  const tenant = resolvedSearchParams?.tenant ?? resolvedSearchParams?.tenant_id;
  const tenantId = Array.isArray(tenant) ? tenant[0] : tenant;
  const qs = tenantId ? `?tenant=${encodeURIComponent(tenantId)}` : "";

  redirect(`/${locale}/home${qs}`);
}
