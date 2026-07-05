# main'e Reconcile — Lodging/Traefik core fix'leri

> otelvatan + dagkent otel çalışması sırasında bulunan **çekirdek/paylaşımlı** düzeltmelerin
> `main`'e alınması. Tenant-özel işler (seeder, chrome, field template) main'e **GİTMEZ**.
>
> **GÜNCELLEME (2026-07-05):** Bu belge, bu tarihteki main durumuna (`45a6b9d`) göre yeniden
> değerlendirildi. Aşağıdaki ① **artık main'de** (ayrı bir session ekledi); yalnızca ② kaldı
> (bu PR) + ③ opsiyonel.

## Durum özeti (güncel main'e karşı doğrulandı)

| # | Konu | Durum | Aksiyon |
|---|------|-------|---------|
| ① | Lodging public API routing (route:cache/404) | ✅ **main'de var** (`efc7ffc`) | **Hiçbir şey yapma.** ade5c67/206a01e cherry-pick ETME → çakışır |
| ② | Traefik per-domain `/api` → Laravel | ⚠️ **KRİTİK, main'de yok** | **BU PR** (`6ec60c2` cherry-pick → `75a6033`) |
| ③ | Tema-token (jenerik) daire-detay component | ⚙️ opsiyonel | İleride main'den otel kurulursa |

---

## ① ✅ TAMAM — Lodging routing fix zaten main'de
`efc7ffc` (bu session) main'in `LodgingModuleServiceProvider`'ını **koşulsuz boot** + public
tenancy zinciri (`UseSiteHostHeader`+`InitializeTenancyForPublicApi`+`MeterTenantUsage`) +
**`EnsureActiveTenantModule`** kapısına çevirdi. **Lodging + Tours ikisini** de kapsar.
- ⚠️ MD'nin eski önerisi `ade5c67 206a01e` (yalnızca lodging, `origin/feat/otelvatan-site`) artık
  **GEREKSİZ ve ÇAKIŞIR** — cherry-pick etme.

## ② ⚠️ BU PR — Traefik per-domain `/api` → Laravel
**Sorun:** `TraefikDynamicConfig` her tenant domaini için yalnızca frontend router'ı (priority 100)
üretiyordu; `/api` istisnası yoktu → tenant domaininde `/api` → Next.js gidiyordu (tarayıcı fetch'leri
HTML alıp boş dönüyordu). **Fix:** her domain için `Host(domain) && PathPrefix(/api|/admin|…)` →
Laravel, **priority 150** router.
- Commit: `6ec60c2` (`origin/feat/dagkent-site`) → bu PR'da `75a6033`. Tek dosya:
  `app/Services/TraefikDynamicConfig.php`.
- **Deploy sonrası (main deploy edilince) ZORUNLU:** app restart (opcache) + dinamik config yeniden üret:
  ```bash
  docker exec grafike_cms_app1 php artisan tinker --execute="app(App\Services\TraefikDynamicConfig::class)->regenerate();"
  ```
> **Neden kritik:** main deploy edilip `regenerate()` çalışırsa, DÜZELTİLMEMİŞ config **tüm
> domainlerin `/api`'sini** bozar. Bu fix main'e girmeden main'den regenerate ETME.

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
