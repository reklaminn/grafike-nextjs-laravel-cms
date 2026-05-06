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
    <!-- Alpine.js -->
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
            $tenantDomain = $tenantForPreview?->domains?->first()?->domain;

            if ($tenantDomain) {
                $liveSiteUrl = str_starts_with($tenantDomain, 'http://') || str_starts_with($tenantDomain, 'https://')
                    ? $tenantDomain
                    : 'https://' . $tenantDomain;
            }
        } catch (\Throwable) {
            $previewTenantName = $activeTenantId;
        }

        $visitSiteUrl = $frontendBaseUrl . (str_contains($frontendBaseUrl, '?') ? '&' : '?') . http_build_query([
            'tenant' => $activeTenantId,
        ]);
    } else {
        $visitSiteUrl = route('admin.tenants.index');
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
                @if(config('cms.agency.logo_dark') || config('cms.agency.logo_url'))
                <img src="{{ config('cms.agency.logo_dark') ?: config('cms.agency.logo_url') }}"
                     alt="{{ config('cms.agency.name') }}"
                     class="h-8 w-auto object-contain flex-shrink-0">
                @else
                <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center flex-shrink-0">
                    <span class="text-white font-bold text-sm">{{ mb_substr(config('cms.agency.name', 'G'), 0, 1) }}</span>
                </div>
                @endif
                <span x-show="sidebarOpen" x-transition class="font-bold text-gray-800 truncate">{{ config('cms.agency.name', config('cms.name', 'Grafike CMS')) }}</span>
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

@stack('scripts')
</body>
</html>
