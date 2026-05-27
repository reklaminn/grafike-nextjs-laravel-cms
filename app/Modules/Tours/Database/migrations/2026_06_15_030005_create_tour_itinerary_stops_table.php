<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tenant DB — Tours module / Phase 1.5.c
     *
     * Multi-stop itinerary refactor.
     *
     * Önceki şema:
     *   tour_itinerary_days  →  1 day = 1 location (location string +
     *                           geo lat/lng + arrival/departure time
     *                           kolonları doğrudan day satırında)
     *
     * Yeni şema:
     *   tour_itinerary_days  →  gün-seviyesi başlık + meals + opsiyonel
     *                           özet (port-bağımsız bilgi)
     *   tour_itinerary_stops →  gün içinde N port/lokasyon ziyareti
     *                           (cruise 1,1,1,2,3,3 pattern: aynı gün
     *                           birden çok port, art arda günler aynı
     *                           port — örn. 2 gece İstanbul'da overnight)
     *
     * Migration stratejisi: hard cut (Phase 1.5.b'deki gibi — canlı veri
     * yok).  Day satırlarındaki port kolonları DROP edilir, yeni stops
     * tablosu eklenir.
     */
    public function up(): void
    {
        // 1) Yeni multi-stop tablosu
        Schema::createIfNotExists('tour_itinerary_stops', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tour_itinerary_day_id')
                ->constrained('tour_itinerary_days')
                ->cascadeOnDelete();

            // Port master referansı — cruise turları için kanonik kaynak.
            // Nullable çünkü paket/günlük turlar Port master kullanmayabilir
            // (sadece "Cappadocia / Göreme" gibi serbest metin yetebilir).
            $table->foreignId('port_id')
                ->nullable()
                ->constrained('ports')
                ->nullOnDelete();

            // Port master kullanılmadığında fallback (paket/günlük turlar).
            // Frontend port_id > location fallback hiyerarşisi uygular.
            $table->string('location', 255)->nullable();
            $table->decimal('geo_lat', 10, 7)->nullable();
            $table->decimal('geo_lng', 10, 7)->nullable();

            // Gün içindeki sıra — birden çok port aynı günde olabilir.
            $table->unsignedSmallInteger('sort_order')->default(0);

            // Operasyonel saatler (port arrival/departure).  Cruise için
            // klasik kullanım; paket turda "rehber buluşma saati" olabilir.
            $table->time('arrival_time')->nullable();
            $table->time('departure_time')->nullable();

            // İsteğe bağlı dwell hesaplaması — bazı acenteler "5 saat"
            // olarak göstermek isteyebilir.  arrival/departure'den
            // türetilebilir ama manuel override için kolon ayırıyoruz.
            $table->unsignedSmallInteger('dwell_minutes')->nullable();

            // Operasyonel not ("Scenic cruising — gemi yanaşmaz",
            // "Tender boat ile karaya çıkış", vb.)
            $table->string('notes', 500)->nullable();

            $table->timestamps();

            $table->index(['tour_itinerary_day_id', 'sort_order'], 'idx_stops_day_sort');
            $table->index('port_id');
        });

        // 2) Day tablosundan port-spesifik kolonları kaldır.
        //    Hard cut: canlı veri yok, geriye uyum derdi yok.
        //    Idempotent: sadece var olan kolonları drop ediyoruz ki
        //    re-run / bad-state recovery güvenli olsun.
        $legacyCols = ['location', 'geo_lat', 'geo_lng', 'arrival_time', 'departure_time'];
        $toDrop     = array_values(array_filter(
            $legacyCols,
            fn ($col) => Schema::hasColumn('tour_itinerary_days', $col)
        ));

        if ($toDrop !== []) {
            Schema::table('tour_itinerary_days', function (Blueprint $table) use ($toDrop) {
                $table->dropColumn($toDrop);
            });
        }
    }

    public function down(): void
    {
        // Önce day kolonlarını geri ekle (FK ya da index yok, basit kolon)
        Schema::table('tour_itinerary_days', function (Blueprint $table) {
            $table->string('location', 255)->nullable()->after('description');
            $table->decimal('geo_lat', 10, 7)->nullable()->after('location');
            $table->decimal('geo_lng', 10, 7)->nullable()->after('geo_lat');
            $table->time('arrival_time')->nullable()->after('geo_lng');
            $table->time('departure_time')->nullable()->after('arrival_time');
        });

        Schema::dropIfExists('tour_itinerary_stops');
    }
};
