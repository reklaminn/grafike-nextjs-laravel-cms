# MVP Roadmap

## Karar Özeti

Bu proje için kesin başlangıç kararı:

- backend: Laravel 12 CMS
- frontend: Next.js 15
- ilk teslim modeli: `Basic HTML Section Mode`
- ikinci ana evrim: `Structured Component Mode`
- uzun vadeli hedef: hibrit sistem
- editör omurgası: `Header / Body / Footer + Row / Column / Block`

Bu kararın sebebi:

- ajansın mevcut iş yapışını bozmamak
- Porto / Woodmart / benzeri HTML tema akışını ürünleştirmek
- ama bunu doğrudan Next.js tabanlı yeni mimariye oturtmak

---

## Son Durum Notu — 2026-04-30

Aktif çalışma branch'i: `feat/multi-tenant`.

Canlı deploy durumu:

- Hostinger VPS üzerinde Docker deploy başarıyla ayağa kaldırıldı.
- Deploy için tek env kaynağı `.env` olarak netleştirildi.
- `.env.hostinger` runtime akışından çıkarıldı; gerekiyorsa sadece örnek dosya olarak kullanılacak.
- `docker-compose.yml` ve `docker-compose.hostinger.yml` `.env` dosyasını container runtime'a da geçiriyor.
- Next.js Docker build için URL fallback'leri eklendi; boş `NEXT_PUBLIC_SITE_URL` build'i kırmıyor.
- Rebase sonrası VPS'te branch ayrışması `git fetch origin` + `git reset --hard origin/feat/multi-tenant` ile çözüldü.

Canlı deploy komutu:

```bash
git fetch origin
git reset --hard origin/feat/multi-tenant
docker compose up -d --build
```

Kaldığımız yer:

- Multi-tenant Docker deploy hattı çalışır duruma geldi.
- Sıradaki teknik kontrol: canlıda admin giriş, tenant/site seçici, Laravel API, Next.js frontend render ve Traefik route doğrulaması.
- Sıradaki ürün geliştirme odağı: Faz 1.5 kalanlarını netleştirip tamamlamak; özellikle live preview, schema visual builder, eksik placeholder uyarıları ve section template editör olgunlaştırması.
- Sentry Next.js instrumentation uyarıları build'i durdurmuyor; ayrı bir teknik borç olarak temizlenecek.

---

## ✅ Tamamlanan Geliştirmeler

### Admin Edit Screen — 5 Aksiyon Serisi (`/admin/pages/{id}/edit`)

Tüm aksiyonlar tamamlanıp `main` branch'e push edildi.

| # | Aksiyon | Commit | Durum |
|---|---|---|---|
| 1 | `_form.blade.php` Partial Parçalama | `546972f` öncesi | ✅ |
| 2 | Canlı Iframe Preview + migrateToSections Önizleme Modalı | `fda94c5` | ✅ |
| 3 | Blok Şema Doğrulaması (`FrontendSectionSchemaValidator`) | `3258433` | ✅ |
| 4 | Page Revisions + `PageObserver` getOriginal() düzeltmesi | `76d06ed` | ✅ |
| 5 | Otomatik Builder Mode Belirleme (`PageEditorData`) | `e0f00a0` | ✅ |

**Aksiyon 1 — `_form` Parçalama**
- 1957 satırlık `_form.blade.php` → `_form/` klasörü altındaki 9 partial'a bölündü
- `PageEditorData` value object; Alpine payload üretimi controller'da, blade'de sadece property okuma
- `basic-info`, `builder-mode`, `frontend-editor`, `legacy-builder`, `editor-modals`, `editor-script`, `preview-panel`, `seo`, `sidebar/` partials

**Aksiyon 2 — Canlı Iframe Preview**
- `preview-panel.blade.php`: Desktop / Tablet / Mobile device toggle, lazy iframe, kaydet sonrası `preview_refresh` flash'ı ile panel otomatik açılıyor
- `builder-mode.blade.php`: `confirm()` dialog kaldırıldı → Alpine async modal; `GET /pages/{page}/migrate-preview` endpoint'inden `current_sections` vs `preview_sections` JSON'u yan yana gösteriyor, onay sonrası POST ile dönüştürüyor
- `.env.example`: `CMS_FRONTEND_URL=http://127.0.0.1:3000` belgelendi

