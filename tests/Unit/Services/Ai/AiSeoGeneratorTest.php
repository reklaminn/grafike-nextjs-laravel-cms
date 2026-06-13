<?php

namespace Tests\Unit\Services\Ai;

use App\Models\Page;
use App\Services\Ai\AiManager;
use App\Services\Ai\AiModelRouter;
use App\Services\Ai\AiQuotaService;
use App\Services\Ai\AiSeoGenerator;
use App\Services\Ai\Dtos\AiResponse;
use App\Services\Ai\Dtos\AiUsage as AiUsageDto;
use App\Services\Ai\TenantAiResolver;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiSeoGeneratorTest extends TestCase
{
    private function generator(): AiSeoGenerator
    {
        $config = [
            'default_provider' => 'anthropic',
            'defaults' => ['max_tokens' => 256, 'temperature' => 0.7, 'timeout' => 5],
            'providers' => [
                'anthropic' => [
                    'driver'      => 'anthropic',
                    'api_key'     => 'system-key',
                    'base_url'    => 'https://api.anthropic.com/v1',
                    'api_version' => '2023-06-01',
                    'models'      => ['simple' => 'haiku', 'complex' => 'sonnet'],
                ],
            ],
            'features' => [
                'seo.meta' => ['tier' => 'simple', 'max_tokens' => 300, 'temperature' => 0.3],
            ],
            'fallback' => ['enabled' => false],
        ];

        $manager   = new AiManager($config);
        $tenantR   = new TenantAiResolver($manager);
        $quota     = new class extends AiQuotaService {
            public function __construct() { parent::__construct(plans: [], defaultPlan: 'free', pricing: []); }
            public function assertWithinQuota(?\App\Models\Tenant $tenant, int $estimatedTokens = 0): void {}
            public function recordSuccess(?\App\Models\Tenant $tenant, string $feature, AiResponse $response, bool $byok = false, bool $fallbackUsed = false, array $extraMetadata = []): \App\Models\AiUsage { return new \App\Models\AiUsage(); }
            public function recordFailure(?\App\Models\Tenant $tenant, string $feature, string $provider, string $model, \Throwable $error, bool $byok = false, array $extraMetadata = []): \App\Models\AiUsage { return new \App\Models\AiUsage(); }
        };
        $router    = new AiModelRouter($manager, $tenantR, $quota, new \App\Services\Ai\AiPromptCache($config['cache'] ?? []), $config);

        return new AiSeoGenerator($router);
    }

    private function page(array $attrs = []): Page
    {
        $page = new Page();
        $page->id    = $attrs['id']    ?? 42;
        $page->title = $attrs['title'] ?? 'Diş Hekimi Kuşadası — Estetik Diş Tedavisi';
        $page->slug  = $attrs['slug']  ?? 'dis-hekimi-kusadasi';
        $page->sections_json = $attrs['sections_json'] ?? [
            [
                'type'    => 'hero',
                'content' => [
                    'heading'    => 'Estetik diş kliniği Kuşadası',
                    'subheading' => 'Gülüş tasarımı, implant ve diş beyazlatma alanında uzman ekip.',
                    'image_id'   => 17,
                ],
            ],
            [
                'type'    => 'rich-text',
                'content' => [
                    'body' => 'Kliniğimizde modern ekipman ve sterilizasyon standartları ile hizmet veriyoruz. Estetik diş hekimliği konusunda 15 yıllık deneyimimizle Türkiye genelinde tanınıyoruz.',
                ],
            ],
        ];
        $page->layout_json = $attrs['layout_json'] ?? null;

        return $page;
    }

    private function fakeAnthropicResponse(string $jsonReply): void
    {
        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'model'   => 'haiku',
                'content' => [['type' => 'text', 'text' => $jsonReply]],
                'usage'   => ['input_tokens' => 200, 'output_tokens' => 60],
            ], 200),
        ]);
    }

    public function test_generates_meta_from_clean_json_reply(): void
    {
        $this->fakeAnthropicResponse(json_encode([
            'title'       => 'Diş Hekimi Kuşadası — Estetik Diş Kliniği',
            'description' => 'Kuşadası\'nda implant, gülüş tasarımı ve diş beyazlatma. 15 yıllık deneyim, modern ekipman.',
            'keywords'    => 'diş hekimi kuşadası, implant, gülüş tasarımı, estetik diş, diş beyazlatma',
        ]));

        $meta = $this->generator()->generate($this->page());

        $this->assertNotEmpty($meta['title']);
        $this->assertNotEmpty($meta['description']);
        $this->assertNotEmpty($meta['keywords']);
        $this->assertLessThanOrEqual(70, mb_strlen($meta['title']));
        $this->assertLessThanOrEqual(160, mb_strlen($meta['description']));
    }

    public function test_handles_markdown_fenced_json(): void
    {
        // Some providers ignore "no code blocks" and still wrap in fences.
        $this->fakeAnthropicResponse("```json\n".json_encode([
            'title'       => 'Test başlık',
            'description' => 'Test açıklama metni.',
            'keywords'    => 'a, b, c',
        ])."\n```");

        $meta = $this->generator()->generate($this->page());
        $this->assertSame('Test başlık', $meta['title']);
    }

    public function test_handles_chatty_prefix_before_json(): void
    {
        // Defensive: model sometimes adds "İşte JSON:" preamble.
        $this->fakeAnthropicResponse('Tabii, işte JSON: '.json_encode([
            'title'       => 'Hi',
            'description' => 'desc',
            'keywords'    => 'k',
        ]));

        $meta = $this->generator()->generate($this->page());
        $this->assertSame('Hi', $meta['title']);
        $this->assertSame('desc', $meta['description']);
    }

    public function test_truncates_overlong_title_and_description(): void
    {
        $longTitle = str_repeat('uzun başlık ', 30);             // ~360 chars
        $longDesc  = str_repeat('Çok uzun bir açıklama metni. ', 20); // ~600 chars

        $this->fakeAnthropicResponse(json_encode([
            'title'       => $longTitle,
            'description' => $longDesc,
            'keywords'    => 'a, b, c',
        ]));

        $meta = $this->generator()->generate($this->page());

        $this->assertLessThanOrEqual(70,  mb_strlen($meta['title']));
        $this->assertLessThanOrEqual(160, mb_strlen($meta['description']));
    }

    public function test_normalizes_keywords_dedup_and_caps_count(): void
    {
        $this->fakeAnthropicResponse(json_encode([
            'title'       => 'x',
            'description' => 'y',
            'keywords'    => 'diş hekimi, Diş Hekimi, implant, , gülüş, gülüş, estetik, beyazlatma, kuşadası, klinik, fazla1, fazla2, fazla3',
        ]));

        $meta = $this->generator()->generate($this->page());
        $parts = array_map('trim', explode(',', $meta['keywords']));

        // Deduped (case-insensitive)
        $this->assertSame(count($parts), count(array_unique(array_map('mb_strtolower', $parts))));
        // Capped at 10
        $this->assertLessThanOrEqual(10, count($parts));
        // First case wins
        $this->assertContains('diş hekimi', $parts);
        $this->assertNotContains('Diş Hekimi', $parts);
    }

    public function test_extracts_text_only_from_meaningful_fields(): void
    {
        // Sayfada media_id, url, color gibi non-content fields var; bunlar
        // model'e gitmemeli. Prompt'u inceleyerek doğrula.
        $captured = null;
        Http::fake([
            'api.anthropic.com/*' => function ($request) use (&$captured) {
                $captured = $request->data();
                return Http::response([
                    'model'   => 'haiku',
                    'content' => [['type' => 'text', 'text' => json_encode([
                        'title' => 't', 'description' => 'd', 'keywords' => 'k',
                    ])]],
                    'usage'   => ['input_tokens' => 1, 'output_tokens' => 1],
                ], 200);
            },
        ]);

        $this->generator()->generate($this->page([
            'sections_json' => [[
                'type'    => 'hero',
                'content' => [
                    'heading'  => 'Anahtar başlık X',
                    'body'     => 'Sayfa içeriği metni.',
                    'image_id' => 9001,
                    'url'      => 'https://example.com/should-not-be-included',
                    'color'    => '#ff0000',
                ],
            ]],
        ]));

        $prompt = implode(' ', array_map(fn ($m) => $m['content'] ?? '', $captured['messages'] ?? []));
        $this->assertStringContainsString('Anahtar başlık X', $prompt);
        $this->assertStringContainsString('Sayfa içeriği metni', $prompt);
        $this->assertStringNotContainsString('9001', $prompt);
        $this->assertStringNotContainsString('should-not-be-included', $prompt);
        $this->assertStringNotContainsString('#ff0000', $prompt);
    }

    public function test_works_without_content_blocks_using_title_and_slug(): void
    {
        $this->fakeAnthropicResponse(json_encode([
            'title' => 'Boş sayfa başlığı', 'description' => 'd', 'keywords' => 'k',
        ]));

        $meta = $this->generator()->generate($this->page([
            'sections_json' => null,
            'layout_json'   => null,
        ]));

        $this->assertSame('Boş sayfa başlığı', $meta['title']);
    }

    public function test_invalid_json_reply_throws(): void
    {
        $this->fakeAnthropicResponse('bu hiç JSON değil sadece düz metin');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/geçerli JSON değil/');

        $this->generator()->generate($this->page());
    }
}
