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
# frontend = Next.js container (grafike_cms_frontend). Değişmediyse docker layer
# cache sayesinde build anında geçer; frontend değişikliği unutulup deploy dışı kalmasın.
docker compose build app1 app2 app3 frontend

echo "▸ Restarting containers..."
docker compose up -d --no-deps app1 app2 app3 frontend

echo "▸ Clearing caches..."
docker compose exec app1 php artisan optimize:clear
docker compose exec app1 php artisan config:cache
docker compose exec app1 php artisan route:cache
docker compose exec app1 php artisan migrate --force

# Traefik dinamik config'i yeniden üret: her tenant domaini için /api (+/admin,
# /forms…) → Laravel backend router (priority 150). Yoksa tarayıcının /api
# fetch'i Next.js'e gidip HTML alır (SSR internal app1:80 çalışsa da). Fix kod
# içinde (TraefikDynamicConfig) — regenerate onu aktif config'e yazar, Traefik
# file-provider ~2sn'de hot-reload eder. Bkz [[traefik-shared-config-regenerate-trap]].
echo "▸ Regenerating Traefik dynamic config (/api → Laravel)..."
docker compose exec app1 php artisan tinker --execute="app(App\Services\TraefikDynamicConfig::class)->regenerate();"

echo "✓ Deploy tamamlandı! ($(git rev-parse --short HEAD))"
