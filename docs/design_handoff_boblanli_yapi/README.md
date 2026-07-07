# Handoff: Boblanlı Yapı — Kurumsal Tek Sayfa Web Sitesi

## Overview
Kuşadası / Aydın'da faaliyet gösteren **Boblanlı Yapı** (inşaat, dekorasyon, revizyon, elektrik) firması için modern, tek sayfa (one-page) kurumsal tanıtım sitesi. Amaç: güven veren, premium, yerel SEO'ya optimize bir tanıtım + iletişim/lead toplama sayfası.

Görsel yön: **"Sağlam & Keskin"** — güçlü kontrast (antrasit/beyaz), kırmızı vurgu ve logodaki eğik çatı çizgisinden türeyen **kırmızı diagonal aksan** motifi.

## About the Design Files
Bu pakette bulunan `Boblanli Yapi.dc.html` dosyası **HTML ile hazırlanmış bir tasarım referansıdır** — nihai görünümü ve davranışı gösteren bir prototiptir, doğrudan production'a kopyalanacak kod değildir. Görev: bu tasarımı **hedef CMS/kod tabanının kendi ortamında yeniden oluşturmaktır** (WordPress tema, Next.js/React, Laravel Blade, Vue, vb.) — o ortamın yerleşik component, template ve stil kalıplarını kullanarak. Eğer henüz bir ortam yoksa, proje için en uygun framework seçilip tasarım orada uygulanmalıdır.

> Not: Kaynak dosya bir "Design Component" (`.dc.html`) formatındadır ve bir runtime (`support.js`) ile render edilir. Bu runtime'ı taşımayın; markup + stiller + davranışları hedef ortamın diline çevirin. Aşağıdaki dokümantasyon bunun için yeterlidir.

## Fidelity
**High-fidelity (hifi).** Nihai renkler, tipografi, boşluklar, ikonlar ve etkileşimler tanımlıdır. Geliştirici UI'ı piksel düzeyinde, kod tabanının mevcut kütüphaneleriyle birebir yeniden oluşturmalıdır. Yalnızca **fotoğraf görselleri placeholder**'dır (aşağıda "Assets").

---

## Sayfa Yapısı (Bölüm Akışı, yukarıdan aşağıya)
1. Sabit (sticky) Header — kaydırınca küçülür
2. Hero (full-bleed, koyu, overlay + kırmızı diagonal)
3. Güven Şeridi (hero altında ince bar)
4. Hizmetler (4 kart)
5. Neden Biz (4 sayaç + 4 avantaj kartı)
6. Çalışma Süreci (4 adım, yatay timeline)
7. Çalışmalarımız / Galeri (asimetrik grid, 8 görsel)
8. İletişim (koyu zemin, bilgiler + form + harita)
9. Footer
10. Sabit (floating) WhatsApp butonu (sağ alt, pulse animasyonlu)

Tek sayfa; header menü linkleri bölüm id'lerine smooth-scroll yapar: `#anasayfa`, `#hizmetler`, `#neden-biz`, `#calismalar`, `#iletisim` (ayrıca `#surec`).

---

## Screens / Views (Bölüm bölüm detay)

### 1. Header (sticky)
- **Layout:** `position: sticky; top:0; z-index:80`. İçerik `max-width:1220px`, ortalı, `padding:15px 26px`, `display:flex; justify-content:space-between; align-items:center`. Yarı saydam beyaz `rgba(255,255,255,.94)` + `backdrop-filter: blur(10px)`, alt kenarlık `1px solid #ededed`.
- **Sol — Logo:** `boblanli-ico.png` (48×48, `object-fit:contain`) + yanında wordmark lockup: "BOBLANLI **YAPI**" (YAPI kırmızı) `Space Grotesk 700, 20px, letter-spacing:0.04em`; altında "İNŞAAT | CONSTRUCTING" `9.5px, 600, letter-spacing:0.26em, #9a9a9a`.
- **Orta/Sağ — Nav (desktop):** linkler `Inter 600, 14.5px, #1A1A1A`, gap 30px. Son öğe kırmızı **WhatsApp** butonu (chat çizgi-ikonu + metin), `background:var(--ac)`, `padding:11px 19px`, hover'da koyu kırmızı dolgu soldan kayar (`.btn-slide`).
- **Mobil (≤880px):** nav gizlenir, sağda antrasit hamburger buton (44×44, `☰`). Tıklanınca dikey açılır panel (`.mobile-nav.open`).
- **Shrink davranışı:** `window.scrollY > 40` iken header'a `.shrink` sınıfı eklenir → iç padding `9px`'e düşer, gölge `0 6px 24px rgba(0,0,0,.10)` kazanır. Geçiş `.3s ease`.

