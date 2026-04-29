<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;

/**
 * Serves a dynamic robots.txt that respects admin-configured AI bot policies.
 */
class RobotsController extends Controller
{
    /**
     * Known AI bots and their default allow state.
     * Key = User-agent string, value = allowed by default.
     */
    public const AI_BOTS = [
        'GPTBot'             => false,   // OpenAI training
        'ChatGPT-User'       => true,    // ChatGPT live browsing
        'ClaudeBot'          => true,    // Anthropic crawl
        'Claude-Web'         => true,    // Anthropic legacy
        'anthropic-ai'       => true,    // Anthropic
        'PerplexityBot'      => true,    // Perplexity AI
        'Google-Extended'    => false,   // Google AI training
        'CCBot'              => false,   // Common Crawl (training data)
        'Bytespider'         => false,   // ByteDance / TikTok
        'Applebot-Extended'  => false,   // Apple AI training
        'Amazonbot'          => true,    // Amazon (Alexa)
        'FacebookBot'        => true,    // Meta
        'Twitterbot'         => true,    // Twitter/X
        'LinkedInBot'        => true,    // LinkedIn
    ];

    public function __invoke(): \Illuminate\Http\Response
    {
        $content = Cache::remember('robots_txt', 3600, function () {
            return $this->build();
        });

        return response($content, 200)->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    protected function build(): string
    {
        $lines = [];

        // ── 1. Global default ──────────────────────────────────────────────
        $lines[] = 'User-agent: *';
        $lines[] = 'Allow: /';
        $lines[] = 'Disallow: /admin/';
        $lines[] = 'Disallow: /member/';
        $lines[] = '';

        // ── 2. AI bots ─────────────────────────────────────────────────────
        $allowAi = (bool) SiteSetting::get('crawl.allow_ai_bots', true);

        foreach (self::AI_BOTS as $bot => $defaultAllow) {
            // Per-bot override from settings (JSON object stored under key)
            $botKey     = 'crawl.bot_' . strtolower(str_replace(['-', ' '], '_', $bot));
            $botAllowed = (bool) SiteSetting::get($botKey, $allowAi ? $defaultAllow : false);

            $lines[] = "User-agent: {$bot}";
            if ($botAllowed) {
                $lines[] = 'Allow: /';
            } else {
                $lines[] = 'Disallow: /';
            }
            $lines[] = '';
        }

        // ── 3. Crawl delay (if set) ────────────────────────────────────────
        $crawlDelay = (int) SiteSetting::get('crawl.crawl_delay', 0);
        if ($crawlDelay > 0) {
            $lines[] = 'User-agent: *';
            $lines[] = "Crawl-delay: {$crawlDelay}";
            $lines[] = '';
        }

        // ── 4. Custom rules (admin textarea) ─────────────────────────────
        $custom = trim((string) SiteSetting::get('crawl.robots_custom', ''));
        if ($custom) {
            $lines[] = $custom;
            $lines[] = '';
        }

        // ── 5. Sitemap ─────────────────────────────────────────────────────
        $lines[] = 'Sitemap: ' . url('sitemap.xml');

        return implode("\n", $lines) . "\n";
    }
}
