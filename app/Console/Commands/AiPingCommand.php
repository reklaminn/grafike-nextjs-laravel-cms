<?php

namespace App\Console\Commands;

use App\Services\Ai\AiManager;
use App\Services\Ai\Dtos\AiRequest;
use App\Services\Ai\Exceptions\AiProviderException;
use Illuminate\Console\Command;
use Throwable;

/**
 * Smoke-test command for the AI abstraction layer.
 *
 * Usage:
 *   php artisan ai:ping
 *   php artisan ai:ping --provider=openai
 *   php artisan ai:ping --provider=anthropic --tier=complex
 *   php artisan ai:ping --prompt="Bir SaaS CMS hakkımızda yazısı yaz" --tier=simple
 */
class AiPingCommand extends Command
{
    protected $signature = 'ai:ping
        {--provider= : Provider name (anthropic, openai, openrouter). Defaults to config(ai.default_provider).}
        {--tier=simple : Model tier (simple|complex).}
        {--model= : Explicit model name; overrides --tier resolution.}
        {--prompt=Tek cümleyle merhaba de. : User prompt to send.}
        {--system= : Optional system prompt.}
        {--max-tokens=200 : Max output tokens.}';

    protected $description = 'Sends a single prompt through the AI abstraction layer to verify provider config and connectivity.';

    public function handle(AiManager $ai): int
    {
        $provider = $this->option('provider') ?: $ai->defaultProvider();
        $tier     = $this->option('tier');
        $model    = $this->option('model') ?: $ai->resolveModel($tier, $provider);

        $this->line("→ provider: <info>{$provider}</info>");
        $this->line("→ model:    <info>{$model}</info>");
        $this->line('→ prompt:   '.$this->option('prompt'));
        $this->newLine();

        try {
            $response = $ai->provider($provider)->generate(new AiRequest(
                model:       $model,
                messages:    [\App\Services\Ai\Dtos\AiMessage::user((string) $this->option('prompt'))],
                system:      $this->option('system') ?: null,
                maxTokens:   (int) $this->option('max-tokens'),
                temperature: 0.7,
                metadata:    ['source' => 'ai:ping'],
            ));
        } catch (AiProviderException $e) {
            $this->error('AI provider error: '.$e->getMessage());
            if ($e->responseBody) {
                $this->line('Response body:');
                $this->line(json_encode($e->responseBody, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }

            return self::FAILURE;
        } catch (Throwable $e) {
            $this->error('Unexpected error: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info('── response ──');
        $this->line($response->content);
        $this->newLine();
        $this->line(sprintf(
            '<comment>tokens: in=%d  out=%d  total=%d   stop=%s</comment>',
            $response->usage->inputTokens,
            $response->usage->outputTokens,
            $response->usage->totalTokens(),
            $response->stopReason ?? '-',
        ));

        return self::SUCCESS;
    }
}
