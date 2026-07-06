# main'e Reconcile — Lodging/Traefik core fix'leri

> otelvatan + dagkent otel çalışması sırasında bulunan **çekirdek/paylaşımlı** düzeltmelerin
> `main`'e alınması. Tenant-özel işler (seeder, chrome, field template) main'e **GİTMEZ**.
>
> **GÜNCELLEME (2026-07-05, v2):** ① ve ② **artık main'de** (doğrulandı). ② de merge oldu
> (`75a6033`). main session'da **zorunlu iş kalmadı**; yalnızca ③ opsiyonel (deploy'u main'e
> çekmek istenirse). Detaylar aşağıda.

## Durum özeti (güncel main'e karşı doğrulandı — `git ls-tree`/`git show` ile)

| # | Konu | Durum | Aksiyon |
|---|------|-------|---------|
| ① | Lodging public API routing (route:cache/404) | ✅ **main'de var** (`efc7ffc`) | Hiçbir şey yapma. ade5c67/206a01e cherry-pick ETME → çakışır |
| ② | Traefik per-domain `/api` → Laravel | ✅ **main'de var** (`75a6033`) | Hiçbir şey yapma. Tek dosya `app/Services/TraefikDynamicConfig.php`, backend router (priority 150) main'de mevcut |
| ③ | Tema-token (jenerik) daire-detay component | ⚙️ opsiyonel | Sadece **deploy'u main'den yapmak** istenirse gerekli |
| ④ | Settings API logo_url/favicon_url ön-eksiz anahtar fallback | 🔴 **main'e ALINMALI** | `feat/multi-tenant` `0d243b4` cherry-pick → `app/Http/Controllers/Api/SettingsController.php` |
| ⑤ | Article detay sayfalarında header/footer eksik (self-contained-chrome) | 🔴 **main'e ALINMALI** | `feat/multi-tenant` `e715c26` cherry-pick → `apps/frontend/app/[locale]/[...slug]/page.tsx` |
| ⑥ | next/image tenant medya kapaklarında "not a valid image" 400 | 🔴 **main'e ALINMALI** | `feat/multi-tenant` `3a93ae9` cherry-pick — 5 dosya (bkz. ⑥ detay) |

---

## ① ✅ TAMAM — Lodging routing fix zaten main'de
`efc7ffc` (bu session) main'in `LodgingModuleServiceProvider`'ını **koşulsuz boot** + public
tenancy zinciri (`UseSiteHostHeader`+`InitializeTenancyForPublicApi`+`MeterTenantUsage`) +
**`EnsureActiveTenantModule`** kapısına çevirdi. **Lodging + Tours ikisini** de kapsar.
- ⚠️ MD'nin eski önerisi `ade5c67 206a01e` (yalnızca lodging, `origin/feat/otelvatan-site`) artık
  **GEREKSİZ ve ÇAKIŞIR** — cherry-pick etme.

## ④ 🔴 ALINMALI — Settings API marka anahtarı fallback (logo/favicon)
**Sorun:** Admin marka formu (`resources/views/admin/settings/index.blade.php`) logo/favicon'u
**ön-eksiz** `settings[logo_url]`/`settings[favicon_url]` olarak kaydeder; ama `Api\SettingsController`
`design.logo_url`/`design.favicon_url` okuyordu → **tüm tenant'larda** header `{{logo_url}}` + `<head>`
favicon boş. (`StructuredDataGenerator` zaten ön-eksiz `logo_url` kullanıyor — doğru konvansiyon.)
**Fix (`feat/multi-tenant` `0d243b4`):** ön-eksiz anahtar önce, `design.*` geriye-dönük fallback:
```php
'logo_url'    => SiteSetting::get('logo_url',    '') ?: SiteSetting::get('design.logo_url',    ''),
'favicon_url' => SiteSetting::get('favicon_url', '') ?: SiteSetting::get('design.favicon_url', ''),
```
Tek dosya, additive, düşük risk. Paylaşımlı controller → cp + restart tüm tenant sitelerini düzeltir.
Aynı fix `feat/homeland-site` `4a0bd68`'de de var. **Aksiyon:** `git cherry-pick 0d243b4`.

