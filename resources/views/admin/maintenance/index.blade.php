@extends('admin.layouts.app')

@section('title', 'Veritabanı Bakımı')
@section('page-title', 'Veritabanı Bakımı')

@section('content')
    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="text-2xl font-bold text-gray-800">{{ $stats['pages'] }}</div>
            <div class="text-xs text-gray-500 mt-1">Toplam Sayfa</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="text-2xl font-bold text-gray-800">{{ $stats['articles'] }}</div>
            <div class="text-xs text-gray-500 mt-1">Toplam Yazı</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="text-2xl font-bold text-gray-800">{{ $stats['seo_entries'] }}</div>
            <div class="text-xs text-gray-500 mt-1">SEO Girdisi</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="text-2xl font-bold text-gray-800">{{ $stats['media'] }}</div>
            <div class="text-xs text-gray-500 mt-1">Medya Dosyası</div>
        </div>
    </div>

    {{-- Orphan Detection --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-base font-semibold text-gray-800">
                <i class="fas fa-search mr-2 text-amber-500"></i>Yetim Kayıt Tespiti
            </h3>
            <p class="text-xs text-gray-400 mt-1">İlişkisi kopmuş veya geçersiz kayıtları tespit edin ve temizleyin.</p>
        </div>

        <div class="divide-y divide-gray-100">
            @php
                $items = [
                    ['key' => 'pages_no_language', 'label' => 'Dili olmayan sayfalar', 'icon' => 'fa-file-alt', 'color' => 'blue'],
                    ['key' => 'articles_no_page', 'label' => 'Sayfası olmayan yazılar', 'icon' => 'fa-newspaper', 'color' => 'green'],
                    ['key' => 'seo_no_target', 'label' => 'Hedefi olmayan SEO girdileri', 'icon' => 'fa-search', 'color' => 'purple'],
                    ['key' => 'unused_media', 'label' => 'Kullanılmayan medya dosyaları', 'icon' => 'fa-images', 'color' => 'amber'],
                    ['key' => 'trashed_pages', 'label' => 'Çöp kutusundaki sayfalar', 'icon' => 'fa-trash', 'color' => 'red'],
                    ['key' => 'trashed_articles', 'label' => 'Çöp kutusundaki yazılar', 'icon' => 'fa-trash', 'color' => 'red'],
                ];
            @endphp

            @foreach($items as $item)
                <div class="flex items-center justify-between px-6 py-4 hover:bg-gray-50">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-{{ $item['color'] }}-50 rounded-lg flex items-center justify-center">
                            <i class="fas {{ $item['icon'] }} text-{{ $item['color'] }}-500"></i>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-800">{{ $item['label'] }}</span>
                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $orphans[$item['key']] > 0 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                {{ $orphans[$item['key']] }}
                            </span>
                        </div>
                    </div>
                    @if($orphans[$item['key']] > 0)
                        <form method="POST" action="{{ route('admin.maintenance.cleanup', [], false) }}"
                              onsubmit="return confirm('{{ $orphans[$item['key']] }} kayıt kalıcı olarak silinecek. Emin misiniz?')">
                            @csrf
                            <input type="hidden" name="type" value="{{ $item['key'] }}">
                            <button type="submit"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-50 text-red-700 text-xs font-medium rounded-lg hover:bg-red-100 transition-colors">
                                <i class="fas fa-broom"></i> Temizle
                            </button>
                        </form>
                    @else
                        <span class="text-xs text-green-600"><i class="fas fa-check-circle mr-1"></i>Temiz</span>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-xl">
            <div class="flex items-center gap-2 text-sm">
                <span class="font-medium text-gray-700">Toplam yetim kayıt:</span>
                <span class="font-bold {{ $orphans['total'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                    {{ $orphans['total'] }}
                </span>
            </div>
        </div>
    </div>
@endsection
