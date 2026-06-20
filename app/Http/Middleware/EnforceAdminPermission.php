<?php

namespace App\Http\Middleware;

use App\Support\AdminPermissions;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tenant-içerik route'larında granüler izin enforcement'ı.
 *
 * Route adından gerekli yeteneği türetir (AdminPermissions::abilityForRoute) ve
 * admin'in o yeteneği yoksa 403 verir. Gate::before sayesinde ajans admin,
 * site owner'ı ve HİÇ rolü olmayan admin'ler her zaman geçer — yalnızca rol
 * ATANMIŞ teammate'ler kısıtlanır. Eşleşmeyen route → kontrol yok.
 */
class EnforceAdminPermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $admin = Auth::guard('admin')->user();

        if ($admin) {
            $ability = AdminPermissions::abilityForRoute(
                $request->route()?->getName(),
                $request->method(),
            );

            if ($ability && ! $admin->can($ability)) {
                abort(403, 'Bu işlem için yetkiniz yok.');
            }
        }

        return $next($request);
    }
}
