{{--
    Reusable help banner with optional intro + numbered steps.
    Collapsible (Alpine), starts open.

    Usage:
        @include('tours::admin.partials._help', [
            'title' => 'Rota takvimi nasıl oluşturulur?',
            'intro' => 'Rota = günlük gezi programı.',
            'steps' => ['Adım 1...', 'Adım 2...'],
        ])
--}}
<div x-data="{ open: false }" class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-3 mb-5">
    <div class="flex items-start justify-between gap-3">
        <div class="flex items-center gap-2 text-sm font-semibold text-blue-800">
            <i class="fas fa-circle-info"></i> {{ $title ?? 'Nasıl kullanılır?' }}
        </div>
        <button type="button" @click="open = !open"
                class="text-blue-400 hover:text-blue-600 text-xs whitespace-nowrap">
            <span x-show="open"><i class="fas fa-chevron-up"></i> gizle</span>
            <span x-show="!open" x-cloak><i class="fas fa-chevron-down"></i> göster</span>
        </button>
    </div>
    <div x-show="open" x-cloak class="mt-2 text-xs text-blue-700 leading-relaxed space-y-2">
        @if(!empty($intro))
            <p>{{ $intro }}</p>
        @endif
        @if(!empty($steps))
            <ol class="list-decimal pl-5 space-y-1">
                @foreach($steps as $step)
                    <li>{!! $step !!}</li>
                @endforeach
            </ol>
        @endif
        @if(!empty($note))
            <p class="text-blue-500 italic"><i class="fas fa-lightbulb mr-1"></i>{{ $note }}</p>
        @endif
    </div>
</div>
