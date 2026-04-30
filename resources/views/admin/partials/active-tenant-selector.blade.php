@php
    $activeTenantId = session('active_tenant');
    $activeTenantName = $activeTenantId
        ? (\App\Models\Tenant::find($activeTenantId)?->name ?? $activeTenantId)
        : null;
@endphp

{{-- Active Site Selector (collapsed: just the badge; expanded: full pill) --}}
<div class="px-3 mb-3">
    <div x-show="sidebarOpen" x-transition>
        @if($activeTenantId)
        <a href="{{ route('admin.tenants.index') }}"
           class="flex items-center gap-2 w-full px-3 py-2 bg-indigo-50 border border-indigo-200 rounded-lg text-sm hover:bg-indigo-100 transition-colors">
            <span class="w-2 h-2 rounded-full bg-green-500 flex-shrink-0"></span>
            <div class="flex-1 min-w-0">
                <p class="text-[10px] text-indigo-400 uppercase tracking-wider font-semibold">Aktif Site</p>
                <p class="text-indigo-700 font-semibold text-xs truncate">{{ $activeTenantName }}</p>
            </div>
            <i class="fas fa-exchange-alt text-indigo-400 text-xs flex-shrink-0"></i>
        </a>
        @else
        <a href="{{ route('admin.tenants.index') }}"
           class="flex items-center gap-2 w-full px-3 py-2 bg-amber-50 border border-amber-200 rounded-lg text-sm hover:bg-amber-100 transition-colors">
            <span class="w-2 h-2 rounded-full bg-amber-400 flex-shrink-0"></span>
            <div class="flex-1">
                <p class="text-[10px] text-amber-500 uppercase tracking-wider font-semibold">Site Seçilmedi</p>
                <p class="text-amber-700 text-xs font-medium">Site seç</p>
            </div>
            <i class="fas fa-chevron-right text-amber-400 text-xs flex-shrink-0"></i>
        </a>
        @endif
    </div>

    {{-- Collapsed state: just a colored dot --}}
    <div x-show="!sidebarOpen" x-cloak class="flex justify-center">
        <a href="{{ route('admin.tenants.index') }}" title="{{ $activeTenantId ? $activeTenantName : 'Site seçilmedi' }}">
            <span class="w-3 h-3 rounded-full {{ $activeTenantId ? 'bg-green-500' : 'bg-amber-400' }} block"></span>
        </a>
    </div>
</div>
