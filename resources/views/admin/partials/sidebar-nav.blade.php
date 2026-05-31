@include('admin.partials.active-tenant-selector')

@php
    $currentRoute = request()->route()?->getName() ?? '';
    $isAgencyAdmin = auth('admin')->user()?->isAgencyAdmin() ?? false;

    // Vertical-module flags drive conditional nav groups (Tours,
    // Commerce, …).  Resolved from the session-selected tenant — pure
    // central-context lookup, no tenant DB queries.
    $activeTenantId = session('active_tenant');
    $activeTenant   = $activeTenantId ? \App\Models\Tenant::query()->find($activeTenantId) : null;
    $hasTours       = $activeTenant?->hasModule('tours')    ?? false;
    $hasCommerce    = $activeTenant?->hasModule('commerce') ?? false;

    $toursItems = $hasTours ? [
        ['route' => 'admin.tours.index',           'icon' => 'fa-route',        'label' => 'Turlar',         'match' => 'admin.tours.index'],
        ['route' => 'admin.tour-categories.index', 'icon' => 'fa-folder-tree',  'label' => 'Tur Kategorileri','match' => 'admin.tour-categories'],
        ['route' => 'admin.tour-tags.index',       'icon' => 'fa-tags',         'label' => 'Etiketler',      'match' => 'admin.tour-tags'],
        ['route' => 'admin.tour-bookings.index',   'icon' => 'fa-ticket',       'label' => 'Rezervasyonlar', 'match' => 'admin.tour-bookings'],
    ] : [];

    // Cruise/master entity yönetim grubu — Tours modülü aktifse görünür.
    // Phase 1.5.d (Ship → Cabin → Port → Destination master CRUD'ları).
    $cruiseItems = $hasTours ? [
        ['route' => 'admin.ship-companies.index',   'icon' => 'fa-building',     'label' => 'Gemi Firmaları',   'match' => 'admin.ship-companies'],
        ['route' => 'admin.ships.index',            'icon' => 'fa-ship',         'label' => 'Gemiler',          'match' => 'admin.ships'],
        ['route' => 'admin.cabin-categories.index', 'icon' => 'fa-list-ul',      'label' => 'Kabin Kategorileri','match' => 'admin.cabin-categories'],
        ['route' => 'admin.cabins.index',           'icon' => 'fa-bed',          'label' => 'Kabinler',         'match' => 'admin.cabins'],
        ['route' => 'admin.cabin-groups.index',     'icon' => 'fa-layer-group',  'label' => 'Kabin Grupları',   'match' => 'admin.cabin-groups'],
        ['route' => 'admin.ports.index',            'icon' => 'fa-anchor',       'label' => 'Limanlar',         'match' => 'admin.ports'],
        ['route' => 'admin.destinations.index',     'icon' => 'fa-map-marked-alt','label' => 'Destinasyonlar',  'match' => 'admin.destinations'],
    ] : [];

    $navItems = [
        ['route' => 'admin.dashboard', 'icon' => 'fa-tachometer-alt', 'label' => 'Dashboard', 'match' => 'admin.dashboard'],
        ['route' => 'admin.pages.index', 'icon' => 'fa-file-alt', 'label' => 'Sayfalar', 'match' => 'admin.pages'],
        ['route' => 'admin.articles.index', 'icon' => 'fa-newspaper', 'label' => 'Yazılar', 'match' => 'admin.articles'],
        ['route' => 'admin.menus.index', 'icon' => 'fa-bars', 'label' => 'Menüler', 'match' => 'admin.menus'],
        ['route' => 'admin.forms.index', 'icon' => 'fa-wpforms', 'label' => 'Formlar', 'match' => 'admin.forms'],
        ['route' => 'admin.media.index', 'icon' => 'fa-images', 'label' => 'Medya', 'match' => 'admin.media'],
        ['route' => 'admin.reviews.index', 'icon' => 'fa-star', 'label' => 'Yorumlar', 'match' => 'admin.reviews'],
        ['route' => 'admin.members.index', 'icon' => 'fa-users', 'label' => 'Üyeler', 'match' => 'admin.members'],
    ];

    $seoItems = [
        ['route' => 'admin.seo.index', 'icon' => 'fa-search', 'label' => 'SEO', 'match' => 'admin.seo'],
        ['route' => 'admin.redirects.index', 'icon' => 'fa-exchange-alt', 'label' => 'Yönlendirmeler', 'match' => 'admin.redirects'],
        ['route' => 'admin.sitemap.index', 'icon' => 'fa-sitemap', 'label' => 'Sitemap', 'match' => 'admin.sitemap'],
        ['route' => 'admin.languages.index', 'icon' => 'fa-globe', 'label' => 'Diller', 'match' => 'admin.languages'],
        ['route' => 'admin.translations.index', 'icon' => 'fa-language', 'label' => 'Çeviriler', 'match' => 'admin.translations'],
    ];

    $designItems = [
        ['route' => 'admin.themes.index', 'icon' => 'fa-swatchbook', 'label' => 'Temalar', 'match' => 'admin.themes'],
        ['route' => 'admin.section-templates.index', 'icon' => 'fa-cubes', 'label' => 'Block Şablonları', 'match' => 'admin.section-templates'],
        ['route' => 'admin.design.index', 'icon' => 'fa-palette', 'label' => 'Tasarım (CSS/JS)', 'match' => 'admin.design'],
        ['route' => 'admin.smtp-profiles.index', 'icon' => 'fa-envelope', 'label' => 'SMTP Profilleri', 'match' => 'admin.smtp-profiles'],
        ['route' => 'admin.currencies.index', 'icon' => 'fa-money-bill-wave', 'label' => 'Döviz Kurları', 'match' => 'admin.currencies'],
    ];

    $systemItems = [
        ['route' => 'admin.tenants.index',    'icon' => 'fa-building',    'label' => 'Siteler',           'match' => 'admin.tenants'],
        ['route' => 'admin.settings.index',   'icon' => 'fa-cog',         'label' => 'Ayarlar',           'match' => 'admin.settings.index'],
        ['route' => 'admin.settings.business','icon' => 'fa-map-marker-alt','label' => 'İşletme Bilgileri','match' => 'admin.settings.business'],
        ['route' => 'admin.settings.crawl',   'icon' => 'fa-robot',       'label' => 'Tarama & LLM',      'match' => 'admin.settings.crawl'],
    ];

    if ($isAgencyAdmin) {
        array_splice($systemItems, 1, 0, [
            ['route' => 'admin.packages.index',    'icon' => 'fa-box-open',    'label' => 'Paketler',          'match' => 'admin.packages'],
            ['route' => 'admin.ai-plans.index',   'icon' => 'fa-robot',       'label' => 'AI Planları',        'match' => 'admin.ai-plans'],
            ['route' => 'admin.admin-users.index', 'icon' => 'fa-user-shield', 'label' => 'Yöneticiler',       'match' => 'admin.admin-users'],
            ['route' => 'admin.roles.index',       'icon' => 'fa-key',         'label' => 'Roller/Yetkiler',   'match' => 'admin.roles'],
            ['route' => 'admin.ai-dashboard',      'icon' => 'fa-chart-pie',   'label' => 'AI Kullanım',       'match' => 'admin.ai-dashboard'],
            ['route' => 'admin.maintenance.index', 'icon' => 'fa-database',    'label' => 'DB Bakım',          'match' => 'admin.maintenance'],
            ['route' => 'admin.activity-log.index','icon' => 'fa-history',     'label' => 'Aktivite Log',      'match' => 'admin.activity-log'],
            ['route' => 'admin.library.index',     'icon' => 'fa-ship',        'label' => 'Cruise Kütüphanesi','match' => 'admin.library'],
        ]);
    } elseif ($activeTenantId) {
        // Tenant admin: kendi sitesinin AI kullanım raporu (per-tenant görünüm).
        array_splice($systemItems, 1, 0, [
            ['route' => 'admin.tenants.ai-usage', 'icon' => 'fa-chart-pie', 'label' => 'AI Kullanım', 'match' => 'admin.tenants.ai-usage', 'url' => route('admin.tenants.ai-usage', $activeTenantId, false)],
        ]);
    }
