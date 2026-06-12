# QA Checklist — P0 Modülleri

> **Amaç:** Gerçek müşteri kullanımına geçmeden önce yapılması gereken kritik testler.
> **Kapsam:** Multi-Tenant İzolasyon · Admin Erişim Kontrolü · Production Ops.
> **Ortam:** Production VPS (`/opt/graficms`) veya staging.
> **Yöntem:** Manuel UI + bazı testlerde `docker exec` + `curl`.

Her test için:
- ✅ Geçti → tick
- ❌ Kaldı → kayda bağla, GH issue aç
- ⚠️ Kısmen → not düş

---

## 🔐 Modül 1 — Multi-Tenant İzolasyon

**Ön koşul**: En az 2 ayrı tenant kurulu olmalı. Hızlı kurulum:
```bash
docker exec -it grafike_cms_app1 php artisan tinker --execute='
  DB::connection("central")->table("tenants")->insert(["id"=>"clinic","data"=>json_encode(["name"=>"Test Klinik","status"=>"active"]),"created_at"=>now(),"updated_at"=>now()]);
  DB::connection("central")->table("tenants")->insert(["id"=>"lawyer","data"=>json_encode(["name"=>"Test Hukuk","status"=>"active"]),"created_at"=>now(),"updated_at"=>now()]);
'
docker exec grafike_cms_app1 php artisan tenants:migrate --tenants=clinic --force
docker exec grafike_cms_app1 php artisan tenants:migrate --tenants=lawyer --force
```

### P0-1.1 — Tenant Provisioning (sıfırdan tenant kurulumu)

**Adımlar:**
1. Admin panel → Siteler → **Yeni Site**
2. Slug: `test-tenant-01`, Domain: `test01.example.local`, Tema seç
3. "Şirket Admin'i de oluştur" işaretle: ad/kullanıcı/şifre gir
4. **Provision Et** butonuna bas

**Beklenen:**
- ✅ Central DB'de `tenants` tablosuna kayıt eklendi
- ✅ Central DB'de `domains` tablosuna 1 satır eklendi
- ✅ Tenant DB olarak `tenant_test-tenant-01` fiziksel oluşturuldu
- ✅ Tenant DB'de tüm tenant migration'ları çalıştı (`pages`, `articles`, `menus`, `site_settings`, vb.)
- ✅ `admin_tenant_access` tablosuna company-admin kaydı düştü
- ✅ Tenant'a giriş yapan company-admin sadece kendi tenant'ını görür

**Doğrulama komutları:**
```bash
docker exec grafike_cms_app1 php artisan tenants:list
docker exec grafike_cms_mariadb mariadb -uroot -p<PW> -e "SHOW DATABASES LIKE 'tenant_%'"
docker exec grafike_cms_mariadb mariadb -uroot -p<PW> tenant_test-tenant-01 -e "SHOW TABLES"
```

---

### P0-1.2 — Domain Routing

**Adımlar:**
1. `/etc/hosts`'a (veya gerçek DNS'e) `127.0.0.1 test01.example.local test02.example.local` ekle
2. Tarayıcıdan `http://test01.example.local` aç → tenant 1'in anasayfası gelmeli
3. `http://test02.example.local` aç → tenant 2'nin anasayfası gelmeli (farklı içerik)
4. Var olmayan domain (`http://undefined.example.local`) → 404

**Beklenen:**
- ✅ Her domain doğru tenant DB'sine yönlenir
- ✅ Tenant 1'in pages tablosundaki içerik tenant 2'de görünmez
- ✅ İki sitenin kaynak HTML'inde farklı `<title>` / `<meta>` çıktısı

**Risk:**
- stancl middleware sırası `bootstrap/app.php`'de doğru kayıtlı olmalı (`InitializeTenancyByDomain`, `PreventAccessFromCentralDomains`)

---

### P0-1.3 — Cross-Tenant Veri Sızması (kritik)

**Bu testin neden P0:** GDPR ihlali = kapanma sebebi. Aşağıdaki 5 alt-test hepsi geçmeli.

