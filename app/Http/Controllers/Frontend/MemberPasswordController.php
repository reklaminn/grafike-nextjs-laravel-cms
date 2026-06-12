<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class MemberPasswordController extends Controller
{
    // ─── Forgot password form ─────────────────────────────────────────────────

    public function showForgot()
    {
        return view('frontend.auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::broker('members')->sendResetLink(
            $request->only('email'),
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', __('Şifre sıfırlama bağlantısı e-postanıza gönderildi.'))
            : back()->with('error', __('Bu e-posta adresiyle kayıtlı bir hesap bulunamadı.'))
                    ->withInput();
    }

    // ─── Reset password form ──────────────────────────────────────────────────

    public function showReset(Request $request, string $token)
    {
        return view('frontend.auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token'                 => 'required',
            'email'                 => 'required|email',
            'password'              => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required',
        ]);

        $status = Password::broker('members')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (Member $member, string $password) {
                $member->forceFill([
                    'password'       => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($member));
            },
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('member.login')
                        ->with('success', 'Şifreniz başarıyla güncellendi. Giriş yapabilirsiniz.')
            : back()->with('error', 'Geçersiz veya süresi dolmuş bağlantı. Lütfen tekrar deneyin.')
                    ->withInput($request->only('email'));
    }
}
