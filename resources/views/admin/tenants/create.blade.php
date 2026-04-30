@extends('admin.layouts.app')
@section('title', 'Yeni Site Oluştur')

@section('content')
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.tenants.index') }}" class="text-gray-400 hover:text-gray-600">
        <i class="fas fa-arrow-left"></i>
    </a>
    <h1 class="text-2xl font-bold text-gray-800">Yeni Site</h1>
</div>

<div class="max-w-2xl">
    <form method="POST" action="{{ route('admin.tenants.store') }}" class="space-y-6">
        @csrf

        <div class="bg-white rounded-xl shadow-sm border p-6 space-y-5">
            <h2 class="font-semibold text-gray-700 text-sm uppercase tracking-wide border-b pb-2">Site Bilgileri</h2>

            {{-- Name --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Site Adı <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-400 @enderror"
                       placeholder="Nuh Çiçek Mobilya">
                @error('name')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Slug / Tenant ID --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Slug (Tenant ID) <span class="text-red-500">*</span>
                </label>
                <div class="flex">
                    <span class="inline-flex items-center px-3 rounded-l-lg border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">tenant_</span>
                    <input type="text" name="slug" id="slug" value="{{ old('slug') }}" required
                           class="flex-1 px-3 py-2 border border-gray-300 rounded-r-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-mono @error('slug') border-red-400 @enderror"
                           placeholder="nuhcicek"
                           pattern="[a-z0-9\-_]+"
                           title="Sadece küçük harf, rakam, tire ve alt çizgi">
                </div>
                <p class="text-xs text-gray-400 mt-1">Veritabanı adı: <code class="bg-gray-100 px-1 rounded" id="db-preview">tenant_nuhcicek</code></p>
                @error('slug')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Primary Domain --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Ana Domain <span class="text-red-500">*</span>
                </label>
                <input type="text" name="domain" value="{{ old('domain') }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-mono @error('domain') border-red-400 @enderror"
                       placeholder="nuhcicek.com.tr veya firma1.{{ env('APP_DOMAIN') }}">
                <p class="text-xs text-gray-400 mt-1">
                    <strong>Özel domain:</strong> <code class="font-mono">nuhcicek.com.tr</code> — www. varyantı otomatik eklenir.<br>
                    <strong>Subdomain:</strong> <code class="font-mono">firma1.{{ env('APP_DOMAIN') }}</code> — www. eklenmez, DNS wildcard (<code>*.{{ env('APP_DOMAIN') }}</code>) yeterli.
                </p>
                @error('domain')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Theme --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tema</label>
                <select name="theme_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Tema seç (sonra da atanabilir)</option>
                    @foreach($themes as $theme)
                    <option value="{{ $theme->id }}" {{ old('theme_id') == $theme->id ? 'selected' : '' }}>
                        {{ $theme->name }} ({{ $theme->engine }})
                    </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-800">
            <i class="fas fa-info-circle mr-1"></i>
            <strong>Sonraki adım:</strong> Site oluşturulduktan sonra <em>Provision</em> butonuna tıklayarak
            tenant veritabanını hazırlayın (migration'lar çalıştırılır).
        </div>

        <div class="flex gap-3">
            <button type="submit"
                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 font-medium">
                <i class="fas fa-save mr-1"></i> Siteyi Oluştur
            </button>
            <a href="{{ route('admin.tenants.index') }}"
               class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">
                İptal
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.getElementById('slug').addEventListener('input', function () {
    document.getElementById('db-preview').textContent = 'tenant_' + (this.value || '…');
});
</script>
@endpush
@endsection
