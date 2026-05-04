'use client';

import { useEffect } from 'react';

interface ThemeScriptsProps {
  scripts: string[];
}

/**
 * Loads theme JS assets **sequentially** on the client and ensures scripts
 * that rely on `document.addEventListener('DOMContentLoaded', …)` still
 * work even though the event fired long before `afterInteractive` scripts run.
 *
 * Why this file exists
 * ─────────────────────
 * Next.js `<Script strategy="afterInteractive">` injects scripts after React
 * hydration.  By that point:
 *   • document.readyState === 'complete'
 *   • DOMContentLoaded has already fired
 *
 * Any script (Bootstrap plugins, video init, slider init, …) that does:
 *   document.addEventListener('DOMContentLoaded', function() { init(); })
 * …will NEVER call init(), because the event was missed.
 *
 * Fix: temporarily patch document.addEventListener so that a
 * 'DOMContentLoaded' subscription made after the event has fired runs the
 * callback immediately (next microtask).  We load each script one at a time
 * to guarantee dependency order (Bootstrap must finish before main.js runs).
 */
export function ThemeScripts({ scripts }: ThemeScriptsProps) {
  useEffect(() => {
    if (!scripts.length) return;

    // ── 1. Patch DOMContentLoaded ─────────────────────────────────────────
    const origAddEventListener = document.addEventListener.bind(document);

    (document as typeof document & { addEventListener: typeof document.addEventListener }) .addEventListener =
      function patchedAEL(
        type: string,
        listener: EventListenerOrEventListenerObject,
        options?: boolean | AddEventListenerOptions,
      ) {
        if (type === 'DOMContentLoaded' && document.readyState !== 'loading') {
          // Event already fired — schedule callback for the next microtask
          const cb =
            typeof listener === 'function'
              ? listener
              : listener.handleEvent.bind(listener);
          Promise.resolve().then(() => cb(new Event('DOMContentLoaded')));
          return;
        }
        origAddEventListener(type as keyof DocumentEventMap, listener as EventListenerOrEventListenerObject, options as boolean | AddEventListenerOptions);
      } as typeof document.addEventListener;

    // ── 2. Load scripts sequentially ─────────────────────────────────────
    const appendedSrcs = new Set<string>();

    const loadScript = (src: string): Promise<void> =>
      new Promise((resolve) => {
        // Skip if already in the DOM (SPA navigation dedup)
        if (appendedSrcs.has(src)) { resolve(); return; }
        try {
          const existing = document.querySelector(`script[src="${src}"]`);
          if (existing) { resolve(); return; }
        } catch {
          // CSS.escape not needed here — if querySelector throws, just load anyway
        }

        appendedSrcs.add(src);
        const el = document.createElement('script');
        el.src = src;
        el.onload  = () => resolve();
        el.onerror = () => resolve(); // continue chain even on 404
        document.body.appendChild(el);
      });

    (async () => {
      for (const src of scripts) {
        await loadScript(src); // sequential: next loads only after previous is done
      }
      // ── 3. Restore original after all scripts finished ────────────────
      // @ts-ignore
      document.addEventListener = origAddEventListener;
    })();

    return () => {
      // @ts-ignore
      document.addEventListener = origAddEventListener;
    };
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []); // run once on mount — scripts array won't change between renders

  return null;
}
