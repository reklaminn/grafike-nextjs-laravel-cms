{{--
    Tenant yedekleme paneli
    Değişkenler:
      $tenant     — Tenant model
      $backups    — array from TenantBackupController::backupList()
      $canManage  — bool (agency admin mi?)
--}}

<div class="bg-white rounded-xl shadow-sm border p-5">
    <div class="flex items-center justify-between mb-4">
        <h2 class="font-semibold text-gray-700 flex items-center gap-2">
            <i class="fas fa-database text-blue-500"></i> Yedekler
        </h2>

        @if($canManage)
        <form method="POST"
              action="{{ route('admin.tenants.backups.store', $tenant) }}"
              onsubmit="this.querySelector('button').disabled=true; this.querySelector('button').innerHTML='<i class=\'fas fa-spinner fa-spin mr-1\'></i> Yedekleniyor…'">
            @csrf
            <button type="submit"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium
                           bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                <i class="fas fa-plus-circle"></i> Şimdi Yedekle
            </button>
        </form>
        @endif
    </div>

    @if(count($backups) === 0)
    <p class="text-sm text-gray-400 py-2">
        Henüz yedek yok.
        @if($canManage)
            Yukarıdaki butonu kullanarak ilk yedeği oluşturabilirsiniz.
        @endif
    </p>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs text-gray-500 border-b">
                    <th class="pb-2 font-medium">Tarih</th>
                    <th class="pb-2 font-medium">Boyut</th>
                    <th class="pb-2 font-medium">Dosya</th>
                    <th class="pb-2 font-medium text-right">İşlemler</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($backups as $backup)
                <tr class="hover:bg-gray-50/50">
                    <td class="py-2.5 pr-4 text-gray-700 whitespace-nowrap">
                        {{ $backup['created_at'] }}
                    </td>
                    <td class="py-2.5 pr-4 text-gray-500 whitespace-nowrap tabular-nums">
                        {{ $backup['size'] }}
                    </td>
                    <td class="py-2.5 pr-4 font-mono text-xs text-gray-400 truncate max-w-[220px]"
                        title="{{ $backup['filename'] }}">
                        {{ $backup['filename'] }}
                    </td>
                    <td class="py-2.5 text-right whitespace-nowrap">
                        {{-- Download --}}
                        <a href="{{ $backup['download_url'] }}"
                           class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium
                                  text-blue-600 hover:text-blue-800 border border-blue-200
                                  hover:border-blue-400 rounded-md transition-colors mr-1">
                            <i class="fas fa-download text-[10px]"></i> İndir
                        </a>

                        @if($canManage)
                        {{-- Delete --}}
                        <form method="POST"
                              action="{{ $backup['delete_url'] }}"
                              class="inline-block"
                              onsubmit="return confirm('{{ $backup['filename'] }} silinsin mi?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium
                                           text-red-500 hover:text-red-700 border border-red-200
                                           hover:border-red-400 rounded-md transition-colors">
                                <i class="fas fa-trash text-[10px]"></i> Sil
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <p class="text-xs text-gray-400 mt-3">
        <i class="fas fa-info-circle mr-0.5"></i>
        Son 30 yedek saklanır, eskiler otomatik silinir.
    </p>
    @endif
</div>
