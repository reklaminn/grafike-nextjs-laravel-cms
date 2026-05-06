@extends('frontend.layouts.auth')

@section('title', 'Profilim')

@section('content')
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.25rem;">
    <h1 style="font-size:1.1rem; font-weight:700; margin:0;">Profilim</h1>
    <div style="display:flex; gap:.75rem; align-items:center; font-size:.85rem;">
        <a href="/" style="color:#6b7280; text-decoration:none;">Ana Sayfa</a>
        <form method="POST" action="{{ route('member.logout', [], false) }}" style="display:inline;">
            @csrf
            <button type="submit" style="background:none; border:none; cursor:pointer; color:#dc2626; font-size:.85rem; padding:0;">
                Çıkış Yap
            </button>
        </form>
    </div>
</div>

<form method="POST" action="{{ route('member.profile.update', [], false) }}"
      style="background:#fff; border:1px solid #e5e7eb; border-radius:.75rem; padding:1.5rem;">
    @csrf
    @method('PUT')

    <div class="form-group" style="margin-bottom:.85rem;">
        <label for="name">Ad Soyad</label>
        <input id="name" type="text" name="name"
               value="{{ old('name', $member->name) }}" required>
        @error('name')
            <p style="color:#dc2626; font-size:.8rem; margin-top:.25rem;">{{ $message }}</p>
        @enderror
    </div>

    <div class="form-group" style="margin-bottom:.85rem;">
        <label>E-posta</label>
        <input type="email" value="{{ $member->email }}" disabled
               style="background:#f9fafb; color:#6b7280; cursor:not-allowed;">
        <p style="font-size:.78rem; color:#9ca3af; margin-top:.2rem;">E-posta değiştirilemez.</p>
    </div>

    <div class="form-group" style="margin-bottom:1rem;">
        <label for="phone">Telefon</label>
        <input id="phone" type="tel" name="phone"
               value="{{ old('phone', $member->phone) }}">
        @error('phone')
            <p style="color:#dc2626; font-size:.8rem; margin-top:.25rem;">{{ $message }}</p>
        @enderror
    </div>

    <div style="border-top:1px solid #e5e7eb; padding-top:1rem; margin-bottom:1rem;">
        <p style="font-size:.85rem; font-weight:600; margin-bottom:.75rem; color:#374151;">
            Şifre Değiştir <span style="font-weight:400; color:#9ca3af;">(opsiyonel)</span>
        </p>
        <div class="form-group" style="margin-bottom:.85rem;">
            <label for="password">Yeni Şifre</label>
            <input id="password" type="password" name="password"
                   autocomplete="new-password">
            @error('password')
                <p style="color:#dc2626; font-size:.8rem; margin-top:.25rem;">{{ $message }}</p>
            @enderror
        </div>
        <div class="form-group" style="margin-bottom:0;">
            <label for="password_confirmation">Şifre Tekrar</label>
            <input id="password_confirmation" type="password" name="password_confirmation"
                   autocomplete="new-password">
        </div>
    </div>

    <button type="submit" class="btn-primary">Profili Güncelle</button>
</form>

{{-- Account info --}}
<div style="background:#fff; border:1px solid #e5e7eb; border-radius:.75rem; padding:1.25rem; margin-top:1rem;">
    <p style="font-size:.85rem; font-weight:600; margin-bottom:.6rem; color:#374151;">Hesap Bilgileri</p>
    <div style="font-size:.85rem; color:#6b7280; display:flex; flex-direction:column; gap:.4rem;">
        <div style="display:flex; justify-content:space-between;">
            <span>Kayıt Tarihi</span>
            <span>{{ $member->created_at?->format('d.m.Y') }}</span>
        </div>
        @if($member->group)
        <div style="display:flex; justify-content:space-between;">
            <span>Üyelik Grubu</span>
            <span>{{ $member->group->name }}</span>
        </div>
        @endif
    </div>
</div>

<div class="auth-footer" style="text-align:center; font-size:.85rem; color:#6b7280; margin-top:1.25rem;">
    <a href="/" style="color:#6366f1; text-decoration:none;">← Ana sayfaya dön</a>
</div>
@endsection
