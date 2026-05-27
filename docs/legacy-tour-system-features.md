# Eski Tur Sistemi — Özellik Envanteri

> **Amaç:** Önceki tur satış sisteminin admin menü yapısı + özelliklerini bu dosyada toplamak.
> Tüm görseller analiz edildiğinde, **`docs/legacy-features-integration-plan.md`** dosyasına
> bu özelliklerin mevcut Tours modülüne nasıl entegre edileceğine dair bir plan yazılacak.
>
> **Mevcut Tours modülü:** `docs/tours-module-plan.md` Phase 1+2+3 (commit `5886eb4`).

---

## 0) Kullanıcı ile Locked Mimari Kararlar

Aşağıdaki tablo, eski sistem analizi sırasında kullanıcı ile birlikte kilitlenmiş
mimari kararları özetler.  Entegrasyon planı bu kararlarla yazılacak.

| # | Konu | Karar | Phase |
|---|---|---|---|
| 1 | **Tour'un 3 taksonomisi** | Ayrı modeller: `TourCategory` (hiyerarşik) + `Destination` (geo) + `Campaign` (zaman+indirim) | 1.5 |
| 2 | **Cruise master data** | `ShipCompany` → `Ship` → `Cabin` (zorunlu ship FK) | 1.5 |
| 3 | **CabinCategory** | Global master tablo (5 default seed: İç/Dış/Balkonlu/Okyanus/Suite) | 1.5 |
| 4 | **CabinGroup** | Company-level cabin selection bundle (m2m to Cabin), `uses_cabin_groups` flag ile opt-in | 1.5 |
| 5 | **Pricing model** | 5-boyutlu: `TourPriceGroup` (named, multi-date) + `TourCabinPrice` (per-cabin 6-tier matrix + calc_method) | 1.5 |
| 6 | **Port master** | `Port` modeli (geo + nüfus + video + flag from country_code CDN), `Destination` ↔ `Port` m2m | 1.5 |
| 7 | **Destinasyon = Gemi Destinasyonu** | Tek `Destination` modeli, `compatible_tour_types` array ile filtre | 1.5 |
| 8 | **3 tip kopyalama** | `TourReplicator` (tüm tour) + `TourDateReplicator` (bulk date) + `PricingReplicator` (inter-tour + date-to-date) | 3.5 |
| 9 | **Itinerary multi-stop/day** | `TourItineraryDay` per-day + `TourItineraryStop` per-port (Tab 2 "Program Gün Şablonu") | 1.5 |
| 10 | **Info-only extras** | `TourExtra.pricing_mode` enum'a `info_only` değeri ekle + `tenant_info_extras` master (vize/vergi) | 1.5 |
| 11 | **Vitrin Düzeni** | Yeni `tour-grid`, `tour-carousel`, `tour-featured-banner` section type'ları — mevcut CMS section pattern'ini kullan | 4 |
| 12 | **Kampanya engine** | Ayrı `Campaign` modeli (discount_type enum + tarih aralığı + m2m Tour/TourDate/Category/Destination), Phase 5 discount engine | 1.5 schema + 5 engine |
| 13 | **TourTypeSetting** | Per-tour-type vertical config (page mapping + dynamic form builder + ödeme opsiyonları) | 3.5 |
| 14 | **Tur Kopyala'da -c suffix** | Kopya edilen tour'da `copied_from_tour_id` FK + slug otomatik `-kopya-N` | 3.5 |
| 15 | **Stok Kodu (SKU)** | `Tour.sku` text alan, unique per-tenant | 1.5 |
| 16 | **TourType genişler** | `cruise/package/daily/ferry` 4 değer | 1.5 |
| 17 | **Tab 2 koşullu görünür** | `Cruise + Package` → Rota Takvimi var; `Daily + Ferry` → yok | 3.5 (admin form) |
| 18 | **PricingMode enum** | `per_person / per_person_group / per_reservation` | 1.5 |
| 19 | **SalesStatus enum** | `live_payment / live_contact / quote_with_acc / quote_without_acc / info_only` | 1.5 + 4 (frontend CTA) |
| 20 | **Araç Kiralama Formu** | Atlandı (kullanıcı not düştü) | — |

---

## 1) Üst Seviye Menü Yapısı

Eski sistem **3 farklı tur türünü ayrı menü grupları olarak** ayırmış:

| Menü Grubu | Durum | Karşılığı (yeni sistem) |
|---|---|---|
| **Günlük Ve Paket Turlar** | ⏳ Alt menü bekleniyor | Phase 1: `TourType::Package` + `TourType::Daily` |
| **Cruise Menu** | ✅ Bu doc'ta analiz edildi | Phase 1: `TourType::Cruise` (eksiklerle, aşağıda) |
| **Feribot Menu** | ⏳ Alt menü bekleniyor | ❌ Yeni sistemde yok (yeni bir vertical?) |

**🔍 Mimari gözlem:** Eski sistem bu üç ürünü **ayrı admin menüleri** olarak ayırmış.
Yeni sistemde tek polymorphic `Tour` modeli + `type` discriminator var (Phase 1 §D1 kararı).
İkisi de geçerli yaklaşım; ileride admin UX'te aynı ayrımı (üç farklı sidebar grubu)
yapmak yine mümkün — Tour modeli arkada tek tablo kalsa bile.

---

## 2) Cruise Menu — Alt İçerik (görsel #1'den)

### 2.1 Gemi Turu Ekle
- **Ne yapar:** Yeni cruise eklemek için **2 yollu** giriş sayfası:
  - 🟢 **Yeni Tur Ekle** — boş formdan başla
  - 🟠 **Tur Kopyala** — mevcut bir cruise'u baz alarak yenisini oluştur
- **Yeni sistem karşılığı:** `/admin/tours/create` (type=cruise) — ✅ Phase 3'te var,
  ama **"Tur Kopyala" akışı eksik 🆕**.

#### 2.1.a 🆕 Yeni Tur Ekle Formu — 9 Tab'lı Wizard

Eski sistemin tour ekleme formu **wizard pattern** — 9 ardışık adım:

| # | Tab | İçerik | Bizim sistemde |
|---|---|---|---|
| 1 | **Genel Bilgiler** | Temel info + kategori + öne çıkanlar + tur seçenekleri | ⚠️ Kısmi |
| 2 | **Rota Takvimi** | Gün gün itinerary | ⚠️ Kısmi (`TourItinerary`+`Day`) |
| 3 | **Genel Fiyatlar** | Tüm tarihlerde geçerli baz fiyatlar | ⚠️ Kısmi (`TourPriceTier`) |
| 4 | **Tarih & Fiyatlar** | Departure tarihleri + per-tarih fiyat override | ⚠️ Kısmi (`TourDate`) |
| 5 | **Açıklamalar** | Detaylı tur açıklamaları | ⚠️ Kısmi (translations) |
| 6 | **Seo Bilgileri** | Meta title/desc + canonical | ✅ Var |
| 7 | **Harita** | Rota / port haritası | ❌ Yok |
| 8 | **Resimler** | Galeri yönetimi | ✅ Var (Spatie) |
| 9 | **Yorumlar** | Tur-spesifik müşteri yorumları | ❌ Yok |

**🟡 UX kararı:** Bizim mevcut form tek-sayfa scrollable.  Wizard pattern cruise için
daha uygun olabilir çünkü çok alan var ve mantıksal gruplama kolaylaştırıyor.  Phase 3.5
veya Phase 5'te wizard'a geçilebilir.

---

##### Tab 1 — Genel Bilgiler (~17 alan)

