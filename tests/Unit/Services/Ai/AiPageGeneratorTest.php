<?php

namespace Tests\Unit\Services\Ai;

use App\Models\SectionTemplate;
use App\Services\Ai\AiManager;
use App\Services\Ai\AiModelRouter;
use App\Services\Ai\AiPageGenerator;
use App\Services\Ai\AiQuotaService;
use App\Services\Ai\Dtos\AiResponse;
use App\Services\Ai\TenantAiResolver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class AiPageGeneratorTest extends TestCase
{
    private function generator(): AiPageGenerator
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
                'page.create' => ['tier' => 'complex', 'max_tokens' => 2000, 'temperature' => 0.5],
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
        $router  = new AiModelRouter($manager, new TenantAiResolver($manager), $quota, $config);

        return new AiPageGenerator($router);
    }

    /**
     * Lightweight stub SectionTemplate — no DB required. Mirrors only
     * the attributes the generator actually reads.
     */
    private function stubTemplate(int $id, string $type, array $schema, string $variation = '', string $name = ''): SectionTemplate
    {
        $tpl = new SectionTemplate();
        $tpl->id          = $id;
        $tpl->type        = $type;
        $tpl->variation   = $variation;
        $tpl->name        = $name ?: ($type.($variation ? '/'.$variation : ''));
        $tpl->render_mode = 'html';
        $tpl->schema_json = $schema;

        return $tpl;
    }

    private function defaultTemplates(): Collection
    {
        return collect([
            $this->stubTemplate(11, 'hero', [
                'title'    => ['type' => 'text'],
                'subtitle' => ['type' => 'textarea'],
                'cta_url'  => ['type' => 'text'],
            ], 'porto-split', 'Porto Hero'),
            $this->stubTemplate(22, 'features', [
                'title'       => ['type' => 'text'],
                'description' => ['type' => 'textarea'],
            ], 'porto-icons', 'Porto Features'),
            $this->stubTemplate(33, 'cta', [
                'headline' => ['type' => 'text'],
                'button'   => ['type' => 'text'],
            ], '', 'Call To Action'),
        ]);
    }

    private function fakeReply(string $json): void
    {
        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'model'   => 'sonnet',
                'content' => [['type' => 'text', 'text' => $json]],
                'usage'   => ['input_tokens' => 800, 'output_tokens' => 250],
            ], 200),
        ]);
    }

    // ────────────────────────────────────────────────────────────────

    public function test_generates_valid_sections_json_with_picked_templates(): void
    {
        $this->fakeReply(json_encode([
            'title' => 'Diş Kliniği Anasayfa',
            'slug'  => 'dis-klinigi-anasayfa',
            'sections' => [
                ['template_id' => 11, 'content' => ['title' => 'Estetik Diş', 'subtitle' => 'Profesyonel ekip', 'cta_url' => '/iletisim']],
                ['template_id' => 22, 'content' => ['title' => 'Hizmetlerimiz', 'description' => 'İmplant, gülüş tasarımı']],
                ['template_id' => 33, 'content' => ['headline' => 'Hemen randevu alın', 'button' => 'Ara']],
            ],
        ]));

        $result = $this->generator()->generate(
            prompt: 'Diş kliniği için anasayfa',
            availableTemplates: $this->defaultTemplates(),
        );

        $this->assertSame('Diş Kliniği Anasayfa', $result['title']);
        $this->assertSame('dis-klinigi-anasayfa', $result['slug']);
        $this->assertSame([11, 22, 33], $result['picked_template_ids']);

        // sections_json should be the region-based v2 shape
        $sj = $result['sections_json'];
        $this->assertSame(2, $sj['version']);
        $this->assertArrayHasKey('regions', $sj);
        $this->assertCount(3, $sj['regions']['body'], '3 rows for 3 blocks (single-column wrapper)');

        // First block: type + section_template_id propagated; content filtered to schema
        $firstBlock = $sj['regions']['body'][0]['columns'][0]['blocks'][0];
        $this->assertSame(11, $firstBlock['section_template_id']);
        $this->assertSame('hero', $firstBlock['type']);
        $this->assertSame('Estetik Diş', $firstBlock['content']['title']);
    }

    public function test_drops_hallucinated_template_ids(): void
    {
        $this->fakeReply(json_encode([
            'title' => 'Test',
            'slug'  => 'test',
            'sections' => [
                ['template_id' => 11, 'content' => ['title' => 'OK', 'subtitle' => 'OK', 'cta_url' => '/x']],
                ['template_id' => 9999, 'content' => ['anything' => 'should be dropped']], // hallucinated
                ['template_id' => 22, 'content' => ['title' => 'F', 'description' => 'D']],
            ],
        ]));

        $result = $this->generator()->generate(
            prompt: 'Test',
            availableTemplates: $this->defaultTemplates(),
        );

        $this->assertSame([11, 22], $result['picked_template_ids']);
        $this->assertCount(2, $result['sections_json']['regions']['body']);
    }

    public function test_filters_content_to_schema_declared_keys(): void
    {
        // AI returns an extra "rogue_key" that's not in the schema —
        // should be silently dropped.
        $this->fakeReply(json_encode([
            'title' => 'Test',
            'slug'  => 'test',
            'sections' => [
                ['template_id' => 11, 'content' => [
                    'title'      => 'OK',
                    'subtitle'   => 'OK',
                    'cta_url'    => '/x',
                    'rogue_key'  => 'should not appear',
                    'image_id'   => 9999,           // not in schema
                ]],
            ],
        ]));

        $result = $this->generator()->generate(
            prompt: 'X',
            availableTemplates: $this->defaultTemplates(),
        );

        $content = $result['sections_json']['regions']['body'][0]['columns'][0]['blocks'][0]['content'];
        $this->assertArrayHasKey('title', $content);
        $this->assertArrayHasKey('subtitle', $content);
        $this->assertArrayHasKey('cta_url', $content);
        $this->assertArrayNotHasKey('rogue_key', $content);
        $this->assertArrayNotHasKey('image_id', $content);
    }

    public function test_throws_when_no_valid_blocks_selected(): void
    {
        $this->fakeReply(json_encode([
            'title' => 'x', 'slug' => 'x',
            'sections' => [
                ['template_id' => 9999, 'content' => []],   // all hallucinated
                ['template_id' => 8888, 'content' => []],
            ],
        ]));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/hiç geçerli blok/');

        $this->generator()->generate(
            prompt: 'X',
            availableTemplates: $this->defaultTemplates(),
        );
    }

    public function test_throws_when_no_templates_available(): void
    {
        Http::fake();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/aktif blok şablonu/');

        $this->generator()->generate(
            prompt: 'X',
            availableTemplates: collect(),
        );

        Http::assertNothingSent();
    }

    public function test_handles_markdown_fenced_json_reply(): void
    {
        $this->fakeReply("```json\n".json_encode([
            'title' => 'Fenced', 'slug' => 'fenced',
            'sections' => [
                ['template_id' => 11, 'content' => ['title' => 'A', 'subtitle' => 'B', 'cta_url' => '/c']],
            ],
        ])."\n```");

        $result = $this->generator()->generate(
            prompt: 'x',
            availableTemplates: $this->defaultTemplates(),
        );

        $this->assertSame('Fenced', $result['title']);
    }

    public function test_normalizes_slug_when_ai_omits_it(): void
    {
        $this->fakeReply(json_encode([
            'title' => 'Çağdaş İçerikli Sayfa Önerisi',
            'slug'  => '',   // empty → must derive from title
            'sections' => [
                ['template_id' => 22, 'content' => ['title' => 'T', 'description' => 'D']],
            ],
        ]));

        $result = $this->generator()->generate(
            prompt: 'x',
            availableTemplates: $this->defaultTemplates(),
        );

        $this->assertNotEmpty($result['slug']);
        $this->assertStringNotContainsString(' ', $result['slug']);
        $this->assertStringNotContainsString('ç', $result['slug'], 'TR chars should be ASCII-ified');
        $this->assertStringNotContainsString('ı', $result['slug']);
    }

    public function test_catalog_is_passed_to_the_model_in_prompt(): void
    {
        $captured = null;
        Http::fake([
            'api.anthropic.com/*' => function ($request) use (&$captured) {
                $captured = $request->data();
                return Http::response([
                    'model'   => 'sonnet',
                    'content' => [['type' => 'text', 'text' => json_encode([
                        'title' => 't', 'slug' => 't',
                        'sections' => [['template_id' => 11, 'content' => ['title' => 'a', 'subtitle' => 'b', 'cta_url' => '/c']]],
                    ])]],
                    'usage'   => ['input_tokens' => 1, 'output_tokens' => 1],
                ], 200);
            },
        ]);

        $this->generator()->generate(
            prompt: 'Diş kliniği',
            availableTemplates: $this->defaultTemplates(),
        );

        $userPrompt = collect($captured['messages'] ?? [])
            ->where('role', 'user')
            ->pluck('content')
            ->implode(' ');

        $this->assertStringContainsString('id=11', $userPrompt);
        $this->assertStringContainsString('id=22', $userPrompt);
        $this->assertStringContainsString('id=33', $userPrompt);
        $this->assertStringContainsString('type=hero', $userPrompt);
        $this->assertStringContainsString('title(text)', $userPrompt);
    }

    public function test_throws_on_invalid_json_reply(): void
    {
        $this->fakeReply('not json');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/geçerli JSON değil/');

        $this->generator()->generate(
            prompt: 'x',
            availableTemplates: $this->defaultTemplates(),
        );
    }

    public function test_locale_appears_in_system_prompt(): void
    {
        $captured = null;
        Http::fake([
            'api.anthropic.com/*' => function ($request) use (&$captured) {
                $captured = $request->data();
                return Http::response([
                    'model'   => 'sonnet',
                    'content' => [['type' => 'text', 'text' => json_encode([
                        'title' => 't', 'slug' => 't',
                        'sections' => [['template_id' => 11, 'content' => ['title' => 'a', 'subtitle' => 'b', 'cta_url' => '/c']]],
                    ])]],
                    'usage'   => ['input_tokens' => 1, 'output_tokens' => 1],
                ], 200);
            },
        ]);

        $this->generator()->generate(
            prompt: 'X',
            availableTemplates: $this->defaultTemplates(),
            locale: 'en',
        );

        $systemPrompt = $captured['system'] ?? '';
        $this->assertStringContainsString('İngilizce', $systemPrompt);
    }
}
