@extends('admin.layouts.app')

@section('title', $package ? 'Paketi Düzenle: '.$package->label : 'Yeni Paket')
@section('page-title', $package ? 'Paketi Düzenle' : 'Yeni Paket')

@section('content')
<div class="max-w-2xl mx-auto">
    <form method="POST"
          action="{{ $package ? route('admin.packages.update', $package) : route('admin.packages.store') }}"
          class="bg-white rounded-xl shadow-sm border p-6 space-y-5">
        @csrf
        @if($package) @method('PUT') @endif

        {{-- Key --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Paket Anahtarı
                <span class="ml-1 text-xs text-gray-400">(değiştirilemez, mevcut tenant'larda kullanılan anahtar)</span>
            </label>
            <input type="text" name="key"
                   value="{{ old('key', $package?->key) }}"
                   {{ $package ? 'readonly' : '' }}
                   placeholder="basic, pro, enterprise …"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm {{ $package ? 'bg-gray-50 text-gray-500 cursor-not-allowed' : 'focus:ring-2 focus:ring-indigo-500' }}">
            @error('key') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Label --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Görünen Ad</label>
            <input type="text" name="label"
                   value="{{ old('label', $package?->label) }}"
                   placeholder="Temel, Standart, Profesyonel …"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
            @error('label') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Limits grid --}}
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Maks. Kullanıcı
                    <span class="text-xs text-gray-400">(boş=∞)</span>
                </label>
                <input type="number" name="max_users" min="1"
                       value="{{ old('max_users', $package?->max_users) }}"
                       placeholder="∞"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                @error('max_users') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Depolama (MB)
                    <span class="text-xs text-gray-400">(boş=∞)</span>
                </label>
                <input type="number" name="max_storage_mb" min="1"
                       value="{{ old('max_storage_mb', $package?->max_storage_mb) }}"
                       placeholder="∞"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                @error('max_storage_mb') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    İstek/gün
                    <span class="text-xs text-gray-400">(boş=kapalı)</span>
                </label>
                <input type="number" name="max_requests_per_day" min="1"
                       value="{{ old('max_requests_per_day', $package?->max_requests_per_day) }}"
                       placeholder="kapalı"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                @error('max_requests_per_day') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- AI Plan --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">AI Planı</label>
            <select name="ai_plan"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                @foreach($aiPlans as $plan)
                <option value="{{ $plan }}" {{ old('ai_plan', $package?->ai_plan) === $plan ? 'selected' : '' }}>
                    {{ $plan }}
                </option>
                @endforeach
            </select>
            @error('ai_plan') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Modules --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Modüller
                <span class="text-xs text-gray-400">(virgülle ayrılmış, örn: tours,commerce)</span>
            </label>
            <input type="text" name="modules_raw"
                   value="{{ old('modules_raw', implode(',', $package?->modules ?? [])) }}"
                   placeholder="tours,commerce"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500"
                   x-data
                   @input="
                       $el.closest('form').querySelectorAll('[name^=\'modules\[\']').forEach(e=>e.remove());
                       $el.value.split(',').map(s=>s.trim()).filter(Boolean).forEach((m,i)=>{
                           const inp = document.createElement('input');
                           inp.type='hidden'; inp.name='modules[]'; inp.value=m;
                           $el.closest('form').appendChild(inp);
                       });
                   ">
            @error('modules') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Sort + Default --}}
        <div class="flex items-center gap-6">
            <div class="w-32">
                <label class="block text-sm font-medium text-gray-700 mb-1">Sıralama</label>
                <input type="number" name="sort_order" min="0"
                       value="{{ old('sort_order', $package?->sort_order ?? 0) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="flex items-center gap-2 pt-5">
                <input type="checkbox" name="is_default" id="is_default" value="1"
                       {{ old('is_default', $package?->is_default) ? 'checked' : '' }}
                       class="w-4 h-4 text-indigo-600 rounded">
                <label for="is_default" class="text-sm text-gray-700">
                    Varsayılan paket
                    <span class="text-xs text-gray-400">(yeni tenant'lara atanır)</span>
                </label>
            </div>
        </div>

        {{-- modules hidden fields (initial render) --}}
        @foreach(old('modules', $package?->modules ?? []) as $mod)
        <input type="hidden" name="modules[]" value="{{ $mod }}">
        @endforeach

        <div class="flex items-center justify-between pt-2 border-t">
            <a href="{{ route('admin.packages.index') }}"
               class="text-sm text-gray-500 hover:text-gray-700">
                <i class="fas fa-arrow-left mr-1"></i> İptal
            </a>
            <button type="submit"
                    class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">
                <i class="fas fa-save mr-1"></i>
                {{ $package ? 'Güncelle' : 'Oluştur' }}
            </button>
        </div>
    </form>
</div>
@endsection
