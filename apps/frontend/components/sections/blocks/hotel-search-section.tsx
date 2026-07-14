"use client";

/**
 * HotelSearchSection — Konaklama ana sayfa arama kutusu. type: "konaklama-arama"
 * (aliaslar: reservation-search / hotel-search / rezervasyon-arama).
 *
 * ÇOK-SİTELİ + TEMA-TOKEN: renk/font CSS değişkenlerinden (her site kendi
 * chrome'uyla), düzen/etiket/hedef içerik alanlarından (per-site tasarım esnek).
 *
 * Özellikler:
 *   • Tek date-range input — popover takvim (RangeCalendar), GEÇMİŞ GÜNLER PASİF.
 *   • Daire tipi seçici (API'den, capacity_max ile).
 *   • Kapasiteli misafir sayacı (yetişkin+çocuk ≤ seçili/en büyük dairenin capacity_max).
 *   • "Ara" → action_target'a (varsayılan /rezervasyon) ?giris=&cikis=&oda=&yetiskin=&cocuk=.
 *
 * content (hepsi opsiyonel): eyebrow, title, date_label, roomtype_label,
 *   adults_label, children_label, search_label, action_target, overlap(px, hero
 *   üstüne bindirme), any_room_label.
 */
import { useEffect, useMemo, useRef, useState } from "react";
import type { PageSection } from "@/lib/types";
import { str } from "@/lib/sections/component-registry";
import { RangeCalendar } from "@/components/lodging/range-calendar";

type RoomType = { slug: string; name: string; capacity_max: number };
type Props = { section: PageSection; lang?: string };

const fmtDate = (s: string) => {
  if (!s) return "";
  const d = new Date(s);
  return Number.isNaN(d.getTime()) ? "" : d.toLocaleDateString("tr-TR", { day: "numeric", month: "short" });
};

