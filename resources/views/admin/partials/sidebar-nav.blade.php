@php
    $currentRoute = request()->route()?->getName() ?? '';

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
        ['route' => 'admin.admin-users.index', 'icon' => 'fa-user-shield', 'label' => 'Yöneticiler', 'match' => 'admin.admin-users'],
        ['route' => 'admin.roles.index', 'icon' => 'fa-key', 'label' => 'Roller/Yetkiler', 'match' => 'admin.roles'],
        ['route' => 'admin.maintenance.index', 'icon' => 'fa-database', 'label' => 'DB Bakım', 'match' => 'admin.maintenance'],
        ['route' => 'admin.activity-log.index', 'icon' => 'fa-history', 'label' => 'Aktivite Log', 'match' => 'admin.activity-log'],
        ['route' => 'admin.settings.index', 'icon' => 'fa-cog', 'label' => 'Ayarlar', 'match' => 'admin.settings'],
    ];
@endphp

<!-- Main navigation -->
@foreach($navItems as $item)
    <a href="{{ route($item['route']) }}"
       class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-700 transition-colors {{ str_starts_with($currentRoute, $item['match']) ? 'active' : '' }}">
        <i class="fas {{ $item['icon'] }} w-5 text-center text-base"></i>
        <span x-show="sidebarOpen" x-transition>{{ $item['label'] }}</span>
    </a>
@endforeach

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
    <a href="{{ route($item['route']) }}"
       class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-700 transition-colors {{ str_starts_with($currentRoute, $item['match']) ? 'active' : '' }}">
        <i class="fas {{ $item['icon'] }} w-5 text-center text-base"></i>
        <span x-show="sidebarOpen" x-transition>{{ $item['label'] }}</span>
    </a>
@endforeach
