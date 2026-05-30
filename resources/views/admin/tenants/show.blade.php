@php
    $backups = app(\App\Http\Controllers\Admin\TenantBackupController::class)->backupList($tenant);
@endphp
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
    @php $statusColor = match($tenant->status) {
        'active'       => 'bg-green-100 text-green-700',
        'provisioning' => 'bg-blue-100 text-blue-700',
        'failed'       => 'bg-red-100 text-red-700',
        default        => 'bg-yellow-100 text-yellow-700',
    }; @endphp
    <span class="ml-auto inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium {{ $statusColor }}">
        @if($tenant->status === 'provisioning')
            <svg class="animate-spin h-3 w-3" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            Hazırlanıyor…
        @elseif($tenant->status === 'active') Aktif
        @elseif($tenant->status === 'failed') Hata
        @else Askıya Alındı
        @endif
    </span>
</div>

{{-- Provisioning banner — auto-refresh every 5s --}}
@if($tenant->status === 'provisioning')
<div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-xl text-sm text-blue-800 flex items-center gap-3" id="provisioning-banner">
    <svg class="animate-spin h-5 w-5 text-blue-500 flex-shrink-0" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
    </svg>
    <div>
        <strong>Veritabanı hazırlanıyor…</strong>
        <span class="text-blue-600"> Migrationlar çalışıyor, bu ~30-60 saniye sürebilir.</span>
        <span class="text-blue-500 ml-2" id="refresh-counter">Otomatik yenileme: <span id="countdown">10</span>s</span>
    </div>
</div>
<script>
(function() {
    var n = 10;
    var el = document.getElementById('countdown');
    var timer = setInterval(function() {
        n--;
        if (el) el.textContent = n;
        if (n <= 0) { clearInterval(timer); location.reload(); }
    }, 1000);
})();
</script>
@endif

@if($tenant->status === 'failed')
<div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
    <i class="fas fa-exclamation-triangle mr-1"></i>
    <strong>Provisioning başarısız.</strong> Log'u kontrol edin veya manuel olarak
    <form method="POST" action="{{ route('admin.tenants.provision', $tenant) }}" class="inline">
        @csrf <button class="underline text-red-600 hover:text-red-800">tekrar dene</button>.
    </form>
</div>
@endif

{{-- Flash messages are handled by the layout (admin.layouts.app) --}}

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Left: Details + actions --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Backup panel --}}
        @include('admin.tenants._backup-panel', [
            'tenant'    => $tenant,
            'backups'   => $backups,
            'canManage' => $canManageTenants,
        ])

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

        {{-- ─── Vertical Modüller (Tours, Commerce, …) ─────────────────────── --}}
        {{-- Seçili modül yoksa ve kullanıcı modülleri yönetemiyorsa paneli
             tamamen gizle (boş alan göstermeyelim). Yöneticiler (ajans) her
             zaman görür ki modül açıp kapatabilsin. --}}
        @if($canManageTenants || count($tenant->enabledModules()) > 0)
            @include('admin.tenants._modules-panel', [
                'tenant'           => $tenant,
                'availableModules' => $availableModules ?? [],
                'canManage'        => $canManageTenants,
            ])
        @endif

        {{-- ─── Iyzico Ayarları (BYOK) — sadece tours/commerce aktifse anlamlı ── --}}
        @if($tenant->hasModule('tours') || $tenant->hasModule('commerce'))
            @include('admin.tenants._iyzico-settings-panel', [
                'tenant'       => $tenant,
                'canManage'    => $canManageTenants,
                'iyzicoStatus' => $iyzicoStatus ?? ['configured' => false, 'sandbox' => true],
            ])
        @endif

        {{-- ─── Sektör Şablonu (FAZ 4.3) ───────────────────────────────────── --}}
        @if($canManageTenants)
            @include('admin.tenants._industry-template-panel', [
                'tenant' => $tenant,
            ])
        @endif

        {{-- ─── AI Kullanım Özeti ──────────────────────────────────────────── --}}
        @include('admin.tenants._ai-usage-panel', [
            'tenant'  => $tenant,
            'plan'    => $aiPlan,
            'usage'   => $aiUsage,
        ])

        {{-- ─── AI Ayarları (BYOK) ─────────────────────────────────────────── --}}
        @include('admin.tenants._ai-settings-panel', [
            'tenant'    => $tenant,
            'providers' => config('ai.providers'),
            'plans'     => config('ai.plans', []),
            'canManage' => $canManageTenants,
        ])
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
