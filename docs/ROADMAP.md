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
| FAZ 4 — AI Özellikleri | 6 | 6 | 0 |
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

### FAZ 3 — AI Altyapısı (7 madde, ~5-7 gün) — **4/7 bitti, 1 ertelendi**

> Foundation. Tüm AI özelliklerinin ön koşulu.

| # | Madde | Detay |
|---|---|---|
| 3.1 ✅ | AI provider abstraction | `app/Services/Ai/` — Anthropic + OpenAI + OpenRouter adapter'ları, `AiManager`, DTOs, Facade, `php artisan ai:ping` komutu; 13 unit test geçiyor. BYOK desteği DTO'da hazır (`apiKeyOverride`). |
| 3.2 ✅ | Model rotasyonu | `AiModelRouter` — `config('ai.features')` kataloğunda her özellik için tier + max_tokens + temperature (`seo.meta`, `page.create`, `block.template`, …). Provider fallback chain (`config('ai.fallback')`) — primary 429/5xx/timeout'ta `openai` → `openrouter` zincirinde tekrar dener; BYOK key'leri fallback'lere taşınmaz (tenant güvenliği). `ai:ping --feature=…` ile feature-mode test. 14 unit test. |
| 3.3 ✅ | BYOK (Bring Your Own Key) | `Tenant` modelinde encrypted API key storage (Crypt::encryptString); `TenantAiResolver` BYOK + tenant tercihlerini uygular; admin tenant detayında "AI Ayarları" kartı (sağlayıcı seçimi, key girişi, canlı test); routes: `PUT admin/tenants/{t}/ai-settings`, `POST .../test`; 7 unit test. |
| 3.4 ✅ | Kota & billing entegrasyonu | `ai_usage` migration (central DB); `AiUsage` model; `AiQuotaService` (kota check + cost calc + usage record); 4 plan (free/starter/pro/enterprise) + `monthly_requests`/`monthly_tokens`/`monthly_cost_usd` limitleri; BYOK tenant'lar muaf; `AiModelRouter` her isteğin önünde quota check + sonra success/failure record; admin tenant show sayfasında AI Kullanım Özeti paneli (gauge bar'lar, kalan kota uyarısı); plan dropdown'u Ayarlar kartında. 15 unit test. |
| 3.5 ⏸ | Redis prompt caching | **ERTELENDİ** — gerçek kullanım verisi olmadan optimize etmek anlamlı değil. Aşağıda "Ertelenen İşler" bölümüne bakın. |
| 3.6 | Streaming desteği (SSE) | Uzun cevaplarda kullanıcı early stop yapabilir; admin UI'da typewriter efekti |
| 3.7 | AI usage dashboardu | Admin: tüm tenantların kullanımı; Tenant: kendi kotasının grafik gauge'u |

---

#### ⏸ Ertelenen: FAZ 3.5 — Redis Prompt Caching

**Karar tarihi:** 2026-05-10
**Atlanma gerekçesi:** Cache implementasyonu, hangi feature'larda ne kadar tekrar olduğunu bilmeden tahminle yapılır → boşa optimize etme riski. Önce gerçek müşteri-yüzlü AI feature'larını canlıya alıp 1-2 hafta veri toplamak, sonra doğru TTL'lerle cache eklemek daha doğru.

**Ne yapacak (revisit edildiğinde):**
- Cache key: `hash(tenant_id + provider + model + system + prompt + temperature + max_tokens)`
- Tenant-scoped (tenant verisi sızmasın)
- Per-feature TTL: `seo.meta` 24h, `page.translate` 7 gün, `page.create` no-cache (yaratıcı)
- Stampede protection: lock + wait (10 admin aynı anda aynı promptu tetiklerse 1 API call)
- `AiUsage` metadata'sında `cache_hit: bool` flag → dashboard'da görünür

**Beklenen tasarruf (200 tenant projeksiyonu):**
- Aylık AI maliyeti: ~$136 → ~$98 (≈ %28 indirim, ~1.300 TL/ay tasarruf)
- Implementasyon: ~1 gün
- ROI: çok yüksek — ama gerçek tekrar oranları ölçülmeden önce optimal TTL bilinmez

