# Tours Module & Modüler Monolit Geçiş Planı

> **Hedef:** iraspa-cms'i tek-vertical "kurumsal site" sistemi olmaktan, aynı çatı altında **kurumsal + e-ticaret + tur satış** yapabilen multi-vertical bir platforma çevirmek. İlk modül: **Tours** (cruise / paket / günlük tur).
> **Süre:** 3-6 ay (4 ay hedef + buffer)
> **Yaklaşım:** Modular monolith — NOT mikroservis, NOT external engine
> **Bu doküman:** Stratejik mimari kararlar + 6 fazlı yol haritası. Kod içermez; her faz ayrı bir PR/sprint olur.

---

## 0. Context (Neden bu doküman?)

### Şu anki durum
iraspa-cms Laravel 12 + Next.js 15 multi-tenant CMS, stancl/tenancy v3 ile database-per-tenant mimarisinde çalışıyor. Şu an sadece "kurumsal site" verticali aktif: page builder + sections_json, theme tokens, SEO, member portal, formlar.

### Tetikleyici ihtiyaç
Kullanıcı kitlesini genişletmek: e-ticaret yapan firmalar ve tur satan turizm acenteleri (cruise gemisi, çoklu-günlük paket turlar, günlük tek-noktadan turlar). Bu işletmeler kurumsal CMS'in editöryel altyapısının yetmediği, transaksiyonel (sepet, rezervasyon, ödeme, sipariş) bir domain ister.

### Verilen kararlar (kullanıcı onaylı)
1. **Modular monolith** — ayrı proje değil, headless engine entegrasyonu değil
2. **Önce Tours**, sonra Commerce (aynı altyapıyı yeniden kullanır)
3. **Hibrit yaklaşım** — Tour/Booking/Rezervasyon modelleri native; ödeme (Iyzico) entegre; kargo/voucher API'leri opsiyonel
4. **Tenant başına çoklu modül** — bir otel tenant'ı eş zamanlı kurumsal sayfa + paket tur satışı + opsiyonel hediyelik shop çalıştırabilmeli
5. **3-6 ay olgun ürün** — MVP'den fazlası: iade/iptal, kupon, multi-currency, multi-language, raporlar, voucher PDF

