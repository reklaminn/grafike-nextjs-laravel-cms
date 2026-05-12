<?php

namespace App\Console\Commands;

use App\Services\Ai\AiManager;
use App\Services\Ai\AiModelRouter;
use App\Services\Ai\Dtos\AiMessage;
use App\Services\Ai\Dtos\AiRequest;
use App\Services\Ai\Exceptions\AiProviderException;
use Illuminate\Console\Command;
use Throwable;

/**
 * Smoke-test command for the AI abstraction layer.
 *
 * Two modes:
 *   - Raw mode (default): exercise a specific provider directly via
 *     AiManager. Useful for verifying credentials and model availability.
 *   - Feature mode (--feature=…): route through AiModelRouter so that
 *     feature defaults + fallback chain are applied. Mirrors production.
 *
 * Usage:
 *   php artisan ai:ping
 *   php artisan ai:ping --provider=openai
 *   php artisan ai:ping --tier=complex
 *   php artisan ai:ping --feature=seo.meta --prompt="Sayfa içeriği: …"
 *   php artisan ai:ping --feature=page.create --prompt="Klinik anasayfası"
 */
class AiPingCommand extends Command
{
    protected $signature = 'ai:ping
        {--provider= : Provider name (anthropic, openai, openrouter). Defaults to config(ai.default_provider).}
        {--tier=simple : Model tier (simple|complex). Ignored when --feature is given (feature config decides).}
        {--model= : Explicit model name; overrides --tier resolution.}
        {--feature= : Route through AiModelRouter using a registered feature key (e.g. seo.meta).}
        {--prompt=Tek cümleyle merhaba de. : User prompt to send.}
        {--system= : Optional system prompt.}
        {--max-tokens=200 : Max output tokens (raw mode only; feature mode uses feature config).}';

    protected $description = 'Sends a single prompt through the AI abstraction layer to verify provider config and connectivity.';

    public function handle(AiManager $ai, AiModelRouter $router): int
    {
        return $this->option('feature')
            ? $this->runFeature($router)
            : $this->runRaw($ai);
    }

    private function runRaw(AiManager $ai): int
    {
        $provider = $this->option('provider') ?: $ai->defaultProvider();
        $tier     = $this->option('tier');
        $model    = $this->option('model') ?: $ai->resolveModel($tier, $provider);

        $this->line('→ mode:     <info>raw</info>');
        $this->line("→ provider: <info>{$provider}</info>");
        $this->line("→ model:    <info>{$model}</info>");
        $this->line('→ prompt:   '.$this->option('prompt'));
        $this->newLine();

        try {
            $response = $ai->provider($provider)->generate(new AiRequest(
                model:       $model,
                messages:    [AiMessage::user((string) $this->option('prompt'))],
                system:      $this->option('system') ?: null,
                maxTokens:   (int) $this->option('max-tokens'),
                temperature: 0.7,
                metadata:    ['source' => 'ai:ping'],
            ));
        } catch (AiProviderException $e) {
            return $this->reportProviderError($e);
        } catch (Throwable $e) {
            $this->error('Unexpected error: '.$e->getMessage());

            return self::FAILURE;
        }

        return $this->reportResponse($response);
    }

    private function runFeature(AiModelRouter $router): int
    {
        $feature = (string) $this->option('feature');
        $tier    = $this->option('tier') !== 'simple' ? $this->option('tier') : null; // only honour explicit override

        try {
            $resolved = $router->resolve($feature, $tier);
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->line('→ mode:     <info>feature</info>');
        $this->line("→ feature:  <info>{$feature}</info>");
        $this->line("→ provider: <info>{$resolved['provider']}</info>");
        $this->line("→ model:    <info>{$resolved['model']}</info> (tier={$resolved['tier']}, max_tokens={$resolved['max_tokens']})");
        $this->line('→ prompt:   '.$this->option('prompt'));
        $this->newLine();

        try {
            $response = $router->generate(
                feature: $feature,
                prompt: (string) $this->option('prompt'),
                system: $this->option('system') ?: null,
                tier: $tier,
                metadata: ['source' => 'ai:ping'],
            );
        } catch (AiProviderException $e) {
            return $this->reportProviderError($e);
        } catch (Throwable $e) {
            $this->error('Unexpected error: '.$e->getMessage());

            return self::FAILURE;
        }

        return $this->reportResponse($response);
    }

    private function reportProviderError(AiProviderException $e): int
    {
        $this->error('AI provider error: '.$e->getMessage());
        if ($e->responseBody) {
            $this->line('Response body:');
            $this->line(json_encode($e->responseBody, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        return self::FAILURE;
    }

    private function reportResponse(\App\Services\Ai\Dtos\AiResponse $response): int
    {
        $this->info('── response ──');
        $this->line($response->content);
        $this->newLine();
        $this->line(sprintf(
            '<comment>provider=%s  model=%s  tokens: in=%d  out=%d  total=%d  stop=%s</comment>',
            $response->provider,
            $response->model,
            $response->usage->inputTokens,
            $response->usage->outputTokens,
            $response->usage->totalTokens(),
            $response->stopReason ?? '-',
        ));

        return self::SUCCESS;
    }
}
