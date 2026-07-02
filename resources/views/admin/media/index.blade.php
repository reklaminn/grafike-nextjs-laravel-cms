@extends('admin.layouts.app')
@section('title', 'Medya Kütüphanesi')
@section('page-title', 'Medya Kütüphanesi')

@section('content')

@if(session('success'))
    <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-5 py-4 flex items-center gap-3 text-green-800 text-sm font-medium">
        <i class="fas fa-circle-check text-green-500"></i> {{ session('success') }}
    </div>
@endif

{{-- Header + Stats --}}
<div class="flex items-center justify-between mb-6">
    <div class="text-sm text-gray-500 flex gap-4">
        <span><i class="fas fa-photo-film mr-1"></i> {{ number_format($stats['total']) }} dosya</span>
        <span><i class="fas fa-image mr-1 text-blue-400"></i> {{ number_format($stats['images']) }} resim</span>
        <span><i class="fas fa-database mr-1 text-gray-400"></i> {{ number_format($stats['total_size'] / 1048576, 1) }} MB</span>
    </div>
    <label class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 cursor-pointer transition-colors">
        <i class="fas fa-upload"></i> Dosya Yükle
        {{-- Bu buton mediaLibrary() component'inin DIŞINDA; bare x-data izole
             bir scope yaratıyordu, uploadFiles tanımsızdı → buton çalışmıyordu.
             Window event ile component'e ilet. --}}
        <input type="file" class="hidden" multiple
               accept="{{ implode(',', array_map(fn($ext) => '.' . $ext, config('cms.media.allowed_extensions', []))) }}"
               @change="$dispatch('media-upload-files', $event.target.files)" x-data>
    </label>
</div>

{{-- Main Alpine component --}}
<div x-data="mediaLibrary()" x-init="init()" @media-upload-files.window="uploadFiles($event.detail)">

    {{-- Upload drop zone --}}
    <div class="bg-white rounded-xl shadow-sm border p-5 mb-5"
         :class="dragging ? 'border-indigo-400 bg-indigo-50' : 'border-gray-200'"
         @dragover.prevent="dragging = true" @dragleave.prevent="dragging = false"
         @drop.prevent="dragging = false; uploadFiles($event.dataTransfer.files)"
         x-show="!uploading">
        <div class="flex items-center gap-3 text-gray-400 justify-center py-3">
            <i class="fas fa-cloud-upload-alt text-2xl"></i>
            <span class="text-sm">Dosyaları buraya sürükleyin</span>
        </div>
    </div>

    {{-- Upload progress --}}
    <div x-show="uploading" class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-5 flex items-center gap-3 text-blue-700 text-sm">
        <i class="fas fa-spinner fa-spin"></i>
        <span x-text="uploadStatus">Yükleniyor...</span>
    </div>

    {{-- Filters --}}
    <form method="GET" class="flex flex-wrap gap-3 mb-5">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Dosya adı ara..."
               class="flex-1 min-w-[200px] px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500">
        <select name="type" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
            <option value="">Tüm Türler</option>
            <option value="image"    {{ request('type') === 'image'    ? 'selected' : '' }}>Resimler</option>
            <option value="video"    {{ request('type') === 'video'    ? 'selected' : '' }}>Videolar</option>
            <option value="document" {{ request('type') === 'document' ? 'selected' : '' }}>Belgeler</option>
        </select>
        <select name="collection" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
            <option value="">Tüm Koleksiyonlar</option>
            @foreach($collections as $col)
                <option value="{{ $col }}" {{ request('collection') === $col ? 'selected' : '' }}>{{ $col }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-lg text-sm hover:bg-gray-700">
            <i class="fas fa-filter mr-1"></i> Filtrele
        </button>
        @if(request('search') || request('type') || request('collection'))
            <a href="{{ route('admin.media.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50">
                <i class="fas fa-times mr-1"></i> Temizle
            </a>
        @endif
    </form>

    {{-- AI alt-text toplu üretim --}}
    <form method="POST" action="{{ route('admin.media.generate-alt-bulk') }}" class="mb-5"
          onsubmit="return confirm('Alt yazısı eksik tüm görseller için AI üretimi başlatılsın mı?\n\n(Görsel başına küçük bir AI maliyeti oluşur; üretim arka planda yapılır.)')">
        @csrf
        <button type="submit"
                class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 text-indigo-700 border border-indigo-200 rounded-lg text-sm font-medium hover:bg-indigo-100">
            <i class="fas fa-wand-magic-sparkles"></i>
            Eksik Alt Yazılarını AI ile Üret
        </button>
    </form>

    {{-- Bulk action bar (appears when items selected) --}}
    <div x-show="selected.length > 0" x-transition
         class="flex items-center gap-4 bg-amber-50 border border-amber-200 rounded-xl px-5 py-3 mb-5">
        <span class="text-sm font-medium text-amber-800">
            <span x-text="selected.length"></span> öge seçildi
        </span>
        <button @click="selectAll()" class="text-sm text-amber-700 underline">Tümünü seç</button>
        <button @click="selected = []" class="text-sm text-amber-700 underline">Seçimi kaldır</button>
        <div class="ml-auto">
            <form method="POST" action="{{ route('admin.media.bulk-destroy') }}"
                  @submit.prevent="if(confirm(selected.length + ' dosyayı silmek istediğinizden emin misiniz?')) { $el.submit(); }">
                @csrf
                <template x-for="id in selected" :key="id">
                    <input type="hidden" name="ids[]" :value="id">
                </template>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700 transition-colors">
                    <i class="fas fa-trash mr-1"></i> Seçilenleri Sil
                </button>
            </form>
        </div>
    </div>

    {{-- "Select all on page" checkbox --}}
    @if($media->count() > 0)
    <div class="flex items-center gap-2 mb-3 text-sm text-gray-500">
        <input type="checkbox" id="select-all-chk"
               @change="$event.target.checked ? selectAll() : (selected = [])"
               :checked="selected.length === {{ $media->count() }}"
               class="w-4 h-4 rounded border-gray-300 text-indigo-600 cursor-pointer">
        <label for="select-all-chk" class="cursor-pointer">Bu sayfadaki tümünü seç</label>
    </div>
    @endif

    {{-- Media Grid --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
        @forelse($media as $item)
            <div class="group relative bg-white rounded-xl shadow-sm border overflow-hidden hover:shadow-md transition-shadow"
                 :class="selected.includes({{ $item->id }}) ? 'ring-2 ring-indigo-500' : ''">

                {{-- Selection checkbox --}}
                <div class="absolute top-2 left-2 z-10"
                     @click.stop>
                    <input type="checkbox"
                           value="{{ $item->id }}"
                           :checked="selected.includes({{ $item->id }})"
                           @change="toggleSelect({{ $item->id }})"
                           class="w-4 h-4 rounded border-gray-300 text-indigo-600 cursor-pointer shadow">
                </div>

                {{-- Thumbnail --}}
                <a href="{{ route('admin.media.show', $item) }}" class="block">
                    @if(str_starts_with($item->mime_type, 'image/'))
                        <div class="aspect-square overflow-hidden bg-gray-100">
                            <img src="{{ $item->getUrl() }}" alt="{{ $item->name }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy">
                        </div>
                    @else
                        <div class="aspect-square flex items-center justify-center bg-gray-50">
                            <div class="text-center">
                                @php
                                    $ext = strtolower(pathinfo($item->file_name, PATHINFO_EXTENSION));
                                    $icon = match(true) {
                                        in_array($ext, ['pdf']) => 'fa-file-pdf text-red-400',
                                        in_array($ext, ['doc','docx']) => 'fa-file-word text-blue-400',
                                        in_array($ext, ['xls','xlsx']) => 'fa-file-excel text-green-400',
                                        in_array($ext, ['zip','rar']) => 'fa-file-archive text-yellow-400',
                                        str_starts_with($item->mime_type, 'video/') => 'fa-file-video text-purple-400',
                                        default => 'fa-file text-gray-400',
                                    };
                                @endphp
                                <i class="fas {{ $icon }} text-3xl mb-1"></i>
                                <div class="text-xs text-gray-500 uppercase font-medium">{{ $ext }}</div>
                            </div>
                        </div>
                    @endif
                </a>

                {{-- Info + actions --}}
                <div class="p-2">
                    <p class="text-xs text-gray-700 truncate font-medium" title="{{ $item->file_name }}">{{ $item->name ?: $item->file_name }}</p>
                    <p class="text-xs text-gray-400">{{ number_format($item->size / 1024, 0) }} KB</p>

                    {{-- Action buttons --}}
                    <div class="flex gap-1 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        {{-- Copy URL --}}
                        <button type="button"
                                @click.stop="copyUrl('{{ $item->getUrl() }}')"
                                title="URL Kopyala"
                                class="flex-1 px-2 py-1 text-xs bg-gray-100 hover:bg-indigo-100 hover:text-indigo-700 rounded transition-colors">
                            <i class="fas fa-copy"></i>
                        </button>
                        {{-- Detail --}}
                        <a href="{{ route('admin.media.show', $item) }}"
                           title="Düzenle"
                           class="flex-1 px-2 py-1 text-xs bg-gray-100 hover:bg-blue-100 hover:text-blue-700 rounded text-center transition-colors">
                            <i class="fas fa-pencil"></i>
                        </a>
                        {{-- Delete --}}
                        <form method="POST" action="{{ route('admin.media.destroy', $item) }}" class="flex-1"
                              @submit.prevent="if(confirm('Bu dosyayı silmek istediğinizden emin misiniz?')) $el.submit()">
                            @csrf @method('DELETE')
                            <button type="submit" title="Sil"
                                    class="w-full px-2 py-1 text-xs bg-gray-100 hover:bg-red-100 hover:text-red-700 rounded transition-colors">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-16 text-gray-400">
                <i class="fas fa-photo-film text-5xl mb-4 block"></i>
                <p class="font-medium">Henüz medya dosyası yok.</p>
                <p class="text-sm mt-1">Dosyaları sürükleyerek veya "Dosya Yükle" ile ekleyin.</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-6">{{ $media->links() }}</div>

    {{-- Copied notification --}}
    <div x-show="copiedMsg" x-transition
         class="fixed bottom-6 right-6 bg-gray-900 text-white text-sm px-4 py-2 rounded-lg shadow-lg z-50">
        <i class="fas fa-check mr-2 text-green-400"></i> URL kopyalandı!
    </div>

</div>

@push('scripts')
<script>
function mediaLibrary() {
    return {
        selected: [],
        dragging: false,
        uploading: false,
        uploadStatus: '',
        copiedMsg: false,
        allIds: @json($media->pluck('id')),

        init() {},

        toggleSelect(id) {
            const idx = this.selected.indexOf(id);
            idx === -1 ? this.selected.push(id) : this.selected.splice(idx, 1);
        },

        selectAll() {
            this.selected = [...this.allIds];
        },

        async copyUrl(url) {
            try {
                await navigator.clipboard.writeText(url);
                this.copiedMsg = true;
                setTimeout(() => this.copiedMsg = false, 2000);
            } catch {
                prompt('URL kopyalamak için seçip kopyalayın:', url);
            }
        },

        async uploadFiles(files) {
            if (!files || files.length === 0) return;
            this.uploading = true;
            let done = 0;

            for (const file of files) {
                this.uploadStatus = `Yükleniyor: ${file.name} (${done + 1}/${files.length})`;
                const formData = new FormData();
                formData.append('file', file);
                formData.append('_token', '{{ csrf_token() }}');

                try {
                    const res  = await fetch('{{ route("admin.media.upload") }}', { method: 'POST', body: formData });
                    const data = await res.json();
                    if (!data.success && data.error) {
                        alert('Hata: ' + data.error);
                    }
                } catch (e) {
                    console.error('Upload failed:', e);
                }
                done++;
            }

            this.uploading = false;
            this.uploadStatus = '';
            window.location.reload();
        },
    };
}
</script>
@endpush

@endsection
