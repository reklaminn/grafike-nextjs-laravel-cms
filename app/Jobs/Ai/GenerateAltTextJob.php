<?php

namespace App\Jobs\Ai;

use App\Services\Ai\AiAltTextGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

/**
 * Tek bir medya görseli için alt yazısını arka planda üretir
 * (toplu "eksikleri doldur" akışı bu job'u medya başına dispatch eder).
 *
 * Tenant bağlamı QueueTenancyBootstrapper ile taşınır — media tenant
 * DB'sinde olduğu için job da aynı tenant'ta çalışmalıdır.
 */
class GenerateAltTextJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 90;

    /** Kota/parayı korumak için otomatik tekrar deneme yok */
    public int $tries = 1;

    public function __construct(public readonly int $mediaId) {}

    public function handle(AiAltTextGenerator $generator): void
    {
        $media = Media::find($this->mediaId);
        if (! $media) {
            return;
        }

        // Kuyrukta beklerken elle doldurulmuş olabilir
        if (filled($media->getCustomProperty('alt_text'))) {
            return;
        }

        $tenant = tenancy()->initialized ? tenant() : null;

        try {
            $alt = $generator->generate($media, $tenant);
        } catch (Throwable $e) {
            // Toplu akışta tek görselin hatası diğerlerini etkilemesin
            Log::info('alt-text üretimi atlandı', ['media' => $media->id, 'err' => $e->getMessage()]);
            return;
        }

        $media->setCustomProperty('alt_text', $alt);
        $media->save();
    }
}
