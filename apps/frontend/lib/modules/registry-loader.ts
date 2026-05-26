/**
 * Vertical-module dynamic loader.
 *
 * Each tenant's /api/v1/site payload includes a `site.modules` array
 * listing the verticals the admin has enabled (tours, commerce, …).
 * This loader lazily imports the matching `register.ts` from each
 * module's frontend directory so the bundle for a pure "kurumsal" tenant
 * stays small — Tours / Commerce code only ships when a tenant actually
 * uses them.
 *
 * Calling convention:
 *
 *   // In a server component (e.g. apps/frontend/app/[locale]/layout.tsx),
 *   // after fetching the site payload:
 *   import { loadModuleSections } from "@/lib/modules/registry-loader";
 *   await loadModuleSections(site.modules);
 *
 * Each module is expected to expose a default-exported `register()`
 * function that mutates the global `sectionRegistry` (and any other
 * registries it needs).  See `apps/frontend/modules/tours/register.ts`
 * — to be created in Phase 4.
 *
 * ─── Phase 0 status ──────────────────────────────────────────────────────
 *
 * This is a skeleton.  No module register files exist yet, so the
 * function currently no-ops for every module slug.  Each `case` will be
 * replaced with a real dynamic import as Phase 4 (Tours frontend) lands.
 */

import type { TenantModule } from "@/lib/types";

type ModuleSlug = TenantModule;

// Track which modules have already been loaded in this process to keep
// dynamic imports idempotent across multiple server renders / refreshes.
const loaded = new Set<ModuleSlug>();

export async function loadModuleSections(modules: ModuleSlug[] | undefined): Promise<void> {
  if (!modules || modules.length === 0) return;

  await Promise.all(
    modules.map(async (slug) => {
      if (loaded.has(slug)) return;
      loaded.add(slug);

      try {
        await loadOne(slug);
      } catch (err) {
        // Soft-fail: a bad module register file should never blow up the
        // entire page render.  Logged so we notice it in Sentry.
        console.error(`[modules] failed to load "${slug}":`, err);
      }
    }),
  );
}

async function loadOne(slug: ModuleSlug): Promise<void> {
  switch (slug) {
    case "tours":
      // Phase 4 wiring:
      //   const mod = await import("@/modules/tours/register");
      //   mod.default();
      return;

    case "commerce":
      // Phase 6 wiring.
      return;

    case "payments":
      // Payments is a backend-only concern (gateway abstraction lives
      // in Laravel).  No frontend bundle to import.
      return;

    default:
      // Forward-compat: an unknown slug arriving from the API means a
      // newer Laravel deploy than the frontend.  Ignore quietly until
      // the frontend catches up.
      return;
  }
}

/** Test helper — flush the loaded-set between test cases. */
export function __resetForTests(): void {
  loaded.clear();
}
