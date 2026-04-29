import type { SectionBlockProps } from "@/lib/sections/component-registry";
import { str } from "@/lib/sections/component-registry";

/**
 * Custom HTML block — type: "custom-html", all variations.
 * Renders arbitrary CMS-authored HTML with no wrapper (unless wrapper_tag is set).
 */
export function CustomHtmlSection({ section }: SectionBlockProps) {
  const c    = (section.content ?? {}) as Record<string, unknown>;
  const html = str(c, "html") || str(c, "html_content") || str(c, "code");

  if (!html) return null;

  return (
    // biome-ignore lint/security/noDangerouslySetInnerHtml: CMS-controlled HTML
    <div className="custom-html-section" dangerouslySetInnerHTML={{ __html: html }} />
  );
}