**Hatırlatma tetikleyicileri — şu durumlardan biri olursa 3.5'i ele al:**
1. **AI usage dashboardu (3.7) canlı** ve aylık AI maliyeti **$50'yi geçiyor** ise
2. **FAZ 4.1 + 4.2** canlıya alındıktan **2 hafta sonra** (gerçek kullanım verisi birikmiş olur)
3. Anthropic/OpenAI'dan **rate limit hatası** alınmaya başlandığında
4. Bir tenant ayda 1000+ AI isteği yapmaya başladığında (büyük müşteri)
5. **FAZ 3.4 (kota & billing) bitti** ve kullanıcılar "kotam çok hızlı bitti" şikayetlerine başladığında

**Ön koşul:** FAZ 3.4 (billing/kota) ve FAZ 3.7 (dashboard) tamamlanmış olmalı — yoksa cache hit'i ölçemeyiz, ROI bilinemeyiz.

**Implementation notları (gelecekteki ben için):**
- Anthropic'in **built-in prompt caching**'i ile karıştırma — o ayrı bir özellik (uzun system prompt'lar için %90 indirim, ekstra Redis gerekmez, sadece API parameter). İkisi birlikte kullanılır.
- `AiModelRouter::generate()` içinde cache lookup ekle — `AiManager` katmanına dokunma
- Cache decorator pattern: `CachedAiProvider implements AiProvider` — wrap edebilir
- Feature config'ine `'cache_ttl' => 1440` (dakika) field'ı ekle; 0 = cache disabled

---

**Bağımlılıklar:** FAZ 1 (tenant izolasyon hazır olmalı)
**Çıktı:** Foundation katmanı hazır. Tek bir yerden tüm AI çağrıları yönetilebilir.

---

### FAZ 4 — AI Özellikleri (6 madde, ~13-18 gün) — **6/6 bitti 🎉**

> Müşteriye değer üreten katman. Her madde ayrı bir release olabilir.

| Sıra | # | Madde | Süre | Açıklama |
|---|---|---|---|---|
| 1 | 4.1 ✅ | **AI SEO meta üretici** | bitti | `AiSeoGenerator` (sections_json/layout_json → text extract → AI), `Admin\Ai\SeoMetaController` (POST /admin/pages/{page}/ai/seo-meta); sayfa edit'inde SEO panel'inde "AI ile Üret" butonu; JSON {title, description, keywords} form alanlarına otomatik yazılır; kota aşımında 402 + kalan kota uyarısı; 8 unit test (markdown fenced JSON, chatty prefix, truncation, keyword dedup/cap, content-field filtering). |
| 2 | 4.2 ✅ | **AI ile blok içerik düzenleme** | bitti | `AiBlockEditor` service (12 preset action: shorten/lengthen/professional/casual/seo_optimize/rephrase/fix_typos + 5 dil çevirisi + custom prompt). Schema-aware: sadece text-tipli alanları (`string`, `text`, `longtext`, `html`, `richtext`) düzenler; `media_id`/url/color/icon gibi non-text alanlara dokunmaz. AI tip değişikliği yaparsa reddedilir (string→array yoksay). Frontend block settings modal'ında "AI Yardımcısı" kartı: action dropdown + custom prompt input + Uygula butonu; cevap settingsDraft.content'e yazılır, admin "Kaydet"e basana kadar uygulanmaz. Routes: `POST admin/ai/block-edit`. 11 unit test. |
| 3 | 4.3 ✅ | **Hazır şablon galerisi** | bitti | 6 sektör: Klinik, Avukat, Salon, Hotel, Emlak, Genel Kurumsal. `site_templates` tablosuna `industry`/`summary`/`sort_order` kolonları (central migration). `SiteTemplate::INDUSTRIES` constant; `forIndustry()` scope. `IndustryTemplateApplier` service — `snapshot_json` (pages + menus + settings) parse eder, tenant DB'ye `tenancy()->initialize` ile yazar; SectionTemplate `template_type` + `template_variation` ile resolve eder, hallucination drop; tenant ayarlarına `industry.template_slug` ve `industry.industry` not düşer. `IndustryTemplateController` (GET admin/industry-templates JSON, POST admin/tenants/{t}/apply-industry-template) — agency-admin gated, replace/merge mode. Tenant detayında "Sektör Şablonu Uygula" paneli: industry tab bar + kart grid + onay modalı (yıkıcı işlem uyarısı). `IndustryTemplatesSeeder` 6 sektörü kurar. |
| 4 | 4.4 ✅ | **AI ile sayfa oluşturma** | bitti | `AiPageGenerator` service — central DB'deki aktif SectionTemplate'leri model'e katalog olarak verir (id + type + alanlar), AI uygun blokları seçip her birinin content'ini üretir; hallucinated template_id'ler silinir, content schema-declared key'lere filtrelenir, `FrontendSections::normalize()` ile region-based sections_json'a wrap edilir. `Admin\Ai\PageGenerateController` (POST admin/ai/pages/generate) iki modlu: preview-only (kaydetmez) ve auto_save=true (yeni Page record + benzersiz slug + edit ekranına redirect). Pages index'inde "AI ile Sayfa Oluştur" gradient buton + 2-adımlı wizard modal (prompt → önizleme blok listesi → kaydet). 10 unit test. |
| 5 | 4.5 ✅ | **AI ile blok şablonu (SectionTemplate) oluşturma** ⭐ | bitti | `AiSectionTemplateGenerator` service — tarif → Tailwind CSS ile production-ready HTML + `schema_json` + `default_content_json`. Cross-validation: `{{placeholder}}`/`{{{placeholder}}}` syntax'ı schema key'leri ile auto-cross-checked (eksik schema key auto-add, orphan default key auto-remove, kullanılmayan schema key warning). type/variation otomatik slugify. `Admin\Ai\SectionTemplateGenerateController` (POST admin/ai/section-templates/generate) — preview mode + auto_save=true (is_active=false ile kaydeder, admin review sonrası açar); **agency-admin only**. SectionTemplates index'inde "AI ile Şablon Oluştur" gradient buton + 2-adımlı wizard modal: prompt + renk paleti / stil / dil hint dropdown'ları, preview adımında schema field chip'leri + warnings paneli + Tailwind CDN'li canlı iframe önizleme + HTML kaynak gösterimi. 10 unit test. |
| 6 | 4.6 ✅ | **AI çevirmen** | bitti | `AiTranslator` service — batch çeviri (tek API call'da onlarca alanı çevirir, per-field call'a göre ~50x daha ucuz). Page için title + tüm SEO alanları + `sections_json` içindeki tüm metin leaf'leri çevrilir; `media_id`/url/color/icon/template_id gibi teknik key'ler skip edilir. Article için title + excerpt + body + SEO. HTML tag'ler korunur, sadece tag'ler arasındaki metin çevrilir. `Admin\Ai\TranslateContentController` (POST admin/ai/translate-content) — legacy endpoint adını korur (geriye dönük uyumluluk), iç implementasyon `AiModelRouter` üzerine taşındı; quota + BYOK + fallback otomatik. Frontend `create-translation.blade.php` JS güncellendi: artık `sections_json` da gelir, Alpine event dispatch ile editor `regions` hot-swap olur. **Eski `AiAssistantController` silindi** — `admin.ai.translate`/`.rewrite`/`.generate-meta` dead route'lar kaldırıldı. 8 unit test (batch flow, fallback to original, sections traversal, technical-key skip, MD fenced reply, invalid JSON throw). |

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

| Adım | Faz | Süre | Not |
|---|---|---|---|
| ✅ 1 | FAZ 3.1 (provider abstraction) | bitti | — |
| ✅ 2 | FAZ 3.2 (model rotasyonu) | bitti | — |
| ✅ 3 | FAZ 3.3 (BYOK) | bitti | — |
| 4 | **FAZ 3.4** (kota & billing) | 1-2 gün | Müşteri parası alma için zorunlu |
| 5 | **FAZ 4.1** (SEO meta üretici) | 1-2 gün | İlk müşteri-yüzlü AI özelliği |
| 6 | **FAZ 4.2** (blok içerik düzenleme) | 2-3 gün | |
| 7 | **FAZ 4.3** (hazır şablon galerisi) | 2 gün UI + içerik | AI değil, içerik seed |
| 8 | **FAZ 4.4** (AI sayfa oluşturma) | 3-5 gün | |
| 9 | **FAZ 4.5** (AI blok şablonu) | 2-3 gün | Firma için |
| 10 | **FAZ 4.6** (AI çevirmen) | 2-3 gün | |
| 11 | **FAZ 3.7** (AI usage dashboard) | 1-2 gün | Kullanım veri toplama görselleştirme |
| 12 | **FAZ 3.5** (Redis cache) ⏸ | 1 gün | Önceki adımlardan veri toplandıktan sonra; yukarıdaki "Ertelenen" bölümünün tetikleyicilerine bak |
| 13 | **FAZ 3.6** (streaming SSE) | 2-3 gün | UX iyileştirme; opsiyonel |
| 14 | FAZ 1.9 (tenant testleri) | 1-2 gün | Düşük öncelik |
| | **Kalan toplam** | **~18-24 iş günü ≈ 4 hafta** | |

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
