<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $admin = Admin::where('username', $request->username)
            ->orWhere('email', $request->username)
            ->first();

        if (! $admin) {
            return back()->withErrors(['username' => 'Gecersiz kullanici adi veya sifre.'])->withInput();
        }

        // MD5 legacy password bridge - auto-upgrade to bcrypt on first login
        if ($admin->legacy_password && md5($request->password) === $admin->legacy_password) {
            $admin->forceFill([
                'password' => Hash::make($request->password),
                'legacy_password' => null,
            ])->save();
        }

        if (Auth::guard('admin')->attempt(
            ['username' => $admin->username, 'password' => $request->password],
            $request->boolean('remember')
        )) {
            $request->session()->regenerate();

            $admin->update([
                'last_login_ip' => $request->ip(),
                'last_login_at' => now(),
            ]);

            $this->auditLog('Giriş yapıldı', $admin, $request);

            if (! $admin->isAgencyAdmin()) {
                $defaultTenantId = $admin->defaultTenantId();

                if ($defaultTenantId) {
                    session(['active_tenant' => $defaultTenantId]);
                }
            }

            return redirect()->intended(route('admin.dashboard'));
        }

        $this->auditLog('Başarısız giriş denemesi', $admin, $request);

        return back()->withErrors(['username' => 'Gecersiz kullanici adi veya sifre.'])->withInput();
    }

    public function logout(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($admin) {
            $this->auditLog('Çıkış yapıldı', $admin, $request);
        }

        return redirect()->route('admin.login');
    }

    /**
     * Auth olaylarını activity log'a yaz (audit trail).
     * Log hatası girişi engellememeli — sessizce geç.
     */
    private function auditLog(string $description, Admin $admin, Request $request): void
    {
        try {
            activity('auth')
                ->causedBy($admin)
                ->withProperties([
                    'ip'         => $request->ip(),
                    'user_agent' => substr((string) $request->userAgent(), 0, 255),
                    'username'   => $admin->username,
                ])
                ->log($description);
        } catch (\Throwable) {
            // activity_log tablosu yoksa / DB hatasında auth akışını bozma
        }
    }
}
