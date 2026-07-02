<?php

namespace Tests\Unit\Services\Ai;

use App\Services\Ai\AiManager;
use App\Services\Ai\AiModelRouter;
use App\Services\Ai\AiQuotaService;
use App\Services\Ai\AiSectionTemplateGenerator;
use App\Services\Ai\Dtos\AiResponse;
use App\Services\Ai\TenantAiResolver;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class AiSectionTemplateGeneratorTest extends TestCase
{
    private function generator(): AiSectionTemplateGenerator
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
                'block.template' => ['tier' => 'complex', 'max_tokens' => 2500, 'temperature' => 0.5],
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
        $router = new AiModelRouter($manager, new TenantAiResolver($manager), $quota, new \App\Services\Ai\AiPromptCache($config['cache'] ?? []), $config);

        return new AiSectionTemplateGenerator($router);
    }

    private function fakeReply(string $text): void
    {
        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'model' => 'sonnet',
                'content' => [['type' => 'text', 'text' => $text]],
                'usage' => ['input_tokens' => 100, 'output_tokens' => 100],
            ], 200),
        ]);
    }

    // ───────────────────────────────────────────────────────────────

    public function test_generates_full_template_with_consistent_schema(): void
    {
        $this->fakeReply(json_encode([
            'name'      => 'Hizmet Kartları 3\'lü',
            'type'      => 'services',
            'variation' => '3-column-cards',
            'html_template' => '<section class="py-12 bg-white"><div class="container mx-auto px-4"><h2 class="text-3xl font-bold text-center text-gray-900">{{title}}</h2><p class="text-center text-gray-600 mt-2">{{subtitle}}</p><div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8"><div class="p-6 border rounded-lg"><img src="{{card_1_icon_url}}" alt="" class="w-12 h-12 mb-4"><h3 class="text-xl font-semibold">{{card_1_title}}</h3><p class="text-gray-600">{{card_1_body}}</p></div></div></div></section>',
            'schema_json' => [
                'title' => ['type' => 'text', 'label' => 'Başlık'],
                'subtitle' => ['type' => 'textarea', 'label' => 'Alt başlık'],
                'card_1_icon_url' => ['type' => 'media_url', 'label' => '1. Kart ikon URL'],
                'card_1_title' => ['type' => 'text', 'label' => '1. Kart başlık'],
                'card_1_body' => ['type' => 'textarea', 'label' => '1. Kart açıklama'],
            ],
            'default_content_json' => [
                'title' => 'Hizmetlerimiz',
                'subtitle' => 'Müşterilerimize sunduğumuz çözümler',
                'card_1_icon_url' => 'https://example.com/icon.svg',
                'card_1_title' => 'Strateji',
                'card_1_body' => 'Hedeflerinize uygun yol haritası.',
            ],
        ]));

        $result = $this->generator()->generate('3 kolonlu hizmet kartı bloğu');

        $this->assertSame('Hizmet Kartları 3\'lü', $result['name']);
        $this->assertSame('services', $result['type']);
        $this->assertSame('3-column-cards', $result['variation']);
        $this->assertStringContainsString('{{title}}', $result['html_template']);
        $this->assertArrayHasKey('title', $result['schema_json']);
        $this->assertSame('Hizmetlerimiz', $result['default_content_json']['title']);
        $this->assertEmpty($result['warnings'], 'no inconsistencies expected');
    }

    public function test_auto_heals_placeholder_missing_from_schema(): void
    {
        // HTML references {{cta_text}} but schema is silent on it.
        $this->fakeReply(json_encode([
            'name' => 'Hero',
            'type' => 'hero',
            'variation' => 'centered',
            'html_template' => '<section><h1>{{title}}</h1><button>{{cta_text}}</button></section>',
            'schema_json' => ['title' => ['type' => 'text', 'label' => 'Başlık']],
            'default_content_json' => ['title' => 'Merhaba'],
        ]));

        $result = $this->generator()->generate('Basit hero');

        $this->assertArrayHasKey('cta_text', $result['schema_json'], 'missing placeholder should be auto-added to schema');
        $this->assertSame('text', $result['schema_json']['cta_text']['type']);
        $this->assertNotEmpty($result['warnings']);
        $this->assertStringContainsString('cta_text', $result['warnings'][0]);
    }

    public function test_flags_schema_keys_unused_in_html(): void
    {
        // Schema has 'subtitle' but HTML doesn't reference it.
        $this->fakeReply(json_encode([
            'name' => 'Hero',
            'type' => 'hero',
            'variation' => 'simple',
            'html_template' => '<section><h1>{{title}}</h1></section>',
            'schema_json' => [
                'title' => ['type' => 'text', 'label' => 'Başlık'],
                'subtitle' => ['type' => 'textarea', 'label' => 'Alt başlık'],
            ],
            'default_content_json' => ['title' => 'a', 'subtitle' => 'b'],
        ]));

        $result = $this->generator()->generate('Hero');

        $this->assertNotEmpty($result['warnings']);
        $this->assertStringContainsString('subtitle', implode(' ', $result['warnings']));
        // subtitle stays in schema (admin may add to HTML later)
        $this->assertArrayHasKey('subtitle', $result['schema_json']);
    }

    public function test_fills_missing_default_content_keys_from_schema(): void
    {
        $this->fakeReply(json_encode([
            'name' => 'Hero',
            'type' => 'hero',
            'variation' => 'x',
            'html_template' => '<h1>{{title}}</h1><p>{{subtitle}}</p>',
            'schema_json' => [
                'title' => ['type' => 'text', 'label' => 'Başlık'],
                'subtitle' => ['type' => 'textarea', 'label' => 'Alt başlık'],
            ],
            'default_content_json' => ['title' => 'A'],     // missing subtitle
        ]));

        $result = $this->generator()->generate('Hero');

        $this->assertArrayHasKey('subtitle', $result['default_content_json']);
        $this->assertSame('', $result['default_content_json']['subtitle'], 'missing default becomes empty string');
    }

    public function test_removes_orphan_default_content_keys(): void
    {
        $this->fakeReply(json_encode([
            'name' => 'Hero',
            'type' => 'hero',
            'variation' => 'x',
            'html_template' => '<h1>{{title}}</h1>',
            'schema_json' => ['title' => ['type' => 'text', 'label' => 'Başlık']],
            'default_content_json' => [
                'title' => 'A',
                'orphan' => 'should disappear',
            ],
        ]));

        $result = $this->generator()->generate('Hero');

        $this->assertArrayNotHasKey('orphan', $result['default_content_json']);
        $this->assertStringContainsString('orphan', implode(' ', $result['warnings']));
    }

    public function test_throws_when_required_fields_missing(): void
    {
        // No html_template
        $this->fakeReply(json_encode([
            'name' => 'Hero',
            'type' => 'hero',
            'schema_json' => ['x' => ['type' => 'text']],
        ]));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/html_template eksik/');

        $this->generator()->generate('X');
    }

    public function test_handles_markdown_fenced_reply(): void
    {
        $this->fakeReply("```json\n".json_encode([
            'name' => 'Fenced',
            'type' => 'hero',
            'variation' => 'x',
            'html_template' => '<h1>{{title}}</h1>',
            'schema_json' => ['title' => ['type' => 'text', 'label' => 'B']],
            'default_content_json' => ['title' => 'A'],
        ])."\n```");

        $result = $this->generator()->generate('Hero');
        $this->assertSame('Fenced', $result['name']);
    }

    public function test_invalid_json_throws(): void
    {
        $this->fakeReply('Tabii, işte:');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/geçerli JSON değil/');
        $this->generator()->generate('x');
    }

    public function test_type_and_variation_are_slugified(): void
    {
        $this->fakeReply(json_encode([
            'name' => 'Hero',
            'type' => 'HERO Block',         // weird casing + space → "hero-block"
            'variation' => '3 Column Cards', // "3-column-cards"
            'html_template' => '<h1>{{title}}</h1>',
            'schema_json' => ['title' => ['type' => 'text', 'label' => 'B']],
            'default_content_json' => ['title' => 'A'],
        ]));

        $result = $this->generator()->generate('Hero');

        $this->assertSame('hero-block', $result['type']);
        $this->assertSame('3-column-cards', $result['variation']);
    }

    public function test_triple_brace_placeholders_recognized(): void
    {
        // {{{rich_body}}} is the raw-HTML escape syntax — should still
        // count as a placeholder for schema cross-check.
        $this->fakeReply(json_encode([
            'name' => 'Rich',
            'type' => 'rich-text',
            'variation' => 'x',
            'html_template' => '<section>{{{rich_body}}}</section>',
            'schema_json' => [
                'rich_body' => ['type' => 'rich-text', 'label' => 'Gövde'],
            ],
            'default_content_json' => ['rich_body' => '<p>İçerik</p>'],
        ]));

        $result = $this->generator()->generate('Rich text block');

        $this->assertEmpty($result['warnings'], 'triple-brace placeholder must match schema cleanly');
    }
}
