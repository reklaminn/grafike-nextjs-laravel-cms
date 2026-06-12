<?php

namespace App\Jobs\Ai;

use App\Models\Page;
use App\Services\Ai\AiSeoGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Sayfa yayınlanırken SEO meta alanları boşsa AI ile otomatik doldurur.
 *
 * Yalnızca tenant ai_settings.auto_seo_meta açıkken dispatch edilir
 * (PageObserver). Üretilen meta REVIEW EDİLMEDEN yazıldığı için
 * davranış opt-in'dir; admin sonradan SEO sekmesinden düzenleyebilir.
 *
 * Tenant bağlamı QueueTenancyBootstrapper ile taşınır.
 */
class GenerateSeoMetaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120;

    /** Kota/parayı korumak için otomatik tekrar deneme yok */
    public int $tries = 1;

    public function __construct(public readonly int $pageId) {}

    public function handle(AiSeoGenerator $generator): void
    {
        $page = Page::find($this->pageId);
        if (! $page) {
            return;
        }

        // Kuyrukta beklerken admin elle doldurmuş olabilir — tekrar kontrol
        $seo = $page->seo()->first();
        if ($seo && (filled($seo->meta_title) || filled($seo->meta_description))) {
            return;
        }

        $tenant = tenancy()->initialized ? tenant() : null;

        try {
            $meta = $generator->generate($page, $tenant);
        } catch (Throwable $e) {
            // Otomatik özellik — kota aşımı veya AI hatası sayfayı etkilemesin
            Log::info('auto-seo-meta atlandı', ['page' => $page->id, 'err' => $e->getMessage()]);
            return;
        }

        $page->seo()->updateOrCreate(
            [],
            [
                'slug'             => $page->slug,
                'language_id'      => $page->language_id,
                'meta_title'       => $meta['title'] ?? null,
                'meta_description' => $meta['description'] ?? null,
                'meta_keywords'    => $meta['keywords'] ?? null,
            ]
        );
    }
}
