@extends('admin.layouts.app')

@section('title', 'Kütüphaneye Veri Yükle')
@section('page-title', 'Kütüphaneye Veri Yükle')

@section('content')
<div class="flex items-center gap-3 mb-5">
    <a href="{{ route('admin.library.index') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left"></i></a>
    <h1 class="text-xl font-bold text-gray-800">Legacy Import — CSV / Excel Yükle</h1>
</div>

@if(session('success'))<div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ session('success') }}</div>@endif
@if(session('error'))<div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">{{ session('error') }}</div>@endif
@if($errors->any())
<div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl">
    <ul class="text-sm text-red-700 list-disc pl-5 space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<div class="mb-5 p-4 bg-blue-50 border border-blue-200 rounded-xl text-sm text-blue-800 space-y-1">
    <p><i class="fas fa-info-circle mr-1"></i> Eski sistemdeki tabloyu <strong>CSV veya Excel</strong> olarak dışa aktarın (ilk satır başlık olmalı), buraya yükleyin. Sonraki adımda kolonları kütüphane alanlarına eşleyeceksiniz.</p>
    <p class="text-xs text-blue-700">İlişki sırası: önce <strong>Gemi Firmaları</strong>, sonra <strong>Gemiler</strong>, sonra <strong>Kabinler</strong> import edilmeli (gemi firmanın, kabin geminin legacy_id'sine bağlanır). Limanlar ve Destinasyonlar bağımsızdır.</p>
</div>

<form method="POST" action="{{ route('admin.library.import.preview') }}" enctype="multipart/form-data"
      class="bg-white rounded-xl shadow-sm border p-6 space-y-6 max-w-3xl">
    @csrf

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">1. Hangi veriyi yüklüyorsunuz?</label>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
            @foreach($specs as $key => $spec)
                <label class="flex items-center gap-2 border border-gray-200 rounded-lg px-3 py-2.5 cursor-pointer hover:bg-gray-50 has-[:checked]:border-indigo-400 has-[:checked]:bg-indigo-50">
                    <input type="radio" name="entity" value="{{ $key }}" {{ old('entity') === $key || (! old('entity') && $loop->first) ? 'checked' : '' }}>
                    <span class="text-sm text-gray-700">{{ $spec['label'] }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">2. Dosya (CSV / XLSX)</label>
        <input type="file" name="file" accept=".csv,.txt,.xlsx,.xls" required
               class="block w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
        <p class="text-xs text-gray-400 mt-1">Maks 20 MB. İlk satır kolon başlıkları olmalı.</p>
    </div>

    <div class="flex justify-end">
        <button class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 font-medium">
            <i class="fas fa-arrow-right mr-1"></i> Devam: Kolonları Eşle
        </button>
    </div>
</form>
@endsection
