"use client";

/**
 * Rezervasyon (Konaklama) bölümü — type: "reservation".
 *
 * Lodging modülünün tenant API'sini kullanır (same-origin relative fetch;
 * Traefik /api'yi Laravel'e yönlendirir, tenant domain'den çözülür):
 *   GET  /api/v1/lodging/room-types
 *   GET  /api/v1/lodging/availability?room_type=&from=&to=
 *   POST /api/v1/lodging/requests
 *
 * İçerik alanları (content, hepsi opsiyonel):
 *   title           — bölüm başlığı
 *   subtitle        — alt açıklama
 *   room_type       — ön seçili oda tipi slug'ı (boşsa "Farketmez")
 *   show_room_picker— "1"/"0": oda tipi seçici gösterilsin mi (varsayılan göster)
 *   cta_label       — gönder butonu metni
 *   success_message — özel teşekkür metni
 */
import { useEffect, useMemo, useState } from "react";
import type { PageSection } from "@/lib/types";
import { str } from "@/lib/sections/component-registry";

// Yalnızca `section`'a bağlı — registry (SectionBlockProps) tarafından da,
// oda-detay sayfasından da tek başına render edilebilsin diye dar prop tipi.
type ReservationSectionProps = { section: PageSection; lang?: string };

type RoomType = {
  slug: string;
  name: string;
  base_price: number;
  currency: string;
  capacity_min: number;
  capacity_max: number;
};

const todayStr = () => new Date().toISOString().slice(0, 10);
const nightsBetween = (a: string, b: string): number => {
  if (!a || !b) return 0;
  const ms = new Date(b).getTime() - new Date(a).getTime();
  return ms > 0 ? Math.round(ms / 86_400_000) : 0;
};

