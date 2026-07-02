# Rezervasyon (Lodging) Modülü — Seeder Sözleşmesi

> `feat/reservation-module` branch'inde inen **Konaklama (lodging)** vertical
> modülünün, site seeder'larının (ilk tüketici: `feat/otelvatan-site` → Vatan
> Suit) bağlanacağı **sabit arayüzü**. Spec: `docs/rezervasyon-modulu-proje-dosyasi.md` §7.
>
> Modül, Tours ile aynı **modüler monolit** desenini izler: `app/Modules/Lodging/`,
> `config/tenant_modules.php`'de kayıtlı, tenant başına opt-in.

---

## 1. Modülü bir tenant'a kurma

Rezervasyon tabloları **`tenants:migrate` ile GELMEZ** (o yalnızca core
`database/migrations/tenant/`'ı çalıştırır). Modül tabloları modül kurulumuyla gelir:

```bash
php artisan tenant:module:install lodging --tenant=otelvatan
```

Bu komut:
1. `Tenant.data['modules']`'a `lodging` ekler,
2. `app/Modules/Lodging/Database/migrations/`'ı o tenant DB'sinde çalıştırır,
3. modülün seeder'larını çalıştırır (şu an yok).

Gelen tablolar (hepsi tenant DB'sinde):

| Tablo | İçerik |
|---|---|
| `room_types` | Oda tipleri (fiyat, kapasite, adet, olanaklar, görseller) |
| `reservations` | Rezervasyon talepleri |
| `room_availabilities` | Bloklu/dolu tarih aralıkları (manuel + rezervasyon türevi) |
| `lodging_settings` | Tek satırlık modül ayarı (bildirim e-postası, WhatsApp, kod ön eki) |

`requires` boş — **ödeme modülü gerektirmez** (MVP talep-odaklı, online ödeme yok).

> Seeder içinden programatik kurulum: `app(\App\Services\Modules\ModuleManager::class)->install($tenant, 'lodging')`.

---

## 2. RoomType seed API'si

`App\Modules\Lodging\Models\RoomType` — tenant context'inde `create()`.

**Zorunlu:** `slug` (tenant içinde benzersiz), `name`.
**Slug kuralı:** `Str::slug()` (küçük harf, tireli), ör. `1-1-suit`, `3-1-deluxe`.

```php
use App\Modules\Lodging\Models\RoomType;

$suit = RoomType::create([
    'slug'         => '1-1-suit',
    'name'         => '1+1 Suit Daire',
    'summary'      => 'Şehir manzaralı, tam donanımlı suit.',
    'description'  => '…uzun açıklama (opsiyonel)…',
    'capacity_min' => 2,
    'capacity_max' => 3,
    'size_m2'      => 55,
    'bedrooms'     => 1,
    'bathrooms'    => 1,
    'base_price'   => 2500.00,   // decimal(10,2), MAJOR birim (kuruş değil)
    'currency'     => 'TRY',
    'unit_count'   => 11,        // envanter adedi — müsaitlik adet bazlı
    'amenities'    => ['Wi-Fi', 'Mutfak', 'Balkon', 'Klima'],
    'images'       => ['/tenant-assets/lodging/suit-1.jpg?tenant=otelvatan'],
    'sort_order'   => 1,
    'is_active'    => true,
]);
```

**Görseller — iki yol:**
- **Seeder (basit):** `images` JSON sütununa tenant-asset yolları yaz (yukarıdaki gibi).
- **Admin (zengin):** Spatie `gallery` koleksiyonuna yükleme (`$room->addMedia(...)->toMediaCollection('gallery')`).
- API çıktısında `imageUrls()` ikisini birleştirir (önce Spatie, yoksa `images`).

Vatan Suit için: **2 RoomType** seed edilir → `1-1-suit` (unit_count 11), `3-1-deluxe` (unit_count 8).

---

## 3. `reservation` blok tipi

Frontend bileşeni `render_mode=component`, blok **`type: "reservation"`** (veya `"rezervasyon"`)
ile eşleşir. **SectionTemplate satırı ŞART DEĞİL** — frontend bloğu `type` ile
component registry'den çözer. Site seeder'ı bloğu doğrudan `pages.sections_json`'a koyar.

**Blok `content` şeması (hepsi opsiyonel):**

| Alan | Tip | Açıklama |
|---|---|---|
| `title` | string | Bölüm başlığı (varsayılan "Rezervasyon Talebi") |
| `subtitle` | string | Alt açıklama |
| `room_type` | string | Ön seçili oda tipi **slug**'ı (boşsa "Farketmez") |
| `show_room_picker` | `"1"`/`"0"` | Oda tipi seçici gösterilsin mi (varsayılan `"1"`) |
| `cta_label` | string | Gönder butonu metni (varsayılan "Talep Gönder") |
| `success_message` | string | Özel teşekkür metni |

**`sections_json`'a eklenecek blok (v2 region layout, body → row → column → blocks):**

```json
{
  "id": "block_reservation_1",
  "type": "reservation",
  "variation": "",
  "render_mode": "component",
  "is_active": true,
  "content": {
    "title": "Rezervasyon Talebi",
    "subtitle": "Tarih ve oda tipini seçin, size dönelim.",
    "room_type": "",
    "show_room_picker": "1",
    "cta_label": "Talep Gönder"
  }
}
```

> İsteğe bağlı admin palet girişi: bir `SectionTemplate` (central, `theme_id` gerekli,
> `type='reservation'`, `render_mode='component'`) seed edilebilir — MVP için gerekli
> değil, blok programatik olarak sections_json'a eklendiği sürece render olur.

---

## 4. Public API (tenant-scoped)

Modül aktif tenant'ta yüklenir. Tenant çözümlemesi: tenant domain'inden otomatik
(same-origin), veya `?tenant={id}` / `X-Tenant-ID` header. `throttle` + honeypot +
(opsiyonel) Cloudflare Turnstile + `MeterTenantUsage` uygulanır.

### `GET /api/v1/lodging/room-types`
```json
{ "data": [
  { "slug": "1-1-suit", "name": "1+1 Suit Daire", "base_price": 2500,
    "currency": "TRY", "capacity_min": 2, "capacity_max": 3,
    "unit_count": 11, "amenities": ["Wi-Fi"], "images": ["…"], "…": "…" }
]}
```

### `GET /api/v1/lodging/availability?room_type={slug}&from=YYYY-MM-DD&to=YYYY-MM-DD`
```json
{ "booked": ["2026-07-10", "2026-07-11"] }
```
> `booked` = o oda tipinin **tamamen dolu** olduğu geceler (Σ dolu adet ≥ `unit_count`).
> Aralık **çıkış-hariç**: 10→12 = 10 ve 11 geceleri.

### `POST /api/v1/lodging/requests`
İstek gövdesi (JSON):
```json
{
  "room_type": "1-1-suit",        // opsiyonel (slug)
  "guest_name": "Ali Veli",        // zorunlu
  "guest_phone": "05551112233",    // zorunlu
  "guest_email": "ali@example.com",// opsiyonel
  "checkin": "2026-09-01",         // zorunlu, bugünden önce olamaz
  "checkout": "2026-09-04",        // zorunlu, checkin'den sonra
  "adults": 2, "children": 0,      // opsiyonel
  "message": "…",                   // opsiyonel
  "_hp_url": ""                     // honeypot — boş bırakılmalı
}
```
Başarılı yanıt:
```json
{ "success": true, "message": "…", "code": "VS-2609-A3F",
  "est_total": 6000, "currency": "TRY", "nights": 3 }
```
Hata: `422` + `{ "error": "…" }` veya `{ "errors": { "checkout": ["…"] } }`.
Seçili oda tipi + tarih aralığı doluysa `422` "tarihler dolu" döner.

---

## 5. Modül ayarları (bildirim + kod ön eki)

`App\Modules\Lodging\Models\LodgingSetting::current()` tek satırı döner (yoksa oluşturur).
Site seeder'ı ayarları şöyle bağlar:

```php
use App\Modules\Lodging\Models\LodgingSetting;

LodgingSetting::current()->update([
    'notification_email'      => 'rezervasyon@vatansuit.com', // yeni talep e-postası buraya
    'whatsapp_number'         => '905551112233',
    'reservation_code_prefix' => 'VS',                        // → VS-2609-A3F
    'default_currency'        => 'TRY',
]);
```

Yeni talepte bu e-postaya `ReservationRequestMail` gider (tenant SMTP profili üzerinden,
form sistemiyle aynı inline desende). Admin panelde her talepte "WhatsApp ile Yanıtla"
deep link'i misafirin numarasıyla üretilir.

---

## 6. Admin yüzeyi (seeder'ın bilmesi gerekmez, referans)

Modül aktifse admin kenar çubuğunda **Konaklama** grubu belirir:
Oda Tipleri · Müsaitlik · Rezervasyonlar · Ayarlar (`admin.lodging.*`).

---

## 7. Vatan Suit seeder'ının yapacakları (özet)

1. `tenant:module:install lodging --tenant=otelvatan` (veya ModuleManager::install).
2. `LodgingSetting::current()->update([...])` — e-posta, WhatsApp, prefix `VS`.
3. 2 × `RoomType::create([...])` — `1-1-suit` (11 adet), `3-1-deluxe` (8 adet).
4. `rezervasyon` sayfasının `sections_json`'ına `type: "reservation"` bloğu ekle (§3).
5. İletişim'i mevcut CMS formuyla bağla (bu modülün kapsamı dışında — core form sistemi).