**Aksiyon 3 — Blok Şema Doğrulaması**
- `app/Support/FrontendSectionSchemaValidator.php`: `string`, `text`, `url`, `email`, `number`, `boolean`, `media_id`, `enum` tiplerini destekliyor; `required`, `max`, `min` kuralları
- `PageRequest::withValidator()` hook'u: `sections_json` içindeki block'ların content'i `SectionTemplate.schema_json`'a karşı doğrulanıyor
- `SectionTemplateFactory` + `ThemeFactory` oluşturuldu (NOT NULL FK zinciri)
- `tests/Feature/Admin/PageSchemaValidationTest.php` — 5 test, hepsi yeşil

**Aksiyon 4 — Page Revisions**
- `page_revisions` tablosu, `PageRevision` modeli, `Page::recordSnapshot()`, `Page::revisions()` relation
- `PageObserver::updating()`: `sections_json` veya `layout_json` dirty olduğunda `getOriginal()` ile pre-update snapshot
- `restoreRevision` controller: snapshot'tan geri yükleme + yeni snapshot (geri geri yükleme için)
- `GET pages/{page}/migrate-preview` + `POST pages/{page}/revisions/{revision}/restore` rotaları
- Sidebar revisions paneli: son 10 revision, "Geri Yükle" butonu
- `tests/Feature/Admin/PageRevisionTest.php` — 4 test, hepsi yeşil
- **Bug fix**: `recordSnapshot` `$page->sections_json` yerine `getOriginal()` kullanıyor (dirty değil, pre-update state)

**Aksiyon 5 — Otomatik Builder Mode**
- `PageEditorData::activeBuilder()` + `showBuilderToggle()` kural seti:
  - `sections_json` dolu, `layout_json` boş → frontend, toggle yok
  - `layout_json` dolu, `sections_json` boş → legacy, dönüştür CTA'sı aktif
  - Her ikisi dolu → frontend default, "eski builder" seçeneği görünür
  - Her ikisi boş → frontend (yeni sayfa)
- `builder-mode.blade.php`: context-aware mesajlar, `x-on:frontend-block-focus.window` event handler

### Block Settings Modal — Field Type Polish

| Alan Tipi | Implementasyon |
|---|---|
| `string` / `text` / `url` / `email` / `color` / `number` | Native HTML input (`type=` prefix) |
| `textarea` | `<textarea>` + auto-height |
| `boolean` | Toggle switch |
| `select` / `enum` | `<select>` — `{label, value}` obje + düz string desteği |
| `image` / `media_id` | Thumbnail önizleme + AJAX media picker modal (jsGrid) |
| `rich-text` / `html` | Quill 2.0.2 WYSIWYG (CDN), `@push('styles')` + `@push('scripts')` |

- `_form/_field-types.blade.php` — tekil partial; hem top-level field hem repeater item kullanıyor
- `blockFieldInput(parentRef, fieldKey, fieldSchema)` Alpine sub-component — reaktif two-way binding
- `MediaController@index` JSON branch: `if ($request->expectsJson())` ile AJAX media picker desteği
- **Bug fix**: Blade `@error` directive çakışması → Alpine `@` event listener'lar `@@` ile escape edildi

---

## Kalan İşler

### Admin Edit Screen — Küçük Eksikler

- [ ] `cms:prune-page-revisions` Artisan komutu — `config('cms.revisions_keep', 50)` ötesindeki revision'ları temizler; mvp scope dışı bırakıldı, ileride eklenecek
- [ ] Revision sidebar'ında admin adı gösterimi — `admin_id` FK mevcut ama UI'da şimdilik gösterilmiyor
- [ ] `migrateToSections` önizleme modalında side-by-side visual diff (şu an pretty-printed JSON, gerçek diff renklendirmesi yok)

---

