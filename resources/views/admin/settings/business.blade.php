@extends('admin.layouts.app')
@section('title', 'İşletme Bilgileri')

@section('content')
<div class="max-w-3xl" x-data="{
    generateJsonLd() {
        const b = {
            '@context': 'https://schema.org',
            '@type': document.getElementById('business_type').value || 'Organization',
            name: document.getElementById('business_name').value,
            url: '{{ url('/') }}',
            telephone: document.getElementById('business_telephone').value,
            email: document.getElementById('business_email').value,
        };

        const street = document.getElementById('business_address_street').value;
        if (street) {
            b.address = {
                '@type': 'PostalAddress',
                streetAddress: street,
                addressLocality: document.getElementById('business_address_city').value,
                postalCode: document.getElementById('business_address_postal_code').value,
                addressCountry: document.getElementById('business_address_country').value || 'TR',
            };
        }

        const lat = document.getElementById('business_geo_lat').value;
        const lng = document.getElementById('business_geo_lng').value;
        if (lat && lng) {
            b.geo = { '@type': 'GeoCoordinates', latitude: parseFloat(lat), longitude: parseFloat(lng) };
        }

        const hours = document.getElementById('business_opening_hours').value;
        if (hours) {
            try {
                const parsed = JSON.parse(hours);
                if (Array.isArray(parsed)) {
                    b.openingHoursSpecification = parsed.map(h => ({
                        '@type': 'OpeningHoursSpecification',
                        dayOfWeek: h.days ? h.days.split('-').map(d => d.trim()) : [],
                        opens: h.hours ? h.hours.split('-')[0].trim() : '09:00',
                        closes: h.hours ? (h.hours.split('-')[1] || '18:00').trim() : '18:00',
                    }));
                }
            } catch {}
        }

        // Remove empty fields
        Object.keys(b).forEach(k => { if (b[k] === '' || b[k] === null || b[k] === undefined) delete b[k]; });

        document.getElementById('organization_json_ld').value = JSON.stringify(b, null, 2);
    }
}">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">İşletme Bilgileri</h1>
            <p class="text-sm text-gray-500 mt-1">
                Schema.org LocalBusiness yapısal verisi ve Google Rich Results için kullanılır.
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.settings.index') }}"
               class="text-xs px-3 py-1.5 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 flex items-center gap-1">
                <i class="fas fa-cog text-[10px]"></i> Genel Ayarlar
            </a>
            <a href="{{ route('admin.settings.crawl') }}"
               class="text-xs px-3 py-1.5 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 flex items-center gap-1">
                <i class="fas fa-robot text-[10px]"></i> AI/Tarama
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg p-4 mb-6 text-sm">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-4 mb-6 text-sm">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.settings.business.update', [], false) }}" class="space-y-6">
        @csrf @method('PUT')

        {{-- ─── Temel Kimlik ─────────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-base font-semibold text-gray-800 mb-4">
                <i class="fas fa-building mr-2 text-indigo-500"></i>Kuruluş Kimliği
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        İşletme Adı
                        <span class="text-gray-400 font-normal ml-1">— schema.org/name</span>
                    </label>
                    <input type="text" id="business_name" name="business[name]"
                           value="{{ old('business.name', $settings['business.name'] ?? '') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           placeholder="Örn: ACME Teknoloji A.Ş.">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        İşletme Türü
                        <span class="text-gray-400 font-normal ml-1">— @type</span>
                    </label>
                    <select id="business_type" name="business[type]"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        @foreach($businessTypes as $val => $label)
                            <option value="{{ $val }}"
                                {{ old('business.type', $settings['business.type'] ?? 'Organization') === $val ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefon</label>
                    <input type="text" id="business_telephone" name="business[telephone]"
                           value="{{ old('business.telephone', $settings['business.telephone'] ?? '') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           placeholder="+90 212 000 0000">
                    <p class="text-xs text-gray-400 mt-1">E.164 formatı önerilir: +90 ile başlayan</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">E-posta</label>
                    <input type="email" id="business_email" name="business[email]"
                           value="{{ old('business.email', $settings['business.email'] ?? '') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           placeholder="info@sirketiniz.com">
                </div>
            </div>
        </div>

        {{-- ─── Adres ────────────────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-base font-semibold text-gray-800 mb-4">
                <i class="fas fa-map-marker-alt mr-2 text-red-500"></i>Adres — PostalAddress
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sokak / Cadde</label>
                    <input type="text" id="business_address_street" name="business[address_street]"
                           value="{{ old('business.address_street', $settings['business.address_street'] ?? '') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           placeholder="Örn: Atatürk Cd. No:1 Daire 5">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Şehir</label>
                    <input type="text" id="business_address_city" name="business[address_city]"
                           value="{{ old('business.address_city', $settings['business.address_city'] ?? '') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           placeholder="İstanbul">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Posta Kodu</label>
                    <input type="text" id="business_address_postal_code" name="business[address_postal_code]"
                           value="{{ old('business.address_postal_code', $settings['business.address_postal_code'] ?? '') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           placeholder="34000">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ülke Kodu</label>
                    <input type="text" id="business_address_country" name="business[address_country]"
                           value="{{ old('business.address_country', $settings['business.address_country'] ?? 'TR') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           placeholder="TR" maxlength="2">
                    <p class="text-xs text-gray-400 mt-1">ISO 3166-1 alpha-2 (2 harf) — TR, US, DE…</p>
                </div>
            </div>
        </div>

        {{-- ─── Coğrafi Koordinat ───────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-base font-semibold text-gray-800 mb-1">
                <i class="fas fa-crosshairs mr-2 text-blue-500"></i>Coğrafi Konum — GeoCoordinates
            </h2>
            <p class="text-xs text-gray-500 mb-4">
                Google Maps'teki işletme konumundan kopyalayabilirsiniz.
                Google Haritalar → sağ tıkla → koordinatları kopyala.
            </p>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Enlem (latitude)</label>
                    <input type="number" id="business_geo_lat" name="business[geo_lat]"
                           value="{{ old('business.geo_lat', $settings['business.geo_lat'] ?? '') }}"
                           step="0.000001" min="-90" max="90"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           placeholder="41.015137">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Boylam (longitude)</label>
                    <input type="number" id="business_geo_lng" name="business[geo_lng]"
                           value="{{ old('business.geo_lng', $settings['business.geo_lng'] ?? '') }}"
                           step="0.000001" min="-180" max="180"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           placeholder="28.979530">
                </div>
            </div>

            {{-- Map preview (lat/lng doldurulunca) --}}
            <div id="map-preview-wrap" class="mt-4 rounded-lg overflow-hidden border border-gray-200 hidden">
                <iframe id="map-preview-iframe"
                    src=""
                    width="100%" height="200" style="border:0;" allowfullscreen loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
        </div>

        {{-- ─── Çalışma Saatleri ────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-base font-semibold text-gray-800 mb-1">
                <i class="fas fa-clock mr-2 text-yellow-500"></i>Çalışma Saatleri — OpeningHoursSpecification
            </h2>
            <p class="text-xs text-gray-500 mb-4">
                JSON formatında girin. Her kayıt için <code class="bg-gray-100 px-1 rounded">days</code> ve
                <code class="bg-gray-100 px-1 rounded">hours</code> alanı olmalı.
                <br>Örnek: <code class="bg-gray-100 px-1 rounded text-xs">[{"days":"Mo-Fr","hours":"09:00-18:00"},{"days":"Sa","hours":"10:00-14:00"}]</code>
            </p>
            <textarea id="business_opening_hours" name="business[opening_hours]" rows="4"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                      placeholder='[{"days":"Mo-Fr","hours":"09:00-18:00"}]'
            >{{ old('business.opening_hours', $settings['business.opening_hours'] ?? '') }}</textarea>
            <p class="text-xs text-gray-400 mt-1">Gün kısaltmaları: Mo Tu We Th Fr Sa Su</p>
        </div>

        {{-- ─── Özel JSON-LD ─────────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-1">
                <h2 class="text-base font-semibold text-gray-800">
                    <i class="fas fa-code mr-2 text-purple-500"></i>Özel Organization JSON-LD
                </h2>
                <button type="button" @click="generateJsonLd()"
                        class="text-xs px-3 py-1.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 flex items-center gap-1.5">
                    <i class="fas fa-magic text-[10px]"></i> Yukarıdaki bilgilerden üret
                </button>
            </div>
            <p class="text-xs text-gray-500 mb-3">
                Dolu olursa bu alan site genelindeki otomatik JSON-LD'yi <strong>geçersiz kılar</strong>.
                Boş bırakırsanız yukarıdaki alanlar kullanılarak otomatik oluşturulur.
                <a href="https://search.google.com/test/rich-results" target="_blank"
                   class="text-indigo-600 hover:underline ml-1">Google Rich Results Test →</a>
            </p>
            <textarea id="organization_json_ld" name="business[organization_json_ld]" rows="12"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                      placeholder='{ "@context": "https://schema.org", "@type": "Organization", "name": "..." }'
            >{{ old('business.organization_json_ld', $settings['business.organization_json_ld'] ?? '') }}</textarea>
        </div>

        <div class="flex items-center justify-between pt-2">
            <p class="text-xs text-gray-400">
                <i class="fas fa-info-circle mr-1"></i>
                Değişiklikler anında frontend JSON-LD çıktısını günceller.
            </p>
            <button type="submit"
                    class="px-6 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors flex items-center gap-2">
                <i class="fas fa-save"></i> Kaydet
            </button>
        </div>
    </form>
</div>

<script>
(function () {
    const latEl = document.getElementById('business_geo_lat');
    const lngEl = document.getElementById('business_geo_lng');
    const wrap   = document.getElementById('map-preview-wrap');
    const iframe = document.getElementById('map-preview-iframe');

    function updateMap() {
        const lat = parseFloat(latEl.value);
        const lng = parseFloat(lngEl.value);
        if (!isNaN(lat) && !isNaN(lng) && Math.abs(lat) <= 90 && Math.abs(lng) <= 180) {
            iframe.src = `https://maps.google.com/maps?q=${lat},${lng}&z=15&output=embed`;
            wrap.classList.remove('hidden');
        } else {
            wrap.classList.add('hidden');
        }
    }

    latEl.addEventListener('change', updateMap);
    lngEl.addEventListener('change', updateMap);

    // Init on load
    if (latEl.value && lngEl.value) updateMap();
})();
</script>
@endsection
