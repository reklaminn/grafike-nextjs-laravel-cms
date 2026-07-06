"use client";

/**
 * Image gallery — type: "gallery" | "resim-galerisi", all variations.
 * Renders a responsive masonry-like grid; clicking opens a lightbox.
 */

import { useState } from "react";
import Image from "next/image";
import type { SectionBlockProps } from "@/lib/sections/component-registry";
import { str, parseJsonArray, extractGroupItems, resolveMediaUrl, isLocalMediaPath } from "@/lib/sections/component-registry";

type GalleryImage = {
  url?: string;
  thumb?: string;
  alt?: string;
  caption?: string;
};

export function GallerySection({ section }: SectionBlockProps) {
  const c = (section.content ?? {}) as Record<string, unknown>;

  const title       = str(c, "title");
  const description = str(c, "description");

  let images: GalleryImage[] = parseJsonArray<GalleryImage>(c, "images");
  if (images.length === 0) {
    images = extractGroupItems(c, "image_", ["url", "thumb", "alt", "caption"], 30)
      .map((item) => ({ url: resolveMediaUrl(item.url ?? ""), alt: item.alt, caption: item.caption }))
      .filter((img) => img.url);
  }

  const [lightbox, setLightbox] = useState<GalleryImage | null>(null);

  if (images.length === 0) return null;

  return (
    <section
      className="gallery-section"
      style={{ padding: "4rem 1.5rem", background: "var(--color-bg, #fff)" }}
    >
      <div style={{ maxWidth: "var(--container-width, 1280px)", margin: "0 auto" }}>
        {(title || description) && (
          <div style={{ textAlign: "center", marginBottom: "2.5rem" }}>
            {title && (
              <h2
                style={{
                  fontSize:    "clamp(1.75rem, 3.5vw, 2.5rem)",
                  fontWeight:  700,
                  color:       "var(--color-heading, #111827)",
                  marginBottom: description ? "0.75rem" : 0,
                }}
              >
                {title}
              </h2>
            )}
            {description && (
              <p style={{ fontSize: "1rem", color: "var(--color-text-soft, #374151)" }}>
                {description}
              </p>
            )}
          </div>
        )}

        {/* Grid */}
        <div
          style={{
            display:             "grid",
            gridTemplateColumns: "repeat(auto-fill, minmax(240px, 1fr))",
            gap:                 "0.75rem",
          }}
        >
          {images.map((img, i) => {
            const src = resolveMediaUrl(img.url ?? "");
            if (!src) return null;
            return (
              <button
                key={i}
                onClick={() => setLightbox(img)}
                style={{
                  display:      "block",
                  position:     "relative",
                  aspectRatio:  "4/3",
                  borderRadius: "var(--radius-card, 0.75rem)",
                  overflow:     "hidden",
                  cursor:       "zoom-in",
                  border:       "none",
                  padding:      0,
                  background:   "#e5e7eb",
                }}
              >
                <Image
                  src={src}
                  alt={img.alt ?? `Gallery image ${i + 1}`}
                  fill
                  sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 25vw"
                  style={{ objectFit: "cover", transition: "transform 0.3s" }}
                  unoptimized={isLocalMediaPath(src)}
                />
              </button>
            );
          })}
        </div>
      </div>

      {/* Lightbox */}
      {lightbox && (
        <div
          role="dialog"
          aria-modal="true"
          onClick={() => setLightbox(null)}
          style={{
            position:        "fixed",
            inset:           0,
            background:      "rgba(0,0,0,0.9)",
            display:         "flex",
            alignItems:      "center",
            justifyContent:  "center",
            zIndex:          9999,
            padding:         "2rem",
            cursor:          "zoom-out",
          }}
        >
          <div style={{ position: "relative", maxWidth: "90vw", maxHeight: "85vh", width: "100%" }}>
            <Image
              src={resolveMediaUrl(lightbox.url ?? "")}
              alt={lightbox.alt ?? ""}
              width={1200}
              height={800}
              unoptimized={isLocalMediaPath(resolveMediaUrl(lightbox.url ?? ""))}
              style={{
                objectFit:    "contain",
                width:        "100%",
                height:       "auto",
                maxHeight:    "80vh",
                borderRadius: "0.5rem",
              }}
            />
            {lightbox.caption && (
              <p
                style={{
                  textAlign:  "center",
                  color:      "rgba(255,255,255,0.75)",
                  marginTop:  "0.75rem",
                  fontSize:   "0.875rem",
                }}
              >
                {lightbox.caption}
              </p>
            )}
          </div>

          {/* Close button */}
          <button
            onClick={() => setLightbox(null)}
            style={{
              position:   "absolute",
              top:        "1rem",
              right:      "1rem",
              background: "rgba(255,255,255,0.15)",
              border:     "none",
              color:      "#fff",
              width:      "2.5rem",
              height:     "2.5rem",
              borderRadius: "50%",
              cursor:     "pointer",
              fontSize:   "1.25rem",
              display:    "flex",
              alignItems: "center",
              justifyContent: "center",
            }}
          >
            ×
          </button>
        </div>
      )}
    </section>
  );
}
