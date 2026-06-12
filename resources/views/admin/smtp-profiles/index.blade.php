@extends('admin.layouts.app')

@section('title', 'SMTP Profilleri')
@section('page-title', 'SMTP Profilleri')

@section('content')
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
            <p class="text-sm text-gray-500">Form gönderimlerinde kullanılacak e-posta sunucu profillerini yönetin.</p>
            <a href="{{ route('admin.smtp-profiles.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                <i class="fas fa-plus"></i> Yeni Profil
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Profil Adı</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Sunucu</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Gönderen</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Varsayılan</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($profiles as $profile)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-medium text-gray-800">{{ $profile->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $profile->host }}:{{ $profile->port }} ({{ $profile->encryption }})</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $profile->from_name }} &lt;{{ $profile->from_email }}&gt;</td>
                            <td class="px-6 py-4 text-center">
                                @if($profile->is_default)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Varsayılan</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <form method="POST" action="{{ route('admin.smtp-profiles.test', $profile, false) }}" class="inline"
                                          x-data x-on:submit.prevent="
                                              let email = prompt('Test e-posta adresi girin:');
                                              if (email) { $el.querySelector('[name=test_email]').value = email; $el.submit(); }
                                          ">
                                        @csrf
                                        <input type="hidden" name="test_email" value="">
                                        <button type="submit" class="p-1.5 text-gray-400 hover:text-green-600" title="Test Gönder">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                    </form>
                                    <a href="{{ route('admin.smtp-profiles.edit', $profile) }}" class="p-1.5 text-gray-400 hover:text-indigo-600" title="Düzenle">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.smtp-profiles.destroy', $profile) }}"
                                          onsubmit="return confirm('Bu profili silmek istediğinize emin misiniz?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600" title="Sil">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500">Henüz SMTP profili oluşturulmamış.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