export function ReservationSection({ section }: ReservationSectionProps) {
  const c = (section.content ?? {}) as Record<string, unknown>;

  const title = str(c, "title") || "Rezervasyon Talebi";
  const subtitle = str(c, "subtitle");
  const ctaLabel = str(c, "cta_label") || "Talep Gönder";
  const successMessage = str(c, "success_message") || "Rezervasyon talebiniz alındı. En kısa sürede size dönüş yapacağız.";
  const presetRoom = str(c, "room_type");
  const showPicker = str(c, "show_room_picker") !== "0";

  const [roomTypes, setRoomTypes] = useState<RoomType[]>([]);
  const [roomSlug, setRoomSlug] = useState(presetRoom);
  const [checkin, setCheckin] = useState("");
  const [checkout, setCheckout] = useState("");
  const [adults, setAdults] = useState(2);
  const [children, setChildren] = useState(0);
  const [name, setName] = useState("");
  const [phone, setPhone] = useState("");
  const [email, setEmail] = useState("");
  const [message, setMessage] = useState("");
  const [honeypot, setHoneypot] = useState("");

  const [availabilityWarning, setAvailabilityWarning] = useState<string | null>(null);
  const [submitting, setSubmitting] = useState(false);
  const [done, setDone] = useState<{ code?: string } | null>(null);
  const [error, setError] = useState<string | null>(null);

  // Ön-doldurma: anasayfa arama widget'ı ve listeleme "Rezervasyon" butonu
  //   /rezervasyon?oda=&giris=&cikis=&yetiskin=&cocuk=
  // ile yönlendirir. Formu bu parametrelerden doldur (İngilizce eşanlamlıları da
  // kabul et). useSearchParams yerine window.location — Suspense sınırı / CSR
  // bailout gerektirmez; efekt yalnızca hidrasyondan sonra çalışır (SSR ile
  // ilk render defaults ⇒ hydration mismatch yok).
  useEffect(() => {
    if (typeof window === "undefined") return;
    const p = new URLSearchParams(window.location.search);
    const room = p.get("oda") || p.get("room_type");
    const gin = p.get("giris") || p.get("checkin");
    const gout = p.get("cikis") || p.get("checkout");
    const ad = p.get("yetiskin") || p.get("adults");
    const ch = p.get("cocuk") || p.get("children");

    // Oda: yalnızca sabit ön-seçim yoksa URL'den al. Oda-detay sayfası content
    // ile odayı kilitler (presetRoom dolu) → URL'yi yok say; /rezervasyon
    // sayfasında content boş → URL kazanır.
    if (room && !presetRoom) setRoomSlug(room);
    if (gin) setCheckin(gin);
    if (gout) setCheckout(gout);
    const adN = Number(ad);
    if (ad && Number.isFinite(adN) && adN >= 1) setAdults(Math.min(30, adN));
    const chN = Number(ch);
    if (ch && Number.isFinite(chN) && chN >= 0) setChildren(Math.min(30, chN));
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  // Load room types for the picker / price estimate.
  useEffect(() => {
    let alive = true;
    fetch("/api/v1/lodging/room-types", { headers: { Accept: "application/json" } })
      .then((r) => r.json())
      .then((d) => {
        if (alive && Array.isArray(d?.data)) setRoomTypes(d.data as RoomType[]);
      })
      .catch(() => {});
    return () => {
      alive = false;
    };
  }, []);

  const selectedRoom = useMemo(
    () => roomTypes.find((rt) => rt.slug === roomSlug) ?? null,
    [roomTypes, roomSlug],
  );

  // Kapasite (doluluk): belirli oda seçiliyse onun capacity_max'ı, "Farketmez"
  // ise en büyük dairenin kapasitesi. Toplam misafir (yetişkin+çocuk) bunu aşamaz.
  const largestCap = useMemo(
    () => roomTypes.reduce((m, rt) => Math.max(m, rt.capacity_max ?? 0), 0),
    [roomTypes],
  );
  const capMax = selectedRoom?.capacity_max ?? (largestCap || 30);

  // Kapasite düşünce (küçük oda seçilince veya URL'den fazla misafir gelince)
  // toplam misafiri kırp: önce çocuğu, sonra yetişkini (en az 1 yetişkin kalır).
  useEffect(() => {
    if (adults + children <= capMax) return;
    const a = Math.max(1, Math.min(adults, capMax));
    setAdults(a);
    setChildren(Math.max(0, capMax - a));
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [capMax]);

  const nights = nightsBetween(checkin, checkout);
  const estTotal = selectedRoom && nights > 0 ? selectedRoom.base_price * nights : null;

  // Light availability probe when a specific room + valid range is chosen.
  useEffect(() => {
    setAvailabilityWarning(null);
    if (!roomSlug || nights <= 0) return;

    let alive = true;
    const url = `/api/v1/lodging/availability?room_type=${encodeURIComponent(roomSlug)}&from=${checkin}&to=${checkout}`;
    fetch(url, { headers: { Accept: "application/json" } })
      .then((r) => r.json())
      .then((d) => {
        if (!alive) return;
        const booked: string[] = Array.isArray(d?.booked) ? d.booked : [];
        if (booked.length > 0) {
          setAvailabilityWarning("Seçtiğiniz tarihlerin bir kısmı dolu görünüyor. Yine de talep gönderebilirsiniz.");
        }
      })
      .catch(() => {});
    return () => {
      alive = false;
    };
  }, [roomSlug, checkin, checkout, nights]);

  async function submit(e: React.FormEvent) {
    e.preventDefault();
    setError(null);

    if (nights <= 0) {
      setError("Lütfen geçerli giriş ve çıkış tarihleri seçin.");
      return;
    }

    setSubmitting(true);
    try {
      const res = await fetch("/api/v1/lodging/requests", {
        method: "POST",
        headers: { "Content-Type": "application/json", Accept: "application/json" },
        body: JSON.stringify({
          room_type: roomSlug || null,
          guest_name: name,
          guest_phone: phone,
          guest_email: email || null,
          checkin,
          checkout,
          adults,
          children,
          message: message || null,
          _hp_url: honeypot,
        }),
      });
      const data = await res.json();

      if (!res.ok) {
        const errs = data?.errors as Record<string, string[]> | undefined;
        const firstFieldError = errs ? String(Object.values(errs)[0]?.[0] ?? "") : "";
        setError(data?.error || firstFieldError || "Talep gönderilemedi.");
        return;
      }

      setDone({ code: data?.code });
    } catch {
      setError("Bağlantı hatası. Lütfen tekrar deneyin.");
    } finally {
      setSubmitting(false);
    }
  }

  const inputStyle: React.CSSProperties = {
    width: "100%",
    padding: ".7rem .9rem",
    border: "1px solid var(--color-border, #d1d5db)",
    borderRadius: "var(--radius-button, .5rem)",
    fontSize: ".95rem",
    background: "var(--color-surface, #fff)",
    color: "var(--color-text, #111827)",
  };
  const labelStyle: React.CSSProperties = {
    display: "block",
    fontSize: ".8rem",
    fontWeight: 600,
    marginBottom: ".3rem",
    color: "var(--color-text-soft, #6b7280)",
  };

  if (done) {
    return (
      <section className="reservation-section" style={{ padding: "4rem 1.5rem", background: "var(--color-bg, #fff)" }}>
        <div style={{ maxWidth: "640px", margin: "0 auto", textAlign: "center" }}>
          <div style={{ fontSize: "2.5rem", marginBottom: "1rem" }}>✓</div>
          <h2 style={{ fontSize: "1.5rem", fontWeight: 700, color: "var(--color-heading, #111827)", marginBottom: ".75rem" }}>
            Teşekkürler!
          </h2>
          <p style={{ color: "var(--color-text-soft, #6b7280)" }}>{successMessage}</p>
          {done.code && (
            <p style={{ marginTop: "1rem", fontFamily: "monospace", color: "var(--color-primary, #6366f1)" }}>
              Talep No: {done.code}
            </p>
          )}
        </div>
      </section>
    );
  }

  return (
    <section className="reservation-section" style={{ padding: "4rem 1.5rem", background: "var(--color-bg, #fff)" }}>
      <div style={{ maxWidth: "720px", margin: "0 auto" }}>
        <h2 style={{ fontSize: "1.75rem", fontWeight: 700, textAlign: "center", color: "var(--color-heading, #111827)", marginBottom: subtitle ? ".5rem" : "1.75rem" }}>
          {title}
        </h2>
        {subtitle && (
          <p style={{ textAlign: "center", color: "var(--color-text-soft, #6b7280)", marginBottom: "1.75rem" }}>{subtitle}</p>
        )}

        <form onSubmit={submit} style={{ display: "grid", gap: "1rem" }}>
          {/* Honeypot — visually hidden, humans never fill it */}
          <input
            type="text"
            name="_hp_url"
            tabIndex={-1}
            autoComplete="off"
            value={honeypot}
            onChange={(e) => setHoneypot(e.target.value)}
            style={{ position: "absolute", left: "-9999px", width: "1px", height: "1px", opacity: 0 }}
            aria-hidden="true"
          />

          {showPicker && roomTypes.length > 0 && (
            <div>
              <label style={labelStyle}>Oda Tipi</label>
              <select value={roomSlug} onChange={(e) => setRoomSlug(e.target.value)} style={inputStyle}>
                <option value="">Farketmez</option>
                {roomTypes.map((rt) => (
                  <option key={rt.slug} value={rt.slug}>
                    {rt.name}{rt.capacity_max ? ` — en fazla ${rt.capacity_max} kişi` : ""}
                  </option>
                ))}
              </select>
            </div>
          )}

          <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: "1rem" }}>
            <div>
              <label style={labelStyle}>Giriş</label>
              <input type="date" required min={todayStr()} value={checkin} onChange={(e) => setCheckin(e.target.value)} style={inputStyle} />
            </div>
            <div>
              <label style={labelStyle}>Çıkış</label>
              <input type="date" required min={checkin || todayStr()} value={checkout} onChange={(e) => setCheckout(e.target.value)} style={inputStyle} />
            </div>
          </div>

          <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: "1rem" }}>
            <div>
              <label style={labelStyle}>Yetişkin</label>
              <input
                type="number"
                min={1}
                max={Math.max(1, capMax - children)}
                value={adults}
                onChange={(e) => setAdults(Math.max(1, Math.min(Number(e.target.value) || 1, capMax - children)))}
                style={inputStyle}
              />
            </div>
            <div>
              <label style={labelStyle}>Çocuk</label>
              <input
                type="number"
                min={0}
                max={Math.max(0, capMax - adults)}
                value={children}
                onChange={(e) => setChildren(Math.max(0, Math.min(Number(e.target.value) || 0, capMax - adults)))}
                style={inputStyle}
              />
            </div>
          </div>
          <p style={{ margin: "-.35rem 0 0", fontSize: ".78rem", color: "var(--color-text-soft, #9ca3af)" }}>
            {selectedRoom
              ? `Bu daire en fazla ${capMax} misafir alır.`
              : `Dairelerimiz en fazla ${capMax} misafir alır — kişi sayısına uygun daireyi seçin.`}
          </p>

          <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: "1rem" }}>
            <div>
              <label style={labelStyle}>Ad Soyad *</label>
              <input type="text" required value={name} onChange={(e) => setName(e.target.value)} style={inputStyle} />
            </div>
            <div>
              <label style={labelStyle}>Telefon *</label>
              <input type="tel" required value={phone} onChange={(e) => setPhone(e.target.value)} style={inputStyle} />
            </div>
          </div>

          <div>
            <label style={labelStyle}>E-posta</label>
            <input type="email" value={email} onChange={(e) => setEmail(e.target.value)} style={inputStyle} />
          </div>

          <div>
            <label style={labelStyle}>Mesaj</label>
            <textarea rows={3} value={message} onChange={(e) => setMessage(e.target.value)} style={inputStyle} />
          </div>

          {estTotal !== null && (
            <div style={{ padding: ".9rem 1.1rem", background: "var(--color-surface, #f9fafb)", borderRadius: "var(--radius-card, .6rem)", fontSize: ".95rem", color: "var(--color-heading, #111827)" }}>
              {nights} gece × {selectedRoom!.name} ≈{" "}
              <strong>
                {estTotal.toLocaleString("tr-TR", { minimumFractionDigits: 2, maximumFractionDigits: 2 })} {selectedRoom!.currency}
              </strong>
              <span style={{ display: "block", fontSize: ".78rem", color: "var(--color-text-soft, #6b7280)", marginTop: ".2rem" }}>
                Tahmini tutar — kesin fiyat için sizinle iletişime geçeceğiz.
              </span>
            </div>
          )}

          {availabilityWarning && (
            <p style={{ fontSize: ".85rem", color: "#b45309", margin: 0 }}>{availabilityWarning}</p>
          )}
          {error && <p style={{ fontSize: ".9rem", color: "#dc2626", margin: 0 }}>{error}</p>}

          <button
            type="submit"
            disabled={submitting}
            style={{
              padding: ".85rem 1.5rem",
              background: "var(--color-primary, #6366f1)",
              color: "#fff",
              border: "none",
              borderRadius: "var(--radius-button, .5rem)",
              fontWeight: 600,
              fontSize: "1rem",
              cursor: submitting ? "wait" : "pointer",
              opacity: submitting ? 0.7 : 1,
            }}
          >
            {submitting ? "Gönderiliyor…" : ctaLabel}
          </button>
        </form>
      </div>
    </section>
  );
}
