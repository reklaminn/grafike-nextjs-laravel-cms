# Tour Wizard — 9-Tab Derin Gap Analizi & Geliştirme Planı

> Kaynak: `docs/legacy-tour-system-features.md` (eski sistem ekran analizi)
> Karşılaştırma: mevcut implementasyon (Phase 1 → 1.5.f, commit `0f7b267` itibarıyla)
> Tarih: 2026-05-28

Bu doküman her tab için **(a) mevcut durum**, **(b) eski sistem spec'i**,
**(c) açıklar**, **(d) öneri + faz** sunar. Sonda **önceliklendirilmiş yol haritası** var.

Öncelik etiketleri:
- 🔴 **P0** — Çekirdek işlev, eski sistemde var, bizde yok/eksik. Yapılmalı.
- 🟡 **P1** — Önemli UX/içerik, kısa vadede değer katar.
- 🟢 **P2** — İyileştirme/nice-to-have, ertelenebilir.

Faz etiketleri: **1.5.x** (admin tamamlama) · **3.5** (admin zenginleştirme) · **4** (frontend) · **5** (gelişmiş)

---

## Özet Skor Tablosu

| Tab | Ad | Mevcut Durum | Eşleşme | Kalan iş ağırlığı |
|---|---|---|---|---|
| 1 | Genel Bilgiler | Çoğu alan var | 🟢 %80 | Orta (kampanya, 6-slot, badge, süre) |
| 2 | Rota Takvimi | Tam editör (1.5.f) | 🟢 %90 | Düşük (meeting/visit tipi) |
| 3 | Genel Fiyatlar | Kısmi | 🟡 %50 | Orta (info-only ekstra, disclaimer) |
| 4 | Tarih & Fiyatlar | Yeniden kuruldu | 🟢 %85 | Orta (fiyat/tarih kopyala) |
| 5 | Açıklamalar | Yapılı alanlar | 🟡 %55 | Orta (esnek section + WYSIWYG) |
| 6 | SEO | Tam | 🟢 %90 | Düşük (canonical, robots) |
| 7 | Harita | Sadece destinasyon | 🔴 %20 | Yüksek (Leaflet + auto-route) |
| 8 | Resimler | Temel upload | 🔴 %35 | Yüksek (şablon + meta + sıralama) |
| 9 | Yorumlar | Placeholder | 🔴 %5 | Orta (moderasyon UI) |

---

## Tab 1 — Genel Bilgiler

### Mevcut durum
- ✅ Tip (zorunlu, boş placeholder), Başlık + alt başlık (en üstte, dil bazlı), Slug (auto-üretim), SKU, Durum
- ✅ Primary kategori + secondary kategoriler (m2m), Gemi (ship_id), Pazarlama etiketleri (TourTag m2m)
- ✅ pricing_mode + sales_status enum, para birimi dropdown, base_price, kapasite, is_featured
- ✅ includes_flight + flight_info (havayolu/kalkış/varış/kod)

### Açıklar (eski sisteme göre)
| Eksik | Öncelik | Faz | Not |
|---|---|---|---|
| **Kampanya Kategorisi** (3. taksonomi) | 🔴 P0 | 5 (veya 1.5.g) | TourTag `tag_type` enum ile çözülebilir; ya da ayrı Campaign model. Şu an marketing tag ile karışık. |
| **Gezi Süresi** (sayı + birim: Gece/Gün/Saat) | 🟡 P1 | 1.5.g | `Tour.duration_value` + `duration_unit` enum. Frontend kart + filtre. Şu an starts/ends'ten türetiliyor. |
| **Öne Çıkan Başlıklar (6 sabit slot)** | 🟡 P1 | 1.5.g | Şu an `highlights` serbest textarea. 6 yapılı slot tasarımcı-dostu (kart ikonları). `tour_translations.highlight_1..6` veya JSON. |
| **Şerit Yazısı (badge/ribbon)** | 🟢 P2 | 3.5 | Kart köşesi "YENİ/İNDİRİM". `Tour.ribbon_text` + renk. |
| **Kısa Açıklama / Özet → WYSIWYG** | 🟡 P1 | 3.5 | Şu an plain textarea. Eski sistemde TinyMCE. (Cross-cutting madde, aşağıda.) |
| **"Seolinki güncelle" checkbox** | 🟢 P2 | — | Auto-slug zaten yapıldı (başlıktan). Checkbox UX şart değil. |

