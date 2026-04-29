import { redirect } from "next/navigation";

type LocaleRootProps = {
  params: Promise<{ locale: string }>;
};

/** /{locale} → redirect to /{locale}/home */
export default async function LocaleRootPage({ params }: LocaleRootProps) {
  const { locale } = await params;
  redirect(`/${locale}/home`);
}
