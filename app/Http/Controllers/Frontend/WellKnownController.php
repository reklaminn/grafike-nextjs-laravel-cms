<?php

namespace App\Http\Controllers\Frontend;

use App\Models\SiteSetting;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

/**
 * Serves AI/LLM discovery files as specified by emerging standards:
 *
 *  - /ai.txt            — Human-readable AI access policy (analogous to robots.txt)
 *  - /.well-known/ai.txt — Same file at canonical well-known path
 *  - /.well-known/mcp.json — Model Context Protocol server manifest
 *                            https://modelcontextprotocol.io/
 */
class WellKnownController
{
    // ─── ai.txt ──────────────────────────────────────────────────────────────

    public function aiTxt(): Response
    {
        $content = Cache::remember('ai_txt', 3600, function () {
            $siteName  = SiteSetting::get('site.title', config('cms.name', 'CMS'));
            $siteUrl   = rtrim(config('app.url', ''), '/');
            $email     = SiteSetting::get('contact.email', '');
            $allowAi   = SiteSetting::get('crawl.allow_ai_bots', '1') === '1';

            $policy = $allowAi ? 'allow' : 'disallow';

            $lines = [
                "# AI Access Policy for {$siteName}",
                "# {$siteUrl}/ai.txt",
                '',
                '# Usage policy for AI systems and language models',
                "AI-Policy: {$policy}",
                '',
                '# Training data usage',
                'Training: ' . ($allowAi ? 'allowed' : 'disallowed'),
                '',
                '# Indexing for AI search / retrieval',
                'Indexing: ' . ($allowAi ? 'allowed' : 'disallowed'),
                '',
                '# Machine-readable content',
                "LLMs-Txt: {$siteUrl}/llms.txt",
                "LLMs-Full-Txt: {$siteUrl}/llms-full.txt",
                "Sitemap: {$siteUrl}/sitemap.xml",
                '',
            ];

            if ($email) {
                $lines[] = "# Contact for AI/data licensing inquiries";
                $lines[] = "Contact: mailto:{$email}";
                $lines[] = '';
            }

            $lines[] = "# Last updated: " . now()->toDateString();

            return implode("\n", $lines);
        });

        return response($content, 200)
            ->header('Content-Type', 'text/plain; charset=utf-8')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    // ─── MCP manifest ────────────────────────────────────────────────────────

    public function mcpJson(): Response
    {
        $manifest = Cache::remember('mcp_json', 3600, function () {
            $siteName = SiteSetting::get('site.title', config('cms.name', 'CMS'));
            $siteUrl  = rtrim(config('app.url', ''), '/');
            $apiBase  = $siteUrl . '/api';

            return json_encode([
                'mcp_version' => '1.0',
                'name'        => $siteName,
                'description' => SiteSetting::get('crawl.llms_description', ''),
                'server_url'  => null,   // No live MCP server yet; set when available
                'resources'   => [
                    [
                        'name'        => 'pages',
                        'uri'         => "{$apiBase}/pages/{slug}",
                        'description' => 'Published page content by slug',
                        'mime_type'   => 'application/json',
                    ],
                    [
                        'name'        => 'articles',
                        'uri'         => "{$apiBase}/articles/{slug}",
                        'description' => 'Published article content by slug',
                        'mime_type'   => 'application/json',
                    ],
                    [
                        'name'        => 'settings',
                        'uri'         => "{$apiBase}/settings",
                        'description' => 'Site settings (title, contact, social, business)',
                        'mime_type'   => 'application/json',
                    ],
                    [
                        'name'        => 'sitemap',
                        'uri'         => "{$siteUrl}/sitemap.xml",
                        'description' => 'Full sitemap with all published URLs',
                        'mime_type'   => 'application/xml',
                    ],
                    [
                        'name'        => 'llms',
                        'uri'         => "{$siteUrl}/llms.txt",
                        'description' => 'Concise site summary for AI context',
                        'mime_type'   => 'text/plain',
                    ],
                ],
                'contact' => SiteSetting::get('contact.email', '') ?: null,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        });

        return response($manifest, 200)
            ->header('Content-Type', 'application/json')
            ->header('Cache-Control', 'public, max-age=3600')
            ->header('Access-Control-Allow-Origin', '*');
    }
}
