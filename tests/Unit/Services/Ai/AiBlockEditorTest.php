<?php

namespace Tests\Unit\Services\Ai;

use App\Services\Ai\AiBlockEditor;
use App\Services\Ai\AiManager;
use App\Services\Ai\AiModelRouter;
use App\Services\Ai\AiQuotaService;
use App\Services\Ai\Dtos\AiResponse;
use App\Services\Ai\TenantAiResolver;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use RuntimeException;
use Tests\TestCase;

class AiBlockEditorTest extends TestCase
{
    private function editor(): AiBlockEditor
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
                'block.edit' => ['tier' => 'simple', 'max_tokens' => 600, 'temperature' => 0.7],
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

        return new AiBlockEditor($router);
    }

    private function fakeReply(string $jsonReply): void
    {
        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'model'   => 'haiku',
                'content' => [['type' => 'text', 'text' => $jsonReply]],
                'usage'   => ['input_tokens' => 100, 'output_tokens' => 30],
            ], 200),
        ]);
    }

    public function test_shorten_action_returns_rewritten_text(): void
    {
        $this->fakeReply(json_encode([
            'heading' => 'Estetik diş',
            'body'    => 'Modern ekipmanla kaliteli hizmet.',
        ]));

        $out = $this->editor()->edit(
            content: ['heading' => 'Estetik diş kliniği', 'body' => 'Uzun bir açıklama metni…'],
            schema: null,
            action: 'shorten',
        );

        $this->assertSame('Estetik diş', $out['heading']);
        $this->assertSame('Modern ekipmanla kaliteli hizmet.', $out['body']);
    }

    public function test_preserves_non_text_fields_even_if_ai_returns_them(): void
    {
        // AI tries to overwrite image_id, but service must keep original.
        $this->fakeReply(json_encode([
            'heading'  => 'Yeni başlık',
            'image_id' => 9999,            // mischievous AI
            'url'      => 'https://hax.bad', // mischievous AI
        ]));

        $out = $this->editor()->edit(
            content: ['heading' => 'X', 'image_id' => 17, 'url' => 'https://safe.example/i.jpg'],
            schema: null,
            action: 'professional',
        );

        $this->assertSame('Yeni başlık', $out['heading']);
        $this->assertSame(17, $out['image_id'], 'image_id must NEVER be mutated by AI');
        $this->assertSame('https://safe.example/i.jpg', $out['url']);
    }

    public function test_schema_aware_mode_only_rewrites_text_typed_fields(): void
    {
        $this->fakeReply(json_encode([
            'title' => 'AI tarafından düzenlenmiş başlık',
            'body'  => 'Yeni body',
        ]));

        $schema = [
            ['key' => 'title',    'type' => 'string'],
            ['key' => 'body',     'type' => 'longtext'],
            ['key' => 'image_id', 'type' => 'media_id'],
        ];

        $out = $this->editor()->edit(
            content: ['title' => 'Eski', 'body' => 'Eski body', 'image_id' => 7],
            schema: $schema,
            action: 'rephrase',
        );

        $this->assertSame('AI tarafından düzenlenmiş başlık', $out['title']);
        $this->assertSame('Yeni body', $out['body']);
        $this->assertSame(7, $out['image_id'], 'media_id schema field must not be touched');
    }

    public function test_rejects_type_mismatch_from_ai(): void
    {
        // AI returns string where original was an array — must reject and
        // keep original.
        $this->fakeReply(json_encode([
            'bullets' => 'this should have stayed an array',
        ]));

        $out = $this->editor()->edit(
            content: ['bullets' => ['a', 'b', 'c']],
            schema: null,
            action: 'rephrase',
        );

        $this->assertSame(['a', 'b', 'c'], $out['bullets']);
    }

    public function test_custom_action_requires_prompt(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/custom_prompt zorunlu/');
        $this->editor()->edit(
            content: ['x' => 'y'],
            schema: null,
            action: 'custom',
            customPrompt: '',
        );
    }

    public function test_unknown_action_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Bilinmeyen eylem/');
        $this->editor()->edit(
            content: ['x' => 'y'],
            schema: null,
            action: 'shoutfromthemountain',
        );
    }

    public function test_returns_original_when_content_has_no_editable_fields(): void
    {
        Http::fake();

        $out = $this->editor()->edit(
            content: ['image_id' => 1, 'url' => 'https://x'],
            schema: null,
            action: 'shorten',
        );

        $this->assertSame(['image_id' => 1, 'url' => 'https://x'], $out);
        Http::assertNothingSent();
    }

    public function test_translate_action_passes_locale_instruction_in_prompt(): void
    {
        $capturedBody = null;
        Http::fake([
            'api.anthropic.com/*' => function ($request) use (&$capturedBody) {
                $capturedBody = $request->data();
                return Http::response([
                    'model' => 'haiku',
                    'content' => [['type' => 'text', 'text' => json_encode(['heading' => 'Hello'])]],
                    'usage' => ['input_tokens' => 1, 'output_tokens' => 1],
                ], 200);
            },
        ]);

        $this->editor()->edit(
            content: ['heading' => 'Merhaba'],
            schema: null,
            action: 'translate_en',
        );

        $userPrompt = collect($capturedBody['messages'] ?? [])
            ->where('role', 'user')
            ->pluck('content')
            ->implode(' ');

        $this->assertStringContainsString('İngilizceye çevir', $userPrompt);
    }

    public function test_parses_markdown_fenced_json_reply(): void
    {
        $this->fakeReply("```json\n".json_encode(['heading' => 'Fenced reply'])."\n```");

        $out = $this->editor()->edit(
            content: ['heading' => 'Eski'],
            schema: null,
            action: 'professional',
        );

        $this->assertSame('Fenced reply', $out['heading']);
    }

    public function test_throws_on_invalid_json(): void
    {
        $this->fakeReply('herşey güzel ama JSON değil');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/geçerli JSON değil/');

        $this->editor()->edit(
            content: ['heading' => 'x'],
            schema: null,
            action: 'shorten',
        );
    }

    public function test_custom_prompt_max_length(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/çok uzun/');
        $this->editor()->edit(
            content: ['x' => 'y'],
            schema: null,
            action: 'custom',
            customPrompt: str_repeat('a', 501),
        );
    }
}
