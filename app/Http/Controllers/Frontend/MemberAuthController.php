<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class MemberAuthController extends Controller
{
    public function showLogin()
    {
        return view('frontend.auth.login');
    }

    public function login(Request $request)
    {
        $key = 'member-login:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->with('error', 'Çok fazla giriş denemesi. Lütfen biraz bekleyin.');
        }

        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $member = Member::where('email', $request->email)->first();

        if (! $member || ! Hash::check($request->password, $member->password)) {
            RateLimiter::hit($key, 300);

            return back()->with('error', 'E-posta veya şifre yanlış.')->withInput();
        }

        if (! $member->is_active) {
            return back()->with('error', 'Hesabınız devre dışı bırakılmış.')->withInput();
        }

        Auth::guard('member')->login($member, $request->boolean('remember'));
        $request->session()->regenerate();
        RateLimiter::clear($key);

        // Tenant başına günlük giriş metering (fail-open).
        if (function_exists('tenancy') && tenancy()->initialized && tenancy()->tenant) {
            app(\App\Services\Tenancy\TenantUsageMeter::class)
                ->hitLogin((string) tenancy()->tenant->getTenantKey());
        }

        return redirect()->intended(route('member.profile'));
    }

    public function showRegister()
    {
        return view('frontend.auth.register');
    }

    public function register(Request $request)
    {
        $key = 'member-register:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            return back()->with('error', 'Çok fazla kayıt denemesi. Lütfen bekleyin.');
        }
        RateLimiter::hit($key, 3600);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:members,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $member = Member::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => $validated['password'],
            'is_active' => true,
        ]);

        Auth::guard('member')->login($member);
        $request->session()->regenerate();

        return redirect()->route('member.profile')
            ->with('success', 'Hesabınız oluşturuldu!');
    }

    public function profile()
    {
        $member = Auth::guard('member')->user();

        return view('frontend.auth.profile', compact('member'));
    }

    public function updateProfile(Request $request)
    {
        $member = Auth::guard('member')->user();

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $member->name = $validated['name'];
        $member->phone = $validated['phone'] ?? $member->phone;

        if (! empty($validated['password'])) {
            $member->password = $validated['password'];
        }

        $member->save();

        return back()->with('success', 'Profil güncellendi.');
    }

    public function logout(Request $request)
    {
        Auth::guard('member')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Oturumunuz kapatıldı.');
    }
}
