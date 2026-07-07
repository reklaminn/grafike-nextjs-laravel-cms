# Boblanlı Yapı — Tenant Go-Live Runbook

**Branch:** `feat/boblanli-site` (feat/multi-tenant tabanından) · **Tenant:** `boblanliyapi` · **Domain:** boblanliyapi.com (IP 168.231.127.204)
**Tema:** `boblanli` (antrasit + kırmızı, Space Grotesk/Inter, keskin köşe) · **Modül:** yok (kurumsal çok-sayfa)

OtelVatan/Homeland deseni: self-contained chrome + alan-editli FieldChrome bölümler.
**Çok sayfa** — menü öğeleri ayrı sayfalar: `home` (zengin landing) · `hizmetler` (4 hizmet + süreç) ·
`neden-biz` (sayaçlar + avantajlar) · `calismalar` (galeri) · `iletisim` (bilgi + harita + form).
Alt sayfalar `bl-page-hero` başlık banner'ı ile başlar. Header nav gerçek sayfa linkleri (/hizmetler …).

## Seeder envanteri (`database/seeders/`)
| Dosya | DB | Ne yapar |
|---|---|---|
| `BoblanliThemeSeeder.php` | central | `boblanli` teması + tokens (kırmızı/antrasit, radius 0) |
| `BoblanliChromeSeeder.php` | central | header (:root + TÜM paylaşılan CSS + sticky nav + checkbox mobil menü) + footer (floating WhatsApp + reveal/counter/shrink JS) + `free-html` |
| `BoblanliFieldChromeSeeder.php` | central | `boblanli/fields/*.php` (7 bölüm-şablonu) |
| `BoblanliTenantSeeder.php` | TENANT | tek `home` sayfası (7 bölüm) + tenant'a özel `boblanli-teklif` formu + site ayarları |

**Frontend:** YENİ COMPONENT YOK. Bölümler `content-block` (basic-html-renderer) + iletişim formu
`type=form` (mevcut FormSection). → **frontend rebuild GEREKMEZ**, mevcut prod build render eder.

## Ön koşullar
1. `boblanliyapi` tenant'ı + domain `boblanliyapi.com` (admin Site Wizard).
2. `php artisan tenants:migrate --tenants=boblanliyapi` (core + forms tabloları).
3. Central `languages`'ta `tr`.
4. **Logo/favicon:** admin → Site Ayarları'ndan `boblanli-ico.png` (logo + favicon) yüklenir.
   Header `{{logo_url}}` ile ikonu gösterir (yüklenmezse sadece "BOBLANLI YAPI" wordmark).

## Deploy (VPS — image baked, composer'sız → require yöntemi)
```bash
cd /opt/graficms
git fetch origin && git checkout feat/boblanli-site && git reset --hard origin/feat/boblanli-site

# Seeder cp (Chrome statik metodları TenantSeeder'da kullanılır)
for f in BoblanliThemeSeeder BoblanliChromeSeeder BoblanliFieldChromeSeeder BoblanliTenantSeeder; do
  docker cp database/seeders/$f.php grafike_cms_app1:/var/www/html/database/seeders/
done
docker cp database/seeders/boblanli grafike_cms_app1:/var/www/html/database/seeders/

# Central + tenant seed (require — composer yok)
docker exec -i grafike_cms_app1 php artisan tinker <<'PHP'
foreach (['BoblanliThemeSeeder','BoblanliChromeSeeder','BoblanliFieldChromeSeeder','BoblanliTenantSeeder'] as $c) {
  require_once "/var/www/html/database/seeders/$c.php";
}
(new Database\Seeders\BoblanliThemeSeeder)->run();
(new Database\Seeders\BoblanliChromeSeeder)->run();
(new Database\Seeders\BoblanliFieldChromeSeeder)->run();
App\Models\Tenant::find('boblanliyapi')->run(function () {
  (new Database\Seeders\BoblanliTenantSeeder)->run();
  echo 'Page='.App\Models\Page::count().' Form='.\DB::table('forms')->where('slug','boblanli-teklif')->count().PHP_EOL;
});
PHP

docker restart grafike_cms_frontend
```

## Doğrulama
- `boblanliyapi.com` → Ana Sayfa: hero (koyu, kırmızı diagonal, tek H1) → güven şeridi → 4 hizmet kartı
  (hover kalkma + kırmızı şerit) → sayaçlar (viewport'a girince sayar) → 4 avantaj → 4 adım süreç →
  8'li galeri (hover zoom + kırmızı overlay) → iletişim (bilgi + harita) → teklif formu → footer + floating WhatsApp.
- Header anchor nav smooth-scroll; scroll>40px header küçülür; ≤880px hamburger menü.
- Form: Ad Soyad / Telefon / Hizmet (select) / Mesaj → gönderim admin → Formlar → boblanli-teklif'te görünür.
- Logo yüklendiyse header'da ikon + wordmark; favicon sekmede.

## Notlar
- Görseller placeholder (hero bg + galeri) — müşteri fotoğrafları admin'den alan-editiyle (`bg_css` / galeri `img`) girilir.
- Harita: bl-07 `map_embed` alanına gerçek Google Maps iframe yapıştırılır.
- İletişim formu iletişim bölümünün **altında** ayrı kart olarak render olur (dark bölüm + beyaz form kartı akışı).
