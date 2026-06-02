# Estetik Dermal — Tenant Go-Live Runbook (v2, baştan)

**Branch:** `deploy/estetik-dermal-golive` · **Tenant:** `estetik_dermal` · **Tenant DB:** `tenant_estetik_dermal`
**Canlı sonuç:** kök URL = **Tema 2** (Clinical Luxury), `/klasik/...` = **Tema 1** (turuncu modern). İkisi yan yana.

---

## 0. Mimari — önce bunu oku (yanlış anlaşılmayı önler)

Bu site **self-contained content-block** mimarisiyle kuruluyor. Sebebi: frontend (`apps/frontend/app/[locale]/layout.tsx`) **header/footer bloklarını `header_variant`'tan RENDER ETMİYOR** ve CSS'i yalnızca `theme.assets.css` `<link>`'lerinden yüklüyor. Bizim CSS'imiz tema asset'inde değil → o yüzden **her sayfa kendi içinde tam**:

```
content.html = <link fonts> + <style>tokens+base/v2 css</style> + <header> + <main gövde> + <footer>
```

Bu HTML, content-block şablonunun `html_template = {{{html}}}` alanı üzerinden **ham** basılır (`basic-html-renderer.ts` → `section.html_override || section.html_template`).

### Admin panelinde göreceğin "tuhaflıklar" NORMAL — hata değil:
| Gördüğün | Açıklama |
|---|---|
| Sayfada **tek blok** (content-block), HEADER/FOOTER bölgeleri **boş** | Kasıtlı. Header/footer o tek bloğun HTML'ine gömülü; ayrı bölge render edilmiyor. |
| "İçerik / Üretilen HTML Kodu" → `<p><br></p>...` | Admin'in genel zengin-metin editörü `content.html` alanını okumaz. **Yok say.** |
| Gerçek HTML yalnızca **"Ham JSON"**'da görünür | Doğru yer orası. `content.html` = sayfanın tamamı. |
| **Render Önizleme** boş | Admin önizlemesi `{{{html}}}`'i simüle etmez. **Gerçeği görmek için tenant URL'sini aç**, admin önizlemesini değil. |

> Özet: Tasarımı doğrulamak için **her zaman canlı tenant URL'sini** ziyaret et (`/tr`, `/tr/klasik`), admin'in iç önizlemesine bakma.

---

## 1. Seeder envanteri (`database/seeders/`)

| Dosya | DB | Ne yapar |
|---|---|---|
| `EstetikDermalThemeSeeder.php` | **central** | Tema 1 + 5 marka teması (`estetikdermal`), `tenant_id=estetik_dermal` |
| `EstetikDermalSectionTemplatesSeeder.php` | **central** | Tema 1 katalog blokları (mustache) — opsiyonel ama zararsız |
| `EstetikDermalChromeSeeder.php` | **central** | Tema 1 `content-block` (`free-html`, `{{{html}}}`) + header/footer şablonları |
| `EstetikDermalV2ThemeSeeder.php` | **central** | Tema 2 teması (`estetikdermal-v2`) |
| `EstetikDermalV2ChromeSeeder.php` | **central** | Tema 2 `content-block` (`v2-free-html`, `{{{html}}}`) + header/footer şablonları |
| `EstetikDermalV2TenantSeeder.php` | **TENANT** | Tema 2 sayfaları — **kök slug** (`home`, `hakkimizda`, `marka/skintech`...) |
| `EstetikDermalKlasikTenantSeeder.php` | **TENANT** | Tema 1 sayfaları — **`klasik/` önekli** (`klasik`, `klasik/hakkimizda`...) + site ayarları |

İki tenant seeder **farklı slug** kullandığı için çakışmaz; ikisi de aynı tenant'ta canlı kalır.
Tüm seeder'lar **idempotent** (`updateOrCreate`) → tekrar çalıştırmak güvenli.

---

## 2. Ön koşullar (bir kez)