#### 1.3.a — Aynı slug iki tenant'ta bağımsız
1. Tenant 1 admin'i → Sayfalar → "anasayfa" slug'lı sayfa oluştur, başlık "Klinik Anasayfa"
2. Tenant 2 admin'i → Sayfalar → "anasayfa" slug'lı sayfa oluştur, başlık "Hukuk Anasayfa"
3. **Beklenen:** İki kayıt da ayrı DB'lerde — çakışma hatası yok. Frontend'de her domain doğru başlığı gösterir.

#### 1.3.b — Tenant 1 admin'i tenant 2'nin verisini göremez
1. Tenant 1 company admin → "Aktif Site: clinic"
2. Sayfalar listesi → sadece clinic sayfaları görünür (lawyer sayfaları yok)
3. URL'i manuel değiştir: `/admin/pages/<lawyer_page_id>/edit` (varsa)
4. **Beklenen:** 404 veya 403 (tenant.admin middleware bloklar)

#### 1.3.c — Cache key izolasyonu
1. Redis CLI: `docker exec grafike_cms_redis redis-cli`
2. Tenant 1 sayfası SEO cache'lendiğinde → `KEYS *` ile bak
3. **Beklenen:** Key format'ı `tenant_clinic_xxx` veya `clinic:cms:seo:xxx` (tenant prefix'i içermeli)
4. Tenant 1 SEO temizle (`cache:clear` veya save) → Tenant 2 cache'i etkilenmez

#### 1.3.d — Storage izolasyonu
1. Tenant 1 admin → bir görsel yükle
2. VPS'te `docker exec grafike_cms_app1 ls /var/www/html/storage/app/`
3. **Beklenen:** `tenant_clinic/` ve `tenant_lawyer/` ayrı klasörler

#### 1.3.e — Tenant DB direkt erişim guard'ı
```bash
docker exec grafike_cms_app1 php artisan tinker --execute='
  $tenant = App\Models\Tenant::find("clinic");
  tenancy()->initialize($tenant);
  echo App\Models\Page::count()."\n";  // clinic count
  tenancy()->end();
  tenancy()->initialize(App\Models\Tenant::find("lawyer"));
  echo App\Models\Page::count()."\n";  // lawyer count — different
'
```

**Beklenen:** İki ayrı sayı; clinic context'te lawyer sayfaları görünmez.

---

### P0-1.4 — Tenant Silme

**Adımlar:**
1. Admin panel → Siteler → bir test tenant'ı seç → **Sil**
2. Onay modalı → silmeyi onayla

**Beklenen:**
- ✅ Central'da `tenants` + `domains` kayıtları silindi (cascade)
- ✅ Tenant DB fiziksel olarak `DROP DATABASE` ile silindi
- ✅ `admin_tenant_access` kayıtları temizlendi
- ✅ Diğer tenant'lar etkilenmedi
- ✅ Storage klasörü (`storage/app/tenant_<slug>/`) hâlâ duruyor (manuel temizlik gerekebilir — not olarak işaretle)

**Doğrulama:**
```bash
docker exec grafike_cms_mariadb mariadb -uroot -p<PW> -e "SHOW DATABASES LIKE 'tenant_silinen-slug%'"
# → boş dönmeli
```

---

### P0-1.5 — BYOK Key İzolasyonu

**Adımlar:**
1. Tenant 1 → AI Ayarları → BYOK aç, Anthropic key gir: `sk-ant-CLINIC-KEY`
2. Tenant 2 → AI Ayarları → BYOK aç, OpenAI key gir: `sk-OPENAI-LAWYER`
3. Test butonuyla her tenant'ta kendi sağlayıcısına ping at
4. Tenant 1 yetkilisi olarak tenant 2'nin ayarlar sayfasına eriş (yetkisi varsa)

**Beklenen:**
- ✅ Tenant 1 SEO meta üretirken Anthropic'e gider (logda doğrula)
- ✅ Tenant 2 SEO meta üretirken OpenAI'a gider
- ✅ İki tenant'ın `tenants.data.ai_settings.api_keys` JSON'unda farklı (Crypt::encryptString edilmiş) değerler
- ✅ Tenant 1 admin'i tenant 2'nin key'ini hiçbir UI'da göremez (sadece "anahtar kayıtlı" badge)

