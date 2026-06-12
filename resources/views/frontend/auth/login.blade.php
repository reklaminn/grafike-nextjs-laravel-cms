@extends('frontend.layouts.auth')

@section('title', 'Giriş Yap')

@section('content')
<h1 class="auth-title" style="font-size:1.1rem; font-weight:600; text-align:center; margin-bottom:1.25rem;">
    Giriş Yap
</h1>

<form method="POST" action="{{ route('member.login.submit', [], false) }}">
    @csrf
    <div class="form-group" style="margin-bottom:.85rem;">
        <label for="email">E-posta</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}"
               required autofocus autocomplete="email">
        @error('email')
            <p style="color:#dc2626; font-size:.8rem; margin-top:.25rem;">{{ $message }}</p>
        @enderror
    </div>

    <div class="form-group" style="margin-bottom:.85rem;">
        <label for="password">Şifre</label>
        <input id="password" type="password" name="password" required autocomplete="current-password">
        @error('password')
            <p style="color:#dc2626; font-size:.8rem; margin-top:.25rem;">{{ $message }}</p>
        @enderror
    </div>

    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem; font-size:.875rem;">
        <label style="display:flex; align-items:center; gap:.4rem; cursor:pointer;">
            <input type="checkbox" name="remember" style="width:auto;">
            Beni hatırla
        </label>
        <a href="{{ route('member.password.forgot') }}" style="color:#6366f1; text-decoration:none;">
            Şifremi unuttum
        </a>
    </div>

    <button type="submit" class="btn-primary">Giriş Yap</button>
</form>

<div class="auth-footer" style="text-align:center; font-size:.85rem; color:#6b7280; margin-top:1.25rem;">
    Hesabınız yok mu?
    <a href="{{ route('member.register') }}" style="color:#6366f1; text-decoration:none;">Kayıt Ol</a>
</div>
@endsection