| Alan | Tip | İçerik / Davranış | Bizim karşılık | Gap |
|---|---|---|---|---|
| **Seçtiğiniz Ürün Türü** | Dropdown | "Gemi Turları" / "Paket Tur" / "Günlük Tur" / "Feribot" | `Tour.type` | ✅ |
| **TUR / AKTİVİTE / HİZMET ADI** | Text | Tam tur başlığı | `TourTranslation.title` | ✅ |
| **Tur Adına Göre Seolinki Güncellemek** | Checkbox | Otomatik slug regen | YOK | 🆕 küçük UX |
| **Gezi Kodu** | Text | SKU (örn. "Selectrum Blu - Patmos ve Mykonos") | YOK | 🔴 (zaten gap'te) |
| **Satış Durumu** | Dropdown | "Satışta Ödemesiz" / muhtemelen başka opsiyonlar | YOK | 🆕 yeni enum |
| **Fiyatlandırma Türü** | Dropdown | "Kişibaşı Fiyat (2 Yetişkin + 1 Çocuk v.b)" / muhtemelen Kabin başı, Paket başı | Per-passenger sabit | 🆕 yeni enum |
| **Yayınla** | Checkbox | Published flag | `Tour.status='published'` | ✅ (bizimki 3-state enum, eskisi boolean) |
| **Ana Kategori ve Kampanya Kategorisi** | Multi-select tag | Yapısal kategori + kampanya — birarada | `TourCategory` (sadece yapısal) | ⚠️ Kampanya ayrımı yok |
| **Bölgeler** | Multi-select tag | Ziyaret edilen portlar (Çeşme, Mykonos, Patmos) | YOK | 🔴 (Port master data) |
| **Gezi Süresi** | Number + unit dropdown | "2 Gece" — sayı + birim (Gece/Gün/Saat) | YOK | 🟡 starts/ends_at'tan türetilebilir ama explicit alan UX'e iyi |
| **Gemi Firması / Gemi** | Dropdown | "Selectum Blu" — single select, hem firma hem gemi (ya da gemi seçince firma türer) | `type_config.ship_name` (string) | 🔴 (ShipCompany + Ship master) |
| **Öne Çıkan Başlıklar (1-6)** | 6 fixed text inputs | 6 sabit slot — "İstanbul'un en ünlü gizemlerini keşfedin" gibi cümleler | `translations.highlights` (serbest) | 🟡 Structured 6-slot daha tasarımcı-dostu |
| **Şerit Yazısı** | Text | Tur kartında köşede badge ("YENİ", "İNDİRİM") | YOK | 🆕 small frontend touch |
| **Kısa Açıklama** | TinyMCE WYSIWYG | Listeleme kartında, max 300 karakter | `translations.short_description` (text) | ⚠️ WYSIWYG vs plain text |
| **Özet** | TinyMCE WYSIWYG | Detay sayfasında above-the-fold | `translations.subtitle` (text) | ⚠️ WYSIWYG vs plain text |
| **Tur Seçenekleri** 🆕 | Multi-checkbox grid (~12+ option) | Marketing tag'leri (aşağıda) | YOK | 🔴 üçüncü taksonomi |

##### "Tur Seçenekleri" — gözlemlenen seçenekler

Görselde 12 seçenek var (ekran kesilmiş, daha fazla olabilir):

- Nehir Turları
- Uçaklı Paket Gemi Turları
- Bayram Turları
- Kurban Bayramı
- Mini Cruise
- **Sadece Gemi Turu** ✓
- Şeker Bayramı
- Sömestre Turları
- **Son Dakika Fırsatları** ✓
- Ultra Lüks Gemiler
- Yılbaşı Gemi Turları
- (devamı kesik)

**🚨 Önemli — "Tur Seçeneği" filtresinin anlamı netleşti:**

Önceki görselde gördüğümüz "Tur Seçeneği" cascade filtresi = **bu marketing tag'lere göre filtreleme**.
Yani 3 ayrı taksonomi sistemi var eski sistemde:

| Taksonomi | Amaç | Örnek | Mevcut |
|---|---|---|---|
| **Ana Kategori** | Yapısal/hiyerarşik | "Türkiye Çıkışlı Gemi Turları" | ✅ `TourCategory` |
| **Kampanya Kategorisi** | Zaman bağımlı promosyon | "Son Dakika İndirimleri" | ⚠️ Karışık (`TourCategory` ile aynı tabloda) |
| **Tur Seçeneği** (yeni) | Marketing tag — özellik filtresi | "Mini Cruise", "Ultra Lüks", "Bayram" | ❌ YOK |

**Mimari karar gerekecek:** 3 ayrı tablo mu, tek `taxonomy` tablosu + `type` discriminator mu?
Önerim: tek `TourTag` modeli + `tag_type` enum (`category`/`campaign`/`marketing`).
Hiyerarşi sadece `category` için.

##### Tab 1 alt-aksiyon butonları

- **Kaydet Devam Et** (yeşil) — kaydet + aynı tab'da kal
- **İleri** (mavi) — kaydet + Tab 2'ye geç
- **Önizleme** (mavi) — frontend preview

---

##### Tab 2 — Rota Takvimi (Per-Night Itinerary)

Top-level alanlar:

| Alan | Tip | İçerik | Bizim karşılık |
|---|---|---|---|
| **Tur Programı** (üstte) | TinyMCE WYSIWYG | Toplu rota özeti (örn. "Çeşme - Patmos - 1 gece Mykonos - Çeşme / 2 Gece - 3 Gün") | `TourItinerary.summary` ile eşleşir |
| **Tur Çıkış Şehri** | Dropdown (Port) | Hareket limanı (Çeşme) | YOK (origin port FK) |
| **Benzer Turlar** | Tag/multi-select | Cross-sell — bu turu beğenirseniz ilginizi çekebilecek diğer turlar | YOK |
| **Program Gün Şablonu Güncelle** 🆕 | Text + Güncelle btn | Pattern "1,1,1,2,3,3" — gece planlarının hangi günlere düştüğü | YOK |

**🧠 "Program Gün Şablonu" mekaniği (hipotezim):**

Cruise itinerary'lerinde bir gün içinde birden çok port ziyareti olabilir
(sabah Mikonos, öğleden sonra Delos, akşam Santorini gibi).  Pattern `1,1,1,2,3,3`
muhtemelen: 6 adet "gece/durak planı" var, sırasıyla gün 1'de 3 stop, gün 2'de 1, gün 3'te 2.

Yani **"Gece Planı" = bir port ziyareti**, **"Gün" = chronological day**.
Bizim modelde bu ayrım yok — her `TourItineraryDay` bir günü temsil ediyor.
Cruise için iki seviyeli yapı gerekebilir:
- `TourItineraryDay` (day_number 1-N)
- `TourItineraryStop` (parent: day) — port ziyareti, saat, konaklama

Veya basitleştirme: mevcut `day_number` yerine `position` + `day_number` ikilisi
(birden çok stop'un aynı gün numarasını paylaşmasına izin verir).

**Per-stop alanları ("X. GECE PLANI" tekrar eden bölüm):**

| Alan | Tip | İçerik | Bizim karşılık |
|---|---|---|---|
| **Gece numarası** | Number input | 1, 2, 3, ... | `TourItineraryDay.day_number` |
| **Şehir / Bölge / Liman / Başlık** | Text | Stop başlığı (Çeşme, Patmos) | `TourItineraryDay.title` |
| **Port dropdown** | Dropdown (Port master) | Port master'dan seçim — eski sistem master tutmuş | YOK (port_name string) |
| **Varış Saati** | Time picker | HH:MM | `TourItineraryDay.arrival_time` ✅ |
| **Kalkış Saati** | Time picker | HH:MM (örn. 02:00, 14:00) | `TourItineraryDay.departure_time` ✅ |
| **Konaklama** | Text | "Abc Hotel, Apart, Çadır", cruise için "Gemi" | YOK (yeni alan) |
| **Tur Programı** (per-stop) | TinyMCE WYSIWYG | O stop'ta yapılacaklar | `TourItineraryDay.description` ✅ |

**Footer:** Geri / Kaydet Devam Et / İleri / Önizleme butonları

---

##### Tab 3 — Genel Fiyatlar

Bu tab "tüm tarihler için geçerli baz fiyatlar" + "bilgi amaçlı ekstra ücretler"
+ pricing disclaimer içerir.

| Alan | Tip | İçerik | Bizim karşılık |
|---|---|---|---|
| **Başlangıç Fiyatı** | Number | Starting from price (örn. 399) | `Tour.base_price` ✅ |
| **İndirimli Başlangıç Fiyatı** 🆕 | Number | Promosyonlu starting price (boş = indirim yok) | YOK (Phase 5'e kalmış kupon engine ile çakışır) |
| **Para Birimi** | Dropdown | EURO, USD, TRY, GBP | `Tour.currency` ✅ |
| **EKSTRA AKTİVİTE VE ÜCRETLER** 🆕 | Repeatable rows | **"Bilgi amaçlı gösterilecek, online tahsil edilmeyecek"** ekstralar | ⚠️ Var ama farklı (TourExtra hep online tahsil edilir) |
| **Genel Açıklama** | TinyMCE WYSIWYG | Fiyat disclaimer (örn. "Doluluğa göre değişebilir...") | YOK |

**🆕 Önemli ayrım — 2 tip ekstra:**

Eski sistemde ekstralar **iki ayrı amaç** için var:

| Tip | Amaç | Online tahsil | Örnek |
|---|---|---|---|
| **Booking Extras** (Phase 1'de `TourExtra`) | Müşteri sepete ekler, online öder | ✅ Evet | Havalimanı transferi, içecek paketi, sigorta |
| **Info-only Extras** (yeni!) | Müşteri bilsin, ama farklı yerde / sonra öder | ❌ Hayır | Vize ücreti, havaalanı vergisi, kapora dışı yan ücretler |

**Mimari öneri:** `TourExtra.pricing_mode` enum'una yeni değer:
`per_booking` / `per_passenger` / **`info_only`** (üçüncü değer).
Frontend'de info_only olanlar "Bilmeniz gerekenler" bloğunda gösterilir,
sepet/checkout'a girmez.

**EKSTRA satır alanları:**

| Alan | Tip | İçerik |
|---|---|---|
| Ekstra Adı | Dropdown | Önceden tanımlı liste ("Vize Ücreti", "Havaalanı Vergisi" gibi) — master data? |
| Ekstra Fiyatı | Number | Tutar |
| Kur | Dropdown | EURO/USD/TRY |
| Kişi Başı | Checkbox | Kişi sayısı ile çarp |
| Sil | Button | Satır kaldır |

**🚨 Yeni keşif:** "Ekstra Adı" bir DROPDOWN — yani **info-only extras master tablosu**
da var (tenant'a göre). Vize ücretleri, havaalanı vergileri tekrarlanan kalemler
olduğu için ortak listede tutulmuş. Yeni sistemde benzer master data:
`tenant_info_extras` (ad, default_currency, default_amount).

**Genel Açıklama örnek metni:**
> "Müsaitlik ve fiyatlar doluluk oranına göre +/- farklılıklar oluşabilir.
>  Rezervasyon talep sırasında anlık geçerli müsaitlik ve fiyat bilgisi tarafınıza iletilecektir.
>  Fiyatlar 2 kişilik kabinlerde kişi başı fiyatlardır. Üç ve dört kişilik kabin
>  müsaitliği ve fiyat bilgisi için ön talep sırasında belirtmenizi rica ederiz."

Bu disclaimer her tour'da tekrarlanan boilerplate — muhtemelen tenant-level
varsayılan + per-tour override pattern'i olmalı.

---

##### Tab 4 — Tarih & Fiyatlar (Cruise Pricing Engine)

> ⚠️ **MİMARİ ALARM:** Bu tab eski sistemin **temel pricing felsefesini** ortaya koyuyor
> ve bu felsefe benim Phase 1 `TourPriceTier` modelimden **fundamental olarak farklı**.
> Cruise satışı için bu model standart — Phase 1.5 refactor'ün scope'unu ciddi büyütüyor.

##### Pricing Modeli (eski sistem)

Eski sistem 3-katmanlı bir hiyerarşi kuruyor:

```
Tarih (Departure Date)
  └── Fiyat Grubu (Price Group — "Yaz 2019", "Erken Rezervasyon")
        └── Oda/Kabin (Room/Cabin — "Standart İç", "Suite", ...)
              └── Person-Tier Matrix:
                    Double | 3rd Person | 4th Person | Child | Baby | Single
                     399       Sorunuz      Sorunuz     S       S     399
```

**Anahtar kavramlar:**

1. **Fiyat Grubu** — bir veya daha çok tarihe atanan fiyat seti
   - Aynı tarih için **birden çok grup** olabilir ("Standart fiyat" + "Erken rezervasyon fiyatı")
   - Bir grup **birden çok tarihe** atanabilir ("Yaz 2026 fiyatları" → 3 Tem, 14 Ağu, 25 Eyl)
   - **Bulk tarih ekleme:** Grup yaratırken multi-date picker → her tarihe aynı grup atanır

2. **Person-Tier Matrix** — her kabin × her tarih için 6-sütunlu fiyat tablosu
   - **Double (Per Person)** — 2 kişilik kabinde kişi başı
   - **3rd Person** — 3. kişi fiyatı
   - **4th Person** — 4. kişi fiyatı
   - **Child** — çocuk
   - **Baby** — bebek
   - **Single** — tek kişilik kullanım
   - "Sorunuz" = "Ask us" — fiyat girilmemiş, müşteri talep etmeli

3. **Hesaplama Yöntemi** (3 değer):
   | Yöntem | Mantık | Kullanım |
   |---|---|---|
   | **Standart (Doublex2)** | Toplam = Double × 2 | Çoğu cruise için (kişi başı × kişi sayısı) |
   | **Kişi Toplama (1+2+3+4)** | Toplam = Single+Double+Triple+Quad alanları | Karmaşık kabin tipi paketleri |
   | **Tek Kabin Fiyatı** | Toplam = sabit kabin fiyatı | Suite/villa tipi paket fiyatlandırma |

##### Tab 4 — Üst Liste Görünümü (mevcut grupların listesi)

Görselde:
- `+ 03-07-2026` (yeşil) — bir tarih, açılınca kabin × person-tier matrix gösterilir
- `+ 14-08-2026` (yeşil) — başka bir tarih
- `+ 25-09-2026` (yeşil) — başka bir tarih
- Her tarihin sağında: pusula (önizleme?), kalem (düzenle), çöp (sil)

Bir tarih genişletildiğinde:
- (a) **Kabin × Person-Tier matrix tablosu** (yukarıdaki örnek)
- (b) **KAMPANYALAR checkbox listesi** — tarihe özel campaign etiketleri

**🆕 Önemli — Kampanyalar tarih bazlı:**
Tab 1'de "Kampanya Kategorisi" tour-level tag olarak vardı. Tab 4'te aynı kampanyalar
**tarihe özel checkbox** olarak yeniden görünüyor — her departure'a farklı kampanya
atanabilir. (Örn. "Yaz 2026 tarihleri Erken Rezervasyon kampanyasında, Eylül tarihleri
Son Dakika İndirimi'nde.")

Tab 4'te görülen kampanya listesi (13 adet):
- 1 Tam 1 Yarım
- 2. Kişi Ücretsiz
- Erken Rezervasyon Fırsatları
- Kurban Bayramı Gemi Turları
- MSC Türkiye Çıkışlı Turlar
- Şeker Bayramı Gemi Turları
- Sömestre Gemi Turları
- Son Dakika İndirimleri
- TL ile Tatil Fırsatları
- Türkiye Çıkışlı Turlar
- Ultra Lüks Gemiler
- Vizesiz Gemi Turları
- Yılbaşı Gemi Turları

**Alt aksiyon butonları:**
- **Yeni Grup Ekle** (kırmızı) — yeni Fiyat Grubu formu açar
- **Tarihleri Kopyala** (turuncu) — bulk date-copy aksiyonu

##### Tab 4 — "Yeni Grup Ekle" Formu (Detaylı Pricing Group Form)

**Üst seviye (grup metadata):**

| Alan | Tip | İçerik | Bizim karşılık |
|---|---|---|---|
| **Tarih Seçiniz** | Multi-date picker | 1+ tarih seçilebilir, hepsine aynı grup atanır | YOK |
| **OPSİYON ADI** | Text | Fiyat grubu adı ("Yaz 2019 Fiyatları") | YOK |
| **AÇIKLAMA** | Text | Opsiyonel grup açıklaması | YOK |
| **Sıra** | Number | Sıralama (default 99999) | YOK |
| **MİNİMUM KİŞİ SAYISI** | Number | Grup için min booking kişi sayısı | YOK |
| **YETİŞKİN ÖNCELİĞİ** | Checkbox | Yetişkin önceliği (rezervasyon logic için) | YOK |
| **KONTENJAN** | Number | Grup-spesifik kapasite (0 = tarih kontenjanı kullan) | YOK |
| **KAMPANYA** | Text | Grup-level campaign tag | YOK |

> ℹ️ Info banner: "Farklı fiyat grupları oluşturarak farklı tarih aralıklarına atayabilir
> veya aynı tarih için farklı opsiyonlar ekleyebilirsiniz"

**Oda Ayarları (room/cabin pricing — repeatable):**

| Alan | Tip | İçerik | Bizim karşılık |
|---|---|---|---|
| **ODA ADI / TÜRÜ** | Text | "Standart Oda", "İç Kabin", "Suite" | `TourCabinType.name` |
| **Fiyat Tanımı** | Text | "1 Tam 1 Yarım Gibi Fiya..." — pricing açıklaması | YOK |
| **Hesaplama Yöntemi** | Dropdown (3 değer) | Standart Doublex2 / Kişi Toplama / Tek Kabin | YOK |
| **Sıra** | Number | Sıralama | YOK (sort_order var) |
| **ÇOCUK YAŞ ARALIĞI** | 2x dropdown (min-max) | Çocuk fiyatının geçerli yaş aralığı | YOK |
| **BEBEK YAŞ ARALIĞI** | 2x dropdown (min-max) | Bebek fiyatının geçerli yaş aralığı | YOK |
| **Kabin Data** | Dropdown | Master kabin tablosundan seç ("Kabin Seçiniz") | YOK |
| **Güverte / Kat Data** | Dropdown | Hangi güverte/kat ("Güverte / Kat Seçiniz") | YOK |

**Person-Tier Price Row (per-room):**

| SINGLE 👤 | DOUBLE 👥 | TRIPLE 👥👥 | QUAD 👥👥👥 | ÇOCUK 🧒 | BEBEK 👶 | PARA BİRİMİ |
|---|---|---|---|---|---|---|
| 100.00 | 50.00 | 0 | 0 | 0 | 0 | EURO |

**Save butonları:**
- "Kaydet ve yeni bir fiyat grubu oluştur" (yeşil — kaydet + yeni grup formu)
- "Kaydet ve devam et" (mavi — kaydet + Tab 5'e geç)

##### Yeni Sistem — Pricing Model Gap

Mevcut Phase 1 `TourPriceTier` modeli **2 boyutlu**:
- Passenger Type (adult/child/infant/senior)
- Cabin Type (cruise için)

Eski sistem pricing **5 boyutlu**:
- Date (specific departure)
- Price Group (named season/campaign)
- Cabin/Room Type
- Person Tier (Single/Double/3rd/4th/Child/Baby)
- Calculation Method (3 farklı total computation)

**Eksik model katmanları:**

| Eksik | Phase önerisi |
|---|---|
| `TourPriceGroup` modeli (named, multi-date attachable) | 1.5 |
| Date ↔ PriceGroup m2m relation | 1.5 |
| Per-cabin person-tier matrix (6 column) | 1.5 (mevcut TourPriceTier'ı genişlet) |
| `calculation_method` enum (3 değer) | 1.5 |
| Per-room child/baby age range | 1.5 |
| Deck/Floor data (cruise için kat seçimi) | 1.5 (CabinGroup master) |
| Per-date kampanya tag'leri | 1.5 |
| Multi-date bulk pricing entry UX | 3.5 (admin form) |

**🚨 Phase 1 modelinin yetersizliği:**
Mevcut model bir cruise için tek satır TourPriceTier yapar:
```
{passenger_type: adult, cabin: 'Suite', price: 1219}
```
Ama eski sistem aynı cabin × aynı tarih için 6 farklı price tier ister:
```
{date: 03-07-2026, group: "Yaz 2026", cabin: "Suite",
 single: 1219, double: 1219, triple: 0, quad: 0, child: 0, baby: 0,
 calc_method: 'standart_doublex2'}
```

Bu Phase 1.5 refactor'ü **kabin master + pricing model'in tam yeniden yazımı**
anlamına geliyor.

##### Tab 4 — "Fiyat Kopyala" Alanı (3. tip kopyalama)

Eski sistemin **üçüncü kopyalama operasyonu** — Tab 4 içinde ayrı bir alan.
"Yeni Grup Ekle" ve "Tarihleri Kopyala" butonlarının altında yer alıyor.

**🔑 Cruise satış operasyonunun gerçeği:**
Eski sistem 3 farklı seviyede kopyalama sunuyor çünkü cruise satışı sürekli
"benzerini yeniden yarat" işidir — aynı gemi, aynı rota, farklı sezon/tarih.

| Kopyalama Tipi | Nereden | Nereye | Ne kopyalanır |
|---|---|---|---|
| **Tur Kopyala** (top of tour list) | Mevcut tour | Yeni tour kaydı | Tüm tour (yapı + opsiyonel fiyat + opsiyonel tarih) |
| **Tarihleri Kopyala** (Tab 4 üst, turuncu btn) | Mevcut tour'un tarihleri | Aynı tour'a yeni tarihler | Tarih + ona bağlı price group atamaları |
| **Fiyat Kopyala** (Tab 4 alt, bu ekran) | İki ayrı senaryo (aşağıda) | (Aşağıda) | Sadece fiyat/tarih, tour yapısı korunur |

##### Tab 5 — Açıklamalar (Flexible Content Sections)

> ℹ️ Info banner: "Aşağıdaki Yazı Alanları Tur Detay Sayfasında Detaylar Alanındaki
> Yazı Düzeni İçin Kullanabilirsiniz. Örnek 1. Alanı Genel Notlar, 2. Alanı Dahil
> Olanlar, 3. Alanı Hariç Olanlar, 4. Alanı Kabin veya Otel Bilgisi için
> kullanabilirsiniz. Sıralamayı Değiştirirseniz Tur Detay Sayfasında da Aynı Sıra
> İle Görüntülenecektir."

**Alan yapısı:**
- **3-4 adet "Bilgi Alanı"** — generic, sıralanabilir WYSIWYG bölümleri
- **1 adet "Voucher Alanı"** — voucher PDF/email içeriği için ayrı alan

**🔑 Önemli mimari fark:**

Eski sistem **N adet generic + ordered text section** sunuyor.  Admin her bölüme
istediği başlığı verir (Dahil Olanlar / Hariç Olanlar / Kabin Bilgisi vs.) ve
sıralayabilir.  Frontend bu sırayla render eder.

Bizim Phase 1 modeli **structured** — `description`, `highlights`,
`important_info`, ayrıca `TourInclude` modeli (`included: boolean` flag).

| Yaklaşım | Avantaj | Dezavantaj |
|---|---|---|
| **Yapılı (bizim)** | Filtreleme, "dahil olanlar" sayısını sorgula, otomatik schema.org Tour markup | Sabit alan listesi, admin yeni bölüm tipi ekleyemez |
| **Esnek (eski)** | Admin custom bölümler yaratır (örn. "VİP transfer detayları") | Tutarsızlık riski, frontend genel render |

**Öneri:** Hibrit model — Phase 1'in yapılı alanlarını koru, **ekstra `TourContentSection`**
modeli ekle (sıralanabilir, başlık + içerik).  Admin yapılı alanlar dolduktan sonra
custom section'lar ekleyebilir.  Frontend her ikisini de render eder.

```php
TourContentSection
  - tour_id
  - language_id
  - title         // örn. "VİP Transfer Detayları"
  - body          // HTML/markdown
  - sort_order
  - is_voucher    // bool — true ise sadece voucher PDF'inde gösterilir
```

`is_voucher` flag'i Voucher Alanı ihtiyacını da karşılar (ayrı alan yerine
aynı tablonun bir tag'i).

**Tab 5 örnek içerik:**

| Alan | Başlık (admin koymuş) | Word count |
|---|---|---|
| Bilgi Alanı 1 | "TAM PANSİYON (FİYATA DAHİLDİR)" + restoran detayları | 42 |
| Bilgi Alanı 2 | "FİYATA DAHİL OLMAYAN HİZMETLER" + servis ücretleri | 52 |
| Bilgi Alanı 3 | "Katılım için:" + vize gereklilikleri (Schengen / Yeşil pasaport vb.) | 145 |
| Voucher Alanı | (boş — bu turda voucher metni girilmemiş) | 0 |

**Footer:** Geri / Kaydet Devam Et / İleri / Önizleme

---

##### Tab 6 — SEO Bilgileri

⏳ **Ekran görüntüsü paylaşılmadı**, ama kullanıcı not düştü: "2. tab seo".

**Mevcut sistem karşılığı:** ✅ Phase 1'de tam destek var:
- `TourTranslation.meta_title`
- `TourTranslation.meta_description`
- `TourTranslation.og_image_url`
- `Tour.structured_data_json` (custom JSON-LD)

Olası eksikler (görsel gelince netleşir):
- Canonical URL field
- Sitemap priority / change frequency
- robots.txt directives (noindex/nofollow per tour)

---

##### Tab 7 — Harita (Interactive Route Map)

**🔑 Çok kritik mekanik:** Tab 2 (Rota Takvimi)'nde seçilen Limanlar Port master'dan
çekildiği için zaten **geo-koordinatları var**.  Tab 7'de "Rotadan Harita oluştur"
butonu otomatik haritayı çiziyor.

Bu, daha önce gap olarak işaretlediğimiz **Port master data + koordinat** ihtiyacının
neden bu kadar kritik olduğunu açıklıyor: Auto-map generation için elzem.

##### Tab 7 — Layout

**Sol panel — Map points list:**
- İlk satır: ikonlu (yürüyen kişi 🚶) = **Buluşma/Hareket Noktası** (örn. Çeşme)
- Diğer satırlar: numaralı (1, 2, 3 = gün numarası) = **Ziyaret Noktaları**
- Her satır: gün # | yer adı | kırmızı sil btn | mavi expand btn (koordinat detay)

**Sağ panel — Leaflet map (OpenStreetMap):**
- Arama kutusu: "Aranacak yerin adını yazınız" (geocoding search)
- 3 buton:
  - 🔴 **Buluşma veya etkinlik noktasını ekle** — meeting/activity pin
  - 🟠 **Ziyaret edilecek nokta ekle** — visited pin
  - ⬜ **Baştan başla** — sıfırla
- Map: zoom +/-, pin'ler day# ile etiketli

**Footer:**
- Geri (back to Tab 6)
- 🟠 **Rotadan Harita oluştur** — Tab 2 itinerary'den otomatik üret

##### Görseldeki örnek noktalar

```
🚶 Çeşme          (start point — Buluşma)
1  Patmos         (gün 1)
1  Mykonos        (gün 1 — aynı gün ikinci stop!)
2  Mykonos        (gün 2)
3  Mykonos        (gün 3)
3  Çeşme          (gün 3 — return to start)
```

**Note:** Aynı yer (Mykonos) 3 ayrı gün için 3 ayrı kayıt — multi-day stay senaryosu.
Çeşme hem gün 0 (buluşma) hem gün 3'te (dönüş) görünür.

**Day pattern doğrulama:** `1, 1, 2, 3, 3` (Çeşme bağımsız sayılırsa) — Tab 2'deki
"Program Gün Şablonu" mekanikine birebir uyuyor.  Aynı gün içinde birden çok port
ziyareti olabileceğini bir kez daha kanıtlıyor.

##### Yeni Sistem Gap

| Eksik | Phase önerisi |
|---|---|
| **İki nokta tipi** (meeting vs visit) | 1.5 (`TourItineraryDay.point_type` enum: `meeting` / `visit`) |
| **Auto-map from route** | 4 (frontend service — port koordinatlarından polyline çiz) |
| **Leaflet/OSM integration** | 4 (frontend) |
| **Tour-level map snapshot** | 4 (cache edilen GeoJSON / static image) |
| **Geocoding search** (yer ara → koordinat al) | 4 (admin form — Nominatim API) |
| **Multi-stop per day** (1,1 patterni) | 1.5 (itinerary refactor — zaten gap'te) |

**Mimari yorum:** Mevcut `TourItineraryDay.geo_lat/geo_lng` alanları doğru yönde
ama tek başına yetersiz.  Phase 1.5'te:
1. Port master + koordinat (kez bir kez gir, her tour'da kullan)
2. TourItineraryDay → Port FK (string yerine)
3. Tour-level map auto-generation (Port geo'larından)
4. Frontend Leaflet component (her tour detay sayfasında)

---

##### Tab 8 — Resimler (Image Library with Ship/Port Templates)

> **🔑 Çok kritik keşif:** Eski sistem tur-spesifik resimlerle birlikte
> **Ship + Port master tablolarının kendi resim kütüphanelerini** kullanabiliyor.
> "Gemi Resimleri" seçince o turun gemisinin tüm fotoğrafları otomatik geliyor.

##### Resim Listeleme Şablonu

**Dropdown — 3 seçenek:**
- "Resim Şablonu Seçiniz" (default — manuel seçim)
- **"Gemi Resimleri"** — `Tour.ship_id` üzerinden Ship master'dan otomatik çek
- **"Liman Resimleri"** — `Tour.itinerary_days[*].port_id` üzerinden Port master'dan otomatik çek

**Mimari implikasyon:**
Phase 1.5 refactor'de Ship + Port master modelleri kurulurken ikisi de Spatie
media-library entegre olmalı:

```php
class Ship extends Model implements HasMedia {
    use InteractsWithMedia;
    public function registerMediaCollections(): void {
        $this->addMediaCollection('gallery');       // gemi fotoğrafları
        $this->addMediaCollection('cabin_photos');  // kabin fotoğrafları
        $this->addMediaCollection('deck_plan')->singleFile();  // güverte planı
    }
}

class Port extends Model implements HasMedia {
    use InteractsWithMedia;
    public function registerMediaCollections(): void {
        $this->addMediaCollection('gallery');       // liman fotoğrafları
    }
}
```

Frontend'de tour detay sayfasında:
- Admin Resim Listeleme Şablonu = "Gemi Resimleri" seçtiyse → Tour.ship.gallery
- Admin = "Liman Resimleri" seçtiyse → Tour.itineraryDays.map(d => d.port.gallery).flat()
- Admin = manuel → Tour.gallery (tour-specific)

##### Image Row Yapısı

Her resim satırında:
- ⤧ Drag handle (sıralama)
- Thumbnail
- **Resim Adı** (input) — alt text + caption
- **Kısa Açıklama** (input) — daha uzun açıklama
- ⭐ Star button — cover/featured flag (anasayfa, OG image vb. için)
- 👁 Eye button — visibility toggle (gizle/göster)
- ☐ Checkbox — bulk action seçimi

**Drag-and-drop sortable list.**

##### Resim Yükleyin veya Seçin

- Yeşil kesik-çizgi drop zone
- Yükle (file picker) veya Seç (media library'den)
- Multi-upload destekli

##### "English" Toggle (sağ üst)

Tour başlığının yanında "English" butonu — büyük ihtimal **resim metadata multi-language**
(Resim Adı + Kısa Açıklama her dil için ayrı).  Phase 1'de Spatie media-library
custom properties JSON kolonu var, multi-language buraya yazılabilir.

##### Yeni Sistem Gap

| Eksik | Phase önerisi |
|---|---|
| **Ship.gallery media collection** | 1.5 (Ship modeli ile birlikte) |
| **Port.gallery media collection** | 1.5 (Port modeli ile birlikte) |
| **Image template selector (Ship/Port/Custom)** | 4 (frontend image aggregator) |
| **Per-image name + description fields** | 3.5 (Spatie custom_properties) |
| **Cover/Featured flag** | 3.5 (Spatie collection_name veya custom_property) |
| **Visibility toggle (hide image)** | 3.5 |
| **Multi-language image metadata** | 3.5 (custom_properties JSON) |

##### 🆕 Bonus İntel — Görseldeki Tour İsmi

> "COSTA FAVOLOSA İLE NORVEÇ FİYORTLARI THY UÇUŞLU 10.06.2026"

Decode:
- **COSTA FAVOLOSA** = gemi adı
- **NORVEÇ FİYORTLARI** = destinasyon (Norveç Fiyortları)
- **THY UÇUŞLU** = THY (Türk Hava Yolları) uçağı dahil
- **10.06.2026** = hareket tarihi

**🔑 Yeni özellik gereksinimi:** "Uçaklı paket gemi turları" — cruise + flight bundle.
Tab 1'deki "Uçaklı Paket Gemi Turları" marketing tag'i bu içindi.

**Phase önerisi:** `Tour.includes_flight: bool` + opsiyonel `flight_info` JSON
(taşıyıcı, kalkış havalimanı, varış havalimanı).  Tam bir Flight entity overkill —
basit bir JSON yeterli.  Frontend'de uçak ikonu + havalimanı bilgisi gösterilir.

---

##### Tab 9 — Yorumlar (Tour-Specific Review System)

⏳ **Ekran görüntüsü paylaşılmadı**, ama kullanıcı not düştü:
> "Tur detayda tura yapılan yorumlar burada görülüyor ve onaylanıyor. Yada serbest yorum ekleniyor."

##### Çıkardığım davranış

Eski sistem tour başına bir **moderasyon kuyruğu + manuel yorum ekleme** aracı sunmuş:

1. **Frontend submission akışı:**
   - Tur detay sayfasında müşteri yorum bırakır (isim + puan + yorum)
   - Backend'e POST gider, `status='pending'` olarak kaydedilir
   - Frontend'de henüz görünmez

2. **Admin moderasyon akışı (Tab 9):**
   - Tüm pending yorumlar listede görünür
   - Admin onayla (approved) / reddet (rejected)
   - Onaylananlar frontend'de görünür hale gelir

3. **Manuel yorum ekleme:**
   - Admin "serbest yorum" girebilir (örn. müşteri telefon/email ile yorum verdiyse)
   - Doğrudan `status='approved'` ile kaydedilir
   - Frontend'de hemen görünür

##### Yeni Sistem Karşılığı

**❌ Mevcut sistem:** Tour-level review YOK.
- Phase 1'de `Review` modeli var ama generic (`reviewable_type/id` polymorphic, başka entity'lere bağlanır)
- Tour için kullanılmıyor

**🆕 Gerekli iş:**
```php
// Mevcut Review modeli polymorphic — Tour'a bağlanmak için sadece eklemeler:
//   reviewable_type = App\Modules\Tours\Models\Tour::class
//   reviewable_id   = $tour->id

// Yeni: TourReview admin moderation UI
// Yeni: Tour::reviews() relationship + scope (approved only)
// Yeni: Frontend review submit form
// Yeni: Admin Tab 9 panel
```

##### Gap

| Eksik | Phase önerisi |
|---|---|
| **Tour review submit (frontend)** | 4 (BookingController benzeri, form submit endpoint) |
| **Tour review moderation (admin)** | 3.5 (Tab 9 listesi + onay/red butonları) |
| **Manuel review ekleme (admin)** | 3.5 (admin form) |
| **Tour-level review aggregation** (ortalama puan vb.) | 4 (frontend için) |
| **Spam protection** (captcha, rate limit) | 5 |
| **Verified booking flag** (sadece booking yapanlar yorum yazsın?) | 5 (opsiyonel) |

##### 🚨 Önemli soru

Phase 1'deki mevcut polymorphic `Review` modeli yeterli mi yoksa
`TourReview` ayrı model gerekir mi?

**Öneri:** Mevcut polymorphic Review yeterli.  Sadece scope + admin UI ekle.
Tour-spesifik alanlar gerekirse (örn. "Hangi kabini kullandı") `custom_data` JSON
kolonu eklenir.

---

##### Fiyat Kopyala — İki Bölüm

**Bölüm 1: Başka tour'dan fiyat al (inter-tour)**

> ⚠️ "LÜTFEN BU TUR İÇİN AŞAĞIDAN FİYAT VE TARİHLERİ KOPYALANACAK TURU SEÇİN."

| Alan | Tip | İçerik |
|---|---|---|
| Kopyalanacak Tur | Dropdown | Source tour (başka bir tour'dan) |
| Tarihlerde Kopyalansın | Checkbox | Tarihleri de kopyala? |
| Fiyatlarda Kopyalansın | Checkbox | Fiyatları da kopyala? |
| Kopyala | Yeşil button | Uygula |

**Kullanım senaryosu:** "Geçen sezon MSC Bellissima için kurduğum 12 kabin × 6 person-tier
pricing matrisi var. Aynı yapıyı Costa Mediterranea'nın yeni cruise'una uygula —
sadece tarihler benim olsun."

**Bölüm 2: Aynı tour'un bir tarihinden diğerine (intra-tour, date-to-date)**

> ⚠️ "LÜTFEN HEDEF TARİH VE KOPYASI ALINACAK TARİHİ SEÇEREK BUTONA BASINIZ."

| Alan | Tip | İçerik |
|---|---|---|
| Hedef Tarihi Seçiniz | Dropdown | Hangi tarihe yapıştır |
| ←Kopyası Alınacak Seçiniz | Dropdown | Hangi tarihten al ("←" ok yön gösteriyor) |
| Kopyala | Kırmızı button | Uygula |

**Kullanım senaryosu:** "03-07-2026 tarihinin tüm kabin × person-tier matrisini
kurdum. Aynı fiyatları 14-08-2026 tarihine kopyala — sonra elle %10 fark uygulayacağım."

##### Yeni Sistem — Kopyalama Stratejisi

Phase 1.5'te bir `PricingReplicator` service tasarlayalım:

```php
PricingReplicator::copyFromTour(Tour $source, Tour $target, bool $dates, bool $prices)
PricingReplicator::copyBetweenDates(TourDate $source, TourDate $target)
TourReplicator::replicate(Tour $source, bool $dates, bool $prices)  // tüm tour
```

3 service, 3 ayrı UX trigger:
- Tour list page üst kısımdaki "Tur Kopyala" → `TourReplicator`
- Tab 4 üst "Tarihleri Kopyala" → `TourDateReplicator` (date-level)
- Tab 4 alt "Fiyat Kopyala" → `PricingReplicator` (2 modu)

---

#### 2.1.b 🆕 Tur Kopyala (önemli özellik)

Cruise satışında çok yaygın bir UX: aynı gemi, aynı rota, farklı tarih.
Her seferinde yeniden yazmaktan kurtarır.

**Akış (ekran görüntüsünden):**
1. **"Kopyalanacak Tur:"** dropdown → mevcut tüm cruise'lar listelenir
2. **"Fiyatlarda Kopyalansın"** checkbox (default ❌ kapalı)
3. **"Tarihlerde Kopyalansın"** checkbox (default ❌ kapalı)
4. **"Kopyala"** butonu

**Davranış (çıkardığım):**

| Checkbox durumu | Kopyalanan | Sıfırdan girilen |
|---|---|---|
| Her ikisi kapalı (default) | Tour temel + çeviri + itinerary + kabin tipleri + ekstralar + destinasyon | Tarihler + fiyatlar |
| Sadece "Fiyatlarda" | + price_tiers | Tarihler |
| Sadece "Tarihlerde" | + tour_dates | Fiyatlar |
| Her ikisi açık | Tam kopya | Sadece yeni slug + içerik düzeltmeleri |

**Yeni sistem entegrasyonu önerisi:**
- `App\Modules\Tours\Services\TourReplicator` (yeni service)
- `TourReplicator::replicate(Tour $source, bool $copyPrices, bool $copyDates): Tour`
- Yeni slug otomatik üret: `{original-slug}-kopya-1`, `-kopya-2`, ...
- "Tur Kopyala" admin action → form modal → POST /admin/tours/{tour}/replicate
- **Phase önerisi:** Phase 3.5 / Phase 4 başlamadan eklenmeli — admin sık kullanacak

**Dropdown'daki tur ismi formatı (önemli bonus intel):**
```
6* Oceania Allura ile Adriyatik ve Akdeniz Gemi Turu (17-10-2026)
│  │       │      │  └── destinasyon                    └── tarih
│  │       │      └── "ile" bağlacı
│  │       └── gemi adı (Ship)
│  └── gemi firması (ShipCompany)
└── gemi yıldız sayısı (Ship.star_rating)
```

**Mimari implikasyonlar:**
- `Ship.star_rating` alanı (tinyint 1-7) gerekli → mevcut Phase 1'de yok
- Tur başlığı `ship + destination + date` ile **otomatik üretilebilir** —
  belki `Tour.auto_title` template'i bir tenant ayarı olur
- Şu an `TourTranslation.title` serbest text; eski sistemde bu pattern'e
  zorlanmamış ama disiplinli kullanılmış

### 2.2 Gemi Turları (Liste + Arama)
- **Ne yapar:** Mevcut tüm cruise'ların listesi + zengin arama paneli
- **Yeni sistem karşılığı:** `/admin/tours?type=cruise` — ✅ temel liste var, ⚠️ **arama paneli çok eksik**
- **Not:** Eski sistem cruise'u ayrı liste olarak ayırırken yenide tek listeden filtre var.
  Belki UX olarak ayrı bir alt-route eklemek daha doğal (`/admin/cruise-tours` gibi).

#### 2.2.a Tur Arama Paneli — Eski Sistem Alanları

Eski admin'in arama paneli **10 farklı filtre** sunuyor:

| Filtre | Davranış | Yeni sistem karşılığı |
|---|---|---|
| **Tur Tipi** | Top-level seçim — diğer filtreleri açar | ✅ Var (`type` filter) |
| **Destinasyon** | Cascade: Tur Tipi seçilince doldurur | ❌ Yok (Destination master data eksik) |
| **Bölge / Liman** | Cascade — coğrafi gruplama + spesifik port | ❌ Yok (Port master data eksik) |
| **Tur Seçeneği** 🆕 | Cascade — anlamı belirsiz, kullanıcıya sorulacak | ❌ Yok |
| **Gemi Firması** | ShipCompany filter | ❌ Yok (Phase 1.5 — Ship master) |
| **Gemi** | Ship filter ("Select Cruise Line Name" placeholder) | ❌ Yok |
| **Gösterim** | 4 state: Yayın / Yayın Dışı / **Süresi Geçen** / Hepsi | ⚠️ 3 state var, **"Süresi Geçenler" yok** |
| **Tur Kodu** | Free-text SKU search | ❌ Yok (Stok Kodu alanı bile yok!) |
| **Tur Adı** | Free-text title search | ✅ Var (search_index üzerinden) |

**🆕 Üstte ek araçlar (sağ panel):**

| Araç | Ne yapar |
|---|---|
| **Sıralama** dropdown + Ayarla | Yeniden Eskiye / Eskiden Yeniye / A-Z / Fiyat ↑↓ / Popülerlik |
| **İndirimli Fiyat Güncelle** + Düzenle | 🔴 **Toplu fiyat güncelleme** — seçili tour'lara % veya sabit indirim uygular |

#### 2.2.b Liste Tablosu — Sütunlar

| Sütun | İçerik | Yeni sistem |
|---|---|---|
| **Stok Kodu** 🆕 | SKU / business reference (örn. `28038 - 23.01.2027`, `NCL Pearl - 30.08..2026-CRUISE REP`) | ❌ Yok |
| 📷 **Photo count** | "📷(11)" — kaç fotoğraf yüklü | ❌ Yok (Spatie var, sayı surface edilmiyor) |
| **Tur Adı** | Tam başlık | ✅ Var |
| **Kategori** | (Çoğu satırda boş — opsiyonel) | ✅ Var (TourCategory) |
| **Fiyatı** | Starting-from price + currency (örn. `399 EUR`, `3696 EUR`) | ✅ Var (base_price) |
| **Düzenle** | Görüntüle (👁) + Düzenle (✏️) butonları | ✅ Var |
| **Checkbox** | Toplu işlem (fiyat güncelle vs.) için seçim | ❌ Yok |

#### 2.2.c Önemli İntel — Eski Sistemin Pattern'leri

**Stok Kodu örnekleri:**
```
Selectrum Blu - Patmos ve Mykonos          → ad bazlı, tarih yok
NCL Pearl - 30.08..2026-CRUISE REP         → gemi + tarih + "REP" marker
NCL Aqua - cruise rep                       → marker only
28038 - 23.01.2027                          → numeric + tarih
28038 - 23.01.2027 -c                       → "-c" suffix = kopya! (Tur Kopyala'dan)
Independence - 17.05.2027                   → gemi + tarih
```
Pattern düzensiz ama **`-c` suffix'i = kopyalanmış tur** — Tur Kopyala özelliğinin
DB'de izinin bırakıldığı yer.  Bizim sistemde benzer bir convention veya
explicit `copied_from_tour_id` FK gerekebilir.

**Fiyat sergileme:**
- Listede tek fiyat var (örn. "399 EUR") — büyük ihtimal **en düşük kabin/yetişkin fiyatı**
  ("starting from").  Çoklu kabin tiplerinin minimumu hesaplanıp gösteriliyor.
- Currency EUR — eski sistem multi-currency (bizde Phase 5 planı zaten var).

**Tarih + tur ilişkisi gizemi:**
- Bazı stok kodlarında tarih var ("28038 - 23.01.2027"), bazılarında yok
- Aynı gemi+rota için 2 satır görünüyor: `28038 - 23.01.2027 -c` ve `28038 - 23.01.2027`
- **Hipotez:** Eski sistemde **bir tour = bir departure tarihi** (her tarih ayrı tour record).
  Bizim sistemde **bir tour = N departure tarihi** (TourDate'ler ayrı).
- Bu, eski sistemde **"Tur Kopyala"nın neden bu kadar kritik olduğunu** açıklıyor:
  her yeni tarih için yeni tour kaydı gerekiyor → kopyala-değiştir akışı.
- 🟢 **Yeni sistemin avantajı:** Bizim modelimiz bir tour'a N tarih eklemeye izin verir,
  o yüzden Tur Kopyala'ya **olmazsa olmaz** değil — ama "yeni sezon, sıfırdan başla"
  senaryosunda yine değerli.

**🚨 Kullanıcıya sorulacak:** "Tur Seçeneği" filtresi ne yapar?  Olası anlamlar:
- (a) Cruise süresi (3 gün / 7 gün / 14 gün)
- (b) Kabin sınıfı ("Inside Cabin", "Balcony" vs. — search-time)
- (c) Paket tipi ("Cabin only" / "All inclusive" / "Bev package dahil")
- (d) Başka bir şey?

### 2.3 Vitrin Düzeni 🆕 — ✨ Kullanıcı önerime açık

> **Kullanıcı:** "Vitrin düzeni için farklı senin belirleyeceğin bir sistem kullanabiliriz"

- **Ne yapar:** Frontend cruise sayfasındaki **showcase / curation tool**.
  Hangi cruise'lar anasayfada nasıl sıralı görünür, hangi banner'lar üstte, vs.
- **Yeni sistem karşılığı:** ❌ **YOK**.  Phase 1'de sadece tek tek tour üzerinde
  `is_featured` boolean var; toplu görsel curation tool'u yok.

#### 2.3.a ✨ Önerim — Section-Based Showcase (mevcut CMS pattern'ini kullan)

Mevcut iraspa-cms zaten **section/block pattern** üzerine kurulu (Page → Sections → render).
Vitrin Düzeni için ayrı bir özel model yerine **yeni section type'ları** eklemek
çok daha doğru:

##### Yeni Section Type'ları (Phase 4'te frontend)

| Section Type | Davranış | Kullanım |
|---|---|---|
| `tour-grid` | Filtre kriterleri ile tour listele | "En çok satan 6 cruise" |
| `tour-carousel` | Otomatik kayan tour kartları | Hero altı slider |
| `tour-featured-banner` | Manuel seçilen tour + custom banner img/text | Anasayfa premium spot |
| `tour-search-widget` | Embedded search formu | Anasayfa booking widget |
| `tour-by-destination` | Destinasyon bazlı grid (Yunan Adaları, Karayipler) | "Destinasyona göre keşfet" |
| `tour-by-campaign` | Aktif kampanyaya bağlı tour'lar | "Son Dakika Fırsatları" |

##### Her section'ın admin config'i (JSON)

```json
{
  "title": "En Popüler Cruise'lar",
  "filter": {
    "tour_type": "cruise",
    "status": "published",
    "is_featured": true,
    "destination_ids": [1, 2],
    "campaign_ids": [],
    "max_price": 5000,
    "departure_after": "2026-06-01"
  },
  "display": {
    "layout": "grid_3_col",
    "show_price": true,
    "show_dates": true,
    "show_ship": true,
    "cta_label": "İncele"
  },
  "sort": "price_asc",
  "limit": 6,
  "manual_pinned": [12, 34, 56]  // Manuel öne sabit tour ID'leri
}
```

##### Manuel "Pinned Tours" Özelliği

Admin filtre kullansa bile (örn. "tüm cruise'lardan en ucuz 6"), bazı tour'ları
**manuel sabitlemek** isteyebilir.  `manual_pinned` array'i bu tour'ları
otomatik filtreye eklenmiş halde döndürür.

Bu hibrit yaklaşım:
- ✅ Esnek (her sayfaya yerleştirilebilir)
- ✅ Mevcut CMS mimarisiyle uyumlu (yeni model gerekmez)
- ✅ Filter + manuel curation birleştirilebilir
- ✅ Multi-tenant doğal (her tenant kendi page'ine ekler)

##### Eski "Vitrin Düzeni" sayfasının karşılığı

Eski sistemde tek sayfa varsa, yeni sistemde:
- Anasayfa: `tour-carousel` + `tour-featured-banner` + `tour-grid` section'ları arkalı önlü
- Cruise listing sayfası: `tour-grid` section'ı + filtre paneli
- Destinasyon detay sayfası: `tour-by-destination` section'ı

Admin Page Builder'a girip section'ları drag-drop yerleştirir.  Phase 4'te
bu section'lar yazılacak.

#### 2.3.b Lock — Karar Verildi ✅

`HomepageShowcase` veya benzeri ayrı model **YOK**.  Vitrin = section system.
Bu, Phase 4 frontend kapsamına dahil.

---

### 2.4 Destinasyonlar 🆕
- **Ne yapar:** Cruise destinasyonları master listesi
  (örn. "Yunan Adaları", "Doğu Akdeniz", "Karayipler", "Bermuda Gemi Turları").
- **Yeni sistem karşılığı:** ⚠️ Kısmi.  Phase 1'de `TourCategory` var (hiyerarşik)
  ama bu generic kategori — destination semantiği yok.
- **Bonus:** Destinasyonlar genellikle frontend'de **filtre + landing page**'ler için
  kullanılır ("Greek Islands cruises" sayfası).

#### 2.4.a Destinasyon Form Yapısı (eski sistem)

Form başlığı: **"Kategori Düzenle"** — eski sistem Destination'ı bağımsız model
değil, generic Kategori modelinin bir varyasyonu olarak kurmuş.

**Üst nav (3 entity link):**
- 🚢 Destinasyonlar — generic destination
- 🚢 **Gemi Destinasyonları** — cruise-spesifik destination (ayrı liste!)
- ⚓ Limanlar — port master

**Form alanları:**

| Alan | Tip | İçerik | Not |
|---|---|---|---|
| **{Destination Adı}** | Text | Destinasyon ismi (örn. "Bermuda Gemi Turları") | Dinamik label |
| 📷(0) | Badge | Bağlı fotoğraf sayısı | Spatie media collection |
| **Kategorisi** | Dropdown | Parent type — burada "DESTİNASYONLAR" seçili | Kategori türü ayraçı |
| **Açıklama** | TinyMCE WYSIWYG | Destinasyon açıklaması | Frontend landing page'de gösterilir |
| **Limanları** | Multi-select | "Liman Seçiniz" — destinasyona bağlı portlar | Destination ↔ Port m2m |
| **Seo Etiketleri** | Text (tag input?) | SEO keywords | Meta keywords |
| **Yayınlanacağı Diller** | Multi-select | Hangi dillerde yayında olsun | Per-language visibility |

#### 2.4.b 🚨 Eski Sistem Mimari Sorunu — Unified "Kategori" Model

Kullanıcı notu:
> "Destinasyon gibi ikinci bir kategori alanı kampanyalar yapmıştım.
>  aynı ekleme yöntemi, ama bunu önerine göre geliştirebiliriz."

Yani eski sistem **tüm taksonomi entity'lerini tek Kategori modelinde** birleştirmiş:

```
Kategori (tek tablo)
  ├── type=Destinations    → Destinasyon (ports m2m, geo data)
  ├── type=ShipDestinations → Gemi Destinasyonu (cruise-spesifik)
  ├── type=Campaigns       → Kampanya (tarih + indirim kuralları)
  └── type=Main            → Ana Kategori (hiyerarşik)
```

**Sorun:** Her tip aslında **farklı domain behavior**'ına sahip:
- **Destinasyon:** port'larla m2m, geo aggregation, landing page
- **Kampanya:** tarih aralığı, discount rule, applied tours, banner
- **Ana Kategori:** sadece hiyerarşi
- **Ship Destination:** cruise-spesifik (Akdeniz, Karayipler) — generic destination'dan farklı?

Tek tabloda tutmak yazılım açısından "polymorphic basitlik" gibi görünür ama
gerçekte her tip kendi domain rule'larına sahip → ya conditional kod patlar, ya
JSON `type_config` ile esnek ama silik bir model çıkar.

#### 2.4.c ✨ Yeni Sistem İçin Önerim — Specialized Models

Önerim **4 ayrı first-class model** + mevcut hiyerarşik `TourCategory`:

```php
// Mevcut (Phase 1)
TourCategory {
  - parent_id (hiyerarşik)
  - slug, sort_order, is_active
  - hasMedia (cover)
  - hasMany TourCategoryTranslation
}
// Kullanım: yapısal hiyerarşi ("Yunan Adaları", "Türkiye Çıkışlı")

// Yeni — Phase 1.5
Destination {
  - slug, sort_order, is_active, is_featured
  - lat, lng (region centroid — harita için)
  - hasMedia (gallery, cover)
  - hasMany DestinationTranslation (name, description, meta_*)
  - belongsToMany Port (via destination_ports pivot)
  - belongsToMany Tour (via tour_destinations pivot)
}
// Kullanım: cruise destinasyonu — "Yunan Adaları" landing page,
// içinde 7 port, 12 cruise tour

Campaign {
  - slug, is_active
  - starts_at, ends_at (kampanya tarih aralığı)
  - discount_type enum (percent / fixed / 2nd_person_free / 1tam1yarim / ...)
  - discount_value (% veya tutar)
  - applies_to enum (all_tours / specific_tours / category / destination)
  - banner_image, banner_text
  - hasMany CampaignTranslation
  - belongsToMany Tour (opsiyonel — applies_to=specific_tours ise)
  - belongsToMany TourDate (per-date kampanya — Tab 4'te gördüğümüz)
}
// Kullanım: "Son Dakika İndirimleri" (28-30 Ekim arası %20),
// "Erken Rezervasyon" (60 gün önceden), "2. Kişi Ücretsiz"

Port {
  - code (TR-CES, GR-MYK), slug
  - lat, lng (kesin koordinat — harita için kritik)
  - country_code
  - hasMedia (gallery, cover)
  - hasMany PortTranslation (name, description)
  - belongsToMany Destination
  - hasMany TourItineraryDay (her itinerary stop bir port'a referans)
}
// Kullanım: master liste, tüm cruise/feribot turlarda yeniden kullanılır

Ship + ShipCompany (Phase 1.5 — zaten gap'te)
```

**Faydalar:**

| Faydası | Açıklama |
|---|---|
| **Domain rules clean** | Campaign'de discount engine, Destination'da port aggregation — birbirine karışmaz |
| **Type-safe relationships** | `$tour->destinations->first()->ports` — JSON cast olmadan |
| **Future-proof** | Yeni alan eklemek için JSON parse + condition yok, sadece migration |
| **Phase 5 entegrasyonu** | Discount engine doğrudan Campaign'e bağlanır (polymorphic değil, direct) |
| **Frontend ISR** | Her entity için ayrı cache tag (`tag:destination:greek-islands`) |

**Trade-off:** Daha fazla model + migration.  Ama bunlar small (~50-100 satır
her biri), refactor maliyeti düşük, runtime overhead yok.

#### 2.4.d "Gemi Destinasyonları" — ✅ Kullanıcı netleştirdi

> **Kullanıcı:** "Gemi destinasyonları ile destinasyonlar farkı yok"

İki menü item aynı tabloyu gösteriyor — sadece UI duplikasyonu, ayrı entity değil.

**Lock:** Tek `Destination` modeli yeterli.  Eski sistemdeki iki menü item'ı
sadece UI shortcut (filtreli görünüm).  Yeni sistemde:

```php
Destination {
  // ... yukarıdaki tüm alanlar ...
  - compatible_tour_types (json array — opsiyonel filter için)
    // ['cruise', 'package', 'daily', 'ferry']
}
```

`compatible_tour_types` boş olursa hepsi için uygun — admin spesifik
istiyorsa ayar verir.  "Bermuda Gemi Turları" sadece cruise için,
"Kapadokya" sadece package için.

Admin nav'da tek "Destinasyonlar" linki yeter; isterseniz "Cruise için
filtreli" gibi filter chips eklenebilir ama ayrı menü gereksiz.

---

### 2.5 Kampanyalar 🆕 — ✨ Kullanıcı önerime açık

> **Kullanıcı:** "Kampanyalar için yazmıştım, şuan destinasyonlarla aynı yöntemi
> kullanıyorum eski projede, önerine açığım"

- **Ne yapar:** Promosyon yönetimi — "Erken rezervasyon %20 indirim", "2. Kişi Ücretsiz",
  "Son Dakika İndirimleri" vs.
- **Eski sistem davranışı:** Destinasyonlarla aynı unified `Kategori` modelinde tutuluyor —
  her şey "kategori" olarak yaşıyor (Destinasyon, Kampanya, ana kategori hepsi tek tablo)

#### 2.5.a ✨ Önerim — Ayrı `Campaign` Modeli (Kategori'den ayır)

Kampanyaların **kategori değil** olduğunu net olarak çıkardığım yerler:
- Tarih aralığı var (`starts_at`, `ends_at`)
- İndirim mekanikleri var (% / sabit / 2. kişi ücretsiz / 1 tam 1 yarım)
- Tour + TourDate ile m2m bağlanır (per-date kampanya — Tab 4'te gördük)
- Frontend'de banner görseli + CTA text gösterilir
- Phase 4 frontend section: `tour-by-campaign`

Bunlar generic taksonomide barınmaz.  Önerim:

```php
Campaign {
  - slug, name
  - is_active
  - starts_at, ends_at                    // kampanya tarih aralığı
  - discount_type enum (
      percent / fixed_amount /
      second_person_free /                // "2. Kişi Ücretsiz"
      one_full_one_half /                 // "1 Tam 1 Yarım"
      free_child / extra_night            // diğer
    )
  - discount_value (decimal — % veya tutar)
  - currency (fixed_amount için)
  - applies_to enum (
      all_tours / specific_tours /
      category / destination / cabin_category
    )
  - banner_image_url (string nullable)
  - banner_alt_text (string nullable)
  - cta_text (string nullable — "Hemen Rezerve Et")
  - sort_order
  - hasMany CampaignTranslation (name, description, banner_text, terms_conditions)
  - belongsToMany Tour (via campaign_tours pivot — applies_to='specific_tours')
  - belongsToMany TourDate (via campaign_tour_dates pivot — Tab 4 per-date)
  - belongsToMany TourCategory (applies_to='category')
  - belongsToMany Destination (applies_to='destination')
  - hasMedia (banner)
}
```

##### Discount Engine Entegrasyonu (Phase 5)

`QuoteService` cabin price hesabından sonra aktif kampanyaları kontrol eder:

```php
function applyCampaignDiscounts(Booking $booking, Quote $quote): Quote
{
    $activeCampaigns = Campaign::query()
        ->active()
        ->whereDate('starts_at', '<=', now())
        ->whereDate('ends_at', '>=', now())
        ->matchingTour($booking->tour, $booking->tourDate)
        ->orderByDesc('discount_value')
        ->get();

    foreach ($activeCampaigns as $campaign) {
        $quote = $campaign->apply($quote, $booking);
    }

    return $quote;
}
```

##### Tab 4 Pricing Setup Entegrasyonu

Tour Tab 4'teki **KAMPANYALAR checkbox'ları** (her tarihe atanan kampanyalar)
artık `campaign_tour_dates` pivot'a kayıtlanır.  Frontend booking'de o tarihin
aktif kampanyaları otomatik uygulanır.

#### 2.5.b Kategori vs Kampanya — Net Ayrım

```
Kategori (TourCategory)       →  Static taksonomi, hiyerarşik, yapısal
                                  "Yunan Adaları Cruise'ları"

Destination                    →  Geo-aggregated landing page
                                  Ports ile m2m
                                  "Bermuda Gemi Turları"

Campaign                       →  Time-bound + discount engine
                                  Tour/Date/Category ile m2m
                                  "Erken Rezervasyon Fırsatları (1-31 Mart)"
```

Üçü ayrı model, ayrı admin sayfaları, ayrı domain behavior.

#### 2.5.c Lock — Karar Verildi ✅

Ayrı `Campaign` modeli, Phase 1.5'te şema, Phase 5'te discount engine + admin UI.

---

### 2.6 Limanlar 🆕
- **Ne yapar:** Port master data — "İstanbul Limanı", "Pire", "Mikonos" vs.
- **Yeni sistem karşılığı:** ❌ **YOK**.  Phase 1'de `TourItineraryDay.location`
  ve `port_name` (string column) var — normalize değil.

#### 2.6.a Liman Ekle Form Yapısı (eski sistem)

Form başlığı yine **"Kategori Düzenle"** — Destination gibi Limanlar da generic
Kategori modelinin varyasyonu.  Ama içerik **çok daha zengin** çünkü cruise
müşterileri ziyaret edecekleri portlar hakkında bilgi/görsel görmek isterler.

**Form alanları (sırasıyla):**

| Alan | Tip | İçerik | Yeni sistemde |
|---|---|---|---|
| **{Port Adı}** | Text + 📷(1) | "Oban-İskoçya" — port adı + foto sayısı | `Port.translations.name` + media |
| **Kategorisi** | Dropdown | "Limanlar" (sabit, type discriminator) | ❌ Gerek yok (ayrı Port tablosu) |
| **Bayrak** | Text/Image URL | Ülke bayrak ikonu | ❌ `country_code`'dan türetilir |
| **Ülke** | Text/Dropdown | "Birleşik Krallık" | `Port.country_code` (ISO-3166-1) |
| **Nüfus** 🆕 | Number | "8.100" — port kasabasının nüfusu | `Port.population` (editöryel) |
| **Latitude** | Decimal | "56.4120" — kesin koordinat | `Port.latitude` |
| **Longitude** | Decimal | "-5.4719" — kesin koordinat | `Port.longitude` |
| **Video** 🆕 | Text URL | YouTube/Vimeo embed URL | `Port.video_url` |
| **Harita** | Embedded Leaflet | lat/lng girince map preview | UI only — backend store etmez |
| **Bulunduğu Bölgeler** | Multi-select Destination | Port'un bağlı olduğu destinasyon(lar) | Port ↔ Destination m2m pivot |
| **Kısa Açıklama** | TinyMCE WYSIWYG | Editöryel açıklama (örn. "İskoçya'nın batı kıyısında, Hebrid Adaları'na açılan önemli bir liman kenti...") | `Port.translations.short_description` |

#### 2.6.b 🚨 Çıkardığım Önemli Mimari Detaylar

1. **Port sadece koordinat değil — editorial entity:**
   Eski sistem nüfus + video + ülke + açıklama tutuyor.  Bu, frontend port detay
   sayfasında ("Oban hakkında bilmeniz gerekenler") kullanılıyor.

2. **Embedded harita preview:**
   Lat/lng input'larına yazınca aşağıdaki Leaflet map otomatik update oluyor.
   Admin koordinatın doğruluğunu görsel olarak teyit edebilir.
   **Bonus:** Map üzerinden tıklayıp koordinatı geri-doldurma yapılabiliyor mu?
   Görsel netleşmiyor ama UX iyi olur (Phase 4 önerisi).

3. **Bidirectional Destination ↔ Port:**
   - Destination form'da → "Limanları" multi-select
   - Port form'da → "Bulunduğu Bölgeler" multi-select Destinasyon
   - Aynı m2m pivot table, 2 UI giriş noktası.  Admin hangi taraftan eklerse
     diğeri otomatik görünür.

4. **Country + Bayrak ayrımı:**
   Eski sistemde ayrı alanlar — büyük ihtimal bayrak = manuel image URL.
   **Yeni sistemde:** `country_code` (ISO-3166-1) tek alan yeter — flag image
   `https://flagcdn.com/{country_code}.svg` ile CDN'den gelir.  Admin elle
   bayrak URL'i girmek zorunda kalmaz.

#### 2.6.c ✨ Yeni Sistem Önerisi — Port Modeli

```php
Port {
  // Identity
  - slug
  - country_code (ISO-3166-1 alpha-2)  // 'GB', 'TR', 'GR'
  - is_active

  // Geo (kritik — auto-map için)
  - latitude (decimal 10,7)
  - longitude (decimal 10,7)

  // Editorial
  - population (unsignedInteger, nullable)
  - video_url (string, nullable)
  - timezone (string, nullable — opsiyonel ek)

  // Media
  - hasMedia (gallery — port fotoğrafları)
  - hasMedia (cover — kapak fotoğrafı, singleFile)

  // Relationships
  - hasMany PortTranslation (name, short_description, long_description, meta_*)
  - belongsToMany Destination (via destination_ports pivot)
  - hasMany TourItineraryDay (FK from itinerary stops)
  - hasMany TourCabin... wait no, ships visit ports, not cabins
}

PortTranslation {
  - port_id, language_id
  - name (örn. TR: "Pire", EN: "Piraeus")
  - short_description
  - long_description
  - meta_title, meta_description
}
```

**Faydalar (eski sistem üzerinde):**
- Port detay sayfası SEO'lu landing page olur (Pire cruise turları)
- Multi-language port adları (TR: Mikonos, EN: Mykonos — tutarlılık)
- Country flag CDN'den (manuel field yok)
- Migration sırasında: eski `port_name` string'lerinden bulk Port creation
  + dedup (Mikonos/Mykonos birleştirilir)

---

### 2.7 Gemi Firmaları 🆕
- **Ne yapar:** Cruise operatörlerinin master listesi
  (Costa, MSC, Royal Caribbean, Celebrity vs.)
- **Yeni sistem karşılığı:** ❌ **YOK**.  Phase 1'de `Tour.type_config.ship_name`
  serbest text alan var — operator/firma kavramı yok.

#### 2.7.a 3-Katmanlı Hiyerarşi — Tam Yapı Netleşti

Eski sistem ekran görüntülerinden çıkardığım net mimari:

```
🏢 Gemi Firmaları (ShipCompany)        — 41 firma kayıtlı (örn. MSC, Azamara, Cunard)
  │
  ├──🚢 Ships (Gemi)                   — her firmanın gemileri (örn. Azamara'nın 4 gemisi)
  │   │                                  Onward, Quest, Pursuit, Journey
  │   │
  │   └──⊞ Cabins (Gemi Kabinleri)    — her geminin kabin envanteri (örn. MSC EURIBIA'nın 5 kabin tipi)
  │       │                             İç Kabin, Dış kabin, Balkonlu, Suit, Okyanus Manzaralı
  │
  └──⊞ Cabin Groups (firma düzeyi)    — opsiyonel: firma-level standart kabin template'leri
                                          (firma genelinde tekrar eden kabin kategorileri)
```

#### 2.7.b Liste Görünümleri (3 Görselin Detayları)

##### Gemi Firmaları (41 kayıt)

| Sütun | İçerik |
|---|---|
| ⤧ | Drag handle (sıralama) |
| ID | 2449, 2339, 2475... |
| Logo | Firma logosu (Azamara, MSC, Cunard, Silversea vs.) |
| Ad | "Azamara Club Cruises", "MSC Cruises" |
| ✅ ✅ | 2 adet aktif flag (büyük ihtimal: web active + print active, ya da TR/EN active) |
| 🚢 (mavi) | → Firma'nın **gemilerine** git |
| ⊞ (mavi) | → Firma'nın **kabin gruplarına** git (firma-level templates) |
| ✏️ | Düzenle |
| ☐ | Bulk select |

##### Gemi İşlemleri (Azamara, 4 ship)

Breadcrumb: `Gemi Firmaları › Azamara Club Cruises › Yeni Ekle`

| Sütun | İçerik |
|---|---|
| ID | 2611, 2503, 2509, 2510 |
| Foto | Gemi fotoğrafı + "(1)" badge (kaç foto) |
| Ad | Azamara Onward, Azamara Quest, Azamara Pursuit, Azamara Journey |
| ✅ ✅ | 2 aktif flag |
| ⊞ 🔴 / 🟢 | Kabin yönetim butonu — kırmızı + tooltip "Kabin Eklenmemiş" / yeşil (var) |
| ✏️ | Düzenle |

**🔑 Çok güzel UX detayı:** Kırmızı buton + "Kabin Eklenmemiş" tooltip — admin
hangi gemilerin daha kurulması gerektiğini bir bakışta görüyor.  Yeni sistem
admin paneline aynı pattern eklenebilir (data-completeness indicators).

##### Gemi Kabin İşlemleri (MSC EURIBIA, 5 kabin tipi)

Breadcrumb: `Gemi Firmaları › MSC Cruises › MSC EURIBIA › Yeni Ekle`

| Sütun | İçerik |
|---|---|
| ID | 8547, 8546, 8545, 8544, 8543 |
| Foto badge | "(0)" — bu kabinlere foto eklenmemiş |
| Ad | Okyanus Manzaralı Kabin, Suit Kabin, Balkonlu Kabin, Dış kabin, İç Kabin |
| ✅ | 1 aktif flag |
| ✏️ | Düzenle |

**Cabin tipi listesinin hiyerarşisi (içeriden dışarıya, fiyat artışı sırası):**
1. İç Kabin (en ucuz)
2. Dış kabin
3. Balkonlu Kabin
4. Suit Kabin
5. Okyanus Manzaralı Kabin (genelde en pahalı premium)

Bu sıralama eski sistemde drag-and-drop ile yapılmış, yeni sistemde sort_order
ile aynı UX korunmalı.

#### 2.7.c ✨ Yeni Sistem Önerisi — Phase 1.5 Refactor Tasarımı

Mevcut `TourCabinType` modeli per-tour kabin tipi tutuyor (her cruise'da
yeniden kabin tanımlanır).  Eski sistemin ship-master pattern'ine geçeceğiz:

```php
ShipCompany {
  - slug, name, website, country
  - description, contact_info
  - hasMedia (logo, gallery)
  - hasMany Ship
  - hasMany CabinTemplate     // opsiyonel — firma-level standart kabinler
}

Ship {
  - ship_company_id (FK)
  - slug
  - star_rating (tinyint 1-7)  // Tab 1'deki "6*" gerekçesi!
  - imo_number (gerçek IMO ID, opsiyonel)
  - year_built (kuruluş yılı)
  - passenger_capacity (toplam yolcu kapasitesi)
  - crew_count
  - tonnage
  - length_m, beam_m
  - hasMedia (gallery, deck_plans, cover)
  - hasMany ShipTranslation (name, description)
  - hasMany ShipCabin
}

ShipCabin {
  // PER-SHIP master — gemideki gerçek kabin envanteri
  - ship_id (FK)
  - cabin_template_id (FK to CabinTemplate, nullable)
  - code (örn. "BAL", "STE")
  - hasMany ShipCabinTranslation (name, description)
  - beds_per_cabin (örn. 2)
  - cabin_count_on_ship (gemide bu tipten kaç kabin var)
  - deck (örn. "Deck 8 — Lido")
  - sort_order, is_active
  - hasMedia (cabin_photos, floor_plan)
}

// Tour-level OVERRIDE (sadece price override için)
TourCabinPrice {
  - tour_id (FK)
  - ship_cabin_id (FK to ShipCabin)
  - price_modifier (signed int, kuruş)  // bu tour için ek/düşük
  - is_available (bu tour'da bu kabin satıştan mı?)
}

// Mevcut Tour modelinde değişiklik:
Tour {
  // ... mevcut alanlar ...
  - ship_id (FK to Ship)  // ✨ yeni — cruise için zorunlu
}
```

#### 2.7.d Migration Stratejisi (Phase 1 → Phase 1.5)

Phase 1'de `TourCabinType` modeli zaten yazılı (commit `c7fada9`).
Refactor planı:

| Eski (Phase 1) | Yeni (Phase 1.5) | Migration aksiyonu |
|---|---|---|
| `tour_cabin_types` tablosu | `ship_cabins` (ship_id ile) + `tour_cabin_prices` (tour-level override) | İki ayrı yeni tablo, eskisi soft-deprecate |
| `Tour.type_config.ship_name` (string) | `Tour.ship_id` (FK) | data migration |
| Per-tour cabin tanımları | Bir kez Ship'e kabin gir, tüm o gemi turları kullanır | DRY — admin işi 10x kolaylaşır |

**Faydaları:**
1. **DRY** — MSC EURIBIA'nın 5 kabini bir kez tanımlanır, her MSC EURIBIA cruise'unda kullanılır
2. **Tutarlılık** — Aynı geminin kabini farklı tour'larda farklı yazılamaz
3. **Marketing** — "Bu gemide neler var?" landing page'i mümkün
4. **Operasyon** — Gemiyi kapsayan bilgi (deck plan, kapasite, IMO) tek yerde

#### 2.7.e Cabin Ekle/Düzenle Formu — 2 Tab Wizard

Cabin formu **2 tab** içeriyor:
1. **Genel Kabin Bilgileri** (General Cabin Info)
2. **Kabin Resimleri** (Cabin Images)

##### Tab 1 — Genel Kabin Bilgileri

| Alan | Tip | İçerik / Davranış | Yeni sistem |
|---|---|---|---|
| **Kabin Adı** | Text (zorunlu) | "Junior Suite 2 Yatak" | `ShipCabin.name` (translation) |
| **Kabin Kategorisi** 🆕 | Dropdown (zorunlu) | "Kabin Kategorisini Seçiniz" — global tip ayraç (Inside/Outside/Balcony/Suite) | `CabinCategory` (yeni master) |
| **Gemi İsmi** | Dropdown (zorunlu) | "MSC Fantasia" — kabin hangi gemiye ait | `ShipCabin.ship_id` |
| **Güverte Adı** | Text (zorunlu) | "Lido Deck", placeholder "strkat:" | `ShipCabin.deck_name` |
| **Max Kişi** | Number (zorunlu) | Kabinin alabileceği max yolcu sayısı | `ShipCabin.max_capacity` |
| **Kişi Başı Fiyat** | Number (zorunlu) | Kabinin baz kişi başı fiyatı | `ShipCabin.base_price_per_person` |
| **Description** | TinyMCE WYSIWYG (zorunlu) | Kabin açıklaması (donanım, manzara, m² vs.) | `ShipCabin.description` (translation) |

##### Tab 2 — Kabin Resimleri

Standart galeri yönetimi (drag-drop, sort, isim/açıklama).  Spatie media-library
ile aynı pattern (Tab 8 tour formundaki gibi).

#### 2.7.e.1 ✅ CabinCategory — Kullanıcı Tarafından Netleştirildi

> **Kullanıcı:** "Kabin kategorisi master form yok, İç, Dış, Okyanus manzaralı,
> balkonlu ve suit kategorileri var, önerine açığım"

##### Lock — Karar Verildi ✅

5 sabit kategori var ama yine de **master tablo** olarak kuracağız (hard-coded enum yerine).
Sebepler:
- Multi-language desteği (TR / EN / ru / ar...)
- Tenant gerektiğinde 6. kategori ekleyebilsin (örn. "Mini Suite", "Family Suite")
- Sort order esnekliği (ucuzdan pahalıya)
- Icon, açıklama, custom property için yer

##### Default Seed (5 standart kategori)

```php
// database/seeders/tenant/CabinCategorySeeder.php (Phase 1.5)
$categories = [
    [
        'slug' => 'inside',
        'sort_order' => 1,
        'icon' => '🛏',
        'translations' => [
            'tr' => ['name' => 'İç Kabin',           'description' => 'Penceresiz, geminin iç kısmında, en ekonomik'],
            'en' => ['name' => 'Inside Cabin',       'description' => 'No window, ship interior, most economical'],
        ],
    ],
    [
        'slug' => 'outside',
        'sort_order' => 2,
        'icon' => '🪟',
        'translations' => [
            'tr' => ['name' => 'Dış Kabin',          'description' => 'Pencereli, deniz manzaralı'],
            'en' => ['name' => 'Outside Cabin',      'description' => 'With window, sea view'],
        ],
    ],
    [
        'slug' => 'ocean_view',
        'sort_order' => 3,
        'icon' => '🌊',
        'translations' => [
            'tr' => ['name' => 'Okyanus Manzaralı',  'description' => 'Geniş pencereli, geniş okyanus manzarası'],
            'en' => ['name' => 'Ocean View',         'description' => 'Large window, wide ocean view'],
        ],
    ],
    [
        'slug' => 'balcony',
        'sort_order' => 4,
        'icon' => '🌅',
        'translations' => [
            'tr' => ['name' => 'Balkonlu Kabin',     'description' => 'Özel balkonlu, açık hava'],
            'en' => ['name' => 'Balcony Cabin',      'description' => 'Private balcony, fresh air'],
        ],
    ],
    [
        'slug' => 'suite',
        'sort_order' => 5,
        'icon' => '✨',
        'translations' => [
            'tr' => ['name' => 'Suite',              'description' => 'Lüks, geniş, oturma alanlı'],
            'en' => ['name' => 'Suite',              'description' => 'Luxury, spacious, with living area'],
        ],
    ],
];
```

##### Admin UI

`tenant:module:install tours` çağrısı bu seed'i çalıştırır → her tenant
default 5 kategori ile başlar.  Admin isterse:
- `/admin/cabin-categories` sayfasından yeni ekleyebilir (örn. "Promo İç Kabin")
- Mevcut kategorinin adını değiştirebilir (örn. "Suite" → "Junior Suite")
- Sort order'ı değiştirebilir
- Bazılarını `is_active = false` ile devre dışı bırakabilir

##### Frontend Kullanımı

- Tour listing'inde cabin tipi filter chip'leri (İç ☐ Dış ☐ Balkon ☑ Suite ☐)
- Tour detay sayfasında kabin selector ikonları
- Filtre URL slug'ı (`/cruise-turlari?cabin=suite`)

---

#### 2.7.f 🔄 Düzeltilmiş Yorum — CabinGroup = Company-Level Cabin Selection

> **Kullanıcı düzeltmesi:**
> "Kabin grubu, gemifırması bazında kabin seçimi için kullanılıyor tur fiyat eklerken.
>  Kabin ekleme gemi bazında kabinleri eklemede kullanılıyor, bunlar da tur eklerken
>  fiyat alanında çıkıyor."

İlk yorumum yanlıştı — CabinGroup paket tur için ayrı bir havuz **değil**.
Doğru yorum:

| Entity | Bağlı olduğu | Rolü |
|---|---|---|
| **Cabin** | ☝️ Her zaman bir Ship'e (cabin.ship_id zorunlu) | Master cabin envanteri — admin "Kabin Ekle" ile **gemi başına** kabin tanımlar |
| **CabinGroup** | ShipCompany | **Filter/seçim mekanizması** — şirket düzeyinde cabin'leri gruplandırıp tour fiyat ekleme adımında toplu seçim sağlar |

##### Gerçek davranış

**1. Cabin ekleme (gemi bazlı master setup):**
```
Admin → Gemi Firması (Azamara) → Ship (MSC EURIBIA) → "Yeni Kabin Ekle"
  Form: İç Kabin, Dış Kabin, Balkonlu, Suite, Okyanus Manzaralı
  Her cabin direkt Ship'e bağlı (cabin.ship_id = MSC EURIBIA)
```

**2. CabinGroup oluşturma (company-level grouping):**
```
Admin → Gemi Firması (Azamara) → "Yeni Kabin Grubu"
  Form: Kabin Grubu Adı = "Paket Tur Kabinleri"
        Gemi Firması = Azamara Cruises
  Sonra: Bu gruba hangi cabin'lerin dahil olduğunu seç
        (Azamara'nın tüm gemilerindeki cabin'lerden alt-küme)
```

**3. Tur fiyat ekleme (Tab 4):**
```
Admin Tab 4'te Yeni Grup Ekle → CabinGroup seç ("Paket Tur Kabinleri")
  Sistem: o gruba ait tüm cabin'leri matrix tablosuna yerleştirir
          Her cabin için person-tier fiyatlar girilir
```

##### Refined Architecture

```
🌐 CabinCategory (GLOBAL)              "İç", "Dış", "Balkonlu", "Suite"...
        ↑ Cabin.cabin_category_id (zorunlu)

🏢 ShipCompany
   ├── 🚢 Ship                  ←─── Cabin.ship_id (zorunlu — her cabin bir geminin)
   │       └── 🛏 Cabin
   │
   └── 📦 CabinGroup            ←─── Cabin'leri m2m bağlar (selection bundle)
           ↑                          Tour pricing setup'ta toplu seçim için
           └── m2m ↔ Cabin (cabin_group_cabin pivot)
```

##### "Paket Tur Kabinleri" örneği — Doğru anlam

"Paket Tur Kabinleri" = Azamara firmasının cabin'lerinden, **paket turlarında
kullanılacak** subset.  Belki şirket "lüks paket turlarımızda sadece Suite ve
Balkonlu Kabin satarız" demek için bu grubu kurar.

Tour pricing setup'ta:
- Admin Yeni Fiyat Grubu Ekle → "Paket Tur Kabinleri" seç
- Sistem o gruba dahil 12 cabin'i matrix'e yerleştirir
- Admin her cabin × person-tier için fiyat girer
- Cabin'leri tek tek seçmekten kurtulur

##### Görseldeki örnek cabin formu — yeniden bakış

Görselde "Kabin Grubu Seç: Paket Tur Kabinleri" alanı yanlış yorumlamama
neden olmuştu.  Aslında ne demek:
- Cabin "Promo İç Kabin" yine bir Ship'e bağlı (form'da gözükmeyen ama altta tutulan)
- "Kabin Grubu Seç" alanı, bu cabin'i "Paket Tur Kabinleri" grubuna **dahil etmek**
  için (m2m pivot kaydı)
- Yani cabin form'unda **2 ilişki** kayıtlanıyor:
  1. Cabin.ship_id (parent, zorunlu)
  2. Cabin ↔ CabinGroup pivot (opsiyonel — bu cabin hangi gruplarda var)

#### 2.7.g Kabin Grubu CRUD Yapısı

##### Kabin Grubu Listesi (Gemi Kabin Grubu İşlemleri)

| Sütun | İçerik |
|---|---|
| ⤧ | Drag handle |
| ID | 322 |
| Ad | "Paket Tur Kabinleri" |
| ✅ | Aktif flag |
| ⊞ (yeşil) | → Bu grubun kabinlerine git |
| ✏️ | Düzenle |

Üst nav: `Gemi Firmaları | Yeni Ekle`

##### Kabin Grubu Ekle Formu

| Alan | Tip | İçerik |
|---|---|---|
| **Kabin Grubu Adı** | Text (zorunlu) | "Paket Tur Kabinleri" |
| **Gemi Firması İsmi** | Dropdown (zorunlu) | Bağlı olduğu ShipCompany — dropdown'da tüm firmalar var |

Çok sade form — sadece ad + firma.  Detay cabin'lerin kendisinde.

#### 2.7.h ✨ Yeni Sistem — Refined Model (Kullanıcı Düzeltmesi Sonrası)

```php
// Master taxonomy (global)
CabinCategory {
  - slug                                  // 'inside', 'outside', 'balcony', 'suite', 'ocean_view'
  - sort_order
  - icon (opsiyonel)
  - hasMany CabinCategoryTranslation
}

// Şirket
ShipCompany {
  - slug, name, logo, website, country
  - hasMany Ship
  - hasMany CabinGroup
}

// Gemi
Ship {
  - ship_company_id (FK)
  - slug, star_rating, imo, year_built, ...
  - hasMany Cabin (cabin.ship_id)
}

// Cabin — her zaman bir Ship'e bağlı
Cabin {
  - ship_id (FK, ZORUNLU)              ← her cabin bir gemiye ait
  - cabin_category_id (FK, ZORUNLU)    ← global tip
  - brand_subcategory (string, opt — "MSC Yacht Club" vs)
  - deck_name (string, opt — "Deck 7 — Lido")
  - max_capacity (smallint)
  - base_price_per_person (int, kuruş)
  - hasMany CabinTranslation (name, description)
  - hasMedia (gallery)
  - belongsToMany CabinGroup (via cabin_group_cabin pivot)
}

// Cabin Group — company-level selection bundle
CabinGroup {
  - ship_company_id (FK, ZORUNLU)
  - slug, name
  - sort_order, is_active
  - hasMany CabinGroupTranslation (name, description)
  - belongsToMany Cabin (via cabin_group_cabin pivot)
}

// Pivot
cabin_group_cabin {
  - cabin_group_id (FK)
  - cabin_id (FK)
  - sort_order
}

// Tour pricing — Tab 4 person-tier matrix
TourPriceGroup {
  - tour_id (FK)
  - cabin_group_id (FK, opsiyonel — pricing bu group'taki cabin'lere uygulanır)
  - name ("Yaz 2026"), description
  - min_persons, capacity_quota, is_active
  - belongsToMany TourDate (m2m)
  - hasMany TourCabinPrice
}

TourCabinPrice {
  - tour_price_group_id (FK)
  - cabin_id (FK)
  - calculation_method enum (doublex2 / person_sum / flat_cabin)
  - price_single, price_double, price_triple, price_quad
  - price_child, price_baby
  - child_age_min, child_age_max, baby_age_min, baby_age_max
}
```

##### Tour-Cabin akışı (eski sistem davranışını yansıtır)

```
1. Admin Tour ekler → Ship seçer (örn. MSC EURIBIA)
2. Tab 4 (Tarih & Fiyatlar) → "Yeni Fiyat Grubu Ekle"
   - Tarihleri seç (multi-date)
   - Opsiyon Adı, min kişi, kontenjan, kampanya
   - CabinGroup seç ("Paket Tur Kabinleri" — MSC firmasının bir grubu)
     → sistem o gruba dahil ve MSC EURIBIA'ya ait cabin'leri matrix'e yerleştirir
   - Her cabin × person-tier için fiyat gir
3. Save → TourPriceGroup + TourCabinPrice'lar oluşur
```

**Önemli detay:** Cabin filter çift-katmanlı:
- CabinGroup'a dahil cabin'ler ∩ Tour.ship'in cabin'leri

CabinGroup birden çok geminin cabin'lerini kapsayabilir ama bu tour MSC EURIBIA'ya
ait olduğu için sadece o geminin cabin'leri görünür.

#### 2.7.i Migration Stratejisi (Phase 1 → 1.5)

| Eski (Phase 1) | Yeni (Phase 1.5) |
|---|---|
| `tour_cabin_types` (per-tour) | `cabins` (per-ship master) + `cabin_groups` + `cabin_group_cabin` pivot |
| `Tour.type_config.ship_name` (string) | `Tour.ship_id` (FK to Ship, cruise için zorunlu) |
| `TourPriceTier` (flat 2D) | `TourPriceGroup` + `TourCabinPrice` (5-boyutlu matrix) |
| Cabin tipi her tour'da yeniden | Cabin master Ship'e bağlı, CabinGroup ile filtrelenir, TourCabinPrice ile fiyatlanır |

#### 2.7.j Gemi Firması Ekle Formu (2 Tab Wizard)

**Tab 1 — Genel Bilgiler:**

| Alan | Tip | İçerik | Yeni sistem |
|---|---|---|---|
| **Firma Adı** | Text (zorunlu) | "MSC Cruises", "Azamara Club Cruises" | `ShipCompany.name` |
| **Tipi** | Text | Firma tipi (Premium / Luxury / Mainstream / Expedition?) | `ShipCompany.company_type` |
| **İşletmeci** | Text | Operatör/parent company (örn. "Norwegian Cruise Line Holdings") | `ShipCompany.operator` |
| **Kuruluş Yılı** | Text | Firma kuruluş yılı | `ShipCompany.founded_year` |
| **Merkez** | Text | Merkez ofis lokasyonu (Cenova, Miami vs.) | `ShipCompany.headquarters` |
| **Kabin Hem Firma Hem Gemi Datası** 🆕 | Checkbox | Cabin'lerin hem company-level (CabinGroup) hem ship-level (Cabin) olarak tutulup tutulmayacağı | `ShipCompany.uses_cabin_groups` (bool flag) |
| **Web Sitesi** | Text URL | Resmi web sitesi | `ShipCompany.website` |
| **Açıklama** | TinyMCE WYSIWYG | Firma tanıtım metni | `ShipCompany.description` (translation) |

**Tab 2 — Seo Bilgileri** (gösterilmedi ama standart meta_title/desc bekleniyor)

##### 🔑 Önemli Mimari İpucu — "Kabin Hem Firma Hem Gemi Datası" Flag

Bu checkbox **CabinGroup'un opt-in olduğunu** gösteriyor:

| Flag durumu | Davranış |
|---|---|
| ☐ Kapalı (default) | Sadece ship-level Cabin'ler kullanılır.  CabinGroup'lar yok sayılır.  Tour pricing setup direkt Ship.cabins listesini gösterir. |
| ☑ Açık | Hem ship-level Cabin'ler hem company-level CabinGroup'lar aktif.  Tour pricing setup CabinGroup seçtirir, o gruptaki cabin'lerin Ship'le kesişimini matrix'e yerleştirir. |

**Yeni sistem implementasyonu:**
```php
ShipCompany {
  // ... mevcut alanlar ...
  - uses_cabin_groups (bool, default false)
}
```

Bu flag ile küçük firmalar (1-2 gemi) basit ship-level cabin yönetimine sahip,
büyük firmalar (10+ gemi) CabinGroup ile gruplandırma kullanabilir.

#### 2.7.k Gemi Ekle Formu (4 Tab Wizard)

Ship formu **4 tab** içeriyor — cruise'un en zengin master entity'si:

| # | Tab | İçerik |
|---|---|---|
| 1 | **Genel Bilgiler** | Temel info + firma + ülke |
| 2 | **Gemi Profili** | Teknik özellikler + facilities (İmkanlar) |
| 3 | **Seo Bilgileri** | Meta title/desc |
| 4 | **Gemi Katları** | Deck plans (resim olarak yüklenir) |

##### Tab 1 — Genel Bilgiler

| Alan | Tip | İçerik | Yeni sistem |
|---|---|---|---|
| **Gemi Adı** | Text (zorunlu) | "MSC EURIBIA", "Azamara Onward" | `Ship.name` |
| **Gemi Firması** | Dropdown (zorunlu) | "Select Cruise Line Name" | `Ship.ship_company_id` |
| **Acentası** | Text | Geminin TR temsilcisi / acentası | `Ship.local_agent` (nullable) |
| **Ülke** | Text (zorunlu) | Geminin bayrak ülkesi | `Ship.flag_country_code` |
| **Açıklama** | TinyMCE WYSIWYG (zorunlu) | Gemi tanıtım metni | `ShipTranslation.description` |

##### Tab 2 — Gemi Profili (Teknik Özellikler)

| Alan | Tip | İçerik | Yeni sistem |
|---|---|---|---|
| **Yapım Yılı** | Number | Gemi yapım yılı | `Ship.year_built` |
| **Yolcu Sayısı** | Number | Max yolcu kapasitesi | `Ship.passenger_capacity` |
| **Mürettabat Sayısı** | Number | Mürettebat sayısı | `Ship.crew_count` |
| **Kat Sayısı** | Number | Güverte/kat sayısı | `Ship.deck_count` |
| **Ağırlığı** | Number | Tonaj (GRT) | `Ship.tonnage` |
| **Uzunluğu** | Number | Metre | `Ship.length_m` |
| **Genişliği** | Number | Metre (beam) | `Ship.beam_m` |
| **Hızı** | Number | Knots | `Ship.cruise_speed_knots` |
| **İmkanlar** | Multi-checkbox | Facilities list (aşağıda) | `Ship.facilities` (JSON array) |

##### Tab 2 — İmkanlar Listesi (gözlemlenen 15 kalem)

```
□ Bahis Tesisleri        □ Bar Özellikleri
□ Bowling Tesisleri      □ Casino Tesisleri
□ Diğer İmkanlar         □ Doğal Senaryo Olanakları
□ Eğlence Tesisleri      □ Havuz Olanakları
□ Klima                  □ Kütüphane Olanakları
□ Sinema Tesisleri       □ Spor Tesisleri
□ WiFi Tesisatı          □ Yemek Odası
□ Yemek Olanakları
```

**Yeni sistem önerisi:** `Ship.facilities` JSON column (`['casino', 'spa', 'pool', 'wifi', ...]`) +
master `ShipFacility` enum config (`config/ship_facilities.php`).  Frontend tour detay
sayfasında ikon grid olarak gösterilir (🎰 Casino, 💧 Havuz, 📶 WiFi vs.).

##### Tab 4 — Gemi Katları (Deck Plans)

Kullanıcı notu: "Gemi katları resim olarak ekleniyor."

Yapılı veri yok — sadece deck plan görselleri Spatie media-library ile yüklenir.

**Yeni sistem:**
```php
Ship::addMediaCollection('deck_plans')
    ->acceptsMimeTypes(['image/jpeg', 'image/png', 'application/pdf']);
```

Cruise müşterileri kabin seçerken deck plan görmek isteyebilir.  Frontend tour
detay sayfasında galerinin yanında ayrı bir "Deck Plans" sekmesi gösterilir.

##### 🚨 Eksik: Star Rating

Tour ismi conventionu "6* Oceania" diyor ama Ship formunda **star_rating
alanı yok**.  İki olasılık:
1. Yıldız sayısı admin convention (tour adına manuel yazılıyor, structured değil)
2. Tab 3 (Seo Bilgileri) içinde olabilir (görsel paylaşılmadı)

**Önerim:** Yeni sistemde `Ship.star_rating` (tinyint 1-7) eklemek mantıklı —
filtreleme + frontend için.  Eski sistem manuel convention'a güveniyor ama
bu kırılgan; structured alan daha iyi.

#### 2.7.l Ship Modelinin Tam Yeniden Yazımı

Tüm tab'lardan toplanan field'larla revize Ship model:

```php
Ship {
  // Identity
  - ship_company_id (FK, zorunlu)
  - slug
  - star_rating (tinyint 1-7, nullable — bizim eklediğimiz)
  - local_agent (string, nullable — Acentası)
  - flag_country_code (ISO-3166-1)

  // Technical specs (Tab 2)
  - year_built (smallint, nullable)
  - passenger_capacity (int, nullable)
  - crew_count (int, nullable)
  - deck_count (smallint, nullable)
  - tonnage (int, nullable — GRT)
  - length_m (decimal 6,2 nullable)
  - beam_m (decimal 6,2 nullable)
  - cruise_speed_knots (decimal 4,1 nullable)

  // Facilities (Tab 2)
  - facilities (json — array of slug strings)

  // Optional industry IDs
  - imo_number (string, nullable — gerçek IMO ID)

  // Status
  - is_active (bool)

  // Relations
  - hasMany ShipTranslation (name, description, meta_*)
  - hasMany Cabin
  - hasMedia (gallery, deck_plans, cover)
}
```

#### 2.7.m ShipCompany Modelinin Tam Yeniden Yazımı

```php
ShipCompany {
  - slug
  - name
  - company_type (string, nullable — "Premium", "Luxury", "Mainstream")
  - operator (string, nullable — parent operator)
  - founded_year (smallint, nullable)
  - headquarters (string, nullable)
  - website (string, nullable)
  - is_active (bool)

  // Kritik flag
  - uses_cabin_groups (bool, default false)

  // Relations
  - hasMany ShipCompanyTranslation (description, meta_*)
  - hasMany Ship
  - hasMany CabinGroup (sadece uses_cabin_groups=true ise anlamlı)
  - hasMedia (logo)
}
```

---

```php
CabinCategory {
  - slug
  - sort_order
  - icon (opsiyonel — frontend ikonu)
  - hasMany CabinCategoryTranslation (name, description)
  // Örnekler:
  // inside       — İç Kabin
  // outside      — Dış Kabin
  // balcony      — Balkonlu Kabin
  // suite        — Suite
  // ocean_view   — Okyanus Manzaralı
}

ShipCabin {
  - ship_id (FK)
  - cabin_category_id (FK to CabinCategory)
  - brand_subcategory (string nullable — "MSC Yacht Club", "NCL Haven")
  - deck_name (string — "Lido Deck 7")
  - max_capacity (smallint)
  - base_price_per_person (int, kuruş)
  - sort_order, is_active
  - hasMedia (gallery)
  - hasMany ShipCabinTranslation (name, description)
}
```

#### 2.7.g Cabin Pricing — Phase 1.5 Refactor Tamlığı

Cabin formunda **"Kişi Başı Fiyat"** alanı var — bu kabinin BAZ fiyatı.  Ama
Tab 4 (tour formu) içinde gördüğümüz matrix:
- Per-cabin × per-tarih × per-person-tier (Single/Double/Triple/Quad/Child/Baby)
- 3 hesaplama yöntemi (Doublex2, Kişi Toplama, Tek Kabin)

**Soru:** Bu iki katman nasıl etkileşir?

**Çıkardığım davranış:**
1. **`ShipCabin.base_price_per_person`** = sistemin **default fiyatı**
   (tüm tour'larda kullanılır, override edilmezse)
2. **Tab 4 person-tier matrix** = tour-spesifik override
   (özel tarih/sezon için farklı fiyat)
3. Quote hesabı: önce tour-level matrix var mı bak, yoksa Ship'in base fiyatından düş

**Yeni sistem önerisi (revize):**

```php
ShipCabin {
  // ... yukarıdaki alanlar ...
  - base_price_per_person (kuruş)  // default — tour override yoksa kullanılır
}

TourCabinPrice {
  - tour_id (FK)
  - ship_cabin_id (FK)
  - price_group_id (FK to TourPriceGroup, nullable — tarih bazlı)
  - calculation_method enum (doublex2 / person_sum / flat_cabin)
  // Person-tier matrix:
  - price_single
  - price_double
  - price_triple
  - price_quad
  - price_child
  - price_baby
  // Yaş aralıkları (Tab 4'te gördüğümüz)
  - child_age_min, child_age_max
  - baby_age_min, baby_age_max
  - is_active
}
```

Quote akışı:
1. Tour + TourDate + Cabin seçildi
2. `TourCabinPrice` var mı (bu tour + cabin için)?
   - Var → matrix kullan + calculation method ile total hesapla
   - Yok → `ShipCabin.base_price_per_person × passenger_count` (sade fallback)

Bu, eski sistemin **tüm cruise pricing felsefesini** doğru karşılıyor.

---

#### 2.7.h Admin UX — Drill-Down Navigation

Eski sistem breadcrumb pattern'ı çok temiz:
```
Gemi Firmaları › Azamara Club Cruises › MSC EURIBIA › Yeni Ekle
```

Yeni sistemde aynı drill-down nav korunmalı.  Routes önerisi:
```
/admin/ship-companies                       — firma listesi
/admin/ship-companies/{company}/ships       — firmanın gemileri
/admin/ship-companies/{company}/ships/{ship}/cabins   — geminin kabinleri
/admin/ship-companies/{company}/cabin-templates       — firma kabin template'leri
```

Eski sistem benzer route hiyerarşisi muhtemelen — UI nav button'ları drilldown
yapıyor (🚢 → ships, ⊞ → cabin groups).

---

### 2.8 Kabin Grupları 🆕
- **Ne yapar:** Master cabin layout — bir geminin sahip olduğu standart kabin
  tiplerini tanımlar (örn. "Costa Mediterranea Inside Cabin", "Royal Caribbean
  Junior Suite").  Her cruise satışında bunları yeniden yazmaktan kurtarır.
- **Yeni sistem karşılığı:** ⚠️ **Farklı paradigma**.  Phase 1'de `TourCabinType`
  per-tour kabin tipi var (her cruise için ayrı kabin tanımlanır).  Eski sistem
  per-ship master data — bir kez tanımla, her sefer yeniden kullan.
- **Phase entegrasyonu:** İki katmanlı yapıya geçmek mantıklı:
  - `Ship` modeli → `cabin_groups` (master cabin templates)
  - `Tour` → `Ship` FK + `TourCabinType` override (price + capacity override)
- **Mimari karar gerekiyor:** Bu, Phase 1 şemasında **büyük bir refactor**
  anlamına gelir.  Phase 4'e geçmeden Phase 1.5 olarak yapılması daha sağlıklı —
  yoksa Phase 4 frontend cabin selector'u sonra yine yeniden yazılır.

### 2.9 Cruise Ayarları 🆕
- **Ne yapar:** Per-tour-type vertical configuration.  Cruise için tanımlanan
  ayarlar Paket / Günlük / Feribot tipleri için de ayrı ayrı yapılır.

#### 2.9.a "Tur Tanımlamaları Düzenle" Form Yapısı

Eski sistem her tur tipini ayrı bir **TourTypeSetting** entity olarak kurmuş.
Görsellerden çıkardığım tam alan listesi:

##### Üst Bilgi Alanları

| Alan | Tip | Örnek değer | Yeni sistem |
|---|---|---|---|
| **Gemi Turları Adı** | Text | "Gemi Turları" — tour type display label | `TourTypeSetting.label` |
| **Seo Link Ayracı** | Text | "tur-detay" — URL path prefix | `TourTypeSetting.url_slug` |
| **Filtre Özellikleri** | Multi-tag | "Tur Seçenekleri" — listeleme'de filter olarak hangi taksonomi gözükür | `TourTypeSetting.filter_taxonomies` (json) |
| **Destinasyon / Kategori** | Multi-tag | "DESTİNASYONLAR" + "KAMPANYALAR" — bağlı taksonomi tipleri | `TourTypeSetting.linked_taxonomies` (json) |
| **Bölge / Liman** | Multi-tag | "Limanlar" — port master eşleştirmesi | `TourTypeSetting.port_master_enabled` (bool) |
| **Listeleme Sayfası Eşleştirme** | Multi-tag (Page) | "Gemi Turları" — frontend listing page mapping | `TourTypeSetting.listing_page_id` (FK Page) |
| **Detay Sayfası Eşleştirme** | Multi-tag (Page) | "Tur Detay" — frontend detail page template | `TourTypeSetting.detail_page_id` (FK Page) |
| **Yolcu Bilgileri Alınsın mı** | Dropdown | "Evet" / "Hayır" — booking'de yolcu detay toplansın mı | `TourTypeSetting.collect_passenger_info` (bool) |

##### 🆕 Dynamic Form Builder — Rezervasyon Yolcu Bilgi Alanları

Admin **booking form'una eklenecek custom yolcu alanlarını** burada tanımlar.
Repeatable row yapısı:

| Sütun | İçerik |
|---|---|
| **Başlık** | Field label ("TCKN", "Pasaport No", "Yemek tercihi") |
| **Option Value** | Select/radio için seçenekler (`vegetarian,vegan,halal,kosher`) |
| **Col Str** | Column structure dropdown — "Tek Satır" (full width), muhtemelen yarım/üçte vs. opsiyonları |
| Yeni Ekle / Sil | Add/remove field rows |

##### 🆕 Dynamic Form Builder — Rezervasyon Form Ek Alanları

Aynı yapı ama **bu sefer non-passenger ek alanlar için** (genel rezervasyon
notları, özel istekler, transfer ihtiyacı vs.).

##### 🆕 Kısmi Ödeme Opsiyonları

Multi-select tag dropdown:
- **Full Ödeme** (default — tam ödeme)
- **%10 Ödeme** (kapora seçeneği)
- **%25 Ödeme**
- **%50 Ödeme**

Bu seçilenler customer checkout'unda **müşteriye sunulur** — müşteri istediğini
seçer.  Phase 0'da locked decision A (saf abonelik) ile uyumlu — para her zaman
acentenin Iyzico hesabına gider, kapora-bakiye akışı acente UX'i.

##### Genel Açıklama

TinyMCE WYSIWYG — bu tour type için genel açıklama/notlar.

#### 2.9.b 🚨 Önemli Çıkarım — Per-Tour-Type Settings Pattern

Kullanıcı notu: "Cruise Ayarları bu şekilde eski projede ekleyelim"

Yani aynı form **her tour tipi için** ayrı yapılıyor:
- Gemi Turları (Cruise) ayarları
- Paket Turlar ayarları
- Günlük Turlar ayarları
- Feribot ayarları

Her birinin kendi:
- Frontend page mapping'i
- Booking form alanları
- Ödeme opsiyonları
- Filtre konfigürasyonu

##### Yeni Sistem Önerisi

```php
TourTypeSetting {
  - tenant_id (zorunlu — tenant DB)
  - tour_type enum (cruise / package / daily / ferry / ...)
  - label (display name)
  - url_slug (örn. "cruise-turlari")

  // Page mapping (frontend pages)
  - listing_page_id (FK to Page, nullable)
  - detail_page_id (FK to Page, nullable)

  // Taxonomy linkage
  - filter_taxonomies (json — hangi taksonomiler listing filter'da)
  - linked_taxonomies (json — hangi taksonomi tipleri kullanılabilir)
  - port_master_enabled (bool — port master kullanılır mı)

  // Booking form
  - collect_passenger_info (bool)
  - passenger_form_fields (json array of FormField)
  - additional_form_fields (json array of FormField)

  // Payment
  - allowed_payment_options (json array — ['full', 'percent_10', 'percent_25', 'percent_50'])

  // Editorial
  - description (text)

  - timestamps
  unique(tenant_id, tour_type)
}

// FormField shape (json array içinde)
FormField {
  - id (uuid — frontend için stable identifier)
  - label
  - type enum (text / textarea / select / radio / checkbox / date / file)
  - option_values (array — select/radio için)
  - column_structure enum (full_row / half / third / quarter)
  - is_required (bool)
  - default_value (string nullable)
  - placeholder (string nullable)
  - sort_order
}
```

##### Admin UX

Sidebar → "Cruise Ayarları" altında ayrı sayfalar:
- `/admin/tour-settings/cruise`
- `/admin/tour-settings/package`
- `/admin/tour-settings/daily`
- `/admin/tour-settings/ferry`

Veya tek sayfada tab'lı görünüm:
```
[Gemi Turları] [Paket Turlar] [Günlük Turlar] [Feribot]
```

#### 2.9.c Mevcut Sistemle Karşılaştırma

**Phase 1'de bizim model:**
- `Tour.type` enum (cruise/package/daily) — tour-level
- Booking form sabit alanlar — config dosyasında
- Ödeme: `payments.reservation_hold_minutes` config — tenant-level değil

**Eski sistem:**
- Her tour tipi için ayrı settings + dynamic form builder
- Admin runtime'da form alanlarını ekler/çıkarır
- Per-vertical payment options

**Yeni sistem entegrasyonu:**
- `TourTypeSetting` modeli + admin UI (Phase 3.5)
- Frontend booking wizard her tour tipi için bu config'i okur (Phase 4)
- Payment options checkout adımında müşteri seçer (Phase 4)

#### 2.9.d Faydalar

1. **Esnek booking form:** Bir cruise acentesi "tekne sertifikası" alanı isterken
   bir paket tur acentesi "vize başvurusu yapılacak mı" alanı ister.  Sabit form
   yetersiz; dynamic builder çözer.

2. **Per-vertical pricing UX:** Cruise için %25 kapora standart, günlük tur için
   100% ödeme.  Her tipte farklı opsiyon seti.

3. **Page mapping:** Frontend tarafında "Gemi Turları" landing page'i ayrı,
   "Paket Turlar" landing'i ayrı.  Admin Page CRUD'unda yapılı sayfaları
   tour type'a bağlar.

4. **Taxonomy esnekliği:** Bir tour tipi sadece destinations+ports kullanırken,
   diğeri kampanyalar da kullanır.  Admin seçer.

---

---

## 3) Günlük / Paket Turlar ve Feribot — ✅ Kullanıcı Netleştirdi

> **Kullanıcı:** "Günlük/paket turlar ve feribot ekleme alanları aynı aslında,
> sadece günlük tur ve feribot ta rota takvimi yok ve fiyatlama farklı.
> Araç kiralama formu gereksiz."

### 3.a Tab Yapısı — Tour Type Bazında Görünürlük

Aynı 9-tab wizard kullanılıyor ama Tab 2 (Rota Takvimi) bazı tip'lerde gizli:

| Tab | Cruise | Paket | Günlük | Feribot |
|---|---|---|---|---|
| 1. Genel Bilgiler | ✅ | ✅ | ✅ | ✅ |
| 2. **Rota Takvimi** | ✅ | ✅ | ❌ Yok | ❌ Yok |
| 3. Genel Fiyatlar | ✅ | ✅ | ✅ | ✅ |
| 4. Tarih & Fiyatlar | ✅ | ✅ | ✅ | ✅ |
| 5. Açıklamalar | ✅ | ✅ | ✅ | ✅ |
| 6. SEO Bilgileri | ✅ | ✅ | ✅ | ✅ |
| 7. Harita | ✅ | ✅ | ✅ | ✅ |
| 8. Resimler | ✅ | ✅ | ✅ | ✅ |
| 9. Yorumlar | ✅ | ✅ | ✅ | ✅ |

**Kural:**
- **Cruise + Paket:** Multi-day, multi-stop → Rota Takvimi gerekli
- **Günlük:** Tek günde tek lokasyon, gün gün rota yok
- **Feribot:** Tek yön A→B (veya gidiş-dönüş), gün gün rota yok

**Yeni sistem implementasyonu:**
```php
// TourType enum'a method ekle
enum TourType: string {
    case Cruise  = 'cruise';
    case Package = 'package';
    case Daily   = 'daily';
    case Ferry   = 'ferry';      // ← Yeni eklenecek

    public function hasMultiDayItinerary(): bool {
        return in_array($this, [self::Cruise, self::Package]);
    }
}

// Admin form'da
@if($tour->type->hasMultiDayItinerary())
    {{-- Tab 2 Rota Takvimi göster --}}
@endif
```

### 3.b ✅ TourType::Ferry — Mevcut Polymorphic'e Eklenir

Feribot ayrı modül **değil**, mevcut `TourType` enum'una 4. değer:

```php
enum TourType: string {
    case Cruise  = 'cruise';
    case Package = 'package';
    case Daily   = 'daily';
    case Ferry   = 'ferry';
}
```

Feribot Cruise'a benzer (gemi + rota) ama:
- Tek yön sefer (Bodrum → Kos)
- Kabin yerine genelde **koltuk** (kabin opsiyonel)
- Araç dahil edilebilir (vehicle ticket)
- Gidiş-dönüş (return ticket) opsiyonu

Bu farklar `Tour.type_config` JSON ile karşılanır:
```json
{
  "from_port": "bodrum",
  "to_port": "kos",
  "vehicle_allowed": true,
  "return_ticket_available": true,
  "seat_classes": ["economy", "business"]
}
```

CabinGroup pattern'i feribot için **opsiyonel** — `Ship.cabins`'te koltuk
sınıfları tanımlanır.  Cabin Categories'e ileride "Economy Seat", "Business Seat"
eklenebilir (master seed update).

---

## 4) Tab 1 Dropdown'larının Cevapları — ✅ Görseller Geldi

Önceki Tab 1 analizinde sorduğum sorular cevap buldu.

### 4.a Fiyatlandırma Türü — 3 Değer

| Değer | Davranış | Tipik kullanım |
|---|---|---|
| **Kişibaşı Fiyat** (2 Yetişkin + 1 Çocuk v.b) | Her yolcu kendi tier'ı (single/double/3rd/4th/child/baby) ile fiyatlanır | Cruise (Tab 4'teki full matrix) |
| **Kişibaşı Grup Fiyatı** (2 Yetişkin + 1 Çocuk v.b) | Aynı yapı ama grup üyelerine indirimli per-person rate | Grup rezervasyonu (10+ kişi) |
| **Rezervasyon Başına Fiyat** (Gruplar v.b) | Tek flat fiyat, kişi sayısından bağımsız (charter, paket grup) | Günlük tur özel kiralama, charter cruise |

**Yeni sistem:**
```php
enum PricingMode: string {
    case PerPerson      = 'per_person';
    case PerPersonGroup = 'per_person_group';
    case PerReservation = 'per_reservation';
}

Tour {
  - pricing_mode (PricingMode enum)
  // ...
}
```

##### TourCabinPrice Matrix'i Pricing Mode'a göre

- **per_person:** Full 6-tier matrix (single/double/triple/quad/child/baby) + 3 calc methods
- **per_person_group:** Single column "group_rate_per_person" + group min/max
- **per_reservation:** Single column "flat_reservation_price" — kabin/tarih bazlı tek rakam

Frontend booking wizard pricing_mode'a göre quote hesabı yapar.

### 4.b Satış Durumu — 5 Değer (Araç Kiralama hariç)

| Değer | Frontend CTA | Behavior |
|---|---|---|
| **Satışta Ödemeli** | "Hemen Rezerve Et" | Tam online booking flow (Iyzico 3DS) |
| **Satışta Ödemesiz** | "Rezervasyon Talebi" | Booking oluşur ama ödeme yok — operatör arar |
| **Teklif Al Formu (Konaklamalı)** | "Teklif Al" | Quote request form + konaklama alanları |
| **Teklif Al Formu 2 (Konaklamasız)** | "Teklif Al" | Quote request form, konaklama yok |
| **Sadece Bilgi** | (CTA yok) | Tur sadece içerik olarak gösterilir, booking/quote yok |

**Yeni sistem:**
```php
enum SalesStatus: string {
    case LivePayment             = 'live_payment';            // Satışta Ödemeli
    case LiveContact             = 'live_contact';            // Satışta Ödemesiz
    case QuoteWithAccommodation  = 'quote_with_accommodation';// Teklif Al (Konaklamalı)
    case QuoteWithoutAccommodation = 'quote_without_accommodation'; // Teklif Al 2
    case InfoOnly                = 'info_only';               // Sadece Bilgi
}

Tour {
  - sales_status (SalesStatus enum, default 'live_payment')
}
```

#### Frontend Booking Wizard Davranışı

```php
// apps/frontend/.../tour-detail/CTAButton.tsx
switch (tour.sales_status) {
  case 'live_payment':
    return <Button onClick={openBookingWizard}>Hemen Rezerve Et</Button>;
  case 'live_contact':
    return <Button onClick={openContactReservationForm}>Rezervasyon Talebi</Button>;
  case 'quote_with_accommodation':
    return <Button onClick={() => openQuoteForm({ withAccommodation: true })}>Teklif Al</Button>;
  case 'quote_without_accommodation':
    return <Button onClick={() => openQuoteForm({ withAccommodation: false })}>Teklif Al</Button>;
  case 'info_only':
    return null;  // No CTA
}
```

Bu, eski sistemdeki **çoklu satış akışlarını** tek bir `Tour` modeli üzerinden
yönetir.  Her tour kendi sales_status'ünü seçer, frontend buna göre davranır.

### 4.c Mimari Karar — Önceki Tab 1 Soruları Cevaplandı

| Önceki sorum | Cevap |
|---|---|
| Satış Durumu enum değerleri? | ✅ 5 değer (Live Payment / Live Contact / Quote w/ Acc / Quote w/o Acc / Info Only) |
| Fiyatlandırma Türü enum değerleri? | ✅ 3 değer (Per Person / Per Person Group / Per Reservation) |
| Araç Kiralama Formu? | ❌ Kullanıcı: "gereksiz" → atlanır |

Locked Decisions tablosuna ekleniyor.

---

## 5) Gap Özeti — Yeni Sistemde Eksikler

Cruise Menu'den çıkardığım eksiklikler önem sırasıyla:

| Özellik | Önem | Mevcut karşılık | Phase önerisi |
|---|---|---|---|
| Limanlar (master data) | 🔴 Yüksek | port_name string | 1.5 / 4 (frontend için kritik) |
| Gemi Firmaları (master) | 🔴 Yüksek | type_config.ship_name | 1.5 (Ship FK için) |
| Kabin Grupları (master) | 🔴 Yüksek | per-tour TourCabinType | 1.5 (büyük refactor) |
| **Tur Kopyala** | 🔴 **Yüksek** | YOK | **3.5** (admin UX sık kullanır) |
| **Stok Kodu (SKU)** | 🔴 **Yüksek** | YOK | 1.5 (operasyon kullanır) |
| **Toplu fiyat güncelleme** | 🔴 **Yüksek** | YOK | 3.5 (kampanyalarda kritik) |
| **Tur Seçenekleri tag'leri** | 🔴 **Yüksek** | YOK | 1.5 (üçüncü taksonomi) |
| **Satış Durumu enum** | 🔴 **Yüksek** | YOK | 1.5 (publish'ten ayrı) |
| **Fiyatlandırma Türü enum** | 🔴 **Yüksek** | Sabit per-passenger | 1.5 / 5 |
| **Info-only extras** (vize/vergi vb.) | 🔴 **Yüksek** | Tek tip TourExtra | 1.5 (`info_only` enum değeri) |
| **Info-only extras master data** | 🟡 Orta | YOK | 1.5 (tenant_info_extras) |
| **İndirimli Başlangıç Fiyatı** | 🟡 Orta | YOK | 5 (kupon engine ile birleşir) |
| **Tur Programı disclaimer (boilerplate)** | 🟢 Düşük | YOK | 3.5 (tenant default + per-tour) |
| **Tur Çıkış Şehri (origin port)** | 🟡 Orta | YOK | 1.5 (Port master ile birlikte) |
| **Benzer Turlar (cross-sell)** | 🟡 Orta | YOK | 4 (frontend curation) |
| **Program Gün Şablonu (multi-stop/day)** | 🔴 **Yüksek** | Tek-stop/day | 1.5 (itinerary refactor) |
| **Konaklama field (per-stop)** | 🟡 Orta | YOK | 1.5 (TourItineraryDay alanı) |
| **TourPriceGroup** (named, multi-date) | 🔴 **Çok Yüksek** | YOK | 1.5 (pricing core) |
| **Person-tier matrix (6 sütun)** | 🔴 **Çok Yüksek** | 2-boyutlu tier | 1.5 (PriceTier refactor) |
| **Hesaplama Yöntemi** (3 değer) | 🔴 **Çok Yüksek** | YOK | 1.5 (calculation enum) |
| **Per-room child/baby age range** | 🟡 Orta | YOK | 1.5 |
| **Güverte / Kat (deck/floor)** | 🟡 Orta | YOK | 1.5 (Ship CabinGroup) |
| **Per-date kampanya checkbox'ları** | 🔴 **Yüksek** | YOK | 1.5 (TourDate ↔ Campaign m2m) |
| **Multi-date bulk price entry** | 🔴 **Yüksek** | Tek-tek tarih | 3.5 (admin UX) |
| **Fiyat Grubu metadata** (min kişi, yetişkin önceliği, kontenjan) | 🟡 Orta | YOK | 1.5 |
| **Fiyat Kopyala (inter-tour)** | 🔴 **Yüksek** | YOK | 3.5 (PricingReplicator) |
| **Fiyat Kopyala (date-to-date)** | 🔴 **Yüksek** | YOK | 3.5 (PricingReplicator) |
| **Tarihleri Kopyala (bulk date copy)** | 🟡 Orta | YOK | 3.5 (TourDateReplicator) |
| **TourContentSection** (esnek N-bölüm) | 🟡 Orta | Yapılı 3 alan | 3.5 (hybrid model) |
| **Voucher Alanı** (per-tour voucher metni) | 🟡 Orta | YOK | 3.5 (`is_voucher` flag) |
| **Tab 7: İki nokta tipi** (meeting/visit) | 🟡 Orta | YOK | 1.5 |
| **Tab 7: Auto-map from route** | 🟡 Orta | YOK | 4 (port koordinatları lazım) |
| **Tab 7: Leaflet/OSM integration** | 🟢 Düşük | YOK | 4 (frontend) |
| **Tab 7: Geocoding search (admin)** | 🟢 Düşük | YOK | 4 (Nominatim) |
| **Tab 8: Ship.gallery media collection** | 🟡 Orta | YOK | 1.5 (Ship modeli ile) |
| **Tab 8: Port.gallery media collection** | 🟡 Orta | YOK | 1.5 (Port modeli ile) |
| **Tab 8: Image template selector (Ship/Port/Custom)** | 🟡 Orta | YOK | 4 (frontend aggregator) |
| **Tab 8: Per-image name/description/cover/visibility** | 🟢 Düşük | YOK | 3.5 |
| **Tab 8: Multi-language image metadata** | 🟢 Düşük | YOK | 3.5 (custom_properties) |
| **Uçaklı paket (Tour.includes_flight)** | 🟡 Orta | YOK | 1.5 (flag + JSON) |
| **Tab 9: Tour review moderation (admin)** | 🟡 Orta | YOK | 3.5 |
| **Tab 9: Tour review submit (frontend)** | 🟡 Orta | YOK | 4 |
| **Tab 9: Manuel review ekleme (admin)** | 🟢 Düşük | YOK | 3.5 |
| **Destination master model** (port m2m + geo) | 🔴 **Yüksek** | YOK | 1.5 |
| **Campaign master model** (date + discount) | 🔴 **Yüksek** | YOK | 1.5 |
| **Destination ↔ Port pivot** | 🔴 **Yüksek** | YOK | 1.5 |
| **Tour ↔ Destination pivot** | 🟡 Orta | YOK | 1.5 |
| **`compatible_tour_types` array** (Destination filter) | 🟡 Orta | YOK | 1.5 |
| **Per-language publish flag** (Yayınlanacağı Diller) | 🟡 Orta | YOK | 1.5 (translations'a `is_published`) |
| **Port.population** (editöryel) | 🟢 Düşük | YOK | 1.5 |
| **Port.video_url** (YouTube embed) | 🟢 Düşük | YOK | 1.5 |
| **Port embedded map preview** (admin form) | 🟢 Düşük | YOK | 3.5 (Leaflet widget) |
| **Country flag from country_code CDN** | 🟢 Düşük | YOK | 1.5 (flagcdn.com convention) |
| **ShipCompany model** (master) | 🔴 **Çok Yüksek** | YOK | 1.5 |
| **Ship model** (per-ship master) | 🔴 **Çok Yüksek** | YOK (type_config.ship_name string) | 1.5 |
| **ShipCabin model** (per-ship inventory) | 🔴 **Çok Yüksek** | TourCabinType (per-tour) | 1.5 — refactor |
| **TourCabinPrice override** (tour-level fiyat) | 🔴 **Çok Yüksek** | TourCabinType.price_modifier | 1.5 — yeniden |
| **Tour.ship_id FK** (cruise zorunlu) | 🔴 **Çok Yüksek** | YOK | 1.5 |
| **Ship.star_rating** (1-7) | 🔴 Yüksek | YOK | 1.5 |
| **Ship.imo / capacity / year_built / tonnage** | 🟡 Orta | YOK | 1.5 |
| **Drill-down admin nav** (breadcrumb) | 🟡 Orta | Düz nav | 3.5 |
| **"Kabin Eklenmemiş" data-completeness indicator** | 🟢 Düşük | YOK | 3.5 |
| **CabinCategory global master** (Inside/Outside/Balcony/Suite) | 🔴 Yüksek | YOK | 1.5 |
| **CabinGroup** (company-level cabin selection bundle) | 🔴 Yüksek | YOK | 1.5 |
| **Cabin ↔ CabinGroup m2m** (pivot for selection) | 🔴 Yüksek | YOK | 1.5 |
| **TourPriceGroup.cabin_group_id** (pricing setup için filter) | 🔴 Yüksek | YOK | 1.5 |
| **ShipCompany.uses_cabin_groups flag** (opt-in CabinGroup) | 🟡 Orta | YOK | 1.5 |
| **ShipCompany metadata** (company_type, operator, founded_year, HQ) | 🟡 Orta | YOK | 1.5 |
| **Ship teknik özellikleri** (year_built, passenger_capacity, crew, deck_count, tonnage, dimensions, speed) | 🟡 Orta | YOK | 1.5 |
| **Ship.facilities JSON** (15+ checkbox: casino, pool, wifi, ...) | 🟡 Orta | YOK | 1.5 |
| **Ship.local_agent** (TR temsilci) | 🟢 Düşük | YOK | 1.5 |
| **Ship.flag_country_code** | 🟢 Düşük | YOK | 1.5 |
| **Ship.star_rating** (1-7) | 🟡 Orta | YOK | 1.5 (yeni eklediğimiz, eski sistemde yok) |
| **Ship deck_plans media collection** | 🟢 Düşük | YOK | 1.5 (Spatie) |
| **4-tab Ship wizard form** | 🟢 Düşük | YOK | 3.5 |
| **2-tab ShipCompany wizard form** | 🟢 Düşük | YOK | 3.5 |
| **TourTypeSetting per-tour-type** (label, URL, page mapping) | 🔴 Yüksek | YOK (config only) | 3.5 (model + UI) |
| **Dynamic form builder** (passenger custom fields) | 🔴 Yüksek | YOK | 3.5 (JSON-driven form) |
| **Dynamic form builder** (additional booking fields) | 🟡 Orta | YOK | 3.5 |
| **Kısmi Ödeme opsiyonları** (10/25/50/100%) | 🔴 Yüksek | Sabit %100 | 3.5 + 4 (UX + backend) |
| **Page mapping** (listing/detail per tour type) | 🟡 Orta | YOK | 3.5 (FK to Page) |
| **Per-tour-type filter taxonomies** | 🟡 Orta | Sabit | 3.5 |
| **collect_passenger_info toggle** | 🟢 Düşük | Sabit (zorunlu) | 3.5 |
| **Cabin.brand_subcategory** (MSC Yacht Club vs) | 🟢 Düşük | YOK | 1.5 (string field) |
| **Cabin.base_price_per_person** (default fallback) | 🔴 Yüksek | YOK | 1.5 |
| **TourCabinPrice matrix** (calc_method + 6-tier prices + yaş aralığı) | 🔴 **Çok Yüksek** | YOK | 1.5 (pricing core) |
| **2-tab Cabin wizard** (Bilgi + Resimler) | 🟢 Düşük | YOK | 3.5 (admin UX) |
| **9-tab wizard form** | 🟡 Orta | Tek-sayfa form | 3.5 / 5 (UX) |
| **Öne Çıkan Başlıklar (6 slot)** | 🟡 Orta | `highlights` text | 3.5 (structured field) |
| **Şerit Yazısı (ribbon)** | 🟢 Düşük | YOK | 4 (frontend touch) |
| **WYSIWYG editör (Kısa Açıklama + Özet)** | 🟡 Orta | Plain text | 3.5 |
| **Gezi Süresi (sayı+birim)** | 🟢 Düşük | YOK (computed) | 3.5 (explicit alan) |
| **Tab 7: Tur-level Harita** | 🟡 Orta | YOK | 4 (frontend) |
| **Tab 9: Tur-level Yorumlar** | 🟡 Orta | YOK | 5 |
| **Süresi Geçen tur filtresi** | 🟡 Orta | YOK | 3.5 (filter only, schema değişmez) |
| **Bölge / Liman / Destinasyon hiyerarşisi** | 🔴 **Yüksek** | port_name string | 1.5 (master data) |
| **Tur Seçeneği** ❓ | ⏳ Belirsiz | YOK | Anlamı netleşince |
| **Gemi yıldız sayısı** | 🟡 Orta | YOK | 1.5 (Ship modelinin alanı) |
| **Liste fotoğraf sayısı** | 🟢 Düşük | YOK (Spatie var) | 3.5 (1 satır eklenir) |
| **Cascade arama (tur tipi → ...)** | 🟡 Orta | Düz filtre | 3.5 (UX iyileştirme) |
| **Sıralama seçenekleri** | 🟢 Düşük | Sabit sort | 3.5 (5 satır kod) |
| Destinasyonlar | 🟡 Orta | TourCategory (generic) | 4 (frontend filtreleme) |
| Kampanyalar | 🟡 Orta | conditions_json kuralları | 5 (zaten planlı, kapsam netleşti) |
| Vitrin Düzeni (showcase) | 🟡 Orta | is_featured flag | 4 (frontend curation tool) |
| **Auto-title template** | 🟢 Düşük | Serbest text | 3.5 / 5 (Ship+Dest+Date'den üretim) |
| Cruise Ayarları (tenant) | 🟢 Düşük | dağınık config | 5 / 6 |
| Feribot vertical | ❓ Belirsiz | Yok | Görsel sonrası karar |

---

## 6) Sonraki Adımlar (bu doc için)

1. ⏳ **Günlük Ve Paket Turlar menüsü** görseli paylaşılınca §3 doldurulacak
2. ⏳ **Feribot Menu** görseli paylaşılınca §4 doldurulacak
3. ⏳ **Frontend görselleri** (eğer varsa) — vitrin düzeni, tur detay sayfası vs.
4. ⏳ **Booking akışı** (eğer varsa) — eski sistemin checkout adımları
5. ⏳ **Admin form alanları** (eğer varsa) — bir cruise eklerken hangi alanlar var

Hepsi geldiğinde **`docs/legacy-features-integration-plan.md`** dosyasına Phase 1.5 +
Phase 4 revize + Phase 5 revize için somut bir entegrasyon planı yazılacak.
