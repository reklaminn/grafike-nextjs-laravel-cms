{{--
    Erişim kısıtlaması sidebar kartı
    Değişkenler: $page (nullable), $memberGroups (Collection)
--}}
@if($memberGroups->isNotEmpty())
<div class="bg-white rounded-xl shadow-sm border p-4 space-y-3">
    <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-1.5">
        <i class="fas fa-shield-alt text-indigo-400 text-xs"></i>
        Erişim Kısıtlaması
    </h3>

    <p class="text-xs text-gray-500 leading-relaxed">
        Seçili gruplar dışındaki ziyaretçiler bu sayfanın içeriğini göremez.
        Boş bırakılırsa herkese açıktır.
    </p>

    @php
        $selectedGroups = old('allowed_group_ids',
            isset($page) ? (array) ($page->allowed_group_ids ?? []) : []
        );
        $selectedGroups = array_map('intval', $selectedGroups);
    @endphp

    <div class="space-y-1.5">
        @foreach($memberGroups as $group)
        <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer select-none">
            <input type="checkbox"
                   name="allowed_group_ids[]"
                   value="{{ $group->id }}"
                   {{ in_array($group->id, $selectedGroups) ? 'checked' : '' }}
                   class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            <span>{{ $group->name }}</span>
        </label>
        @endforeach
    </div>

    @if(in_array(true, array_map(fn($id) => in_array($id, $selectedGroups), $memberGroups->pluck('id')->all())))
    <p class="text-xs text-indigo-600 bg-indigo-50 rounded-lg px-2.5 py-1.5">
        <i class="fas fa-lock mr-1"></i>
        Bu sayfa kısıtlı — sadece seçili grup üyeleri görebilir.
    </p>
    @endif
</div>
@endif
