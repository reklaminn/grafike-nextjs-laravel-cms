@extends('frontend.layouts.auth')

@section('title', 'Kayıt Ol')

@section('content')
<h1 class="auth-title" style="font-size:1.1rem; font-weight:600; text-align:center; margin-bottom:1.25rem;">
    Kayıt Ol
</h1>

<form method="POST" action="{{ route('member.register.submit', [], false) }}">
    @csrf

    <div class="form-group" style="margin-bottom:.85rem;">
        <label for="name">Ad Soyad <span style="color:#dc2626;">*</span></label>
        <input id="name" type="text" name="name" value="{{ old('name') }}"
               required autocomplete="name">
        @error('name')
            <p style="color:#dc2626; font-size:.8rem; margin-top:.25rem;">{{ $message }}</p>
        @enderror
    </div>

    <div class="form-group" style="margin-bottom:.85rem;">
        <label for="email">E-posta <span style="color:#dc2626;">*</span></label>
        <input id="email" type="email" name="email" value="{{ old('email') }}"
               required autocomplete="email">
        @error('email')
            <p style="color:#dc2626; font-size:.8rem; margin-top:.25rem;">{{ $message }}</p>
        @enderror
    </div>

    <div class="form-group" style="margin-bottom:.85rem;">
        <label for="phone">Telefon</label>
        <input id="phone" type="tel" name="phone" value="{{ old('phone') }}"
               autocomplete="tel">
        @error('phone')
            <p style="color:#dc2626; font-size:.8rem; margin-top:.25rem;">{{ $message }}</p>
        @enderror
    </div>

    <div class="form-group" style="margin-bottom:.85rem;">
        <label for="password">Şifre <span style="color:#dc2626;">*</span></label>
        <input id="password" type="password" name="password"
               required autocomplete="new-password">
        @error('password')
            <p style="color:#dc2626; font-size:.8rem; margin-top:.25rem;">{{ $message }}</p>
        @enderror
    </div>

    <div class="form-group" style="margin-bottom:1rem;">
        <label for="password_confirmation">Şifre Tekrar <span style="color:#dc2626;">*</span></label>
        <input id="password_confirmation" type="password" name="password_confirmation"
               required autocomplete="new-password">
    </div>

    <button type="submit" class="btn-primary">Kayıt Ol</button>
</form>

<div class="auth-footer" style="text-align:center; font-size:.85rem; color:#6b7280; margin-top:1.25rem;">
    Zaten hesabınız var mı?
    <a href="{{ route('member.login') }}" style="color:#6366f1; text-decoration:none;">Giriş Yap</a>
</div>
@endsection
