@extends('admin.layouts.app')
@section('title', 'Siteler (Tenants)')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Siteler</h1>
        <p class="text-sm text-gray-500 mt-1">Her site ayrı bir veritabanında izole çalışır (stancl/tenancy)</p>
    </div>
    <a href="{{ route('admin.tenants.create') }}"
       class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 font-medium">
        <i class="fas fa-plus mr-1"></i> Yeni Site
    </a>
</div>

@if(session('active_tenant'))
<div class="mb-4 p-3 bg-indigo-50 border border-indigo-200 rounded-lg flex items-center justify-between text-sm">
    <span class="text-indigo-700">
        <i class="fas fa-circle text-indigo-500 mr-1 text-xs"></i>
        Aktif site: <strong>{{ session('active_tenant') }}</strong>
    </span>
    <form method="POST" action="{{ route('admin.tenants.clear-active') }}">
        @csrf
        <button type="submit" class="text-indigo-500 hover:text-indigo-700 underline text-xs">Temizle</button>
    </form>
</div>
@endif

@if($tenants->isEmpty())
<div class="bg-white rounded-xl border border-dashed border-gray-300 p-12 text-center">
    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <i class="fas fa-building text-gray-400 text-2xl"></i>
    </div>
    <h3 class="text-gray-700 font-medium mb-2">Henüz site yok</h3>
    <p class="text-gray-500 text-sm mb-4">İlk müşteri sitenizi oluşturun</p>
    <a href="{{ route('admin.tenants.create') }}"
       class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700">
        <i class="fas fa-plus mr-2"></i> Yeni Site Oluştur
    </a>
</div>
@else
<div class="bg-white rounded-xl shadow-sm border overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b">
            <tr>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Site</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Domain(lar)</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Durum</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Oluşturulma</th>
                <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">İşlemler</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($tenants as $tenant)
            <tr class="hover:bg-gray-50 transition-colors {{ session('active_tenant') === $tenant->id ? 'bg-indigo-50/60' : '' }}">
                <td class="px-5 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-indigo-100 flex items-center justify-center flex-shrink-0">
                            <span class="text-indigo-600 font-bold text-sm uppercase">{{ substr($tenant->id, 0, 1) }}</span>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800">{{ $tenant->name ?? $tenant->id }}</p>
                            <p class="text-xs text-gray-400 font-mono">{{ $tenant->id }}</p>
                        </div>
                        @if(session('active_tenant') === $tenant->id)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 mr-1"></span>Aktif
                        </span>
                        @endif
                    </div>
                </td>
                <td class="px-5 py-4">
                    <div class="flex flex-wrap gap-1">
                        @foreach($tenant->domains as $domain)
                        <span class="inline-block px-2 py-0.5 bg-gray-100 text-gray-600 rounded text-xs font-mono">{{ $domain->domain }}</span>
                        @endforeach
                    </div>
                </td>
                <td class="px-5 py-4">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                        {{ $tenant->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                        <span class="w-1.5 h-1.5 rounded-full mr-1 {{ $tenant->status === 'active' ? 'bg-green-500' : 'bg-yellow-500' }}"></span>
                        {{ $tenant->status === 'active' ? 'Aktif' : 'Askıya Alındı' }}
                    </span>
                </td>
                <td class="px-5 py-4 text-gray-500 text-xs">
                    {{ $tenant->created_at?->format('d.m.Y') }}
                </td>
                <td class="px-5 py-4">
                    <div class="flex items-center justify-end gap-2">
                        {{-- Switch to this tenant --}}
                        @if(session('active_tenant') !== $tenant->id)
                        <form method="POST" action="{{ route('admin.tenants.switch', $tenant) }}">
                            @csrf
                            <button type="submit"
                                    class="px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded-lg text-xs hover:bg-indigo-100 font-medium"
                                    title="Bu siteyi aktif yap">
                                <i class="fas fa-toggle-off mr-1"></i> Seç
                            </button>
                        </form>
                        @endif

                        <a href="{{ route('admin.tenants.show', $tenant) }}"
                           class="px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg text-xs hover:bg-gray-200">
                            Detay
                        </a>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
@endsection
