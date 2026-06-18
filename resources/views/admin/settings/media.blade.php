@extends('admin.layouts.app')
@section('title', 'Medya & Sıkıştırma')

@section('content')
@php
    $compressEnabled = ($settings['media.compress_enabled'] ?? '1') === '1';
    $toWebp          = ($settings['media.to_webp'] ?? '0') === '1';
    $maxSizeKb       = (int) ($settings['media.max_size_kb'] ?? config('cms.media.max_upload_size', 10240));
    $maxWidth        = (int) ($settings['media.max_width'] ?? 2560);
    $maxHeight       = (int) ($settings['media.max_height'] ?? 0);
    $quality         = (int) ($settings['media.quality'] ?? 82);
@endphp
<div class="max-w-3xl"
     x-data="{
        enabled: {{ $compressEnabled ? 'true' : 'false' }},
        toWebp: {{ $toWebp ? 'true' : 'false' }},
        quality: {{ $quality }}
     }">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Medya & Sıkıştırma</h1>
            <p class="text-sm text-gray-500 mt-1">
                Görsel yüklenirken uygulanacak boyut sınırı, ölçü ve sıkıştırma varsayılanları.
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.settings.index') }}"
               class="text-xs px-3 py-1.5 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 flex items-center gap-1">
                <i class="fas fa-cog text-[10px]"></i> Genel Ayarlar
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg p-4 mb-6 text-sm">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-4 mb-6 text-sm">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.settings.media.update', [], false) }}" class="space-y-6">
        @csrf @method('PUT')

        {{-- ─── Yükleme sınırı ─────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-base font-semibold text-gray-800 mb-4">
                <i class="fas fa-upload text-gray-400 mr-1.5"></i> Yükleme Sınırı
            </h2>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Maksimum dosya boyutu (KB)</label>
                <input type="number" name="media[max_size_kb]" value="{{ old('media.max_size_kb', $maxSizeKb) }}"
                       min="128" max="51200" step="1" required
                       class="w-48 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                <p class="mt-1 text-xs text-gray-500">
                    {{ number_format($maxSizeKb / 1024, 1) }} MB civarı. Bundan büyük dosya reddedilir.
                    Sunucunun PHP <code>upload_max_filesize</code>/<code>post_max_size</code> sınırından yüksek olamaz.
                </p>
            </div>
        </div>

        {{-- ─── Sıkıştırma & ölçü ──────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-base font-semibold text-gray-800 mb-4">
                <i class="fas fa-compress text-gray-400 mr-1.5"></i> Sıkıştırma & Yeniden Boyutlandırma
            </h2>

            <label class="flex items-center gap-2 mb-4 cursor-pointer">
                <input type="checkbox" name="media[compress_enabled]" value="1" x-model="enabled"
                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                <span class="text-sm font-medium text-gray-700">Yüklenen görselleri otomatik sıkıştır</span>
            </label>

            <div class="space-y-5" :class="!enabled && 'opacity-50 pointer-events-none'">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Maksimum genişlik (px)</label>
                        <input type="number" name="media[max_width]" value="{{ old('media.max_width', $maxWidth) }}"
                               min="0" max="10000" step="1" required x-bind:disabled="!enabled"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                        <p class="mt-1 text-xs text-gray-500">0 = sınırsız. Oran korunur, yalnızca büyükse küçültülür.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Maksimum yükseklik (px)</label>
                        <input type="number" name="media[max_height]" value="{{ old('media.max_height', $maxHeight) }}"
                               min="0" max="10000" step="1" required x-bind:disabled="!enabled"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                        <p class="mt-1 text-xs text-gray-500">0 = sınırsız.</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        JPEG / WebP kalitesi: <span class="font-semibold text-indigo-600" x-text="quality"></span>
                    </label>
                    <input type="range" name="media[quality]" min="10" max="100" step="1"
                           x-model.number="quality" x-bind:disabled="!enabled"
                           class="w-full accent-indigo-600">
                    <p class="mt-1 text-xs text-gray-500">Düşük = daha küçük dosya, daha düşük kalite. Önerilen: 75–85. (PNG kayıpsızdır, kalite uygulanmaz.)</p>
                </div>

                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="media[to_webp]" value="1" x-model="toWebp" x-bind:disabled="!enabled"
                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm font-medium text-gray-700">JPEG/PNG'leri WebP'ye çevir</span>
                    <span class="text-xs text-gray-400">(genelde daha küçük; dosya uzantısı .webp olur)</span>
                </label>
            </div>

            <div class="mt-5 rounded-lg border border-blue-200 bg-blue-50 px-3 py-2.5 text-xs text-blue-800">
                <i class="fas fa-circle-info mr-1"></i>
                Ayarlar yalnızca <strong>bundan sonra yüklenecek</strong> görsellere uygulanır; mevcut medya değişmez.
                SVG, animasyonlu GIF ve görsel olmayan dosyalar (PDF, doc, zip) sıkıştırmadan geçmez.
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-indigo-700">
                <i class="fas fa-save"></i> Kaydet
            </button>
        </div>
    </form>
</div>
@endsection
