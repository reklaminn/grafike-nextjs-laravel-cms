@extends('admin.layouts.app')
@section('title', 'Pazarlama Etiketleri')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Pazarlama Etiketleri</h1>
        <p class="text-sm text-gray-500">
            Tur kartlarında ve filtre menülerinde gösterilen segment etiketleri
            (Aile Dostu, Romantik, Erken Rezervasyon …).
        </p>
    </div>
    <a href="{{ route('admin.tour-tags.create') }}"
       class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 font-medium">
        <i class="fas fa-plus mr-1"></i> Yeni Etiket
    </a>
</div>

@if(session('success'))<div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ session('success') }}</div>@endif
@if(session('error'))<div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">{{ session('error') }}</div>@endif

<div class="bg-white rounded-xl shadow-sm border overflow-hidden">
    @if($tags->isEmpty())
        <div class="p-10 text-center text-sm text-gray-500">
            Henüz etiket yok.
            <a href="{{ route('admin.tour-tags.create') }}" class="text-indigo-600 hover:underline">İlkini oluştur</a>.
        </div>
    @else
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left text-xs font-semibold text-gray-500 uppercase">
                    <th class="px-4 py-3">Etiket</th>
                    <th class="px-4 py-3">Slug</th>
                    <th class="px-4 py-3">İkon</th>
                    <th class="px-4 py-3">Sıra</th>
                    <th class="px-4 py-3">Durum</th>
                    <th class="px-4 py-3 text-right">Eylem</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($tags as $tag)
                    @php
                        $tr = $defaultLanguage ? $tag->translationFor($defaultLanguage->id) : $tag->translations->first();
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-800 font-medium">
                            {{ $tr->name ?? '— çevirisi yok —' }}
                        </td>
                        <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $tag->slug }}</td>
                        <td class="px-4 py-3">
                            @if($tag->icon)
                                <code class="text-xs text-indigo-600">{{ $tag->icon }}</code>
                                <i class="fas {{ $tag->icon }} ml-2 text-gray-400"></i>
                            @else
                                <span class="text-xs text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $tag->sort_order }}</td>
                        <td class="px-4 py-3">
                            @if($tag->is_active)
                                <span class="inline-flex px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-xs font-medium">Aktif</span>
                            @else
                                <span class="inline-flex px-2 py-0.5 rounded-full bg-gray-200 text-gray-600 text-xs font-medium">Pasif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.tour-tags.edit', $tag) }}"
                               class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">Düzenle</a>
                            <form action="{{ route('admin.tour-tags.destroy', $tag) }}" method="POST" class="inline ml-3"
                                  onsubmit="return confirm('Etiketi silmek istediğinize emin misiniz?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">Sil</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $tags->links() }}</div>
    @endif
</div>
@endsection
