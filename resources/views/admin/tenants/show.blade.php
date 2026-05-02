@extends('admin.layouts.app')
@section('title', ($tenant->name ?? $tenant->id) . ' — Site Detayı')

@section('content')
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.tenants.index') }}" class="text-gray-400 hover:text-gray-600">
        <i class="fas fa-arrow-left"></i>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ $tenant->name ?? $tenant->id }}</h1>
        <p class="text-sm text-gray-400 font-mono">ID: {{ $tenant->id }} &nbsp;·&nbsp; DB: tenant_{{ $tenant->id }}</p>
    </div>
    <span class="ml-auto inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
        {{ $tenant->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
        {{ $tenant->status === 'active' ? 'Aktif' : 'Askıya Alındı' }}
    </span>
</div>

{{-- Flash messages --}}
@if(session('success'))
<div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700 whitespace-pre-line">
    <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
    <i class="fas fa-exclamation-triangle mr-1"></i> {{ session('error') }}
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Left: Details + actions --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Domains --}}
        <div class="bg-white rounded-xl shadow-sm border p-5">
            <h2 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                <i class="fas fa-globe text-indigo-500"></i> Domain'ler
            </h2>
            <div class="space-y-2">
                @forelse($tenant->domains as $domain)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <span class="font-mono text-sm text-gray-700">{{ $domain->domain }}</span>
                    <a href="https://{{ $domain->domain }}" target="_blank"
                       class="text-xs text-indigo-500 hover:underline">
                        <i class="fas fa-external-link-alt mr-0.5"></i> Ziyaret Et
                    </a>
                </div>
                @empty
                <p class="text-gray-400 text-sm">Domain kaydı yok.</p>
                @endforelse
            </div>
        </div>

        {{-- Provision --}}
        @if($canManageTenants)
        <div class="bg-white rounded-xl shadow-sm border p-5">
            <h2 class="font-semibold text-gray-700 mb-2 flex items-center gap-2">
                <i class="fas fa-database text-green-500"></i> Veritabanı Provision
            </h2>
            <p class="text-sm text-gray-500 mb-4">
                <code class="bg-gray-100 px-1.5 py-0.5 rounded">tenant_{{ $tenant->id }}</code> veritabanını oluşturur
                ve tüm tenant migration'larını çalıştırır.
                Yeni migration eklendiğinde tekrar çalıştırabilirsiniz (idempotent).
            </p>
            <form method="POST" action="{{ route('admin.tenants.provision', $tenant, false) }}">
                @csrf
                <button type="submit"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700 font-medium"
                        onclick="return confirm('Tenant migration\'ları çalıştırılsın mı?')">
                    <i class="fas fa-play mr-1"></i> Provision / Migrate
                </button>
            </form>
        </div>
        @endif

        {{-- Edit --}}
        @if($canManageTenants)
        <div class="bg-white rounded-xl shadow-sm border p-5">
            <h2 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                <i class="fas fa-edit text-blue-500"></i> Site Bilgilerini Güncelle
            </h2>
            <form method="POST" action="{{ route('admin.tenants.update', $tenant, false) }}" class="space-y-4">
                @csrf @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Site Adı</label>
                    <input type="text" name="name" value="{{ old('name', $tenant->name) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tema</label>
                    <select name="theme_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                        <option value="">— Tema seç —</option>
                        @foreach($themes as $theme)
                        <option value="{{ $theme->id }}" {{ old('theme_id', $tenant->theme_id) == $theme->id ? 'selected' : '' }}>
                            {{ $theme->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Durum</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                        <option value="active"   {{ old('status', $tenant->status) === 'active'    ? 'selected' : '' }}>Aktif</option>
                        <option value="suspended" {{ old('status', $tenant->status) === 'suspended' ? 'selected' : '' }}>Askıya Alındı</option>
                    </select>
                </div>

                <button type="submit"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 font-medium">
                    <i class="fas fa-save mr-1"></i> Kaydet
                </button>
            </form>
        </div>
        @endif
    </div>

    {{-- Right: Quick actions --}}
    <div class="space-y-4">

        {{-- Switch to this tenant --}}
        <div class="bg-white rounded-xl shadow-sm border p-5">
            <h2 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
                <i class="fas fa-toggle-on text-indigo-500"></i> Aktif Site
            </h2>
            @if(session('active_tenant') === $tenant->id)
            <p class="text-sm text-indigo-600 mb-3">
                <i class="fas fa-check-circle mr-1"></i> Bu site şu an aktif.
            </p>
            <form method="POST" action="{{ route('admin.tenants.clear-active', [], false) }}">
                @csrf
                <button type="submit" class="w-full px-3 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">
                    Aktifi Temizle
                </button>
            </form>
            @else
            <p class="text-sm text-gray-500 mb-3">
                Bu siteyi seçin; Sayfalar, Yazılar ve Menüler bu sitenin veritabanından çalışır.
            </p>
            <form method="POST" action="{{ route('admin.tenants.switch', $tenant, false) }}">
                @csrf
                <button type="submit" class="w-full px-3 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 font-medium">
                    <i class="fas fa-toggle-off mr-1"></i> Bu Siteyi Seç
                </button>
            </form>
            @endif
        </div>

        {{-- Artisan info --}}
        @if($canManageTenants)
        <div class="bg-gray-50 rounded-xl border p-4 text-xs text-gray-500 font-mono space-y-1">
            <p class="font-semibold text-gray-600 mb-2 font-sans text-xs uppercase">Artisan komutları</p>
            <p>php artisan tenants:migrate</p>
            <p class="pl-2 text-gray-400">--tenants={{ $tenant->id }}</p>
            <p class="mt-2">php artisan tenants:run</p>
            <p class="pl-2 text-gray-400">"db:seed --class=TenantSeeder"</p>
            <p class="pl-2 text-gray-400">--tenants={{ $tenant->id }}</p>
        </div>
        @endif

        {{-- Danger zone --}}
        @if($canManageTenants)
        <div class="bg-white rounded-xl shadow-sm border border-red-100 p-5">
            <h2 class="font-semibold text-red-600 mb-2 text-sm flex items-center gap-2">
                <i class="fas fa-exclamation-triangle"></i> Tehlikeli Bölge
            </h2>
            <p class="text-xs text-gray-500 mb-3">Bu işlem geri alınamaz. Tenant kaydı silinir.</p>
            <form method="POST" action="{{ route('admin.tenants.destroy', $tenant) }}">
                @csrf @method('DELETE')
                <button type="submit"
                        class="w-full px-3 py-2 bg-red-50 text-red-600 border border-red-200 rounded-lg text-xs hover:bg-red-100"
                        onclick="return confirm('{{ $tenant->name ?? $tenant->id }} silinsin mi? Bu işlem GERİ ALINAMAZ!')">
                    <i class="fas fa-trash mr-1"></i> Tenant'ı Sil
                </button>
            </form>
        </div>
        @endif
    </div>
</div>
@endsection
