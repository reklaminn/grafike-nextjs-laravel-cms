@extends('admin.layouts.app')
@section('title', 'Medya Detay')

@section('content')
<div class="max-w-4xl">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Medya Detay</h1>
        <a href="{{ route('admin.media.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Geri</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Preview --}}
        <div class="bg-white rounded-xl shadow-sm border p-4">
            @if(str_starts_with($media->mime_type, 'image/'))
                <img src="{{ $media->getUrl() }}" alt="{{ $media->name }}" class="w-full rounded-lg">
            @else
                <div class="aspect-video flex items-center justify-center bg-gray-50 rounded-lg">
                    <div class="text-center">
                        <i class="fas fa-file text-5xl text-gray-400 mb-3"></i>
                        <div class="text-sm text-gray-500">{{ $media->mime_type }}</div>
                    </div>
                </div>
            @endif

            {{-- Info --}}
            <div class="mt-4 space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">Dosya Adı:</span><span class="text-gray-800">{{ $media->file_name }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Boyut:</span><span>{{ number_format($media->size / 1024, 1) }} KB</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Tür:</span><span>{{ $media->mime_type }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Koleksiyon:</span><span>{{ $media->collection_name }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Yüklenme:</span><span>{{ $media->created_at->format('d.m.Y H:i') }}</span></div>
                @if($media->model)
                    <div class="flex justify-between"><span class="text-gray-500">Bağlı Model:</span><span>{{ class_basename($media->model_type) }} #{{ $media->model_id }}</span></div>
                @endif
            </div>

            {{-- URL --}}
            <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                <label class="text-xs text-gray-500 block mb-1">Dosya URL</label>
                <input type="text" value="{{ $media->getUrl() }}" readonly
                       class="w-full text-xs bg-transparent border-none p-0 text-gray-700 focus:ring-0"
                       onclick="this.select()">
            </div>
        </div>

        {{-- Edit Form --}}
        <div>
            <form method="POST" action="{{ route('admin.media.update', $media, false) }}" class="bg-white rounded-xl shadow-sm border p-6 space-y-4">
                @csrf @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dosya Adı</label>
                    <input type="text" name="name" value="{{ old('name', $media->name) }}"
                           class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-indigo-500">
                </div>

                <div x-data="{ generating: false, error: '' }">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alt Text (SEO)</label>
                    <div class="flex gap-2">
                        <input type="text" name="custom_properties[alt_text]" id="alt_text_input"
                               value="{{ old('custom_properties.alt_text', $media->getCustomProperty('alt_text', '')) }}"
                               class="flex-1 px-3 py-2 border rounded-lg text-sm focus:ring-indigo-500" placeholder="Resim açıklaması">
                        @if(str_starts_with((string) $media->mime_type, 'image/') && $media->mime_type !== 'image/svg+xml')
                        <button type="button"
                                :disabled="generating"
                                @click="
                                    generating = true; error = '';
                                    fetch(@js(route('admin.media.generate-alt', $media, false)), {
                                        method: 'POST',
                                        credentials: 'same-origin',
                                        headers: {
                                            'Accept': 'application/json',
                                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '',
                                        },
                                    })
                                    .then(r => r.json())
                                    .then(d => {
                                        if (d.ok) document.getElementById('alt_text_input').value = d.alt;
                                        else error = d.message || 'Üretilemedi.';
                                    })
                                    .catch(() => error = 'Ağ hatası.')
                                    .finally(() => generating = false)"
                                title="Görseli AI'a gösterip alt yazısı üretir — kaydetmeden önce düzenleyebilirsiniz"
                                class="shrink-0 inline-flex items-center gap-1.5 px-3 py-2 bg-indigo-50 text-indigo-700 rounded-lg text-xs font-medium hover:bg-indigo-100 disabled:opacity-50">
                            <i class="fas" :class="generating ? 'fa-spinner fa-spin' : 'fa-wand-magic-sparkles'"></i>
                            <span x-text="generating ? 'Üretiliyor…' : 'AI ile Üret'"></span>
                        </button>
                        @endif
                    </div>
                    <p x-show="error" x-cloak class="mt-1 text-xs text-red-600" x-text="error"></p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Başlık</label>
                    <input type="text" name="custom_properties[title]" value="{{ old('custom_properties.title', $media->getCustomProperty('title', '')) }}"
                           class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-indigo-500" placeholder="Medya başlığı">
                </div>

                <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700">Güncelle</button>
            </form>

            {{-- Delete --}}
            <form method="POST" action="{{ route('admin.media.destroy', $media) }}" class="mt-4" onsubmit="return confirm('Bu medya dosyasını silmek istediğinize emin misiniz?')">
                @csrf @method('DELETE')
                <button type="submit" class="w-full px-4 py-2 bg-red-50 text-red-600 border border-red-200 rounded-lg text-sm hover:bg-red-100">
                    <i class="fas fa-trash mr-1"></i> Dosyayı Sil
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
