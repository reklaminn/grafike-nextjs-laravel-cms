# Traefik — Central Domain Origin Certificate (cms.grafcore.com)

## Özet
`cms.grafcore.com` (central/admin domain) **Cloudflare proxy arkasında** olduğu için Let's
Encrypt cert'i **alamaz** (TLS-ALPN-01 challenge CF edge'inde takılır). Bu yüzden cert'ini bir
**Cloudflare Origin Certificate**'tan alır — Traefik file-provider ile `traefik-dynamic/certs.yaml`
üzerinden, SNI ile eşleşir. Tenant domainleri ise ACME (HTTP-01) kullanır (repo dışı `acme.json`).

## ⚠️ TUZAK — neden bir kez 526 verdi (2026-07-05)
`traefik-dynamic/` dizini **git repo'nun İÇİNDE** (`/opt/graficms/traefik-dynamic`) ve `certs.yaml`
elle yerleştirilmiş **untracked** bir dosyaydı. Branch-switch / deploy sırasında çalıştırılan bir
**`git clean -fd`** klasörü `certs.yaml` ile birlikte **sildi** → cms.grafcore.com'un cert kaynağı
kalmadı → Traefik default self-signed sundu → Cloudflare Full(strict) → **526**.

- `git reset --hard` untracked'i **silmez**; `git clean -fd` **siler**.
- `TraefikDynamicConfig::regenerate()` yalnızca `tenants.yaml` yazar — **certs.yaml'ı geri
  oluşturmaz** (kayıp kalıcıdır).
- Tenant siteleri etkilenmedi (cert'leri repo-dışı acme.json'da).

## Önlem
1. **`.gitignore`'a `traefik-dynamic/` eklendi** → `git clean -fd` artık dokunmaz.
   > NOT: `git clean -fd**x**` (ignored dahil) yine siler. Tam güvenlik için ↓
2. **Sağlam (yapılacak):** `traefik-dynamic`'i **repo dışına taşı** (ör. `/opt/traefik-dynamic`),
   docker-compose bind-mount'larını güncelle (`./traefik-dynamic:/var/traefik-dynamic` ve Traefik'in
   `:/etc/traefik/dynamic`) → hiçbir git komutu dokunamaz.

## Kayıpsa geri koyma (re-provision)
1. Cloudflare → SSL/TLS → **Origin Server → Create Certificate** (cms.grafcore.com; RSA/ECC, 15 yıl).
   LE kullanmaz → 429 rate-limit'e takılmaz.
2. VPS'te `/opt/graficms/traefik-dynamic/` altına koy:
   - `cms.grafcore.com.crt` (Origin Certificate)
   - `cms.grafcore.com.key` (Private Key)
   - `certs.yaml`:
     ```yaml
     tls:
       certificates:
         - certFile: /var/traefik-dynamic/cms.grafcore.com.crt
           keyFile: /var/traefik-dynamic/cms.grafcore.com.key
     ```
   > Traefik ve app aynı host dizinini görür (`/etc/traefik/dynamic` ve `/var/traefik-dynamic`);
   > file-provider ~2sn'de hot-reload eder.
3. Cloudflare → SSL/TLS → Overview → **Full (Strict)**'e geri al (geçici "Full"dan).

## İlgili — ayrı büyük iş: LE 429 fırtınası
Proxy'li domainler TLS-ALPN-01 ile LE cert alamaz → tekrarlayan başarısız authorization → rate limit.
Kalıcı: Traefik `mytlschallenge` resolver'ını **DNS-01 (Cloudflare API token)**'a çevir, ya da tüm
proxy'li domainlerde CF Origin Cert kullan. Yoksa tenant cached cert'leri dolunca onlar da 526 verir.
