@extends('admin.layouts.app')
@section('title', 'Tur Kategorileri')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Tur Kategorileri</h1>
        <p class="text-sm text-gray-500">Hiyerarşik kategori yapısı; tur listeleme + frontend filtre menüsü buradan beslenir.</p>
    </div>
    <a href="{{ route('admin.tour-categories.create') }}"
       class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 font-medium">
        <i class="fas fa-plus mr-1"></i> Yeni Kategori
    </a>
</div>

@if(session('success'))<div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ session('success') }}</div>@endif
@if(session('error'))<div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">{{ session('error') }}</div>@endif

<div class="bg-white rounded-xl shadow-sm border overflow-hidden">
    @if($categories->isEmpty())
        <div class="p-10 text-center text-sm text-gray-500">
            Henüz kategori yok. <a href="{{ route('admin.tour-categories.create') }}" class="text-indigo-600 hover:underline">İlkini oluştur</a>.
        </div>
    @else
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left text-xs font-semibold text-gray-500 uppercase">
                    <th class="px-4 py-3">Kategori</th>
                    <th class="px-4 py-3">Slug</th>
                    <th class="px-4 py-3">Tur sayısı</th>
                    <th class="px-4 py-3">Durum</th>
                    <th class="px-4 py-3 text-right">Eylem</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($categories as $category)
                    @include('tours::admin.categories._row', ['category' => $category, 'depth' => 0, 'defaultLanguage' => $defaultLanguage])
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
