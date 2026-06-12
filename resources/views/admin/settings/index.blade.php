@extends('admin.layouts.app')

@section('title', 'Ayarlar')
@section('page-title', 'Site Ayarları')

@section('content')
    <form method="POST" action="{{ route('admin.settings.update', [], false) }}">
        @csrf
        @method('PUT')

        <div class="space-y-6">
            <!-- General Settings -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-base font-semibold text-gray-800 mb-4">
                    <i class="fas fa-globe mr-2 text-indigo-500"></i>Genel Ayarlar
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Site Başlığı</label>
                        <input type="text" name="settings[site.title]" value="{{ $settings['site.title'] ?? '' }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Firma Adı</label>
                        <input type="text" name="settings[site.company_name]" value="{{ $settings['site.company_name'] ?? '' }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Site Açıklaması</label>
                        <textarea name="settings[site.description]" rows="2"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        >{{ $settings['site.description'] ?? '' }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Sektör / İşletme Tipi
                            <span class="ml-1 text-[11px] text-gray-400">(AI içerik üretiminde kullanılır)</span>
                        </label>
                        <input type="text" name="settings[site.sector]"
                               value="{{ $settings['site.sector'] ?? '' }}"
                               placeholder="Örn: Diş Kliniği, Hukuk Bürosu, Otel"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Hedef Kitle
                            <span class="ml-1 text-[11px] text-gray-400">(AI içerik üretiminde kullanılır)</span>
                        </label>
                        <input type="text" name="settings[site.target_audience]"
                               value="{{ $settings['site.target_audience'] ?? '' }}"
                               placeholder="Örn: 30-55 yaş arası aileler, İstanbul"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            CSS Çatısı / Framework
                            <span class="ml-1 text-[11px] text-gray-400">(AI'ın ikon/bileşen adlarını doğru yazması için)</span>
                        </label>
                        <select name="settings[site.css_framework]"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            @foreach(['Bootstrap 5','Bootstrap 4','Tailwind CSS','Foundation','Özel CSS'] as $fw)
                            <option value="{{ $fw }}" {{ ($settings['site.css_framework'] ?? 'Bootstrap 5') === $fw ? 'selected' : '' }}>{{ $fw }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Contact Settings -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-base font-semibold text-gray-800 mb-4">
                    <i class="fas fa-phone-alt mr-2 text-green-500"></i>İletişim Bilgileri
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Adres</label>
                        <textarea name="settings[contact.address]" rows="2"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        >{{ $settings['contact.address'] ?? '' }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Telefon</label>
                        <input type="text" name="settings[contact.phone]" value="{{ $settings['contact.phone'] ?? '' }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fax</label>
                        <input type="text" name="settings[contact.fax]" value="{{ $settings['contact.fax'] ?? '' }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">E-posta</label>
                        <input type="email" name="settings[contact.email]" value="{{ $settings['contact.email'] ?? '' }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                </div>
            </div>

            <!-- Social Media -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-base font-semibold text-gray-800 mb-4">
                    <i class="fas fa-share-alt mr-2 text-purple-500"></i>Sosyal Medya
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach(['facebook', 'instagram', 'twitter', 'youtube', 'linkedin', 'tiktok'] as $social)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fab fa-{{ $social }} mr-1"></i>{{ ucfirst($social) }}
                            </label>
                            <input type="url" name="settings[social.{{ $social }}]"
                                   value="{{ $settings['social.' . $social] ?? '' }}"
                                   placeholder="https://..."
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Analytics -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-base font-semibold text-gray-800 mb-4">
                    <i class="fas fa-chart-line mr-2 text-orange-500"></i>Analitik & Takip
                </h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Google Analytics ID</label>
                        <input type="text" name="settings[analytics.google_id]"
                               value="{{ $settings['analytics.google_id'] ?? '' }}"
                               placeholder="G-XXXXXXXXXX"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Header Ek Kodlar (head tagı içine)</label>
                        <textarea name="settings[analytics.head_code]" rows="4"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        >{{ $settings['analytics.head_code'] ?? '' }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Save Button -->
            <div class="flex justify-end">
                <button type="submit"
                        class="px-8 py-3 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors shadow-sm">
                    <i class="fas fa-save mr-2"></i> Tüm Ayarları Kaydet
                </button>
            </div>
        </div>
    </form>

    {{-- ── E-posta Şablonu Test ──────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mt-6">
        <h3 class="text-base font-semibold text-gray-800 mb-1">
            <i class="fas fa-envelope-open-text mr-2 text-indigo-500"></i>E-posta Şablonu Önizleme
        </h3>
        <p class="text-xs text-gray-500 mb-4">
            Üyelerinize gönderilen şifre sıfırlama e-postasının marka özelleştirmeli halini test adresinize gönderin.
        </p>
        <form method="POST" action="{{ route('admin.settings.test-mail', [], false) }}" class="flex items-end gap-3">
            @csrf
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Test E-posta Adresi</label>
                <input type="email" name="test_email"
                       value="{{ auth()->user()->email ?? '' }}"
                       placeholder="ornek@email.com"
                       required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>
            <button type="submit"
                    class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors whitespace-nowrap">
                <i class="fas fa-paper-plane mr-1"></i> Test Gönder
            </button>
        </form>
    </div>
@endsection
