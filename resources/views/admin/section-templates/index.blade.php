@extends('admin.layouts.app')

@section('title', 'Block Şablonları')
@section('page-title', 'Block Şablonları')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm text-gray-500">
                    Yeni builder picker'ında görünen block şablonlarını buradan yönet.
                </p>
            </div>
            <a href="{{ route('admin.section-templates.create') }}"
               class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                <i class="fas fa-plus"></i> Yeni Block Şablonu
            </a>
        </div>

        {{-- Filtreler --}}
        <form method="GET" class="grid grid-cols-1 gap-3 rounded-xl border border-gray-200 bg-white p-4 md:grid-cols-2 xl:grid-cols-[minmax(0,1fr)_220px_220px_180px_auto]">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Ara</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Ad, type, variation veya legacy modül..."
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Tema</label>
                <select name="theme_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-indigo-500">
                    <option value="">Tüm temalar</option>
                    @foreach($themes as $theme)
                        <option value="{{ $theme->id }}" @selected((string) request('theme_id') === (string) $theme->id)>
                            {{ $theme->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Type</label>
                <select name="type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-indigo-500">
                    <option value="">Tüm type'lar</option>
                    @foreach($typeOptions as $value => $label)
                        <option value="{{ $value }}" @selected(request('type') === $value)>
                            {{ $label }} ({{ $value }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Render</label>
                <select name="render_mode" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-indigo-500">
                    <option value="">Tüm render modları</option>
                    <option value="html" @selected(request('render_mode') === 'html')>html</option>
                    <option value="component" @selected(request('render_mode') === 'component')>component</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">
                    <i class="fas fa-search"></i> Filtrele
                </button>
                @if(request()->hasAny(['q', 'theme_id', 'type', 'render_mode', 'status']))
                    <a href="{{ route('admin.section-templates.index') }}" class="inline-flex items-center gap-2 rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">
                        Temizle
                    </a>
                @endif
            </div>
        </form>

        {{-- Status filter pills --}}
        <div class="flex flex-wrap items-center gap-2">
            @php
                $statusParam = request('status', '');
                $baseQuery = request()->except('status', 'page', 'trashed');
            @endphp

            @if(! $trashed)
                <span class="text-sm text-gray-500">Durum:</span>
                <a href="{{ route('admin.section-templates.index', $baseQuery) }}"
                   class="rounded-full px-3 py-1 text-xs font-medium {{ $statusParam === '' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    Tümü
                </a>
                <a href="{{ route('admin.section-templates.index', array_merge($baseQuery, ['status' => 'active'])) }}"
                   class="rounded-full px-3 py-1 text-xs font-medium {{ $statusParam === 'active' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    Aktif
                </a>
                <a href="{{ route('admin.section-templates.index', array_merge($baseQuery, ['status' => 'inactive'])) }}"
                   class="rounded-full px-3 py-1 text-xs font-medium {{ $statusParam === 'inactive' ? 'bg-gray-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    Pasif
                </a>
            @else
                <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-800">
                    <i class="fas fa-archive mr-1"></i> Arşiv görünümü
                </span>
            @endif

            <span class="ml-auto text-sm text-gray-400">{{ $sectionTemplates->total() }} şablon</span>

            {{-- Arşiv toggle --}}
            @if(! $trashed)
                <a href="{{ route('admin.section-templates.index', ['trashed' => 1]) }}"
                   class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium {{ $trashedCount > 0 ? 'bg-amber-50 text-amber-700 hover:bg-amber-100' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                    <i class="fas fa-archive"></i> Arşiv
                    @if($trashedCount > 0)
                        <span class="rounded-full bg-amber-200 px-1.5 font-semibold text-amber-900">{{ $trashedCount }}</span>
                    @endif
                </a>
            @else
                <a href="{{ route('admin.section-templates.index') }}"
                   class="inline-flex items-center gap-1.5 rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700 hover:bg-indigo-100">
                    <i class="fas fa-arrow-left"></i> Aktif şablonlar
                </a>
            @endif
        </div>

        {{-- Kart grid --}}
        <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
            @forelse($sectionTemplates as $sectionTemplate)
                @php
                    $usageCount = $usageCounts[$sectionTemplate->id] ?? 0;
                    $usedPages = array_values($usageMap[$sectionTemplate->id] ?? []);
                @endphp
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    {{-- Preview görsel --}}
                    @php $previewImgUrl = $sectionTemplate->getFirstMediaUrl('preview_image'); @endphp
                    @if($previewImgUrl)
                        <div class="aspect-video w-full overflow-hidden bg-gray-100">
                            <img src="{{ $previewImgUrl }}"
                                 alt="{{ $sectionTemplate->name }}"
                                 class="h-full w-full object-cover">
                        </div>
                    @else
                        @php
                            $typeIconMap = [
                                'hero' => 'fa-image', 'hero-banner' => 'fa-image',
                                'header' => 'fa-bars', 'footer' => 'fa-grip-lines',
                                'slider' => 'fa-film', 'gallery' => 'fa-images',
                                'rich-text' => 'fa-align-left', 'content-block' => 'fa-file-lines',
                                'article-list' => 'fa-newspaper', 'features' => 'fa-star',
                                'cta' => 'fa-bullhorn', 'testimonials' => 'fa-quote-left',
                                'cards' => 'fa-id-card', 'spacer' => 'fa-arrows-up-down',
                                'video-embed' => 'fa-play-circle', 'page-header' => 'fa-heading',
                                'menu' => 'fa-list-ul',
                            ];
                            $icon = $typeIconMap[$sectionTemplate->type] ?? 'fa-cubes';
                        @endphp
                        <div class="flex aspect-video w-full items-center justify-center bg-gradient-to-br from-gray-50 to-gray-100">
                            <i class="fas {{ $icon }} text-4xl text-gray-300"></i>
                        </div>
                    @endif

                    <div class="p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-base font-semibold text-gray-900 truncate">{{ $sectionTemplate->name }}</h3>
                                </div>
                                <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                                    <span class="rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700">
                                        {{ $sectionTemplate->theme?->slug ?? 'tema yok' }}
                                    </span>
                                    <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $sectionTemplate->render_mode === 'component' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        <i class="fas {{ $sectionTemplate->render_mode === 'component' ? 'fa-puzzle-piece' : 'fa-code' }} mr-1"></i>{{ $sectionTemplate->render_mode }}
                                    </span>
                                    <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $sectionTemplate->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-500' }}">
                                        {{ $sectionTemplate->is_active ? 'Aktif' : 'Pasif' }}
                                    </span>
                                    @if($usageCount > 0)
                                        <span class="rounded-full bg-sky-50 px-2 py-0.5 text-xs font-medium text-sky-700">
                                            <i class="fas fa-file-lines mr-1"></i>{{ $usageCount }} sayfada kullanılıyor
                                        </span>
                                    @endif
                                </div>
                                <p class="mt-1.5 text-sm text-gray-500">
                                    <span class="font-medium text-gray-700">{{ $sectionTemplate->type }}</span>
                                    <span class="mx-1">·</span>
                                    <span>{{ $sectionTemplate->variation }}</span>
                                </p>
                            </div>
                        </div>

                        <dl class="mt-4 grid grid-cols-3 gap-2 text-sm text-gray-600">
                            <div class="rounded-lg bg-gray-50 px-2.5 py-2">
                                <dt class="text-[10px] uppercase tracking-wide text-gray-400">Legacy</dt>
                                <dd class="mt-0.5 truncate text-xs font-medium text-gray-700">{{ $sectionTemplate->legacy_module_key ?: '—' }}</dd>
                            </div>
                            <div class="rounded-lg bg-gray-50 px-2.5 py-2">
                                <dt class="text-[10px] uppercase tracking-wide text-gray-400">Component</dt>
                                <dd class="mt-0.5 truncate text-xs font-medium text-gray-700">{{ $sectionTemplate->component_key ?: '—' }}</dd>
                            </div>
                            <div class="rounded-lg bg-gray-50 px-2.5 py-2">
                                <dt class="text-[10px] uppercase tracking-wide text-gray-400">Schema</dt>
                                <dd class="mt-0.5 text-xs font-medium text-gray-700">{{ count($sectionTemplate->schema_json ?? []) }} alan</dd>
                            </div>
                        </dl>

                        <div class="mt-4 rounded-lg border border-gray-100 bg-gray-50 px-3 py-2">
                            <div class="flex items-center justify-between gap-3">
                                <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Kullanan Sayfalar</div>
                                <span class="rounded-full bg-white px-2 py-0.5 text-xs font-medium text-gray-600">{{ $usageCount }}</span>
                            </div>
                            @if($usageCount > 0)
                                <div class="mt-2 flex flex-wrap gap-1.5">
                                    @foreach(array_slice($usedPages, 0, 4) as $usedPage)
                                        <a href="{{ route('admin.pages.edit', $usedPage['id']) }}"
                                           class="rounded-full bg-sky-50 px-2 py-1 text-xs font-medium text-sky-700 hover:bg-sky-100"
                                           title="/{{ $usedPage['slug'] }}">
                                            <i class="fas fa-file-lines mr-1"></i>{{ $usedPage['title'] }}
                                        </a>
                                    @endforeach
                                    @if($usageCount > 4)
                                        <span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-500">+{{ $usageCount - 4 }} daha</span>
                                    @endif
                                </div>
                            @else
                                <p class="mt-1 text-xs text-gray-500">Bu şablon henüz hiçbir sayfada kullanılmıyor.</p>
                            @endif
                        </div>

                        <div class="mt-4 flex items-center gap-2">
                            @if(! $trashed)
                                <a href="{{ route('admin.section-templates.edit', $sectionTemplate) }}"
                                   class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-50 px-3 py-1.5 text-sm font-medium text-indigo-700 hover:bg-indigo-100">
                                    <i class="fas fa-pen"></i> Düzenle
                                </a>
                                <form method="POST" action="{{ route('admin.section-templates.duplicate', $sectionTemplate, false) }}">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center gap-1.5 rounded-lg bg-gray-50 px-3 py-1.5 text-sm font-medium text-gray-600 hover:bg-gray-100">
                                        <i class="fas fa-copy"></i> Klonla
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.section-templates.destroy', $sectionTemplate) }}"
                                      class="ml-auto"
                                      onsubmit="return confirm('{{ $usageCount > 0 ? "Bu şablon {$usageCount} sayfada kullanılıyor! Arşivlemek istiyor musunuz?" : "Bu block şablonunu arşivlemek istiyor musunuz?" }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-amber-600 hover:bg-amber-50 {{ $usageCount > 0 ? 'ring-1 ring-amber-200' : '' }}"
                                            title="Arşivle">
                                        <i class="fas fa-archive"></i>
                                        @if($usageCount > 0)
                                            <span class="text-xs font-medium">{{ $usageCount }}</span>
                                        @endif
                                    </button>
                                </form>
                            @else
                                {{-- Arşivlenmiş: geri yükle + kalıcı sil --}}
                                <form method="POST" action="{{ route('admin.section-templates.restore', $sectionTemplate, false) }}">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center gap-1.5 rounded-lg bg-green-50 px-3 py-1.5 text-sm font-medium text-green-700 hover:bg-green-100">
                                        <i class="fas fa-rotate-left"></i> Geri Yükle
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.section-templates.force-delete', $sectionTemplate, false) }}"
                                      class="ml-auto"
                                      onsubmit="return confirm('Bu şablon kalıcı olarak silinecek. Geri alınamaz. Devam etmek istiyor musunuz?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-red-500 hover:bg-red-50">
                                        <i class="fas fa-trash-alt"></i> Kalıcı Sil
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-xl border border-dashed border-gray-300 bg-white py-16 text-center text-gray-400">
                    <i class="fas fa-cubes mb-3 text-4xl"></i>
                    <p>Henüz block şablonu bulunmuyor.</p>
                </div>
            @endforelse
        </div>

        {{ $sectionTemplates->links() }}
    </div>
@endsection