### 2. Hero
- **Layout:** `position:relative; background:#0d0d0d; color:#fff`. İçerik `max-width:1220px`, `min-height: clamp(560px,88vh,860px)`, dikey ortalı flex, `padding: clamp(90px,15vh,150px) 26px clamp(70px,11vh,120px)`.
- **Katmanlar (arkadan öne):**
  1. Arka plan görseli (placeholder: 45° çizgili desen) — *gerçekte koyu tonlu şantiye/bina fotoğrafı gelecek, sol taraf koyu olmalı*.
  2. Gradient overlay: `linear-gradient(90deg,#0b0b0bF2 0%,#0b0b0bcc 42%,#0b0b0b55 100%)` (soldan koyu → sağa şeffaf).
  3. Kırmızı diagonal slash: sağ tarafta 2 adet ince çubuk, `width:3px; height:130%; background:var(--ac); transform:rotate(20deg)`, opacity .9 ve .35.
- **İçerik (alttan-yukarı stagger reveal ile):**
  - Eyebrow rozet: kenarlıklı, `▪ KUŞADASI · AYDIN BÖLGESİ`, `12.5px 600 uppercase, letter-spacing:0.08em`.
  - **H1 (tek H1):** "Kuşadası'nda **İnşaat, Tadilat** ve **Elektrik** Hizmetleri" (kalın kelimeler `var(--ac)`). `Space Grotesk 700, clamp(2.4rem,6.2vw,5rem), line-height:0.98, uppercase, letter-spacing:-0.01em, max-width:16ch`.
  - Alt başlık (p): "Boblanlı Yapı; Kuşadası ve Aydın genelinde inşaat, iç dekorasyon, kapsamlı revizyon ve elektrik arıza-onarım hizmetlerini tek çatı altında sunar. Güvenilir işçilik, zamanında teslim, uygun fiyat." — `clamp(1.02rem,1.7vw,1.25rem), line-height:1.6, #cfcfcf, max-width:560px`.
  - CTA'lar (flex, wrap, gap:14px):
    - Birincil: `📞(çizgi-ikon) Ücretsiz Keşif İçin Arayın` → `href="tel:+905326576271"`, `background:var(--ac)`, `.btn-slide` hover.
    - İkincil: "Hizmetlerimizi İnceleyin" → `href="#hizmetler"`, şeffaf, `border:2px solid rgba(255,255,255,.3)`, hover'da beyaz kenarlık + hafif dolgu.

### 3. Güven Şeridi
- Hero'nun altında, `border-top:1px solid rgba(255,255,255,.1); background:rgba(0,0,0,.35)`. Ortalı flex, gap `14px 44px`, `15px 600`.
- 3 öğe (kırmızı çizgi-ikon + metin), aralarında kırmızı `/` ayraç: **Hızlı Müdahale** (şimşek), **Garantili İşçilik** (onay/check), **Kuşadası & Çevresi** (konum pin).

