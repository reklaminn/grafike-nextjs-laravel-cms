<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CentralSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Sistem geneli ayarlar — sadece superadmin (agency admin) erişebilir.
 *
 * Şu an: AI API anahtarları yönetimi.
 * İleride: mail SMTP, storage, webhook vb. buraya eklenebilir.
 */
class SystemSettingsController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────

    /** GET /admin/settings/ai-keys */
    public function aiKeys(): View
    {
        $this->authorizeAgencyAdmin();

        $providers = [
            'anthropic'  => ['label' => 'Anthropic (Claude)', 'icon' => 'fa-brain', 'color' => 'text-orange-600'],
            'openrouter' => ['label' => 'OpenRouter',         'icon' => 'fa-route', 'color' => 'text-blue-600'],
            'openai'     => ['label' => 'OpenAI (GPT)',        'icon' => 'fa-robot', 'color' => 'text-green-600'],
        ];

        $hasKey = [];
        foreach (array_keys($providers) as $p) {
            $hasKey[$p] = CentralSetting::hasAiKey($p);
        }

        return view('admin.settings.ai-keys', [
            'providers'       => $providers,
            'hasKey'          => $hasKey,
            'defaultProvider' => CentralSetting::get('ai.default_provider')
                                 ?? config('ai.default_provider', 'anthropic'),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────

    /** POST /admin/settings/ai-keys */
    public function updateAiKeys(Request $request): RedirectResponse
    {
        $this->authorizeAgencyAdmin();

        $validated = $request->validate([
            'default_provider'   => 'nullable|string|in:anthropic,openrouter,openai',
            'anthropic_api_key'  => 'nullable|string|max:512',
            'openrouter_api_key' => 'nullable|string|max:512',
            'openai_api_key'     => 'nullable|string|max:512',
            'clear_anthropic'    => 'nullable|boolean',
            'clear_openrouter'   => 'nullable|boolean',
            'clear_openai'       => 'nullable|boolean',
        ]);

        // Default provider
        if (! empty($validated['default_provider'])) {
            CentralSetting::set('ai.default_provider', $validated['default_provider'], 'string', 'ai');
        }

        // API keys: boş form alanı = "dokunma"; clear_ flag = sil; dolu = güncelle
        foreach (['anthropic', 'openrouter', 'openai'] as $provider) {
            $keyField   = "{$provider}_api_key";
            $clearField = "clear_{$provider}";

            if (! empty($validated[$clearField])) {
                CentralSetting::remove("ai.{$provider}_api_key");
            } elseif (! empty($validated[$keyField])) {
                CentralSetting::set(
                    "ai.{$provider}_api_key",
                    trim($validated[$keyField]),
                    'encrypted',
                    'ai',
                );
            }
            // else: boş → dokunma
        }

        return redirect()
            ->route('admin.settings.ai-keys')
            ->with('success', 'AI anahtarları güncellendi.');
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function authorizeAgencyAdmin(): void
    {
        abort_unless(Auth::guard('admin')->user()?->isAgencyAdmin(), 403);
    }
}
