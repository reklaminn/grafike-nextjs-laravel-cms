@extends('frontend.layouts.auth')

@section('title', 'Şifremi Unuttum')

@section('content')
<h1 class="auth-title" style="font-size:1.1rem; font-weight:600; text-align:center; margin-bottom:.5rem;">
    Şifremi Unuttum
</h1>
<p style="text-align:center; font-size:.875rem; color:#6b7280; margin-bottom:1.25rem;">
    E-posta adresinizi girin, şifre sıfırlama bağlantısı gönderelim.
</p>

<form method="POST" action="{{ route('member.password.email', [], false) }}">
    @csrf

    <div class="form-group" style="margin-bottom:1rem;">
        <label for="email">E-posta</label>
        <input id="email" type="email" name="email"
               value="{{ old('email') }}" required autofocus autocomplete="email">
        @error('email')
            <p style="color:#dc2626; font-size:.8rem; margin-top:.25rem;">{{ $message }}</p>
        @enderror
    </div>

    <button type="submit" class="btn-primary">Bağlantı Gönder</button>
</form>

<div class="auth-footer" style="text-align:center; font-size:.85rem; color:#6b7280; margin-top:1.25rem;">
    <a href="{{ route('member.login') }}" style="color:#6366f1; text-decoration:none;">← Giriş sayfasına dön</a>
</div>
@endsection
