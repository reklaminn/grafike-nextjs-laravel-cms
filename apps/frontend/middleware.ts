import { NextResponse } from "next/server";
import type { NextRequest } from "next/server";

const DEFAULT_LOCALE = process.env.NEXT_PUBLIC_DEFAULT_LOCALE ?? "tr";

// Build supported locale list from env (comma-separated) or fall back to default only.
const SUPPORTED_LOCALES: string[] = (
  process.env.NEXT_PUBLIC_SUPPORTED_LOCALES ?? DEFAULT_LOCALE
)
  .split(",")
  .map((l) => l.trim())
  .filter(Boolean);

const BACKEND_PATH_PREFIXES = [
  "/admin",
  "/api",
  "/build",
  "/storage",
  "/vendor",
  "/livewire",
  "/up",
];

function detectLocaleInPath(pathname: string): string | null {
  return (
    SUPPORTED_LOCALES.find(
      (locale) => pathname === `/${locale}` || pathname.startsWith(`/${locale}/`),
    ) ?? null
  );
}

function tenantPreviewId(request: NextRequest): string | null {
  const tenant = request.nextUrl.searchParams.get("tenant")
    ?? request.nextUrl.searchParams.get("tenant_id");

  if (!tenant || !/^[a-zA-Z0-9_-]+$/.test(tenant)) {
    return null;
  }

  return tenant;
}

export function middleware(request: NextRequest) {
  const { pathname } = request.nextUrl;
  const tenant = tenantPreviewId(request);

  const requestHeaders = new Headers(request.headers);
  if (tenant) {
    requestHeaders.set("x-tenant-id", tenant);
  }

  if (BACKEND_PATH_PREFIXES.some((prefix) => pathname === prefix || pathname.startsWith(`${prefix}/`))) {
    return NextResponse.next({ request: { headers: requestHeaders } });
  }

  const detectedLocale = detectLocaleInPath(pathname);

  if (detectedLocale) {
    // Path already has a valid locale prefix — let it through, but stamp the header.
    const response = NextResponse.next({ request: { headers: requestHeaders } });
    response.headers.set("x-locale", detectedLocale);
    return response;
  }

  // No locale prefix — redirect to default locale.
  const url = request.nextUrl.clone();
  url.pathname = `/${DEFAULT_LOCALE}${pathname}`;
  const response = NextResponse.redirect(url, { status: 308 });
  response.headers.set("x-locale", DEFAULT_LOCALE);
  return response;
}

export const config = {
  // Skip Next.js internals, static files and sitemap/robots
  matcher: [
    "/((?!_next/static|_next/image|favicon\\.ico|robots\\.txt|sitemap\\.xml).*)",
  ],
};
