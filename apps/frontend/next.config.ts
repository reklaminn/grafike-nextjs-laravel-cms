import type { NextConfig } from "next";
import { withSentryConfig } from "@sentry/nextjs";

const nextConfig: NextConfig = {
  // ── Output ──────────────────────────────────────────────────────────────────
  // "standalone" bundles only what's needed — required for the Docker runner stage.
  output: "standalone",

  // ── Images ──────────────────────────────────────────────────────────────────
  images: {
    // Allow WebP and AVIF output (sharp is installed — auto-detected by Next.js)
    formats: ["image/avif", "image/webp"],
    remotePatterns: [
      // Production: HTTPS from any domain (CDN, Spatie MediaLibrary, etc.)
      { protocol: "https", hostname: "**" },
      // Local dev: HTTP from any hostname
      { protocol: "http",  hostname: "**" },
    ],
  },

  // ── Headers ─────────────────────────────────────────────────────────────────
  async headers() {
    return [
      {
        // Protect the revalidation endpoint — only allow server-to-server calls
        source: "/api/revalidate",
        headers: [
          { key: "Cache-Control", value: "no-store" },
        ],
      },
    ];
  },
};

// ── Sentry ────────────────────────────────────────────────────────────────────
// Only active when SENTRY_DSN is set.  When it's empty the wrapper is a no-op.
export default withSentryConfig(nextConfig, {
  org:     process.env.SENTRY_ORG     ?? "",
  project: process.env.SENTRY_PROJECT ?? "",

  // Suppress noisy CLI output unless explicitly enabled
  silent: !process.env.CI,

  // Upload source maps in CI/production; skip locally to speed up builds
  sourcemaps: {
    disable: !process.env.SENTRY_AUTH_TOKEN,
  },

  // Automatically tree-shake Sentry logging in client bundles
  disableLogger: true,
});