### Öneri
1. **Kampanya taksonomisini netleştir** (P0): `TourTag` modeline `tag_type` enum ekle (`marketing` / `campaign`). Tab 1'de iki ayrı multi-select grup: "Pazarlama Etiketleri" + "Kampanyalar". Tab 4'te tarihe özel kampanya checkbox'ı buradan beslenir. → **Locked karar revize**: Campaign'i ayrı model yerine TourTag+enum ile çözmek daha hızlı.
2. **Gezi Süresi** alanı ekle (P1) — kart/filtre için kritik.
3. 6-slot highlight + ribbon — 1.5.g'de toplu.

---

## Tab 2 — Rota Takvimi

### Mevcut durum (1.5.f'de kuruldu)
- ✅ Dil bazlı editör, Tur Programı (özet), Çıkış Şehri (origin_port_id)
- ✅ Gün/Durak tekrarlı kartlar: gün no + Liman dropdown + başlık + konaklama + varış/kalkış saati + program
- ✅ Multi-stop (aynı gün no tekrarı), delete-recreate sync

### Açıklar
| Eksik | Öncelik | Faz | Not |
|---|---|---|---|
| **Nokta tipi (meeting / visit)** | 🟡 P1 | 1.5.g | Tab 7 haritası için gerekli — buluşma noktası vs ziyaret noktası ayrımı. `TourItineraryStop.point_type` enum. |
| **Program Gün Şablonu pattern (1,1,1,2,3,3)** | 🟢 P2 | — | Artık gereksiz — multi-stop gün no tekrarıyla zaten çözüldü. |
| **Benzer Turlar (cross-sell)** | 🟢 P2 | 3.5 | `tour_related` m2m. Frontend "bunları da beğenebilirsiniz". |
| **Program WYSIWYG (per-stop)** | 🟡 P1 | 3.5 | Şu an plain input. Zengin metin opsiyonel. |

### Öneri
- `point_type` enum'u ekle (P1) — Tab 7 auto-map'in ön koşulu.
- Cross-sell + WYSIWYG ertelenebilir.

---

## Tab 3 — Genel Fiyatlar