## Faz 0: Temel Yön ve Hazırlık ✅

Hedef:

- repo yapısını belirlemek
- veri modelini netleştirmek
- teknik kararları dondurmak

Çıktılar:

- mimari dokümanı
- başlangıç planı
- örnek theme pack
- örnek site template
- monorepo klasör yapısı

---

## Faz 1: Basic HTML Section Mode ✅

Hedef:

- mevcut ajans akışını ürüne dönüştürmek
- HTML template section mantığını Next.js üzerinde çalıştırmak

Kapsam:

- Laravel public API kontratı
- Next.js app skeleton
- theme pack sistemi
- tema CSS/JS asset tanımı
- HTML section snippet modeli
- region tabanlı frontend editörü (`header/body/footer`)
- satır / kolon / block veri modeli
- sayfa bazlı block sıralama
- aktif/pasif row/column/block kontrolü
- sayfa bazlı custom css/js
- global custom css/js
- backend Sentry entegrasyon planı

İlk endpoint seti:

- `GET /api/site`
- `GET /api/settings`
- `GET /api/menus/{location}`
- `GET /api/pages/{slug}`
- `GET /api/articles/{slug}`
- `GET /api/articles`

İlk tema örneği:

- `porto-furniture`

İlk section seti:

- hero
- text-block
- testimonials
- faq
- cta-banner
- blog-list
- gallery
- video
- spacer

Başarı ölçütü:

- Porto benzeri bir temayı CSS/JS + HTML section parçalarıyla sisteme alabilmek
- bir firmaya uyarlayabilmek
- içerik/görsel/sıralama/custom css ile teslim edebilmek
- admin panelde `Header / Body / Footer` altında satır, kolon ve block mantığıyla düzenleme yapabilmek

---

## Faz 1.5: Section Template Olgunlaşması

Hedef:

- Faz 1'de gelen Basic HTML Section Mode'un içerik üretim döngüsünü kullanıcı için anlaşılır hâle getirmek
- "HTML yapıştır → block şablonuna çevir" akışını manuel JSON yazımı gerektirmeden kullanılabilir kılmak
- Şablon içine site/menu/ayar verilerini placeholder olarak gömmeyi tek tık ile yapılabilir hâle getirmek

Kapsam:

### HTML'yi Şablona Dönüştür — Repeat alan seçimi

- [ ] Mevcut otomatik DOM tarama algoritmasına interaktif seçim katmanı eklenir
- [ ] Yapıştırılan HTML üzerinde tekrar eden öbekler (ürün kartı, slide, list item) admin önizlemede işaretlenebilir
- [ ] Seçilen tekrar öbeği `slide_N_*` / `card_N_*` placeholder grubuna dönüştürülür ve schema'da repeater tipinde tek alan oluşturulur
- [ ] Kullanıcı tekrar sayısını (3, 4, 6 vb.) önizleme üzerinden belirleyebilir
- [ ] Repeat öbek sınırı görsel olarak çerçeve ile gösterilir; çoklu seçim yapılabilir

### Menü placeholder yönetimi

- [ ] HTML editörü içinden "Menü Ekle" butonu ile kayıtlı menülerden seçim yapılır
- [ ] Site içinde tanımlı menü slug/location listesi dropdown olarak sunulur
- [ ] Tam HTML (`{{{menu_<key>_html}}}`) mı yalnız item HTML (`{{{menu_<key>_items_html}}}`) mı seçimi yapılır
- [ ] Seçim sonrası placeholder doğru token ile editöre tek tıkta gömülür; manuel placeholder ezberi gerekmez
- [ ] Mevcut menülerin yenilenmesi için "menüleri yeniden yükle" butonu bulunur

### Site ayarlarından gelen sabit placeholder'lar