**Doğrulama:**
```bash
docker exec grafike_cms_app1 php artisan tinker --execute='
  $t1 = App\Models\Tenant::find("clinic");
  $t2 = App\Models\Tenant::find("lawyer");
  var_dump($t1->aiApiKey("anthropic"));  // sk-ant-CLINIC-KEY
  var_dump($t2->aiApiKey("anthropic"));  // null
  var_dump($t1->aiApiKey("openai"));      // null
  var_dump($t2->aiApiKey("openai"));      // sk-OPENAI-LAWYER
'
```

---

### P0-1.6 — Aynı Slug İki Tenant'ta Çakışmaz (DB tarafından)

Bu **P0-1.3.a** ile aynı kapsam — burada DB seviyesinde doğrulama:

```sql
-- tenant_clinic DB'de
SELECT slug FROM pages WHERE slug = 'anasayfa';
-- → 1 row

-- tenant_lawyer DB'de
SELECT slug FROM pages WHERE slug = 'anasayfa';
-- → 1 row, FARKLI ID, FARKLI title
```

**Beklenen:** UNIQUE constraint sadece DB içinde (tenant scope), iki DB arasında değil. ✅ Bu zaten doğru çünkü ayrı DB'ler.

---

## 👤 Modül 2 — Admin Erişim Kontrolü

### P0-2.1 — Agency Admin vs Tenant Admin Yetki Ayrımı

**Adımlar:**
1. Agency admin (`admin` role) ile giriş yap
2. Sidebar'da görünmesi gerekenler: Siteler, Yöneticiler, Roller, AI Kullanım, DB Bakım, Aktivite Log
3. Çıkış yap → tenant admin (`role: owner` veya `editor`) ile giriş yap
4. Sidebar'da görünmemesi gerekenler: Yöneticiler, Roller, DB Bakım

**Beklenen:**
- ✅ Agency admin tüm tenant'ları görebilir
- ✅ Tenant admin sadece kendisine `admin_tenant_access` ile bağlı tenant(ları) görür
- ✅ Tenant admin manuel URL ile `/admin/admin-users` çağırırsa 403
- ✅ Tenant admin manuel URL ile `/admin/tenants/{olmayan-id}` → 403 veya 404

**Risk:**
- Eski `is_super_admin` flag'i varsa kontrol et; yeni sistem `role` üzerinden çalışıyor
- `isAgencyAdmin()` metodunun her controller'da `authorizeAgencyAdmin()` gate ettiğinden emin ol

---

### P0-2.2 — AdminTenantAccess Gating

**Adımlar:**
1. Agency admin → Tenant 1'in detay sayfasında "Yetki Yönet" (veya benzeri)
2. Test bir company admin oluştur, tenant 1'e erişim ver
3. Bu admin ile giriş yap
4. Tenant 2'nin detay sayfasına manuel URL ile gir: `/admin/tenants/lawyer`

**Beklenen:**
- ✅ Tenant 1'in detay sayfası açılır
- ✅ Tenant 2'nin detay sayfası 403
- ✅ Tenant 2'nin Sayfalar URL'i (`/admin/pages` after `switchTo`) erişilemez
- ✅ `Admin::canAccessTenant($tenant)` metodu doğru çalışır

---

### P0-2.3 — Tenant Switch (Active Tenant Session)