## ⑤ 🔴 ALINMALI — Article detay sayfalarında header/footer eksik
**Sorun:** Self-contained-chrome tenant'larda (Estetik Dermal, otel'ler) header/footer HER Page'in
kendi `sections_json.regions`'ından basılır; `SiteShell` (`apps/frontend/components/layout/
site-shell.tsx`) salt pass-through, global chrome yok. Catch-all route'un Article-detay dalı
(`apps/frontend/app/[locale]/[...slug]/page.tsx`) bir `Page` DEĞİLDİR → hiçbir chrome almaz.
95 ürünlük Estetik Dermal kataloğuyla ilk kez fark edildi ama Article kullanan HER tenant'ı etkiler
(ör. DemoSeeder blog örneği de aynı boşluğa düşer).
**Fix (`feat/multi-tenant` `e715c26`):** ebeveyn sayfanın (`articlePage.slug`) `regions.header`/
`regions.footer`'ı ayrıca çekilip `RegionLayoutRenderer` ile Article içeriğinin etrafına sarılıyor
(ebeveynin gövdesi render edilmiyor, sadece chrome). Additive — önceden hiç chrome yoktu, regresyon
riski yok. Tek dosya. **Aksiyon:** `git cherry-pick e715c26`.

## ⑥ 🔴 ALINMALI — next/image tenant medya kapaklarında "not a valid image" 400
**Sorun:** `next/image`'in `/_next/image` optimize proxy'si "local" (relative) `src`'leri KENDİ
Next.js sunucusundan self-fetch etmeye çalışır. `TenantMediaUrlGenerator` (`de1056e`/`fb0d13d`)
bilinçli olarak **domain'siz/relative** URL üretir (`/tenant-assets/{path}?tenant={id}` — public
sitede `cms.grafcore.com` görünmesin diye). Ama bu path yalnızca Laravel backend'de var, Next'in
kendi sunucusunda böyle bir route yok → self-fetch 404 → Next "not a valid image" diyip **400**
döner. Article/tenant medya kullanan **her** `<Image>` kullanımını etkiler (Estetik Dermal'e özgü
değil — 95 ürünlük katalogla ilk kez görsel olarak fark edildi, muhtemelen önceden hep gizli kalmıştı).
**Fix (`feat/multi-tenant` `3a93ae9`):** yeni `isLocalMediaPath()` helper'ı
(`apps/frontend/lib/sections/component-registry.ts`) + relative src'lerde `unoptimized={true}` —
proxy bypass edilir, tarayıcı düz `<img>` gibi URL'i sayfanın kendi origin'ine göre çözer (asıl
tasarım amacı zaten buydu). Gerçek external (http/https) URL'lerde optimizasyon korunur. 5 dosya:
`article-list-section.tsx`, `gallery-section.tsx`, `logo-band-section.tsx`,
`app/[locale]/[...slug]/page.tsx`, `component-registry.ts` (Estetik Dermal'e özel
`products-grid-client.tsx` main'de yok, dahil değil). **Aksiyon:** `git cherry-pick 3a93ae9`
(bir dosyada — `products-grid-client.tsx` main'de olmadığı için — trivial "delete/modify" çakışması
çıkarsa, o dosyayı `git rm` ile cherry-pick'ten çıkar, diğer 5 dosya sorunsuz gelir).

## ② ✅ TAMAM — Traefik per-domain `/api` → Laravel zaten main'de
**Sorun (çözüldü):** `TraefikDynamicConfig` her tenant domaini için yalnızca frontend router'ı
(priority 100) üretiyordu; `/api` istisnası yoktu → tenant domaininde `/api` → Next.js gidiyordu.
**Fix (main'de):** her domain için `Host(domain) && PathPrefix(/api|/admin|…)` → Laravel, **priority 150**
backend router. Commit **`75a6033`** — `origin/main`'de doğrulandı (`app/Services/TraefikDynamicConfig.php`
satır ~184-191, `$backendService` + priority 150). `6ec60c2` (feat/dagkent-site) ile aynı içerik.
- **Yapılacak: yok.** Yeniden cherry-pick/merge etme.
- **Deploy notu:** main deploy edilirse `regenerate()` **artık güvenli** (fix main'de). Prod app
  image'ları da fix'li koddan (feat/dagkent-site build'i) → recurrence riski kapandı.

## ③ ⚙️ OPSİYONEL — Tema-token daire-detay component
main'de daire-detay component'i **yok** (bilinçli kaldırıldı — `909d99d`). `46b7714` (dagkent) bunu
**tema-token'a** (CSS var: `--color-*`, `--font-heading`, `--surface-soft`, `--placeholder-grad`, `--wa`;
fallback = otelvatan) çevirdi → tek build her oteli kendi chrome'uyla render eder. main'den otel
kurulacaksa gerekli; değilse ertelenebilir. Alınırsa **2 dosya** (dagkent seeder'ları HARİÇ):
- `apps/frontend/components/sections/blocks/room-detail-section.tsx` (dagkent `46b7714` jenerik sürüm)
- `apps/frontend/components/sections/section-registry.ts` (`daire-detay`/`room-detail` kaydı)
> **DİKKAT:** `46b7714` kapasite kilidinden (bu session, `59b9abc`/`749aad0`/`5131cd4`) ÖNCE yazıldıysa,
> jenerik component'e **misafir kapasite kilidini** de eklemek gerekir (sayaç max = capacity_max).

---

## Ek notlar (koordinasyon)
- **Workflow:** Bu belge "main PR-only" varsayıyordu, ancak main'e bu session **doğrudan push**
  yapıldı (başarılı). Branch-protection fiilen zorlanmıyor. Netleşene kadar bu **infra** fix'i
  yine de PR ile alıyoruz (güvenli taraf).
- **İki otel tenant'ı:** `feat/otelvatan-site` + `feat/dagkent-site`. Reusable modül işleri iki
  yönlü paylaşılmalı: bu session'ın **kapasite kontrolü** (`59b9abc`+`749aad0`) main'de → dagkent
  main'i merge edince alır; dagkent'in Traefik (②) + tema-token component'i (③) main'e gelir.

## main'e GİTMEYECEKLER (tenant-özel)
- `database/seeders/OtelVatan*`, `database/seeders/otelvatan/**` → `feat/otelvatan-site`
- `database/seeders/Dagkent*`, `database/seeders/dagkent/**` → `feat/dagkent-site`
- `docs/design_handoff_*`, runbook'lar, tema/chrome/field template'leri (tenant-özel)

## Doğrulama (reconcile sonrası)
- `main` build temiz: `php -l app/Services/TraefikDynamicConfig.php` + frontend `tsc`.
- Lodging tenant domaininde `curl https://<domain>/api/v1/lodging/room-types` → JSON (Next.js HTML değil).
- Yeni tenant + `regenerate()` sonrası domain `/api` → Laravel.
