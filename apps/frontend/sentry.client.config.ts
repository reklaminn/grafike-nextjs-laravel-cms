import * as Sentry from "@sentry/nextjs";

// Only initialise when DSN is provided (production / staging)
if (process.env.NEXT_PUBLIC_SENTRY_DSN) {
  Sentry.init({
    dsn: process.env.NEXT_PUBLIC_SENTRY_DSN,
    environment: process.env.NEXT_PUBLIC_APP_ENV ?? "production",

    // Capture 10 % of transactions for performance monitoring
    tracesSampleRate: 0.1,

    // Replay 1 % of sessions, 100 % of sessions with errors
    replaysSessionSampleRate: 0.01,
    replaysOnErrorSampleRate: 1.0,

    integrations: [
      Sentry.replayIntegration({
        maskAllText: true,
        blockAllMedia: true,
      }),
    ],
  });
}