- [ ] Site bazlı genel alanlar (`phone`, `email`, `address`, `whatsapp_number`, `working_hours`, `tax_id` vb.) için placeholder picker
- [ ] HTML editöründen "Sistem Alanı Ekle" dropdown'u açılır, mevcut `site_name`, `theme_slug`, `logo_url`, `favicon_url`, `footer_text` gibi alanlar listelenir
- [ ] `SiteSetting` tablosuna yeni eklenen her alan otomatik placeholder kataloğuna düşer
- [ ] Kullanıcı her site için bu alanları admin → ayarlar üzerinden tek bir yerden doldurabilir; bütün block'lara otomatik yansır
- [ ] Eksik veya tanımsız placeholder'lar render anında uyarı verir

### Block Şablon Yönetim Paneli — P1 (Hızlı Kazanımlar)

`/admin/section-templates` ekranında ilk dalgada uygulanması gereken iyileştirmeler. Hepsi 30–90 dakikalık küçük işlerdir, tek commit/PR seti olarak gönderilebilir.

- [ ] **Index'te preview thumbnail göster** — `preview_image` varsa kart tepesine 16:9 görsel, yoksa type ikonunu (hero/footer/cta) yerleştir
- [ ] **Index'te kullanım sayısı badge'i** — bir şablon kaç sayfa tarafından kullanılıyor; silmeden önce etkiyi gösterir
- [ ] **Status filter butonları** — "Tümü / Aktif / Pasif" pill toggle, mevcut tema + arama filtresinin yanına eklenir
- [ ] **Klonla aksiyonu** — `duplicate(SectionTemplate $st)` controller method'u; yeni varyasyon yaratırken kopya-yapıştır yükünü kaldırır
- [ ] **JSON form-level validation** — `SectionTemplateRequest::withValidator()` hook'u ile `schema_json`, `default_content_json`, `legacy_config_map_json` decode edilemiyorsa hata; her schema alanının `type` zorunlu
- [ ] **Render mode'a göre alan görünürlüğü netleşsin** — html mode'da `component_key` gizli (mevcut), component mode'da `html_template` collapsed-default
- [ ] **Form partial parçalama** — `_form.blade.php` (552 satır) → `_form/basic-info`, `_form/template`, `_form/schema`, `_form/legacy`, `_form/sidebar/docs` partial'larına bölünür (page form refactor pattern'i ile aynı yaklaşım)

### Block Şablon Yönetim Paneli — P2 (Orta Vadeli Geliştirmeler)

İkinci dalgada uygulanacak orta ölçekli geliştirmeler. Her biri yarım gün ile 1 gün arası iş yükü taşır.

