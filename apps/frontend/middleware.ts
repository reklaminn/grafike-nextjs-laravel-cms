import { NextResponse } from "next/server";
import type { NextRequest } from "next/server";

const DEFAULT_LOCALE = process.env.NEXT_PUBLIC_DEFAULT_LOCALE ?? "tr";

// Central admin domain — requests here go to /admin, not tenant pages.
const CENTRAL_HOST = process.env.NEXT_PUBLIC_SITE_URL
  ? new URL(process.env.NEXT_PUBLIC_SITE_URL).host
  : null;

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
    ?? request.nextUrl.searchParams.get("tenant_id")
    ?? request.cookies.get("grafike_preview_tenant")?.value;

  if (!tenant || !/^[a-zA-Z0-9_-]+$/.test(tenant)) {
    return null;
  }

  return tenant;
}

export function middleware(request: NextRequest) {
  const { pathname } = request.nextUrl;

  // Bakım modu bypass — ?onizleme=<token> gelince token'ı cookie'ye yaz ve
  // parametreyi URL'den temizle. Sonraki istekler cookie'yi taşır; SSR bunu
  // API'ye iletir, SiteController tenant preview_token ile eşleştirip bypass eder.
  const bypassToken = request.nextUrl.searchParams.get("onizleme");
  if (bypassToken !== null) {
    const clean = request.nextUrl.clone();
    clean.searchParams.delete("onizleme");
    const res = NextResponse.redirect(clean, { status: 302 });
    if (/^[A-Za-z0-9_-]{8,64}$/.test(bypassToken)) {
      res.cookies.set("grafike_site_bypass", bypassToken, {
        path: "/",
        maxAge: 60 * 60 * 24 * 30, // 30 gün
        sameSite: "lax",
      });
    } else {
      // Geçersiz/boş token → bypass'ı temizle (bakım moduna geri dön).
      res.cookies.delete("grafike_site_bypass");
    }
    return res;
  }

  const tenant = tenantPreviewId(request);

  // Central domain without a tenant preview → redirect to admin panel.
  const host = request.headers.get("host")?.split(":")[0]?.toLowerCase();
  if (CENTRAL_HOST && host === CENTRAL_HOST && !tenant) {
    const adminUrl = request.nextUrl.clone();
    adminUrl.pathname = "/admin";
    return NextResponse.redirect(adminUrl, { status: 302 });
  }

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
    if (tenant && request.nextUrl.searchParams.has("tenant")) {
      response.cookies.set("grafike_preview_tenant", tenant, {
        path: "/",
        maxAge: 60 * 60,
        sameSite: "lax",
      });
    }
    return response;
  }

  // No locale prefix — redirect to default locale.
  const url = request.nextUrl.clone();
  url.pathname = `/${DEFAULT_LOCALE}${pathname}`;
  const response = NextResponse.redirect(url, { status: 308 });
  response.headers.set("x-locale", DEFAULT_LOCALE);
  if (tenant && request.nextUrl.searchParams.has("tenant")) {
    response.cookies.set("grafike_preview_tenant", tenant, {
      path: "/",
      maxAge: 60 * 60,
      sameSite: "lax",
    });
  }
  return response;
}

export const config = {
  // Skip Next.js internals, static files and sitemap/robots
  matcher: [
    "/((?!_next/static|_next/image|favicon\\.ico|robots\\.txt|sitemap\\.xml).*)",
  ],
};
