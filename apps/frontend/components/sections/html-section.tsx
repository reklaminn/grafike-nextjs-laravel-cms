'use client';

import { useEffect, useRef } from 'react';

type HtmlSectionProps = {
  html: string;
};

/**
 * Execute every <script> element inside `container`.
 *
 * `innerHTML` / `dangerouslySetInnerHTML` do NOT run scripts — browsers
 * intentionally ignore scripts injected that way.  The only reliable approach
 * is to clone each <script> node into a freshly created element and append it
 * to the document; the browser then fetches/executes it exactly once.
 *
 * We also patch `document.addEventListener` briefly so that any script using
 * the `DOMContentLoaded` pattern still fires even though the event has long
 * since passed (Next.js `afterInteractive` runs well after DOMContentLoaded).
 */
function executeScripts(container: HTMLElement) {
  const scripts = Array.from(container.querySelectorAll<HTMLScriptElement>('script'));
  if (!scripts.length) return;

  // Temporarily wrap addEventListener so DOMContentLoaded callbacks fire
  // immediately when the event has already passed.
  const originalAdd = document.addEventListener.bind(document);
  const domReady = document.readyState !== 'loading';

  if (domReady) {
    // @ts-ignore – deliberate short-lived monkey-patch
    document.addEventListener = function patchedAdd(
      type: string,
      listener: EventListenerOrEventListenerObject,
      options?: boolean | AddEventListenerOptions,
    ) {
      if (type === 'DOMContentLoaded') {
        // event already fired — call immediately (next microtask)
        Promise.resolve().then(() => {
          if (typeof listener === 'function') listener(new Event('DOMContentLoaded'));
          else listener.handleEvent(new Event('DOMContentLoaded'));
        });
      } else {
        originalAdd(type, listener, options);
      }
    };
  }

  scripts.forEach((oldScript) => {
    const newScript = document.createElement('script');

    // Copy all attributes (src, type, async, defer, data-*, …)
    Array.from(oldScript.attributes).forEach((attr) => {
      newScript.setAttribute(attr.name, attr.value);
    });

    // For inline scripts copy the text content
    if (!newScript.src) {
      newScript.textContent = oldScript.textContent;
    }

    // Replace in-place so relative position is preserved
    oldScript.replaceWith(newScript);
  });

  // Restore original addEventListener after all scripts are queued
  if (domReady) {
    // @ts-ignore
    document.addEventListener = originalAdd;
  }
}

export function HtmlSection({ html }: HtmlSectionProps) {
  const ref = useRef<HTMLDivElement>(null);

  useEffect(() => {
    if (ref.current) {
      executeScripts(ref.current);
    }
  // Re-run only when the HTML string actually changes
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [html]);

  return (
    <div
      ref={ref}
      className="html-section"
      // dangerouslySetInnerHTML preserves ALL original HTML attributes
      // (autoplay, muted, playsinline, data-*, …) without any React
      // camelCase translation that createElement would apply.
      dangerouslySetInnerHTML={{ __html: html }}
    />
  );
}
