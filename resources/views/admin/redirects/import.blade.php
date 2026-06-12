@extends('admin.layouts.app')

@section('title', 'CSV İçe Aktar')
@section('page-title', 'Toplu Yönlendirme İçe Aktarma')

@section('content')
    <div class="max-w-xl">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-base font-semibold text-gray-800 mb-4">CSV Dosyası Yükle</h3>
            <p class="text-sm text-gray-500 mb-4">CSV dosyası formatı: <code class="bg-gray-100 px-2 py-0.5 rounded text-xs">kaynak_url,hedef_url,durum_kodu</code></p>
            <p class="text-xs text-gray-400 mb-4">Google Search Console export dosyasını doğrudan yükleyebilirsiniz. İlk satır başlık olarak kabul edilir.</p>

            <form method="POST" action="{{ route('admin.redirects.process-import', [], false) }}" enctype="multipart/form-data">
                @csrf
                <div class="space-y-4">
                    <input type="file" name="csv_file" accept=".csv,.txt" required
                           class="w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">

                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-xs font-medium text-gray-600 mb-2">Örnek CSV:</p>
                        <pre class="text-xs text-gray-500 font-mono">/eski-sayfa,/yeni-sayfa,301
/hakkimizda-eski,/hakkimizda,301
/urun/eski-urun,/urunler/yeni-urun,302</pre>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="px-6 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                            <i class="fas fa-upload mr-1"></i> İçe Aktar
                        </button>
                        <a href="{{ route('admin.redirects.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200 transition-colors">İptal</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
