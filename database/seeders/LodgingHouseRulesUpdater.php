<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * CERRAHİ updater — daire-detay bloklarının içeriğine "Konaklama Kuralları"
 * alanlarını (house_rules + checkin_time + checkout_time) yazar. Sayfadaki
 * BAŞKA hiçbir şeye dokunmaz (TenantSeeder re-run sections_json'ı ezer — bunu
 * kullan). Böylece kurallar DB'de açık ve düzenlenebilir olur.
 *
 * KURALLARI DEĞİŞTİRMEK İÇİN: aşağıdaki $rules / $checkin / $checkout değerlerini
 * düzenle, sonra ilgili tenant context'inde bu updater'ı tekrar çalıştır (değerleri
 * her seferinde YENİDEN yazar — idempotent, mevcut değeri günceller).
 *
 * TENANT CONTEXT'inde çalıştır (her otel için):
 *   php artisan tinker --execute="\App\Models\Tenant::find('otelvatan')->run(function(){
 *     require_once base_path('database/seeders/LodgingHouseRulesUpdater.php');
 *     (new \Database\Seeders\LodgingHouseRulesUpdater)->run(); });"
 *
 * NOT: Farklı otel farklı kural isterse ya bu değerleri değiştirip o tenant'ta
 * çalıştır, ya da otelin TenantSeeder'ındaki $detay'ı düzenle.
 */
class LodgingHouseRulesUpdater extends Seeder
{
    /** Tüm otellerde şu an aynı; otel bazında değiştirmek istersen burayı düzenle. */
    private array $rules = [
        'Evcil hayvan kabul edilmemektedir.',
        'Tüm dairelerimiz sigara içilmeyen alandır.',
    ];
    private string $checkin = '14:00';
    private string $checkout = '12:00';

    /** sections_json ağacında type==='daire-detay' bloklarını bul, content'i güncelle. */
    private function walk(&$node): bool
    {
        $changed = false;
        if (is_array($node)) {
            // Bu düğüm bir daire-detay bloğu mu?
            if (($node['type'] ?? null) === 'daire-detay' && isset($node['content']) && is_array($node['content'])) {
                $node['content']['checkin_time']  = $this->checkin;
                $node['content']['checkout_time'] = $this->checkout;
                $node['content']['house_rules']   = $this->rules;
                $changed = true;
            }
            foreach ($node as $k => &$v) {
                if ($k === 'content') { continue; } // content'i yukarıda işledik
                if ($this->walk($v)) {
                    $changed = true;
                }
            }
            unset($v);
        }
        return $changed;
    }

    public function run(): void
    {
        $updated = 0;
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
        $this->command?->info("LodgingHouseRulesUpdater: {$updated} sayfada daire-detay kuralları güncellendi.");
    }
}
