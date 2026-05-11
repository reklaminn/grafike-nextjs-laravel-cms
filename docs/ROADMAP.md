# iraspa-cms — Geliştirme Yol Haritası

> Son güncelleme: 2026-05-10
> Branch: `feat/multi-tenant`
> Repo: [reklaminn/grafike-cms](https://github.com/reklaminn/grafike-cms) (PRIVATE)

Bu doküman, projeye ait master plan'ın tamamlanma durumunu ve kalan iş kalemlerini tek noktadan takip etmek için hazırlanmıştır. Detaylı orijinal plan `/Users/eserulusoy/.claude/plans/projeyi-analiz-et-graceful-nova.md` altındadır.

---

## 📊 Genel Durum

| | Toplam | Bitti | Kalan |
|---|---:|---:|---:|
| FAZ 1 — Multi-Tenant + Docker HA | 9 | 8 | 1 |
| FAZ 2 — Admin Pages Refactor | 5 | 5 | 0 |
| FAZ 3 — AI Altyapısı | 7 | 0 | 7 |
| FAZ 4 — AI Özellikleri | 6 | 0 | 6 |
| **Toplam plan içi** | **27** | **13** | **14** |
| Plan dışı tamamlanan | 5+ | 5+ | — |

---

## ✅ FAZ 1 — Multi-Tenant + Docker HA (8/9)

| # | Madde | Kanıt |
|---|---|---|
| 1.1 | `site_id` + Site model temizliği | `app/Models/Site.php` yok; `database/migrations/2026_04_22_160000_*` içinde "sites table removed" notu |
| 1.2 | stancl/tenancy kurulumu | `composer.json` `"stancl/tenancy": "^3.10"`; `config/tenancy.php`; `app/Models/Tenant.php` (BaseTenant + HasDatabase) |
| 1.3 | Central + tenant migration ayrımı | `database/migrations/tenant/0001…` klasörü; `tenants` ve `domains` ana klasörde |
| 1.4 | Central modellere `$connection = 'central'` | `app/Models/Admin.php:16`, `Theme.php:13`, `Language.php:13`, `SectionTemplate.php:16` |
| 1.5 | Admin tenant yönetimi | `app/Http/Controllers/Admin/TenantController.php`; `resources/views/admin/tenants/`; `routes/admin.php:41` |
| 1.6 | Cache + storage izolasyonu | `config/tenancy.php:40-41` Cache + Filesystem bootstrappers |
| 1.7 | API site_id filtreleri kaldırıldı | `app/Http/Controllers/Api/` grep: 0 sonuç |
| 1.8 | docker-compose 3 app + Traefik HA | `docker-compose.yml` — app1/2/3 + Traefik labels + health check + Redis + MariaDB |
| 1.9 | **Tenant testleri** ❌ | `tests/Feature/` altında tenant test yok |

## ✅ FAZ 2 — Admin Pages Refactor (5/5) 🎉

| # | Madde | Kanıt |
|---|---|---|
| 2.1 | _form.blade.php parçalama | 1957 → 25 satır; `resources/views/admin/pages/_form/` modüler partial'lar; `app/Support/PageEditorData.php` VO |
| 2.2 | Canlı iframe preview | `config/cms.php:16` `frontend_url`; `_form/preview-panel.blade.php` |
| 2.3 | Blok şema doğrulaması | `app/Support/FrontendSectionSchemaValidator.php`; `PageRequest:62-84` `withValidator` |
| 2.4 | Page revisions + dry-run | `tenant/0010_create_page_revisions_table`; `PageRevision.php`; `PageObserver:18-27` |
| 2.5 | Otomatik builder mode | `PageEditorData:72` `activeBuilder()`, `:99` `showBuilderToggle()` |

---

## 🎁 Plan Dışı Tamamlanan Önemli İşler

Bu işler orijinal plan dosyasında yoktu ama yapıldı — değerli ek özellikler:

| Commit | Özellik | Etki |
|---|---|---|
| `eccbe7f` | Member portal + grup tabanlı sayfa/blok kısıtlaması | Üye sistemi, password-protected pages, `tenant/0012_add_group_restriction_to_pages` |
| `0b022a4` | Tenant DB + storage backup sistemi | `resources/views/admin/tenants/_backup-panel.blade.php` — operasyonel açıdan kritik |
| `2da0f81` | LLMs.txt + locale-prefixed URLs + cache busting | AI tarayıcılarına optimize çıktı — diferansiyel özellik |
| `2d11ac9` | Production compose'a Next.js frontend entegrasyonu | ISR cache isolation; production-ready frontend |
| `7268a4b` | Floating language switcher (frontend) | Tüm sayfalarda dil değişim widget'ı |
| `cbeb0c7` | İşletme bilgileri editörü: geo-coords + opening hours UI | Schema.org LocalBusiness JSON-LD; user-friendly editor |
| `2395e03` | JSON-LD generator external script | Blade `@` collision çözümü |
| `482b816` | Cross-DB query fix (activity_log + maintenance) | Tenant connection ↔ central DB tablo erişimi düzeltildi |

---

## ❌ Kalan İş — Yeni Faz Listesi

### FAZ 1.9 — Tenant testleri *(düşük öncelik)*

- 2 farklı tenant oluşturup farklı domain'le test
- app2'yi durdurup app1+app3'ün hizmet vermeye devam ettiğini doğrulama
- Cache izolasyon testi (tenant A değişikliği tenant B'yi etkilememeli)
- Aynı slug'lı sayfa farklı tenant'larda çakışmaması

**Süre:** 1-2 gün

---

### FAZ 3 — AI Altyapısı (7 madde, ~5-7 gün) — **2/7 bitti**

> Foundation. Tüm AI özelliklerinin ön koşulu.

| # | Madde | Detay |
|---|---|---|
| 3.1 ✅ | AI provider abstraction | `app/Services/Ai/` — Anthropic + OpenAI + OpenRouter adapter'ları, `AiManager`, DTOs, Facade, `php artisan ai:ping` komutu; 13 unit test geçiyor. BYOK desteği DTO'da hazır (`apiKeyOverride`). |
| 3.2 | Model rotasyonu | Basit (SEO meta, kısa metin) → Haiku/4o-mini; karmaşık (tam sayfa, tool-use) → Sonnet/4o; her endpoint için varsayılan model |
| 3.3 ✅ | BYOK (Bring Your Own Key) | `Tenant` modelinde encrypted API key storage (Crypt::encryptString); `TenantAiResolver` BYOK + tenant tercihlerini uygular; admin tenant detayında "AI Ayarları" kartı (sağlayıcı seçimi, key girişi, canlı test); routes: `PUT admin/tenants/{t}/ai-settings`, `POST .../test`; 7 unit test. |
| 3.4 | Kota & billing entegrasyonu | Aylık token + istek limiti; `ai_usage` tablosu (central DB); aşımda blok + paid plan'a yönlendirme; plan başına kota config'i |
| 3.5 | Redis prompt caching | Aynı prompt 30 dk içinde tekrar gelirse cache → token harcanmaz |
| 3.6 | Streaming desteği (SSE) | Uzun cevaplarda kullanıcı early stop yapabilir; admin UI'da typewriter efekti |
| 3.7 | AI usage dashboardu | Admin: tüm tenantların kullanımı; Tenant: kendi kotasının grafik gauge'u |

**Bağımlılıklar:** FAZ 1 (tenant izolasyon hazır olmalı)
**Çıktı:** Foundation katmanı hazır. Tek bir yerden tüm AI çağrıları yönetilebilir.

---

### FAZ 4 — AI Özellikleri (6 madde, ~13-18 gün)

> Müşteriye değer üreten katman. Her madde ayrı bir release olabilir.

| Sıra | # | Madde | Süre | Açıklama |
|---|---|---|---|---|
| 1 | 4.1 | **AI SEO meta üretici** | 1-2 gün | Sayfa içeriğinden otomatik title/description/keywords; en hızlı ROI |
| 2 | 4.2 | **AI ile blok içerik düzenleme** | 2-3 gün | Blok seçilir → "daha kısa", "TR→EN", "SEO odaklı", "profesyonel" |
| 3 | 4.3 | **Hazır şablon galerisi** | 2 gün UI + 4-6 saat/şablon | Klinik/Avukat/Restoran/Salon/Emlak — tek tıkla site; AI değil, içerik seed'i |
| 4 | 4.4 | **AI ile sayfa oluşturma** | 3-5 gün | Prompt → mevcut SectionTemplate'lerden uygun sections_json |
| 5 | 4.5 | **AI ile blok şablonu (SectionTemplate) oluşturma** ⭐ | 2-3 gün | Firma için: AI'a tarif → HTML template + schema_json + Tailwind class'lar |
| 6 | 4.6 | **AI çevirmen** | 2-3 gün | Sayfa → hedef dilde yeni sayfa (parent_id ile bağlı) |

**Bağımlılıklar:** FAZ 3
**Karar:** Bu özellikler **paid feature** olarak müşterilere fiyatlandırılacak. BYOK seçeneği ücretsizdir (kullanıcı kendi maliyetini öder).

---

### FAZ 5 — Görsel AI *(sonraya bırakıldı)*

| Madde | Detay |
|---|---|
| 5.1 | Flux schnell entegrasyonu | $0.003/image — varsayılan |
| 5.2 | Flux dev / DALL-E 3 HD opsiyonu | Kalite için upgrade |
| 5.3 | MediaLibrary auto-save + tenant kotası | Üretilen görseller otomatik kayıt |

---

### FAZ 6+ — Diferansiyel Özellikler *(uzun vadeli, rakipte yok)*

- AI Content Audit / SEO koçu
- Doğal dil arama
- AI form alan üreticisi
- Frontend chat agent (ziyaretçi için)
- Üye için 2FA
- Grafana + Plausible Docker kurulumu
- Müşteri self-servis tenant oluşturma
- Reseller / çok-ajans desteği
- Fatura / paket yönetimi

---

## 🎯 Önerilen Başlangıç Sırası

| Adım | Faz | Süre |
|---|---|---|
| 1 | **FAZ 3** (AI altyapı) | 5-7 gün |
| 2 | **FAZ 4.1** (SEO meta üretici — ilk wow) | 1-2 gün |
| 3 | **FAZ 4.2** (blok içerik düzenleme) | 2-3 gün |
| 4 | **FAZ 4.3** (hazır şablon galerisi) | 2 gün UI + içerik |
| 5 | **FAZ 4.4** (AI sayfa oluşturma) | 3-5 gün |
| 6 | **FAZ 4.5** (AI blok şablonu) | 2-3 gün |
| 7 | **FAZ 4.6** (AI çevirmen) | 2-3 gün |
| 8 | FAZ 1.9 (tenant testleri) | 1-2 gün |
| | **Toplam** | **~20-26 iş günü ≈ 4-5 hafta** |

---

## 🔧 Kullanıcı Kararları (yol gösterici)

Bu kararlar sonraki iterasyonlarda referans alınmalı:

1. **Drag-and-drop istemiyoruz** — tasarımı firma yapacak; mevcut section editor + AI sohbet yeterli.
2. **AI özellikleri ücretli** — paid feature olarak müşterilere fiyatlandırılacak.
3. **Çoklu provider** — Anthropic + OpenAI + OpenRouter; BYOK seçeneği zorunlu.
4. **Model rotasyonu** — basit istek ucuz model (Haiku/4o-mini), karmaşık istek güçlü model (Sonnet/4o).
5. **Görsel üretimi sonra** — ilk fazda sadece metin AI.
6. **Kota gerekli** — aylık token + istek limiti, aşımda paid plan'a yönlendirme.

---

## 💰 Maliyet Tahmini

200 müşteri varsayımı, ortalama kullanım, $1 ≈ 35 TL:

| Senaryo | Aylık Maliyet (Sonnet) | Aylık (Haiku) |
|---|---:|---:|
| Tek tenant orta kullanım | $1.18 ≈ 41 TL | $0.65 ≈ 23 TL |
| Yoğun kullanan tenant | $3-5 ≈ 100-175 TL | $1-2 ≈ 35-70 TL |
| Hafif kullanan tenant | $0.30-0.50 ≈ 10-18 TL | $0.10-0.20 ≈ 4-7 TL |
| **200 tenant ortalama** | **~$200/ay ≈ 7.000 TL** | **~$70/ay ≈ 2.500 TL** |

Model rotasyon stratejisi ile beklenen ortalama: **~3.000-4.500 TL/ay**

Tek istek başına örnekler:
- "Hakkımızda yazısı yaz" — Sonnet: $0.013 (≈0.45 TL); Haiku: $0.003 (≈0.10 TL)
- "Tam klinik anasayfası yap" — Sonnet: $0.039 (≈1.4 TL); Haiku: $0.013 (≈0.45 TL)
- "TR→EN çeviri (1 sayfa)" — Sonnet: $0.018 (≈0.65 TL); Haiku: $0.006 (≈0.20 TL)

Görsel (sonraki faz):
- Flux schnell: $0.003/image
- Flux dev: $0.025/image
- DALL-E 3 HD: $0.080/image