**Adımlar:**
1. Tenant admin'i çoklu tenant'a erişimli (örn. iki klinik şubesi)
2. Tenant 1'i seç (`switchTo`) → Sayfalar listesinde tenant 1 verisi
3. Aynı session'da Tenant 2'yi seç → Sayfalar listesi tenant 2'ye değişir
4. Logout → login → varsayılan tenant (default flag'li) açılır
5. "Aktifi Temizle" → session'daki active_tenant temizlenir

**Beklenen:**
- ✅ Session değişikliği DB connection değişimine yol açar (stancl)
- ✅ Cache key'leri otomatik tenant-scoped olur
- ✅ Tenant 2'ye geçişte Tenant 1 verisi sızmaz (Redis prefix)

**Risk:** Octane veya FrankenPHP gibi long-lived worker'larda session leak — production'da `php-fpm` mode'unda olduğunu doğrula.

---

### P0-2.4 — Şifre Sıfırlama Akışı (Member + Admin)

#### Admin tarafı
1. Admin giriş sayfası → "Şifremi Unuttum"
2. Email gir, gönder → mailbox kontrol et
3. Maildeki link → yeni şifre gir → giriş yap

**Beklenen:**
- ✅ Email gönderildi (`docker exec grafike_cms_app1 php artisan tinker --execute='Mail::fake()'` test ortamında, gerçekte SMTP)
- ✅ Token expiry çalışıyor (60 dk varsayılan)
- ✅ Eski şifre artık geçerli değil

#### Member tarafı (tenant)
1. Tenant'ın frontend sitesinde üye girişi → "Şifremi Unuttum"
2. Email gir, gönder
3. Maildeki link → yeni şifre

**Beklenen:**
- ✅ Branded email template'i (FAZ plan dışı işlerden)
- ✅ Member tenant DB'de sadece kendi tenant'ında bulunmalı
- ✅ Cross-tenant email collision → password reset alma yolu yok

---

### P0-2.5 — Role / Permission Değişikliği Canlıda Yansır

**Adımlar:**
1. Agency admin → Roller → "Editor" rolüne "delete pages" yetkisi ekle
2. Editor rolüne sahip admin'in açık session'ında deneme
3. Aynı admin'i "Editor" → "Viewer" yap → yeniden test

**Beklenen:**
- ✅ Yeni session açıldığında yetki güncel
- ✅ Mevcut session'ında controller authorize() çağrılarında yeni yetki uygulanır
- ⚠️ Cache'lenmiş yetki varsa `php artisan permission:cache-reset` gerekebilir

---

### P0-2.6 — 2FA (Bulunmuyor — bilgi notu)

**Durum:** 2FA henüz implement edilmedi. Plan dışı todo listede mevcut.

**Geçici öneri:**
- Production'da admin login'i için **strong password policy** (min 12 char) zorunlu tut
- SSH key auth + nginx allowlist ile admin URL'i kısıtla
- 2FA'yı eklemek isterseniz: FAZ 5+ olarak ayrı bir iş

---

## 🐳 Modül 13 — Production Ops

### P0-13.1 — 3-App HA (Traefik + Health Check)

**Adımlar:**
1. SSH ile VPS'e gir
2. `docker ps` → 3 app container görünür (`grafike_cms_app1/2/3`) ve hepsi `healthy`
3. `curl https://tenant1.example.com` → 200 OK
4. **app2'yi durdur:** `docker stop grafike_cms_app2`
5. 30 saniye bekle (Traefik health check intervali)
6. Tekrar `curl https://tenant1.example.com` → hâlâ 200 OK
7. **app2'yi geri başlat:** `docker start grafike_cms_app2`
8. 30 saniye sonra `docker ps` → `healthy`

**Beklenen:**
- ✅ Site kullanıcılarının fark etmediği bir kesintisiz hizmet
- ✅ Traefik dashboard'da (eğer açıksa) app2 "unhealthy" → "healthy" geçişi görünür
- ✅ Application log'larda hata patlaması yok

**Risk:**
- Stateful (Octane in-memory cache) varsa session/cache farkı oluşabilir → Redis'ten share ediliyor mu doğrula

---

### P0-13.2 — SSL / Certificate Yenileme

**Adımlar:**
1. Traefik config'inde Let's Encrypt resolver tanımlı mı doğrula
2. `docker exec traefik traefik healthcheck` (eğer var)
3. `curl -vI https://tenant1.example.com 2>&1 | grep -E "expire|issuer"`
4. Domain için kurulu sertifika **>30 gün** geçerliyse OK

**Beklenen:**
- ✅ Tüm tenant domain'leri kendi cert'leriyle servis ediliyor
- ✅ `acme.json` dosyası persist disk'te (`/letsencrypt/acme.json`)
- ✅ Yenilenme otomatik (Traefik 60 gün öncesinden başlar)

---

### P0-13.3 — MariaDB + Redis Persistence

**Adımlar:**
1. `docker exec grafike_cms_mariadb mariadb -uroot -p<PW> grafike_main -e "SELECT COUNT(*) FROM tenants"`
2. Şu komutu çalıştır:
   ```bash
   docker exec grafike_cms_mariadb mariadb -uroot -p<PW> grafike_main \
     -e "INSERT INTO ai_usage (tenant_id, feature, provider, model, total_tokens, success, created_at) VALUES ('persist-test', 'test', 'anthropic', 'haiku', 100, 1, NOW())"
   ```
3. **Container restart:** `docker restart grafike_cms_mariadb`
4. 10 saniye bekle, tekrar SELECT
5. **Aynı şeyi Redis için:** `redis-cli SET persist:test "hello"` → `docker restart grafike_cms_redis` → `redis-cli GET persist:test`

**Beklenen:**
- ✅ MariaDB volume'da veri persist
- ✅ Redis volume'da `--save 60 1` ile persist (compose'ta tanımlı)
- ⚠️ Redis FLUSHALL test etme — production data var

