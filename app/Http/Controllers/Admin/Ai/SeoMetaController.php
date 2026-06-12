<?php

namespace App\Http\Controllers\Admin\Ai;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\Ai\AiSeoGenerator;
use App\Services\Ai\Exceptions\AiQuotaExceededException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

/**
 * AJAX endpoint that returns AI-generated SEO meta (title / description /
 * keywords) for a Page. The view fills the form with the result; the
 * admin reviews and saves through the regular page form.
 *
 * No persistence happens here on purpose — caller (admin user) sees the
 * suggestion first and can edit before saving.
 */
class SeoMetaController extends Controller
{
    public function __invoke(Request $request, Page $page, AiSeoGenerator $generator): JsonResponse
    {
        // Tenancy middleware on the route group already ensures the admin
        // can access this tenant; the page is implicitly scoped via the
        // active tenant DB connection (stancl), so no extra check needed.

        $tenant = tenancy()->initialized ? tenant() : null;

        try {
            $meta = $generator->generate($page, $tenant);
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

        return response()->json([
            'ok'   => true,
            'meta' => $meta,
        ]);
    }
}
