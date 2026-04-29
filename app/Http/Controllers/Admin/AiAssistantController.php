<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Language;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AiAssistantController extends Controller
{
    public function translate(Request $request)
    {
        $request->validate([
            'text' => 'required|string|max:10000',
            'source_lang' => 'required|string|max:5',
            'target_lang' => 'required|string|max:5',
        ]);

        $prompt = "Translate the following text from {$request->source_lang} to {$request->target_lang}. Return only the translation, nothing else.\n\nText: {$request->text}";

        return response()->json([
            'result' => $this->callAi($prompt),
        ]);
    }

    public function rewrite(Request $request)
    {
        $request->validate([
            'text' => 'required|string|max:10000',
            'style' => 'nullable|string|in:professional,casual,seo,short',
        ]);

        $style = $request->style ?? 'professional';
        $styleMap = [
            'professional' => 'in a professional tone',
            'casual' => 'in a casual, friendly tone',
            'seo' => 'optimized for SEO with relevant keywords',
            'short' => 'making it more concise and shorter',
        ];

        $prompt = "Rewrite the following text {$styleMap[$style]}. Return only the rewritten text.\n\nText: {$request->text}";

        return response()->json([
            'result' => $this->callAi($prompt),
        ]);
    }

    public function generateMeta(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:500',
            'content' => 'nullable|string|max:10000',
            'lang' => 'nullable|string|max:5',
        ]);

        $lang = $request->lang ?? 'tr';
        $prompt = "Generate SEO meta tags for a web page in {$lang} language.\nPage title: {$request->title}\nContent: " . ($request->content ? substr($request->content, 0, 2000) : 'N/A') . "\n\nReturn a JSON object with: meta_title (max 60 chars), meta_description (max 160 chars), meta_keywords (comma-separated, max 5). Return only the JSON.";

        $result = $this->callAi($prompt);

        // Try to parse as JSON
        try {
            $json = json_decode($result, true);
            if ($json) {
                return response()->json(['result' => $json]);
            }
        } catch (\Throwable $e) {
            // Fall through
        }

        return response()->json(['result' => $result]);
    }

    /**
     * Translate all text fields of a page or article in one shot.
     * POST /admin/ai/translate-content
     *
     * Body: { type: 'page'|'article', id: int, target_language_id: int }
     * Returns: { result: { title, excerpt?, body?, seo_title?, seo_description? } }
     */
    public function translateContent(Request $request)
    {
        $request->validate([
            'type'               => 'required|in:page,article',
            'id'                 => 'required|integer',
            'target_language_id' => 'required|exists:languages,id',
        ]);

        $targetLang = Language::findOrFail($request->integer('target_language_id'));
        $targetName = $targetLang->name; // e.g. "English", "Türkçe"

        if ($request->type === 'page') {
            $model = Page::with('language', 'seo')->findOrFail($request->id);
            $sourceLang = $model->language?->name ?? 'unknown';

            $fields = [
                'title' => $model->title,
            ];

            if ($model->seo?->meta_title) $fields['seo_title'] = $model->seo->meta_title;
            if ($model->seo?->meta_description) $fields['seo_description'] = $model->seo->meta_description;
        } else {
            $model = Article::with('language', 'seo')->findOrFail($request->id);
            $sourceLang = $model->language?->name ?? 'unknown';

            $fields = ['title' => $model->title];
            if ($model->excerpt) $fields['excerpt'] = $model->excerpt;
            if ($model->body)    $fields['body']    = substr($model->body, 0, 8000); // truncate for AI
            if ($model->seo?->meta_title)       $fields['seo_title']       = $model->seo->meta_title;
            if ($model->seo?->meta_description) $fields['seo_description'] = $model->seo->meta_description;
        }

        $fieldsJson = json_encode($fields, JSON_UNESCAPED_UNICODE);

        $prompt = <<<PROMPT
        You are a professional translator. Translate the following JSON fields from {$sourceLang} to {$targetName}.
        Return ONLY a valid JSON object with the same keys, with values translated. Do not add explanations.
        Do not translate HTML tags inside values — only translate the text content.

        Input:
        {$fieldsJson}
        PROMPT;

        $raw = $this->callAi(trim($prompt));

        // Attempt to extract JSON from the response
        $decoded = null;
        if (preg_match('/\{.*\}/s', $raw, $matches)) {
            $decoded = json_decode($matches[0], true);
        }

        if (! is_array($decoded)) {
            return response()->json([
                'error' => 'AI geçerli JSON döndürmedi.',
                'raw'   => $raw,
            ], 422);
        }

        return response()->json(['result' => $decoded]);
    }

    protected function callAi(string $prompt): string
    {
        $provider = config('services.ai.provider', 'openai');
        $apiKey = config('services.ai.api_key');
        $model = config('services.ai.model', 'gpt-4o-mini');

        if (empty($apiKey)) {
            return 'AI servisi yapılandırılmamış. config/services.php dosyasına ai ayarlarını ekleyin.';
        }

        try {
            if ($provider === 'anthropic') {
                $response = Http::withHeaders([
                    'x-api-key' => $apiKey,
                    'anthropic-version' => '2023-06-01',
                    'Content-Type' => 'application/json',
                ])->timeout(30)->post('https://api.anthropic.com/v1/messages', [
                    'model' => $model,
                    'max_tokens' => 1024,
                    'messages' => [['role' => 'user', 'content' => $prompt]],
                ]);

                if ($response->successful()) {
                    return $response->json('content.0.text', '');
                }
            } else {
                // OpenAI
                $response = Http::withToken($apiKey)
                    ->timeout(30)
                    ->post('https://api.openai.com/v1/chat/completions', [
                        'model' => $model,
                        'messages' => [['role' => 'user', 'content' => $prompt]],
                        'max_tokens' => 1024,
                    ]);

                if ($response->successful()) {
                    return $response->json('choices.0.message.content', '');
                }
            }

            return 'AI yanıt vermedi. HTTP: ' . $response->status();
        } catch (\Throwable $e) {
            return 'AI hatası: ' . $e->getMessage();
        }
    }
}