### Mevcut durum
- ✅ base_price + currency (Tab 1'de)
- ✅ Fiyat grupları listesi + "Yeni Fiyat Grubu" linki (Tab 4 motoruna)
- ⚠️ `TourExtra` modeli var ama hep online tahsil edilir

### Açıklar
| Eksik | Öncelik | Faz | Not |
|---|---|---|---|
| **İndirimli Başlangıç Fiyatı** | 🟡 P1 | 5 | `Tour.discount_base_price`. Kupon engine ile çakışır — Phase 5. |
| **Info-only Ekstralar** (online tahsil edilMEyen) | 🔴 P0 | 1.5.g | `TourExtra.pricing_mode` enum'a `info_only` ekle. Vize/havaalanı vergisi gibi. `tenant_info_extras` master zaten var (1.5.a). |
| **Ekstra Adı dropdown (master'dan)** | 🟡 P1 | 1.5.g | `tenant_info_extras` master'dan seç — tekrarlanan kalemler. |
| **Genel Açıklama / Fiyat Disclaimer** (WYSIWYG) | 🟡 P1 | 1.5.g | "Doluluğa göre değişebilir…" boilerplate. Tenant-level default + per-tour override. |

### Öneri
1. **TourExtra'ya `info_only` mode** (P0) — frontend "Bilmeniz gerekenler" bloğu, sepete girmez.
2. **Fiyat disclaimer** alanı + tenant-level default (P1).
3. Ekstra master dropdown — `tenant_info_extras` bağla.

---

## Tab 4 — Tarih & Fiyatlar (Cruise Pricing Engine)

### Mevcut durum (1.5.e + rebuild)
- ✅ Departure listesi + tekli + **toplu tarih ekleme** (gece sayısı → bitiş auto)
- ✅ Fiyat Grubu formu (legacy spec'e göre): Opsiyon Adı + metadata + Tarih Seçiniz + **tekrarlı oda satırları** (ODA ADI/kabin dropdown/güverte/fiyat tanımı/hesaplama/yaş/6 fiyat) + çift kaydet
- ✅ 3 hesaplama yöntemi (Doublex2/PersonSum/FlatCabin)

### Açıklar
| Eksik | Öncelik | Faz | Not |
|---|---|---|---|
| **Tarihe özel KAMPANYALAR checkbox** | 🔴 P0 | 1.5.g | Her departure'a farklı kampanya. `tour_date_campaign` m2m (TourTag campaign tipi). |
| **"Tarihleri Kopyala"** (date replication) | 🟡 P1 | 3.5 | Bir turun tarihlerini + grup atamalarını kopyala. `TourDateReplicator`. |
| **"Fiyat Kopyala"** (inter-tour + date-to-date) | 🟡 P1 | 3.5 | İki senaryo: başka turdan al / tarihten tarihe. `PricingReplicator`. |
| **Tarih genişletince inline matrix** | 🟢 P2 | — | Şu an ayrı form sayfası. Eski sistemde `+` ile satır-içi açılıyordu. UX tercihi — ayrı sayfa da kabul. |

### Öneri
1. **Tarihe özel kampanya m2m** (P0) — Tab 1 kampanya taksonomisi netleşince.
2. **PricingReplicator + TourDateReplicator** (P1, 3.5) — eski sistemin 3 kopyalama operasyonundan 2'si (Tur Kopyala zaten var).

---

## Tab 5 — Açıklamalar

### Mevcut durum
- ✅ Dil bazlı: kısa açıklama, detay, öne çıkanlar, önemli bilgi (4 yapılı textarea)
- ⚠️ Tümü plain textarea (WYSIWYG değil)
- ⚠️ Sabit alan listesi (admin custom bölüm ekleyemez)

### Açıklar
| Eksik | Öncelik | Faz | Not |
|---|---|---|---|
| **Esnek sıralanabilir içerik bölümleri** | 🟡 P1 | 3.5 | Eski sistem N adet generic ordered WYSIWYG bölüm. Hibrit: yapılı alanları koru + `TourContentSection` (title+body+sort+is_voucher) ekle. |
| **Voucher Alanı** | 🟢 P2 | 3.5 | `TourContentSection.is_voucher` flag'i ile çözülür. |
| **WYSIWYG editör** | 🟡 P1 | 3.5 | Cross-cutting — aşağıda. |

### Öneri
- **TourContentSection** hibrit model (P1, 3.5): yapılı alanlar + admin'in eklediği custom bölümler. Frontend sırayla render.

---

## Tab 6 — SEO

### Mevcut durum
- ✅ meta_title, meta_description, og_image_url (dil bazlı) + structured_data_json (JSON-LD)

### Açıklar
| Eksik | Öncelik | Faz | Not |
|---|---|---|---|
| **Canonical URL** | 🟢 P2 | 3.5 | `tour_translations.canonical_url`. |
| **robots (noindex/nofollow)** | 🟢 P2 | 3.5 | `Tour.robots` veya per-translation. |
| **Sitemap priority / changefreq** | 🟢 P2 | 4 | Sitemap generator entegrasyonu. |

### Öneri
- SEO neredeyse tam. Canonical + robots küçük eklemeler (3.5). Otomatik JSON-LD üretimi (`searchfit-seo:schema-markup` skill ile) bonus.

---

## Tab 7 — Harita 🔴 (en büyük açık)

### Mevcut durum
- ⚠️ Sadece destinasyon m2m checkbox'ı. Gerçek harita/koordinat YOK.

### Eski sistem
- Leaflet/OSM interaktif harita, sol panel nokta listesi (meeting 🚶 + visit 1,2,3)
- "Rotadan Harita Oluştur" — Tab 2 itinerary port koordinatlarından otomatik polyline
- Geocoding arama (yer ara → koordinat)

### Açıklar
| Eksik | Öncelik | Faz | Not |
|---|---|---|---|
| **Port koordinatları** | ✅ | — | Port master'da lat/lng zaten var (1.5.a). |
| **Nokta tipi (meeting/visit)** | 🟡 P1 | 1.5.g | Tab 2 stop'a `point_type`. |
| **Auto-map from route** | 🔴 P0(frontend) | 4 | Port geo'larından polyline çiz. Admin'de önizleme, frontend'de Leaflet. |
| **Leaflet/OSM component** | 🔴 P0(frontend) | 4 | Next.js tour detay sayfası. |
| **Geocoding arama (Nominatim)** | 🟢 P2 | 4 | Port master zaten koordinat tutuyor — admin'de port ekleme yeterli olabilir. |
| **Tour-level map snapshot (GeoJSON cache)** | 🟢 P2 | 4 | Performans. |

### Öneri
- Admin tarafı: Tab 7 zaten destinasyon seçtiriyor; **harita oluşturma frontend işi** (Phase 4). Admin'e küçük bir "rota önizleme" (port pin'leri statik) eklenebilir (P1, 3.5) ama asıl Leaflet frontend'de.
- Ön koşul: Tab 2 `point_type` enum.

---

## Tab 8 — Resimler 🔴

### Mevcut durum
- ✅ Cover (tek) + gallery (çoklu) + brochure (PDF) upload + sil
- ⚠️ Per-image metadata, sıralama, featured, visibility YOK
- ⚠️ Ship/Port resim şablonu (otomatik çekme) YOK

### Eski sistem
- Resim Şablonu dropdown: "Gemi Resimleri" (Ship master'dan) / "Liman Resimleri" (Port master'dan) / Manuel
- Her resimde: drag-sort + ad + açıklama + ⭐featured + 👁visibility + bulk select
- Multi-language image metadata

### Açıklar
| Eksik | Öncelik | Faz | Not |
|---|---|---|---|
| **Ship.gallery / Port.gallery media** | ✅ kısmen | — | Ship'te cover/gallery/deck_plans var (1.5.d); Port'ta cover/gallery var. ✅ |
| **Resim şablonu seçici (Gemi/Liman/Manuel)** | 🟡 P1 | 4 | Frontend image aggregator — tour.ship.gallery + tour port'larının gallery'si. |
| **Per-image ad + açıklama** | 🟡 P1 | 3.5 | Spatie `custom_properties` JSON. |
| **Featured / cover flag** | 🟡 P1 | 3.5 | Spatie collection veya custom_property. |
| **Visibility toggle** | 🟢 P2 | 3.5 | |
| **Drag-sort sıralama** | 🟡 P1 | 3.5 | Spatie `order_column` + Alpine sortable. |
| **Multi-language metadata** | 🟢 P2 | 3.5 | custom_properties JSON per-lang. |

### Öneri
1. **Drag-sort + per-image ad/açıklama + featured** (P1, 3.5) — Spatie custom_properties + order_column.
2. **Resim şablonu seçici** (P1, 4) — frontend'de Gemi/Liman galerisini otomatik birleştir.

---

## Tab 9 — Yorumlar 🔴

### Mevcut durum
- ⚠️ Placeholder. Generic polymorphic `Review` modeli var ama Tour'a bağlı değil.

### Eski sistem
- Moderasyon kuyruğu (pending → approve/reject)
- Manuel yorum ekleme (telefon/email'le gelen yorumlar, direkt approved)
- Frontend submit akışı

### Açıklar
| Eksik | Öncelik | Faz | Not |
|---|---|---|---|
| **Tour ↔ Review polymorphic bağ** | 🔴 P0 | 3.5 | `Review.reviewable = Tour`. Tour::reviews() scope (approved). |
| **Admin moderasyon UI (Tab 9)** | 🔴 P0 | 3.5 | Pending listesi + onay/red. |
| **Manuel yorum ekleme** | 🟡 P1 | 3.5 | Admin form, direkt approved. |
| **Frontend submit** | 🟡 P1 | 4 | Tur detay sayfası form + POST endpoint. |
| **Ortalama puan aggregation** | 🟡 P1 | 4 | Frontend kart "4.8 ★ (32 yorum)". |
| **Spam koruma + verified booking flag** | 🟢 P2 | 5 | |

### Öneri
- **Mevcut polymorphic Review yeterli** — `TourReview` ayrı model gereksiz.
- 3.5'te: scope + admin moderasyon paneli + manuel ekleme. 4'te: frontend submit + aggregation.

---

## Cross-Cutting (tüm tab'ları kesen) Öneriler

### CC1 — WYSIWYG Editör 🟡 P1 (3.5)
Kısa açıklama, özet, detay, highlights, fiyat disclaimer, content section, per-stop program — hepsi şu an plain textarea. Eski sistem TinyMCE.
**Öneri:** Hafif bir editör (Trix / Quill / TipTap) entegre et, `wysiwyg` Blade component'i yap, ilgili alanlara uygula. Çıktı sanitize edilmiş HTML.

### CC2 — Kampanya Taksonomisi 🔴 P0 (1.5.g)
Tab 1 + Tab 4 kampanya istiyor. **Öneri:** `TourTag.tag_type` enum (`marketing` / `campaign`). Tek tablo, iki amaç. Tab 1'de iki grup, Tab 4'te tarihe özel kampanya checkbox.

### CC3 — Wizard Navigasyon 🟢 P2 (3.5)
Eski sistemde her tab'da "Kaydet Devam Et / İleri / Önizleme". Bizimki tek formda tek "Kaydet". **Öneri:** Sticky bar'a "Kaydet ve İleri" (sonraki tab'a x-data ile geç) + "Önizleme" (frontend draft preview) ekle. Düşük öncelik — mevcut tek-kaydet çalışıyor.

### CC4 — Replication Suite 🟡 P1 (3.5)
Eski sistem 3 kopyalama: Tur Kopyala (✅ var) + Tarih Kopyala + Fiyat Kopyala. **Öneri:** `PricingReplicator` + `TourDateReplicator` servisleri, Tab 4'e "Tarihleri/Fiyatları Kopyala" butonları.

### CC5 — Info-only Extras + Master 🔴 P0 (1.5.g)
Vize/havaalanı vergisi gibi bilgi-amaçlı ekstralar. `TourExtra.pricing_mode = info_only` + `tenant_info_extras` master dropdown (master zaten var).

---

## Önceliklendirilmiş Yol Haritası

### Faz 1.5.g — Admin Tamamlama (P0 ağırlıklı, ~1 hafta)
1. 🔴 CC2: Kampanya taksonomisi (`TourTag.tag_type`) + Tab 1 iki-grup + Tab 4 tarihe özel kampanya m2m
2. 🔴 CC5: `TourExtra.info_only` + `tenant_info_extras` master dropdown (Tab 3)
3. 🔴 Tab 3: Fiyat disclaimer alanı (+ tenant default)
4. 🟡 Tab 1: Gezi Süresi (value+unit) + 6-slot highlights
5. 🟡 Tab 2: `point_type` enum (meeting/visit)

### Faz 3.5 — Admin Zenginleştirme (P1, ~1-2 hafta)
6. 🟡 CC1: WYSIWYG editör (tüm metin alanları)
7. 🟡 CC4: PricingReplicator + TourDateReplicator (Tab 4 kopyalama)
8. 🟡 Tab 5: TourContentSection (esnek bölümler)
9. 🟡 Tab 8: drag-sort + per-image meta + featured (Spatie custom_properties)
10. 🔴 Tab 9: Review polymorphic bağ + moderasyon UI + manuel ekleme
11. 🟢 Tab 1: ribbon/şerit · Tab 6: canonical + robots

### Faz 4 — Frontend (tur detay sayfası)
12. 🔴 Tab 7: Leaflet harita + auto-route polyline
13. 🟡 Tab 8: resim şablonu aggregator (Gemi/Liman galerisi)
14. 🟡 Tab 9: frontend yorum submit + ortalama puan
15. 🟡 Tab 1: gezi süresi + badge frontend gösterimi

### Faz 5 — Gelişmiş
16. İndirimli fiyat + kupon engine (Campaign discount)
17. Spam koruma, verified booking flag
18. Tab 7: GeoJSON cache, geocoding arama

---

## Mevcut Sağlamlık Notları
- ✅ Implicit route-model binding tenant DB fix (commit 449447c) — tüm edit sayfaları çalışıyor
- ✅ Migration drift önleme (createIfNotExists + sync-migrations) — tenant DB tutarlılığı
- ✅ Blade `@context` escape dersi — JSON-LD placeholder'larda `@@` kullan
- ✅ Auto-slug + currency dropdown + type-zorunlu (UX düzeltmeleri)

## Açık Mimari Kararlar (kullanıcı onayı bekleyen)
1. **Kampanya:** TourTag+enum (hızlı) **vs** ayrı Campaign model (kupon engine ile birleşik, Phase 5)? → Öneri: 1.5.g'de TourTag+enum ile başla, Phase 5'te discount-bearing Campaign'e evril.
2. **WYSIWYG:** Hangi editör? Trix (Laravel-dostu, basit) **vs** TipTap (zengin, JS ağır)? → Öneri: Trix (minimal bağımlılık).
3. **Tab 4 matrix UX:** Ayrı sayfa (mevcut) **vs** tarih-içi inline expand (eski sistem)? → Öneri: Mevcut ayrı sayfa kalsın; inline gereksiz karmaşıklık.
