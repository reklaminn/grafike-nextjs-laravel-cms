<?php

namespace Tests\Unit\Services\Ai;

use App\Models\Language;
use App\Models\Page;
use App\Models\SeoEntry;
use App\Services\Ai\AiManager;
use App\Services\Ai\AiModelRouter;
use App\Services\Ai\AiQuotaService;
use App\Services\Ai\AiTranslator;
use App\Services\Ai\Dtos\AiResponse;
use App\Services\Ai\TenantAiResolver;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiTranslatorTest extends TestCase
{
    private function translator(): AiTranslator
    {
        $config = [
            'default_provider' => 'anthropic',
            'defaults' => ['max_tokens' => 256, 'temperature' => 0.7, 'timeout' => 5],
            'providers' => [
                'anthropic' => [
                    'driver' => 'anthropic',
                    'api_key' => 'sys',
                    'base_url' => 'https://api.anthropic.com/v1',
                    'api_version' => '2023-06-01',
                    'models' => ['simple' => 'haiku', 'complex' => 'sonnet'],
                ],
            ],
            'features' => [
                'page.translate' => ['tier' => 'simple', 'max_tokens' => 2500, 'temperature' => 0.3],
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
        $router = new AiModelRouter($manager, new TenantAiResolver($manager), $quota, $config);

        return new AiTranslator($router);
    }

    private function lang(int $id, string $name, string $code): Language
    {
        $l = new Language();
        $l->id   = $id;
        $l->name = $name;
        $l->code = $code;

        return $l;
    }

    private function page(array $attrs): Page
    {
        $p = new Page();
        foreach ($attrs as $k => $v) {
            $p->{$k} = $v;
        }
        $p->setRelation('seo', null);
        $p->setRelation('language', null);

        return $p;
    }

    /**
     * Build a Http::response promise wrapped as an Anthropic Messages reply.
     */
    private function anthropicReply(string $textContent): \GuzzleHttp\Promise\PromiseInterface
    {
        return Http::response([
            'model'   => 'haiku',
            'content' => [['type' => 'text', 'text' => $textContent]],
            'usage'   => ['input_tokens' => 50, 'output_tokens' => 30],
        ], 200);
    }

    // ─────────────────────────────────────────────────────────────────

    public function test_translates_page_title_and_seo_fields_in_one_call(): void
    {
        Http::fake([
            'api.anthropic.com/*' => $this->anthropicReply(json_encode([
                'title'           => 'Services',
                'seo_title'       => 'Our Services',
                'seo_description' => 'Premium services in Istanbul',
            ])),
        ]);

        $seo = new SeoEntry();
        $seo->meta_title = 'Hizmetlerimiz | Site';
        $seo->meta_description = 'Premium hizmetler';

        $page = $this->page([
            'id'    => 1,
            'title' => 'Hizmetler',
            'sections_json' => null,
        ]);
        $page->setRelation('language', $this->lang(1, 'Türkçe', 'tr'));
        $page->setRelation('seo', $seo);

        $result = $this->translator()->translatePage(
            $page,
            $this->lang(2, 'İngilizce', 'en'),
        );

        $this->assertSame('Services', $result['fields']['title']);
        $this->assertSame('Our Services', $result['fields']['seo_title']);
        $this->assertSame('Premium services in Istanbul', $result['fields']['seo_description']);
        $this->assertNull($result['sections_json']);
        $this->assertSame('Türkçe', $result['source_lang']);
        $this->assertSame('İngilizce', $result['target_lang']);

        Http::assertSentCount(1);
    }

    public function test_falls_back_to_original_when_ai_omits_a_field(): void
    {
        Http::fake([
            'api.anthropic.com/*' => $this->anthropicReply(json_encode(['title' => 'Translated only title'])),
        ]);

        $seo = new SeoEntry();
        $seo->meta_title = 'Original SEO';

        $page = $this->page(['id' => 1, 'title' => 'Original', 'sections_json' => null]);
        $page->setRelation('language', $this->lang(1, 'TR', 'tr'));
        $page->setRelation('seo', $seo);

        $result = $this->translator()->translatePage($page, $this->lang(2, 'EN', 'en'));

        $this->assertSame('Translated only title', $result['fields']['title']);
        $this->assertSame('Original SEO', $result['fields']['seo_title']);
    }

    public function test_translates_sections_json_text_leaves_in_a_second_batch(): void
    {
        // Two API calls expected: one scalar fields batch, one sections batch.
        // We sequence the responses with Http::sequence().
        Http::fake([
            'api.anthropic.com/*' => Http::sequence()
                ->push([
                    'model'   => 'haiku',
                    'content' => [['type' => 'text', 'text' => json_encode(['title' => 'Home'])]],
                    'usage'   => ['input_tokens' => 1, 'output_tokens' => 1],
                ], 200)
                ->push([
                    'model'   => 'haiku',
                    'content' => [['type' => 'text', 'text' => json_encode([
                        // Sections batch: AI assigns prefix "TR:" to each value
                        't0' => 'TR:Anasayfa',
                        't1' => 'TR:Hizmetlerimiz',
                    ])]],
                    'usage'   => ['input_tokens' => 1, 'output_tokens' => 1],
                ], 200),
        ]);

        $sections = [
            'version' => 2,
            'regions' => [
                'header' => [],
                'body' => [[
                    'columns' => [[
                        'width' => 12,
                        'blocks' => [[
                            'id' => 'b1',
                            'type' => 'hero',
                            'section_template_id' => 11,
                            'image_id' => 7,
                            'url' => 'https://x.test',
                            'content' => [
                                'title'    => 'Anasayfa',
                                'subtitle' => 'Hizmetlerimiz',
                                'image_id' => 99,
                                'color'    => '#fff',
                            ],
                        ]],
                    ]],
                ]],
                'footer' => [],
            ],
        ];

        $page = $this->page(['id' => 1, 'title' => 'Anasayfa', 'sections_json' => $sections]);
        $page->setRelation('language', $this->lang(1, 'TR', 'tr'));

        $result = $this->translator()->translatePage($page, $this->lang(2, 'EN', 'en'));

        $this->assertSame('Home', $result['fields']['title']);

        $block = $result['sections_json']['regions']['body'][0]['columns'][0]['blocks'][0];
        $this->assertStringStartsWith('TR:', $block['content']['title']);
        $this->assertStringStartsWith('TR:', $block['content']['subtitle']);
        $this->assertSame(99, $block['content']['image_id'], 'image_id must NOT be translated');
        $this->assertSame('#fff', $block['content']['color'], 'color must NOT be translated');
        $this->assertSame(7, $block['image_id']);
        $this->assertSame('https://x.test', $block['url']);
        $this->assertSame('b1', $block['id']);
        $this->assertSame(11, $block['section_template_id']);

        Http::assertSentCount(2);
    }

    public function test_empty_sections_skipped_no_call(): void
    {
        Http::fake([
            'api.anthropic.com/*' => $this->anthropicReply(json_encode(['title' => 'X'])),
        ]);

        $page = $this->page(['id' => 1, 'title' => 'Orig', 'sections_json' => null]);
        $page->setRelation('language', $this->lang(1, 'TR', 'tr'));

        $this->translator()->translatePage($page, $this->lang(2, 'EN', 'en'));

        Http::assertSentCount(1);
    }

    public function test_handles_markdown_fenced_reply(): void
    {
        Http::fake([
            'api.anthropic.com/*' => $this->anthropicReply(
                "```json\n".json_encode(['title' => 'Fenced'])."\n```"
            ),
        ]);

        $page = $this->page(['id' => 1, 'title' => 'Original', 'sections_json' => null]);
        $page->setRelation('language', $this->lang(1, 'TR', 'tr'));

        $result = $this->translator()->translatePage($page, $this->lang(2, 'EN', 'en'));
        $this->assertSame('Fenced', $result['fields']['title']);
    }

    public function test_invalid_json_reply_throws(): void
    {
        Http::fake([
            'api.anthropic.com/*' => $this->anthropicReply('totally not json'),
        ]);

        $page = $this->page(['id' => 1, 'title' => 'Orig', 'sections_json' => null]);
        $page->setRelation('language', $this->lang(1, 'TR', 'tr'));

        $this->expectException(\RuntimeException::class);
        $this->translator()->translatePage($page, $this->lang(2, 'EN', 'en'));
    }

    public function test_target_language_name_appears_in_system_prompt(): void
    {
        Http::fake([
            'api.anthropic.com/*' => $this->anthropicReply(json_encode(['title' => 'X'])),
        ]);

        $page = $this->page(['id' => 1, 'title' => 'Test', 'sections_json' => null]);
        $page->setRelation('language', $this->lang(1, 'Türkçe', 'tr'));

        $this->translator()->translatePage($page, $this->lang(2, 'Almanca', 'de'));

        Http::assertSent(function ($request) {
            $sys = $request->data()['system'] ?? '';
            return str_contains($sys, 'Türkçe') && str_contains($sys, 'Almanca');
        });
    }

    public function test_short_strings_are_skipped_in_sections(): void
    {
        // Sections payload contains a short "tag" → must NOT be in the
        // translation batch user prompt.
        Http::fake([
            'api.anthropic.com/*' => Http::sequence()
                ->push([
                    'model'   => 'haiku',
                    'content' => [['type' => 'text', 'text' => json_encode(['title' => 'T-en'])]],
                    'usage'   => ['input_tokens' => 1, 'output_tokens' => 1],
                ], 200)
                ->push([
                    'model'   => 'haiku',
                    'content' => [['type' => 'text', 'text' => json_encode(['t0' => 'TR:Bir başlık'])]],
                    'usage'   => ['input_tokens' => 1, 'output_tokens' => 1],
                ], 200),
        ]);

        $sections = [
            'version' => 2,
            'regions' => ['header' => [], 'body' => [[
                'columns' => [[
                    'width' => 12,
                    'blocks' => [[
                        'id' => 'b1',
                        'type' => 'spacer',
                        'content' => [
                            'tag'     => 'x',            // < 2 chars → skip
                            'heading' => 'Bir başlık',
                        ],
                    ]],
                ]],
            ]], 'footer' => []],
        ];

        $page = $this->page(['id' => 1, 'title' => 'T', 'sections_json' => $sections]);
        $page->setRelation('language', $this->lang(1, 'TR', 'tr'));

        $this->translator()->translatePage($page, $this->lang(2, 'EN', 'en'));

        // Assert the 2nd call's user prompt contains "Bir başlık" but NOT "x"
        // as a translatable value.
        Http::assertSent(function ($request) {
            $msgs = $request->data()['messages'] ?? [];
            $userMsg = collect($msgs)->firstWhere('role', 'user')['content'] ?? '';
            // We can only see batches that contain block content (skip first call)
            if (! str_contains($userMsg, 'başlık')) return false;
            return str_contains($userMsg, 'Bir başlık')
                && ! preg_match('/"t[0-9]+":\s*"x"/', $userMsg);
        });
    }
}