1. `estetik_dermal` tenant'ı mevcut (yoksa admin → Site Wizard, domain ekle).
2. Tenant DB migrate edilmiş: `docker exec grafike_cms_app1 php artisan tenants:migrate --tenants=estetik_dermal`
3. Central'da `languages` tablosunda `tr` var (yoksa seeder ilk dili kullanır).

> **Container adları:** app replikaları `grafike_cms_app1/2/3`, frontend `grafike_cms_frontend`.
> **Uygulama image'a "baked"** (volume-mount değil) → seeder dosyalarını çalıştırmadan önce `docker cp` ile container'a kopyalamak **şart**. `db:seed` derlenmiş asset gerektirmez, kopyala-çalıştır yeterli (rebuild gerekmez).

---

## 3. Deploy adımları (VPS) — kopyala-yapıştır

```bash
# ── 0) Host'ta güncel dosyalar
cd /opt/graficms
git fetch && git checkout deploy/estetik-dermal-golive && git pull

# ── 1) Tüm seeder dosyalarını app container'a kopyala (image baked → şart)
for f in EstetikDermalThemeSeeder EstetikDermalSectionTemplatesSeeder EstetikDermalChromeSeeder \
         EstetikDermalV2ThemeSeeder EstetikDermalV2ChromeSeeder \
         EstetikDermalV2FieldChromeSeeder EstetikDermalKlasikFieldChromeSeeder \
         EstetikDermalV2TenantSeeder EstetikDermalKlasikTenantSeeder; do
  docker cp database/seeders/$f.php grafike_cms_app1:/var/www/html/database/seeders/
done

# ── 2) CENTRAL seed (tema + chrome + ALAN ŞABLONLARI) — central DB
docker exec grafike_cms_app1 php artisan db:seed --class=EstetikDermalThemeSeeder --force
docker exec grafike_cms_app1 php artisan db:seed --class=EstetikDermalSectionTemplatesSeeder --force
docker exec grafike_cms_app1 php artisan db:seed --class=EstetikDermalChromeSeeder --force
docker exec grafike_cms_app1 php artisan db:seed --class=EstetikDermalV2ThemeSeeder --force
docker exec grafike_cms_app1 php artisan db:seed --class=EstetikDermalV2ChromeSeeder --force
# Alan-tabanlı bölüm şablonları (İçerik sekmesinde düzenleme + repeater) — TENANT'tan ÖNCE şart
docker exec grafike_cms_app1 php artisan db:seed --class=EstetikDermalV2FieldChromeSeeder --force
docker exec grafike_cms_app1 php artisan db:seed --class=EstetikDermalKlasikFieldChromeSeeder --force

# ── 3) TENANT seed (tenant context) — iki tema tek komutta
docker exec grafike_cms_app1 php artisan tinker --execute="App\Models\Tenant::find('estetik_dermal')->run(function(){ Artisan::call('db:seed',['--class'=>'EstetikDermalV2TenantSeeder','--force'=>true]); Artisan::call('db:seed',['--class'=>'EstetikDermalKlasikTenantSeeder','--force'=>true]); echo PHP_EOL.'TENANT SEED OK'.PHP_EOL; });"

# ── 4) Frontend cache temizle
docker restart grafike_cms_frontend
```

> Komutları **3 app replikasının hepsine** uygulamana gerek yok — hepsi **aynı DB'ye** yazar; tek replikadan (`app1`) seed yeterli. `docker cp`'yi de yalnız `app1`'e yaptık çünkü dosyalar DB'ye değil, sadece o an çalıştıran process'e lazım.
>
> **Yeni seeder sınıfı bulunamazsa** ("Class not found"): `docker exec grafike_cms_app1 composer dump-autoload -o` çalıştırıp 2-3. adımı tekrarla. (Laravel 12 `Database\Seeders` PSR-4 olduğu için genelde gerekmez.)

---

## 4. Doğrulama (canlı URL'de — admin önizlemesinde DEĞİL)

Tenant kök adresi (örn. `https://estetikdermal.com/tr` ya da `https://cms.grafcore.com/tr?tenant=estetik_dermal`). Her kontrolde **Ctrl+Shift+R** (hard refresh):