- [ ] **Schema visual builder** — JSON yerine "Alan Ekle" butonu + her alan için `key/type/label/required/max/min/options/help` form satırı; arka planda JSON üretilir. Desteklenen tipler: `string | text | textarea | url | email | number | boolean | enum | media_id | color | select | repeater`
- [ ] **Default content otomatik türetme** — schema yapısından default JSON üret butonu (key→type/label'a göre placeholder); textarea readonly olur, "düzenle" tıklanınca free-edit
- [ ] **HTML ↔ Schema key diff göstergesi** — HTML'deki tüm `{{key}}` taranır; schema'da olmayanlar kırmızı, schema'da olup HTML'de olmayanlar gri liste; kayıttan önce uyarı verir
- [ ] **CodeMirror / Monaco entegrasyonu** — `html_template` için HTML highlighting + basic linting; satır numarası + auto-indent
- [ ] **HTML'yi Şablona Dönüştür — güvenli mod** — modal: "Mevcut schema'yı sıfırlamak istiyor musun? Veya birleştir mi?" — sessiz overwrite riskini kaldırır
- [ ] **Live preview iframe** — form altında küçük bir iframe; "Önizle" butonu schema + default_content ile geçici render endpoint'ine POST atar, iframe gösterir

### Block Şablon Yönetim Paneli — P3 (Yapısal Geliştirmeler)

Üçüncü dalgada uygulanacak yapısal değişiklikler. Veri modeli ve sistem mimarisini etkileyen, daha uzun vadeli iyileştirmelerdir.

- [ ] **Soft delete** — `section_templates` tablosuna `deleted_at` kolonu eklenir. Bir şablon silinmek istendiğinde halen kullanan sayfalar listelenir; kullanıcı "arşivle" veya "zorla sil" seçimi yapar.
- [ ] **`preview_image` Spatie media-library upload** — text URL alanı yerine; thumbnail otomatik üretimi ve responsive görsel desteği
- [ ] **Component key autocomplete kaynağı** — `apps/frontend/components/sections/` dizinindeki `.tsx` dosyaları build-time taranıp `storage/app/component-registry.json` manifest'i üretilir
- [ ] **Section template versiyonlama** — `page_revisions` pattern'i section template'lerine taşınır; "v1 yayında, v2 draft" akışı
- [ ] **`legacy_module_key` enum select** — `app/Services/ModuleRenderer/Modules/*.php` dosyaları taranıp legacy modül kataloğu otomatik üretilir; plain text input yerine select

---

## Faz 2: Structured Component Mode

Hedef:

- en çok kullanılan section'ları HTML snippet'ten çıkarıp reusable Next.js component'e çevirmek

Kapsam:

- [ ] section registry
- [ ] section schema standardı
- [ ] variation sistemi
- [ ] theme token sistemi
- [ ] React tabanlı section renderer
- [ ] hero/testimonials/blog-list/cta gibi çekirdek section'ların component sürümleri
- [ ] frontend Sentry entegrasyonu

Not:

- Structured mode yeni bir ikinci editör yaratmayacak
- Faz 1'de gelen region tabanlı builder korunacak
- yalnızca bazı block'lar `render_mode=component` ile render edilecek

Başarı ölçütü:

- aynı page içinde hem HTML section hem component section kullanılabilmesi
- en sık kullanılan blokların structured render'a taşınması

---

## Faz 3: Template ve Site Instance Sistemi

Hedef:

- oluşturulan siteleri tekrar kullanılabilir template varlığına dönüştürmek

Kapsam:

- [ ] site instance modeli
- [ ] siteyi template olarak kaydetme
- [ ] template'ten yeni site oluşturma
- [ ] theme preset kopyalama
- [ ] ajans/müşteri yetki sınırları

Başarı ölçütü:

- bir template'in birden fazla firmada renk/section/varyasyon farkıyla kullanılabilmesi

---

## Faz 4: AI-Assisted Template Creation

Hedef:

- referans ekran görüntülerinden reusable theme/section/template üretimini hızlandırmak

Kapsam:

- [ ] referans görsel analizi
- [ ] section extraction
- [ ] theme preset önerisi
- [ ] variation önerisi
- [ ] template taslağı üretimi

Başarı ölçütü:

- Porto / Woodmart / özel referanslardan daha hızlı template üretmek

---

## Faz 5: Olgunlaşma

Hedef:

- üretim güvenliği ve panel deneyimini olgunlaştırmak

Kapsam:

- [ ] reusable block marketplace
- [ ] theme diff / override takibi
- [ ] daha net müşteri/ajans erişim modeli
- [ ] taşınabilir template export/import
- [ ] `cms:prune-page-revisions` Artisan komutu (`config('cms.revisions_keep', 50)`)

---

## Teknik Sıralama

Kod geliştirme sırası şu olmalı:

1. ✅ Next.js temel app
2. ✅ Laravel public API
3. ✅ Basic HTML Section Mode
4. ✅ Region / row / column / block editörü
5. ✅ Admin edit screen — partial refactor, preview, revisions, schema validation, builder mode
6. 🔲 Theme packs olgunlaşması
7. 🔲 Section template yönetim paneli P1 → P2 → P3
8. 🔲 Structured component renderer
9. 🔲 Template/site instance sistemi
10. 🔲 AI hızlandırıcı katman

---

## Bu Roadmap Nasıl Kullanılacak

Bu dosya sabit bir sözleşme değildir.

Ama şu kararı sabit kabul eder:

- ilk ürün teslim yaklaşımı `Basic HTML Section Mode`
- hedef platform `Next.js + Laravel`

İleride fazlar:

- birleştirilebilir
- bölünebilir
- yeniden sıralanabilir

ancak temel başlangıç yönü korunmalıdır.
