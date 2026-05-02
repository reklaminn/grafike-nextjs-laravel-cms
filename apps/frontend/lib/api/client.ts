import { headers } from "next/headers";
import type {
  ArticleDetailPayload,
  ArticleListPayload,
  FormPayload,
  MenuPayload,
  MenusPayload,
  PagePayload,
  SettingsPayload,
  SitePayload,
} from "@/lib/types";
import {
  mockHeaderMenuPayload,
  mockPagePayload,
  mockSettingsPayload,
  mockSitePayload,
} from "@/lib/api/mock-data";

const API_BASE_URL = process.env.CMS_API_URL;
const FALLBACK_SITE_HOST = process.env.NEXT_PUBLIC_SITE_URL
  ? new URL(process.env.NEXT_PUBLIC_SITE_URL).host
  : null;
const INTERNAL_API_HOSTS = new Set([
  "app1",
  "app2",
  "app3",
  "grafike_cms_app1",
  "grafike_cms_app2",
  "grafike_cms_app3",
]);

type ResourceEnvelope<T> = { data: T };

function unwrapResource<T>(payload: T | ResourceEnvelope<T>): T {
  if (payload && typeof payload === "object" && "data" in payload) {
    return (payload as ResourceEnvelope<T>).data;
  }
  return payload as T;
}

async function getSiteHostHeader(): Promise<string | null> {
  const requestHeaders = await headers();
  const host = requestHeaders.get("x-forwarded-host") ?? requestHeaders.get("host");
  const normalizedHost = host?.split(",")[0]?.trim().split(":")[0]?.toLowerCase();

  if (host && normalizedHost && !INTERNAL_API_HOSTS.has(normalizedHost)) {
    return host;
  }

  return FALLBACK_SITE_HOST;
}

async function getTenantPreviewHeader(): Promise<string | null> {
  const requestHeaders = await headers();
  const tenant = requestHeaders.get("x-tenant-id")?.trim();

  return tenant && /^[a-zA-Z0-9_-]+$/.test(tenant) ? tenant : null;
}

/**
 * Core fetch helper.
 *
 * @param tags  Next.js cache tags — used by revalidateTag() in the ISR webhook.
 */
async function fetchJson<T>(
  path: string,
  fallback: T,
  wrapped = true,
  tags?: string[],
): Promise<T> {
  if (!API_BASE_URL) return fallback;

  try {
    const siteHost = await getSiteHostHeader();
    const tenantId = await getTenantPreviewHeader();
    const requestHeaders: Record<string, string> = {};

    if (siteHost) {
      requestHeaders["X-Site-Host"] = siteHost;
      requestHeaders["X-Forwarded-Host"] = siteHost;
    }

    if (tenantId) {
      requestHeaders["X-Tenant-ID"] = tenantId;
    }

    const cacheOptions = tenantId
      ? { cache: "no-store" as const }
      : {
          next: {
            revalidate: 60,
            ...(tags && tags.length > 0 ? { tags } : {}),
          },
        };

    const response = await fetch(`${API_BASE_URL}${path}`, {
      headers: Object.keys(requestHeaders).length > 0 ? requestHeaders : undefined,
      ...cacheOptions,
    });

    if (!response.ok) return fallback;

    const payload = (await response.json()) as T | ResourceEnvelope<T>;
    return wrapped ? unwrapResource<T>(payload) : (payload as T);
  } catch {
    return fallback;
  }
}

// ─── Site & Settings ──────────────────────────────────────────────────────────

export async function getSitePayload(lang?: string): Promise<SitePayload> {
  const qs = lang ? `?lang=${encodeURIComponent(lang)}` : "";
  return fetchJson<SitePayload>(`/api/v1/site${qs}`, mockSitePayload, true, [
    "site",
    "settings",
  ]);
}

export async function getSettingsPayload(): Promise<SettingsPayload> {
  return fetchJson<SettingsPayload>("/api/v1/settings", mockSettingsPayload, false, [
    "settings",
  ]);
}

// ─── Menus ────────────────────────────────────────────────────────────────────

export async function getMenuPayload(location: string): Promise<MenuPayload> {
  return fetchJson<MenuPayload>(`/api/v1/menus/${location}`, mockHeaderMenuPayload, true, [
    "menus",
    `menu-${location}`,
  ]);
}

export async function getMenusPayload(): Promise<MenusPayload> {
  return fetchJson<MenusPayload>("/api/v1/menus", [mockHeaderMenuPayload], true, ["menus"]);
}

// ─── Pages ────────────────────────────────────────────────────────────────────

export async function getPagePayload(slug: string, lang?: string): Promise<PagePayload | null> {
  const tenantId = await getTenantPreviewHeader();
  const fallback = tenantId ? null : mockPagePayload(slug);
  const qs = lang ? `?lang=${encodeURIComponent(lang)}` : "";
  return fetchJson<PagePayload | null>(`/api/v1/pages/${slug}${qs}`, fallback, true, [
    "pages",
    `page-${slug}`,
  ]);
}

// ─── Articles ─────────────────────────────────────────────────────────────────

export type GetArticlesOptions = {
  pageId?: number;
  siteId?: number;
  lang?: string;
  featuredOnly?: boolean;
  limit?: number;
  page?: number;
};

export async function getArticles(options: GetArticlesOptions = {}): Promise<ArticleListPayload> {
  const params = new URLSearchParams();
  if (options.pageId)       params.set("page_id",      String(options.pageId));
  if (options.siteId)       params.set("site_id",       String(options.siteId));
  if (options.lang)         params.set("lang",           options.lang);
  if (options.featuredOnly) params.set("featured_only", "1");
  if (options.limit)        params.set("limit",          String(options.limit));
  if (options.page)         params.set("page",           String(options.page));

  const qs   = params.toString();
  const path = `/api/v1/articles${qs ? `?${qs}` : ""}`;

  return fetchJson<ArticleListPayload>(
    path,
    { data: [], meta: { current_page: 1, per_page: 12, total: 0, last_page: 1 } },
    false,
    // Tag includes page_id so article-list blocks on a specific page revalidate correctly
    ["articles", ...(options.pageId ? [`articles-page-${options.pageId}`] : [])],
  );
}

export async function getArticle(slug: string, lang?: string): Promise<ArticleDetailPayload | null> {
  const qs = lang ? `?lang=${encodeURIComponent(lang)}` : "";
  return fetchJson<ArticleDetailPayload | null>(
    `/api/v1/articles/${slug}${qs}`,
    null,
    true,
    ["articles", `article-${slug}`],
  );
}

// ─── Forms ────────────────────────────────────────────────────────────────────

export async function getForm(formId: number | string): Promise<FormPayload | null> {
  return fetchJson<FormPayload | null>(`/api/v1/forms/${formId}`, null, true, [
    "forms",
    `form-${formId}`,
  ]);
}
