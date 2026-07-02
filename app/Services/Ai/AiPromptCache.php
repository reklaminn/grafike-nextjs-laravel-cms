<?php

namespace App\Services\Ai;

use App\Models\Tenant;
use App\Services\Ai\Dtos\AiMessage;
use App\Services\Ai\Dtos\AiRequest;
use App\Services\Ai\Dtos\AiResponse;
use App\Services\Ai\Dtos\AiUsage;
use Closure;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Yanıt önbelleği (FAZ 3.5 — Redis prompt caching).
 *
 * Aynı (tenant + provider + model + system + messages + temperature +
 * max_tokens) için bir kez üretilen AiResponse'u Redis'te saklar; sonraki
 * özdeş istekler sağlayıcıya gitmeden önbellekten döner.
 *
 * Tasarım notları:
 *   - Tenant izolasyonu: key'e tenant_id gömülür (CacheTenancyBootstrapper
 *     ayrıca per-tenant prefix uygular — savunma derinliği).
 *   - Stampede koruması: Cache::lock ile aynı anda gelen N özdeş istek tek
 *     API çağrısı yapar; diğerleri kilidi bekleyip taze yazılan değeri okur.
 *   - Yalnızca BAŞARILI yanıtlar saklanır; hata fırlatan callback cache'lenmez.
 *   - Anthropic'in yerleşik cache_control'ü ile KARIŞTIRILMAZ — o ayrı katman
 *     (uzun system prompt token indirimi), bu katman tam yanıt tekrarını keser.
 */
class AiPromptCache
{
    /**
     * @param  array{enabled:bool, store:?string, prefix:string, lock_wait:int, prompt_cache?:array}  $config
     */
    public function __construct(private readonly array $config)
    {
    }

    public function enabled(): bool
    {
        return (bool) ($this->config['enabled'] ?? false);
    }

    /**
     * Önbellekten al; yoksa callback ile üret, sakla ve döndür.
     *
     * @param  Closure():AiResponse  $generate  Cache miss'te çalışacak üretim
     * @return array{response:AiResponse, hit:bool}
     */
    public function remember(
        string $provider,
        AiRequest $request,
        ?Tenant $tenant,
        int $ttlMinutes,
        Closure $generate,
    ): array {
        // Cache kapalı veya TTL yok → doğrudan üret (kayıt yok).
        if (! $this->enabled() || $ttlMinutes <= 0) {
            return ['response' => $generate(), 'hit' => false];
        }

        $key = $this->cacheKey($provider, $request, $tenant);
        $store = $this->store();

        // 1) Hızlı yol: önbellekte var mı?
        if (($cached = $this->read($store, $key)) !== null) {
            return ['response' => $cached, 'hit' => true];
        }

        // 2) Stampede koruması: kilidi al, içeride tekrar kontrol et.
        $lockWait = max(0, (int) ($this->config['lock_wait'] ?? 10));
        $lock = $store->getStore() instanceof \Illuminate\Contracts\Cache\LockProvider
            ? $store->lock($key.':lock', $lockWait + 5)
            : null;

        if ($lock === null) {
            // Kilit desteklenmiyorsa (driver yoksa) korumasız üret.
            return $this->generateAndStore($store, $key, $ttlMinutes, $generate);
        }

        try {
            $lock->block($lockWait);
        } catch (Throwable) {
            // Kilit beklerken zaman aşımı → korumasız üret (yine de doğru sonuç).
            return $this->generateAndStore($store, $key, $ttlMinutes, $generate);
        }

        try {
            // Kilidi beklerken başka istek doldurmuş olabilir.
            if (($cached = $this->read($store, $key)) !== null) {
                return ['response' => $cached, 'hit' => true];
            }

            return $this->generateAndStore($store, $key, $ttlMinutes, $generate);
        } finally {
            optional($lock)->release();
        }
    }

