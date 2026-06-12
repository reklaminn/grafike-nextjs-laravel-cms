@extends('admin.layouts.app')
@section('title', 'Üyeler')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Üye Yönetimi</h1>
    <a href="{{ route('admin.members.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700">
        <i class="fas fa-plus mr-1"></i> Yeni Üye
    </a>
</div>

{{-- Stats --}}
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm border p-4 text-center">
        <div class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</div>
        <div class="text-xs text-gray-500">Toplam Üye</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border p-4 text-center">
        <div class="text-2xl font-bold text-green-600">{{ $stats['active'] }}</div>
        <div class="text-xs text-gray-500">Aktif</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border p-4 text-center">
        <div class="text-2xl font-bold text-indigo-600">{{ $stats['new_this_month'] }}</div>
        <div class="text-xs text-gray-500">Bu Ay</div>
    </div>
</div>

{{-- Filters --}}
<form method="GET" class="flex flex-wrap gap-3 mb-6">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Ad, e-posta veya telefon..."
           class="flex-1 min-w-[200px] px-4 py-2 border rounded-lg text-sm focus:ring-indigo-500">
    <select name="group_id" class="px-4 py-2 border rounded-lg text-sm">
        <option value="">Tüm Gruplar</option>
        @foreach($groups as $group)
            <option value="{{ $group->id }}" {{ request('group_id') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
        @endforeach
    </select>
    <select name="status" class="px-4 py-2 border rounded-lg text-sm">
        <option value="">Tüm Durumlar</option>
        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Pasif</option>
    </select>
    <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-lg text-sm">Filtrele</button>
</form>

{{-- Table --}}
<div class="bg-white rounded-xl shadow-sm border overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b">
            <tr>
                <th class="text-left px-4 py-3 font-medium text-gray-600">Ad</th>
                <th class="text-left px-4 py-3 font-medium text-gray-600">E-posta</th>
                <th class="text-left px-4 py-3 font-medium text-gray-600">Grup</th>
                <th class="text-center px-4 py-3 font-medium text-gray-600">Durum</th>
                <th class="text-left px-4 py-3 font-medium text-gray-600">Kayıt</th>
                <th class="text-right px-4 py-3 font-medium text-gray-600">İşlemler</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($members as $member)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $member->name }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $member->email }}</td>
                    <td class="px-4 py-3">
                        @if($member->group)
                            <span class="px-2 py-0.5 bg-indigo-100 text-indigo-700 rounded-full text-xs">{{ $member->group->name }}</span>
                        @else
                            <span class="text-gray-400 text-xs">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="w-2 h-2 inline-block rounded-full {{ $member->is_active ? 'bg-green-500' : 'bg-red-400' }}"></span>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $member->created_at->format('d.m.Y') }}</td>
                    <td class="px-4 py-3 text-right space-x-2">
                        <a href="{{ route('admin.members.edit', $member) }}" class="text-indigo-600 hover:underline text-xs">Düzenle</a>
                        <form method="POST" action="{{ route('admin.members.toggle-active', $member, false) }}" class="inline">
                            @csrf
                            <button type="submit" class="text-xs {{ $member->is_active ? 'text-yellow-600' : 'text-green-600' }} hover:underline">
                                {{ $member->is_active ? 'Devre Dışı' : 'Aktif Et' }}
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Üye bulunamadı.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $members->links() }}</div>
@endsection
