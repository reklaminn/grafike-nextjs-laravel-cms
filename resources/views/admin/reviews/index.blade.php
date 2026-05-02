@extends('admin.layouts.app')
@section('title', 'Yorum Moderasyonu')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Yorum Moderasyonu</h1>
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm border p-4 text-center">
        <div class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</div>
        <div class="text-xs text-gray-500">Toplam</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border p-4 text-center">
        <div class="text-2xl font-bold text-yellow-600">{{ $stats['pending'] }}</div>
        <div class="text-xs text-gray-500">Bekleyen</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border p-4 text-center">
        <div class="text-2xl font-bold text-green-600">{{ $stats['approved'] }}</div>
        <div class="text-xs text-gray-500">Onaylı</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border p-4 text-center">
        <div class="text-2xl font-bold text-indigo-600">{{ $stats['avg_rating'] }}</div>
        <div class="text-xs text-gray-500">Ort. Puan</div>
    </div>
</div>

{{-- Filters --}}
<form method="GET" class="flex gap-3 mb-6">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Yazar veya yorum ara..."
           class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-indigo-500">
    <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
        <option value="">Tüm Yorumlar</option>
        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Bekleyen</option>
        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Onaylı</option>
    </select>
    <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-lg text-sm">Filtrele</button>
</form>

{{-- Reviews List --}}
<div class="space-y-4">
    @forelse($reviews as $review)
        <div class="bg-white rounded-xl shadow-sm border p-5 {{ !$review->is_approved ? 'border-l-4 border-l-yellow-400' : '' }}">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="font-semibold text-gray-800">{{ $review->author_name }}</span>
                        @if($review->author_email)
                            <span class="text-xs text-gray-400">{{ $review->author_email }}</span>
                        @endif
                        {{-- Rating Stars --}}
                        <div class="flex">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="w-4 h-4 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-200' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            @endfor
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-xs {{ $review->is_approved ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                            {{ $review->is_approved ? 'Onaylı' : 'Bekliyor' }}
                        </span>
                    </div>
                    @if($review->body)
                        <p class="text-sm text-gray-600">{{ $review->body }}</p>
                    @endif
                    <div class="flex items-center gap-3 mt-2 text-xs text-gray-400">
                        <span>{{ $review->created_at->format('d.m.Y H:i') }}</span>
                        <span>IP: {{ $review->ip_address }}</span>
                        @if($review->reviewable)
                            <span>
                                {{ class_basename($review->reviewable_type) }}: {{ $review->reviewable->title ?? '#' . $review->reviewable_id }}
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-2 ml-4">
                    @if(!$review->is_approved)
                        <form method="POST" action="{{ route('admin.reviews.approve', $review, false) }}">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 bg-green-50 text-green-700 rounded-lg text-xs hover:bg-green-100" title="Onayla">
                                <i class="fas fa-check"></i>
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.reviews.reject', $review, false) }}">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 bg-yellow-50 text-yellow-700 rounded-lg text-xs hover:bg-yellow-100" title="Reddet">
                                <i class="fas fa-times"></i>
                            </button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}" onsubmit="return confirm('Bu yorumu silmek istediğinize emin misiniz?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="px-3 py-1.5 bg-red-50 text-red-600 rounded-lg text-xs hover:bg-red-100" title="Sil">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-xl shadow-sm border p-8 text-center text-gray-400">
            <p>Henüz yorum yok.</p>
        </div>
    @endforelse
</div>

<div class="mt-6">{{ $reviews->links() }}</div>
@endsection
