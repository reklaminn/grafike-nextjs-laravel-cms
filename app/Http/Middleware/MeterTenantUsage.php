<?php

namespace App\Http\Middleware;

use App\Services\Tenancy\TenantUsageMeter;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Meters public (tenant-resolved) requests per tenant per day, and optionally
 * enforces a soft daily request cap from the tenant's package
 * (max_requests_per_day). MUST run AFTER tenancy is initialized.
 *
 * Safe by design: if the package sets no limit (null) it only counts; on any
 * error it falls open (never blocks a live request).
 */
class MeterTenantUsage
{
    public function __construct(private readonly TenantUsageMeter $meter)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $tenant = (function_exists('tenancy') && tenancy()->initialized) ? tenancy()->tenant : null;

        if ($tenant) {
            $tenantId = (string) $tenant->getTenantKey();

            // Askıya alınmış tenant — public istekler tamamen kapalı
            if ($tenant->isSuspended()) {
                return response()->json([
                    'error' => 'Bu site geçici olarak askıya alınmıştır.',
                ], 503);
            }

            // Frontend SSR (Next.js renderer) — günlük public limitten MUAF.
            // Limit gerçek ziyaretçi/abuse trafiği içindir; renderer'ın kendi
            // sunucu çağrıları (özellikle preview no-store modunda sayfa başına
            // birkaç çağrı) buna sayılmaz. Geçerli iç token → say(ma)/engelle(me).
            if ($this->isInternalRequest($request)) {
                return $next($request);
            }

            // Günlük limit = paket limiti + aktif geçici yükseltmeler
            // (quota_extensions). Paket limiti tanımsızsa sınırsız.
            $max = $tenant->effectiveDailyRequestLimit();
            if ($max !== null && $this->meter->requestsToday($tenantId) >= $max) {
                return response()->json([
                    'error'       => 'Günlük istek limiti aşıldı. Lütfen daha sonra tekrar deneyin.',
                    'limit'       => $max,
                    'retry_after' => now()->diffInSeconds(now()->endOfDay()),
                ], 429);
            }

            $this->meter->hitRequest($tenantId);
        }

        return $next($request);
    }

    /**
     * İstek frontend SSR renderer'ından mı geliyor? Paylaşılan gizli token
     * (CMS_INTERNAL_API_TOKEN) X-Internal-Token header'ıyla eşleşmeli. Token
     * tanımsızsa (boş) muafiyet kapalıdır — yani yanlışlıkla herkesi muaf
     * tutmaz. hash_equals ile zamanlama-güvenli karşılaştırma.
     */
    private function isInternalRequest(Request $request): bool
    {
        $token = (string) config('cms.internal_api_token', '');

        if ($token === '') {
            return false;
        }

        return hash_equals($token, (string) $request->header('X-Internal-Token', ''));
    }
}
