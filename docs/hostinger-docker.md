# Hostinger VPS Docker Kurulumu

Bu kurulum dosyasi mevcut `traefik_proxy` agini ve harici MariaDB sunucusunu kullanir.

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

```bash
git pull
docker compose -f docker-compose.hostinger.yml up -d --build
```

## Notlar

- MySQL/MariaDB bu stack icinde degil; mevcut sunucu/container kullanilir.
- Redis bu stack ile birlikte gelir.
- Traefik route'u `APP_DOMAIN` uzerinden olusur.
- `queue` ve `scheduler` ayridir; production isleri icin gereklidir.