### 4. Hizmetler (`#hizmetler`, beyaz zemin)
- **Section padding:** `clamp(64px,8vw,110px) 26px`, `max-width:1220px`.
- **Başlık bloğu (ortalı):** eyebrow "— HİZMETLERİMİZ —" (kırmızı, çizgilerle), **H2** "Sunduğumuz Hizmetler" (`clamp(2rem,4vw,3rem), 700, uppercase`), alt metin "Tek elden, uçtan uca çözüm." (`1.15rem, #5a5a5a`).
- **Grid:** `repeat(auto-fit, minmax(255px,1fr))`, gap 24px → 4 kart.
- **Kart (`.svc-card`):** beyaz, `border:1px solid #e9e9e9`, `padding:34px 28px 32px`, üstte kırmızı şerit (`.svc-bar`, `height:4px`). İçerik: 56×56 kırmızı çizgi-ikon, **H3** (`1.4rem, 700`), açıklama (`0.98rem, line-height:1.65, #565656`).
  - **Hover:** kart `translateY(-8px)`, gölge `0 22px 44px rgba(0,0,0,.13)`, kırmızı şerit `height:8px`'e büyür. Kartlar `transition-delay` ile kademeli reveal (.06/.12/.18s).
- **Kartların içeriği (H3 + metin):**
  1. **Kuşadası Elektrik Arıza ve Onarım** — "Kuşadası ve Aydın çevresinde ev, ofis ve işyerlerinde elektrik arıza tespiti ve onarımı yapıyoruz. Sigorta atması, kaçak akım, pano arızası, priz ve aydınlatma sorunlarına hızlı müdahale; tesisat yenileme ve pano montajında garantili işçilik." (ikon: şimşek)
  2. **Toptan Elektrik Malzemesi Satışı** — "Kaliteli elektrik malzemelerini toptan ve perakende uygun fiyatlarla temin ediyoruz. Kablo, kablo makarası, pano, sigorta, priz-anahtar, LED ve aydınlatma ürünlerinde geniş stok. Müteahhit ve işletmeler için Aydın bölgesinde hızlı tedarik." (ikon: kutu/paket)
  3. **Kuşadası Dekorasyon ve Tadilat** — "Daire, villa, ofis ve işyerleriniz için komple tadilat, iç dekorasyon ve kapsamlı revizyon. Boya-badana, alçıpan, zemin döşeme, mutfak ve banyo yenileme, kartonpiyer tek elden. Anahtar teslim daire tadilatında planlı süreç ve şeffaf fiyatlandırma." (ikon: boya rulosu)
  4. **Kuşadası ve Aydın İnşaat Hizmetleri** — "Projelendirmeden anahtar teslime kadar profesyonel inşaat hizmeti. Konut, villa ve ticari yapı projelerinde sağlam mühendislik, kaliteli malzeme ve zamanında teslim. Kuşadası ve Aydın genelinde kaba-ince yapı ve tadilat-güçlendirme işlerinde deneyimli ekip." (ikon: bina)

### 5. Neden Biz (`#neden-biz`, açık gri `#F4F4F4`)
- Eyebrow "— NEDEN BOBLANLI YAPI? —", **H2** "Neden Kuşadası'nda Boblanlı Yapı?".
- **Sayaç bandı:** `repeat(auto-fit, minmax(150px,1fr))`, `gap:1px; background:#e0e0e0` (hairline ayraçlar). Her hücre koyu `#111`, ortalı, `padding:32px 20px`. Rakam `Space Grotesk 700, clamp(2.2rem,4vw,3rem), color:var(--ac)`, altında etiket `14px 600 #cfcfcf`.
  - **Sayaç değerleri:** `150+` Tamamlanan Proje · `10+` Yıl Tecrübe · `100%` Müşteri Memnuniyeti · `4` Ana Hizmet Alanı.
  - **Animasyon:** `data-count="<hedef>"` olan `<span>`'ler viewport'a girince (IntersectionObserver, threshold .5) 0'dan hedefe **ease-out-cubic** ile ~1400ms sayar (bkz. State/Behavior).
