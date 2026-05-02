@extends('admin.layouts.app')

@section('title', 'Düzenle: ' . $menu->name)
@section('page-title', 'Menü Düzenle: ' . $menu->name)

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Menu Settings -->
        <div class="space-y-6">
            <form method="POST" action="{{ route('admin.menus.update', $menu, false) }}">
                @csrf @method('PUT')
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                    <h3 class="text-base font-semibold text-gray-800">Menü Ayarları</h3>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Menü Adı</label>
                        <input type="text" name="name" required value="{{ $menu->name }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Konum</label>
                        <select name="location" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                            <option value="header" {{ $menu->location === 'header' ? 'selected' : '' }}>Header</option>
                            <option value="footer" {{ $menu->location === 'footer' ? 'selected' : '' }}>Footer</option>
                            <option value="sidebar" {{ $menu->location === 'sidebar' ? 'selected' : '' }}>Sidebar</option>
                            <option value="mobile" {{ $menu->location === 'mobile' ? 'selected' : '' }}>Mobil</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Dil</label>
                        <select name="language_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                            @foreach($languages as $lang)
                                <option value="{{ $lang->id }}" {{ $menu->language_id == $lang->id ? 'selected' : '' }}>{{ $lang->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700">
                        <i class="fas fa-save mr-1"></i> Kaydet
                    </button>
                </div>
            </form>

            <!-- Add Menu Item -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6" x-data="{ type: 'custom' }">
                <h3 class="text-base font-semibold text-gray-800 mb-4">Öğe Ekle</h3>

                <div class="flex gap-2 mb-4">
                    <button type="button" @click="type = 'custom'" :class="type === 'custom' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-50 text-gray-600'"
                            class="flex-1 px-3 py-1.5 text-xs font-medium rounded-lg">Özel Link</button>
                    <button type="button" @click="type = 'page'" :class="type === 'page' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-50 text-gray-600'"
                            class="flex-1 px-3 py-1.5 text-xs font-medium rounded-lg">Sayfa</button>
                </div>

                <form id="addItemForm" class="space-y-3">
                    <div>
                        <input type="text" name="title" required placeholder="Menü öğesi adı"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                    <div x-show="type === 'custom'">
                        <input type="url" name="url" placeholder="https://..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                    <div x-show="type === 'page'">
                        <select name="page_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                            <option value="">Sayfa seçin</option>
                            @foreach($pages as $page)
                                <option value="{{ $page->id }}">{{ $page->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit"
                            class="w-full px-4 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700">
                        <i class="fas fa-plus mr-1"></i> Ekle
                    </button>
                </form>
            </div>
        </div>

        <!-- Menu Items Tree -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-base font-semibold text-gray-800 mb-4">
                    <i class="fas fa-list mr-2"></i>Menü Yapısı
                    <span class="text-sm font-normal text-gray-400">(Sürükle-bırak ile sıralayın)</span>
                </h3>

                <div id="menu-items" class="space-y-2">
                    @forelse($menu->items as $item)
                        @include('admin.menus._item', ['item' => $item, 'depth' => 0])
                    @empty
                        <div class="text-center py-8 text-gray-400">
                            <i class="fas fa-bars text-3xl mb-2"></i>
                            <p class="text-sm">Henüz menü öğesi yok. Sol panelden ekleyin.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.getElementById('addItemForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch('{{ route("admin.menus.add-item", $menu, false) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(Object.fromEntries(formData))
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) location.reload();
        });
    });
</script>
@endpush
