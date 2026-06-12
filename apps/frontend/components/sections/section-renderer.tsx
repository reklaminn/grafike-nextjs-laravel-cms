/**
 * SectionRenderer — routes a PageSection to the appropriate renderer.
 *
 * Resolution order:
 *   1. article-list → ArticleListSection (async data fetch)
 *   2. form         → FormSectionLoader  (async data fetch)
 *   3. HTML template engine (render_mode === "html")
 *   4. component registry — exact "type/variation" or fallback "type"
 *   5. Development placeholder
 */

import "./section-registry"; // boot — registers all components into the registry

import { createElement } from "react";
import { HtmlSection }         from "@/components/sections/html-section";
import { ArticleListSection }  from "@/components/sections/article-list-section";
import { FormSectionLoader }   from "@/components/sections/form-section-loader";
import type { MenusPayload, PageSection, SettingsPayload, SitePayload } from "@/lib/types";
import { buildElementProps }        from "@/lib/sections/element-props";
import { renderBasicHtmlSection }   from "@/lib/sections/basic-html-renderer";
import { sectionRegistry }          from "@/lib/sections/component-registry";

type SectionRendererProps = {
  section:  PageSection;
  site:     SitePayload["site"];
  settings: SettingsPayload["settings"];
  menus:    MenusPayload;
  pageId?:  number;
  lang?:    string;
};

export function SectionRenderer({
  section, site, settings, menus, pageId, lang,
}: SectionRendererProps) {
  // ── 1. Dynamic data sections ───────────────────────────────────────────────
  if (section.type === "article-list") {
    return <ArticleListSection section={section} pageId={pageId} lang={lang} />;
  }

  if (section.type === "form") {
    return <FormSectionLoader section={section} />;
  }

  const props = buildElementProps({
    className:        section.css_class,
    id:               section.element_id,
    inlineStyle:      section.inline_style,
    customAttributes: section.custom_attributes,
  });

  // ── 2. HTML template engine ───────────────────────────────────────────────
  if (section.render_mode === "html") {
    const html = renderBasicHtmlSection(section, { site, settings, menus });

    if (!section.wrapper_tag) {
      return <HtmlSection html={html} />;
    }

    return createElement(section.wrapper_tag, props, <HtmlSection html={html} />);
  }

  // ── 3. Component registry ─────────────────────────────────────────────────
  const RegisteredComponent = sectionRegistry.resolve(section.type, section.variation);
  if (RegisteredComponent) {
    // Wrap in element if wrapper_tag is set; otherwise render directly.
    if (section.wrapper_tag) {
      return createElement(
        section.wrapper_tag,
        props,
        <RegisteredComponent
          section={section}
          site={site}
          settings={settings}
          menus={menus}
          pageId={pageId}
          lang={lang}
        />,
      );
    }

    return (
      <RegisteredComponent
        section={section}
        site={site}
        settings={settings}
        menus={menus}
        pageId={pageId}
        lang={lang}
      />
    );
  }

  // ── 4. Development placeholder ────────────────────────────────────────────
  const tag = section.wrapper_tag || "section";

  return createElement(
    tag,
    props,
    <div
      className="section-placeholder"
      style={{
        padding:      "2rem",
        background:   "#fafafa",
        border:       "2px dashed #d1d5db",
        borderRadius: "0.5rem",
        color:        "#9ca3af",
        fontSize:     "0.85rem",
      }}
    >
      <strong style={{ color: "#6b7280" }}>Component:</strong>{" "}
      {section.type}.{section.variation}
    </div>,
  );
}