- **Avantaj kartları:** `repeat(auto-fit, minmax(230px,1fr))`, gap 24px. Beyaz, `padding:32px 28px`, sol kenar `4px solid var(--ac)`. 44×44 kırmızı çizgi-ikon + **H3** (`1.3rem 700`) + açıklama (`0.97rem, #565656`).
  1. **Deneyimli Ekip** (ekip ikonu) — "Aydın bölgesinde yılların saha tecrübesine sahip uzman kadro."
  2. **Zamanında Teslim** (saat) — "Söz verdiğimiz tarihte, eksiksiz ve temiz teslim."
  3. **Şeffaf Fiyatlandırma** (etiket) — "Sürpriz maliyet yok; net keşif, açık teklif."
  4. **Garantili İşçilik** (kalkan+onay) — "İnşaat, tadilat ve elektrik işlerinde garanti veriyoruz."

### 6. Çalışma Süreci (`#surec`, koyu `#111`)
- Arka planda soluk kırmızı diagonal çizgi (`rotate(20deg), opacity:.25`). Eyebrow "— SÜRECİMİZ —", **H2** "Nasıl Çalışıyoruz?" (beyaz).
- **Timeline:** `repeat(auto-fit, minmax(210px,1fr))`, gap 26px. Arkada yatay ilerleme çizgisi: `position:absolute; top:22px; left:8%; right:8%; height:2px; background:linear-gradient(90deg,var(--ac),rgba(230,51,41,.15))`.
- **Her adım:** 44×44 kırmızı yuvarlak rozet (numara `Space Grotesk 700, 18px, border:4px solid #111`), **H3** (`1.25rem 700`, sol tarafında 20px kırmızı çizgi-ikon) + açıklama (`0.95rem, #a8a8a8`).
  1. **01 Keşif & Görüşme** (büyüteç) — "Yerinde keşif yapar, ihtiyacınızı dinleriz."
  2. **02 Teklif & Planlama** (belge) — "Net teklif ve iş planını birlikte belirleriz."
  3. **03 Uygulama** (çekiç) — "Titiz işçilikle, plana sadık kalarak uygularız."
  4. **04 Teslim & Destek** (anahtar) — "Eksiksiz teslim eder, sonrasında destek veririz."

### 7. Çalışmalarımız / Galeri (`#calismalar`, beyaz)
- Eyebrow "— REFERANSLAR —", **H2** "Tamamlanan İşlerimiz", alt metin "Kuşadası ve çevresinde hayata geçirdiğimiz projelerden bir seçki."
- **Asimetrik grid:** `grid-template-columns:repeat(4,1fr); grid-auto-rows:190px; gap:14px`. 8 `<figure class="gal">`. İlk figür `span 2 / span 2` (büyük), iki figür `span 2` (geniş), kalanlar 1×1 — tekdüze kareden kaçınılmış düzen.
  - **Mobil (≤760px):** 2 sütuna düşer (`grid-auto-rows:150px`), `.big` öğeler `span 2` (tam genişlik) kalır, diğerleri 1×1.
- **Her kare:** placeholder `<div class="gal-img">` (gerçekte fotoğraf `<img>`), üstünde `<figcaption class="gal-ov">` iş adı.
  - **Hover:** görsel `scale(1.08)` (`.5s`), üzerine kırmızı overlay belirir `linear-gradient(0deg, rgba(230,51,41,.82), rgba(230,51,41,.2))` (opacity 0→1, `.35s`), iş adı `Space Grotesk 700` beyaz, sol-alt hizalı.
  - **İş adları:** İnşaat Projesi · İç Dekorasyon · Elektrik İşleri · Banyo Revizyonu · Daire Tadilatı · Mutfak Yenileme · İş Yeri Tadilatı · Aydınlatma.

