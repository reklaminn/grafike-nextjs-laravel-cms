@extends('admin.layouts.app')
@section('title', 'İşletme Bilgileri')

@section('content')
@php
    $siteUrl = url('/');
@endphp
<div class="max-w-3xl">

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
                        <span class="text-gray-400 font-normal ml-1">— schema type</span>
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
                Aşağıdaki kısa yollardan birini kullanın veya enlem/boylam değerlerini elle girin.
            </p>

            {{-- Hızlı yöntemler --}}
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 mb-4 space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        <i class="fas fa-paste mr-1 text-gray-400"></i>
                        Google Maps koordinatını yapıştır
                    </label>
                    <div class="flex gap-2">
                        <input type="text" id="coord_paste"
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                               placeholder="41.015137, 28.979530">
                        <button type="button" id="btn-coord-apply"
                                class="px-3 py-2 bg-indigo-600 text-white text-xs font-medium rounded-lg hover:bg-indigo-700 whitespace-nowrap">
                            Uygula
                        </button>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">
                        Google Haritalar üzerinde sağ tıkla → ilk satırdaki "lat, lng" değerini kopyala-yapıştır.
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="button" id="btn-geolocate"
                            class="px-3 py-2 bg-blue-50 border border-blue-200 text-blue-700 text-xs font-medium rounded-lg hover:bg-blue-100 flex items-center gap-1.5">
                        <i class="fas fa-location-arrow"></i> Konumumu Kullan
                    </button>
                    <button type="button" id="btn-geocode"
                            class="px-3 py-2 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-medium rounded-lg hover:bg-emerald-100 flex items-center gap-1.5">
                        <i class="fas fa-search-location"></i> Adresten Bul
                    </button>
                    <span id="coord_status" class="text-xs text-gray-500 self-center"></span>
                </div>
            </div>

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
                    width="100%" height="220" style="border:0;" allowfullscreen loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
        </div>

        {{-- ─── Çalışma Saatleri ────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-1">
                <h2 class="text-base font-semibold text-gray-800">
                    <i class="fas fa-clock mr-2 text-yellow-500"></i>Çalışma Saatleri
                </h2>
                <button type="button" id="btn-toggle-hours-json"
                        class="text-xs text-gray-500 hover:text-gray-700 flex items-center gap-1">
                    <i class="fas fa-code text-[10px]"></i> Gelişmiş (JSON)
                </button>
            </div>
            <p class="text-xs text-gray-500 mb-4">
                Her gün için açık/kapalı durumunu ve saatleri seçin.
            </p>

            {{-- Hızlı preset'ler --}}
            <div class="flex flex-wrap gap-2 mb-3">
                <button type="button" data-hours-preset="weekdays-9-18"
                        class="px-3 py-1.5 bg-gray-50 border border-gray-200 text-xs font-medium text-gray-700 rounded-lg hover:bg-gray-100">
                    <i class="fas fa-bolt text-[10px] mr-1 text-yellow-500"></i>Hafta içi 09:00–18:00
                </button>
                <button type="button" data-hours-preset="all-9-18"
                        class="px-3 py-1.5 bg-gray-50 border border-gray-200 text-xs font-medium text-gray-700 rounded-lg hover:bg-gray-100">
                    <i class="fas fa-bolt text-[10px] mr-1 text-yellow-500"></i>Hafta içi + Cmt 09:00–18:00
                </button>
                <button type="button" data-hours-preset="247"
                        class="px-3 py-1.5 bg-gray-50 border border-gray-200 text-xs font-medium text-gray-700 rounded-lg hover:bg-gray-100">
                    <i class="fas fa-bolt text-[10px] mr-1 text-yellow-500"></i>7/24 Açık
                </button>
                <button type="button" data-hours-preset="clear"
                        class="px-3 py-1.5 bg-gray-50 border border-gray-200 text-xs font-medium text-gray-700 rounded-lg hover:bg-gray-100">
                    <i class="fas fa-eraser text-[10px] mr-1 text-gray-400"></i>Temizle
                </button>
            </div>

            {{-- Görsel editör --}}
            <div id="hours-editor" class="border border-gray-200 rounded-lg divide-y divide-gray-100">
                @php
                    $weekDays = [
                        ['code' => 'Mo', 'name' => 'Pazartesi'],
                        ['code' => 'Tu', 'name' => 'Salı'],
                        ['code' => 'We', 'name' => 'Çarşamba'],
                        ['code' => 'Th', 'name' => 'Perşembe'],
                        ['code' => 'Fr', 'name' => 'Cuma'],
                        ['code' => 'Sa', 'name' => 'Cumartesi'],
                        ['code' => 'Su', 'name' => 'Pazar'],
                    ];
                @endphp
                @foreach ($weekDays as $day)
                    <div class="flex items-center gap-3 p-3 hours-row" data-day="{{ $day['code'] }}">
                        <label class="flex items-center gap-2 w-32 cursor-pointer">
                            <input type="checkbox" data-role="day-active"
                                   class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                            <span class="text-sm font-medium text-gray-700">{{ $day['name'] }}</span>
                        </label>
                        <div class="flex items-center gap-2 flex-1">
                            <input type="time" data-role="open" value="09:00"
                                   class="px-2 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent disabled:bg-gray-50 disabled:text-gray-400">
                            <span class="text-gray-400">—</span>
                            <input type="time" data-role="close" value="18:00"
                                   class="px-2 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent disabled:bg-gray-50 disabled:text-gray-400">
                            <span data-role="status" class="text-xs text-gray-400 ml-1">Kapalı</span>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Hidden actual field (gönderilen) --}}
            <textarea id="business_opening_hours" name="business[opening_hours]" rows="4"
                      class="hidden w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:ring-2 focus:ring-indigo-500 focus:border-transparent mt-3"
                      placeholder='[{"days":"Mo-Fr","hours":"09:00-18:00"}]'
            >{{ old('business.opening_hours', $settings['business.opening_hours'] ?? '') }}</textarea>
            <p id="hours-json-help" class="hidden text-xs text-gray-400 mt-1">
                Schema.org formatında JSON. UI'dan değişiklik yapınca burası otomatik güncellenir.
            </p>
        </div>

        {{-- ─── Özel JSON-LD ─────────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-1">
                <h2 class="text-base font-semibold text-gray-800">
                    <i class="fas fa-code mr-2 text-purple-500"></i>Özel Organization JSON-LD
                </h2>
                <button type="button" id="btn-generate-jsonld"
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
                      placeholder='{ "context": "https://schema.org", "type": "Organization", "name": "..." }'
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
    const SITE_URL = {!! json_encode($siteUrl) !!};

    // ── JSON-LD generator ────────────────────────────────────────────
    function generateJsonLd() {
        const ctxKey  = String.fromCharCode(64) + 'context';
        const typeKey = String.fromCharCode(64) + 'type';

        const b = {
            [ctxKey]:  'https://schema.org',
            [typeKey]: document.getElementById('business_type').value || 'Organization',
            name:      document.getElementById('business_name').value,
            url:       SITE_URL,
            telephone: document.getElementById('business_telephone').value,
            email:     document.getElementById('business_email').value,
        };

        const street = document.getElementById('business_address_street').value;
        if (street) {
            b.address = {
                [typeKey]:       'PostalAddress',
                streetAddress:   street,
                addressLocality: document.getElementById('business_address_city').value,
                postalCode:      document.getElementById('business_address_postal_code').value,
                addressCountry:  document.getElementById('business_address_country').value || 'TR',
            };
        }

        const lat = document.getElementById('business_geo_lat').value;
        const lng = document.getElementById('business_geo_lng').value;
        if (lat && lng) {
            b.geo = { [typeKey]: 'GeoCoordinates', latitude: parseFloat(lat), longitude: parseFloat(lng) };
        }

        const hours = document.getElementById('business_opening_hours').value;
        if (hours) {
            try {
                const parsed = JSON.parse(hours);
                if (Array.isArray(parsed)) {
                    b.openingHoursSpecification = parsed.map(function (h) {
                        return {
                            [typeKey]: 'OpeningHoursSpecification',
                            dayOfWeek: h.days ? h.days.split('-').map(function (d) { return d.trim(); }) : [],
                            opens:     h.hours ? h.hours.split('-')[0].trim() : '09:00',
                            closes:    h.hours ? (h.hours.split('-')[1] || '18:00').trim() : '18:00',
                        };
                    });
                }
            } catch (e) {}
        }

        Object.keys(b).forEach(function (k) {
            if (b[k] === '' || b[k] === null || b[k] === undefined) delete b[k];
        });

        document.getElementById('organization_json_ld').value = JSON.stringify(b, null, 2);
    }

    const btn = document.getElementById('btn-generate-jsonld');
    if (btn) btn.addEventListener('click', generateJsonLd);

    // ── Map preview ──────────────────────────────────────────────────
    const latEl = document.getElementById('business_geo_lat');
    const lngEl = document.getElementById('business_geo_lng');
    const wrap   = document.getElementById('map-preview-wrap');
    const iframe = document.getElementById('map-preview-iframe');

    function updateMap() {
        const lat = parseFloat(latEl.value);
        const lng = parseFloat(lngEl.value);
        if (!isNaN(lat) && !isNaN(lng) && Math.abs(lat) <= 90 && Math.abs(lng) <= 180) {
            iframe.src = 'https://maps.google.com/maps?q=' + lat + ',' + lng + '&z=15&output=embed';
            wrap.classList.remove('hidden');
        } else {
            wrap.classList.add('hidden');
        }
    }

    latEl.addEventListener('change', updateMap);
    lngEl.addEventListener('change', updateMap);
    latEl.addEventListener('input', updateMap);
    lngEl.addEventListener('input', updateMap);

    if (latEl.value && lngEl.value) updateMap();

    // ── Koordinat hızlı yöntemleri ──────────────────────────────────
    const statusEl = document.getElementById('coord_status');
    function setStatus(msg, kind) {
        if (!statusEl) return;
        statusEl.textContent = msg || '';
        statusEl.className = 'text-xs self-center ' +
            (kind === 'error' ? 'text-red-600' :
             kind === 'success' ? 'text-emerald-600' :
             kind === 'loading' ? 'text-gray-500' : 'text-gray-500');
    }

    function applyCoords(lat, lng) {
        latEl.value = parseFloat(lat).toFixed(6);
        lngEl.value = parseFloat(lng).toFixed(6);
        updateMap();
    }

    function parseCoordPair(input) {
        if (!input) return null;
        // Virgül, noktalı virgül veya boşlukla ayrılmış 2 sayı
        const cleaned = input.replace(/[°NSEW]/gi, '').trim();
        const parts = cleaned.split(/[,;\s]+/).filter(Boolean);
        if (parts.length < 2) return null;
        const lat = parseFloat(parts[0]);
        const lng = parseFloat(parts[1]);
        if (isNaN(lat) || isNaN(lng)) return null;
        if (Math.abs(lat) > 90 || Math.abs(lng) > 180) return null;
        return { lat: lat, lng: lng };
    }

    const pasteEl = document.getElementById('coord_paste');
    const btnApply = document.getElementById('btn-coord-apply');
    if (btnApply) {
        btnApply.addEventListener('click', function () {
            const parsed = parseCoordPair(pasteEl.value);
            if (!parsed) {
                setStatus('Geçerli koordinat bulunamadı. Örnek: 41.015137, 28.979530', 'error');
                return;
            }
            applyCoords(parsed.lat, parsed.lng);
            setStatus('Koordinat uygulandı.', 'success');
        });
    }
    // Yapıştırınca otomatik dene
    if (pasteEl) {
        pasteEl.addEventListener('paste', function () {
            setTimeout(function () {
                const parsed = parseCoordPair(pasteEl.value);
                if (parsed) {
                    applyCoords(parsed.lat, parsed.lng);
                    setStatus('Koordinat otomatik uygulandı.', 'success');
                }
            }, 50);
        });
    }

    // Konumumu kullan (browser geolocation)
    const btnGeo = document.getElementById('btn-geolocate');
    if (btnGeo) {
        btnGeo.addEventListener('click', function () {
            if (!navigator.geolocation) {
                setStatus('Tarayıcınız konum servisini desteklemiyor.', 'error');
                return;
            }
            setStatus('Konum alınıyor...', 'loading');
            navigator.geolocation.getCurrentPosition(
                function (pos) {
                    applyCoords(pos.coords.latitude, pos.coords.longitude);
                    setStatus('Mevcut konumunuz uygulandı.', 'success');
                },
                function (err) {
                    setStatus('Konum alınamadı: ' + (err.message || 'izin reddedildi'), 'error');
                },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        });
    }

    // Adresten bul (Nominatim — OpenStreetMap)
    const btnGeocode = document.getElementById('btn-geocode');
    if (btnGeocode) {
        btnGeocode.addEventListener('click', function () {
            const street  = (document.getElementById('business_address_street').value || '').trim();
            const city    = (document.getElementById('business_address_city').value || '').trim();
            const postal  = (document.getElementById('business_address_postal_code').value || '').trim();
            const country = (document.getElementById('business_address_country').value || '').trim();
            const parts = [street, postal, city, country].filter(Boolean);
            if (parts.length === 0) {
                setStatus('Önce yukarıdaki adres alanlarını doldurun.', 'error');
                return;
            }
            const query = parts.join(', ');
            setStatus('Adres aranıyor: ' + query, 'loading');
            const url = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(query);
            fetch(url, { headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.ok ? r.json() : Promise.reject(r.statusText); })
                .then(function (data) {
                    if (!data || !data.length) {
                        setStatus('Adres bulunamadı. Daha açık yazın veya yapıştır alanını kullanın.', 'error');
                        return;
                    }
                    applyCoords(data[0].lat, data[0].lon);
                    setStatus('Adres bulundu: ' + (data[0].display_name || '').slice(0, 80), 'success');
                })
                .catch(function (e) {
                    setStatus('Adres servisi hata verdi. Lütfen daha sonra tekrar deneyin.', 'error');
                });
        });
    }

    // ── Çalışma saatleri editörü ─────────────────────────────────────
    const HOURS_TEXTAREA = document.getElementById('business_opening_hours');
    const HOURS_HELP     = document.getElementById('hours-json-help');
    const HOURS_TOGGLE   = document.getElementById('btn-toggle-hours-json');
    const DAY_ORDER      = ['Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su'];

    function getRow(code) {
        return document.querySelector('.hours-row[data-day="' + code + '"]');
    }

    function setRow(code, active, open, close) {
        const row = getRow(code);
        if (!row) return;
        const cb     = row.querySelector('[data-role="day-active"]');
        const oEl    = row.querySelector('[data-role="open"]');
        const cEl    = row.querySelector('[data-role="close"]');
        const status = row.querySelector('[data-role="status"]');
        cb.checked  = !!active;
        oEl.value   = open  || '09:00';
        cEl.value   = close || '18:00';
        oEl.disabled = !active;
        cEl.disabled = !active;
        if (status) {
            status.textContent = active ? 'Açık' : 'Kapalı';
            status.className = 'text-xs ml-1 ' + (active ? 'text-emerald-600 font-medium' : 'text-gray-400');
        }
    }

    function expandDayRange(spec) {
        // "Mo-Fr" → ["Mo","Tu","We","Th","Fr"], "Sa,Su" → ["Sa","Su"], "Mo" → ["Mo"]
        const result = [];
        if (!spec) return result;
        const tokens = spec.split(',').map(function (s) { return s.trim(); });
        tokens.forEach(function (token) {
            if (token.indexOf('-') > -1) {
                const ends = token.split('-').map(function (s) { return s.trim(); });
                const i1 = DAY_ORDER.indexOf(ends[0]);
                const i2 = DAY_ORDER.indexOf(ends[1]);
                if (i1 > -1 && i2 > -1 && i2 >= i1) {
                    for (let i = i1; i <= i2; i++) result.push(DAY_ORDER[i]);
                }
            } else if (DAY_ORDER.indexOf(token) > -1) {
                result.push(token);
            }
        });
        return result;
    }

    function loadHoursFromJson() {
        // Tüm günleri kapalı başlat
        DAY_ORDER.forEach(function (d) { setRow(d, false, '09:00', '18:00'); });
        const raw = (HOURS_TEXTAREA.value || '').trim();
        if (!raw) return;
        try {
            const parsed = JSON.parse(raw);
            if (!Array.isArray(parsed)) return;
            parsed.forEach(function (item) {
                const days = expandDayRange(item.days || '');
                const hours = String(item.hours || '').split('-').map(function (s) { return s.trim(); });
                const open  = hours[0] || '09:00';
                const close = hours[1] || '18:00';
                days.forEach(function (d) { setRow(d, true, open, close); });
            });
        } catch (e) {
            // Geçersiz JSON — UI temiz kalsın, kullanıcı JSON modunda düzeltebilir
        }
    }

    function buildHoursJson() {
        // Ardışık aynı saatli günleri grupla: Mo,Tu,We,Th,Fr (09-18) → "Mo-Fr"
        const days = DAY_ORDER.map(function (code) {
            const row = getRow(code);
            const cb  = row.querySelector('[data-role="day-active"]');
            if (!cb.checked) return null;
            const o = row.querySelector('[data-role="open"]').value || '09:00';
            const c = row.querySelector('[data-role="close"]').value || '18:00';
            return { code: code, hours: o + '-' + c };
        });

        const groups = [];
        let current = null;
        for (let i = 0; i < days.length; i++) {
            const d = days[i];
            if (!d) { current = null; continue; }
            if (current && current.hours === d.hours) {
                current.end = d.code;
            } else {
                current = { start: d.code, end: d.code, hours: d.hours };
                groups.push(current);
            }
        }

        const out = groups.map(function (g) {
            return {
                days:  g.start === g.end ? g.start : g.start + '-' + g.end,
                hours: g.hours,
            };
        });
        HOURS_TEXTAREA.value = out.length ? JSON.stringify(out) : '';
    }

    function bindHoursRow(row) {
        const cb = row.querySelector('[data-role="day-active"]');
        const o  = row.querySelector('[data-role="open"]');
        const c  = row.querySelector('[data-role="close"]');
        const status = row.querySelector('[data-role="status"]');

        cb.addEventListener('change', function () {
            o.disabled = !cb.checked;
            c.disabled = !cb.checked;
            if (status) {
                status.textContent = cb.checked ? 'Açık' : 'Kapalı';
                status.className = 'text-xs ml-1 ' + (cb.checked ? 'text-emerald-600 font-medium' : 'text-gray-400');
            }
            buildHoursJson();
        });
        o.addEventListener('change', buildHoursJson);
        c.addEventListener('change', buildHoursJson);
    }

    document.querySelectorAll('.hours-row').forEach(bindHoursRow);

    // Preset'ler
    document.querySelectorAll('[data-hours-preset]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const preset = btn.getAttribute('data-hours-preset');
            DAY_ORDER.forEach(function (d) { setRow(d, false, '09:00', '18:00'); });
            if (preset === 'weekdays-9-18') {
                ['Mo', 'Tu', 'We', 'Th', 'Fr'].forEach(function (d) { setRow(d, true, '09:00', '18:00'); });
            } else if (preset === 'all-9-18') {
                ['Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'].forEach(function (d) { setRow(d, true, '09:00', '18:00'); });
            } else if (preset === '247') {
                DAY_ORDER.forEach(function (d) { setRow(d, true, '00:00', '23:59'); });
            }
            // 'clear' zaten yukarıda yapıldı
            buildHoursJson();
        });
    });

    // JSON gelişmiş mod toggle
    if (HOURS_TOGGLE) {
        HOURS_TOGGLE.addEventListener('click', function () {
            const isHidden = HOURS_TEXTAREA.classList.contains('hidden');
            if (isHidden) {
                HOURS_TEXTAREA.classList.remove('hidden');
                HOURS_HELP.classList.remove('hidden');
                HOURS_TOGGLE.innerHTML = '<i class="fas fa-times text-[10px]"></i> JSON\'u gizle';
            } else {
                HOURS_TEXTAREA.classList.add('hidden');
                HOURS_HELP.classList.add('hidden');
                HOURS_TOGGLE.innerHTML = '<i class="fas fa-code text-[10px]"></i> Gelişmiş (JSON)';
                // JSON'da elle değişiklik yaptıysa UI'ı yenile
                loadHoursFromJson();
            }
        });
        // JSON görünürken textarea'da değişiklik UI'ı re-yükle
        HOURS_TEXTAREA.addEventListener('change', function () {
            if (!HOURS_TEXTAREA.classList.contains('hidden')) loadHoursFromJson();
        });
    }

    // Sayfa yüklenince mevcut JSON'dan UI'ı doldur
    loadHoursFromJson();
})();
</script>
@endsection
