#!/usr/bin/env bash
# scripts/diag.sh — graficms hızlı hata teşhisi.
#
# HOST tarafında çalışır (docker exec ile). Image'a gömülmez → rebuild GEREKMEZ,
# sadece `git pull` yeterli. Tüm çalışan app container'larını otomatik bulur.
#
# Kullanım (VPS'te /opt/graficms içinde):
#   bash scripts/diag.sh           # her app'te SON hata bloğunu (stack trace) göster
#   bash scripts/diag.sh dns       # DNS (getaddrinfo / EAI_AGAIN) durumunu özetle
#   bash scripts/diag.sh watch     # canlı izle (tail -f) — sayfayı yenile, hatayı anında gör
#   bash scripts/diag.sh "metin"   # logda metni ara, son eşleşmeleri göster
#   bash scripts/diag.sh clear     # tüm app loglarını sıfırla (temiz başlangıç için)
set -u

# Çalışan app container'larını otomatik bul (…_app1 / …_app2 …).
APPS=$(docker ps --format '{{.Names}}' | grep -E '_app[0-9]+$' | sort)
[ -z "$APPS" ] && APPS=$(docker ps --format '{{.Names}}' | grep -iE 'app' | sort)
[ -z "$APPS" ] && { echo "Çalışan app container bulunamadı (docker ps)."; exit 1; }

MODE=${1:-last}
sep(){ printf '\n\033[1;36m========== %s ==========\033[0m\n' "$1"; }

# Container içinde en güncel log dosyasını bulan ortak parça.
FIND_LOG='f=$(ls -t storage/logs/*.log 2>/dev/null | head -1);
  [ -z "$f" ] && f=$(ls -t /var/www/html/storage/logs/*.log 2>/dev/null | head -1);
  [ -z "$f" ] && f=$(ls -t /var/www/storage/logs/*.log 2>/dev/null | head -1);'

case "$MODE" in
  last)
    for c in $APPS; do
      sep "$c — SON HATA"
      docker exec "$c" sh -c "$FIND_LOG"'
        [ -z "$f" ] && { echo "  (log dosyası bulunamadı)"; exit 0; }
        echo "log: $f"
        ln=$(grep -nE "\.(ERROR|CRITICAL|ALERT|EMERGENCY): " "$f" | tail -1 | cut -d: -f1)
        [ -z "$ln" ] && { echo "✅ Bu container logunda ERROR yok."; exit 0; }
        echo "----- son hata (satır $ln) + stack trace -----"
        sed -n "${ln},$((ln+35))p" "$f"
      '
    done
    ;;

  dns)
    for c in $APPS; do
      sep "$c — DNS / getaddrinfo"
      docker exec "$c" sh -c "$FIND_LOG"'
        [ -z "$f" ] && { echo "  (log yok)"; exit 0; }
        echo "toplam getaddrinfo hatası: $(grep -c getaddrinfo "$f")"
        echo "son 3 oluşum:"; grep getaddrinfo "$f" | tail -3 | cut -c1-110
        echo "mariadb_admindb çözümü:"; getent hosts mariadb_admindb 2>/dev/null || grep mariadb /etc/hosts || echo "  (pin yok!)"
      '
    done
    ;;

  watch)
    echo "Canlı izleme — Ctrl-C ile çık. Şimdi tarayıcıda hatalı sayfayı yenile."
    trap 'kill 0' EXIT INT TERM
    for c in $APPS; do
      docker exec "$c" sh -c "$FIND_LOG"' tail -n0 -f "$f"' 2>/dev/null | sed "s/^/[$c] /" &
    done
    wait
    ;;

  clear)
    for c in $APPS; do
      sep "$c — log temizleniyor"
      docker exec "$c" sh -c "$FIND_LOG"' [ -n "$f" ] && : > "$f" && echo "temizlendi: $f"'
    done
    ;;

  *)
    for c in $APPS; do
      sep "$c — \"$MODE\" araması"
      docker exec -e Q="$MODE" "$c" sh -c "$FIND_LOG"'
        [ -z "$f" ] && { echo "  (log yok)"; exit 0; }
        grep -nE "$Q" "$f" | tail -15
      '
    done
    ;;
esac
