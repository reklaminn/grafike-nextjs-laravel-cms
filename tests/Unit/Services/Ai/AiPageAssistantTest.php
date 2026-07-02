<?php

namespace Tests\Unit\Services\Ai;

use App\Services\Ai\AiBlockEditor;
use App\Services\Ai\AiManager;
use App\Services\Ai\AiModelRouter;
use App\Services\Ai\AiPageAssistant;
use App\Services\Ai\AiPromptCache;
use App\Services\Ai\AiQuotaService;
use App\Services\Ai\Dtos\AiResponse;
use App\Services\Ai\TenantAiResolver;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class AiPageAssistantTest extends TestCase
{
    private function assistant(): AiPageAssistant
    {
        $config = [
            'default_provider' => 'anthropic',
            'defaults' => ['max_tokens' => 256, 'temperature' => 0.7, 'timeout' => 5],
            'providers' => [
                'anthropic' => [
                    'driver' => 'anthropic',
                    'api_key' => 'system-key',
                    'base_url' => 'https://api.anthropic.com/v1',
                    'api_version' => '2023-06-01',
                    'models' => ['simple' => 'haiku', 'complex' => 'sonnet'],
                ],
            ],
            'features' => [
                'page.assist' => ['tier' => 'complex', 'max_tokens' => 2000, 'temperature' => 0.6],
            ],
            'fallback' => ['enabled' => false],
        ];

        $manager = new AiManager($config);
        $quota   = new class extends AiQuotaService {
            public function __construct() { parent::__construct(plans: [], defaultPlan: 'free', pricing: []); }
            public function assertWithinQuota(?\App\Models\Tenant $tenant, int $estimatedTokens = 0): void {}
            public function recordSuccess(?\App\Models\Tenant $tenant, string $feature, AiResponse $response, bool $byok = false, bool $fallbackUsed = false, array $extraMetadata = []): \App\Models\AiUsage { return new \App\Models\AiUsage(); }
            public function recordFailure(?\App\Models\Tenant $tenant, string $feature, string $provider, string $model, \Throwable $error, bool $byok = false, array $extraMetadata = []): \App\Models\AiUsage { return new \App\Models\AiUsage(); }
        };
        $router = new AiModelRouter($manager, new TenantAiResolver($manager), $quota, new AiPromptCache($config['cache'] ?? []), $config);

        return new AiPageAssistant($router, new AiBlockEditor($router));
    }

    private function fakeReply(string $json): void
    {
        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'model'   => 'sonnet',
                'content' => [['type' => 'text', 'text' => $json]],
                'usage'   => ['input_tokens' => 600, 'output_tokens' => 200],
            ], 200),
        ]);
    }

    private function blocks(): array
    {
        return [
            ['ref' => 1, 'type' => 'hero',     'name' => 'Hero',       'content' => ['title' => 'Eski Başlık', 'subtitle' => 'Alt metin', 'cta_url' => '/iletisim']],
            ['ref' => 2, 'type' => 'features', 'name' => 'Özellikler', 'content' => ['title' => 'Hizmetler']],
            ['ref' => 3, 'type' => 'cta',      'name' => 'CTA',        'content' => ['headline' => 'Hemen ara']],
        ];
    }

    // ────────────────────────────────────────────────────────────────

    public function test_edit_op_resolves_text_changes_and_skips_url_fields(): void
    {
        $this->fakeReply(json_encode([
            'summary' => 'Başlık satış odaklı yapıldı.',
            'operations' => [
                ['op' => 'edit', 'ref' => 1, 'fields' => [
                    'title'   => 'Yeni Satış Odaklı Başlık',
                    'cta_url' => '/degismemeli',   // URL — AI'ya verilmedi, merge etmemeli
                ]],
            ],
        ]));

        $plan = $this->assistant()->plan('başlıkları satış odaklı yap', $this->blocks());

        $this->assertSame('Başlık satış odaklı yapıldı.', $plan['summary']);
        $this->assertCount(1, $plan['operations']);

        $op = $plan['operations'][0];
        $this->assertSame('edit', $op['op']);
        $this->assertSame(1, $op['ref']);
        $this->assertSame('Hero', $op['name']);

        // Sadece title değişmeli; cta_url (URL alanı) plana hiç girmemeli.
        $keys = array_column($op['changes'], 'key');
        $this->assertContains('title', $keys);
        $this->assertNotContains('cta_url', $keys);

        $titleChange = collect($op['changes'])->firstWhere('key', 'title');
        $this->assertSame('Eski Başlık', $titleChange['old']);
        $this->assertSame('Yeni Satış Odaklı Başlık', $titleChange['new']);
    }

    public function test_unknown_ref_is_dropped_with_warning(): void
    {
        $this->fakeReply(json_encode([
            'summary' => 'x',
            'operations' => [
                ['op' => 'edit', 'ref' => 99, 'fields' => ['title' => 'yok']],
            ],
        ]));

        $plan = $this->assistant()->plan('düzenle', $this->blocks());

        $this->assertSame([], $plan['operations']);
        $this->assertNotEmpty($plan['warnings']);
        $this->assertStringContainsString('#99', implode(' ', $plan['warnings']));
    }

    public function test_reorder_is_completed_to_full_permutation(): void
    {
        // AI sadece [2,1] verir; eksik ref (3) sona eklenmeli.
        $this->fakeReply(json_encode([
            'summary' => 'sıralama',
            'operations' => [
                ['op' => 'reorder', 'order' => [2, 1]],
            ],
        ]));

        $plan = $this->assistant()->plan('hizmetleri öne al', $this->blocks());

        $reorder = collect($plan['operations'])->firstWhere('op', 'reorder');
        $this->assertNotNull($reorder);
        $this->assertSame([2, 1, 3], $reorder['order']);
        $this->assertSame(['Özellikler', 'Hero', 'CTA'], array_column($reorder['order_named'], 'name'));
    }

    public function test_remove_op_passes_through_with_name(): void
    {
        $this->fakeReply(json_encode([
            'summary' => 'kaldır',
            'operations' => [
                ['op' => 'remove', 'ref' => 3],
            ],
        ]));

        $plan = $this->assistant()->plan('cta bloğunu kaldır', $this->blocks());

        $remove = collect($plan['operations'])->firstWhere('op', 'remove');
        $this->assertNotNull($remove);
        $this->assertSame(3, $remove['ref']);
        $this->assertSame('CTA', $remove['name']);
    }

    public function test_no_op_edit_produces_no_operation(): void
    {
        // AI aynı değeri döndürürse değişiklik yok → edit op üretilmemeli.
        $this->fakeReply(json_encode([
            'summary' => 'değişiklik yok',
            'operations' => [
                ['op' => 'edit', 'ref' => 1, 'fields' => ['title' => 'Eski Başlık']],
            ],
        ]));

        $plan = $this->assistant()->plan('aynısını yaz', $this->blocks());

        $this->assertSame([], $plan['operations']);
        $this->assertNotEmpty($plan['warnings']);
    }

    public function test_throws_on_invalid_json(): void
    {
        $this->fakeReply('kesinlikle json değil');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/geçerli JSON değil/');

        $this->assistant()->plan('x', $this->blocks());
    }

    public function test_block_catalog_with_editable_fields_reaches_the_model(): void
    {
        $captured = null;
        Http::fake([
            'api.anthropic.com/*' => function ($request) use (&$captured) {
                $captured = $request->data();
                return Http::response([
                    'model'   => 'sonnet',
                    'content' => [['type' => 'text', 'text' => json_encode(['summary' => 's', 'operations' => []])]],
                    'usage'   => ['input_tokens' => 1, 'output_tokens' => 1],
                ], 200);
            },
        ]);

        $this->assistant()->plan('düzenle', $this->blocks());

        $userPrompt = collect($captured['messages'] ?? [])
            ->where('role', 'user')
            ->pluck('content')
            ->implode(' ');

        $this->assertStringContainsString('#1', $userPrompt);
        $this->assertStringContainsString('Hero', $userPrompt);
        $this->assertStringContainsString('Eski Başlık', $userPrompt);
        // URL alanı katalogda yer almamalı (AI'ya hiç gösterilmez).
        $this->assertStringNotContainsString('/iletisim', $userPrompt);
    }
}
