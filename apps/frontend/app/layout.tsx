/**
 * Root layout — minimal shell.
 *
 * All locale-specific rendering (theme assets, tokens, site shell) lives in
 * app/[locale]/layout.tsx.  This file only provides <html> and <body> tags
 * that Next.js requires at the root, using the locale stamped by middleware.
 */
import type { Metadata } from "next";
import { headers } from "next/headers";
import "./globals.css";

export const metadata: Metadata = {
  metadataBase: new URL(process.env.NEXT_PUBLIC_SITE_URL ?? "http://localhost:3000"),
};

export default async function RootLayout({
  children,
}: Readonly<{ children: React.ReactNode }>) {
  const h = await headers();
  // middleware stamps x-locale on every response; fall back to env default.
  const locale = h.get("x-locale") ?? (process.env.NEXT_PUBLIC_DEFAULT_LOCALE ?? "tr");

  return (
    <html lang={locale} suppressHydrationWarning>
      <body>{children}</body>
    </html>
  );
}
