"use client";

/**
 * Daire detay bölümü — type: "daire-detay" / "room-detail".
 *
 * TEMA-TOKEN'LI (jenerik): renk/font tenant chrome'unun :root değişkenlerinden gelir
 * (--color-*, --font-heading, --surface-soft, --placeholder-grad, --wa). Fallback'ler
 * otelvatan (altın/Cormorant) değerleridir → otelvatan chrome'u değiştirmeden aynı görünür;
 * dagkent chrome yeşil/Bricolage override eder. Tek build tüm tenant'lara hizmet eder.
 *
 * content.room_type slug'ına göre Lodging RoomType'ını çeker; sağ sticky kartta TEK AKIŞLI
 * TAKVİM ile müsaitlik (dolu günler /api/v1/lodging/availability'den) + yetişkin/çocuk + tahmini tutar.
 */
import { useEffect, useMemo, useState } from "react";
import type { SectionBlockProps } from "@/lib/sections/component-registry";
import { str } from "@/lib/sections/component-registry";

type RoomType = {
  slug: string; name: string; summary: string | null; description: string | null;
  capacity_min: number; capacity_max: number; size_m2: number | null;
  bedrooms: number; bathrooms: number; base_price: number; currency: string;
  unit_count: number; amenities: string[]; images: string[];
};

// Tema değişkenleri (fallback = otelvatan)
const C = {
  bg: "var(--color-bg, #FAF4E9)",
  surface: "var(--color-surface, #ffffff)",
  soft: "var(--surface-soft, #FCF8EF)",
  heading: "var(--color-heading, #2A2521)",
  textSoft: "var(--color-text-soft, #6B6053)",
  primary: "var(--color-primary, #B8893F)",
  border: "var(--color-border, rgba(42,37,33,0.16))",
  fontH: "var(--font-heading, 'Cormorant Garamond', serif)",
  grad: "var(--placeholder-grad, linear-gradient(120deg,#EFE7D3,#DCCCA5 55%,#C9B37E))",
  wa: "var(--wa, #25D366)",
};

const pad = (n: number) => (n < 10 ? "0" + n : String(n));
const ymd = (d: Date) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
const addDays = (d: Date, n: number) => { const x = new Date(d); x.setDate(x.getDate() + n); return x; };
const nightsBetween = (a: string, b: string): number => {
  if (!a || !b) return 0;
  const ms = new Date(b).getTime() - new Date(a).getTime();
  return ms > 0 ? Math.round(ms / 86_400_000) : 0;
};
const tl = (n: number) => "₺" + n.toLocaleString("tr-TR");
const MONTHS = ["Ocak", "Şubat", "Mart", "Nisan", "Mayıs", "Haziran", "Temmuz", "Ağustos", "Eylül", "Ekim", "Kasım", "Aralık"];
const WK = ["Pt", "Sa", "Ça", "Pe", "Cu", "Ct", "Pz"];

function specCard(label: string, value: string) {
  return (
    <div style={{ background: C.soft, border: `1px solid ${C.border}`, padding: "16px 14px", textAlign: "center" }}>
      <div style={{ fontFamily: C.fontH, fontWeight: 700, fontSize: "22px", color: C.heading }}>{value}</div>
      <div style={{ fontSize: "11px", letterSpacing: ".08em", color: C.textSoft, textTransform: "uppercase", marginTop: "3px" }}>{label}</div>
    </div>
  );
}

function Counter({ label, value, min, max, onChange }: { label: string; value: number; min: number; max: number; onChange: (n: number) => void }) {
  const btn: React.CSSProperties = { width: "28px", height: "28px", border: `1px solid ${C.border}`, background: C.soft, color: C.primary, fontSize: "16px", cursor: "pointer", display: "flex", alignItems: "center", justifyContent: "center" };
  return (
    <div>
      <div style={{ fontSize: "11px", fontWeight: 700, letterSpacing: ".06em", textTransform: "uppercase", color: C.textSoft, marginBottom: "6px" }}>{label}</div>
      <div style={{ display: "flex", alignItems: "center", gap: "10px" }}>
        <button type="button" aria-label="Azalt" onClick={() => onChange(Math.max(min, value - 1))} style={btn}>−</button>
        <span style={{ fontSize: "15px", fontWeight: 700, color: C.heading, minWidth: "18px", textAlign: "center" }}>{value}</span>
        <button type="button" aria-label="Arttır" onClick={() => onChange(Math.min(max, value + 1))} style={btn}>+</button>
      </div>
    </div>
  );
}

