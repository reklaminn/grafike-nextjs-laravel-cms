{{--
    Iyzico BYOK (Bring Your Own Keys) panel.

    Same UX shape as _ai-settings-panel.blade.php:
      • Plain inputs for API key + secret key
      • Empty submitted value = "leave existing untouched"
      • `clear_*` checkbox = explicitly drop a stored key
      • Sandbox toggle
      • Status badge (configured / unconfigured)

    Variables (passed via include):
      $tenant       Tenant
      $canManage    bool — agency admin gate
      $iyzicoStatus ['configured' => bool, 'sandbox' => bool]
--}}
<div class="bg-white rounded-xl shadow-sm border p-5">
    <div class="flex items-center justify-between mb-4">
        <h2 class="font-semibold text-gray-700 flex items-center gap-2">
            <i class="fas fa-credit-card text-pink-500"></i> Iyzico Ayarları (BYOK)
        </h2>
        <span class="text-xs px-2 py-1 rounded-full
            {{ $iyzicoStatus['configured']
                ? ($iyzicoStatus['sandbox'] ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700')
                : 'bg-gray-100 text-gray-600' }}">
            @if(!$iyzicoStatus['configured'])
                Anahtar girilmedi
            @elseif($iyzicoStatus['sandbox'])
                Sandbox (test)
            @else
                Production
            @endif
        </span>
    </div>

    <p class="text-xs text-gray-500 mb-4">
        Bu acente için Iyzico API anahtarlarını buradan girin.  Ödemeler <strong>doğrudan acentenin
        Iyzico hesabına</strong> akar; platform aracı değildir.  Anahtarlar veritabanına şifrelenerek
        (Laravel Crypt + APP_KEY) kaydedilir.
    </p>

    @if($canManage)
    <form method="POST" action="{{ route('admin.tenants.iyzico-settings.update', $tenant, false) }}" class="space-y-4">
        @csrf @method('PUT')

        {{-- Sandbox toggle --}}
        <label class="flex items-start gap-3 cursor-pointer">
            <input type="hidden" name="sandbox" value="0">
            <input type="checkbox" name="sandbox" value="1"
                   {{ $iyzicoStatus['sandbox'] ? 'checked' : '' }}
                   class="mt-1 h-4 w-4 text-amber-600 rounded">
            <span>
                <span class="text-sm font-medium text-gray-700">Sandbox / Test ortamı kullan</span>
                <span class="block text-xs text-gray-500">
                    Açıkken sandbox-api.iyzipay.com'a istek gider, gerçek para hareketi yok.
                    Production öncesi <strong>mutlaka</strong> sandbox'ta test edin.
                </span>
            </span>
        </label>

        {{-- API Key --}}
        <div>
            <div class="flex items-center justify-between mb-1">
                <label class="block text-sm font-medium text-gray-700">API Key</label>
                @if($iyzicoStatus['configured'])
                    <label class="text-xs flex items-center gap-1 text-red-600">
                        <input type="hidden" name="clear_api_key" value="0">
                        <input type="checkbox" name="clear_api_key" value="1" class="h-3 w-3 text-red-600 rounded">
                        Anahtarı sil
                    </label>
                @endif
            </div>
            <input type="text" name="api_key" autocomplete="off"
                   placeholder="{{ $iyzicoStatus['configured'] ? '••••••••  (mevcut anahtar saklı — değiştirmek için yeni girin)' : 'sandbox-XXXXXX' }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono">
        </div>

        {{-- Secret Key --}}
        <div>
            <div class="flex items-center justify-between mb-1">
                <label class="block text-sm font-medium text-gray-700">Secret Key</label>
                @if($iyzicoStatus['configured'])
                    <label class="text-xs flex items-center gap-1 text-red-600">
                        <input type="hidden" name="clear_secret_key" value="0">
                        <input type="checkbox" name="clear_secret_key" value="1" class="h-3 w-3 text-red-600 rounded">
                        Secret'i sil
                    </label>
                @endif
            </div>
            <input type="password" name="secret_key" autocomplete="off"
                   placeholder="{{ $iyzicoStatus['configured'] ? '••••••••  (mevcut secret saklı)' : 'sandbox-secret-XXXXXX' }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono">
        </div>

        <div class="flex items-center justify-between pt-2">
            <p class="text-xs text-gray-400">
                <i class="fas fa-shield-alt"></i> Anahtarlar plain-text olarak DB'ye gitmez.
            </p>
            <button type="submit"
                    class="px-4 py-2 bg-pink-600 text-white rounded-lg text-sm hover:bg-pink-700 font-medium">
                <i class="fas fa-save mr-1"></i> Iyzico Ayarlarını Kaydet
            </button>
        </div>
    </form>
    @else
        <p class="text-xs text-gray-400">Iyzico ayarlarını düzenlemek için ajans yöneticisi olmanız gerekir.</p>
    @endif
</div>
