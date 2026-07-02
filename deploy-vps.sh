#!/bin/bash
# ─────────────────────────────────────────────────────────────────────────────
# VPS deploy — bu sunucu bir DEPLOY HEDEFİdir, geliştirme yapılmaz.
#
# 'git pull' yerine 'fetch + reset --hard origin/main' kullanılır: VPS yereli
# nasıl olursa olsun (yerel commit, başka branch'ten artık, divergent) her deploy
# canlı kodu = origin/main'e TEMİZ eşitler. Böylece "divergent branches" hatası
# olmaz. Tenant geliştirme branch'leri (estetik_dermal, satyapi …) origin'de
# kendi başlarına durur; main'e karışmaz.
#
# Bu dosya artık repoda tracked'dir → branch/stash işlemlerinde kaybolmaz.
# ─────────────────────────────────────────────────────────────────────────────
set -e
cd /opt/graficms

echo "▸ origin/main'e eşitleniyor (temiz deploy hedefi)..."
git fetch origin
git checkout -f main
git reset --hard origin/main
echo "  HEAD: $(git rev-parse --short HEAD)"

echo "▸ Building images..."
docker compose build app1 app2 app3

echo "▸ Restarting containers..."
docker compose up -d --no-deps app1 app2 app3

echo "▸ Clearing caches..."
docker compose exec app1 php artisan optimize:clear
docker compose exec app1 php artisan config:cache
docker compose exec app1 php artisan route:cache
docker compose exec app1 php artisan migrate --force

echo "✓ Deploy tamamlandı! ($(git rev-parse --short HEAD))"
