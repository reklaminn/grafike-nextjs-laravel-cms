# Boblanlı Yapı — Alan Şablonları (field templates)

Tek-sayfa kurumsal inşaat sitesi. Her dosya bir `SectionTemplate` (type=`content-block`,
render_mode=`html`) döndürür; `BoblanliFieldChromeSeeder` glob'lar. Bölümler paylaşılan chrome
CSS/JS'ini kullanır (`BoblanliChromeSeeder` header <style> + footer <script>):
`data-reveal` (scroll reveal), `data-count`+`data-suffix` (sayaç), `.svc-card`/`.svc-bar` (hizmet
kart hover), `.gal`/`.gal-img`/`.gal-ov` (galeri hover), `.btn-slide`/`.btn-ghost`, `.site-head.shrink`.

## Mustache
- `{{alan}}` escaped · `{{{alan}}}` ham (html tipi) · repeater `X` → `{{{X_html}}}` + `X.type='repeater'`+`item_template`.

## Tema
Antrasit `#1A1A1A` + kırmızı vurgu `#E63329` (var `--ac`/`--ac-d`/`--ink`), Space Grotesk (başlık) +
Inter (gövde), **keskin köşe** (radius 0). Renk/font = `BoblanliChromeSeeder` `:root`.

## Dosyalar (sıra = sayfa akışı)
| Dosya | Bölüm |
|---|---|
| bl-01-hero | Hero (#anasayfa, koyu, kırmızı diagonal, tek H1) |
| bl-02-guven | Güven şeridi (3 öğe) |
| bl-03-hizmetler | Hizmetler (#hizmetler, 4 kart) |
| bl-04-neden | Neden Biz (#neden-biz, 4 sayaç + 4 avantaj) |
| bl-05-surec | Çalışma Süreci (#surec, 4 adım timeline) |
| bl-06-galeri | Çalışmalar (#calismalar, 8'li asimetrik grid, repeater) |
| bl-07-iletisim | İletişim (#iletisim, bilgi + harita) |

**İletişim formu** ayrı bir `type=form` bloğudur (FieldChrome değil): tenant'a özel
`boblanli-teklif` formu (Ad Soyad / Telefon / Hizmet select / Mesaj), `BoblanliTenantSeeder`
tarafından kurulur ve iletişim bölümünün altına döşenir.
