<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') - {{ config('cms.agency.name', config('cms.name', 'Grafike CMS')) }}</title>
    @if(config('cms.agency.favicon_url'))
    <link rel="icon" href="{{ config('cms.agency.favicon_url') }}">
    @endif

    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js — collapse plugin core'dan ÖNCE yüklenmeli (defer sırayı korur);
         yoksa x-collapse "plugin yüklü değil" hatası verir (seo.blade / preview-panel) -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Heroicons (for inline SVG icons) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        [x-cloak] { display: none !important; }
        .sidebar-link.active { background-color: rgb(79 70 229); color: white; }
        .sidebar-link:hover:not(.active) { background-color: rgb(238 242 255); }
    </style>
    @stack('styles')
</head>
<body class="h-full" x-data="{ sidebarOpen: true, mobileMenuOpen: false }">
@php
    $frontendBaseUrl = rtrim(config('cms.frontend_url'), '/') ?: url('/');
    $activeTenantId = session('active_tenant') ?: Auth::guard('admin')->user()?->defaultTenantId();
    $isAdminAuthenticated = Auth::guard('admin')->check();
    $previewTenantName = null;
    $liveSiteUrl = null;

    if ($isAdminAuthenticated && $activeTenantId) {
        try {
            $tenantForPreview = tenancy()->central(
                fn () => \App\Models\Tenant::with('domains')->find($activeTenantId)
            );
            $previewTenantName = $tenantForPreview?->name ?? $activeTenantId;

            // Webmail butonu: tenant'ın mailcow_domain'i varsa göster
            $webmailUrl = null;
            $mailcowBase = \App\Models\CentralSetting::get('mailcow.url');
            if ($mailcowBase && $tenantForPreview?->mailcowDomain()) {
                $webmailUrl = rtrim($mailcowBase, '/');
            }

            // stancl VirtualColumn: data JSON'da 'domains' key varsa Eloquent
            // ilişkisi yerine array döner → Collection metotları çalışmaz.
            $rawDomains = $tenantForPreview?->domains;
            $firstDomain = null;
            if ($rawDomains instanceof \Illuminate\Support\Collection) {
                $firstDomain = $rawDomains->first();
            } elseif (is_array($rawDomains) && !empty($rawDomains)) {
                $firstDomain = $rawDomains[0];
            }
            $tenantDomain = is_object($firstDomain)
                ? $firstDomain->domain
                : ($firstDomain['domain'] ?? null);

            if ($tenantDomain) {
                $liveSiteUrl = str_starts_with($tenantDomain, 'http://') || str_starts_with($tenantDomain, 'https://')
                    ? $tenantDomain
                    : 'https://' . $tenantDomain;
            }
        } catch (\Throwable) {
            $previewTenantName = $activeTenantId;
            $webmailUrl = null;
        }

        $visitSiteUrl = $frontendBaseUrl . (str_contains($frontendBaseUrl, '?') ? '&' : '?') . http_build_query([
            'tenant' => $activeTenantId,
        ]);
    } else {
        $visitSiteUrl = route('admin.tenants.index');
        $webmailUrl   = null;
    }
@endphp

