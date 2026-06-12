@extends('admin.layouts.app')

@section('title', $plan ? 'AI Planı Düzenle: '.$plan->label : 'Yeni AI Planı')
@section('page-title', $plan ? 'AI Planı Düzenle' : 'Yeni AI Planı')

@section('content')
<div class="max-w-xl mx-auto">
    <form method="POST"
          action="{{ $plan ? route('admin.ai-plans.update', $plan) : route('admin.ai-plans.store') }}"
          class="bg-white rounded-xl shadow-sm border p-6 space-y-5">
        @csrf
        @if($plan) @method('PUT') @endif

        {{-- Key --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Plan Anahtarı
                <span class="ml-1 text-xs text-gray-400">(değiştirilemez)</span>
            </label>
            <input type="text" name="key"
                   value="{{ old('key', $plan?->key) }}"
                   {{ $plan ? 'readonly' : '' }}
                   placeholder="free, starter, pro …"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm {{ $plan ? 'bg-gray-50 text-gray-500 cursor-not-allowed' : 'focus:ring-2 focus:ring-indigo-500' }}">
            @error('key') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Label --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Görünen Ad</label>
            <input type="text" name="label"
                   value="{{ old('label', $plan?->label) }}"
                   placeholder="Ücretsiz, Başlangıç, Pro …"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
            @error('label') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Limits --}}
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    İstek/ay
                    <span class="text-xs text-gray-400">(boş=∞)</span>
                </label>
                <input type="number" name="monthly_requests" min="1"
                       value="{{ old('monthly_requests', $plan?->monthly_requests) }}"
                       placeholder="∞"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                @error('monthly_requests') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Token/ay
                    <span class="text-xs text-gray-400">(boş=∞)</span>
                </label>
                <input type="number" name="monthly_tokens" min="1"
                       value="{{ old('monthly_tokens', $plan?->monthly_tokens) }}"
                       placeholder="∞"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                @error('monthly_tokens') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Maliyet/ay ($)
                    <span class="text-xs text-gray-400">(boş=∞)</span>
                </label>
                <input type="number" name="monthly_cost_usd" min="0" step="0.01"
                       value="{{ old('monthly_cost_usd', $plan?->monthly_cost_usd) }}"
                       placeholder="∞"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                @error('monthly_cost_usd') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Sort + Default --}}
        <div class="flex items-center gap-6">
            <div class="w-32">
                <label class="block text-sm font-medium text-gray-700 mb-1">Sıralama</label>
                <input type="number" name="sort_order" min="0"
                       value="{{ old('sort_order', $plan?->sort_order ?? 0) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="flex items-center gap-2 pt-5">
                <input type="checkbox" name="is_default" id="is_default" value="1"
                       {{ old('is_default', $plan?->is_default) ? 'checked' : '' }}
                       class="w-4 h-4 text-indigo-600 rounded">
                <label for="is_default" class="text-sm text-gray-700">
                    Varsayılan plan
                    <span class="text-xs text-gray-400">(yeni tenant'lara atanır)</span>
                </label>
            </div>
        </div>

        <div class="flex items-center justify-between pt-2 border-t">
            <a href="{{ route('admin.ai-plans.index') }}"
               class="text-sm text-gray-500 hover:text-gray-700">
                <i class="fas fa-arrow-left mr-1"></i> İptal
            </a>
            <button type="submit"
                    class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">
                <i class="fas fa-save mr-1"></i>
                {{ $plan ? 'Güncelle' : 'Oluştur' }}
            </button>
        </div>
    </form>
</div>
@endsection