### 8. İletişim (`#iletisim`, koyu `#111`)
- Arka planda soluk kırmızı diagonal. Eyebrow "— İLETİŞİM —", **H2** "Kuşadası İnşaat ve Tadilat İçin Bize Ulaşın", alt metin "Kuşadası, Aydın'da inşaat, dekorasyon, revizyon veya elektrik hizmeti mi arıyorsunuz? Ücretsiz keşif için bir telefon uzağınızdayız. Türkmen Mahallesi'ndeki ofisimize uğrayın ya da WhatsApp'tan hemen yazın."
- **Grid:** `repeat(auto-fit, minmax(300px,1fr))`, gap 44px, align-items:start.
- **Sol — İletişim bilgileri** (50×50 kutu ikon + etiket + değer):
  - Telefon: `tel:+905326576271` — kırmızı kutu, telefon ikonu.
  - WhatsApp: `https://wa.me/905326576271` — kırmızı kutu, chat ikonu.
  - Adres: "Türkmen Mahallesi, Bahçearası Sok. No:2 İç Kapı:3, Kuşadası / Aydın" — koyu kutu, konum ikonu.
  - Sosyal: `📷 @tahsinboblanli` (`https://instagram.com/tahsinboblanli`), `🌐 boblanliyapi.com` — çizgi-ikonlar.
  - **Harita:** `aspect-ratio:16/9; border-radius:14px` yuvarlatılmış çerçeveli placeholder → gerçekte Google Maps embed (Türkmen Mah. konumu).
- **Sağ — İletişim formu** (`background:#181818; border:1px solid #2a2a2a; border-radius:14px; padding:clamp(24px,3vw,38px)`):
  - Alanlar (`.field`): Ad Soyad (text), Telefon (tel), **Hizmet Seçimi** (select: Elektrik Arıza ve Onarım / Toptan Elektrik & Malzeme Satışı / Dekorasyon ve Revizyon / İnşaat / Diğer), Mesajınız (textarea, 4 satır). Her biri `background:#111; border:1px solid #333; color:#fff; border-radius:8px; padding:14px 15px`. **Focus:** `border-color:var(--ac); background:rgba(230,51,41,.04)`.
  - Buton: "Teklif İsteyin" (kırmızı, `.btn-slide`). Submit sonrası label "Gönderildi ✓" olur ve yeşil onay mesajı görünür (bkz. State).

### 9. Footer (`#1A1A1A`)
- 4 sütun grid `repeat(auto-fit, minmax(220px,1fr))`, gap 42px, alt kenarlık `1px solid #2c2c2c`.
  - **Marka:** beyaz kutu içinde `boblanli-ico.png` + wordmark; slogan "**Güvenle inşa eder, özenle tamamlarız.**"
  - **Hizmetler:** 4 link (`#hizmetler`).
  - **İletişim:** telefon, WhatsApp, adres.
  - **Takip Edin:** Instagram, boblanliyapi.com.
- Alt satır: "© 2026 Boblanlı Yapı — Tüm hakları saklıdır." (`13px, #7a7a7a`, ortalı).

### 10. Floating WhatsApp
- `position:fixed; right:22px; bottom:22px; z-index:90`, 58×58 daire, `background:var(--ac)`, chat çizgi-ikonu (beyaz). **`wa-pulse`** keyframe ile sürekli kırmızı halka nabzı; hover'da `scale(1.06)`. `href="https://wa.me/905326576271"`.

---

