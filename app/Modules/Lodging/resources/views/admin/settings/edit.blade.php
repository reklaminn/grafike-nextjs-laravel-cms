@extends('admin.layouts.app')
@section('title', 'Konaklama Ayarları')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Konaklama Ayarları</h1>
    <p class="text-sm text-gray-500">Bildirim e-postası, WhatsApp numarası ve rezervasyon kod ön eki.</p>
</div>

@if(session('success'))<div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ session('success') }}</div>@endif
@if($errors->any())<div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700"><ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

<form method="POST" action="{{ route('admin.lodging.settings.update') }}" class="max-w-xl">
    @csrf @method('PUT')
    <div class="bg-white rounded-xl shadow-sm border p-6 space-y-5">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Bildirim E-postası</label>
            <input type="email" name="notification_email" value="{{ old('notification_email', $setting->notification_email) }}"
                   placeholder="rezervasyon@oteliniz.com"
                   class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            <p class="text-xs text-gray-400 mt-1">Yeni talep geldiğinde bu adrese e-posta gider.</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">WhatsApp Numarası</label>
            <input type="text" name="whatsapp_number" value="{{ old('whatsapp_number', $setting->whatsapp_number) }}"
                   placeholder="905551112233"
                   class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            <p class="text-xs text-gray-400 mt-1">Uluslararası format (ülke kodu ile). "WhatsApp ile Yanıtla" bağlantısı misafirin numarasını kullanır; bu alan bilgilendirme amaçlıdır.</p>
        </div>
        <div class="grid grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kod Ön Eki</label>
                <input type="text" name="reservation_code_prefix" value="{{ old('reservation_code_prefix', $setting->reservation_code_prefix) }}"
                       maxlength="8" placeholder="VS"
                       class="w-full rounded-lg border-gray-300 text-sm uppercase focus:border-indigo-500 focus:ring-indigo-500">
                <p class="text-xs text-gray-400 mt-1">Örn. VS → VS-2607-A3F</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Varsayılan Para Birimi</label>
                <input type="text" name="default_currency" value="{{ old('default_currency', $setting->default_currency) }}"
                       maxlength="3" class="w-full rounded-lg border-gray-300 text-sm uppercase focus:border-indigo-500 focus:ring-indigo-500">
            </div>
        </div>
        <div class="pt-2">
            <button type="submit" class="px-5 py-2.5 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 font-medium">Kaydet</button>
        </div>
    </div>
</form>
@endsection
