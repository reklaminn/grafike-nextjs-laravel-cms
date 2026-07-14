<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * CERRAHİ updater — otel (otelvatan/dagkent/homeland) oda-listeleme kartındaki
 * iki-buton bloğuna {{acts_on}}/{{acts_off}} display-toggle enjekte eder.
 *
 * NEDEN: TenantSeeder::run() Page::updateOrCreate ile sections_json'ı TAMAMEN
 * ezer → admin içerik düzenlemeleri kaybolur. Bu updater ise sadece daireler
 * item_template'indeki buton fragment'lerini str_replace ile değiştirir; sayfadaki
 * diğer HİÇBİR şeye dokunmaz. Idempotent: {{acts_off}} zaten varsa atlar.
 *
 * TENANT CONTEXT'inde çalıştır (her otel için ayrı):
 *   php artisan tinker --execute="\App\Models\Tenant::find('otelvatan')->run(function(){
 *     require_once base_path('database/seeders/LodgingListingButtonsUpdater.php');
 *     (new \Database\Seeders\LodgingListingButtonsUpdater)->run(); });"
 *
 * Frontend listing-section acts_on/acts_off'u availability_enabled'a göre besler
 * (kapalı → rezervasyon butonları gizli, tek "Detaylar"; açık → mevcut iki buton).
 */
class LodgingListingButtonsUpdater extends Seeder
{
    /**
     * Her otel için [ara, değiştir] fragment çiftleri. Yalnız ilgili tenant'ın
     * sayfalarında eşleşen set uygulanır (fragment'ler tenant'a özgü stil taşır).
     */
    private function replacements(): array
    {
        return [
            // ── otelvatan ──────────────────────────────────────────────────
            ['flex:1 1 auto;text-align:center;background:#2A2521;color:#fff;font-size:14px;font-weight:700;padding:13px 18px;text-decoration:none">Detayları Gör &amp; Müsaitlik</a>',
             'flex:1 1 auto;text-align:center;background:#2A2521;color:#fff;font-size:14px;font-weight:700;padding:13px 18px;text-decoration:none;{{acts_on}}">Detayları Gör &amp; Müsaitlik</a>'],
            ['flex:1 1 auto;text-align:center;background:#fff;color:#2A2521;border:1px solid rgba(42,37,33,0.25);font-size:14px;font-weight:700;padding:13px 18px;text-decoration:none">Rezervasyon</a>',
             'flex:1 1 auto;text-align:center;background:#fff;color:#2A2521;border:1px solid rgba(42,37,33,0.25);font-size:14px;font-weight:700;padding:13px 18px;text-decoration:none;{{acts_on}}">Rezervasyon</a><a href="/daire-{{slug}}" style="flex:1 1 auto;text-align:center;background:#2A2521;color:#fff;font-size:14px;font-weight:700;padding:13px 18px;text-decoration:none;{{acts_off}}">Detaylar</a>'],

            // ── dagkent (border-radius:8px) ────────────────────────────────
            ['flex:1;text-align:center;background:#eef1e9;color:#2f4638;font-size:14px;font-weight:600;padding:12px;border-radius:8px;text-decoration:none">Detay</a>',
             'flex:1;text-align:center;background:#eef1e9;color:#2f4638;font-size:14px;font-weight:600;padding:12px;border-radius:8px;text-decoration:none;{{acts_on}}">Detay</a>'],
            ['flex:1;text-align:center;background:#2f4638;color:#f5f2ea;font-size:14px;font-weight:600;padding:12px;border-radius:8px;text-decoration:none">Talep</a>',
             'flex:1;text-align:center;background:#2f4638;color:#f5f2ea;font-size:14px;font-weight:600;padding:12px;border-radius:8px;text-decoration:none;{{acts_on}}">Talep</a><a href="/daire-{{slug}}" style="flex:1;text-align:center;background:#2f4638;color:#f5f2ea;font-size:14px;font-weight:600;padding:12px;border-radius:8px;text-decoration:none;{{acts_off}}">Detaylar</a>'],

            // ── homeland (border-radius:3px, Jost) ─────────────────────────
            ['flex:1;text-align:center;border:1px solid rgba(230,220,205,0.2);color:#EDE8E0;font-family:\'Jost\',sans-serif;font-size:14px;font-weight:400;padding:13px;border-radius:3px;text-decoration:none">Detay</a>',
             'flex:1;text-align:center;border:1px solid rgba(230,220,205,0.2);color:#EDE8E0;font-family:\'Jost\',sans-serif;font-size:14px;font-weight:400;padding:13px;border-radius:3px;text-decoration:none;{{acts_on}}">Detay</a>'],
            ['flex:1;text-align:center;background:#C7A16B;color:#0C0D0F;font-family:\'Jost\',sans-serif;font-size:14px;font-weight:500;padding:13px;border-radius:3px;text-decoration:none">Talep</a>',
             'flex:1;text-align:center;background:#C7A16B;color:#0C0D0F;font-family:\'Jost\',sans-serif;font-size:14px;font-weight:500;padding:13px;border-radius:3px;text-decoration:none;{{acts_on}}">Talep</a><a href="/daire-{{slug}}" style="flex:1;text-align:center;background:#C7A16B;color:#0C0D0F;font-family:\'Jost\',sans-serif;font-size:14px;font-weight:500;padding:13px;border-radius:3px;text-decoration:none;{{acts_off}}">Detaylar</a>'],
        ];
    }

    private function transform(string $s): string
    {
        // Idempotent: bu template zaten güncellenmişse dokunma.
        if (str_contains($s, '{{acts_off}}')) {
            return $s;
        }
        foreach ($this->replacements() as [$search, $replace]) {
            if (str_contains($s, $search)) {
                $s = str_replace($search, $replace, $s);
            }
        }
        return $s;
    }

    /** sections_json array'ini yürü; string değerleri dönüştür. */
    private function walk(&$node): bool
    {
        $changed = false;
        if (is_array($node)) {
            foreach ($node as &$v) {
                if ($this->walk($v)) {
                    $changed = true;
                }
            }
            unset($v);
        } elseif (is_string($node)) {
            $new = $this->transform($node);
            if ($new !== $node) {
                $node = $new;
                $changed = true;
            }
        }
        return $changed;
    }

    public function run(): void
    {
        $updated = 0;
        // Trashed dahil TÜM sayfalar (daireler bloğu nerede olursa).
        foreach (Page::withTrashed()->get() as $page) {
            $sections = $page->sections_json;
            if (! is_array($sections)) {
                continue;
            }
            if ($this->walk($sections)) {
                $page->sections_json = $sections;
                $page->save();
                $updated++;
            }
        }
        $this->command?->info("LodgingListingButtonsUpdater: {$updated} sayfa güncellendi.");
    }
}