function RangeCalendar({
  booked, checkin, checkout, onPick, showBooked = true,
}: {
  booked: Set<string>; checkin: string; checkout: string; onPick: (d: string) => void; showBooked?: boolean;
}) {
  const today = useMemo(() => { const t = new Date(); t.setHours(0, 0, 0, 0); return t; }, []);
  const [view, setView] = useState(() => new Date(today.getFullYear(), today.getMonth(), 1));

  const first = new Date(view.getFullYear(), view.getMonth(), 1);
  const daysInMonth = new Date(view.getFullYear(), view.getMonth() + 1, 0).getDate();
  const offset = (first.getDay() + 6) % 7;
  const todayStr = ymd(today);
  const atCurrentMonth = view.getFullYear() === today.getFullYear() && view.getMonth() === today.getMonth();

  const cells: (number | null)[] = [];
  for (let i = 0; i < offset; i++) cells.push(null);
  for (let d = 1; d <= daysInMonth; d++) cells.push(d);

  const arrowStyle: React.CSSProperties = {
    width: "30px", height: "30px", border: `1px solid ${C.border}`, background: C.soft,
    display: "flex", alignItems: "center", justifyContent: "center", cursor: "pointer", color: C.primary, fontSize: "16px",
  };

  return (
    <div style={{ border: `1px solid ${C.border}`, padding: "12px 12px 8px", marginBottom: "12px" }}>
      <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginBottom: "10px" }}>
        <button type="button" aria-label="Önceki ay" disabled={atCurrentMonth}
          onClick={() => setView(new Date(view.getFullYear(), view.getMonth() - 1, 1))}
          style={{ ...arrowStyle, opacity: atCurrentMonth ? 0.35 : 1, cursor: atCurrentMonth ? "not-allowed" : "pointer" }}>‹</button>
        <div style={{ fontFamily: C.fontH, fontWeight: 600, fontSize: "18px", color: C.heading }}>{MONTHS[view.getMonth()]} {view.getFullYear()}</div>
        <button type="button" aria-label="Sonraki ay"
          onClick={() => setView(new Date(view.getFullYear(), view.getMonth() + 1, 1))} style={arrowStyle}>›</button>
      </div>
      <div style={{ display: "grid", gridTemplateColumns: "repeat(7,1fr)", gap: "2px", marginBottom: "4px" }}>
        {WK.map((w) => <span key={w} style={{ textAlign: "center", fontSize: "10.5px", fontWeight: 700, color: C.textSoft, padding: "4px 0" }}>{w}</span>)}
      </div>
      <div style={{ display: "grid", gridTemplateColumns: "repeat(7,1fr)", gap: "2px" }}>
        {cells.map((d, i) => {
          if (d === null) return <span key={"e" + i} />;
          const ds = ymd(new Date(view.getFullYear(), view.getMonth(), d));
          const isPast = ds < todayStr;
          const isBooked = booked.has(ds);
          const isCheckin = ds === checkin;
          const isCheckout = ds === checkout;
          const inRange = !!checkin && !!checkout && ds > checkin && ds < checkout;
          const disabled = isPast || isBooked;
          let bg = "transparent";
          let color = C.heading;
          let extra: React.CSSProperties = {};
          if (isPast) { color = "#CFC7B8"; }
          else if (isBooked) { bg = "#F3DED2"; color = "#B4735A"; extra = { textDecoration: "line-through" }; }
          else if (isCheckin || isCheckout) { bg = C.heading; color = "#fff"; }
          else if (inRange) { bg = C.soft; }
          return (
            <button key={ds} type="button" disabled={disabled} onClick={() => onPick(ds)}
              title={isBooked ? "Dolu" : undefined}
              style={{ height: "34px", border: "none", background: bg, color, fontSize: "13px", fontWeight: (isCheckin || isCheckout) ? 700 : 400,
                cursor: disabled ? "not-allowed" : "pointer", ...extra }}>{d}</button>
          );
        })}
      </div>
      <div style={{ display: "flex", flexWrap: "wrap", gap: "12px", marginTop: "10px", paddingTop: "8px", borderTop: `1px solid ${C.border}`, fontSize: "11.5px", color: C.textSoft }}>
        {showBooked && <span style={{ display: "inline-flex", alignItems: "center", gap: "5px" }}><span style={{ width: "12px", height: "12px", background: "#F3DED2" }} />Dolu</span>}
        <span style={{ display: "inline-flex", alignItems: "center", gap: "5px" }}><span style={{ width: "12px", height: "12px", background: C.heading }} />Seçili</span>
      </div>
    </div>
  );
}

