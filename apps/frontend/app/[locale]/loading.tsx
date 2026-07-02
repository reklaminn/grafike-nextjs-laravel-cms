/**
 * Locale segment yükleme iskeleti — veri çekilirken boş beyaz ekran
 * yerine içerik yer tutucuları gösterir. Tamamen görsel (metin yok),
 * bu yüzden i18n gerektirmez.
 */
export default function LocaleLoading() {
  const bar = (width: string, height = "1rem") => ({
    width,
    height,
    background: "var(--color-border,#e5e7eb)",
    borderRadius: ".375rem",
    animation: "cms-pulse 1.5s ease-in-out infinite",
  });

  return (
    <main className="container" style={{ padding: "3rem 1rem", maxWidth: "960px", margin: "0 auto" }}>
      <style>{`@keyframes cms-pulse { 0%,100% { opacity: .5 } 50% { opacity: 1 } }`}</style>

      {/* Hero iskeleti */}
      <div style={{ ...bar("100%", "16rem"), marginBottom: "2.5rem" }} />

      {/* Başlık + paragraflar */}
      <div style={{ ...bar("45%", "1.75rem"), marginBottom: "1.25rem" }} />
      <div style={{ ...bar("100%"), marginBottom: ".6rem" }} />
      <div style={{ ...bar("92%"), marginBottom: ".6rem" }} />
      <div style={{ ...bar("75%"), marginBottom: "2.5rem" }} />

      {/* Kart satırı */}
      <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fit, minmax(220px, 1fr))", gap: "1.25rem" }}>
        <div style={bar("100%", "9rem")} />
        <div style={bar("100%", "9rem")} />
        <div style={bar("100%", "9rem")} />
      </div>
    </main>
  );
}