export function HotelSearchSection({ section }: Props) {
  const c = (section.content ?? {}) as Record<string, unknown>;
  const eyebrow = str(c, "eyebrow");
  const title = str(c, "title");
  const dateLabel = str(c, "date_label") || "GİRİŞ — ÇIKIŞ";
  const roomLabel = str(c, "roomtype_label") || "DAİRE TİPİ";
  const adultsLabel = str(c, "adults_label") || "YETİŞKİN";
  const childrenLabel = str(c, "children_label") || "ÇOCUK";
  const searchLabel = str(c, "search_label") || "Müsaitlik Ara";
  const anyRoomLabel = str(c, "any_room_label") || "Farketmez";
  const target = str(c, "action_target") || "/rezervasyon";
  const overlapRaw = parseInt(str(c, "overlap", ""), 10);
  const overlap = Number.isFinite(overlapRaw) ? overlapRaw : 0;

  const [rooms, setRooms] = useState<RoomType[]>([]);
  // null = henüz bilinmiyor (yüklenirken gösterme), true = müsaitlik açık,
  // false = vitrin modu → arama kutusu tamamen gizli.
  const [enabled, setEnabled] = useState<boolean | null>(null);
  const [roomSlug, setRoomSlug] = useState("");
  const [checkin, setCheckin] = useState("");
  const [checkout, setCheckout] = useState("");
  const [adults, setAdults] = useState(2);
  const [children, setChildren] = useState(0);
  const [calOpen, setCalOpen] = useState(false);
  const dateRef = useRef<HTMLDivElement | null>(null);

  useEffect(() => {
    let alive = true;
    fetch("/api/v1/lodging/room-types", { headers: { Accept: "application/json" } })
      .then((r) => r.json())
      .then((d) => {
        if (!alive) return;
        if (Array.isArray(d?.data)) setRooms(d.data as RoomType[]);
        // settings yoksa (eski API) müsaitlik AÇIK varsay → geriye dönük uyumlu.
        setEnabled(d?.settings?.availability_enabled !== false);
      })
      .catch(() => { if (alive) setEnabled(true); }); // API hatasında mevcut davranışı koru (açık).
    return () => { alive = false; };
  }, []);

  // Takvim popover'ı dışarı tıklanınca kapansın.
  useEffect(() => {
    if (!calOpen) return;
    function onDoc(e: MouseEvent) {
      if (dateRef.current && !dateRef.current.contains(e.target as Node)) setCalOpen(false);
    }
    document.addEventListener("mousedown", onDoc);
    return () => document.removeEventListener("mousedown", onDoc);
  }, [calOpen]);

  const selected = useMemo(() => rooms.find((r) => r.slug === roomSlug) ?? null, [rooms, roomSlug]);
  const largestCap = useMemo(() => rooms.reduce((m, r) => Math.max(m, r.capacity_max ?? 0), 0), [rooms]);
  const capMax = selected?.capacity_max ?? (largestCap || 12);

  // Kapasite düşünce misafiri kırp (önce çocuk, sonra yetişkin; min 1 yetişkin).
  useEffect(() => {
    if (adults + children <= capMax) return;
    const a = Math.max(1, Math.min(adults, capMax));
    setAdults(a);
    setChildren(Math.max(0, capMax - a));
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [capMax]);

  function pickDay(ds: string) {
    if (!checkin || (checkin && checkout)) { setCheckin(ds); setCheckout(""); return; }
    if (ds <= checkin) { setCheckin(ds); setCheckout(""); return; }
    setCheckout(ds);
    setCalOpen(false); // aralık tamamlandı
  }

  function search() {
    const q: string[] = [];
    if (checkin) q.push("giris=" + encodeURIComponent(checkin));
    if (checkout) q.push("cikis=" + encodeURIComponent(checkout));
    if (roomSlug) q.push("oda=" + encodeURIComponent(roomSlug));
    q.push("yetiskin=" + adults);
    q.push("cocuk=" + children);
    window.location.href = `${target}${q.length ? "?" + q.join("&") : ""}`;
  }

  const dateText = checkin
    ? (checkout ? `${fmtDate(checkin)} → ${fmtDate(checkout)}` : `${fmtDate(checkin)} → …`)
    : "";

  const cell: React.CSSProperties = {
    background: "var(--color-surface, #fff)", padding: "18px 20px",
    display: "flex", flexDirection: "column", gap: "8px", justifyContent: "center", minWidth: 0,
  };
  const capLabel: React.CSSProperties = {
    fontSize: "10.5px", fontWeight: 700, letterSpacing: ".16em",
    color: "var(--color-primary, #B8893F)", textTransform: "uppercase",
  };
  const value: React.CSSProperties = { fontSize: "14.5px", fontWeight: 600, color: "var(--color-heading, #2A2521)" };
  const stepBtn: React.CSSProperties = {
    width: "26px", height: "26px", border: "1px solid var(--color-border, rgba(42,37,33,0.18))",
    background: "var(--color-bg, #FCF8EF)", color: "var(--color-heading, #6E5223)",
    fontSize: "16px", cursor: "pointer", display: "flex", alignItems: "center", justifyContent: "center",
    borderRadius: "var(--radius-button, 4px)",
  };

  function Stepper({ label, val, onDec, onInc, incDisabled }: { label: string; val: number; onDec: () => void; onInc: () => void; incDisabled: boolean }) {
    return (
      <div style={cell}>
        <span style={capLabel}>{label}</span>
        <div style={{ display: "flex", alignItems: "center", gap: "12px" }}>
          <button type="button" aria-label="Azalt" onClick={onDec} style={stepBtn}>−</button>
          <span style={{ ...value, minWidth: "16px", textAlign: "center" }}>{val}</span>
          <button type="button" aria-label="Arttır" onClick={onInc} disabled={incDisabled}
            style={{ ...stepBtn, opacity: incDisabled ? 0.35 : 1, cursor: incDisabled ? "not-allowed" : "pointer" }}>+</button>
        </div>
      </div>
    );
  }

  const atCap = adults + children >= capMax;

  // Vitrin modu (veya henüz yüklenmedi) → arama kutusunu hiç gösterme.
  if (enabled !== true) return null;

  return (
    <section style={{ maxWidth: "1150px", margin: `${overlap ? -overlap : 0}px auto 0`, padding: "0 24px", position: "relative", zIndex: 20 }}>
      {(eyebrow || title) && (
        <div style={{ textAlign: "center", marginBottom: "1.25rem" }}>
          {eyebrow && <div style={{ fontSize: ".8rem", fontWeight: 700, letterSpacing: ".16em", textTransform: "uppercase", color: "var(--color-primary, #B8893F)", marginBottom: ".4rem" }}>{eyebrow}</div>}
          {title && <h2 style={{ margin: 0, fontSize: "1.9rem", fontWeight: 700, color: "var(--color-heading, #2A2521)", fontFamily: "var(--font-heading, inherit)" }}>{title}</h2>}
        </div>
      )}

      <div style={{
        background: "var(--color-border, rgba(42,37,33,0.1))",
        boxShadow: "0 26px 64px rgba(0,0,0,0.14)",
        display: "grid", gridTemplateColumns: "repeat(auto-fit, minmax(150px, 1fr))", gap: "1px",
      }}>
        {/* Tek date-range input → popover takvim */}
        <div ref={dateRef} style={{ ...cell, position: "relative", cursor: "pointer" }}>
          <span style={capLabel}>{dateLabel}</span>
          <button type="button" onClick={() => setCalOpen((o) => !o)}
            style={{ ...value, background: "none", border: "none", padding: 0, textAlign: "left", cursor: "pointer", fontFamily: "inherit", color: dateText ? "var(--color-heading, #2A2521)" : "var(--color-text-soft, #9ca3af)" }}>
            {dateText || "Tarih seçin"}
          </button>
          {calOpen && (
            <div style={{
              position: "absolute", top: "calc(100% + 8px)", left: 0, zIndex: 40,
              background: "var(--color-surface, #fff)", border: "1px solid var(--color-border, #e5e7eb)",
              boxShadow: "0 20px 50px rgba(0,0,0,0.18)", padding: "16px", width: "min(340px, 86vw)",
              borderRadius: "var(--radius-card, 8px)",
            }}>
              <RangeCalendar checkin={checkin} checkout={checkout} onPick={pickDay} />
              {(checkin || checkout) && (
                <button type="button" onClick={() => { setCheckin(""); setCheckout(""); }}
                  style={{ marginTop: "8px", background: "none", border: "none", cursor: "pointer", fontSize: "12px", fontWeight: 700, color: "var(--color-primary, #9A6E2E)", padding: 0 }}>
                  Temizle
                </button>
              )}
            </div>
          )}
        </div>

        {/* Daire tipi */}
        <div style={cell}>
          <span style={capLabel}>{roomLabel}</span>
          <select value={roomSlug} onChange={(e) => setRoomSlug(e.target.value)}
            style={{ ...value, border: "none", background: "none", padding: 0, cursor: "pointer", fontFamily: "inherit" }}>
            <option value="">{anyRoomLabel}</option>
            {rooms.map((r) => (
              <option key={r.slug} value={r.slug}>{r.name}{r.capacity_max ? ` (${r.capacity_max} kişi)` : ""}</option>
            ))}
          </select>
        </div>

        <Stepper label={adultsLabel} val={adults} incDisabled={atCap}
          onDec={() => setAdults((v) => Math.max(1, v - 1))}
          onInc={() => { if (!atCap) setAdults((v) => v + 1); }} />
        <Stepper label={childrenLabel} val={children} incDisabled={atCap}
          onDec={() => setChildren((v) => Math.max(0, v - 1))}
          onInc={() => { if (!atCap) setChildren((v) => v + 1); }} />

        <button type="button" onClick={search}
          style={{
            background: "var(--color-heading, #2A2521)", color: "#fff", border: "none", cursor: "pointer",
            fontSize: "14.5px", fontWeight: 700, letterSpacing: ".02em",
            display: "flex", alignItems: "center", justifyContent: "center", gap: "9px", padding: "20px", minHeight: "100%",
          }}>
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><circle cx="11" cy="11" r="8" /><path d="m21 21-4.3-4.3" /></svg>
          {searchLabel}
        </button>
      </div>
    </section>
  );
}
