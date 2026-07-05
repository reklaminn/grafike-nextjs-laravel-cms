"use client";

/**
 * RangeCalendar — tek-akışlı tarih aralığı takvimi (tema-token, çok-siteli).
 *
 * Renkler CSS değişkenlerinden gelir (fallback'lı) → her site kendi chrome'uyla
 * render eder. Tıkla → giriş, tekrar tıkla → çıkış. **Geçmiş günler pasif.**
 * Opsiyonel `booked` (Set<Y-m-d>) → dolu günler işaretli ve seçilemez.
 *
 * Konaklama arama kutusu (HotelSearchSection) ve gelecekte daire-detay bunu
 * paylaşabilir. Pazartesi başlangıçlı (TR). 1 veya 2 ay gösterebilir.
 */
import { useMemo, useState } from "react";

const MONTHS = ["Ocak", "Şubat", "Mart", "Nisan", "Mayıs", "Haziran", "Temmuz", "Ağustos", "Eylül", "Ekim", "Kasım", "Aralık"];
const WK = ["Pt", "Sa", "Ça", "Pe", "Cu", "Ct", "Pz"];

const pad = (n: number) => (n < 10 ? "0" + n : String(n));
export const ymd = (d: Date) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;

type Props = {
  checkin: string;
  checkout: string;
  onPick: (ymd: string) => void;
  booked?: Set<string>;
  months?: 1 | 2;
};

export function RangeCalendar({ checkin, checkout, onPick, booked, months = 1 }: Props) {
  const today = useMemo(() => { const t = new Date(); t.setHours(0, 0, 0, 0); return t; }, []);
  const [view, setView] = useState(() => new Date(today.getFullYear(), today.getMonth(), 1));
  const todayStr = ymd(today);
  const atCurrent = view.getFullYear() === today.getFullYear() && view.getMonth() === today.getMonth();

  const nav: React.CSSProperties = {
    width: "30px", height: "30px", border: "1px solid var(--color-border, #e5e7eb)",
    background: "var(--color-surface, #fff)", color: "var(--color-heading, #374151)",
    borderRadius: "var(--radius-button, 6px)", cursor: "pointer",
    display: "flex", alignItems: "center", justifyContent: "center", fontSize: "16px",
  };

  const shown = Array.from({ length: months }, (_, i) => new Date(view.getFullYear(), view.getMonth() + i, 1));

  function renderMonth(base: Date) {
    const y = base.getFullYear(), m = base.getMonth();
    const daysInMonth = new Date(y, m + 1, 0).getDate();
    const offset = (new Date(y, m, 1).getDay() + 6) % 7; // Pazartesi başlangıç
    const cells: (number | null)[] = [];
    for (let i = 0; i < offset; i++) cells.push(null);
    for (let d = 1; d <= daysInMonth; d++) cells.push(d);

    return (
      <div key={`${y}-${m}`} style={{ flex: 1, minWidth: 0 }}>
        <div style={{ textAlign: "center", fontWeight: 700, fontSize: ".95rem", color: "var(--color-heading, #111827)", fontFamily: "var(--font-heading, inherit)", marginBottom: ".5rem" }}>
          {MONTHS[m]} {y}
        </div>
        <div style={{ display: "grid", gridTemplateColumns: "repeat(7,1fr)", gap: "2px", marginBottom: "2px" }}>
          {WK.map((w) => <span key={w} style={{ textAlign: "center", fontSize: ".62rem", fontWeight: 700, color: "var(--color-text-soft, #9ca3af)", padding: "3px 0" }}>{w}</span>)}
        </div>
        <div style={{ display: "grid", gridTemplateColumns: "repeat(7,1fr)", gap: "2px" }}>
          {cells.map((d, i) => {
            if (d === null) return <span key={"e" + i} />;
            const ds = ymd(new Date(y, m, d));
            const isPast = ds < todayStr;
            const isBooked = booked?.has(ds) ?? false;
            const isCheckin = ds === checkin;
            const isCheckout = ds === checkout;
            const inRange = !!checkin && !!checkout && ds > checkin && ds < checkout;
            const disabled = isPast || isBooked;

            let bg = "transparent";
            let color = "var(--color-text, #374151)";
            let deco = "none";
            if (isPast) { color = "var(--color-text-soft, #cbd5e1)"; }
            else if (isBooked) { bg = "color-mix(in srgb, var(--color-primary, #b4735a) 16%, #fff)"; color = "var(--color-text-soft, #b4735a)"; deco = "line-through"; }
            else if (isCheckin || isCheckout) { bg = "var(--color-primary, #6366f1)"; color = "#fff"; }
            else if (inRange) { bg = "color-mix(in srgb, var(--color-primary, #6366f1) 16%, transparent)"; }

            return (
              <button
                key={ds}
                type="button"
                disabled={disabled}
                onClick={() => onPick(ds)}
                title={isBooked ? "Dolu" : isPast ? "Geçmiş tarih" : undefined}
                style={{
                  height: "30px", border: "none", background: bg, color,
                  fontSize: ".8rem", fontWeight: (isCheckin || isCheckout) ? 700 : 400,
                  textDecoration: deco, borderRadius: "6px",
                  cursor: disabled ? "not-allowed" : "pointer",
                }}
              >
                {d}
              </button>
            );
          })}
        </div>
      </div>
    );
  }

  return (
    <div>
      <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginBottom: ".5rem" }}>
        <button type="button" aria-label="Önceki ay" disabled={atCurrent}
          onClick={() => setView(new Date(view.getFullYear(), view.getMonth() - 1, 1))}
          style={{ ...nav, opacity: atCurrent ? 0.4 : 1, cursor: atCurrent ? "not-allowed" : "pointer" }}>‹</button>
        <button type="button" aria-label="Sonraki ay"
          onClick={() => setView(new Date(view.getFullYear(), view.getMonth() + 1, 1))} style={nav}>›</button>
      </div>
      <div style={{ display: "flex", gap: "1.5rem" }}>
        {shown.map(renderMonth)}
      </div>
      <div style={{ display: "flex", flexWrap: "wrap", gap: ".9rem", marginTop: ".6rem", fontSize: ".7rem", color: "var(--color-text-soft, #6b7280)" }}>
        <span style={{ display: "inline-flex", alignItems: "center", gap: "5px" }}>
          <span style={{ width: "11px", height: "11px", background: "var(--color-primary, #6366f1)", borderRadius: "3px" }} />Seçili
        </span>
        {booked && (
          <span style={{ display: "inline-flex", alignItems: "center", gap: "5px" }}>
            <span style={{ width: "11px", height: "11px", background: "color-mix(in srgb, var(--color-primary, #b4735a) 16%, #fff)", borderRadius: "3px" }} />Dolu
          </span>
        )}
      </div>
    </div>
  );
}
