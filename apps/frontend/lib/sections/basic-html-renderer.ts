import type { MenuItem, MenusPayload, PageSection, SettingsPayload, SitePayload } from "@/lib/types";

function escapeHtml(value: unknown): string {
  return String(value ?? "")
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#039;");
}

export type SectionRenderContext = {
  site: SitePayload["site"];
  settings: SettingsPayload["settings"];
  menus: MenusPayload;
};

type RepeaterSchema = {
  type?: unknown;
  item_template?: unknown;
};

type MenuTemplateSet = {
  wrapper_template?: unknown;
  item_template?: unknown;
  item_with_children_template?: unknown;
  child_item_template?: unknown;
  child_item_with_children_template?: unknown;
};

function normalizeMenuKey(key: string): string {
  return key.toLowerCase().replaceAll(/[^a-z0-9_]+/g, "_").replaceAll(/^_+|_+$/g, "");
}

function getMenuTemplateSet(schema: PageSection["schema"] | undefined, key: string): MenuTemplateSet {
  const container = schema?.menu_templates ?? schema?.menuTemplates;

  if (!container || typeof container !== "object" || Array.isArray(container)) {
    return {};
  }

  const value = (container as Record<string, unknown>)[key];

  return value && typeof value === "object" && !Array.isArray(value) ? (value as MenuTemplateSet) : {};
}

function renderMenuTemplate(template: string, values: Record<string, unknown>): string {
  const rawValues = template.replaceAll(/{{{\s*([a-zA-Z0-9_]+)\s*}}}/g, (_match, key: string) => {
    return String(values[key] ?? "");
  });

  return rawValues.replaceAll(/{{\s*([a-zA-Z0-9_]+)\s*}}/g, (_match, key: string) => {
    return escapeHtml(values[key]);
  });
}

function defaultMenuItemTemplate(): string {
  return `<li><a href="{{url}}"{{{target_attr}}}>{{title}}</a></li>`;
}

function defaultMenuItemWithChildrenTemplate(): string {
  return `<li><a href="{{url}}"{{{target_attr}}}>{{title}}</a><ul>{{{children_html}}}</ul></li>`;
}

function selectMenuItemTemplate(templateSet: MenuTemplateSet, hasChildren: boolean, isChild: boolean): string {
  if (hasChildren) {
    const template = isChild
      ? templateSet.child_item_with_children_template || templateSet.item_with_children_template
      : templateSet.item_with_children_template;

    return typeof template === "string" ? template : defaultMenuItemWithChildrenTemplate();
  }

  const template = isChild
    ? templateSet.child_item_template || templateSet.item_template
    : templateSet.item_template;

  return typeof template === "string" ? template : defaultMenuItemTemplate();
}

function renderMenuItems(items: MenuItem[], templateSet: MenuTemplateSet = {}, isChild = false): string {
  return items
    .map((item) => {
      const childrenHtml = item.children?.length ? renderMenuItems(item.children, templateSet, true) : "";
      const template = selectMenuItemTemplate(templateSet, childrenHtml !== "", isChild);

      return renderMenuTemplate(template, {
        id: item.id,
        title: item.title,
        url: item.url || "#",
        target: item.target || "",
        target_attr: item.target ? ` target="${escapeHtml(item.target)}"` : "",
        children_html: childrenHtml,
        active_class: "",
        has_children_class: childrenHtml !== "" ? "has-children" : "",
      });
    })
    .join("");
}

