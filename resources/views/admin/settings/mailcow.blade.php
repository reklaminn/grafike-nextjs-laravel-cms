@extends('admin.layouts.app')
@section('title', 'Mail (Mailcow) Ayarları')
@section('page-title', 'Mail (Mailcow) Ayarları')

@section('content')
<div class="max-w-2xl">

    @if(session('success'))
        <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg px-4 py-3 text-sm">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-envelope-open-text text-blue-600"></i>
            </div>
            <div>
                <h2 class="text-base font-semibold text-gray-800">Mailcow Bağlantısı</h2>
                <p class="text-xs text-gray-400">Mail sunucusu API erişim bilgileri</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.settings.mailcow.update') }}">
            @csrf
            <div class="space-y-5">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Mailcow URL
                    </label>
                    <input type="url" name="mailcow_url"
                           value="{{ old('mailcow_url', $mailcowUrl) }}"
                           placeholder="https://mail.example.com"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-400 mt-1">Mailcow'un admin paneline eriştiğiniz URL (trailing slash olmadan)</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        API Anahtarı
                        @if($hasApiKey)
                            <span class="ml-2 text-xs text-emerald-600 font-normal">
                                <i class="fas fa-check-circle"></i> Kayıtlı
                            </span>
                        @endif
                    </label>
                    <input type="password" name="mailcow_api_key"
                           placeholder="{{ $hasApiKey ? '••••••••••••••••' : 'API anahtarınızı girin' }}"
                           autocomplete="new-password"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-400 mt-1">Mailcow Admin → API → Oluştur'dan alınır. Şifreli saklanır.</p>

                    @if($hasApiKey)
                    <div class="mt-2 flex items-center gap-2">
                        <input type="checkbox" name="clear_api_key" value="1" id="clear_api_key"
                               class="h-4 w-4 rounded border-gray-300 text-red-600">
                        <label for="clear_api_key" class="text-xs text-red-600 cursor-pointer">API anahtarını sil</label>
                    </div>
                    @endif
                </div>

            </div>

            <div class="mt-6 flex items-center gap-3">
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-save mr-1"></i> Kaydet
                </button>
                <button type="button" id="testBtn"
                        class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                    <i class="fas fa-plug mr-1"></i> Bağlantıyı Test Et
                </button>
                <span id="testResult" class="text-sm hidden"></span>
            </div>
        </form>
    </div>

    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-xl p-4 text-sm text-blue-800">
        <p class="font-medium mb-2"><i class="fas fa-info-circle mr-1"></i> Kurulum Adımları</p>
        <ol class="list-decimal list-inside space-y-1 text-xs text-blue-700">
            <li>Mailcow Admin Paneli'ne giriş yapın</li>
            <li><strong>Yapılandırma → API</strong> bölümüne gidin</li>
            <li>"API Anahtarı Oluştur" → Read+Write izni verin</li>
            <li>CMS'in IP adresini "İzin Verilen IP'ler" listesine ekleyin</li>
            <li>Anahtarı yukarıya yapıştırıp kaydedin</li>
        </ol>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('testBtn').addEventListener('click', async function () {
    const btn = this;
    const result = document.getElementById('testResult');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Test ediliyor…';
    result.className = 'text-sm hidden';

    try {
        const r = await fetch(@js(route('admin.settings.mailcow.test', [], false)), {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
        });
        const data = await r.json();
        result.textContent = data.message;
        result.className = 'text-sm ' + (data.ok ? 'text-emerald-600' : 'text-red-600');
        result.classList.remove('hidden');
    } catch (e) {
        result.textContent = 'Ağ hatası: ' + e.message;
        result.className = 'text-sm text-red-600';
        result.classList.remove('hidden');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-plug mr-1"></i> Bağlantıyı Test Et';
    }
});
</script>
@endpush
