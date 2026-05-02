@extends('admin.layouts.app')

@section('title', 'Sitemap Ayarları')
@section('page-title', 'Sitemap Konfigürasyonu')

@section('content')
<form method="POST" action="{{ route('admin.sitemap.update', [], false) }}">
    @csrf
    @method('PUT')

    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
            <p class="text-sm text-gray-500">Her URL için sitemap öncelik, değişim sıklığı ve dahil etme ayarlarını yönetin.</p>
            <div class="flex items-center gap-3">
                <a href="{{ url('sitemap.xml') }}" target="_blank"
                   class="px-4 py-2 border border-gray-300 text-gray-600 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                    <i class="fas fa-external-link-alt mr-1"></i> Sitemap'i Gör
                </a>
                <form method="POST" action="{{ route('admin.sitemap.refresh', [], false) }}" class="inline">
                    @csrf
                    <button type="submit"
                            class="px-4 py-2 border border-amber-300 text-amber-700 text-sm font-medium rounded-lg hover:bg-amber-50 transition-colors"
                            onclick="return confirm('Sitemap önbelleği temizlenecek. Devam etmek istiyor musunuz?')">
                        <i class="fas fa-rotate mr-1"></i> Yenile
                    </button>
                </form>
                <button type="submit"
                        class="px-5 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                    <i class="fas fa-save mr-1"></i> Kaydet
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">URL (Slug)</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase w-32">Öncelik</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase w-40">Değişim Sıklığı</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase w-24">Hariç Tut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($entries as $entry)
                        <tr class="hover:bg-gray-50">
                            <input type="hidden" name="entries[{{ $loop->index }}][id]" value="{{ $entry->id }}">
                            <td class="px-6 py-3">
                                <span class="text-sm text-gray-800">/{{ $entry->slug }}</span>
                                <span class="text-xs text-gray-400 ml-2">({{ class_basename($entry->seoable_type ?? 'N/A') }})</span>
                            </td>
                            <td class="px-4 py-3">
                                <select name="entries[{{ $loop->index }}][sitemap_priority]"
                                        class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
                                    @foreach(['1.0','0.9','0.8','0.7','0.6','0.5','0.4','0.3','0.2','0.1'] as $p)
                                        <option value="{{ $p }}" {{ number_format($entry->sitemap_priority ?? 0.5, 1) == $p ? 'selected' : '' }}>{{ $p }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-4 py-3">
                                <select name="entries[{{ $loop->index }}][sitemap_changefreq]"
                                        class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
                                    @foreach(['always','hourly','daily','weekly','monthly','yearly','never'] as $freq)
                                        <option value="{{ $freq }}" {{ ($entry->sitemap_changefreq ?? 'weekly') === $freq ? 'selected' : '' }}>{{ $freq }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <input type="hidden" name="entries[{{ $loop->index }}][sitemap_exclude]" value="0">
                                <input type="checkbox" name="entries[{{ $loop->index }}][sitemap_exclude]" value="1"
                                       {{ ($entry->sitemap_exclude ?? false) ? 'checked' : '' }}
                                       class="h-4 w-4 rounded border-gray-300 text-red-600 focus:ring-red-500">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($entries->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $entries->links() }}
            </div>
        @endif
    </div>
</form>
@endsection
