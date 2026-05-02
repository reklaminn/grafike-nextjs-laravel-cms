@extends('admin.layouts.app')
@section('title', 'Üye Düzenle')

@section('content')
<div class="max-w-2xl">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Üye Düzenle</h1>
        <a href="{{ route('admin.members.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Geri</a>
    </div>

    <form method="POST" action="{{ route('admin.members.update', $member, false) }}" class="bg-white rounded-xl shadow-sm border p-6 space-y-5">
        @csrf @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ad Soyad *</label>
                <input type="text" name="name" value="{{ old('name', $member->name) }}" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">E-posta *</label>
                <input type="email" name="email" value="{{ old('email', $member->email) }}" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Telefon</label>
                <input type="tel" name="phone" value="{{ old('phone', $member->phone) }}" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Şifre (opsiyonel)</label>
                <input type="password" name="password" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-indigo-500" placeholder="Boş bırakılırsa değişmez">
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Üyelik Grubu</label>
                <select name="group_id" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-indigo-500">
                    <option value="">Seçiniz</option>
                    @foreach($groups as $group)
                        <option value="{{ $group->id }}" {{ old('group_id', $member->group_id) == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <label class="flex items-center gap-2 text-sm">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $member->is_active) ? 'checked' : '' }} class="rounded text-indigo-600">
                    Aktif
                </label>
            </div>
        </div>
        <div class="flex gap-3 pt-2">
            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700">Güncelle</button>
            <a href="{{ route('admin.members.index') }}" class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">İptal</a>
        </div>
    </form>
</div>
@endsection