export function RoomDetailSection({ section }: SectionBlockProps) {
  const c = (section.content ?? {}) as Record<string, unknown>;
  const wantSlug = str(c, "room_type");
  const eyebrow = str(c, "eyebrow") || "DAİRE DETAYI";
  const checkinTime = str(c, "checkin_time") || "14:00";
  const checkoutTime = str(c, "checkout_time") || "12:00";

  const [rooms, setRooms] = useState<RoomType[]>([]);
  const [loaded, setLoaded] = useState(false);
  // Müsaitlik motoru. false = vitrin modu: dolu-gün/fiyat hesabı yapılmaz,
  // yalnızca tarih aralığı + kişi sayısı seçilir, forma yönlendirilir.
  const [enabled, setEnabled] = useState(true);
  const [booked, setBooked] = useState<Set<string>>(new Set());
  const [checkin, setCheckin] = useState("");
  const [checkout, setCheckout] = useState("");
  const [adults, setAdults] = useState(2);
  const [children, setChildren] = useState(0);

  useEffect(() => {
    let alive = true;
    fetch("/api/v1/lodging/room-types", { headers: { Accept: "application/json" } })
      .then((r) => r.json())
      .then((d) => {
        if (!alive) return;
        if (Array.isArray(d?.data)) setRooms(d.data as RoomType[]);
        setEnabled(d?.settings?.availability_enabled !== false); // settings yoksa açık varsay
        setLoaded(true);
      })
      .catch(() => { if (alive) setLoaded(true); });
    return () => { alive = false; };
  }, []);

  const room = rooms.find((r) => r.slug === wantSlug) ?? rooms[0] ?? null;

  useEffect(() => {
    // Vitrin modunda müsaitlik HESAPLANMAZ → dolu-gün sorgusu atlanır.
    if (!room || !enabled) return;
    let alive = true;
    const now = new Date(); now.setHours(0, 0, 0, 0);
    const url = `/api/v1/lodging/availability?room_type=${encodeURIComponent(room.slug)}&from=${ymd(now)}&to=${ymd(addDays(now, 120))}`;
    fetch(url, { headers: { Accept: "application/json" } })
      .then((r) => r.json())
      .then((d) => { if (alive && Array.isArray(d?.booked)) setBooked(new Set(d.booked as string[])); })
      .catch(() => {});
    return () => { alive = false; };
  }, [room?.slug, enabled]);

  // Kapasite (doluluk): toplam misafir (yetişkin+çocuk) odanın capacity_max'ını
  // aşamaz. Oda yüklenince fazla geleni kırp (önce çocuk, sonra yetişkin; min 1).
  useEffect(() => {
    if (!room) return;
    const capMax = room.capacity_max ?? 30;
    if (adults + children <= capMax) return;
    const a = Math.max(1, Math.min(adults, capMax));
    setAdults(a);
    setChildren(Math.max(0, capMax - a));
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [room?.slug]);

  function pickDay(ds: string) {
    if (!checkin || (checkin && checkout)) { setCheckin(ds); setCheckout(""); return; }
    if (ds <= checkin) { setCheckin(ds); setCheckout(""); return; }
    setCheckout(ds);
  }

  const nights = nightsBetween(checkin, checkout);
  const estTotal = room && nights > 0 ? room.base_price * nights : null;

  const rangeHasBooked = useMemo(() => {
    if (!checkin || !checkout) return false;
    for (let d = new Date(checkin); ymd(d) < checkout; d = addDays(d, 1)) {
      if (booked.has(ymd(d))) return true;
    }
    return false;
  }, [checkin, checkout, booked]);

  if (!room) {
    return (
      <section style={{ background: C.bg, padding: "clamp(90px,12vw,140px) 24px 80px", textAlign: "center", color: C.textSoft }}>
        {loaded ? "Daire bulunamadı." : "Yükleniyor…"}
      </section>
    );
  }

  const cap = room.capacity_min === room.capacity_max ? `${room.capacity_max} kişi` : `${room.capacity_min}-${room.capacity_max} kişi`;
  const phone = str(c, "phone") || "905415884959";              // fallback = otelvatan
  const waNum = str(c, "whatsapp") || phone;
  const reserveHref =
    `/rezervasyon?oda=${encodeURIComponent(room.slug)}` +
    (checkin ? `&giris=${checkin}` : "") + (checkout ? `&cikis=${checkout}` : "") +
    `&yetiskin=${adults}&cocuk=${children}`;
  const reserveLabel = enabled ? "Müsaitlik & Talep Et" : "Rezervasyon Talebi Oluştur";
  const wa = `https://wa.me/${waNum}?text=${encodeURIComponent(`Merhaba, ${room.name} için müsaitlik ve rezervasyon bilgisi almak istiyorum.`)}`;

  return (
    <section style={{ background: C.bg, padding: "clamp(90px,12vw,140px) 24px 72px" }}>
      <div style={{ maxWidth: "1200px", margin: "0 auto" }}>
        <a href="/daireler" style={{ display: "inline-flex", alignItems: "center", gap: "6px", fontSize: "13.5px", fontWeight: 600, color: C.primary, marginBottom: "22px" }}>‹ Tüm Daireler</a>

        <div style={{ display: "grid", gridTemplateColumns: "2fr 1fr", gap: "12px", marginBottom: "34px" }}>
          <div style={{ position: "relative", aspectRatio: "16/10", background: C.grad }}>
            <span style={{ position: "absolute", left: "18px", top: "18px", background: C.heading, color: "#fff", fontSize: "11px", fontWeight: 700, letterSpacing: ".1em", padding: "7px 13px" }}>{room.unit_count} ADET MEVCUT</span>
          </div>
          <div style={{ display: "grid", gridTemplateRows: "1fr 1fr", gap: "12px" }}>
            <div style={{ background: C.grad }} />
            <div style={{ background: C.grad }} />
          </div>
        </div>

        <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fit,minmax(300px,1fr))", gap: "40px", alignItems: "start" }}>
          <div>
            <div style={{ fontSize: "11.5px", fontWeight: 700, letterSpacing: ".24em", color: C.primary, marginBottom: "12px" }}>{eyebrow}</div>
            <h1 style={{ fontFamily: C.fontH, fontWeight: 700, fontSize: "clamp(2.2rem,4.5vw,3.2rem)", color: C.heading, margin: "0 0 22px" }}>{room.name}</h1>

            <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fit,minmax(120px,1fr))", gap: "12px", marginBottom: "28px" }}>
              {room.size_m2 ? specCard("Büyüklük", `${room.size_m2} m²`) : null}
              {specCard("Kapasite", cap)}
              {specCard("Yatak Odası", String(room.bedrooms))}
              {specCard("Banyo", String(room.bathrooms))}
            </div>

            {room.description && <p style={{ fontSize: "15.5px", lineHeight: 1.75, color: C.textSoft, margin: "0 0 28px" }}>{room.description}</p>}

            {room.amenities.length > 0 && (
              <>
                <h3 style={{ fontFamily: C.fontH, fontWeight: 700, fontSize: "22px", color: C.heading, margin: "0 0 14px" }}>Olanaklar</h3>
                <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fit,minmax(160px,1fr))", gap: "12px 18px", marginBottom: "30px" }}>
                  {room.amenities.map((a) => (
                    <span key={a} style={{ fontSize: "14px", color: C.textSoft, display: "flex", alignItems: "center", gap: "9px" }}>
                      <span style={{ width: "26px", height: "26px", background: C.soft, display: "inline-flex", alignItems: "center", justifyContent: "center", color: C.primary, flexShrink: 0 }}>✓</span>{a}
                    </span>
                  ))}
                </div>
              </>
            )}

            <div style={{ background: C.heading, color: "rgba(255,255,255,0.85)", padding: "24px 26px" }}>
              <h3 style={{ fontFamily: C.fontH, fontWeight: 700, fontSize: "20px", color: "#fff", margin: "0 0 14px" }}>Konaklama Kuralları</h3>
              <ul style={{ listStyle: "none", margin: 0, padding: 0, display: "grid", gap: "10px", fontSize: "14px" }}>
                <li>Giriş: {checkinTime} · Çıkış: {checkoutTime}</li>
                <li>Evcil hayvan kabul edilmemektedir.</li>
                <li>Tüm dairelerimiz sigara içilmeyen alandır.</li>
              </ul>
            </div>
          </div>

          <aside style={{ position: "sticky", top: "94px", background: C.surface, border: `1px solid ${C.border}`, boxShadow: "0 14px 40px rgba(0,0,0,0.08)" }}>
            <div style={{ background: C.soft, padding: "18px 24px" }}>
              <div style={{ display: "flex", alignItems: "baseline", gap: "6px" }}>
                <span style={{ fontFamily: C.fontH, fontWeight: 700, fontSize: "30px", color: C.heading }}>{tl(room.base_price)}</span>
                <span style={{ fontSize: "14px", color: C.textSoft }}>/ gece</span>
              </div>
            </div>
            <div style={{ padding: "18px 20px 22px" }}>
              <div style={{ fontSize: "12.5px", color: C.textSoft, marginBottom: "8px" }}>
                {!checkin ? "Giriş tarihini seçin" : !checkout ? "Çıkış tarihini seçin" : `${checkin} → ${checkout}`}
              </div>

              <RangeCalendar booked={booked} checkin={checkin} checkout={checkout} onPick={pickDay} showBooked={enabled} />

              <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: "12px", marginBottom: "6px" }}>
                <Counter label="Yetişkin" value={adults} min={1} max={Math.max(1, room.capacity_max - children)} onChange={setAdults} />
                <Counter label="Çocuk" value={children} min={0} max={Math.max(0, room.capacity_max - adults)} onChange={setChildren} />
              </div>
              <div style={{ fontSize: "11.5px", color: C.textSoft, marginBottom: "12px" }}>
                Bu daire en fazla <strong>{room.capacity_max} misafir</strong> alır.
              </div>

              {/* Tahmini tutar — yalnızca müsaitlik açıkken (vitrin modunda hesaplama yok) */}
              {enabled && nights > 0 && (
                <div style={{ background: C.soft, padding: "12px 14px", marginBottom: "12px", fontSize: "14px", color: C.heading }}>
                  <div style={{ display: "flex", justifyContent: "space-between" }}><span>{nights} gece</span><strong>{estTotal !== null ? tl(estTotal) : "—"}</strong></div>
                  <div style={{ fontSize: "11.5px", color: C.textSoft, marginTop: "4px" }}>Tahmini tutar — kesin fiyat için sizinle iletişime geçeceğiz.</div>
                </div>
              )}

              {/* Müsaitlik durumu — yalnızca müsaitlik açıkken */}
              {enabled && (
                <div style={{ minHeight: "18px", marginBottom: "12px" }}>
                  {nights > 0 && (rangeHasBooked
                    ? <div style={{ fontSize: "13.5px", fontWeight: 700, color: "#B4735A" }}>Seçtiğiniz aralıkta dolu gün var. Yine de talep gönderebilirsiniz.</div>
                    : <div style={{ fontSize: "13.5px", fontWeight: 700, color: "#3B7A57" }}>✓ Seçtiğiniz tarihler müsait görünüyor.</div>)}
                </div>
              )}

              {(checkin || checkout) && (
                <button type="button" onClick={() => { setCheckin(""); setCheckout(""); }}
                  style={{ background: "none", border: "none", cursor: "pointer", fontSize: "12.5px", fontWeight: 700, color: C.primary, padding: "0 0 12px" }}>Tarihleri temizle</button>
              )}

              <a href={reserveHref} style={{ display: "block", textAlign: "center", background: C.heading, color: "#fff", fontSize: "15px", fontWeight: 700, padding: "15px", marginBottom: "10px", textDecoration: "none" }}>{reserveLabel}</a>
              <a href={`tel:+${phone}`} style={{ display: "block", textAlign: "center", background: C.surface, color: C.heading, border: `1px solid ${C.border}`, fontSize: "14px", fontWeight: 700, padding: "13px", marginBottom: "10px", textDecoration: "none" }}>Hemen Ara</a>
              <a href={wa} target="_blank" rel="noopener" style={{ display: "block", textAlign: "center", background: C.wa, color: "#fff", fontSize: "14px", fontWeight: 700, padding: "13px", textDecoration: "none" }}>WhatsApp ile Sor</a>
            </div>
          </aside>
        </div>
      </div>
    </section>
  );
}
