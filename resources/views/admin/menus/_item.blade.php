<div class="border border-gray-200 rounded-lg p-3 {{ $depth > 0 ? 'ml-8' : '' }}" data-id="{{ $item->id }}">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <i class="fas fa-grip-vertical text-gray-300 cursor-move"></i>
            <span class="w-2 h-2 rounded-full {{ $item->is_active ? 'bg-green-400' : 'bg-gray-300' }}"></span>
            <span class="text-sm font-medium text-gray-700">{{ $item->title }}</span>
            <span class="text-xs text-gray-400">{{ $item->url ?: ($item->page ? '/' . $item->page->slug : '#') }}</span>
        </div>
        <div class="flex items-center gap-1">
            <button type="button" onclick="if(confirm('Bu öğeyi silmek istediğinize emin misiniz?')) deleteMenuItem('{{ route('admin.menus.delete-item', [$menu->id, $item->id], false) }}')"
                    class="p-1.5 text-gray-400 hover:text-red-600 transition-colors">
                <i class="fas fa-trash-alt text-xs"></i>
            </button>
        </div>
    </div>

    @if($item->children && $item->children->count() > 0)
        <div class="mt-2 space-y-2">
            @foreach($item->children->sortBy('sort_order') as $child)
                @include('admin.menus._item', ['item' => $child, 'depth' => $depth + 1])
            @endforeach
        </div>
    @endif
</div>

@once
@push('scripts')
<script>
function deleteMenuItem(url) {
    fetch(url, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    }).then(r => r.json()).then(data => { if(data.success) location.reload(); });
}
</script>
@endpush
@endonce
