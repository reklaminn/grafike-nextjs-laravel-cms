@php
    $name = optional($category->translations->firstWhere('language_id', $defaultLanguage?->id))->name
        ?? optional($category->translations->first())->name
        ?? '(adsız)';
    $tourCount = $category->tours()->count();
@endphp
<tr>
    <td class="px-4 py-3">
        <span style="padding-left: {{ $depth * 20 }}px;">
            @if($depth > 0)<i class="fas fa-level-up-alt fa-rotate-90 text-gray-300 mr-2"></i>@endif
            <span class="font-medium text-gray-700">{{ $name }}</span>
        </span>
    </td>
    <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $category->slug }}</td>
    <td class="px-4 py-3 text-gray-600">{{ $tourCount }}</td>
    <td class="px-4 py-3">
        @if($category->is_active)
            <span class="text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700">Aktif</span>
        @else
            <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">Pasif</span>
        @endif
    </td>
    <td class="px-4 py-3 text-right">
        <a href="{{ route('admin.tour-categories.edit', $category) }}"
           class="text-indigo-600 hover:underline text-xs mr-3">
            <i class="fas fa-edit"></i> Düzenle
        </a>
        <form method="POST" action="{{ route('admin.tour-categories.destroy', $category) }}" class="inline">
            @csrf @method('DELETE')
            <button type="submit"
                    onclick="return confirm('Bu kategori silinsin mi?')"
                    class="text-red-600 hover:underline text-xs">
                <i class="fas fa-trash"></i> Sil
            </button>
        </form>
    </td>
</tr>
@foreach($category->children as $child)
    @include('tours::admin.categories._row', ['category' => $child, 'depth' => $depth + 1, 'defaultLanguage' => $defaultLanguage])
@endforeach
