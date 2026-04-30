# Hostinger VPS Docker Kurulumu

Bu kurulum dosyasi mevcut `traefik_proxy` agini ve harici MariaDB sunucusunu kullanir.

## Son Deploy Notu — 2026-04-30

Canli Hostinger VPS deploy'u `feat/multi-tenant` branch'i uzerinden Docker ile basariyla ayaga kaldirildi.

Bu deploy icin net karar:

- VPS'te tek env kaynagi `.env` dosyasidir.
- `.env.hostinger` runtime dosyasi olarak kullanilmiyor; gerekirse sadece ornek dosyadan `.env` uretmek icin kullanilir.
- `docker-compose.yml` ve `docker-compose.hostinger.yml` icindeki `env_file` hedefi `.env` olarak ayarlandi.
- Next.js frontend Docker build asamasinda bos URL gelirse fallback olarak `http://localhost:3000` kullanilir; production'da asil degerler `.env` icinden gelmelidir.

Rebase/force-push sonrasi VPS'te branch ayrismasi olursa calisan komut:

```bash
git fetch origin
git reset --hard origin/feat/multi-tenant
```

Ardindan deploy:

```bash
docker compose up -d --build
```

Hostinger ozel compose dosyasi kullanilacaksa:

```bash
docker compose -f docker-compose.hostinger.yml up -d --build
```

Not: `docker compose --env-file .env.hostinger ...` kullanilmayacak. Bu proje icin VPS standardi `.env` dosyasidir.

## 1. Ortam dosyasini hazirla

```bash
cp .env.hostinger.example .env
```

Su alanlari doldur:

- `APP_KEY`
- `APP_URL`
- `APP_DOMAIN`
- `DB_HOST`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`
- `MAIL_*`

`APP_KEY` uretmek icin:

```bash
php artisan key:generate --show
```

## 2. Traefik network'unu kontrol et

Bu compose dosyasi `traefik_proxy` external network bekler:

```bash
docker network ls
```

Yoksa olustur:

```bash
docker network create traefik_proxy
```

## 3. Uygulamayi ayağa kaldir

```bash
docker compose -f docker-compose.hostinger.yml up -d --build
```

## 4. Migrasyonlari dogrula

```bash
docker compose -f docker-compose.hostinger.yml logs -f app
```

Gerekirse elle calistir:

```bash
docker compose -f docker-compose.hostinger.yml exec app php artisan migrate --force
```

## 5. Guncelleme

Normal durumda:

```bash
git pull
docker compose -f docker-compose.hostinger.yml up -d --build
```

Branch gecmisi rebase edildiyse veya `git pull` "divergent branches" hatasi verirse:

```bash
git fetch origin
git reset --hard origin/feat/multi-tenant
docker compose -f docker-compose.hostinger.yml up -d --build
```

## Notlar

- MySQL/MariaDB bu stack icinde degil; mevcut sunucu/container kullanilir.
- Redis bu stack ile birlikte gelir.
- Traefik route'u `APP_DOMAIN` uzerinden olusur.
- `queue` ve `scheduler` ayridir; production isleri icin gereklidir.
- Sentry instrumentation uyarilari su an build'i durdurmuyor; ayri bir temizlik isi olarak ele alinacak.
