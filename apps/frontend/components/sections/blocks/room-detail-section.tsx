/**
 * RoomDetailSection — "daire-detay" / "oda-detay" bloğu.
 *
 * Tek daire/oda tipinin detay sayfası. content.room_type (slug) ile Lodging
 * API'sinden oda tipini çeker ve gösterir:
 *   • görsel galeri  • başlık + özet  • kapasite/alan/oda/banyo rozetleri
 *   • açıklama        • olanaklar (amenities)  • fiyat
 *   • odaya KİLİTLİ rezervasyon formu (ReservationSection, picker gizli)
 *
 * Async server component (veri çeker) → section-renderer'da ListingSection
 * gibi doğrudan döndürülür.
 *
 * content: room_type (zorunlu slug), reserve_title (ops)
 */
import { getRoomTypes } from "@/lib/api/client";
import { str } from "@/lib/sections/component-registry";
import type { PageSection, RoomType } from "@/lib/types";
import { ReservationSection } from "@/components/sections/blocks/reservation-section";

function fmtPrice(value: number, currency: string): string {
  const n = Number.isFinite(value) ? value : 0;
  return `${n.toLocaleString("tr-TR", { minimumFractionDigits: 0, maximumFractionDigits: 0 })} ${currency}`.trim();
}

function badges(room: RoomType): string[] {
  const out: string[] = [];
  if (room.capacity_max != null) {
    const min = room.capacity_min != null && room.capacity_min !== room.capacity_max ? `${room.capacity_min}-` : "";
    out.push(`👥 ${min}${room.capacity_max} kişi`);
  }
  if (room.size_m2 != null) out.push(`📐 ${room.size_m2} m²`);
  if (room.bedrooms != null) out.push(`🛏 ${room.bedrooms} yatak odası`);
  if (room.bathrooms != null) out.push(`🚿 ${room.bathrooms} banyo`);
  return out;
}