<div class="min-h-full">
    <!-- Mobile sidebar backdrop -->
    <div x-show="mobileMenuOpen" x-cloak
         class="fixed inset-0 z-40 bg-gray-600 bg-opacity-75 lg:hidden"
         @click="mobileMenuOpen = false"></div>

    <!-- Sidebar -->
    <aside :class="sidebarOpen ? 'w-64' : 'w-20'"
           class="fixed inset-y-0 left-0 z-50 hidden min-h-0 lg:flex lg:flex-col bg-white shadow-lg transition-all duration-300">

        <!-- Logo -->
        <div class="flex h-16 items-center justify-between px-4 border-b border-gray-200">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 min-w-0">
                @if(config('cms.agency.logo_url') || config('cms.agency.logo_dark'))
                {{-- Sidebar beyaz zeminli → koyu/normal logo (logo_url) kullan.
                     logo_dark koyu-zemin (açık renkli) varyant; sadece fallback. --}}
                <img src="{{ config('cms.agency.logo_url') ?: config('cms.agency.logo_dark') }}"
                     alt="{{ config('cms.agency.name') }}"
                     class="h-8 w-auto object-contain flex-shrink-0">
                @else
                <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center flex-shrink-0">
                    <span class="text-white font-bold text-sm">{{ mb_substr(config('cms.agency.name', 'G'), 0, 1) }}</span>
                </div>
                @endif
                {{-- Logo yanında isim gösterilmiyor; logo görseli yeterli --}}
            </a>
            <button @click="sidebarOpen = !sidebarOpen" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-bars"></i>
            </button>
        </div>

        <!-- Navigation -->
        <nav class="mt-4 flex-1 min-h-0 overflow-y-auto px-3 pb-4 space-y-1">
            @include('admin.partials.sidebar-nav')
        </nav>
    </aside>

    <!-- Mobile sidebar -->
    <aside x-show="mobileMenuOpen" x-cloak
           class="fixed inset-y-0 left-0 z-50 flex min-h-0 w-64 flex-col bg-white shadow-lg lg:hidden"
           x-transition:enter="transform transition-transform duration-300"
           x-transition:enter-start="-translate-x-full"
           x-transition:enter-end="translate-x-0"
           x-transition:leave="transform transition-transform duration-300"
           x-transition:leave-start="translate-x-0"
           x-transition:leave-end="-translate-x-full">

        <div class="flex h-16 items-center justify-between px-4 border-b">
            @if(config('cms.agency.logo_url'))
            <img src="{{ config('cms.agency.logo_url') }}" alt="{{ config('cms.agency.name') }}" class="h-7 w-auto object-contain">
            @else
            <span class="font-bold text-gray-800">{{ config('cms.agency.name', config('cms.name', 'Grafike CMS')) }}</span>
            @endif
            <button @click="mobileMenuOpen = false" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <nav class="mt-4 flex-1 min-h-0 overflow-y-auto px-3 pb-4 space-y-1">
            @include('admin.partials.sidebar-nav')
        </nav>
    </aside>

    <!-- Main content -->
    <div :class="sidebarOpen ? 'lg:pl-64' : 'lg:pl-20'" class="transition-all duration-300">

        <!-- Top navbar -->
        <header class="sticky top-0 z-30 bg-white shadow-sm border-b border-gray-200">
            <div class="flex h-16 items-center justify-between px-4 sm:px-6">
                <div class="flex items-center gap-4">
                    <button @click="mobileMenuOpen = true" class="lg:hidden text-gray-500 hover:text-gray-700">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <h1 class="text-lg font-semibold text-gray-800">@yield('page-title', 'Dashboard')</h1>
                </div>

                <div class="flex items-center gap-4">
                    @if($isAdminAuthenticated)
                        <!-- Global search -->
                        <div x-data="adminGlobalSearch()" class="relative hidden md:block"
                             @keydown.escape.window="open = false" @click.away="open = false">
                            <div class="relative">
                                <i class="fas fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                                <input type="text" x-model="query" @input.debounce.300ms="search()"
                                       @focus="query.length >= 2 && (open = true)"
                                       placeholder="Ara: sayfa, yazı, form, şablon…"
                                       class="w-48 lg:w-64 rounded-lg border border-gray-200 bg-gray-50 py-2 pl-8 pr-3 text-sm focus:border-transparent focus:bg-white focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <div x-show="open" x-cloak
                                 class="absolute left-0 right-0 z-50 mt-2 max-h-96 overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-xl">
                                <template x-if="loading">
                                    <div class="px-4 py-3 text-xs text-gray-400"><i class="fas fa-spinner fa-spin mr-1"></i> Aranıyor…</div>
                                </template>
                                <template x-if="!loading && Object.keys(groups).length === 0">
                                    <div class="px-4 py-3 text-xs text-gray-400">Sonuç bulunamadı.</div>
                                </template>
                                <template x-for="[groupKey, rows] in Object.entries(groups)" :key="groupKey">
                                    <div class="border-b border-gray-50 last:border-0">
                                        <p class="px-4 pt-2.5 pb-1 text-[10px] font-semibold uppercase tracking-widest text-gray-400"
                                           x-text="groupLabels[groupKey] || groupKey"></p>
                                        <template x-for="row in rows" :key="row.url">
                                            <a :href="row.url" class="block px-4 py-2 hover:bg-indigo-50">
                                                <span class="block truncate text-sm text-gray-800" x-text="row.label"></span>
                                                <span class="block truncate text-[11px] text-gray-400" x-text="row.sublabel"></span>
                                            </a>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Tenant preview -->
                        <a href="{{ $visitSiteUrl }}" target="{{ $activeTenantId ? '_blank' : '_self' }}"
                           title="{{ $activeTenantId ? 'Preview: ' . $previewTenantName : 'Önizleme için önce site seç' }}"
                           class="text-sm {{ $activeTenantId ? 'text-indigo-600 bg-indigo-50 hover:bg-indigo-100 px-3 py-2 rounded-lg font-medium' : 'text-amber-700 bg-amber-50 hover:bg-amber-100 px-3 py-2 rounded-lg font-medium' }} flex items-center gap-1">
                            <i class="fas fa-eye"></i>
                            <span class="hidden sm:inline">{{ $activeTenantId ? 'Preview' : 'Site seç' }}</span>
                        </a>

                        @if($liveSiteUrl)
                            <a href="{{ $liveSiteUrl }}" target="_blank"
                               title="Canlı site: {{ $liveSiteUrl }}"
                               class="text-sm text-gray-600 bg-gray-50 hover:bg-gray-100 px-3 py-2 rounded-lg font-medium flex items-center gap-1">
                                <i class="fas fa-external-link-alt"></i>
                                <span class="hidden sm:inline">Canlı Site</span>
                            </a>
                        @endif

                        @if(!empty($webmailUrl))
                            <a href="{{ $webmailUrl }}" target="_blank"
                               title="Webmail: {{ $webmailUrl }}"
                               class="text-sm text-blue-600 bg-blue-50 hover:bg-blue-100 px-3 py-2 rounded-lg font-medium flex items-center gap-1">
                                <i class="fas fa-envelope"></i>
                                <span class="hidden sm:inline">Webmail</span>
                            </a>
                        @endif
                    @endif

                    <!-- User dropdown -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open"
                                class="flex items-center gap-2 text-sm text-gray-700 hover:text-gray-900">
                            <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-indigo-600"></i>
                            </div>
                            <span class="hidden sm:inline">{{ Auth::guard('admin')->user()->name ?? 'Admin' }}</span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>

                        <div x-show="open" @click.away="open = false" x-cloak
                             class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border py-1 z-50">
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                <i class="fas fa-user-cog mr-2"></i> Profil
                            </a>
                            <hr class="my-1">
                            <form method="POST" action="{{ route('admin.logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                    <i class="fas fa-sign-out-alt mr-2"></i> Çıkış Yap
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        {{-- ── Kurulum Sihirbazı Onboarding Banner ───────────────────────── --}}
        @php
            $showSetupBanner = false;
            if ($isAdminAuthenticated && $activeTenantId
                && \Illuminate\Support\Facades\Route::has('admin.wizard.index')) {
                try {
                    $showSetupBanner = !\App\Models\SiteSetting::get('site.setup_completed')
                                      && !request()->routeIs('admin.wizard.*');
                } catch (\Throwable) {}
            }
        @endphp
        @if($showSetupBanner)
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600">
            <div class="flex items-center justify-between gap-4 px-6 py-2.5">
                <div class="flex items-center gap-2.5 text-white text-sm">
                    <i class="fas fa-wand-magic-sparkles animate-pulse"></i>
                    <span class="font-medium">Siteniz henüz kurulmadı.</span>
                    <span class="hidden sm:inline text-indigo-200">AI destekli sihirbaz ile dakikalar içinde tüm sayfalarınızı hazırlayın.</span>
                </div>
                <a href="{{ route('admin.wizard.index', [], false) }}"
                   class="flex-shrink-0 bg-white text-indigo-700 text-xs font-semibold px-3 py-1.5 rounded-lg hover:bg-indigo-50 transition-colors whitespace-nowrap">
                    Kurulumu Başlat <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>
        @endif

        <!-- Page content -->
        <main class="py-6 px-4 sm:px-6 lg:px-8">
            <!-- Flash messages -->
            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                     class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-check-circle"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button @click="show = false" class="text-green-600 hover:text-green-800">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div x-data="{ show: true }" x-show="show"
                     class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button @click="show = false" class="text-red-600 hover:text-red-800">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Hatalar oluştu:</strong>
                    </div>
                    <ul class="list-disc pl-8 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<script>
// Header global arama bileşeni — GET /admin/search?q=… (GlobalSearchController)
function adminGlobalSearch() {
    return {
        query: '',
        open: false,
        loading: false,
        groups: {},
        groupLabels: {
            pages: 'Sayfalar',
            articles: 'Yazılar',
            forms: 'Formlar',
            templates: 'Block Şablonları',
        },
        async search() {
            const q = this.query.trim();
            if (q.length < 2) { this.open = false; this.groups = {}; return; }

            this.open = true;
            this.loading = true;
            try {
                const r = await fetch(@js(route('admin.search', [], false)) + '?q=' + encodeURIComponent(q), {
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json' },
                });
                const data = await r.json().catch(() => ({ groups: {} }));
                // Yanıt gelene kadar kullanıcı yazmaya devam etmiş olabilir
                if (this.query.trim() === q) {
                    this.groups = data.groups || {};
                }
            } catch (e) {
                this.groups = {};
            } finally {
                this.loading = false;
            }
        },
    };
}
</script>

@stack('scripts')
</body>
</html>
