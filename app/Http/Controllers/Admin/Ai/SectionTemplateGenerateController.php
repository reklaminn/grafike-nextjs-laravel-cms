<?php

namespace App\Http\Controllers\Admin\Ai;

use App\Http\Controllers\Controller;
use App\Models\SectionTemplate;
use App\Services\Ai\AiSectionTemplateGenerator;
use App\Services\Ai\Exceptions\AiQuotaExceededException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

/**
 * Generate a brand-new SectionTemplate from a natural-language description.
 * Agency-admin only — SectionTemplate library is global, shared across tenants.
 *
 * Two flows in one endpoint:
 *  - Preview (default): return generated payload + warnings; nothing
 *    persisted, admin can iterate by clicking "Tekrar Üret".
 *  - auto_save=true: insert the SectionTemplate row (is_active=false so
 *    it doesn't show in the picker until reviewed) and redirect to edit.
 */
class SectionTemplateGenerateController extends Controller
{
    public function __invoke(Request $request, AiSectionTemplateGenerator $generator): JsonResponse
    {
        $this->authorizeAgencyAdmin();

        $validated = $request->validate([
            'prompt'        => 'required|string|min:10|max:1500',
            'theme_id'      => 'nullable|integer',
            'color_scheme'  => 'nullable|string|max:100',
            'style'         => 'nullable|string|max:100',
            'language'      => 'nullable|string|max:50',
            'auto_save'     => 'nullable|boolean',
        ]);

        $hints = array_filter([
            'color_scheme' => $validated['color_scheme'] ?? null,
            'style'        => $validated['style'] ?? null,
            'language'     => $validated['language'] ?? null,
        ]);

        // Section templates live in central DB, no tenant context needed.
        // Quota for SectionTemplate generation hits the agency, not any tenant.
        try {
            $result = $generator->generate(
                prompt: $validated['prompt'],
                tenant: null,
                hints:  $hints ?: null,
            );
        } catch (AiQuotaExceededException $e) {
            return response()->json([
                'ok'         => false,
                'error_code' => 'quota_exceeded',
                'message'    => $e->getMessage(),
                'limit_type' => $e->limitType,
                'plan'       => $e->plan,
                'used'       => $e->used,
                'limit'      => $e->limit,
            ], 402);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'ok'         => false,
                'error_code' => 'generation_failed',
                'message'    => $e->getMessage(),
            ], 500);
        }

        if (filter_var($validated['auto_save'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $template = SectionTemplate::create([
                'theme_id'             => $validated['theme_id'] ?? null,
                'name'                 => $result['name'],
                'type'                 => $result['type'],
                'variation'            => $result['variation'],
                'render_mode'          => 'html',
                'html_template'        => $result['html_template'],
                'schema_json'          => $result['schema_json'],
                'default_content_json' => $result['default_content_json'],
                'is_active'            => false, // start inactive until admin reviews
            ]);

            return response()->json([
                'ok'           => true,
                'mode'         => 'saved',
                'template_id'  => $template->id,
                'warnings'     => $result['warnings'],
                'redirect_url' => route('admin.section-templates.edit', $template, false),
            ]);
        }

        return response()->json([
            'ok'      => true,
            'mode'    => 'preview',
            'preview' => $result,
        ]);
    }

    private function authorizeAgencyAdmin(): void
    {
        abort_unless(Auth::guard('admin')->user()?->isAgencyAdmin(), 403, 'Yalnızca ajans yöneticileri block şablonu üretebilir.');
    }
}
