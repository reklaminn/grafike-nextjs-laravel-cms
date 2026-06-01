# Estetik Dermal — Tenant Go-Live Runbook

Branch: `deploy/estetik-dermal-golive` · Tenant: `estetik_dermal` · DB: `tenant_estetik_dermal`

Bu paket, Estetik Dermal sitesini **CMS-native** (serbest-HTML `content-block` + ortak `header`/`footer` blokları) olarak tenant'a kurar. Tüm seeder'lar **idempotent** (`updateOrCreate`) — tekrar çalıştırılabilir, mevcut veriyi bozmaz.

## İçindekiler (database/seeders/)
| Dosya | Nereye yazar | Ne yapar |
|---|---|---|
| `EstetikDermalThemeSeeder.php` | **central** | 6 tema (ana + 5 marka), `tenant_id=estetik_dermal` |
| `EstetikDermalSectionTemplatesSeeder.php` | **central** | 12 katalog bloğu (mustache) |
| `EstetikDermalChromeSeeder.php` | **central** | `header` + `footer` (ortak chrome, base.css inline) + `content-block` (serbest HTML) |
| `EstetikDermalTenantSeeder.php` | **TENANT** | 12 sayfa (gövde = serbest HTML), header/footer menü, site ayarları |

## Ön koşullar
1. `estetik_dermal` tenant'ı mevcut olmalı (yoksa admin / Site Wizard ile oluştur, domain `estetikdermal.com` ekle).
2. Tenant DB migrate edilmiş olmalı: `php artisan tenants:migrate --tenants=estetik_dermal`
3. Central'da `languages` tablosunda `tr` dili olmalı (seeder `code='tr'` arar, yoksa ilk dili kullanır).

## Deploy adımları (VPS)
```bash
# 1) Branch'i çek + deploy
cd /opt/graficms   # proje kökü (kendi yolunuza göre)
git fetch && git checkout deploy/estetik-dermal-golive && git pull
/opt/graficms/deploy-vps.sh      # composer install + migrate + frontend build (her zamanki akış)

# 2) CENTRAL seed (tema + bloklar + chrome) — central DB
php artisan db:seed --class=EstetikDermalThemeSeeder --force
php artisan db:seed --class=EstetikDermalSectionTemplatesSeeder --force
php artisan db:seed --class=EstetikDermalChromeSeeder --force

# 3) TENANT seed (sayfalar + menü + ayarlar) — TENANT context'inde!
#    (projenizin tenant-seed komutunu kullanın; stancl tipik kullanımları:)
php artisan tenants:run "db:seed --class=EstetikDermalTenantSeeder --force" --tenants=estetik_dermal
#    Alternatif: tinker ile
#      $t = App\Models\Tenant::find('estetik_dermal');
#      $t->run(fn() => Artisan::call('db:seed', ['--class'=>'EstetikDermalTenantSeeder','--force'=>true]));
```

## Görseller & logo (önemli)
Sayfa gövdeleri görselleri **`assets/img/...jpg`** göreli yoluyla çağırır (image-ready: dosya yoksa krem fallback, bozuk ikon yok).
1. **Logo:** `dermallogo.png`'yi frontend'in servis ettiği yere koy (örn. `apps/frontend/public/assets/img/dermallogo.png`) **veya** medyaya yükle; sonra admin'de **Ayarlar → `site.logo`** değerini bu URL yap (header `{{logo_url}}` kullanır).
2. **Ürün/marka görselleri:** `apps/frontend/public/assets/img/` altına yorumlardaki adlarla koy (`hero-rrs.jpg`, `product-<slug>.jpg`, `brand-panel-<slug>.jpg`, `{marka}-hero.jpg`...). Geldikçe otomatik görünür.

## Doğrulama (staging/preview'da ÖNCE)
- [ ] Tenant domain'inde site açılıyor, header/footer (Estetik Dermal + yeşil WhatsApp) render ediliyor.
  - Header/footer **`theme.header_variant=estetikdermal-header`** / **`-footer`** ayarlarıyla çözülür — gelmiyorsa frontend'in header/footer çözümleme mantığını + bu ayar anahtarlarını kontrol et.
- [ ] 12 sayfa geziliyor (home, hakkimizda, urunler, urun-detay, markalar, etkinlikler, iletisim, marka/*), slider çalışıyor (custom_js), kategori çipleri, premium görünüm.
- [ ] Logo görünüyor (`site.logo` set edildi mi).
- [ ] Menü linkleri doğru.
- [ ] Ayar anahtarları (`site.logo`, `contact.*`, `social.*`) frontend'in beklediğiyle uyuşuyor — uyuşmazsa `EstetikDermalTenantSeeder` içindeki anahtarları frontend'e göre düzelt.

## TEMA 2 (Clinical Luxury) — alternatif tasarım
İkinci bir tema (`estetikdermal-v2`, "Tema 2 — Clinical Luxury": Fraunces serif + turuncu, premium) ayrı seeder'larla eklenir. Her iki tema CMS'te **yan yana** kayıtlı olur (admin görür/seçer).
```bash
# central — Tema 2 + chrome
php artisan db:seed --class=EstetikDermalV2ThemeSeeder --force
php artisan db:seed --class=EstetikDermalV2ChromeSeeder --force
# tenant — Tema 2 sayfaları (TENANT context)
php artisan tenants:run "db:seed --class=EstetikDermalV2TenantSeeder --force" --tenants=estetik_dermal
```
> **DİKKAT:** V2TenantSeeder, Tema 1 ile **AYNI slug'ları** kullanır → çalıştırınca canlı sayfaları Tema 2 ile **değiştirir**. İki tasarımı aynı anda canlı tutmak için Tema 2'yi **ayrı bir staging tenant'ta** seed edin (örn. `estetik_dermal_v2`), karşılaştırıp karar verin.
> **Animasyon notu:** v2 scroll-reveal/sayaç animasyonları JS ister; CMS bloklarındaki `<script>` Next.js'te çalışmayabilir. Bu yüzden v2 chrome'una `.reveal{opacity:1!important}` override kondu → içerik JS olmadan da görünür (animasyon olmasa bile). İstenirse v2.js sayfa `custom_js`'ine eklenip animasyonlar aktive edilebilir.

## Notlar
- Seeder'lar idempotent: tekrar çalıştırmak güvenli.
- Marka sayfaları ortak ED header/footer kullanır; marka kimliği gövde + `custom_css` (`:root` palet override) ile gelir (header/footer'ı da o sayfada marka rengine boyar).
- Lokal sqlite kopyasında uçtan uca test edildi: 12 sayfa + chrome + menü + ayar hatasız.
- Geri alma: sayfaları silmek için tenant DB'de ilgili slug'lı `pages` kayıtlarını kaldır (idempotent olduğu için yeniden seed eski hale getirir).