### Mevcut altyapı (keşif sonuçları)
- `Tenant` modeli JSON `data` kolonu pattern'i ile (custom field eklemek migration gerektirmiyor)
- Central DB'de `site_templates.industry` zaten var (corporate, salon, hotel, clinic, lawyer, real_estate) — multi-vertical düşüncesi mimaride hazır
- `SectionTemplate` şema-tabanlı + pluggable (`render_mode`, `component_key`)
- Admin panel **custom Blade + Livewire 4** stack'i — Filament/Nova **yok**, stack tutarlılığını koru
- composer.json psr-4: sadece `App\` → `app/`; `Modules/` namespace yok (kurulacak)
- Service provider'lar: AppServiceProvider, AiServiceProvider, TenancyServiceProvider (yenisi eklenecek)
- Spatie ekosistemi yüklü: media-library, permissions, translatable, sluggable, activitylog
- Next.js section registry basit Map (`apps/frontend/components/sections/section-registry.ts`) — modül-aware dynamic loader'a yükseltilecek

---

## 1. Stratejik Tasarım Kararları (Fazlara geçmeden önce lock'lanır)

### D1. Polymorphic Tour vs ayrı modeller (cruise / package / daily)

**Karar:** Tek `Tour` modeli + `type` discriminator + `type_config` JSON kolonu (Single Table Inheritance benzeri ama hafif).

**Rasyonel:**
- Üç tür de %70 ortak (slug, başlık, kategori, departure/date, capacity, price tiers, itinerary, media, SEO, multi-language). Ayrı tablo = üç katmanlı join, üç ayrı admin CRUD, üç ayrı API endpoint.
- Type-spesifik alanlar (cruise: kabin tipleri & güverte; package: dahil/hariç hizmetler; daily: günlük saatler, buluşma noktası) `type_config` JSON kolonunda.
- Cruise'a özgü güçlü relation gereken yer (`CabinType`, `ItineraryDay`) yine ayrı tablo — ancak parent `tours` tek tablo.
- Eloquent'te `Tour::query()->cruise()` local scope + opsiyonel `CruiseTour extends Tour` child model (tip-spesifik metotlar için).
- Karşı argüman (ayrı modeller): Reddedildi — admin "Turlar" tek liste görmek isteyecek, raporlar tek tablodan çok kolay.

### D2. Ödeme nereye? Tours içinde mi, paylaşılan modül mü?

**Karar:** Ayrı paylaşılan `app/Modules/Payments/` modülü.

**Rasyonel:** Commerce de aynı Iyzico'yu kullanacak, üyelik aidatı da. Gateway abstraction'ı tek yerde:
- `App\Modules\Payments\Contracts\PaymentGateway` interface
- `App\Modules\Payments\Gateways\IyzicoGateway` (Phase 2), ileride Stripe/PayTR
- `PaymentTransaction` (tenant DB) + `central.payment_audit_log` (compliance audit)
- Booking polymorphic `payable` ile Payment'a bağlanır

### D3. Booking tablosu nerede?

**Karar:** Booking, Reservation, Passenger, Voucher, Refund hepsi **tenant DB'de**. Central DB sadece audit trail.

**Rasyonel:**
- KVKK uyumu — bir acentenin müşteri verisi başka tenant DB'sinde sızıntı taşımaz
- Tenant silinirse tüm rezervasyon verisi temiz biçimde gider
- **İstisna:** `central.payment_audit_log` — Iyzico webhook'larının asla kaybolmaması için minimal kayıt (tenant_id, booking_ref, amount, gateway_ref, status), reconciliation için

### D4. Modül migration'ları yeni tenant'a nasıl yüklenir?

**Karar:** İki katmanlı yapı:
1. Core tenant migrations (`database/migrations/tenant/*.php`) — her tenant'a otomatik (mevcut davranış değişmez)
2. Modül-spesifik migrations (`app/Modules/Tours/Database/migrations/`) — sadece modül aktif tenant'larda

**Yeni artisan komutları:**
- `php artisan tenant:module:install tours --tenant=acentem` — `data.modules`'a ekler + module migrations'ı çalıştırır
- `php artisan tenant:module:uninstall tours --tenant=acentem [--drop-tables]` — default drop-false
- `php artisan tenants:migrate --module=tours` — deploy'da tüm tours-enabled tenant'larda module migrations

`TenancyServiceProvider`'daki tenant-creation pipeline'a `MigrateEnabledModules` job eklenir.

### D5. Coupon/Discount sistemi

**Karar:** Paylaşılan `app/Modules/Commerce/Discount/` ama Tours fazında minimum yüzey build edilir.

Aynı kupon hem Tour bookingine hem Commerce sepetine uygulanabilmeli. Polymorphic `discountable_type` + Strategy pattern (PercentDiscount, FixedAmountDiscount, FirstBookingDiscount). Phase 5'te Tours için yeter; Phase 6 Commerce ile genişler.

### D6. Tur arama/filtre — Laravel mi, Meilisearch mi?

**Karar:** Laravel-side + indexed columns + JSON path index. Meilisearch escape hatch.

- 5K-50K tur ölçeğine kadar `where + whereJsonContains + paginate` yeter
- `tours.search_index` denormalize TEXT + fulltext index
- Phase 5'te `TourSearchService` interface bırakılır — Meilisearch ileride tak-çıkar

---

## 2. Faz Yapısı

| Faz | Hedef | Süre |
|-----|-------|------|
| **0** | Modular monolith altyapısı (modüller, tenant flags, conditional admin/API) | 1.5 hafta |
| **1** | Tours veri modeli + migrations + temel model relationship'leri | 2.5 hafta |
| **2** | Booking engine + Payments modülü + Iyzico entegrasyonu | 2.5 hafta |
| **3** | Admin UX (Blade + Livewire, calendar, manifest, voucher PDF) | 3 hafta |
| **4** | Next.js frontend (catalog, detail, booking wizard) | 3 hafta |
| **5** | Operations & polish (coupon, refund, multi-currency, reports, email pipeline) | 4 hafta |
| **6** | Commerce (gelecek — Phase 0 mimarisinin validasyonu) | Ayrı planlama |

**Toplam: ~16.5 hafta (~4 ay).** %20 buffer ile 5 ay; üst sınır (6 ay) E2E test + security audit + pilot tenant onboarding'i kapsar.

---

## Phase 0 — Modular Monolith Altyapısı (1.5 hafta)

### Goal
Tenant'ın hangi modülleri kullandığını flag'leyebileceğimiz, modüllerin kendi migration/route/view/service provider'larını yönetebildiği namespace yapısı.

### Deliverables

**Backend:**
- `app/Modules/` dizini. Yapı: `app/Modules/{Tours,Payments,Commerce}/{Console,Database/migrations,Http/Controllers,Http/Resources,Livewire,Models,Providers,Services,routes,resources/views}/`
- `composer.json` psr-4: `"App\\Modules\\": "app/Modules/"`
- `app/Modules/Tours/Providers/ToursModuleServiceProvider.php` — route/view/migration register eder; `tenancy()->tenant?->hasModule('tours')` false ise hiçbir şey kaydetmez
- `app/Modules/Payments/Providers/PaymentsModuleServiceProvider.php`
- `bootstrap/providers.php`'a koşulsuz olarak register; provider içi tenant-aware kararı verir
- `app/Models/Tenant.php`'a metotlar: `hasModule()`, `enableModule()`, `disableModule()`, `enabledModules()`
- `app/Services/Tenants/ModuleAccess.php` — central context'te belirli tenant için modül kontrolü
- Artisan komutları: `tenant:module:install`, `tenant:module:uninstall`, `tenant:module:list`
- `TenancyServiceProvider`'da `MigrateEnabledModules` job pipeline'a eklenir

**Frontend:**
- `apps/frontend/lib/api/types.ts` — `SiteConfig.modules: string[]`
- `app/Http/Controllers/Api/SiteController.php` — `/api/v1/site` payload'ına `modules` eklenir
- `apps/frontend/lib/sections/component-registry.ts` — module-bazlı conditional dynamic import
- `apps/frontend/lib/modules/registry-loader.ts` (yeni) — `loadModuleSections(modules: string[])`

**Admin:**
- `resources/views/admin/layouts/app.blade.php` sol menü `@if($tenant->hasModule('tours'))` koşullu
- `resources/views/admin/tenants/edit.blade.php`'a "Modüller" sekmesi (checkbox)
- `app/Http/Controllers/Admin/TenantController.php` — modül enable/disable + install command tetikleyici

### Verification
1. Yeni tenant `tours` aktif değil. `/admin` menüsünde "Turlar" görünmez.
2. `php artisan tenant:module:install tours --tenant=acentem-test` → `data.modules = ["tours"]`, dummy `_tours_smoke_test_table` o tenantta yaratılır.
3. `tenants:migrate --module=tours` tüm tours-enabled tenantlarda module migrations çalıştırır.
4. Frontend `/api/v1/site` payload'unda `modules: ["tours"]` döner; disabled tenantta `modules: []`.

### Reuse
- `Tenant.data` JSON pattern (`ai_settings` ile aynı)
- Stancl mevcut migration pipeline (sadece job zincirini uzat)

---

## Phase 1 — Tours Veri Modeli (2.5 hafta)

### Goal
Cruise/package/daily üç türü destekleyen, multi-language ve multi-currency hazırlığı yapılmış, kapasite-takip edilebilir tur kataloğu.

### Tablolar (`app/Modules/Tours/Database/migrations/`, tenant DB)

| Tablo | Amaç |
|-------|------|
| `tour_categories` | Hiyerarşik (parent_id), staudenmeir/laravel-adjacency-list |
| `tours` | `type` enum (cruise/package/daily), `type_config` JSON, slug, status, currency, base_price, capacity_default, `search_index` TEXT |
| `tour_translations` | Multi-language (title, description, short_desc, meta_*) |
| `tour_dates` | Departure/time slot. `starts_at`, `ends_at`, `capacity_override`, `price_override`, `capacity_left`, `status` |
| `tour_itineraries` | Tour itinerary parent |
| `tour_itinerary_days` | Day-by-day (day_number, title, description, port_name, geo) |
| `tour_cabin_types` | Cruise-specific: code, name, capacity, price_modifier, deck |
| `tour_price_tiers` | adult/child/infant/senior + early-bird (JSON conditions) |
| `tour_extras` | Add-on'lar (transfer, sigorta, içecek paketi) |
| `tour_includes`, `tour_excludes` | İçerik listesi |

**Booking domain (schema lock, doldurma Phase 2):**

| Tablo | Amaç |
|-------|------|
| `bookings` | booking_ref unique, member_id?, tour_date_id, status, total_amount, currency, customer_snapshot JSON |
| `booking_passengers` | ad/soyad, TCKN/passport, DOB, passenger_type, cabin_id? |
| `booking_extras` | seçilen extra'lar |
| `vouchers` | Spatie media-library üzerinden PDF metadata + storage |
| `refunds` | partial/full refund kayıtları |

**Media:** `Tour`, `TourItineraryDay`, `TourCabinType` Spatie `HasMedia` implement eder.

### Model dosyaları
- `app/Modules/Tours/Models/{Tour,TourCategory,TourTranslation,TourDate,TourItinerary,TourItineraryDay,TourCabinType,TourPriceTier,TourExtra,Booking,BookingPassenger,BookingExtra,Voucher,Refund}.php`
- `app/Modules/Tours/Models/Concerns/HasTourType.php`
- `app/Modules/Tours/Enums/{TourType,BookingStatus,RefundStatus,PassengerType}.php`
- `app/Modules/Tours/Models/Cruise/CruiseTour.php` (opsiyonel child)

### Verification
1. Seeder: 1 cruise + 3 kabin tipi + 5 departure + 7-day itinerary + 4 price tier. `Tour::cruise()->with([...])->first()` çalışır.
2. Aynı tour TR + EN çevirisiyle gelir.
3. Spatie media ile galeri + PDF brochure eklenir.
4. `tours:reindex` komutu `search_index` doldurur.

### Reuse
- `Language`, `Currency` central modelleri
- Spatie translatable, sluggable, media-library
- Mevcut `Translation` modeli pattern'i

---

## Phase 2 — Booking Engine + Payments (2.5 hafta)

### Goal
Concurrent-safe rezervasyon, deposit/balance ödeme, Iyzico 3DS entegrasyonu.

### Deliverables

**Payments modülü (`app/Modules/Payments/`):**
- `Contracts/PaymentGateway.php` — `initialize3DS()`, `complete()`, `refund()`, `webhook()`
- `Gateways/IyzicoGateway.php`
- `Models/{PaymentTransaction,PaymentRefund}.php` (tenant DB)
- `Services/PaymentDispatcher.php` — polymorphic `payable`
- `database/migrations/YYYY_MM_DD_create_central_payment_audit_table.php` + `App\Models\Central\PaymentAuditLog`
- `config/payments.php` — gateway config (BYOK: tenant `data.iyzico_keys`)
- `routes/tenant_api.php` Iyzico webhook (CSRF muaf, signature verify)

**Tours booking engine:**
- `app/Modules/Tours/Services/Booking/BookingService.php`
- `app/Modules/Tours/Services/Booking/CapacityLockService.php` — `DB::transaction` + `lockForUpdate`
- `app/Modules/Tours/Services/Booking/QuoteService.php` — total + extras + tax
- `app/Modules/Tours/StateMachines/BookingStateMachine.php` — elle ENUM transition tablosu (kütüphane bağımlılığı yok)
- `app/Modules/Tours/Events/{BookingCreated,BookingConfirmed,BookingCancelled,BookingExpired}.php`
- `app/Modules/Tours/Listeners/SendBookingConfirmationEmail.php`
- `app/Modules/Tours/Jobs/ExpireUnpaidReservation.php` (20dk hold)
- `app/Modules/Tours/Http/Controllers/Api/BookingController.php` (public)

### Concurrency
- `tour_dates.capacity_left` integer; booking yaratırken `DB::transaction(fn() => $date = TourDate::lockForUpdate()->find(); throw if capacity_left < n; decrement)`
- Pending booking 20dk hold; 20:01'de `ExpireUnpaidReservation` queued job capacity'yi iade

### Verification
1. PHPUnit + parallel job: 1 koltuk kalmış departure'a 2 simultaneous request. Biri success, diğeri `CapacityExhaustedException`.
2. Iyzico sandbox test kart ile 3DS flow → `reserved → confirmed`.
3. Confirmed booking refund → Iyzico webhook → `refunded`, capacity iade.
4. `central.payment_audit_log`'da reconciliation row mevcut.

### Reuse
- Mevcut `App\Jobs` queue
- `Member` opsiyonel booking sahibi
- `SmtpProfile` tenant email

---

## Phase 3 — Admin UX (3 hafta)

### Goal
Mevcut Blade + Livewire 4 stack'inde Tour CRUD, departure calendar, booking management, manifest/voucher export. **Filament/Nova KESİNLİKLE eklenmez** — stack tutarlılığı kritik.

### Deliverables (`app/Modules/Tours/Livewire/Admin/`)
- `Tours/{Index,Create,Edit,Translate}.php`
- `Tours/Tabs/{BasicTab,ItineraryTab,CabinsTab,DatesTab,PricingTab,MediaTab,SeoTab}.php` (sekmeli edit)
- `Tours/DepartureCalendar.php` — FullCalendar.io v6 community
- `Bookings/{Index,Show,Manifest}.php`
- `Bookings/Filters.php`
- `app/Modules/Tours/Services/Export/ManifestExporter.php` — maatwebsite/excel
- `app/Modules/Tours/Services/Export/VoucherPdfRenderer.php` — `spatie/laravel-pdf` (composer require)
- `app/Modules/Tours/resources/views/admin/...` Blade views (mevcut `resources/views/admin/pages/edit.blade.php` deseni baz)
- Routes: `routes/admin.php`'da `if (tenant()?->hasModule('tours'))` koşullu grup

### Navigation
- Sol menü: "Turlar" (Index + Categories + Calendar), "Rezervasyonlar"
- Sadece `tenant->hasModule('tours')` true ise

### Verification
1. Admin Cruise yaratır → 7 günlük itinerary + 3 kabin + 12 departure → publish
2. Phase 2 API'den booking oluşturulur
3. Admin booking listesinde görür, manifest Excel indirir (ad/TCKN/kabin sütunları)
4. Voucher PDF jenerasyonu → S3 → admin "Voucher indir" butonu

---

## Phase 4 — Next.js Frontend Booking Flow (3 hafta)

### Goal
Tur kataloğu, detay sayfası, booking wizard. Mevcut section-registry pattern'ine bağlanır.

### Deliverables (`apps/frontend/`)
- `apps/frontend/modules/tours/` (yeni)
  - `components/sections/{tour-grid,tour-card,tour-detail-hero,tour-itinerary-timeline,tour-cabin-selector,tour-departure-list,tour-extras,tour-booking-cta}.tsx`
  - `components/wizard/{DateStep,PassengersStep,ExtrasStep,PaymentStep,ConfirmStep}.tsx`
  - `components/wizard/BookingWizard.tsx`
  - `lib/api/tours.ts` — typed client (list, detail, quote, booking create, payment init)
  - `lib/types/tour.ts`
  - `register.ts` — section registry'ye `tour-grid`, `tour-detail` ekler
- `apps/frontend/app/[locale]/turlar/page.tsx` — ISR (tag: `tenant:{id}:tours`)
- `apps/frontend/app/[locale]/turlar/[slug]/page.tsx`
- `apps/frontend/app/[locale]/turlar/[slug]/rezervasyon/page.tsx` — wizard

### Revalidation
- Mevcut `FrontendRevalidator` Tour kaydedildiğinde `tenant:{id}:tours` ve `tenant:{id}:tour:{slug}` purge eder
- Departure capacity her booking'de değişir → 30sn revalidate veya CSR fetch (capacity hassas — CSR önerilir)

### Verification
- Staging tenant E2E (Playwright): anasayfa → tur listesi → detay → tarih → 2 yetişkin → ödeme (Iyzico sandbox) → onay → email
- Locale switch (TR/EN) tour çevirilerini gösterir

### Reuse
- `section-renderer.tsx` ve registry
- `UseSiteHostHeader` middleware
- Mevcut theme tokens (`site.tokens`)
- i18n (`apps/frontend/lib/i18n.ts`)

---

## Phase 5 — Operations & Polish (4 hafta)

### Goal
Mature ürün: indirim, iptal politikası, multi-currency, multi-language tamamlandı, rapor + email pipeline tamam.

### Deliverables

**Discount/coupon** (`app/Modules/Commerce/Discount/` — Tours yüzeyi minimum):
- `Models/{Coupon,DiscountRule}.php`
- `Services/CouponEngine.php` — `apply($booking, $code)` polymorphic
- Admin Livewire CRUD

**Cancellation policy:**
- `app/Modules/Tours/Models/CancellationPolicy.php` — tarih-bandlı (days_before, refund_percent)
- Tour ve TourCategory'ye `cancellation_policy_id`
- `Services/RefundCalculator.php`

**Multi-currency:**
- TCMB exchange rate cron: `app/Console/Commands/SyncExchangeRatesCommand.php` (günlük 04:00)
- `central.exchange_rates`
- `app/Services/CurrencyConverter.php`
- Frontend: para birimi seçici

**Multi-language tour content:**
- Phase 1'de `tour_translations` schema var; bu fazda admin UX translation tab tamamlanır

**Reports:**
- `app/Modules/Tours/Livewire/Admin/Reports/{Sales,Occupancy,CustomerSegment}.php`
- ApexCharts.js veya Chart.js
- Excel export

**Email pipeline:**
- `app/Modules/Tours/Mail/{BookingConfirmation,VoucherDelivery,PaymentReceived,CancellationConfirmed,ReminderBeforeDeparture}.php`
- `SendDepartureRemindersCommand` (günlük 09:00, T-3 reminder)
- Admin email template editor (SiteSetting + Blade snippet pattern)

### Verification
- Kupon uygulanmış booking → T-30 günde iptal → %50 refund hesabı → Iyzico refund → müşteriye iptal emaili
- EUR currency booking → TL'ye convert → ödeme TL üzerinden
- Sales dashboard'unda son 30 gün geliri, en çok satan turlar, occupancy

---

## Phase 6 — Commerce (Future)

Phase 0'ı validate etmek için kısa not: `app/Modules/Commerce/` aynı pattern'i takip eder. `Payments` modülünü reuse eder. `Discount` polymorphic olduğu için aynı kupon Cart + Tour Booking'e uygulanır. `tenant:module:install commerce` yeterli. Tahmini: 6-8 hafta ayrı planlama.

---

## 3. Reuse — Mevcut Kod Tabanından

| Reuse | Nereden | Neden |
|-------|---------|-------|
| `Tenant.data` JSON | `app/Models/Tenant.php` (mevcut `ai_settings`) | `modules` flag burada, schema migration yok |
| Stancl tenant pipeline | `app/Providers/TenancyServiceProvider.php` | Module migration job tak |
| `Language` + `Translation` | `app/Models/{Language,Translation}.php` | Tour multi-language |
| `Currency` | `app/Models/Currency.php` | Tour base_price currency |
| Spatie media-library | `database/migrations/tenant/0008_create_media_table.php` | Galeri + voucher PDF |
| Spatie translatable, sluggable | composer.json | TourTranslation + slug |
| Spatie permissions | `AdminRole`, `AdminPermission` | tours.view, bookings.refund |
| maatwebsite/excel | composer.json | Manifest export |
| `Member`, `MemberGroup` | `app/Models/Member.php` | Booking sahibi opsiyonel |
| `SmtpProfile` | `app/Models/SmtpProfile.php` | Tenant özel SMTP |
| `FrontendRevalidator` | `app/Services/FrontendRevalidator.php` | Next.js cache invalidate |
| Section registry pattern | `apps/frontend/components/sections/section-registry.ts` | tour-* section'lar |
| `UseSiteHostHeader` | `app/Http/Middleware/UseSiteHostHeader.php` | Booking API tenant context |
| Admin Blade layout | `resources/views/admin/layouts/app.blade.php` | Tours admin aynı kabuk |
| `AdminTenantAccess` | `app/Models/AdminTenantAccess.php` | Acente admin otomatik erişim |
| Livewire 4 patterns | `app/Livewire/Admin/` | Form, validation, modal |

---

## 4. Çapraz Kesen Konular

### Test stratejisi
- Her faz minimum: Unit (Service), Feature (HTTP), Integration (DB transaction)
- **Phase 2 zorunlu:** concurrency stress test (`tests/Feature/Tours/CapacityRaceTest.php`)
- **Phase 4 zorunlu:** Playwright E2E
- PHPUnit 11 + Mockery mevcut stack

### Observability
- `sentry/sentry-laravel` mevcut → Tours hataları otomatik
- Booking state transition log → `spatie/laravel-activitylog` (mevcut)
- `docs/sentry-observability-plan.md` ile uyumlu

### Backward compatibility
- Mevcut kurumsal tenant'lar etkilenmez — `tours` flag'siz, kod yüklenmez
- Module migrations sadece module-enabled tenant DB'lere
- Frontend section'ları lazy import → kurumsal bundle büyümez

### Riskler & Mitigation
1. **Module SP'lerinin tenant context'ine bağımlılığı** — central context (cron, queue) yanlış davranabilir. Mitigation: `runInTenantContext($tenant, fn() => ...)` helper.
2. **Capacity race condition** — Phase 2 explicit lock testi şart.
3. **Iyzico webhook reliability** — central audit log + reconciliation cron (`tours:reconcile-payments` günlük).
4. **PDF/Excel jobs queue worker'ı sıkıştırma** — ayrı queue (`pdf`, `exports`), supervisor config.

---

## 5. Phase 0 Bağımlılıkları (Phase 1'e geçmeden lock)

1. `composer.json` psr-4'e `App\Modules\` eklenir
2. PDF kütüphanesi: **`spatie/laravel-pdf`** (browsershot wrapper) — ✅ **KİLİTLİ**
3. `iyzico/iyzipay-php` SDK — **BYOK modeli**: her acente kendi Iyzico API key'lerini `Tenant.data.iyzico_keys` altında (encrypted) saklar. Para direkt acentenin Iyzico hesabına gider, platform üzerinden geçmez. ✅ **KİLİTLİ**
4. FullCalendar.io community edition (Premium gerekmez)
5. State machine: **elle ENUM transition tablosu** (kütüphane bağımlılığı yok)
6. **Object storage: Cloudflare R2** — tenant başına klasörleme: `cms-uploads/tenants/{tenant_id}/...`. `config/filesystems.php`'a `r2` disk eklenir; `config/tenancy.php` `filesystem.disks` array'ine `r2` katılır; `root_override` pattern'i R2 için `tenants/%tenant%/` olur. Spatie media-library `r2` disk'i tüm modüller için default storage. ✅ **KİLİTLİ**
7. **Guest checkout**: Booking için Member kaydı zorunlu değil. `bookings.member_id` nullable; guest booking için `customer_snapshot` JSON (ad/email/telefon) yeterli. Booking sonrası "hesap oluştur" opsiyonel CTA. ✅ **KİLİTLİ**

---

## 6. Critical Files (öncelik sırasıyla)

**Phase 0 — en üst öncelik:**
- `/Volumes/Dev/iraspa-cms/composer.json` — psr-4 namespace eklenir
- `/Volumes/Dev/iraspa-cms/app/Models/Tenant.php` — `hasModule()`, `enableModule()`
- `/Volumes/Dev/iraspa-cms/app/Providers/TenancyServiceProvider.php` — `MigrateEnabledModules` job
- `/Volumes/Dev/iraspa-cms/app/Modules/Tours/Providers/ToursModuleServiceProvider.php` (yeni)
- `/Volumes/Dev/iraspa-cms/app/Console/Commands/Tenant/ModuleInstallCommand.php` (yeni)

**Phase 1 — kritik:**
- `/Volumes/Dev/iraspa-cms/app/Modules/Tours/Database/migrations/0001_create_tour_categories_table.php`
- `/Volumes/Dev/iraspa-cms/app/Modules/Tours/Database/migrations/0002_create_tours_table.php`
- `/Volumes/Dev/iraspa-cms/app/Modules/Tours/Database/migrations/0003_create_tour_dates_table.php`
- `/Volumes/Dev/iraspa-cms/app/Modules/Tours/Database/migrations/0010_create_bookings_table.php`
- `/Volumes/Dev/iraspa-cms/app/Modules/Tours/Models/Tour.php`

**Phase 2 — kritik:**
- `/Volumes/Dev/iraspa-cms/app/Modules/Payments/Contracts/PaymentGateway.php`
- `/Volumes/Dev/iraspa-cms/app/Modules/Payments/Gateways/IyzicoGateway.php`
- `/Volumes/Dev/iraspa-cms/app/Modules/Tours/Services/Booking/BookingService.php`
- `/Volumes/Dev/iraspa-cms/app/Modules/Tours/Services/Booking/CapacityLockService.php`
- `/Volumes/Dev/iraspa-cms/routes/tenant_api.php` — webhook + booking routes

**Frontend (Phase 0 + 4):**
- `/Volumes/Dev/iraspa-cms/apps/frontend/lib/api/types.ts` — `SiteConfig.modules`
- `/Volumes/Dev/iraspa-cms/apps/frontend/components/sections/section-registry.ts` — dynamic loader
- `/Volumes/Dev/iraspa-cms/app/Http/Controllers/Api/SiteController.php` — `modules` payload

---

## 7. Platform Gelir Modeli — KİLİTLİ

**Seçim: (A) Saf aylık abonelik.** ✅

Tenant'lar plana göre sabit aylık ödeme yapar (örn. Kurumsal ₺499/ay, Tours ₺1.499/ay, Tours+Commerce ₺2.499/ay). Booking başına komisyon **yok** — para BYOK Iyzico ile direkt acentenin hesabına gider, platform takip etmez.

Phase 5'te raporlar **sadece tenant-side** (acente kendi satış/doluluk/müşteri raporlarını görür). Platform-side aggregate komisyon raporu / invoicing cron yok.

İleride hacim büyürse hibrit modele geçiş yine mümkün (booking tablosundaki veri her zaman var, aggregate sorgu yazılabilir), ama Phase 5'te bunu build etmiyoruz.
