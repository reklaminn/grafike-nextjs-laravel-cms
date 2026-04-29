/**
 * Section Component Registry
 *
 * Maps "type/variation" (or just "type") keys to React server components.
 * SectionRenderer checks here before falling back to the HTML template engine.
 *
 * Register a component:
 *   registry.register("hero", HeroSection);
 *   registry.register("hero/porto-split", HeroSplitSection);
 *
 * Resolution order: exact "type/variation" → fallback "type" → null
 */

import type { ComponentType } from "react";
import type { PageSection, SitePayload, SettingsPayload, MenusPayload } from "@/lib/types";

export type SectionBlockProps = {
  section: PageSection;
  site: SitePayload["site"];
  settings: SettingsPayload["settings"];
  menus: MenusPayload;
  pageId?: number;
  lang?: string;
};

type Registry = Map<string, ComponentType<SectionBlockProps>>;

const _registry: Registry = new Map();

export const sectionRegistry = {
  /** Register a component for an exact "type/variation" or fallback "type" key. */
  register(key: string, component: ComponentType<SectionBlockProps>): void {
    _registry.set(key, component);
  },

  /** Resolve component: tries "type/variation" first, then "type". */
  resolve(type: string, variation: string): ComponentType<SectionBlockProps> | null {
    return _registry.get(`${type}/${variation}`) ?? _registry.get(type) ?? null;
  },
};

// ─── Content helpers ──────────────────────────────────────────────────────────

/** Safely read a string from section.content */
export function str(content: Record<string, unknown>, key: string, fallback = ""): string {
  const val = content[key];
  return typeof val === "string" ? val.trim() : fallback;
}

/** Resolve a possibly-relative URL against the API base (for legacy media paths). */
export function resolveMediaUrl(url: string, apiBase?: string): string {
  if (!url) return "";
  if (url.startsWith("http") || url.startsWith("//") || url.startsWith("/")) return url;
  const base = (apiBase ?? process.env.NEXT_PUBLIC_API_URL ?? "").replace(/\/$/, "");
  return `${base}/${url}`;
}

/**
 * Try to parse a JSON-encoded array from section.content[key].
 * Returns [] on failure.
 */
export function parseJsonArray<T = Record<string, unknown>>(
  content: Record<string, unknown>,
  key: string,
): T[] {
  const raw = content[key];
  if (Array.isArray(raw)) return raw as T[];
  if (typeof raw === "string") {
    try {
      const parsed = JSON.parse(raw);
      return Array.isArray(parsed) ? (parsed as T[]) : [];
    } catch {
      return [];
    }
  }
  return [];
}

/**
 * Extract indexed group fields from content.
 * e.g. { item_1_title, item_1_icon, item_2_title, item_2_icon } → [{title, icon}, {title, icon}]
 *
 * @param content   section.content object
 * @param prefix    field prefix, e.g. "item_"
 * @param keys      sub-keys to collect, e.g. ["title", "icon", "text"]
 * @param maxItems  safety cap
 */
export function extractGroupItems(
  content: Record<string, unknown>,
  prefix: string,
  keys: string[],
  maxItems = 20,
): Array<Record<string, string>> {
  const items: Array<Record<string, string>> = [];

  for (let i = 1; i <= maxItems; i++) {
    const item: Record<string, string> = {};
    let hasAny = false;

    for (const key of keys) {
      const val = content[`${prefix}${i}_${key}`];
      if (typeof val === "string" && val.trim()) {
        item[key] = val.trim();
        hasAny = true;
      } else {
        item[key] = "";
      }
    }

    if (!hasAny) break;
    items.push(item);
  }

  return items;
}
