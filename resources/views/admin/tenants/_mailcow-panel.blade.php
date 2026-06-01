{{-- ── Mailcow Domain Paneli ──────────────────────────────────────────────── --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
    <h2 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
        <i class="fas fa-envelope-open-text text-blue-500"></i> Mail (Mailcow) Domain
    </h2>

    @if(session('mailcow_success'))
        <div class="mb-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg px-3 py-2 text-xs">
            <i class="fas fa-check-circle mr-1"></i> {{ session('mailcow_success') }}
        </div>
    @endif
    @if(session('mailcow_error'))
        <div class="mb-3 bg-red-50 border border-red-200 text-red-800 rounded-lg px-3 py-2 text-xs">
            <i class="fas fa-exclamation-triangle mr-1"></i> {{ session('mailcow_error') }}
        </div>
    @endif

    @php $mailcowDomain = $tenant->mailcowDomain(); @endphp

    @if($mailcowDomain)
        <div class="flex items-center gap-2 mb-4">
            <span class="flex-1 font-mono text-sm text-gray-800 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2">
                {{ $mailcowDomain }}
            </span>
            <a href="{{ route('admin.mail.index') }}"
               class="px-3 py-2 text-xs bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 font-medium whitespace-nowrap">
                <i class="fas fa-inbox mr-1"></i> Mail Hesapları
            </a>
        </div>
    @else
        <p class="text-xs text-gray-400 mb-4">Bu tenant için Mailcow domain tanımlanmamış.</p>
    @endif

    @if($canManage)
    <form method="POST" action="{{ route('admin.tenants.mailcow-domain.update', $tenant) }}">
        @csrf @method('PUT')
        <div class="flex gap-2">
            <input type="text" name="mailcow_domain"
                   value="{{ $mailcowDomain ?? '' }}"
                   placeholder="firma.com"
                   class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
            <button type="submit"
                    class="px-3 py-2 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 whitespace-nowrap">
                <i class="fas fa-save mr-1"></i> Kaydet
            </button>
        </div>

        {{-- Mailcow'da otomatik oluştur seçeneği --}}
        <label class="mt-2.5 flex items-center gap-2 cursor-pointer group">
            <input type="checkbox" name="create_in_mailcow" value="1"
                   class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
            <span class="text-xs text-gray-600 group-hover:text-gray-800">
                Mailcow'da yoksa otomatik oluştur
                <span class="text-gray-400">(10 mailbox, 5 alias, 10GB kota)</span>
            </span>
        </label>
        <p class="text-xs text-gray-400 mt-1">Mailcow'da kayıtlı domain adını girin (ör. firma.com)</p>
    </form>
    @endif
</div>