export async function RoomDetailSection({ section }: { section: PageSection }) {
  const content = (section.content ?? {}) as Record<string, unknown>;
  const slug = str(content, "room_type");

  const rooms = await getRoomTypes();
  const room = slug ? rooms.find((r) => r.slug === slug) ?? null : null;

  if (!room) {
    return (
      <section style={{ padding: "5rem 1.5rem", textAlign: "center" }}>
        <p style={{ color: "var(--color-text-soft, #9ca3af)", fontSize: "1rem" }}>
          Bu oda tipi bulunamadı veya yayından kaldırılmış.
        </p>
      </section>
    );
  }

  const price = fmtPrice(room.base_price, room.currency ?? "");
  const images = room.images ?? [];
  const chips = badges(room);

  // Odaya kilitli rezervasyon formu — picker gizli, oda content ile sabit.
  const reservationSection = {
    ...section,
    id: `${section.id}-rezervasyon`,
    type: "reservation",
    variation: "",
    render_mode: "component" as const,
    content: {
      room_type: room.slug,
      show_room_picker: "0",
      title: str(content, "reserve_title") || "Rezervasyon Talebi",
      subtitle: `${room.name} için tarihlerinizi seçin`,
    },
  } as PageSection;

  return (
    <>
      {/* ── Kahraman: galeri + bilgi ─────────────────────────────────── */}
      <section style={{ padding: "3.5rem 1.5rem 1rem", background: "var(--color-bg, #fff)" }}>
        <div style={{ maxWidth: "var(--container-width, 1200px)", margin: "0 auto" }}>
          <div
            style={{
              display: "grid",
              gridTemplateColumns: "repeat(auto-fit, minmax(320px, 1fr))",
              gap: "2.5rem",
              alignItems: "start",
            }}
          >
            {/* Galeri */}
            <div style={{ display: "flex", flexDirection: "column", gap: ".75rem" }}>
              <div
                style={{
                  aspectRatio: "4 / 3",
                  borderRadius: "var(--radius-card, 1rem)",
                  overflow: "hidden",
                  background: "var(--color-surface, #f3f4f6)",
                  display: "flex",
                  alignItems: "center",
                  justifyContent: "center",
                }}
              >
                {images[0] ? (
                  // eslint-disable-next-line @next/next/no-img-element
                  <img
                    src={images[0]}
                    alt={room.name}
                    style={{ width: "100%", height: "100%", objectFit: "cover", display: "block" }}
                  />
                ) : (
                  <span style={{ fontSize: "3rem", color: "var(--color-text-soft, #cbd5e1)" }}>🏢</span>
                )}
              </div>

              {images.length > 1 && (
                <div style={{ display: "grid", gridTemplateColumns: "repeat(4, 1fr)", gap: ".6rem" }}>
                  {images.slice(1, 5).map((img, i) => (
                    <div
                      key={i}
                      style={{
                        aspectRatio: "1 / 1",
                        borderRadius: "var(--radius-button, .6rem)",
                        overflow: "hidden",
                        background: "var(--color-surface, #f3f4f6)",
                      }}
                    >
                      {/* eslint-disable-next-line @next/next/no-img-element */}
                      <img
                        src={img}
                        alt={`${room.name} ${i + 2}`}
                        style={{ width: "100%", height: "100%", objectFit: "cover", display: "block" }}
                      />
                    </div>
                  ))}
                </div>
              )}
            </div>

            {/* Bilgi */}
            <div style={{ display: "flex", flexDirection: "column", gap: "1.1rem" }}>
              <h1
                style={{
                  fontSize: "2rem",
                  fontWeight: 700,
                  color: "var(--color-heading, #111827)",
                  margin: 0,
                  lineHeight: 1.15,
                }}
              >
                {room.name}
              </h1>

              {room.summary && (
                <p style={{ fontSize: "1.05rem", color: "var(--color-text-soft, #6b7280)", margin: 0, lineHeight: 1.6 }}>
                  {room.summary}
                </p>
              )}

              {chips.length > 0 && (
                <div style={{ display: "flex", flexWrap: "wrap", gap: ".9rem", fontSize: ".92rem", color: "var(--color-text, #374151)" }}>
                  {chips.map((b) => (
                    <span
                      key={b}
                      style={{
                        display: "inline-flex",
                        alignItems: "center",
                        padding: ".4rem .8rem",
                        background: "var(--color-surface, #f9fafb)",
                        border: "1px solid var(--color-border, #e5e7eb)",
                        borderRadius: "var(--radius-button, .5rem)",
                      }}
                    >
                      {b}
                    </span>
                  ))}
                </div>
              )}

              <div style={{ display: "flex", alignItems: "baseline", gap: ".5rem", marginTop: ".3rem" }}>
                <span style={{ fontSize: "1.75rem", fontWeight: 800, color: "var(--color-heading, #111827)" }}>{price}</span>
                <span style={{ fontSize: ".85rem", color: "var(--color-text-soft, #9ca3af)" }}>/ gecelik başlangıç</span>
              </div>

              <a
                href="#oda-rezervasyon"
                style={{
                  alignSelf: "flex-start",
                  marginTop: ".4rem",
                  display: "inline-flex",
                  alignItems: "center",
                  padding: ".85rem 1.6rem",
                  background: "var(--color-primary, #6366f1)",
                  color: "#fff",
                  fontSize: "1rem",
                  fontWeight: 600,
                  borderRadius: "var(--radius-button, .5rem)",
                  textDecoration: "none",
                }}
              >
                Rezervasyon Yap
              </a>
            </div>
          </div>
        </div>
      </section>

      {/* ── Açıklama + olanaklar ─────────────────────────────────────── */}
      {(room.description || (room.amenities && room.amenities.length > 0)) && (
        <section style={{ padding: "2rem 1.5rem", background: "var(--color-bg, #fff)" }}>
          <div
            style={{
              maxWidth: "var(--container-width, 1200px)",
              margin: "0 auto",
              display: "grid",
              gridTemplateColumns: "repeat(auto-fit, minmax(280px, 1fr))",
              gap: "2.5rem",
            }}
          >
            {room.description && (
              <div>
                <h2 style={{ fontSize: "1.3rem", fontWeight: 700, color: "var(--color-heading, #111827)", marginTop: 0, marginBottom: ".9rem" }}>
                  Genel Bilgi
                </h2>
                <p style={{ fontSize: "1rem", color: "var(--color-text, #374151)", lineHeight: 1.75, margin: 0 }}>
                  {room.description}
                </p>
              </div>
            )}

            {room.amenities && room.amenities.length > 0 && (
              <div>
                <h2 style={{ fontSize: "1.3rem", fontWeight: 700, color: "var(--color-heading, #111827)", marginTop: 0, marginBottom: ".9rem" }}>
                  Olanaklar
                </h2>
                <ul
                  style={{
                    listStyle: "none",
                    margin: 0,
                    padding: 0,
                    display: "grid",
                    gridTemplateColumns: "repeat(auto-fill, minmax(160px, 1fr))",
                    gap: ".65rem",
                  }}
                >
                  {room.amenities.map((a) => (
                    <li key={a} style={{ display: "flex", alignItems: "center", gap: ".5rem", fontSize: ".95rem", color: "var(--color-text, #374151)" }}>
                      <span style={{ color: "var(--color-primary, #6366f1)", fontWeight: 700 }}>✓</span>
                      {a}
                    </li>
                  ))}
                </ul>
              </div>
            )}
          </div>
        </section>
      )}

      {/* ── Odaya kilitli rezervasyon formu ──────────────────────────── */}
      <div id="oda-rezervasyon">
        <ReservationSection section={reservationSection} />
      </div>
    </>
  );
}
