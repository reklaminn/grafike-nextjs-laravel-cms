import type { SectionBlockProps } from "@/lib/sections/component-registry";
import { str } from "@/lib/sections/component-registry";

/**
 * Converts YouTube / Vimeo watch URLs to embed URLs.
 * Returns original URL if it's already an embed or unknown provider.
 */
function toEmbedUrl(url: string): string {
  if (!url) return "";

  // Already an embed URL
  if (url.includes("/embed/") || url.includes("player.vimeo.com")) return url;

  // YouTube: https://www.youtube.com/watch?v=ID or https://youtu.be/ID
  const ytMatch =
    url.match(/[?&]v=([^&]+)/) ??
    url.match(/youtu\.be\/([^?]+)/);
  if (ytMatch) {
    return `https://www.youtube.com/embed/${ytMatch[1]}?rel=0`;
  }

  // Vimeo: https://vimeo.com/12345678
  const vimeoMatch = url.match(/vimeo\.com\/(\d+)/);
  if (vimeoMatch) {
    return `https://player.vimeo.com/video/${vimeoMatch[1]}`;
  }

  return url;
}

/** Video embed — type: "video-embed", all variations. */
export function VideoEmbedSection({ section }: SectionBlockProps) {
  const c = (section.content ?? {}) as Record<string, unknown>;

  const rawUrl     = str(c, "url") || str(c, "video_url");
  const title      = str(c, "title");
  const aspectStr  = str(c, "aspect_ratio", "16/9");

  if (!rawUrl) return null;

  const embedUrl    = toEmbedUrl(rawUrl);
  const [w, h]      = aspectStr.split("/").map(Number);
  const paddingTop  = h && w ? `${(h / w) * 100}%` : "56.25%";

  return (
    <section
      className="video-embed-section"
      style={{
        padding:    "4rem 1.5rem",
        background: "var(--color-bg, #fff)",
      }}
    >
      <div
        style={{
          maxWidth: "var(--container-width, 1280px)",
          margin:   "0 auto",
        }}
      >
        {title && (
          <h2
            style={{
              textAlign:    "center",
              fontSize:     "clamp(1.5rem, 3vw, 2.25rem)",
              fontWeight:   700,
              marginBottom: "2rem",
              color:        "var(--color-heading, #111827)",
            }}
          >
            {title}
          </h2>
        )}

        {/* Responsive iframe wrapper */}
        <div
          style={{
            position:     "relative",
            paddingTop,
            borderRadius: "var(--radius-card, 1rem)",
            overflow:     "hidden",
            boxShadow:    "0 20px 60px rgba(0,0,0,0.15)",
          }}
        >
          <iframe
            src={embedUrl}
            title={title || "Video"}
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
            allowFullScreen
            loading="lazy"
            style={{
              position: "absolute",
              inset:    0,
              width:    "100%",
              height:   "100%",
              border:   "none",
            }}
          />
        </div>
      </div>
    </section>
  );
}
