export type ThemeTokens = Record<string, string>;

/**
 * Vertical modules the current tenant has opted into.
 *
 * - `tours`     — cruise / paket / günlük tur kataloğu + booking
 * - `commerce`  — e-ticaret katalogu + sepet (Phase 6, not shipped yet)
 * - `payments`  — paylaşılan ödeme altyapısı (dependency-only; transitively
 *                 enabled when Tours or Commerce is on)
 *
 * Empty array (default for existing "kurumsal" tenants) means the
 * frontend should ship the base CMS bundle only — no module-specific
 * sections or routes load.  See lib/modules/registry-loader.ts.
 */
export type TenantModule = "tours" | "commerce" | "payments" | string;

export type SitePayload = {
  site: {
    name: string;
    domain: string;
    /** @see TenantModule */
    modules: TenantModule[];
    theme: {
      slug: string;
      engine: string;
      assets?: {
        css: string[];
        js: string[];
      };
    };
    tokens: ThemeTokens;
    header_variant: string;
    footer_variant: string;
    locale?: string;
    available_locales?: Array<{
      code: string;
      locale: string;
      name: string;
    }>;
  };
};

export type MenuItem = {
  id: number;
  title: string;
  url: string;
  target: string | null;
  children: MenuItem[];
};

export type MenuPayload = {
  id: number;
  name: string;
  slug: string;
  location: string;
  items: MenuItem[];
};

export type MenusPayload = MenuPayload[];

export type SettingsPayload = {
  settings: {
    site_title: string;
    logo_url: string;
    favicon_url: string;
    footer_text: string;
    contact: {
      phone: string;
      email: string;
      address: string;
    };
    social: Record<string, string>;
    services: {
      google_analytics_id?: string;
      google_tag_manager_id?: string;
      recaptcha_site_key?: string;
      google_site_verification?: string;
      bing_site_verification?: string;
      indexnow_key?: string;
      [key: string]: string | undefined;
    };
    business?: {
      name?: string;
      type?: string;
      address_street?: string;
      address_city?: string;
      address_postal_code?: string;
      address_country?: string;
      telephone?: string;
      email?: string;
      geo_lat?: string;
      geo_lng?: string;
      opening_hours?: string;         // JSON string  e.g. [{"days":"Mo-Fr","hours":"09:00-18:00"}]
      organization_json_ld?: string;  // pre-built JSON-LD from admin
    };
  };
};

export type PageSection = {
  id: string;
  type: string;
  variation: string;
  render_mode: "html" | "component";
  section_template_id?: number;
  template_name?: string;
  html_template?: string | null;
  component_key?: string | null;
  schema?: Record<string, unknown>;
  is_active: boolean;
  content: Record<string, unknown>;
  custom_css?: string;
  custom_js?: string;
  wrapper_tag?: string | null;
  css_class?: string | null;
  element_id?: string | null;
  inline_style?: string | null;
  custom_attributes?: string | null;
  html_override?: string | null;
};

export type PageRegionBlock = PageSection & {
  region?: "header" | "body" | "footer" | string;
  row_id?: string;
  column_id?: string;
  column_width?: number;
};

export type PageRegionColumn = {
  id: string;
  width: number;
  is_active: boolean;
  responsive?: {
    xs?: number | null;
    sm?: number | null;
    md?: number | null;
    lg?: number | null;
    xl?: number | null;
  };
  css_class?: string | null;
  element_id?: string | null;
  inline_style?: string | null;
  custom_attributes?: string | null;
  blocks: PageRegionBlock[];
};

export type PageRegionRow = {
  id: string;
  type: "row" | string;
  is_active: boolean;
  container?: string | null;
  wrapper_tag?: string | null;
  css_class?: string | null;
  element_id?: string | null;
  inline_style?: string | null;
  custom_attributes?: string | null;
  columns: PageRegionColumn[];
};

export type PageRegions = {
  header: PageRegionRow[];
  body: PageRegionRow[];
  footer: PageRegionRow[];
};

export type ArticleCover = {
  url: string;
  thumb: string;
  alt: string;
} | null;

export type ArticleListItem = {
  id: number;
  title: string;
  slug: string;
  excerpt: string | null;
  display_date: string | null;
  published_at: string | null;
  is_featured: boolean;
  cover: ArticleCover;
  author: { id: number; name: string } | null;
  language: { id: number; code: string } | null;
  page: { id: number; title: string; slug: string } | null;
};

export type ArticleListPayload = {
  data: ArticleListItem[];
  meta: {
    current_page: number;
    per_page: number;
    total: number;
    last_page: number;
  };
};

export type FormField = {
  id: number;
  name: string;
  label: string;
  type: "text" | "email" | "textarea" | "select" | "checkbox" | "radio" | "tel" | "url" | "number" | "date" | "file" | "hidden";
  placeholder?: string | null;
  default_value?: string | null;
  options: Array<{ label: string; value: string }> | string[];
  is_required: boolean;
  css_class?: string | null;
  section?: string | null;
};

export type FormPayload = {
  id: number;
  name: string;
  slug: string;
  description?: string | null;
  requires_captcha: boolean;
  fields: FormField[];
};

export type ArticleBlock = {
  type: "heading" | "paragraph" | "image" | "video" | "html";
  level?: number;
  text?: string;
  content?: string;
  images?: Array<{ url: string; alt?: string; caption?: string }>;
  url?: string;
  alt?: string;
  caption?: string;
  embed_url?: string;
  code?: string;
};

export type ArticleDetail = {
  id: number;
  title: string;
  slug: string;
  excerpt: string | null;
  body: string | null;
  content_json: ArticleBlock[];
  display_date: string | null;
  published_at: string | null;
  updated_at: string | null;
  listing_variant: string | null;
  detail_variant: string | null;
  is_featured: boolean;
  cover: ArticleCover;
  gallery: Array<{ url: string; thumb: string; name: string; alt: string }>;
};

export type ArticleDetailPayload = {
  article: ArticleDetail;
  author: { id: number; name: string } | null;
  language: { id: number; code: string; locale: string; name: string } | null;
  page: { id: number; title: string; slug: string } | null;
  seo: {
    title: string;
    description: string;
    canonical: string;
    noindex: boolean;
    og_image?: string | null;
    og_type?: string;
    hreflang_tags?: Record<string, string>;
    structured_data?: object | null;
  };
};

export type PageSeoData = {
  title: string;
  description: string;
  keywords?: string;
  canonical: string;
  noindex?: boolean;
  og_image?: string | null;
  og_type?: string;
  hreflang_tags?: Record<string, string>;
  structured_data?: object | null;
  schema_type?: string | null;
};

export type PagePayload = {
  page: {
    id: number;
    title: string;
    slug: string;
    template?: string | null;
    sections: PageSection[];
    region_version?: number;
    regions?: PageRegions;
    breadcrumbs?: Array<{ title: string; slug: string; url: string }>;
    is_password_protected?: boolean;
    is_locked?: boolean;
    has_member_only_content?: boolean;
    is_group_restricted?: boolean;
    required_group_names?: string[];
  };
  seo: PageSeoData;
  theme?: {
    slug: string;
  };
};
