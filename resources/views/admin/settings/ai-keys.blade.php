@extends('admin.layouts.app')
@section('title', 'AI Anahtarları')
@section('page-title', 'AI Anahtarları')

@section('content')
<div class="max-w-2xl">

    {{-- Başlık --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">AI API Anahtarları</h1>
        <p class="text-sm text-gray-500 mt-1">
            Tüm tenant'lar için geçerli sistem geneli AI anahtarları.
            Tenant kendi BYOK anahtarını tanımlamışsa o öncelikli kullanılır.
        </p>
    </div>

    {{-- Bilgi kutusu --}}
    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-xl text-sm text-blue-800 flex items-start gap-3">
        <i class="fas fa-info-circle mt-0.5 flex-shrink-0"></i>
        <div>
            <p class="font-medium">Öncelik sırası:</p>
            <ol class="mt-1 list-decimal pl-4 space-y-0.5 text-blue-700">
                <li>Tenant BYOK anahtarı (Admin → Site Detayı → AI Ayarları)</li>
                <li>Bu sayfada tanımlanan sistem anahtarı ← <strong>burada yönetiyorsunuz</strong></li>
                <li><code class="bg-blue-100 px-1 rounded">.env</code> değerleri (fallback)</li>
            </ol>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.settings.ai-keys.update') }}" class="space-y-6"
          x-data="{ confirmClear: null }">
        @csrf

        {{-- Default Provider --}}
        <div class="bg-white rounded-xl shadow-sm border p-5">
            <h2 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                <i class="fas fa-sliders text-indigo-500"></i> Varsayılan AI Provider
            </h2>
            <select name="default_provider"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                <option value="">— .env değerini kullan ({{ config('ai.default_provider', 'anthropic') }}) —</option>
                @foreach($providers as $key => $p)
                <option value="{{ $key }}" {{ $defaultProvider === $key ? 'selected' : '' }}>
                    {{ $p['label'] }}
                </option>
                @endforeach
            </select>
            <p class="text-xs text-gray-400 mt-1.5">Tenant BYOK yoksa bu provider kullanılır.</p>
        </div>

        {{-- API Keys --}}
        <div class="bg-white rounded-xl shadow-sm border p-5">
            <h2 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                <i class="fas fa-key text-indigo-500"></i> API Anahtarları
            </h2>

            <div class="space-y-5">
                @foreach($providers as $providerKey => $p)
                <div class="border border-gray-100 rounded-lg p-4 bg-gray-50/50">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <i class="fas {{ $p['icon'] }} {{ $p['color'] }}"></i>
                            <span class="font-medium text-sm text-gray-800">{{ $p['label'] }}</span>
                        </div>
                        @if($hasKey[$providerKey])
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-xs font-medium">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Kayıtlı
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-gray-100 text-gray-500 rounded-full text-xs">
                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Tanımsız
                        </span>
                        @endif
                    </div>

                    <div class="flex gap-2">
                        <input type="password"
                               name="{{ $providerKey }}_api_key"
                               placeholder="{{ $hasKey[$providerKey] ? '••••••••••••••• (değiştirmek için yazın)' : 'sk-…' }}"
                               autocomplete="new-password"
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 bg-white">

                        @if($hasKey[$providerKey])
                        <div x-data="{ clearing: false }">
                            <input type="hidden" name="clear_{{ $providerKey }}" :value="clearing ? '1' : '0'">
                            <button type="button"
                                    @click="if(confirm('{{ $p['label'] }} anahtarı silinsin mi?')) { clearing = true; $el.closest('form').submit(); }"
                                    class="px-3 py-2 bg-red-50 text-red-600 border border-red-200 rounded-lg text-xs hover:bg-red-100 whitespace-nowrap">
                                <i class="fas fa-trash mr-1"></i> Sil
                            </button>
                        </div>
                        @endif
                    </div>

                    @if($providerKey === 'anthropic')
                    <p class="text-xs text-gray-400 mt-1.5">
                        <a href="https://console.anthropic.com/settings/keys" target="_blank" class="text-indigo-500 hover:underline">
                            <i class="fas fa-external-link-alt mr-0.5"></i> console.anthropic.com
                        </a>
                        · sk-ant-api… formatında
                    </p>
                    @elseif($providerKey === 'openrouter')
                    <p class="text-xs text-gray-400 mt-1.5">
                        <a href="https://openrouter.ai/keys" target="_blank" class="text-indigo-500 hover:underline">
                            <i class="fas fa-external-link-alt mr-0.5"></i> openrouter.ai/keys
                        </a>
                        · sk-or-v1-… formatında · Birçok modele tek key
                    </p>
                    @elseif($providerKey === 'openai')
                    <p class="text-xs text-gray-400 mt-1.5">
                        <a href="https://platform.openai.com/api-keys" target="_blank" class="text-indigo-500 hover:underline">
                            <i class="fas fa-external-link-alt mr-0.5"></i> platform.openai.com
                        </a>
                        · sk-proj-… formatında
                    </p>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        {{-- Kaydet --}}
        <div class="flex items-center justify-between">
            <p class="text-xs text-gray-400">
                <i class="fas fa-lock mr-1"></i> Anahtarlar şifreli olarak veritabanında saklanır.
            </p>
            <button type="submit"
                    class="px-5 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">
                <i class="fas fa-save mr-1.5"></i> Kaydet
            </button>
        </div>
    </form>
</div>
@endsection
