/**
 * On-demand ISR revalidation endpoint.
 *
 * Called by Laravel observers (via FrontendRevalidator service) after content
 * is saved.  Accepts JSON body with `paths` and/or `tags` arrays.
 *
 * Security: Bearer token in Authorization header must match REVALIDATE_SECRET.
 *
 * POST /api/revalidate
 * Authorization: Bearer <REVALIDATE_SECRET>
 * Content-Type: application/json
 *
 * Body:
 *   { "paths": ["/tr/home"], "tags": ["page-home", "pages"] }
 */

import { revalidatePath, revalidateTag } from "next/cache";
import { NextRequest, NextResponse } from "next/server";

const REVALIDATE_SECRET = process.env.REVALIDATE_SECRET;

function unauthorized() {
  return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
}

export async function POST(request: NextRequest) {
  // ── Auth ──────────────────────────────────────────────────────────────────
  if (!REVALIDATE_SECRET) {
    return NextResponse.json({ error: "REVALIDATE_SECRET not configured" }, { status: 500 });
  }

  const authHeader = request.headers.get("authorization") ?? "";
  const token = authHeader.startsWith("Bearer ") ? authHeader.slice(7) : "";

  if (token !== REVALIDATE_SECRET) {
    return unauthorized();
  }

  // ── Body ──────────────────────────────────────────────────────────────────
  let body: { paths?: string[]; tags?: string[] };
  try {
    body = (await request.json()) as { paths?: string[]; tags?: string[] };
  } catch {
    return NextResponse.json({ error: "Invalid JSON body" }, { status: 400 });
  }

  const revalidatedPaths: string[] = [];
  const revalidatedTags: string[] = [];

  // ── Revalidate by path ────────────────────────────────────────────────────
  if (Array.isArray(body.paths)) {
    for (const path of body.paths) {
      if (typeof path === "string" && path.startsWith("/")) {
        revalidatePath(path);
        revalidatedPaths.push(path);
      }
    }
  }

  // ── Revalidate by tag ─────────────────────────────────────────────────────
  if (Array.isArray(body.tags)) {
    for (const tag of body.tags) {
      if (typeof tag === "string") {
        revalidateTag(tag);
        revalidatedTags.push(tag);
      }
    }
  }

  return NextResponse.json({
    revalidated: true,
    paths: revalidatedPaths,
    tags:  revalidatedTags,
    at:    new Date().toISOString(),
  });
}
