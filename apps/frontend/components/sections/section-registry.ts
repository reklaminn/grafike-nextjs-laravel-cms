/**
 * Section Component Registry — boot file.
 *
 * Import this once (in section-renderer.tsx) to register all section components.
 * Add new components here as they are created.
 */

import { sectionRegistry } from "@/lib/sections/component-registry";

import { HeroSection }          from "@/components/sections/blocks/hero-section";
import { FeaturesSection }      from "@/components/sections/blocks/features-section";
import { CtaBannerSection }     from "@/components/sections/blocks/cta-banner-section";
import { RichTextSection }      from "@/components/sections/blocks/rich-text-section";
import { SpacerSection }        from "@/components/sections/blocks/spacer-section";
import { VideoEmbedSection }    from "@/components/sections/blocks/video-embed-section";
import { CustomHtmlSection }    from "@/components/sections/blocks/custom-html-section";
import { TestimonialsSection }  from "@/components/sections/blocks/testimonials-section";
import { GallerySection }       from "@/components/sections/blocks/gallery-section";
import { LogoBandSection }      from "@/components/sections/blocks/logo-band-section";
import { PricingSection }       from "@/components/sections/blocks/pricing-section";

// ─── Hero ─────────────────────────────────────────────────────────────────────
// Catch-all for any hero variation that is not explicitly overridden
sectionRegistry.register("hero",              HeroSection);
sectionRegistry.register("hero-banner",       HeroSection);

// ─── Features ─────────────────────────────────────────────────────────────────
sectionRegistry.register("features",          FeaturesSection);

// ─── CTA ─────────────────────────────────────────────────────────────────────
sectionRegistry.register("cta-banner",        CtaBannerSection);
sectionRegistry.register("cta",               CtaBannerSection);

// ─── Rich text ────────────────────────────────────────────────────────────────
sectionRegistry.register("rich-text",         RichTextSection);
sectionRegistry.register("metin-blogu",       RichTextSection);
sectionRegistry.register("tam-icerik",        RichTextSection);

// ─── Spacer ───────────────────────────────────────────────────────────────────
sectionRegistry.register("spacer",            SpacerSection);

// ─── Video ────────────────────────────────────────────────────────────────────
sectionRegistry.register("video-embed",       VideoEmbedSection);
sectionRegistry.register("video",             VideoEmbedSection);

// ─── Custom HTML ──────────────────────────────────────────────────────────────
sectionRegistry.register("custom-html",       CustomHtmlSection);

// ─── Testimonials ─────────────────────────────────────────────────────────────
sectionRegistry.register("testimonials",      TestimonialsSection);
sectionRegistry.register("yorumlar",          TestimonialsSection);

// ─── Gallery ─────────────────────────────────────────────────────────────────
sectionRegistry.register("gallery",           GallerySection);
sectionRegistry.register("resim-galerisi",    GallerySection);
sectionRegistry.register("resim-listeleme",   GallerySection);

// ─── Logo band ────────────────────────────────────────────────────────────────
sectionRegistry.register("logo-menu",         LogoBandSection);
sectionRegistry.register("logo-band",         LogoBandSection);

// ─── Pricing ─────────────────────────────────────────────────────────────────
sectionRegistry.register("pricing",           PricingSection);