- [ ] **Kök = Tema 2:** `/tr` → Clinical Luxury (Fraunces serif başlıklar, turuncu aksan, yeşil WhatsApp ikonu, header+footer var, layout düzgün).
- [ ] **Tema 2 sayfaları:** `/tr/hakkimizda`, `/tr/urunler`, `/tr/markalar`, `/tr/etkinlikler`, `/tr/iletisim`, `/tr/marka-{skintech,seffiline,aespio,woorhi,mi-medical,neogenesis}` — her marka kendi paletinde.
- [ ] **Klasik = Tema 1:** `/tr/klasik` → turuncu modern anasayfa (header+footer, hero, stat'lar).
- [ ] **Tema 1 sayfaları:** `/tr/klasik-hakkimizda`, `/tr/klasik-urunler`, `/tr/klasik-markalar`, `/tr/klasik-marka-skintech` ...

> **ÖNEMLİ — DÜZ SLUG:** Frontend route'u (`pages/{slug}`) slug'ta **slash kabul etmez** → `marka/seffiline` gibi nested slug **404** verir. Bu yüzden tüm marka/alt sayfalar **düz** slug kullanır: `marka-seffiline`, `klasik-marka-seffiline`. Seeder'lar çalışırken eski nested kayıtları (`marka/%`, `klasik/%`) otomatik siler.
- [ ] WhatsApp ikonu **küçük** (dev yeşil kare değil), header/footer **stilli** geliyor.
- [ ] Konsolda kırık görsel/404 sadece henüz yüklenmemiş `assets/img/*.jpg` olmalı (krem fallback'li, kırık ikon yok).

---

## 5. Görseller & logo (sonradan eklenir)

Sayfa gövdeleri görselleri **`/assets/img/...`** mutlak yoluyla çağırır → bu dosyaları frontend'in servis ettiği `apps/frontend/public/assets/img/` altına koy:

1. **Logo:** `dermallogo.png` (yoksa `logo-full.svg` fallback'i devrede). `apps/frontend/public/assets/img/dermallogo.png`.
2. **Ürün/marka görselleri:** `hero-*.jpg`, `product-<slug>.jpg`, `brand-panel-<slug>.jpg`, `{marka}-hero.jpg` — geldikçe otomatik görünür.

> Image-ready desen: dosya yokken krem zemin fallback gösterilir, bozuk-ikon çıkmaz.

---

## 6. Bakım / geri alma

- **Yeniden seed:** herhangi bir seeder'ı tekrar çalıştır — idempotent, mevcut kaydı günceller.
- **Tasarımı güncelleme:** statik kaynağı (`/Volumes/Dev/estetikdermal/site/`) düzenle → generator'ı çalıştır:
  - Tema 2: `python3 /tmp/gen_cms_v2.py`
  - Tema 1 (klasik): `python3 /tmp/gen_cms_klasik.py`
  - Üretilen dosyayı `seeder/`'dan repoya kopyala, commit/push, VPS'te 3. adımı tekrarla.
- **Bir temayı kaldırma:** ilgili tenant seeder'ın slug'larına sahip `pages` kayıtlarını tenant DB'den sil. Örn. yalnız Tema 1'i kaldır: `slug LIKE 'klasik%'` kayıtlarını sil.
- **Kök tasarımı takas etme (Tema 1 ↔ Tema 2):** slug stratejisini ters çevirmek gerekir (generator'larda prefix'i swap'le, yeniden üret). Sor, yapayım.

---

## 7. Bilinen sınırlar

- CMS bloklarındaki `<script>` Next.js'te çalışmaz → v2/v1 scroll-reveal & slider **oto-animasyonları pasif**. İçerik JS'siz de görünür (`.reveal{opacity:1!important}` override + slider 1. slaytta sabit). İstenirse animasyon, sayfa `custom_js`'ine `v2.js`/slider JS eklenerek aktive edilebilir.
- Admin panelinden bu sayfaları **blok-blok düzenlemek pratik değil** (tek opak HTML bloğu). Düzenleme = statik kaynağı güncelle + yeniden seed (bkz. §6).
