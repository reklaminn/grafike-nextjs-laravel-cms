/**
 * Frontend UI metinleri — locale bazlı.
 *
 * CMS içeriği zaten dil bazlı gelir; bu sözlük yalnızca CMS'in
 * kapsamadığı sistem mesajları içindir (404, hata, üye kilidi, arama…).
 * Bilinmeyen locale → İngilizce'ye, o da yoksa Türkçe'ye düşer.
 */

type UiStrings = {
  notFoundTitle: string;
  notFoundBody: string;
  backHome: string;
  errorTitle: string;
  errorBody: string;
  retry: string;
  memberOnlyTitle: string;
  memberOnlyBody: string;
  memberRestrictedBody: (groups: string[]) => string;
  login: string;
  previewBanner: (tenant: string) => string;
  searchPlaceholder: string;
  searchButton: string;
  searchNoResults: (q: string) => string;
  searchMinChars: string;
  searchError: string;
};

const tr: UiStrings = {
  notFoundTitle: "Sayfa bulunamadı",
  notFoundBody: "Aradığınız sayfa mevcut değil veya taşınmış olabilir.",
  backHome: "Ana sayfaya dön",
  errorTitle: "Bir şeyler ters gitti",
  errorBody: "Beklenmeyen bir hata oluştu. Lütfen tekrar deneyin.",
  retry: "Tekrar dene",
  memberOnlyTitle: "Üyelere özel içerik",
  memberOnlyBody: "Bu sayfanın bir kısmı sadece kayıtlı üyelere açıktır.",
  memberRestrictedBody: (groups) =>
    groups.length > 0
      ? `Bu sayfa yalnızca ${groups.join(", ")} üyelerine özeldir.`
      : "Bu sayfa belirli üye gruplarına özeldir.",
  login: "Giriş Yap",
  previewBanner: (tenant) => `Önizleme modu — ${tenant} · Bu görünüm yayındaki siteyi etkilemez`,
  searchPlaceholder: "Sitede ara…",
  searchButton: "Ara",
  searchNoResults: (q) => `"${q}" için sonuç bulunamadı.`,
  searchMinChars: "En az 2 karakter yazın.",
  searchError: "Arama sırasında bir hata oluştu.",
};

const en: UiStrings = {
  notFoundTitle: "Page not found",
  notFoundBody: "The page you are looking for does not exist or may have moved.",
  backHome: "Back to home",
  errorTitle: "Something went wrong",
  errorBody: "An unexpected error occurred. Please try again.",
  retry: "Try again",
  memberOnlyTitle: "Members-only content",
  memberOnlyBody: "Part of this page is only available to registered members.",
  memberRestrictedBody: (groups) =>
    groups.length > 0
      ? `This page is restricted to members of: ${groups.join(", ")}.`
      : "This page is restricted to specific member groups.",
  login: "Sign In",
  previewBanner: (tenant) => `Preview mode — ${tenant} · This view does not affect the live site`,
  searchPlaceholder: "Search the site…",
  searchButton: "Search",
  searchNoResults: (q) => `No results found for "${q}".`,
  searchMinChars: "Type at least 2 characters.",
  searchError: "An error occurred while searching.",
};

const dictionaries: Record<string, UiStrings> = { tr, en };

export function uiStrings(locale: string): UiStrings {
  const lang = locale.split(/[-_]/)[0].toLowerCase();
  return dictionaries[lang] ?? en ?? tr;
}