## Interactions & Behavior
- **Smooth scroll:** `html { scroll-behavior: smooth }`, header linkleri bölüm id'lerine gider.
- **Scroll reveal:** `[data-reveal]` öğeleri başlangıçta `opacity:0; translateY(30px)`. IntersectionObserver (threshold .12, `rootMargin:0px 0px -8% 0px`) görünür olunca `.in` ekler → `opacity:1; transform:none`, geçiş `.7s cubic-bezier(.22,.61,.36,1)`. Kartlarda `transition-delay` ile kademeli (stagger). **Güvenlik:** IO yoksa veya 2500ms sonra tümü açılır (içerik asla gizli kalmaz).
- **Sayaç animasyonu:** `[data-count]` viewport'a girince ease-out-cubic ile ~1400ms sayar; `requestAnimationFrame`. Bir kez çalışır (unobserve).
- **Header shrink:** scroll > 40px → `.shrink`.
- **Buton hover (`.btn-slide`):** koyu kırmızı katman soldan içeri kayar (`translateX(-101%)→0`, `.32s`). İkincil hero buton (`.btn-ghost` deseni): antrasit dolgu alttan yukarı.
- **Kart hover:** yukarı kalkma + gölge + kırmızı şerit büyümesi. **Galeri hover:** zoom + kırmızı overlay + iş adı.
- **Form submit:** `preventDefault()`, `sent=true` → buton "Gönderildi ✓", yeşil onay mesajı. *Gerçek gönderim backend/CMS'e bağlanmalı (aşağıya bkz.).*
- **Mobil menü:** hamburger toggle ile açılır/kapanır; link tıklanınca kapanır.
- **Responsive:** tüm grid'ler `auto-fit/minmax` ile akışkan; galeri ≤760px 2 sütun; nav ≤880px hamburger. Tipografi `clamp()` ile ölçekli.

## State Management
- `menuOpen: boolean` — mobil menü açık/kapalı.
- `sent: boolean` — form gönderildi mi (buton etiketi + onay mesajını sürer).
- Türetilen: `submitLabel` (`sent ? 'Gönderildi ✓' : 'Teklif İsteyin'`).
- Prop/tweak (opsiyonel): `accentColor` (varsayılan `#E63329`), `accentColorDark` (`#c9271f`), `showWhatsApp` (bool). Renkler `--ac` / `--ac-d` CSS değişkenlerine bağlanır.
- **Data fetch:** yok (statik tanıtım). **Yapılacak entegrasyon:** form submit → CMS/endpoint'e POST (ör. `/api/contact`) veya e-posta servisi; başarı/hata durumlarını gösterin. Alanlar: `ad`, `telefon`, `hizmet`, `mesaj`.

## Design Tokens
**Renkler**
- Antrasit / ana metin (`--ink`): `#1A1A1A`
- Kırmızı vurgu (`--ac`): `#E63329` — hover koyu (`--ac-d`): `#c9271f`
- Koyu bölüm zemini: `#111111` · Hero zemini: `#0d0d0d` · Form kartı: `#181818` · input: `#111`
- Açık gri bölüm: `#F4F4F4` · Beyaz: `#FFFFFF`
- Kenarlıklar: açık `#e9e9e9` / `#ededed`; koyu `#2a2a2a` / `#2c2c2c` / `#333`
- İkincil metin: `#565656` (açık zemin), `#a8a8a8` / `#b0b0b0` / `#cfcfcf` (koyu zemin), `#9a9a9a` (eyebrow)
- Form onay yeşili: `#4ade80`

**Tipografi**
- Başlıklar: **Space Grotesk** (500/600/700) — H1/H2/H3, sayaçlar, numaralar; büyük başlıklar UPPERCASE + `letter-spacing:-0.01em`.
- Gövde: **Inter** (400/500/600/700).
- Ölçek: H1 `clamp(2.4rem,6.2vw,5rem)/0.98`; H2 `clamp(2rem,4vw,3rem)/1.02`; kart H3 `1.4rem`; avantaj/adım H3 `1.25–1.3rem`; gövde `0.95–1.25rem`, `line-height:1.6`; eyebrow `13px/0.2em uppercase`.

**Boşluk / Ölçü**
- İçerik genişliği: `max-width:1220px`, yatay padding `26px`.
- Bölüm dikey padding: `clamp(64px,8vw,110px)` (hero özel, yukarıda).
- Grid gap: kartlar 24px, galeri 14px, iletişim 44px.

