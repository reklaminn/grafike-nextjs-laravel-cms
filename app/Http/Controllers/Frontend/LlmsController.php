<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\Seo\LlmsTxtGenerator;
use Illuminate\Support\Facades\Cache;

/**
 * Serves llms.txt and llms-full.txt for AI/LLM discovery.
 *
 * Spec: https://llmstxt.org/
 */
class LlmsController extends Controller
{
    public function __construct(protected LlmsTxtGenerator $generator) {}

    /** GET /llms.txt — concise site overview */
    public function index(): \Illuminate\Http\Response
    {
        $content = Cache::remember('llms_txt', 21600, fn () => $this->generator->generate());

        return response($content, 200)
            ->header('Content-Type', 'text/plain; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=21600');
    }

    /** GET /llms-full.txt — full content dump */
    public function full(): \Illuminate\Http\Response
    {
        $content = Cache::remember('llms_full_txt', 21600, fn () => $this->generator->generateFull());

        return response($content, 200)
            ->header('Content-Type', 'text/plain; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=21600');
    }
}
