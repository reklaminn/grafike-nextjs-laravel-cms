<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Public-API karşılığı [[RequireTenantModule]] — o middleware admin session'ındaki
 * `active_tenant`'a bakar; bu ise TENANCY ile o an INITIALIZE edilmiş tenant'ın
 * (stancl `tenant()`) belirli bir dikey modüle sahip olup olmadığını kontrol eder.
 *
 * Neden ayrı bir middleware: modül rotaları artık KOŞULSUZ boot'ta yüklenir
 * (route:cache uyumu) — eskiden TenancyInitialized event'inde loadRoutesFrom ile
 * yükleniyordu, `route:cache` sonrası no-op olup rotalar kayboluyordu. Rotalar
 * her zaman kayıtlı olduğundan, modülü OLMAYAN tenant'ta 404'e düşürmek için
 * istek-başına bu kapıya ihtiyaç var (aksi halde room_types tablosu olmayan
 * kurumsal tenant'ta 500 SQL hatası).
 *
 * Kullanım (tenancy init SONRASI): ->middleware('tenant.module.active:lodging')
 * Tenant yoksa / modül kapalıysa 404 (hangi tenant'ta hangi modül var sızmasın).
 */
class EnsureActiveTenantModule
{
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $tenant = function_exists('tenant') ? tenant() : null;

        if (! $tenant || ! method_exists($tenant, 'hasModule') || ! $tenant->hasModule($module)) {
            abort(404);
        }

        return $next($request);
    }
}