**Köşe / Gölge / Kenarlık**
- Border-radius: butonlar/kartlar keskin (0) — *keskin köşe dili bilinçli*; form alanları `8px`; form kartı & harita `14px`; floating buton daire.
- Kart hover gölge: `0 22px 44px rgba(0,0,0,.13)`; header shrink gölge: `0 6px 24px rgba(0,0,0,.10)`.
- Aksan çizgileri: 2–3px kırmızı, `rotate(20deg)` diagonal; kart üst şeridi 4px (hover 8px).

**Animasyon süreleri:** reveal `.7s cubic-bezier(.22,.61,.36,1)`; buton slide `.32s`; kart/galeri hover `.28–.5s`; sayaç ~1400ms ease-out-cubic; WhatsApp pulse `2.4s` sonsuz.

## SEO Gereksinimleri (KRİTİK — birebir taşınmalı)
- `<html lang="tr">`.
- `<title>`: "Boblanlı Yapı | Kuşadası İnşaat, Tadilat ve Elektrik Hizmetleri"
- `<meta name="description">`: "Kuşadası ve Aydın'da inşaat, dekorasyon, revizyon ve elektrik arıza-onarım hizmetleri. Boblanlı Yapı ile güvenilir işçilik ve uygun fiyat. ☎ +90 532 657 62 71"
- Open Graph (og:title/description/type/image) ve `keywords` meta'sı mevcut.
- **Tek H1**, düzenli H2/H3 hiyerarşisi (yukarıdaki metinler anahtar kelime optimize edilmiştir: "Kuşadası inşaat/elektrikçi/tadilat", "Aydın inşaat firması" vb.).
- Telefon ve adres **metin olarak** (görsel değil) — yerel SEO için kritik; `tel:` linki var.
- **LocalBusiness JSON-LD** (`@type: GeneralContractor`) `<head>`'de: ad, adres, telefon, geo, çalışma saatleri, hizmet bölgeleri (Kuşadası/Aydın/Söke/Davutlar), `hasOfferCatalog` ile 4 hizmet. Tam blok kaynak dosyanın `<head>`'indedir — **aynen taşıyın**.
  - Güncellenecek: `email` (şu an placeholder `info@boblanliyapi.com`), `geo.latitude/longitude` (Google Haritalar'dan gerçek ofis koordinatı), gerekiyorsa `postalCode`.
- Görsellere açıklayıcı `alt` eklenecek (ör. `alt="Kuşadası inşaat çalışması - Boblanlı Yapı"`).
- Google Rich Results Test ile doğrulayın.

## Assets
- `boblanli-ico.png` — kare marka markası (house-B, kırmızı çatı detaylı), 2000×2000 saydam PNG. Header + footer + favicon.
- `boblanli-logo.png` — yatay tam logo (mark + "BOBLANLI YAPI / İNŞAAT | CONSTRUCTING"), 1663×929 saydam PNG. OG image / JSON-LD logo. İstenirse footer veya iletişimde büyük kullanılabilir.
- **Fotoğraflar (PLACEHOLDER — sağlanacak):** hero (koyu, geniş şantiye/bina; sol taraf koyu), 4 hizmet görseli, 8 galeri görseli. Kaynak önerisi: Unsplash/Pexels/Pixabay (telifsiz). Tutarlılık: hepsine ~%10 antrasit overlay / hafif desatürasyon, min. 1920px genişlik (hero), filigransız. `favicon` = ico.
- **İkonlar:** tümü tek renk (currentColor) **inline SVG çizgi ikon**, stroke-width 1.6, Lucide/Heroicons tarzı. Hedef kod tabanında mevcut ikon kütüphanesiyle (Lucide/Heroicons/Font Awesome) eşlenebilir: bolt, box, paint-roller, building, users, clock, tag, shield-check, phone, chat, map-pin, instagram, globe, search, file, hammer, key.

## Files
- `Boblanli Yapi.dc.html` — tasarımın tamamı (markup + inline stiller + davranış). Referans olarak inceleyin; markup/stil/davranışı hedef ortama çevirin. (Runtime `support.js`'i taşımayın.)
- `boblanli-ico.png`, `boblanli-logo.png` — marka görselleri.
