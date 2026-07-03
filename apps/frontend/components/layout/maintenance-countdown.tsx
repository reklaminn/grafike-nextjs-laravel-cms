"use client";

/**
 * MaintenanceCountdown — "Yakında" sayfasında yayın tarihine canlı geri sayım.
 * Server'da hydration uyuşmazlığı olmasın diye ilk render'da boş; useEffect ile
 * tarayıcıda hesaplanır ve saniyede bir güncellenir.
 */
import { useEffect, useState } from "react";

type Parts = { days: number; hours: number; minutes: number; seconds: number };

function diff(target: number): Parts | null {
  const ms = target - Date.now();
  if (ms <= 0) return null;
  return {
    days: Math.floor(ms / 86_400_000),
    hours: Math.floor((ms % 86_400_000) / 3_600_000),
    minutes: Math.floor((ms % 3_600_000) / 60_000),
    seconds: Math.floor((ms % 60_000) / 1_000),
  };
}

function pad(n: number): string {
  return String(n).padStart(2, "0");
}

export function MaintenanceCountdown({ until }: { until: string }) {
  const target = new Date(until).getTime();
  const [parts, setParts] = useState<Parts | null>(null);
  const [ready, setReady] = useState(false);

  useEffect(() => {
    if (!Number.isFinite(target)) return;
    setReady(true);
    setParts(diff(target));
    const id = setInterval(() => setParts(diff(target)), 1000);
    return () => clearInterval(id);
  }, [target]);

  // Geçersiz tarih ya da süre dolmuş → gösterme.
  if (!ready || !parts) return null;

  const units: Array<{ value: number; label: string }> = [
    { value: parts.days, label: "gün" },
    { value: parts.hours, label: "saat" },
    { value: parts.minutes, label: "dakika" },
    { value: parts.seconds, label: "saniye" },
  ];

  return (
    <div style={{ display: "flex", gap: "0.75rem", flexWrap: "wrap", justifyContent: "center", marginTop: "0.5rem" }}>
      {units.map((u) => (
        <div
          key={u.label}
          style={{
            minWidth: "68px",
            padding: "0.75rem 0.5rem",
            borderRadius: "var(--radius-card, 0.75rem)",
            background: "var(--color-surface, #fff)",
            border: "1px solid var(--color-border, #e5e7eb)",
          }}
        >
          <div style={{ fontSize: "1.6rem", fontWeight: 700, lineHeight: 1, color: "var(--color-heading, #111827)", fontVariantNumeric: "tabular-nums" }}>
            {pad(u.value)}
          </div>
          <div style={{ fontSize: "0.7rem", textTransform: "uppercase", letterSpacing: "0.05em", color: "var(--color-text-soft, #9ca3af)", marginTop: "0.3rem" }}>
            {u.label}
          </div>
        </div>
      ))}
    </div>
  );
}