    /**
     * @param  Closure():AiResponse  $generate
     * @return array{response:AiResponse, hit:bool}
     */
    private function generateAndStore(CacheRepository $store, string $key, int $ttlMinutes, Closure $generate): array
    {
        $response = $generate();
        // Yalnızca içerik dolu yanıtları sakla (boş/yarım dönüşleri değil).
        if ($response->content !== '') {
            try {
                $store->put($key, $this->serialize($response), now()->addMinutes($ttlMinutes));
            } catch (Throwable $e) {
                Log::warning('AiPromptCache write failed', ['key' => $key, 'error' => $e->getMessage()]);
            }
        }

        return ['response' => $response, 'hit' => false];
    }

    private function read(CacheRepository $store, string $key): ?AiResponse
    {
        try {
            $raw = $store->get($key);
        } catch (Throwable $e) {
            Log::warning('AiPromptCache read failed', ['key' => $key, 'error' => $e->getMessage()]);

            return null;
        }

        return is_array($raw) ? $this->deserialize($raw) : null;
    }

    private function store(): CacheRepository
    {
        $name = $this->config['store'] ?? null;

        return $name ? Cache::store($name) : Cache::store();
    }

    /**
     * Deterministik önbellek anahtarı. ROADMAP:
     *   hash(tenant_id + provider + model + system + prompt + temperature + max_tokens)
     * Görsel içerik base64'ü doğrudan değil, hash'i ile katılır (anahtar kısa kalsın).
     */
    public function cacheKey(string $provider, AiRequest $request, ?Tenant $tenant): string
    {
        $payload = [
            'tenant'      => $tenant?->getKey() ? (string) $tenant->getKey() : 'central',
            'provider'    => $provider,
            'model'       => $request->model,
            'system'      => $request->system ?? '',
            'messages'    => array_map(fn (AiMessage $m) => [
                'role'    => $m->role,
                'content' => $this->normalizeContentForKey($m->content),
            ], $request->messages),
            'temperature' => $request->temperature,
            'max_tokens'  => $request->maxTokens,
        ];

        $hash = hash('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return ($this->config['prefix'] ?? 'ai_resp').':'.$payload['tenant'].':'.$hash;
    }

    /**
     * Görselleri (büyük base64) anahtarda hash'leyerek temsil et.
     *
     * @param  string|array<int,array<string,mixed>>  $content
     * @return string|array<int,array<string,mixed>>
     */
    private function normalizeContentForKey(string|array $content): string|array
    {
        if (is_string($content)) {
            return $content;
        }

        return array_map(function (array $block): array {
            if (($block['type'] ?? null) === 'image' && isset($block['base64'])) {
                return [
                    'type'      => 'image',
                    'mimeType'  => $block['mimeType'] ?? 'image/jpeg',
                    'image_sha' => hash('sha256', (string) $block['base64']),
                ];
            }

            return $block;
        }, $content);
    }

    /**
     * @return array<string,mixed>
     */
    private function serialize(AiResponse $response): array
    {
        return [
            'content'             => $response->content,
            'model'               => $response->model,
            'provider'            => $response->provider,
            'input_tokens'        => $response->usage->inputTokens,
            'output_tokens'       => $response->usage->outputTokens,
            'cached_input_tokens' => $response->usage->cachedInputTokens,
            'stop_reason'         => $response->stopReason,
        ];
    }

    /**
     * @param  array<string,mixed>  $raw
     */
    private function deserialize(array $raw): AiResponse
    {
        return new AiResponse(
            content:    (string) ($raw['content'] ?? ''),
            model:      (string) ($raw['model'] ?? ''),
            provider:   (string) ($raw['provider'] ?? ''),
            usage:      new AiUsage(
                inputTokens:       (int) ($raw['input_tokens'] ?? 0),
                outputTokens:      (int) ($raw['output_tokens'] ?? 0),
                cachedInputTokens: isset($raw['cached_input_tokens']) ? (int) $raw['cached_input_tokens'] : null,
            ),
            stopReason: $raw['stop_reason'] ?? null,
            // Çağıranlar bunun önbellekten geldiğini ayırt edebilsin.
            raw:        ['_cache_hit' => true],
        );
    }
}
