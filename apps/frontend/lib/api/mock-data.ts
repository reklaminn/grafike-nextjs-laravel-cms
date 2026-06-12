import type { MenuPayload, PagePayload, SettingsPayload, SitePayload } from "@/lib/types";

function blockToRegion(block: NonNullable<PagePayload["page"]["sections"]>[number], index: number) {
  return {
    id: `row_body_${index + 1}`,
    type: "row" as const,
    is_active: true,
    columns: [
      {
        id: `col_body_${index + 1}_1`,
        width: 12,
        is_active: true,
        blocks: [block],
      },
    ],
  };
}

export const mockSitePayload: SitePayload = {
  site: {
    name: "Grafike Furniture",
    domain: "grafike.local",
    // Mock tenant runs core "kurumsal" only.  Override in a per-test fixture
    // (e.g. ["tours"]) when wiring up Phase 4 frontend integration tests.
    modules: [],
    theme: {
      slug: "porto-furniture",
      engine: "next"
    },
    tokens: {
      color_primary: "#7c5a3a",
      color_secondary: "#f3ede6",
      color_accent: "#111827",
      radius_card: "20px",
      radius_button: "999px",
      container_width: "1320px"
    },
    header_variant: "porto-furniture-header",
    footer_variant: "porto-furniture-footer",
    locale: "tr"
  }
};

export const mockSettingsPayload: SettingsPayload = {
  settings: {
    site_title: "Grafike Furniture",
    logo_url: "",
    favicon_url: "",
    footer_text: "© 2026 Grafike Furniture",
    contact: {
      phone: "+90 212 555 0000",
      email: "hello@grafike.test",
      address: "Istanbul, Turkiye"
    },
    social: {
      instagram: "https://instagram.com/grafike"
    },
    services: {}
  }
};

export const mockHeaderMenuPayload: MenuPayload = {
  id: 1,
  name: "Header Menu",
  slug: "header-tr",
  location: "header",
  items: [
    {
      id: 1,
      title: "Ana Sayfa",
      url: "/home",
      target: "_self",
      children: []
    },
    {
      id: 2,
      title: "Blog",
      url: "/blog",
      target: "_self",
      children: []
    }
  ]
};

const pages: Record<string, PagePayload> = {
  home: {
    page: {
      id: 1,
      title: "Home",
      slug: "home",
      sections: [
        {
          id: "hero_1",
          type: "hero",
          variation: "porto-split",
          render_mode: "html",
          section_template_id: 1,
          template_name: "Hero / Porto Split",
          html_template:
            '<section class="hero hero--porto-split"><div class="container"><p class="eyebrow">{{eyebrow}}</p><h1>{{title}}</h1><p class="subtitle">{{subtitle}}</p><a class="button" href="{{button_url}}">{{button_text}}</a></div></section>',
          is_active: true,
          content: {
            eyebrow: "Grafike Demo",
            title: "Modern Furniture Collections",
            subtitle: "Basic HTML Section Mode ile ilk Porto benzeri giriş sayfası.",
            button_text: "Discover Collection",
            button_url: "/collections"
          }
        },
        {
          id: "features_1",
          type: "features",
          variation: "porto-icons",
          render_mode: "html",
          section_template_id: 2,
          template_name: "Features / Porto Icons",
          html_template:
            '<section class="features features--porto-icons"><div class="container"><h2>{{title}}</h2><p>{{description}}</p></div></section>',
          is_active: true,
          content: {
            title: "Why customers choose this brand",
            description: "This homepage is now coming from the Laravel public API instead of static mock-only wiring."
          }
        },
        {
          id: "articles_1",
          type: "article-list",
          variation: "porto-cards",
          render_mode: "html",
          section_template_id: 3,
          template_name: "Article List / Porto Cards",
          html_template:
            '<section class="section-card" style="padding:32px;"><div class="container"><h2>{{title}}</h2><p>{{description}}</p><div class="article-grid">{{{items_html}}}</div></div></section>',
          is_active: true,
          content: {
            title: "Yazi Modulu Ornegi",
            description: "Bu alan blog yazilarindan uretilen kartlari gosterir.",
            items_html:
              '<article style="padding:20px;border:1px solid var(--border-soft);border-radius:var(--radius-card);display:grid;gap:12px;"><h3 style="margin:0;font-size:20px;">Reusable theme pack mantigi</h3><p style="margin:0;color:var(--text-soft);line-height:1.7;">Ayni template farkli marka tokenlari ile tekrar kullanilabilir.</p><a href="/reusable-theme-pack-mantigi" style="font-weight:700;color:var(--color-primary);">Yaziyi oku</a></article>'
          }
        }
      ],
      region_version: 2,
      regions: {
        header: [],
        body: [],
        footer: [],
      }
    },
    seo: {
      title: "Home",
      description: "Mock homepage payload",
      canonical: "http://localhost:3000/home"
    },
    theme: {
      slug: "porto-furniture"
    }
  },
  blog: {
    page: {
      id: 2,
      title: "Blog",
      slug: "blog",
      template: "blog-index",
      sections: [
        {
          id: "blog_intro",
          type: "rich-text",
          variation: "porto-content",
          render_mode: "html",
          section_template_id: 4,
          template_name: "Rich Text / Porto Content",
          html_template:
            '<section class="section-card" style="padding:32px;"><div class="container"><h2>{{title}}</h2><div class="content">{{{body_html}}}</div></div></section>',
          is_active: true,
          content: {
            title: "Blog",
            body_html:
              "<p>Bu sayfa demo yazilarini listeler. Faz 1'de liste kartlari da yine HTML section template ile geliyor.</p>"
          }
        }
      ],
      region_version: 2,
      regions: {
        header: [],
        body: [],
        footer: [],
      }
    },
    seo: {
      title: "Blog",
      description: "Mock blog payload",
      canonical: "http://localhost:3000/blog"
    },
    theme: {
      slug: "porto-furniture"
    }
  }
};

pages.home.page.regions!.body = pages.home.page.sections.map(blockToRegion);
pages.blog.page.regions!.body = pages.blog.page.sections.map(blockToRegion);

export function mockPagePayload(slug: string): PagePayload | null {
  return pages[slug] ?? null;
}