function buildSystemPlaceholders(context: SectionRenderContext, schema?: PageSection["schema"]): Record<string, string> {
  const { site, settings, menus } = context;
  const placeholders: Record<string, string> = {
    site_name: site.name,
    site_domain: site.domain,
    site_locale: site.locale || "",
    theme_slug: site.theme.slug,
    header_variant: site.header_variant || "",
    footer_variant: site.footer_variant || "",
    site_title: settings.site_title || site.name,
    logo_url: settings.logo_url || "",
    favicon_url: settings.favicon_url || "",
    footer_text: settings.footer_text || "",
    contact_phone: settings.contact.phone || "",
    phone: settings.contact.phone || "",
    contact_email: settings.contact.email || "",
    email: settings.contact.email || "",
    contact_address: settings.contact.address || "",
    address: settings.contact.address || "",
    social_whatsapp: settings.social?.whatsapp || "",
    whatsapp_number: settings.social?.whatsapp || "",
    social_instagram: settings.social?.instagram || "",
    social_facebook: settings.social?.facebook || "",
    social_x: settings.social?.x || settings.social?.twitter || "",
  };

  menus.forEach((menu) => {
    const keyParts = [menu.location, menu.slug].filter(Boolean);

    keyParts.forEach((key) => {
      const normalizedKey = normalizeMenuKey(key);
      const templateSet = getMenuTemplateSet(schema, normalizedKey);
      const itemsHtml = renderMenuItems(menu.items || [], templateSet);
      const wrapperTemplate = typeof templateSet.wrapper_template === "string" ? templateSet.wrapper_template : `<ul>{{{items_html}}}</ul>`;

      placeholders[`menu_${normalizedKey}_html`] = renderMenuTemplate(wrapperTemplate, {
        items_html: itemsHtml,
        menu_name: menu.name,
        menu_key: normalizedKey,
      });
      placeholders[`menu_${normalizedKey}_items_html`] = itemsHtml;
      placeholders[`menu_${normalizedKey}_name`] = menu.name;
    });
  });

  return placeholders;
}

function resolveValue(
  key: string,
  content: PageSection["content"],
  systemValues: Record<string, string>,
): unknown {
  if (Object.prototype.hasOwnProperty.call(content, key)) {
    return content[key];
  }

  return systemValues[key];
}

function resolveRepeaterHtml(
  key: string,
  content: PageSection["content"],
  systemValues: Record<string, string>,
  context: SectionRenderContext,
  schema?: PageSection["schema"],
): string | null {
  if (!key.endsWith("_html")) {
    return null;
  }

  const baseKey = key.slice(0, -5);
  const value = content[baseKey];
  const fieldSchema = schema?.[baseKey] as RepeaterSchema | undefined;

  if (!Array.isArray(value) || fieldSchema?.type !== "repeater" || typeof fieldSchema.item_template !== "string") {
    return null;
  }

  return value
    .filter((item): item is PageSection["content"] => item !== null && typeof item === "object" && !Array.isArray(item))
    .map((item) => renderTemplateString(fieldSchema.item_template as string, item, context))
    .join("");
}

function renderTemplateString(
  template: string,
  content: PageSection["content"],
  context: SectionRenderContext,
  schema?: PageSection["schema"],
): string {
  const systemValues = buildSystemPlaceholders(context, schema);

  const withRawValues = template.replaceAll(/{{{\s*([a-zA-Z0-9_]+)\s*}}}/g, (_match, key: string) => {
    const repeaterHtml = resolveRepeaterHtml(key, content, systemValues, context, schema);

    if (repeaterHtml !== null) {
      return repeaterHtml;
    }

    return String(resolveValue(key, content, systemValues) ?? "");
  });

  return withRawValues.replaceAll(/{{\s*([a-zA-Z0-9_]+)\s*}}/g, (_match, key: string) => {
    return escapeHtml(resolveValue(key, content, systemValues));
  });
}

export function renderBasicHtmlSection(section: PageSection, context: SectionRenderContext): string {
  const template = section.html_override || section.html_template;

  if (template) {
    return renderTemplateString(template, section.content, context, section.schema);
  }

  return `
    <section class="section-card" style="padding:24px;">
      <strong>${escapeHtml(section.type)}</strong>
      <p style="margin-top:8px;color:var(--text-soft);">No HTML renderer registered for this section yet.</p>
    </section>
  `;
}
