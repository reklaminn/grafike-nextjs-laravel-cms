# Rezervasyon Modülü — Proje Dosyası

> iraspa-cms (Laravel 12 + Next.js 15, çok-tenant) için **jenerik, yeniden kullanılabilir konaklama rezervasyon motoru**. Bu dosya modülün kapsamını, veri modelini, admin/frontend entegrasyonunu ve seeder sözleşmesini kapsar. Ayrı bir session'da **sıfırdan** geliştirilecek şekilde kendi kendine yeterlidir.

**İlk tüketici:** `Vatan Suit` oteli (Tokat, 19 daire: 11× 1+1 Suit, 8× 3+1 Deluxe). Site seeder'ı `feat/otelvatan-site` branch'inde **bekliyor**; bu modül inince ona bağlanacak.

---

## 0. Altın Kurallar (ihlal etme)

1. **Mevcut CMS'i bozma.** Her şey **eklemeli** (additive): yeni tablolar, yeni modeller, yeni admin bölümü, yeni blok tipi. Mevcut form sistemine, Theme/Page/SectionTemplate çekirdeğine, frontend renderer'ın mevcut davranışına **dokunma**.
2. **`main`'e ve `feat/multi-tenant`'a doğrudan dokunma.** Modül kendi branch'inde: **`feat/reservation-module`** (`feat/multi-tenant`'tan aç — çok-tenant altyapısı orada).
3. **Jenerik ol, Vatan Suit'e gömme.** Modül = tüm oteller için yetenek. Vatan Suit'in odaları/fiyatları modülün içine **hardcode edilmez** — onlar site seeder'ında veri olarak instance edilir.
4. **Çok-tenant izolasyonu.** Tüm rezervasyon verisi **tenant DB'sinde** (central değil). Migration'lar `tenants:migrate` ile çalışır.
5. **Push öncesi daima `fetch/pull`. Force push yok.** (Paralel session'lar aynı repoda çalışıyor.)

---

## 1. Amaç & Kapsam

Ziyaretçiden **tarih + kişi + oda tipi** ile **rezervasyon talebi** toplayan; admin tarafında oda tiplerini, müsaitliği ve gelen talepleri yöneten bir motor. MVP hedefi **talep + müsaitlik gösterimi** (ödeme/PMS entegrasyonu değil).

### Kapsam içi (MVP)
- Oda tipi (RoomType) yönetimi + fiyat.
- Müsaitlik (dolu tarih aralıkları) yönetimi ve **gerçek** takvime yansıması.
- Rezervasyon talebi alma (form) + admin gelen-kutusu + durum yönetimi.
- Tahmini tutar hesabı (gece × fiyat, basit kural desteği).
- Frontend'de `reservation` blok tipi + müsaitlik/talep API'si.
- Yeni talepte e-posta bildirimi (+ opsiyonel WhatsApp deep link).

### Kapsam dışı (sonraki fazlar — şimdi YAPMA)
- Online ödeme / sanal POS.
- Kanal yöneticisi / iCal / OTA senkronu (Booking.com vb.).
- Birim-bazlı envanter takibi (hangi spesifik daire) — MVP'de **adet bazlı** yeterli.
- Dinamik fiyatlandırma motoru (sezon/hafta sonu matrisleri) — MVP'de tek base_price + tek basit kural.

---

## 2. Mevcut Sistemi Önce Anla (geliştirmeye başlamadan)

Bu dosya varsayımlarla yazıldı; **kod tabanında doğrula** ve gerekirse uyarlama yap:

1. **Tenant altyapısı:** `stancl/tenancy` kullanılıyor. Tenant migration'ları nerede? (`database/migrations/tenant/` benzeri.) `App\Models\Tenant`, `tenants:migrate`, `tenants:run` komutlarını incele.
2. **Form sistemi:** Var olan `Form` / `FormField` / `FormSubmission` (ya da benzeri) modellerini bul. **Rezervasyon talebi bunların üzerine mi kurulmalı, yoksa ayrı `Reservation` tablosu mu?** Öneri: rezervasyonun kendine ait alanları (tarih, oda tipi, tutar, durum) yapısal olduğu için **ayrı `Reservation` modeli**; ama bildirim/gelen-kutusu altyapısını form sisteminden ödünç alabilirsin. (Bu repoda form sistemini araştıran bir çalışma yapıldı — `docs/`de veya form modellerinde izlerine bak.)
3. **Section template / blok kayıt sistemi:** `App\Models\SectionTemplate` (`theme_id`, `type`, `variation`, `render_mode`, `html_template`, `schema_json`, `default_content_json`). Bloklar `Page.sections_json` (version 2: `regions.{header,body,footer} → rows → columns → blocks`) içinde referanslanır. Blok: `{ type, variation, section_template_id, content, html_override }`.
4. **Frontend renderer:** `apps/frontend` içinde `basic-html-renderer.ts` blokları `section.html_override || section.html_template` ile **ham** basıyor (`{{{html}}}`). Rezervasyon bloğu **ham HTML + JS yeterli değil mi, yoksa özel React bileşeni mi gerekiyor?** (Bkz. §5.)
5. **Public API sözleşmesi:** `docs/public-api-contract.md` — frontend→Laravel API deseni, tenant çözümleme, `X-Internal-Token` SSR muafiyeti (bkz. tenant 429 notu). Rezervasyon API'sini bu desene uydur.
6. **Tenant medya:** görseller `/tenant-assets/{path}?tenant={id}` ile servis ediliyor (Spatie özel url_generator). RoomType görselleri bunu kullanmalı.

---

## 3. Veri Modeli (tenant DB, tenant-scoped)

### 3.1 `room_types`
| Alan | Tip | Not |
|---|---|---|
| id | pk | |
| slug | string, unique | ör. `1-1-suit`, `3-1-deluxe` |
| name | string | "1+1 Suit Daire" |
| summary | string, null | kısa açıklama |
| description | text, null | uzun açıklama |
| capacity_min / capacity_max | int | 2 / 3 |
| size_m2 | int, null | 55 |
| bedrooms / bathrooms | int | 1 / 1 |
| base_price | decimal(10,2) | gecelik temsili fiyat |
| currency | string(3) | `TRY` |
| unit_count | int | envanterdeki adet (11) — müsaitlik hesabı adet bazlı |
| amenities | json | ["Wi-Fi","Mutfak",...] |
| images | json | tenant-asset yolları |
| sort_order | int | |
| is_active | bool | |

### 3.2 `room_availability` (dolu/bloklu aralıklar)
| Alan | Tip | Not |
|---|---|---|
| id | pk | |
| room_type_id | fk | |
| date | date | tek gün satırı (aralık = çoklu satır) **veya** `start_date`/`end_date` — tercihini belgele |
| status | enum | `blocked` / `booked` |
| qty | int | o gün dolu birim sayısı; `qty >= unit_count` → o oda tipi o gün **dolu** |
| source | string, null | `manual` / `reservation` |

> MVP basit yol: onaylanan `Reservation` kayıtlarından müsaitliği **türet** (ayrı tablo olmadan). Ama elle bloklama (bakım, uzun süreli misafir) gerektiği için hafif bir `room_availability` tablosu önerilir. Kararı bu dosyada güncelle.

### 3.3 `reservations`
| Alan | Tip | Not |
|---|---|---|
| id | pk | |
| code | string, unique | insan-okur talep no (ör. `VS-2607-A3F`) |
| room_type_id | fk, null | |
| guest_name | string | |
| guest_phone | string | |
| guest_email | string, null | |
| checkin / checkout | date | |
| nights | int | türetilir |
| adults / children | int | |
| est_total | decimal(10,2), null | gece × base_price (+ kural) |
| message | text, null | |
| status | enum | `pending` / `confirmed` / `cancelled` (varsayılan pending) |
| source | string | `web` / `whatsapp` / `phone` |
| meta | json, null | UTM, arama parametreleri |
| timestamps | | |

### 3.4 (opsiyonel) `rate_rules`
Basit kural motoru: `type` (`min_nights_discount` vb.), `params` (json: `{min_nights:3, free_nth:4, discount:0.5}`), `is_active`. MVP'de tek kural yeterli; yoksa `est_total` hesabını serviste sabit tut.

**İlişkiler:** `RoomType hasMany Reservation`, `RoomType hasMany RoomAvailability`.

---

## 4. Servis Katmanı

- `AvailabilityService`
  - `bookedDates(RoomType, from, to): array<Y-m-d>` — takvimin "dolu" günlerini döner (qty≥unit_count veya blocked).
  - `isRangeAvailable(RoomType, checkin, checkout): bool`.
- `PricingService`
  - `estimate(RoomType, checkin, checkout): {nights, subtotal, discount, total}` — kural uygular.
- `ReservationService`
  - `createRequest(payload): Reservation` — doğrula, `code` üret, `est_total` hesapla, kaydet, bildirim tetikle.
  - `confirm(Reservation)` / `cancel(Reservation)` — durum + (varsa) müsaitlik bloklama.

---

## 5. Frontend / CMS Entegrasyonu

### 5.1 Blok tipi: `reservation`
Yeni `SectionTemplate` tipi/variation'ı. İki uygulama seçeneği — **kararı ver ve belgele:**

- **(A) Ham HTML + gömülü JS (en az sürtünme):** blok `html_template`'i statik HTML üretir; içindeki küçük `<script>` tenant API'sine `GET` müsaitlik + `POST` talep atar. Mevcut `basic-html-renderer.ts` ({{{html}}}) ile uyumlu, **renderer'a dokunmadan** çalışır. Site seeder'ı bu HTML'i döşer. **MVP için önerilen.**
- **(B) Özel Next.js bileşeni:** `apps/frontend`'e `ReservationBlock.tsx` eklenir; renderer blok `type==='reservation'` görünce bu bileşeni basar. Daha temiz durum yönetimi (takvim, sayaç, canlı tutar) ama renderer'a **eklemeli** dokunuş gerekir (mevcut davranışı bozmadan). Faz 2'ye uygun.

> Not: Vatan Suit için "pragmatik" karar alındı (native date input, canlı takvim modalı zorunlu değil). Bu, **(A)** seçeneğini MVP'de fazlasıyla yeterli kılar.

### 5.2 Public API (tenant-scoped, `docs/public-api-contract.md` desenine uygun)
- `GET /api/{tenant}/reservation/room-types` → aktif oda tipleri (fiyat, kapasite, görsel).
- `GET /api/{tenant}/reservation/availability?room_type=slug&from=&to=` → `{ booked: ["2026-07-10", ...] }`.
- `POST /api/{tenant}/reservation/requests` → talep oluştur (rate-limit + honeypot/captcha; **MeterTenantUsage 429**'a dikkat, SSR değil public POST).
- Doğrulama: ad+telefon zorunlu, checkout>checkin, tarih geçmiş değil.

### 5.3 Bildirim
Yeni talepte: tenant'ın bildirim e-postasına mail (RoomType + tarih + kişi + tutar özeti). Opsiyonel: admin'e WhatsApp deep link. Mailcow zaten tenant başına domain sağlıyor.

---

## 6. Admin Paneli

- **Oda Tipleri:** CRUD (fiyat, kapasite, adet, olanaklar, görsel yükleme → tenant-assets).
- **Müsaitlik:** oda tipi seç → takvimde gün(ler)i blocked işaretle/kaldır. Onaylı rezervasyonlar otomatik dolu görünür.
- **Rezervasyonlar (gelen kutusu):** liste (tarih/oda/durum filtresi), detay, durum değiştir (`pending→confirmed/cancelled`), not ekle. Yeni talep rozeti.
- Menü/izinler mevcut admin desenine uygun; **yeni bölüm**, mevcut menüleri bozmadan.

---

## 7. Seeder Sözleşmesi (site seeder'ının modülden bekledikleri)

Modül biterken şunları **sabit ve belgeli** bırak — `feat/otelvatan-site` seeder'ı bunlara bağlanacak:

1. **RoomType seed API'si:** bir tenant context'inde `RoomType::create([...])` ile oda tipi nasıl oluşturulur (zorunlu alanlar, slug kuralı).
2. **`reservation` blok tipi:** `SectionTemplate` `type`/`variation` adı + blok `content` şeması (hangi oda tiplerini gösterir, başlık, CTA vb.) — site seeder'ı `sections_json`'a bu bloğu ekleyecek.
3. **API endpoint yolları** ve beklenen istek/yanıt şekli (§5.2) — blok JS'i / native form buna POST edecek.
4. **Migration'ların tenant'a nasıl uygulandığı:** `tenants:migrate --tenants=otelvatan` çalıştığında rezervasyon tabloları geliyor mu?

Bu dördü netleşince site seeder'ı: (a) 2 RoomType (suit/deluxe) seed eder, (b) `rezervasyon` sayfasına `reservation` bloğu koyar, (c) İletişim'i mevcut CMS formuyla bağlar.

---

## 8. Kabul Kriterleri

- [ ] `tenants:migrate` yeni tenant'ta rezervasyon tablolarını kuruyor; **mevcut tenant'lar etkilenmiyor**.
- [ ] Admin'de oda tipi + müsaitlik + rezervasyon yönetilebiliyor.
- [ ] Frontend'de `reservation` bloğu müsaitliği API'den çekiyor, talep POST'u tenant DB'sine düşüyor, e-posta gidiyor.
- [ ] Aynı motor **ikinci bir tenant**ta (test) sıfır kod değişikliğiyle çalışıyor (jenerik doğrulaması).
- [ ] Mevcut CMS (Estetik Dermal, diğer siteler) davranışı **değişmedi** — regression yok.
- [ ] `feat/reservation-module` branch'i, `main`/`feat/multi-tenant` bozulmadan review'a hazır.

---

## 9. Açık Kararlar (geliştirici doldursun)

- [ ] `room_availability`: tek-gün satır mı, start/end aralık mı?
- [ ] Blok uygulaması: (A) ham HTML+JS mi, (B) React bileşeni mi?
- [ ] Rezervasyon, mevcut form/submission altyapısını yeniden mi kullanıyor yoksa tamamen ayrı mı?
- [ ] Fiyat kuralı MVP'de sabit mi (`rate_rules` ertelendi mi)?
- [ ] E-posta şablonu / gönderim yolu (Mailcow SMTP tenant başına?).
