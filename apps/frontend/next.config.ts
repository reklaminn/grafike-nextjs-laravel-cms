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

  // ── Rewrites ────────────────────────────────────────────────────────────────
  async rewrites() {
    // beforeFiles: page-route eşleşmesinden ÖNCE çalışır. Aksi halde (afterFiles)
    // locale-siz /tenant-assets/2/x.png yolunu [locale]/[...slug] catch-all'ı
    // (locale=tenant-assets) yakalayıp render etmeye çalışır → 500. beforeFiles
    // proxy'yi routing'in en başına alır, catch-all'a hiç ulaşmaz.
    return {
      beforeFiles: [
        {
          // Tenant medya (Spatie MediaLibrary → TenantMediaUrlGenerator) domain'siz
          // /tenant-assets/{path}?tenant={id} URL'i üretir (public sitede backend
          // domain'i görünmesin diye). Bu path SADECE Laravel'de var — next/image'in
          // kendi /_next/image optimize proxy'si "local" src'leri KENDİ sunucusundan
          // self-fetch etmeye çalışır ve bu route Next'te tanımlı olmadığı için 404
          // ("not a valid image" 400) alırdı. Bu rewrite Next'in kendi routing
          // katmanına /tenant-assets'i tanıtıp CMS_API_URL (SSR'ın zaten kullandığı
          // internal Docker bağlantısı) üzerinden Laravel'e proxy'ler — hem 400
          // çözülür hem next/image'in boyutlandırma/WebP optimizasyonu korunur.
          //
          // ÖNEMLİ: Next, rewrites destination'ını BUILD-time'da routes-manifest'e
          // gömer; runtime container env'i (CMS_API_URL=http://app1:80) çok geç
          // kalır. Tracked .env.local'de CMS_API_URL=http://127.0.0.1:8000 (yerel
          // dev) olduğu için CMS_API_URL kullanılırsa build 127.0.0.1:8000'i
          // dondurur → canlıda ECONNREFUSED. Bu yüzden .env.local'in KİRLETMEDİĞİ
          // ayrı bir değişken (CMS_INTERNAL_URL) kullanıp internal Docker servis
          // adresini (app1:80) varsayılan yapıyoruz. Yerel dev'de gerekirse
          // .env.local'e CMS_INTERNAL_URL=http://127.0.0.1:8000 eklenebilir.
          source: "/tenant-assets/:path*",
          destination: `${process.env.CMS_INTERNAL_URL ?? "http://app1:80"}/tenant-assets/:path*`,
        },
      ],
    };
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
