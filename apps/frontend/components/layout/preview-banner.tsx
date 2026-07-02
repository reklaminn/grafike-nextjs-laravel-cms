'use client';

import { useEffect, useState } from 'react';

/**
 * Önizleme modu rozeti — tenant sitesi draft/taslak içerikle görüntülenirken
 * editöre "bu yayındaki site değil" uyarısı verir.
 *
 * Neden köşede yüzen pill (üstte tam-genişlik bant DEĞİL):
 * temaların header'ı çoğunlukla `position: fixed; top: 0` olduğundan, üstte
 * duran bir bant header'ın üstünü örter (logo/menü görünmez). Sabit köşe pill'i
 * site düzenini hiç etkilemez ve kapatılabilir. Kapatma sessionStorage'da tutulur
 * → önizleme sırasında sayfalar arasında gezerken kapalı kalır; küçük 👁 düğmesi
 * ile tekrar açılır.
 */
export function PreviewBanner({ label }: { label: string }) {
  const [hidden, setHidden] = useState(false);

  useEffect(() => {
    try {
      setHidden(sessionStorage.getItem('cms_preview_banner_hidden') === '1');
    } catch {
      /* sessionStorage erişilemez — rozet görünür kalsın */
    }
  }, []);

  const hide = () => {
    setHidden(true);
    try {
      sessionStorage.setItem('cms_preview_banner_hidden', '1');
    } catch {
      /* yoksay */
    }
  };

  const show = () => {
    setHidden(false);
    try {
      sessionStorage.removeItem('cms_preview_banner_hidden');
    } catch {
      /* yoksay */
    }
  };

  if (hidden) {
    return (
      <button
        type="button"
        onClick={show}
        aria-label="Önizleme bilgisini göster"
        title="Önizleme modu"
        style={{
          position: 'fixed',
          bottom: 12,
          left: 12,
          zIndex: 9999,
          width: 34,
          height: 34,
          borderRadius: '9999px',
          border: 'none',
          background: '#4f46e5',
          color: '#fff',
          fontSize: '1rem',
          lineHeight: 1,
          cursor: 'pointer',
          boxShadow: '0 4px 14px rgba(79,70,229,.4)',
        }}
      >
        👁
      </button>
    );
  }

  return (
    <div
      style={{
        position: 'fixed',
        bottom: 12,
        left: 12,
        zIndex: 9999,
        display: 'flex',
        alignItems: 'center',
        gap: '.5rem',
        maxWidth: 'min(92vw, 30rem)',
        background: '#4f46e5',
        color: '#fff',
        fontSize: '.78rem',
        fontWeight: 600,
        letterSpacing: '.01em',
        padding: '.4rem .5rem .4rem .75rem',
        borderRadius: '9999px',
        boxShadow: '0 6px 20px rgba(79,70,229,.4)',
      }}
    >
      <span aria-hidden="true">👁</span>
      <span
        style={{
          overflow: 'hidden',
          textOverflow: 'ellipsis',
          whiteSpace: 'nowrap',
        }}
      >
        {label}
      </span>
      <button
        type="button"
        onClick={hide}
        aria-label="Önizleme rozetini kapat"
        title="Kapat"
        style={{
          flexShrink: 0,
          display: 'inline-flex',
          alignItems: 'center',
          justifyContent: 'center',
          width: 22,
          height: 22,
          borderRadius: '9999px',
          border: 'none',
          background: 'rgba(255,255,255,.18)',
          color: '#fff',
          fontSize: '.85rem',
          lineHeight: 1,
          cursor: 'pointer',
        }}
      >
        ✕
      </button>
    </div>
  );
}
