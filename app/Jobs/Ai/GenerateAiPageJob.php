<?php

namespace App\Jobs\Ai;

use App\Models\Page;
use App\Services\Ai\AiPageGenerator;
use App\Services\Ai\Exceptions\AiQuotaExceededException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * AI sayfa üretimini arka planda çalıştırır.
 *
 * HTTP isteği job'u kuyruğa atıp job_id döner; UI status endpoint'ini
 * (PageGenerateController::status) poll'layarak sonucu alır. Böylece
 * 10-30 sn süren AI çağrısı tarayıcıyı bekletmez, 504 riski kalmaz.
 *
 * Tenant bağlamı QueueTenancyBootstrapper ile otomatik taşınır —
 * dispatch tenant context'inde yapıldığı sürece handle() da aynı
 * tenant'ta çalışır (Page::create doğru tenant DB'sine gider).
 *
 * Durum cache'te tutulur: ai_page_job:{jobId}
 *   { status: queued|running|done|failed, ...payload }
 */
class GenerateAiPageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** AI çağrısı uzun sürebilir */
    public int $timeout = 240;

    /** AI hatasında otomatik tekrar deneme YOK — kota/parayı korur */
    public int $tries = 1;

    private const CACHE_TTL = 3600; // 1 saat

    public function __construct(
        public readonly string $jobId,
        public readonly string $prompt,
        public readonly string $locale,
        public readonly ?int $languageId,
        public readonly ?int $parentId,
        public readonly bool $autoSave,
    ) {}

    public static function cacheKey(string $jobId): string
    {
        return "ai_page_job:{$jobId}";
    }

    public function handle(AiPageGenerator $generator): void
    {
        $this->putStatus(['status' => 'running']);

        $tenant = tenancy()->initialized ? tenant() : null;

        try {
            $result = $generator->generate(
                prompt: $this->prompt,
                tenant: $tenant,
                locale: $this->locale,
            );
        } catch (AiQuotaExceededException $e) {
            $this->putStatus([
                'status'     => 'failed',
                'error_code' => 'quota_exceeded',
                'message'    => $e->getMessage(),
            ]);
            return;
        } catch (Throwable $e) {
            report($e);
            $this->putStatus([
                'status'     => 'failed',
                'error_code' => 'generation_failed',
                'message'    => $e->getMessage(),
            ]);
            return;
        }

        if ($this->autoSave) {
            $page = Page::create([
                'title'         => $result['title'],
                'slug'          => $this->uniqueSlug($result['slug']),
                'language_id'   => $this->languageId,
                'parent_id'     => $this->parentId,
                'status'        => 'draft',
                'sections_json' => $result['sections_json'],
                'show_in_menu'  => false,
            ]);

            $this->putStatus([
                'status'       => 'done',
                'mode'         => 'saved',
                'page_id'      => $page->id,
                'title'        => $page->title,
                'slug'         => $page->slug,
                'block_count'  => count($result['picked_template_ids'] ?? []),
                'redirect_url' => route('admin.pages.edit', $page, false),
            ]);
            return;
        }

        $this->putStatus([
            'status'  => 'done',
            'mode'    => 'preview',
            'preview' => $result,
        ]);
    }

    public function failed(?Throwable $e): void
    {
        $this->putStatus([
            'status'     => 'failed',
            'error_code' => 'generation_failed',
            'message'    => $e?->getMessage() ?? 'Bilinmeyen hata (worker kesintisi olabilir).',
        ]);
    }

    private function putStatus(array $payload): void
    {
        Cache::put(self::cacheKey($this->jobId), $payload, self::CACHE_TTL);
    }

    /**
     * PageGenerateController::uniqueSlug ile aynı mantık — job tenant
     * DB bağlamında çalıştığı için sorgular doğru tenant'a gider.
     */
    private function uniqueSlug(string $base): string
    {
        if (! Page::query()->where('slug', $base)->exists()) {
            return $base;
        }
        for ($i = 2; $i < 200; $i++) {
            $candidate = $base.'-'.$i;
            if (! Page::query()->where('slug', $candidate)->exists()) {
                return $candidate;
            }
        }

        return $base.'-'.substr(uniqid(), -4);
    }
}
