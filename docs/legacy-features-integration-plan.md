# Eski Tur Sistemi → Yeni iraspa-cms Entegrasyon Planı

> **Bağlı dokümanlar:**
> - `docs/tours-module-plan.md` — orijinal Tours modülü roadmap (Phase 0-6)
> - `docs/legacy-tour-system-features.md` — eski sistem feature envanteri + 20 locked decision
>
> **Bu doküman:** Eski sistemden çıkardığımız mimari kararları **mevcut Phase 0-3 kodu üstüne**
> nasıl entegre edeceğimizi, hangi sırayla, hangi dosyalara dokunarak yapacağımızı somutlaştırır.
>
> **Kapsam dışı:** Feribot vertical (`TourType::Ferry`).  Sonraki iterasyonda eklenir,
> mevcut plana etkisi sıfır (sadece enum'a 4. değer + type_config alanı).

---

## 1. Context — Neden Bu Plan

Phase 0-3 zaten deploy edildi (`5886eb4` commit).  Tour wizard, booking engine,
Iyzico 3DS, admin UX temel halinde çalışıyor.  **Ama** eski sistem analizi
gösterdi ki cruise satışı için kurduğumuz veri modeli yetersiz:

| Konu | Phase 1'de (mevcut) | Eski sisteme göre eksik |
|---|---|---|
| Cabin yönetimi | Per-tour `TourCabinType` (her cruise'da yeniden yazılır) | Per-ship master + company-level grouping |
| Pricing | 2-boyutlu `TourPriceTier` (adult/cabin) | 5-boyutlu (date × group × cabin × person-tier × calc method) |
| Ship | `type_config.ship_name` string | `ShipCompany` → `Ship` → `Cabin` 3-katmanlı master |
| Port | `port_name` string | Port master (geo + content + flag + image) |
| Itinerary | Tek stop/day | Multi-stop/day (1,1,1,2,3,3 pattern) |
| Destination | `TourCategory` (generic) | Destination master + Port m2m |
| Kampanya | Yok (Phase 5'e ertelenmiş) | `Campaign` + discount engine + per-date m2m |
| Sales flow | Tek model (Iyzico zorunlu) | 5 farklı CTA mode (payment/contact/quote/info) |
| Vitrin | `is_featured` boolean | Section-based showcase (filtre + manuel pin) |
| Copy operations | Yok | 3 tip (tour / date / pricing — inter/intra) |

**Yaklaşım:** Hard cut migration.  Henüz canlı tenant verisi yok — mevcut
Phase 1 `TourCabinType` / `TourPriceTier` tablolarını drop edip yeni mimari
ile değiştiriyoruz.  Eğer ileride deploy edilmiş tenant'lar olursa, data
migration script'i ile aktarılır.

---

## 2. Yeni Mimari — Yüksek Seviye

```
┌─────────────────────────────────────────────────────────────────────────┐
│ GLOBAL MASTERS (tenant DB, tüm tour'lar paylaşır)                       │
│                                                                         │
│   CabinCategory  ──┐                                                    │
│                    │   (5 seed: İç/Dış/Balkonlu/Okyanus/Suite)         │
│   ShipCompany ──┐  │                                                    │
│       ↓         │  │                                                    │
│       Ship ─────┼──┼──> Cabin (cabin_category_id FK, ship_id zorunlu)  │
│       ↓         │  │                                                    │
│   CabinGroup ───┘  │   (cabin_group_cabin m2m pivot)                   │
│                                                                         │
│   Port (geo + content)                                                  │
│   Destination ←──→ Port (destination_ports m2m)                        │
│   Campaign (date range + discount type + polymorphic apply)            │
│   TourTag (marketing tags: "Mini Cruise", "Ultra Lüks" vs)             │
│   tenant_info_extras (vize/havaalanı vergisi master)                   │
└─────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────┐
│ TOUR ENTITY (master'lara FK + m2m bağlantılar)                          │
│                                                                         │
│   Tour                                                                  │
│     - ship_id          (cruise/ferry için FK)                          │
│     - sku                                                               │
│     - pricing_mode     (per_person / per_person_group / per_reservation)│
│     - sales_status     (5 farklı CTA mode)                             │
│     - includes_flight  + flight_info JSON                              │
│     - copied_from_tour_id  (Tur Kopyala için)                          │
│     - existing translations, dates, extras, includes                   │
│                                                                         │
│   ├── tour_destinations (m2m → Destination)                            │
│   ├── tour_marketing_tags (m2m → TourTag)                              │
│   ├── tour_categories (m2m → TourCategory)                             │
│   ├── tour_itineraries → days → stops (multi-stop)                     │
│   └── tour_price_groups → tour_cabin_prices (5D matrix)                │
└─────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────┐
│ TENANT CONFIG                                                           │
│                                                                         │
│   TourTypeSetting (cruise/package/daily için ayrı satır)               │
│     - label, url_slug                                                   │
│     - listing_page_id, detail_page_id                                   │
│     - passenger_form_fields (JSON dynamic builder)                     │
│     - additional_form_fields (JSON)                                     │
│     - allowed_payment_options (json: ['full','10','25','50'])          │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## 3. Faz Yapısı — 4 Ana Blok

| Faz | Hedef | Süre | Çıktı |
|---|---|---|---|
| **1.5** | Data model refactor — master entity'ler, yeni pricing | 4-5 hafta | ~30 yeni tablo, 2 tablo drop, services rewrite |
| **3.5** | Admin UX — 9-tab wizard, master CRUDs, copy services, dynamic forms | 4-5 hafta | ~25 controller, ~50 blade view, ~10 service |
| **4 (rev)** | Frontend — showcase section'lar, 5 CTA flow, 3 pricing mode UI, Leaflet | 6-8 hafta | ~30 React component, 6 section, booking wizard |
| **5** | Campaign engine + voucher PDF + reviews + multi-currency | 4-5 hafta | Discount runtime, PDF generator, review moderation |

**Toplam: ~18-23 hafta (4.5-5.5 ay).**  Phase 4 başlamadan Phase 1.5 + 3.5
tamamlanmış olmalı (frontend yazılırken backend stabil olsun).

---

## Phase 1.5 — Data Model Refactor (4-5 hafta)

### 1.5.a Yeni Master Tablolar (Hafta 1-2)

#### Migration sırası (FK dependency)

```
1. cabin_categories + translations              (no deps)
2. ship_companies + translations                (no deps)
3. ships + translations                         (FK: ship_company_id)
4. cabins + translations                        (FK: ship_id, cabin_category_id)
5. cabin_groups + translations                  (FK: ship_company_id)
6. cabin_group_cabin (pivot)
7. ports + translations                         (no deps)
8. destinations + translations                  (no deps)
9. destination_ports (pivot)
10. tour_tags + translations                    (marketing tags)
11. tenant_info_extras                          (vize/vergi master)
```

#### Yeni dosyalar

```
app/Modules/Tours/Database/migrations/
  2026_06_01_010001_create_cabin_categories_tables.php
  2026_06_01_010002_create_ship_companies_tables.php
  2026_06_01_010003_create_ships_tables.php
  2026_06_01_010004_create_cabins_tables.php
  2026_06_01_010005_create_cabin_groups_tables.php
  2026_06_01_010006_create_ports_tables.php
  2026_06_01_010007_create_destinations_tables.php
  2026_06_01_010008_create_tour_tags_tables.php
  2026_06_01_010009_create_tenant_info_extras_table.php

app/Modules/Tours/Models/
  CabinCategory.php + CabinCategoryTranslation.php
  ShipCompany.php + ShipCompanyTranslation.php
  Ship.php + ShipTranslation.php
  Cabin.php + CabinTranslation.php
  CabinGroup.php + CabinGroupTranslation.php
  Port.php + PortTranslation.php
  Destination.php + DestinationTranslation.php
  TourTag.php + TourTagTranslation.php
  TenantInfoExtra.php

app/Modules/Tours/Database/seeders/
  CabinCategorySeeder.php       (5 default kategori)
  TourTagSeeder.php             (12+ marketing tag default)
```

### 1.5.b Pricing Refactor (Hafta 2-3)

#### Migration sırası

```
1. tour_price_groups + tour_price_group_tour_date (pivot)
2. tour_cabin_prices
3. DROP tour_price_tiers (Phase 1)
4. DROP tour_cabin_types (Phase 1)
```

#### Yeni dosyalar

```
app/Modules/Tours/Database/migrations/
  2026_06_08_020001_create_tour_price_groups_tables.php
  2026_06_08_020002_create_tour_cabin_prices_table.php
  2026_06_08_020003_drop_tour_price_tiers_and_cabin_types.php

app/Modules/Tours/Models/
  TourPriceGroup.php + TourPriceGroupTranslation.php
  TourCabinPrice.php

app/Modules/Tours/Enums/
  PricingMode.php          (per_person / per_person_group / per_reservation)
  CalculationMethod.php    (standart_doublex2 / person_sum / flat_cabin)
  SalesStatus.php          (5 değer)

app/Modules/Tours/Services/Booking/
  QuoteService.php         (REWRITE — pricing mode + calc method aware)
  PriceCalculator.php      (yeni — 3 mode için ayrı calculator strategy)
```

#### Quote Calculator Strategy Pattern

```php
interface PriceCalculator {
    public function calculate(TourCabinPrice $price, PassengerSet $passengers): int;
}

class StandartDoublex2Calculator implements PriceCalculator { ... }
class PersonSumCalculator implements PriceCalculator { ... }
class FlatCabinCalculator implements PriceCalculator { ... }

class QuoteService {
    public function compute(...): Quote {
        // 1. Tour'un pricing_mode'unu oku
        // 2. TourPriceGroup'unu bul (tarih + cabin_group filter)
        // 3. Her cabin için TourCabinPrice'tan matrix al
        // 4. CalculationMethod'a göre Calculator seç
        // 5. Toplam hesapla
        // 6. Aktif Campaign'leri uygula (Phase 5'te)
    }
}
```

### 1.5.c Tour Entity Enrichment (Hafta 3-4)

#### Migration

```
2026_06_15_030001_extend_tours_table.php
  ALTER TABLE tours ADD:
    - ship_id (FK to ships, nullable — cruise/ferry için)
    - sku (string, nullable, unique per tenant)
    - pricing_mode (enum)
    - sales_status (enum, default 'live_payment')
    - includes_flight (bool, default false)
    - flight_info (json, nullable)
    - copied_from_tour_id (FK to tours.id, nullable)
```

#### Tour ↔ Master pivot tables

```
2026_06_15_030002_create_tour_master_pivots.php
  tour_destinations (tour_id, destination_id, sort_order)
  tour_marketing_tags (tour_id, tour_tag_id)
  tour_categories (tour_id, tour_category_id)    -- m2m artık, FK değil
```

#### Itinerary refactor

```
2026_06_15_030003_refactor_tour_itineraries.php
  CREATE tour_itinerary_stops (
    id, tour_itinerary_day_id, position,
    port_id (FK), title, description,
    arrival_time, departure_time,
    accommodation (text),
    custom_text
  )
  
  DROP tour_itinerary_days kolonları:
    location, geo_lat, geo_lng, arrival_time, departure_time, meals
  
  EKLE tour_itinerary_days:
    summary (text, day-level özet)
```

Eski TourItineraryDay verisi (varsa) → TourItineraryStop'a 1:1 taşı.

### 1.5.d Campaign + TourTypeSetting (Hafta 4-5)

#### Migration

```
1. campaigns + translations + 4 pivot (tours/dates/categories/destinations)
2. tour_type_settings (tenant_id, tour_type, label, url_slug,
   listing_page_id, detail_page_id, collect_passenger_info,
   passenger_form_fields json, additional_form_fields json,
   allowed_payment_options json, description)
```

#### Models

```
app/Modules/Tours/Models/
  Campaign.php + CampaignTranslation.php
  TourTypeSetting.php

app/Modules/Tours/Enums/
  CampaignDiscountType.php
  CampaignAppliesTo.php

app/Modules/Tours/Services/Booking/
  CampaignResolver.php    (aktif kampanyaları bulur — Phase 5'te apply edilecek)
```

### 1.5.e Verification

```bash
php artisan tenant:module:install tours --tenant=test
# Beklenen: 30+ yeni tablo, 5 cabin category seed'lendi, 12 tour tag seed'lendi

php artisan tinker
> $tour = Tour::factory()->for(Ship::factory()->for(ShipCompany::factory()))->create();
> $tour->ship->cabins->count();  // should match seed
> $quote = app(QuoteService::class)->compute($tour, $date, $draft);
> // 3 farklı pricing_mode için ayrı test
```

Test dosyaları:
```
tests/Feature/Tours/CabinHierarchyTest.php
tests/Feature/Tours/PricingModeCalculationTest.php
tests/Feature/Tours/CampaignResolverTest.php
tests/Feature/Tours/TourReplicatorTest.php  (Phase 3.5'e taşınabilir)
```

---

## Phase 3.5 — Admin UX Modernization (4-5 hafta)

### 3.5.a 9-Tab Tour Wizard (Hafta 1-2)

Mevcut tek-sayfa tour form'unu **9-tab wizard**'a refactor et.  Livewire 4
component'leri ile her tab kendi state'ini tutar.

```
app/Modules/Tours/Livewire/Admin/TourWizard/
  Wizard.php                  (parent component, tab state)
  Tabs/
    GenelBilgilerTab.php      (Tab 1 — 17 alan + 3 taksonomi seçici)
    RotaTakvimiTab.php        (Tab 2 — itinerary day/stop builder, conditional)
    GenelFiyatlarTab.php      (Tab 3 — starting price + info-only extras)
    TarihFiyatlarTab.php      (Tab 4 — multi-date price group + matrix)
    AciklamalarTab.php        (Tab 5 — N adet ordered content section)
    SeoTab.php                (Tab 6 — meta fields)
    HaritaTab.php             (Tab 7 — Leaflet point editor + auto-route)
    ResimlerTab.php           (Tab 8 — Spatie media + Ship/Port template)
    YorumlarTab.php           (Tab 9 — moderation queue)

resources/views/livewire/admin/tour-wizard/
  wizard.blade.php
  tabs/*.blade.php           (her tab için partial)
```

**Tab 2 koşullu görünürlük:**
```php
@if($tour->type->hasMultiDayItinerary())
    @include('livewire.admin.tour-wizard.tabs.rota-takvimi')
@endif
```

### 3.5.b Master Data Admin CRUDs (Hafta 2-3)

Drill-down navigation pattern (`Gemi Firmaları › MSC Cruises › MSC EURIBIA › Cabin`):

```
app/Modules/Tours/Http/Controllers/Admin/
  ShipCompanyController.php       resource: /admin/ship-companies
  ShipController.php              resource: /admin/ship-companies/{c}/ships
  CabinController.php             resource: /admin/ship-companies/{c}/ships/{s}/cabins
  CabinGroupController.php        resource: /admin/ship-companies/{c}/cabin-groups
  CabinCategoryController.php     resource: /admin/cabin-categories
  PortController.php              resource: /admin/ports
  DestinationController.php       resource: /admin/destinations
  CampaignController.php          resource: /admin/campaigns
  TourTagController.php           resource: /admin/tour-tags
  TenantInfoExtraController.php   resource: /admin/info-extras
  TourTypeSettingController.php   resource: /admin/tour-settings/{type}

resources/views/admin/[entity]/
  index.blade.php
  create.blade.php / edit.blade.php (form)
  _row.blade.php (list partial — drag handle, edit, delete, drill-down btn)
```

**"Data completeness" indicator pattern:**
Ship listesinde kabini olmayan satırlar kırmızı `Kabin Eklenmemiş` tooltip ile
işaretlenir (eski sistemden ilham).

### 3.5.c Copy Services (Hafta 3)

```
app/Modules/Tours/Services/Replication/
  TourReplicator.php
    replicate(Tour $src, bool $withDates, bool $withPrices): Tour
  
  TourDateReplicator.php
    bulkCreate(Tour $tour, array $dates, ?TourPriceGroup $applyGroup): array
  
  PricingReplicator.php
    copyFromTour(Tour $src, Tour $tgt, bool $withDates, bool $withPrices)
    copyBetweenDates(TourDate $src, TourDate $tgt)

app/Modules/Tours/Http/Controllers/Admin/
  TourReplicationController.php   (3 endpoint: tour copy, date copy, pricing copy)

resources/views/admin/tours/_replicate-modals.blade.php
  - Tur Kopyala modal (target tour'a kopyala)
  - Tarihleri Kopyala modal (mevcut tour'a yeni tarihler)
  - Fiyat Kopyala modal (2 mod: inter-tour + date-to-date)
```

**Slug generation:** `{original-slug}-kopya-N` (N artırılır collision'da).
**Tracking:** `Tour.copied_from_tour_id` FK + admin UI'da "Bu tour'un kopyaları" linkı.

### 3.5.d Dynamic Form Builder (Hafta 4)

TourTypeSetting içindeki `passenger_form_fields` ve `additional_form_fields`
JSON arraylerini admin'in görsel olarak yönetebilmesi için:

```
app/Modules/Tours/Livewire/Admin/FormBuilder/
  FormBuilder.php             (drag-drop sortable field list)
  FieldRow.php                (her field: label, type, options, col_str)

resources/views/livewire/admin/form-builder/
  builder.blade.php
  field-row.blade.php
  field-type-modal.blade.php  (text/select/radio/checkbox/date için config)
```

Admin runtime'da:
- Drag-drop ile alan ekle/sırala/sil
- Her alan: Başlık + tip + option values + column structure
- "Önizleme" butonu — generated form HTML preview

### 3.5.e Bulk Operations + Filter Improvements (Hafta 4-5)

#### Toplu Fiyat Güncelleme (eski sistemdeki "İndirimli Fiyat Güncelle")

```
app/Modules/Tours/Services/BulkPriceUpdater.php
  applyDiscount(array $tourIds, string $type, decimal $value)
  // type: percent / fixed_amount
  // value: 20 (%20 indirim) veya 50000 (50 TL indirim)

app/Modules/Tours/Http/Controllers/Admin/BulkActionController.php
```

#### Süresi Geçen Tour Filtresi

```
Tour::query()->expiredDepartures()  // scope:
  whereDoesntHave('dates', fn ($q) => $q->where('starts_at', '>=', now()))
```

#### Cascade Search Filters

```
- Tur Tipi (cruise/package/daily)
- Destinasyon (cruise tipinde Pire/Mikonos, package'ta Antalya/Kapadokya)
- Bölge/Liman (Port master)
- Tur Seçeneği (marketing tags)
- ShipCompany / Ship
- Gösterim: Yayınlanan / Yayınlanmayan / Süresi Geçen / Hepsi
- Tur Kodu (SKU) / Tur Adı
```

### 3.5.f Verification

- Tüm 12+ master entity için CRUD smoke test
- Tour wizard 9 tab kayıt + yükle round-trip
- 3 copy service test (Phase 1.5 testlerinden taşınır)
- Dynamic form builder JSON output validation
- Bulk discount uygulama sonrası tour fiyatları doğru

---

## Phase 4 — Frontend Revize (6-8 hafta)

### 4.a Showcase Section System (Hafta 1-2)

```
apps/frontend/components/sections/blocks/tours/
  TourGridSection.tsx              (filtreli liste, manuel pinned tours)
  TourCarouselSection.tsx          (auto-scroll slider)
  TourFeaturedBannerSection.tsx    (manuel tour + custom banner)
  TourSearchWidgetSection.tsx      (embedded search formu)
  TourByDestinationSection.tsx     (destination bazlı grid)
  TourByCampaignSection.tsx        (aktif kampanya tour'ları)

apps/frontend/lib/sections/component-registry.ts
  + register('tour-grid', TourGridSection)
  + register('tour-carousel', TourCarouselSection)
  + ... (6 yeni section)

backend: app/Models/SectionTemplate seed
  6 yeni section template seed'i + JSON schema (filter + display config)
```

Admin Page Builder'a bu yeni section'ları drag-drop yerleştirir.

### 4.b 5 CTA Flow (Hafta 2-3)

```
apps/frontend/components/booking/
  BookingCTA.tsx                   (sales_status'e göre render switch)
  flows/
    LivePaymentFlow.tsx            (Iyzico 3DS booking wizard)
    LiveContactFlow.tsx            (sade contact form, ödeme yok)
    QuoteWithAccommodationFlow.tsx (teklif al + konaklama alanları)
    QuoteWithoutAccommodationFlow.tsx
    InfoOnlyDisplay.tsx            (CTA yok, sadece içerik)

apps/frontend/lib/api/tours.ts
  + POST /api/v1/tours/contact-reservation
  + POST /api/v1/tours/quote-request
```

Backend:
```
app/Modules/Tours/Models/
  TourQuoteRequest.php             (Quote inquiry records)
  TourContactReservation.php       (No-payment reservation requests)

app/Modules/Tours/Http/Controllers/Api/
  QuoteRequestController.php
  ContactReservationController.php
```

### 4.c 3 Pricing Mode UI (Hafta 3)

Booking wizard'ın price preview adımı `Tour.pricing_mode`'a göre değişir:

| Mode | UI |
|---|---|
| `per_person` | Cabin selector + passenger tier breakdown (S/D/T/Q/C/B) |
| `per_person_group` | Cabin selector + grup boyutu + per-person rate |
| `per_reservation` | Tek "rezervasyon ücreti" + total |

### 4.d Map + Cabin Selector (Hafta 4-5)

```
apps/frontend/components/maps/
  TourRouteMap.tsx                 (Leaflet, port koordinatlarından polyline)

apps/frontend/components/booking/
  CabinSelector.tsx                (cruise için ship.cabins + price matrix)
  CabinCategoryFilter.tsx          (Inside/Outside/Balcony/Suite filter chips)
```

Auto-map generation:
```typescript
const generateMapFromItinerary = (stops: TourItineraryStop[]) => {
  const points = stops.map(s => [s.port.lat, s.port.lng]);
  return <Polyline positions={points} />;
};
```

### 4.e Destination + Campaign Landing Pages (Hafta 5-6)

```
apps/frontend/app/[locale]/destinasyonlar/[slug]/page.tsx
  - Destination cover + description
  - Ports list (with photos)
  - Compatible tours grid (filtered by destination)
  - Map showing all ports

apps/frontend/app/[locale]/kampanyalar/[slug]/page.tsx
  - Campaign banner + terms
  - Date range countdown
  - Applicable tours grid
  - Discount details
```

### 4.f Tour Detail Page Refactor (Hafta 6-7)

Eski Phase 4 tour-detail componentine yeni alanları entegre et:
- Ship card (name, star_rating, facilities icons, deck plans link)
- Itinerary timeline (multi-stop per day)
- Cabin matrix (per-cabin pricing visible)
- Info-only extras display ("Bilmeniz gerekenler" block)
- Auto-aggregated images (Ship.gallery + Port.gallery + Tour.gallery)
- Map (auto-generated from itinerary)
- CTA (5 flow'dan biri)

### 4.g Frontend Verification (Hafta 7-8)

```
apps/frontend/__tests__/booking-flows.spec.ts (Playwright)
  - Live payment flow (Iyzico sandbox)
  - Live contact flow
  - Quote request flow (with/without accommodation)
  - Info-only display
```

---

## Phase 5 — Operations + Polish (4-5 hafta)

### 5.a Campaign Discount Engine (Hafta 1-2)

Phase 1.5'te yazılan `CampaignResolver` + yeni `CampaignApplier`:

```
app/Modules/Tours/Services/Campaign/
  CampaignResolver.php             (aktif kampanyaları bul)
  CampaignApplier.php              (Quote'a uygula)
  Strategies/
    PercentDiscountStrategy.php
    FixedAmountStrategy.php
    SecondPersonFreeStrategy.php
    OneFullOneHalfStrategy.php
    FreeChildStrategy.php
```

QuoteService entegrasyonu:
```php
public function compute(...): Quote {
    $quote = $this->basicCompute(...);
    $quote = app(CampaignApplier::class)->apply($quote, $tour, $tourDate);
    return $quote;
}
```

### 5.b Voucher PDF Generator (Hafta 2-3)

```
composer require spatie/laravel-pdf

app/Modules/Tours/Services/Voucher/
  VoucherPdfGenerator.php
  VoucherTemplateRenderer.php

resources/views/vouchers/
  default.blade.php                (tenant override-able template)

app/Modules/Tours/Listeners/
  GenerateVoucherOnBookingConfirmed.php
    Triggered by: BookingConfirmed event
    Action: Spatie\Browsershot ile PDF render → R2'ye yükle → email attach
```

QR code: voucher.code'dan `https://{tenant}/voucher/verify/{code}` URL'i,
operator phone scan ederse `Voucher::markRedeemed()` çağrılır.

### 5.c Multi-Currency (Hafta 3)

```
app/Console/Commands/SyncTcmbExchangeRatesCommand.php   (günlük cron 04:00)
app/Models/Central/ExchangeRate.php
app/Services/CurrencyConverter.php

Frontend: para birimi seçici (TL/EUR/USD) — fiyatlar dynamic convert
```

### 5.d Email Pipeline (Hafta 3-4)

```
app/Modules/Tours/Mail/
  BookingReservationMail.php       (mevcut, geliştir)
  BookingConfirmedMail.php          (mevcut, voucher PDF attach)
  BookingCancelledMail.php          (yeni)
  BookingExpiredMail.php            (yeni)
  DepartureReminderMail.php         (T-3 gün, T-1 gün)
  QuoteRequestReceivedMail.php      (yeni — admin'e)
  QuoteResponseMail.php             (yeni — müşteriye)

app/Modules/Tours/Console/SendDepartureRemindersCommand.php
  Schedule: günlük 09:00, T-3 ve T-1 reminder
```

### 5.e Reviews Moderation + Manual Add (Hafta 4)

```
app/Modules/Tours/Http/Controllers/Admin/TourReviewController.php
  index    /admin/tour-reviews                 (filter by status)
  approve  /admin/tour-reviews/{id}/approve
  reject   /admin/tour-reviews/{id}/reject
  create   /admin/tour-reviews/create          (manuel ekleme)

apps/frontend/components/reviews/
  ReviewSubmitForm.tsx
  ReviewsList.tsx                              (tour detail sayfasında)
```

### 5.f Reports (Hafta 4-5)

```
app/Modules/Tours/Livewire/Admin/Reports/
  SalesReport.php                  (revenue over time, by tour type)
  OccupancyReport.php              (departure capacity utilization)
  CustomerSegmentReport.php        (member vs guest, repeat customers)
  CampaignPerformanceReport.php    (campaign usage + discount given)
```

---

## 4. Mevcut Phase 1 Kodu — Etkilenen Dosyalar

### Silinecek (Phase 1.5'te)

```
app/Modules/Tours/Models/TourCabinType.php
app/Modules/Tours/Models/TourPriceTier.php
app/Modules/Tours/Database/migrations/2026_05_27_000005_create_tour_cabin_types_table.php
app/Modules/Tours/Database/migrations/2026_05_27_000006_create_tour_pricing_tables.php (tour_price_tiers kısmı)
```

### Değiştirilecek

```
app/Modules/Tours/Models/Tour.php
  + ship_id, sku, pricing_mode, sales_status, includes_flight, flight_info, copied_from_tour_id

app/Modules/Tours/Services/Booking/QuoteService.php
  REWRITE — new pricing model + calculator strategy

app/Modules/Tours/Enums/TourType.php
  + Ferry (case eklenir ama Feribot kapsam dışı; Phase 6'da kullanılır)

app/Modules/Tours/Models/TourItineraryDay.php
  - location, geo_lat, geo_lng, arrival_time, departure_time, meals kolonları kalkar
  + TourItineraryStop relation

app/Modules/Tours/Models/Booking.php
  + sales_flow_metadata (json — quote request, contact reservation için)

resources/views/admin/tenants/_modules-panel.blade.php
  - mevcut "tours" entry'sini değiştir (artık Phase 1.5 features ile geliyor)
```

### Korunacak (değişiklik yok)

```
app/Modules/Payments/* (tüm Iyzico stack)
app/Modules/Tours/Models/Booking, BookingPassenger, BookingExtra, Voucher, Refund
app/Modules/Tours/Services/Booking/CapacityLockService
app/Modules/Tours/StateMachines/BookingStateMachine
app/Modules/Tours/Events/* + Listeners/* + Jobs/*
app/Services/Modules/ModuleManager, ModuleRegistry (Phase 0)
```

---

## 5. Migration Stratejisi — Hard Cut

**Henüz canlı tenant verisi yok** — Phase 0-3 deploy edildi ama gerçek tur/booking
girilmedi.  Bu yüzden migration düz tutulur:

1. Phase 1.5 install komutu yeni 30+ tablo yaratır
2. Eski `tour_cabin_types` ve `tour_price_tiers` tabloları drop edilir
3. `tours.type_config.ship_name` string'leri `tours.ship_id`'ye taşınmaz
   (henüz veri yok) — yeni tour'lar Ship FK ile yaratılır

Eğer ileride **prod tenant verisi** çıkarsa:
- `app/Modules/Tours/Console/MigrateLegacyTourData.php` artisan komutu yazılır
- Eski string'leri normalize edip Ship/Port/Cabin master'larında oluşturur
- Tour'a FK atar
- Eski tabloları arşivler (drop yerine `_archived_` prefix)

---

## 6. Risk + Mitigation

| Risk | Etki | Mitigation |
|---|---|---|
| Yeni 30+ tablo migration sırasında bozulur | Phase 1.5 kullanılmaz | Migration sırası FK dependency'ye göre kesin yazılır + her migration'ın down() metoduyla rollback test edilir |
| QuoteService rewrite booking flow'u kırar | Production'da rezervasyon çalışmaz | Phase 1.5'te yeni QuoteService yazılır AMA Phase 1'in eski versiyonu paralel tutulur, feature flag ile (`tenant.data.use_new_pricing`) — kademeli geçiş |
| Dynamic form builder JSON şeması frontend'le uyumsuz | Booking wizard render edemez | TypeScript shared type definition (`apps/frontend/lib/types/forms.ts`) — backend JSON generator ile birebir match |
| Ship/Port master data tenant'a manuel girilmesi yorucu | Onboarding uzar | Phase 1.5'te bulk import CSV tool + popüler cruise line'lar için seed data (MSC + Costa + RC ship listesi public) |
| Phase 4 frontend Phase 1.5 backend hazır olmadan başlar | Backend değişikliği frontend'i bozar | Faz sırası katı — Phase 4 sadece 1.5 + 3.5 tamamlandıktan sonra başlar |

---

## 7. Verification — Her Faz Sonu

### Phase 1.5 Sonu
- `php artisan tenant:module:install tours --tenant=test` → 30+ tablo, seeds
- Tinker: Ship → Cabin → CabinGroup → Tour → TourPriceGroup → TourCabinPrice
  factory chain çalışır
- QuoteService 3 pricing mode için doğru total döndürür
- CapacityRaceTest hâlâ green (mevcut testler bozulmadı)

### Phase 3.5 Sonu
- Admin tüm master CRUD'lara erişebilir
- 9-tab Tour wizard tüm tab'larda kayıt yapar
- 3 copy service end-to-end çalışır (tinker + browser test)
- Dynamic form builder JSON çıktısı frontend'in beklediği şemada

### Phase 4 Sonu
- Playwright E2E: tour detail → 5 farklı CTA flow
- Showcase section'lar admin page builder'da render olur
- Cruise booking wizard: cabin select + person tier → quote → 3DS → confirm
- Quote request flow: form submit → admin dashboard'da görünür

### Phase 5 Sonu
- Aktif Campaign uygulanmış booking'in total'i doğru
- Voucher PDF inebilir + email'e attach olarak gelir
- T-3 reminder cron'u çalışır
- Reviews moderation queue + manuel ekleme
- Multi-currency: aynı tour TL/EUR/USD farklı görünür

---

## 8. Critical Files — Hızlı Referans

### Yeni yazılacak (toplam ~90 dosya)

```
app/Modules/Tours/Database/migrations/   ~18 migration
app/Modules/Tours/Models/                ~25 model + translation
app/Modules/Tours/Services/              ~12 service
app/Modules/Tours/Enums/                 ~6 yeni enum
app/Modules/Tours/Http/Controllers/Admin/ ~15 controller
app/Modules/Tours/Livewire/Admin/        ~10 component (wizard + form builder)
resources/views/admin/tours-*/           ~40 blade view
apps/frontend/components/sections/blocks/tours/ ~6 section
apps/frontend/components/booking/        ~10 flow component
apps/frontend/components/maps/           ~2 component
```

### Mevcut değişecek (~10 dosya)

```
app/Modules/Tours/Models/Tour.php
app/Modules/Tours/Models/TourItineraryDay.php
app/Modules/Tours/Services/Booking/QuoteService.php
app/Modules/Tours/Enums/TourType.php
app/Modules/Tours/Database/migrations/2026_05_27_000005_create_tour_cabin_types_table.php (drop)
app/Modules/Tours/Database/migrations/2026_05_27_000006_create_tour_pricing_tables.php (partial drop)
app/Modules/Tours/Providers/ToursModuleServiceProvider.php (yeni event/service register)
resources/views/admin/partials/sidebar-nav.blade.php (yeni master link'leri)
apps/frontend/lib/types.ts (yeni Tour fields)
apps/frontend/lib/api/tours.ts (yeni endpoint'ler)
```

---

## 9. Sıra ile Başlamak İçin İlk Adımlar

Plan onaylanırsa **Phase 1.5.a** (master tablolar) ile başlayacağız:

1. ✅ Locked Decisions tablosu hazır (`legacy-tour-system-features.md` §0)
2. ✅ Bu plan dosyası hazır
3. ⏳ **CabinCategory + 5 seed migration yazılır** (en küçük blok, en az dependency)
4. ⏳ ShipCompany migration + model
5. ⏳ Ship migration + model (Tab 1 + Tab 2 alanları)
6. ⏳ Cabin migration (ship_id zorunlu, cabin_category_id zorunlu)
7. ⏳ CabinGroup migration + pivot
8. ⏳ Verification: tinker'da Cabin → Ship → Company chain çalışır
9. ⏳ Phase 1.5.a commit + push

Sonra 1.5.b (pricing), 1.5.c (Tour enrich), 1.5.d (Campaign + Setting), 1.5.e (test).

---

## 10. Phase 6+ Backlog (Feribot dahil — bu planın dışında)

- **Feribot vertical:** `TourType::Ferry` + sefer-tarife + araç bileti
- **Commerce module** (Phase 6 — Tours mimarisini reuse eder)
- **AI-assisted tur content generator** (mevcut AI altyapısı kullanılır)
- **Affiliate / partner pricing** (B2B kanal)
- **Loyalty program** (member points)
- **Push notifications** (mobile booking app için backend hazır olur)

Bu backlog Phase 1.5-5 tamamlanmadan açılmaz.

---

## 11. Onay

Bu plan onaylanırsa hemen Phase 1.5.a ile başlıyorum.  Revize istersen:
- Hangi faz/madde değişsin
- Hangi karar farklı olsun
- Hangi sıra revize edilsin

söyle, planı güncelliyorum.