@endphp

{{-- ── Kurulum Sihirbazı — her zaman erişilebilir, flag'e göre stil --}}
@if($activeTenantId && \Illuminate\Support\Facades\Route::has('admin.wizard.index'))
@php
    $wizardDone = false;
    try { $wizardDone = (bool) \App\Models\SiteSetting::get('site.setup_completed'); } catch (\Throwable) {}
@endphp
<a href="{{ route('admin.wizard.index') }}"
   class="sidebar-link mb-1 flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
          {{ str_starts_with($currentRoute, 'admin.wizard') ? 'active' : ($wizardDone ? 'text-gray-600 hover:bg-gray-50' : 'text-purple-700 bg-purple-50 border border-purple-200 hover:bg-purple-100') }}">
    <i class="fas fa-wand-magic-sparkles w-5 text-center text-base"></i>
    <span x-show="sidebarOpen" x-transition class="flex items-center justify-between w-full">
        <span>{{ $wizardDone ? 'Site Sihirbazı' : 'Site Kur' }}</span>
        @if(!$wizardDone)
        <span class="ml-2 flex h-2 w-2 relative">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
        </span>
        @endif
    </span>
</a>
<div class="my-1 border-t border-gray-100"></div>
@endif

<!-- Main navigation -->
@foreach($navItems as $item)
    <a href="{{ route($item['route']) }}"
       class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-700 transition-colors {{ str_starts_with($currentRoute, $item['match']) ? 'active' : '' }}">
        <i class="fas {{ $item['icon'] }} w-5 text-center text-base"></i>
        <span x-show="sidebarOpen" x-transition>{{ $item['label'] }}</span>
    </a>
@endforeach

<!-- Tours (module-gated; only shows when active tenant has 'tours' enabled) -->
@if(!empty($toursItems))
<div class="my-3 border-t border-gray-200"></div>
<div class="px-3 pt-2 pb-1">
    <span class="text-[10px] font-semibold text-amber-500 uppercase tracking-wider" x-show="sidebarOpen" x-transition>
        <i class="fas fa-route mr-0.5"></i> Turizm
    </span>
</div>
@foreach($toursItems as $item)
    <a href="{{ route($item['route']) }}"
       class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-700 transition-colors {{ str_starts_with($currentRoute, $item['match']) ? 'active' : '' }}">
        <i class="fas {{ $item['icon'] }} w-5 text-center text-base"></i>
        <span x-show="sidebarOpen" x-transition>{{ $item['label'] }}</span>
    </a>
@endforeach
@endif

<!-- Cruise Yönetimi (Phase 1.5.d — ship/cabin/port/destination master CRUD'ları) -->
@if(!empty($cruiseItems))
<div class="my-2 border-t border-gray-100"></div>
<div class="px-3 pt-1 pb-1">
    <span class="text-[10px] font-semibold text-blue-500 uppercase tracking-wider" x-show="sidebarOpen" x-transition>
        <i class="fas fa-ship mr-0.5"></i> Cruise Yönetimi
    </span>
</div>
@foreach($cruiseItems as $item)
    <a href="{{ route($item['route']) }}"
       class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-700 transition-colors {{ str_starts_with($currentRoute, $item['match']) ? 'active' : '' }}">
        <i class="fas {{ $item['icon'] }} w-5 text-center text-base"></i>
        <span x-show="sidebarOpen" x-transition>{{ $item['label'] }}</span>
    </a>
@endforeach
@endif

<!-- SEO & Diller -->
<div class="my-3 border-t border-gray-200"></div>
<div class="px-3 pt-2 pb-1">
    <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider" x-show="sidebarOpen" x-transition>SEO & Diller</span>
</div>
@foreach($seoItems as $item)
    <a href="{{ route($item['route']) }}"
       class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-700 transition-colors {{ str_starts_with($currentRoute, $item['match']) ? 'active' : '' }}">
        <i class="fas {{ $item['icon'] }} w-5 text-center text-base"></i>
        <span x-show="sidebarOpen" x-transition>{{ $item['label'] }}</span>
    </a>
@endforeach

<!-- Tasarım & Entegrasyonlar -->
<div class="my-3 border-t border-gray-200"></div>
<div class="px-3 pt-2 pb-1">
    <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider" x-show="sidebarOpen" x-transition>Tasarım</span>
</div>
@foreach($designItems as $item)
    <a href="{{ route($item['route']) }}"
       class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-700 transition-colors {{ str_starts_with($currentRoute, $item['match']) ? 'active' : '' }}">
        <i class="fas {{ $item['icon'] }} w-5 text-center text-base"></i>
        <span x-show="sidebarOpen" x-transition>{{ $item['label'] }}</span>
    </a>
@endforeach

<!-- Sistem -->
<div class="my-3 border-t border-gray-200"></div>
<div class="px-3 pt-2 pb-1">
    <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider" x-show="sidebarOpen" x-transition>Sistem</span>
</div>
@foreach($systemItems as $item)
    <a href="{{ $item['url'] ?? route($item['route']) }}"
       class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-700 transition-colors {{ str_starts_with($currentRoute, $item['match']) ? 'active' : '' }}">
        <i class="fas {{ $item['icon'] }} w-5 text-center text-base"></i>
        <span x-show="sidebarOpen" x-transition>{{ $item['label'] }}</span>
    </a>
@endforeach
