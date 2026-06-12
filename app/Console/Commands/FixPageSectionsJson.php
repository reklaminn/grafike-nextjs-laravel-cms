<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Wizard'ın çift encode ettiği sections_json kayıtlarını onarır.
 *
 * Sorun: Wizard `json_encode([])` (string) değerini array-cast alana yazınca
 * Laravel çift encode eder → DB'de `"[]"` → okunca string `'[]'` → TypeError.
 *
 * Bu komut aktif tenant DB'sindeki bozuk kayıtları düzeltir.
 * Çalıştırma: php artisan pages:fix-sections-json --tenant=estetikdermal
 */
class FixPageSectionsJson extends Command
{
    protected $signature   = 'pages:fix-sections-json {--tenant= : Tenant ID (zorunlu)}';
    protected $description = 'Çift encode edilmiş sections_json kayıtlarını onarır';

    public function handle(): int
    {
        $tenantId = $this->option('tenant');
        if (! $tenantId) {
            $this->error('--tenant=<id> parametresi zorunlu');
            return 1;
        }

        $tenant = \App\Models\Tenant::find($tenantId);
        if (! $tenant) {
            $this->error("Tenant bulunamadı: {$tenantId}");
            return 1;
        }

        tenancy()->initialize($tenant);

        try {
            // DB'de string olarak saklanan sections_json kayıtlarını bul ve düzelt
            $fixed = 0;
            $pages = DB::table('pages')->get(['id', 'sections_json']);

            foreach ($pages as $page) {
                $raw = $page->sections_json;
                if ($raw === null) continue;

                // Sorunlu durum 1: '"[]"' — çift encode, string döner
                // Sorunlu durum 2: '[]'  — string olarak DB'de, array cast çalışmadı
                $decoded = json_decode($raw, true);

                if (is_string($decoded)) {
                    // Çift encode: json_decode('"[]"') = '[]' (string)
                    $inner = json_decode($decoded, true) ?? [];
                    DB::table('pages')->where('id', $page->id)
                        ->update(['sections_json' => json_encode($inner)]);
                    $this->line("  #$page->id: çift encode → düzeltildi (". count($inner) ." blok)");
                    $fixed++;
                } elseif ($decoded === null && $raw !== 'null') {
                    // Geçersiz JSON — boş array yaz
                    DB::table('pages')->where('id', $page->id)
                        ->update(['sections_json' => '[]']);
                    $this->line("  #$page->id: geçersiz JSON → [] yapıldı");
                    $fixed++;
                }
            }

            $this->info("Tamamlandı. Düzeltilen: {$fixed} / " . count($pages) . " sayfa.");
        } finally {
            tenancy()->end();
        }

        return 0;
    }
}