---

### P0-13.4 — Migration & Seeder Akışı

**Adımlar:**
1. Yeni bir migration ekledin sınıflandırması (örn. central migration)
2. **Central:** `docker exec grafike_cms_app1 php artisan migrate --force`
3. **Tenant başına:** `docker exec grafike_cms_app1 php artisan tenants:migrate --force`
4. Belirli bir tenant için: `--tenants=clinic`
5. Sektör seeder: `docker exec grafike_cms_app1 php artisan db:seed --class=IndustryTemplatesSeeder --force`

**Beklenen:**
- ✅ Migration central'a uygulandı, tenant DB'lere uygulanmadı (ayırma çalışıyor)
- ✅ Tenant migration sadece tenant_<slug> DB'lerine uygulandı
- ✅ Seeder idempotent (ikinci sefer çalıştırınca duplicate hatası vermedi — `updateOrCreate` pattern'i)

**Risk:** Yeni eklenmiş `2026_05_10_120000_create_ai_usage_table.php` ve `2026_05_10_180000_add_industry_to_site_templates.php` migration'ları production'da çalıştırılmış mı doğrula.

---

### P0-13.5 — Cache Temizleme + OPcache Restart Akışı

**Bu kritik:** Daha önce business.blade.php sorunu yaşadık. Doğru sıra:

```bash
# 1. Yeni kodu çek
cd /opt/graficms
git fetch origin && git reset --hard origin/feat/multi-tenant

# 2. Migration varsa çalıştır
docker exec grafike_cms_app1 php artisan migrate --force

# 3. Tüm container'larda cache temizliği
for c in grafike_cms_app1 grafike_cms_app2 grafike_cms_app3; do
  docker exec $c sh -c 'rm -f /var/www/html/storage/framework/views/*.php'
  docker exec $c php /var/www/html/artisan view:clear
  docker exec $c php /var/www/html/artisan route:clear
  docker exec $c php /var/www/html/artisan config:clear
  docker exec $c php /var/www/html/artisan cache:clear
done

# 4. Container restart — OPcache temizliği için ZORUNLU
docker restart grafike_cms_app1 grafike_cms_app2 grafike_cms_app3
```

**Beklenen:**
- ✅ Production hatası `business.blade.php` yine başlamamalı (geçmişte yaşandı)
- ✅ Yeni route'lar (`admin.ai.stream.block-edit`, vb.) `php artisan route:list`'de görünür
- ✅ Site downtime <5 saniye (3 container sırayla restart)

**Risk:** `docker restart` aynı anda 3 container yaparsa Traefik tümünü unhealthy görür. **Sırayla yap:** `docker restart app1 && sleep 10 && docker restart app2 && sleep 10 && docker restart app3`

---

### P0-13.6 — Backup Oluşturma + İndirme

**Adımlar:**
1. Agency admin → Tenant detayı → Backup panel
2. **Backup Oluştur** butonu
3. Liste'de yeni satır görünür (timestamp + boyut)
4. **İndir** butonu → tar.gz dosyası inmiş olmalı
5. tar.gz içeriği:
   ```
   tar -tzf tenant-clinic-2026-05-10.tar.gz
   # → db.sql + storage/* dosyaları
   ```

**Beklenen:**
- ✅ Tenant DB tam dump'ı (CREATE TABLE + INSERT)
- ✅ Tenant storage klasörü (media library dosyaları dahil)
- ✅ Backup'lar `/storage/backups/{tenant_id}/` altında tutulur
- ✅ Backup silme butonu çalışır (dosya gerçekten silinir)

**Risk:** Büyük tenant'larda (10GB+ storage) backup uzun sürebilir → timeout limits

---

### P0-13.7 — Disk Doluluk Monitoring

**Adımlar:**
```bash
df -h | grep -E "/dev/sd|/dev/nvme"
docker exec grafike_cms_mariadb du -sh /var/lib/mysql
docker exec grafike_cms_redis du -sh /data
```

**Beklenen:**
- ✅ `/` partition'da <80% kullanım
- ✅ MariaDB ve Redis volume'ları izlenebilir boyutta
- ⚠️ 200 tenant senaryosunda 200 × ~50MB = 10GB DB'ye hazırlık

**Alarm önerisi:** `df -h | awk '$5+0 > 85'` cron'da, Slack webhook'a uyarı

---

### P0-13.8 — Log Erişimi & Boyut Kontrolü

**Adımlar:**
```bash
docker exec grafike_cms_app1 tail -100 /var/www/html/storage/logs/laravel.log
docker exec grafike_cms_app1 wc -l /var/www/html/storage/logs/laravel.log
docker logs grafike_cms_app1 --tail 100
```

**Beklenen:**
- ✅ Production ERROR seviyesi log'ları görünür
- ✅ Log dosyaları rotate ediliyor (`storage/logs/laravel-YYYY-MM-DD.log` daily rotation)
- ✅ Container stdout/stderr → docker logs

**Risk:** Log dosyası 1GB'ı geçerse `du -sh` ile izle; `logrotate` veya Laravel's `LOG_CHANNEL=daily` setup'ı

---

## 📋 Özet Checklist (yazdırılabilir)

### Modül 1 — Multi-Tenant İzolasyon
- [ ] P0-1.1 Tenant provisioning
- [ ] P0-1.2 Domain routing
- [ ] P0-1.3 Cross-tenant sızma (5 alt-test)
  - [ ] 1.3.a Aynı slug izolasyon
  - [ ] 1.3.b Cross-tenant URL erişimi 403
  - [ ] 1.3.c Cache key izolasyonu
  - [ ] 1.3.d Storage izolasyonu
  - [ ] 1.3.e Tinker context guard
- [ ] P0-1.4 Tenant silme
- [ ] P0-1.5 BYOK key izolasyonu
- [ ] P0-1.6 Slug çakışması yok

### Modül 2 — Admin Erişim Kontrolü
- [ ] P0-2.1 Agency vs tenant admin yetki
- [ ] P0-2.2 AdminTenantAccess gating
- [ ] P0-2.3 Tenant switch session
- [ ] P0-2.4 Şifre sıfırlama (admin + member)
- [ ] P0-2.5 Role değişikliği yansıması
- [ ] P0-2.6 2FA — şu an yok, not düşüldü

### Modül 13 — Production Ops
- [ ] P0-13.1 3-App HA + Traefik
- [ ] P0-13.2 SSL cert geçerlilik + yenileme
- [ ] P0-13.3 MariaDB + Redis persistence
- [ ] P0-13.4 Migration + seeder akışı
- [ ] P0-13.5 Cache temizleme + OPcache restart
- [ ] P0-13.6 Backup oluşturma + indirme
- [ ] P0-13.7 Disk doluluk monitoring
- [ ] P0-13.8 Log erişimi + rotation

---

## 🎯 Beklenen Süre

- **Hızlı smoke test** (her başlığa <2 dk): ~45 dk
- **Tam kapsamlı** (her senaryoyu test verisiyle): ~3-4 saat
- **Hata bulunursa:** ayrı issue açılır, fix sonrası P0 yeniden çalıştırılır

## 📌 P0 Geçmeden Yapma

- ❌ Production'a gerçek müşteri eklemeyin
- ❌ Domain DNS'i canlı kullanıma alma
- ❌ Müşterilere demo URL gönderme

P0'ın tüm maddeleri ✅ olunca **P1 modüllere** geçilir (AI özellikleri, frontend render, vb. — ayrı dokümanda detaylandırılacak).
