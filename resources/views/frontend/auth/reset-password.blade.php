@extends('frontend.layouts.auth')

@section('title', 'Yeni Şifre Belirle')

@section('content')
<h1 class="auth-title" style="font-size:1.1rem; font-weight:600; text-align:center; margin-bottom:1.25rem;">
    Yeni Şifre Belirle
</h1>

<form method="POST" action="{{ route('member.password.update', [], false) }}">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">

    <div class="form-group" style="margin-bottom:.85rem;">
        <label for="email">E-posta</label>
        <input id="email" type="email" name="email"
               value="{{ old('email', $email) }}" required autocomplete="email">
        @error('email')
            <p style="color:#dc2626; font-size:.8rem; margin-top:.25rem;">{{ $message }}</p>
        @enderror
    </div>

    <div class="form-group" style="margin-bottom:.85rem;">
        <label for="password">Yeni Şifre <span style="color:#dc2626;">*</span></label>
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

    <button type="submit" class="btn-primary">Şifremi Güncelle</button>
</form>
@endsection
