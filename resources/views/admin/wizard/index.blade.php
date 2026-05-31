@extends('admin.layouts.app')

@section('title', 'Site Kurulum Sihirbazı')
@section('page-title', '🏗️ Site Kurulum Sihirbazı')

@section('content')
<div x-data="siteWizard()" class="max-w-3xl mx-auto">

    {{-- ── Tamamlandı banner ───────────────────────────────────────────── --}}
    @if($setupCompleted)
    <div class="mb-6 bg-emerald-50 border border-emerald-200 rounded-xl p-5 flex items-center justify-between gap-4"
         x-data="{ resetting: false }">
        <div class="flex items-center gap-3 text-emerald-800">
            <i class="fas fa-check-circle text-2xl"></i>
            <div>
                <p class="font-semibold">Kurulum daha önce tamamlandı.</p>
                <p class="text-sm mt-0.5">Sihirbazı yeniden çalıştırarak firma bilgilerini güncelleyebilir veya yeni sayfalar ekleyebilirsin.</p>
            </div>
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
            <button type="button"
                    :disabled="resetting"
                    @click="resetting = true; fetch(@js(route('admin.wizard.reset', [], false)), {method:'POST', headers:{'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content, 'Accept':'application/json'}}).then(() => window.location.reload())"
                    class="bg-white border border-emerald-300 text-emerald-700 text-sm font-medium px-3 py-2 rounded-lg hover:bg-emerald-50 disabled:opacity-50 flex items-center gap-1.5">
                <i class="fas" :class="resetting ? 'fa-spinner fa-spin' : 'fa-rotate-right'"></i>
                <span x-text="resetting ? 'Sıfırlanıyor…' : 'Yeniden Başlat'"></span>
            </button>
            <a href="{{ route('admin.pages.index', [], false) }}"
               class="bg-emerald-600 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-emerald-700">
                Sayfalara Git
            </a>
        </div>
    </div>
    @endif

    {{-- ── İlerleme göstergesi ─────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center gap-0">
            @foreach([
                ['num' => 1, 'label' => 'Firma Bilgileri', 'icon' => 'fa-building'],
                ['num' => 2, 'label' => 'Sayfa Seçimi',    'icon' => 'fa-list-check'],
                ['num' => 3, 'label' => 'Oluşturuluyor',   'icon' => 'fa-wand-magic-sparkles'],
            ] as $s)
            <div class="flex items-center {{ !$loop->last ? 'flex-1' : '' }}">
                <div class="flex flex-col items-center">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-300"
                         :class="{
                             'bg-indigo-600 text-white': step === {{ $s['num'] }},
                             'bg-emerald-500 text-white': step > {{ $s['num'] }},
                             'bg-gray-200 text-gray-500': step < {{ $s['num'] }}
                         }">
                        <template x-if="step > {{ $s['num'] }}"><i class="fas fa-check text-xs"></i></template>
                        <template x-if="step <= {{ $s['num'] }}"><span>{{ $s['num'] }}</span></template>
                    </div>
                    <span class="text-[11px] mt-1 font-medium text-center leading-tight"
                          :class="step >= {{ $s['num'] }} ? 'text-gray-800' : 'text-gray-400'">
                        {{ $s['label'] }}
                    </span>
                </div>
                @if(!$loop->last)
                <div class="flex-1 h-0.5 mx-2 mt-[-14px] rounded transition-all duration-300"
                     :class="step > {{ $s['num'] }} ? 'bg-emerald-400' : 'bg-gray-200'"></div>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════
         ADIM 1 — Firma Bilgileri
    ══════════════════════════════════════════════════════════════════ --}}
    <div x-show="step === 1" x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-base font-semibold text-gray-900 mb-1 flex items-center gap-2">
                <i class="fas fa-building text-indigo-500"></i> Firma / Site Bilgileri
            </h2>
            <p class="text-xs text-gray-500 mb-5">Bu bilgiler, AI'ın sayfaları firmanıza özel içerikle doldurmasını sağlar.</p>

            <div class="space-y-4">
                {{-- Firma Adı + Sektör --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Firma / Site Adı <span class="text-red-500">*</span>
                        </label>
                        <input x-model="company_name" type="text" maxlength="200" placeholder="Örn: Denta Smile Kliniği"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Sektör / İşletme Tipi <span class="text-red-500">*</span>
                        </label>
                        <select x-model="sector"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                            <option value="">— Seçin —</option>
                            <option value="Diş Kliniği">Diş Kliniği</option>
                            <option value="Sağlık / Klinik / Hastane">Sağlık / Klinik / Hastane</option>
                            <option value="Restoran / Kafe">Restoran / Kafe</option>
                            <option value="Hukuk Bürosu / Avukatlık">Hukuk Bürosu / Avukatlık</option>
                            <option value="Güzellik / Kuaför / Spa">Güzellik / Kuaför / Spa</option>
                            <option value="Otel / Pansiyon / Konaklama">Otel / Pansiyon / Konaklama</option>
                            <option value="Turizm / Seyahat Acentesi">Turizm / Seyahat Acentesi</option>
                            <option value="E-Ticaret / Online Mağaza">E-Ticaret / Online Mağaza</option>
                            <option value="Gayrimenkul / Emlak">Gayrimenkul / Emlak</option>
                            <option value="Kurumsal / Şirket">Kurumsal / Şirket</option>
                            <option value="Eğitim / Kurs / Okul">Eğitim / Kurs / Okul</option>
                            <option value="Teknoloji / Yazılım">Teknoloji / Yazılım</option>
                            <option value="İnşaat / Yapı / Mimarlık">İnşaat / Yapı / Mimarlık</option>
                            <option value="Otomotiv / Araç Kiralama">Otomotiv / Araç Kiralama</option>
                            <option value="Diğer">Diğer</option>
                        </select>
                    </div>
                </div>

                {{-- Açıklama --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Firma Tanıtımı</label>
                    <textarea x-model="description" rows="3" maxlength="1000"
                              placeholder="Kısaca firmanızı tanıtın — AI bunu içeriklere yansıtır."
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                {{-- Hedef Kitle + Şehir --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Hedef Kitle</label>
                        <input x-model="target_audience" type="text" maxlength="300"
                               placeholder="Örn: 25-55 yaş arası aileler, Ankara"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Şehir / Bölge</label>
                        <input x-model="city" type="text" maxlength="100"
                               placeholder="Örn: İstanbul, Kadıköy"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                {{-- Telefon + E-posta --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Telefon
                            <span class="ml-1 text-[11px] text-gray-400">(CTA butonlarında kullanılır)</span>
                        </label>
                        <input x-model="phone" type="text" maxlength="30"
                               placeholder="0212 000 00 00"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">E-posta</label>
                        <input x-model="email" type="email" maxlength="200"
                               placeholder="info@firmaniz.com"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                {{-- CSS Framework --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Kullanılan CSS Çatısı
                        <span class="ml-1 text-[11px] text-gray-400">(ikon / bileşen adları için AI'a bilgi verir)</span>
                    </label>
                    <select x-model="css_framework"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                        <option value="Bootstrap 5">Bootstrap 5</option>
                        <option value="Bootstrap 4">Bootstrap 4</option>
                        <option value="Tailwind CSS">Tailwind CSS</option>
                        <option value="Foundation">Foundation</option>
                        <option value="Özel CSS">Özel CSS (framework yok)</option>
                    </select>
                </div>

                {{-- Hata --}}
                <div x-show="step1Error" x-cloak
                     class="text-xs text-red-600 flex items-center gap-1">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span x-text="step1Error"></span>
                </div>
            </div>

            <div class="flex justify-end mt-6">
                <button type="button" @click="saveAndNext()"
                        :disabled="saving || !company_name.trim() || !sector"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas" :class="saving ? 'fa-spinner fa-spin' : 'fa-arrow-right'"></i>
                    <span x-text="saving ? 'Kaydediliyor…' : 'Devam Et — Sayfa Seçimi'"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════
         ADIM 2 — Sayfa Seçimi
    ══════════════════════════════════════════════════════════════════ --}}
    <div x-show="step === 2" x-cloak x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">

            {{-- Başlık + araç çubuğu --}}
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h2 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-list-check text-indigo-500"></i> Hangi Sayfalar Oluşturulsun?
                    </h2>
                    <p class="text-xs text-gray-500 mt-1"
                       x-text="manualMode
                           ? 'Her satıra bir sayfa yazın. Alt sayfa için 2+ boşluk/tab ile girintileyin.'
                           : 'AI sektörünüze özel sayfalar önerdi. İstediğinizi seçin veya kaldırın.'">
                    </p>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    {{-- Manuel / AI toggle --}}
                    <button type="button" @click="manualMode = !manualMode"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 border text-xs font-medium rounded-lg transition-colors"
                            :class="manualMode
                                ? 'border-purple-300 text-purple-700 bg-purple-50 hover:bg-purple-100'
                                : 'border-gray-300 text-gray-600 bg-white hover:bg-gray-50'">
                        <i class="fas" :class="manualMode ? 'fa-robot' : 'fa-pencil'"></i>
                        <span x-text="manualMode ? 'AI Önerisi Kullan' : 'Manuel Giriş'"></span>
                    </button>
                    {{-- AI yeniden öner (sadece AI modunda) --}}
                    <button type="button" @click="suggestPages()"
                            x-show="!manualMode"
                            :disabled="suggesting"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-indigo-300 text-indigo-600 text-xs font-medium rounded-lg hover:bg-indigo-50 disabled:opacity-50">
                        <i class="fas" :class="suggesting ? 'fa-spinner fa-spin' : 'fa-rotate-right'"></i>
                        <span>Yeniden Öner</span>
                    </button>
                </div>
            </div>

            {{-- ── AI MODU ─────────────────────────────────────────────── --}}
            <div x-show="!manualMode">

                {{-- Yükleniyor --}}
                <div x-show="suggesting" x-cloak class="py-8 text-center text-gray-400 text-sm">
                    <i class="fas fa-spinner fa-spin text-2xl mb-3 block text-indigo-400"></i>
                    AI sektörünüze uygun sayfalar seçiyor…
                </div>

                {{-- Hata --}}
                <div x-show="suggestError && !suggesting" x-cloak
                     class="bg-red-50 border border-red-200 rounded-lg p-3 text-sm text-red-700 mb-4">
                    <div class="flex items-start gap-2">
                        <i class="fas fa-exclamation-triangle mt-0.5 flex-shrink-0"></i>
                        <div>
                            <span x-text="suggestError"></span>
                            <button type="button" @click="manualMode = true"
                                    class="ml-2 underline text-red-600 hover:text-red-800 font-medium">
                                Manuel giriş yap →
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Sayfa listesi --}}
                <div x-show="!suggesting && pages.length > 0" x-cloak class="space-y-2">
                    <template x-for="(page, i) in pages" :key="i">
                        <label class="flex items-start gap-3 p-3 rounded-lg border cursor-pointer transition-colors"
                               :class="page.selected
                                   ? 'bg-indigo-50 border-indigo-300'
                                   : 'bg-gray-50 border-gray-200 opacity-60'">
                            <input type="checkbox" x-model="page.selected"
                                   class="mt-0.5 h-4 w-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-sm font-medium text-gray-900" x-text="page.title"></span>
                                    <span class="text-xs font-mono text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded"
                                          x-text="page.slug ? '/'+page.slug : '/'"></span>
                                </div>
                                <p class="text-xs text-gray-500 mt-0.5" x-text="page.purpose"></p>
                            </div>
                        </label>
                    </template>

                    {{-- Manuel ekleme satırı --}}
                    <div class="border-t border-dashed border-gray-200 pt-3 mt-3">
                        <div class="flex items-center gap-2">
                            <input x-model="newPageTitle" type="text" maxlength="100"
                                   placeholder="+ Özel sayfa başlığı ekle (Örn: Ekibimiz)"
                                   @keydown.enter="addCustomPage()"
                                   class="flex-1 px-3 py-2 border border-dashed border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-solid bg-gray-50">
                            <button type="button" @click="addCustomPage()"
                                    :disabled="!newPageTitle.trim()"
                                    class="px-3 py-2 bg-gray-100 text-gray-700 text-xs font-medium rounded-lg hover:bg-gray-200 disabled:opacity-40">
                                <i class="fas fa-plus mr-1"></i> Ekle
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Seçim özeti --}}
                <div x-show="!suggesting && pages.length > 0" x-cloak
                     class="mt-4 flex items-center justify-between text-xs text-gray-500">
                    <span>
                        <span class="font-semibold text-gray-800" x-text="selectedCount()"></span> sayfa seçildi
                    </span>
                    <div class="flex gap-2">
                        <button type="button" @click="toggleAll(true)"
                                class="text-indigo-600 hover:underline">Tümünü Seç</button>
                        <span>·</span>
                        <button type="button" @click="toggleAll(false)"
                                class="text-gray-500 hover:underline">Tümünü Kaldır</button>
                    </div>
                </div>
            </div>

            {{-- ── MANUEL HIYERARŞI MODU ──────────────────────────────── --}}
            <div x-show="manualMode" x-cloak>
                <div class="mb-3 p-3 bg-purple-50 border border-purple-200 rounded-lg text-xs text-purple-700">
                    <p class="font-medium mb-1"><i class="fas fa-lightbulb mr-1"></i> Format:</p>
                    <pre class="font-mono leading-5 text-purple-600">Ana Sayfa
Hizmetler
  Saç Ekimi
  Sakal Ekimi
Hakkımızda
Blog
İletişim</pre>
                    <p class="mt-1.5 text-purple-500">Alt sayfalar da ayrı birer sayfa olarak oluşturulur.</p>
                </div>

                <textarea x-model="hierarchyText"
                          rows="10"
                          placeholder="Ana Sayfa&#10;Hizmetler&#10;  Hizmet 1&#10;  Hizmet 2&#10;Hakkımızda&#10;İletişim"
                          class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm font-mono focus:ring-2 focus:ring-purple-500 resize-none bg-gray-50"></textarea>

                <div class="flex items-center justify-between mt-3">
                    <span class="text-xs text-gray-400">
                        <span x-text="parseHierarchy(hierarchyText).length"></span> sayfa algılandı
                    </span>
                    <button type="button"
                            @click="applyHierarchy()"
                            :disabled="parseHierarchy(hierarchyText).length === 0"
                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 disabled:opacity-40">
                        <i class="fas fa-check"></i>
                        <span>Sayfaları Belirle</span>
                        <span x-show="parseHierarchy(hierarchyText).length > 0"
                              class="bg-purple-500 text-white text-xs font-bold px-1.5 py-0.5 rounded-full"
                              x-text="parseHierarchy(hierarchyText).length"></span>
                    </button>
                </div>

                {{-- Önizleme: belirlendikten sonra --}}
                <div x-show="manualApplied && pages.length > 0" x-cloak class="mt-4 space-y-1.5">
                    <p class="text-xs font-medium text-gray-600 mb-2">
                        <i class="fas fa-check-circle text-green-500 mr-1"></i>
                        <span x-text="pages.length"></span> sayfa belirlendi — hepsini seçili olarak ekledim:
                    </p>
                    <template x-for="(page, i) in pages" :key="i">
                        <div class="flex items-center gap-2 p-2 bg-gray-50 rounded-lg border border-gray-200">
                            <i class="fas fa-file text-gray-400 text-xs w-4 text-center"></i>
                            <span class="text-sm text-gray-800" x-text="page.title"></span>
                            <span class="text-xs font-mono text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded ml-auto"
                                  x-text="page.slug ? '/'+page.slug : '/'"></span>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Alt butonlar --}}
            <div class="flex items-center justify-between mt-6">
                <button type="button" @click="step = 1"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200">
                    <i class="fas fa-arrow-left"></i> Geri
                </button>
                <button type="button" @click="startGeneration()"
                        :disabled="selectedCount() === 0 || generating"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-purple-600 to-indigo-600 text-white text-sm font-medium rounded-lg hover:from-purple-700 hover:to-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm">
                    <i class="fas fa-wand-magic-sparkles"></i>
                    <span x-text="selectedCount() + ' Sayfayı Oluştur'"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════
         ADIM 3 — Oluşturuluyor / Tamamlandı
    ══════════════════════════════════════════════════════════════════ --}}
    <div x-show="step === 3" x-cloak x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">

            {{-- Başlık --}}
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                    <template x-if="!wizardDone">
                        <span><i class="fas fa-spinner fa-spin text-indigo-500"></i> Sayfalar Oluşturuluyor…</span>
                    </template>
                    <template x-if="wizardDone && errorCount() === 0">
                        <span><i class="fas fa-check-circle text-emerald-500"></i> Tüm Sayfalar Hazır!</span>
                    </template>
                    <template x-if="wizardDone && errorCount() > 0">
                        <span><i class="fas fa-exclamation-circle text-amber-500"></i> Kısmen Tamamlandı</span>
                    </template>
                </h2>
                <span class="text-sm text-gray-500">
                    <span class="font-semibold text-indigo-700" x-text="completedCount"></span>
                    / <span x-text="generatedPages.length"></span>
                </span>
            </div>

            {{-- İlerleme çubuğu --}}
            <div class="w-full bg-gray-100 rounded-full h-2 mb-5 overflow-hidden">
                <div class="h-2 rounded-full bg-gradient-to-r from-indigo-500 to-purple-500 transition-all duration-500"
                     :style="'width:' + (generatedPages.length > 0 ? Math.round((completedCount/generatedPages.length)*100) : 0) + '%'">
                </div>
            </div>

            {{-- Sayfa satırları --}}
            <div class="space-y-2">
                <template x-for="(pg, i) in generatedPages" :key="i">
                    <div class="flex items-center gap-3 p-3 rounded-lg border transition-colors"
                         :class="{
                             'bg-gray-50 border-gray-200': pg.status === 'pending',
                             'bg-indigo-50 border-indigo-200': pg.status === 'generating',
                             'bg-emerald-50 border-emerald-200': pg.status === 'done',
                             'bg-red-50 border-red-200': pg.status === 'error',
                         }">
                        {{-- Durum ikonu --}}
                        <div class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0 text-sm"
                             :class="{
                                 'bg-gray-200 text-gray-400': pg.status === 'pending',
                                 'bg-indigo-100 text-indigo-600': pg.status === 'generating',
                                 'bg-emerald-100 text-emerald-600': pg.status === 'done',
                                 'bg-red-100 text-red-600': pg.status === 'error',
                             }">
                            <template x-if="pg.status === 'pending'"><i class="fas fa-circle-dot text-xs"></i></template>
                            <template x-if="pg.status === 'generating'"><i class="fas fa-spinner fa-spin text-xs"></i></template>
                            <template x-if="pg.status === 'done'"><i class="fas fa-check text-xs"></i></template>
                            <template x-if="pg.status === 'error'"><i class="fas fa-times text-xs"></i></template>
                        </div>

                        {{-- Başlık --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate" x-text="pg.title"></p>
                            <p class="text-[11px] mt-0.5"
                               :class="pg.status === 'error' ? 'text-red-600' : 'text-gray-400'"
                               x-text="pg.status === 'generating' ? 'AI içerik üretiyor…'
                                      : pg.status === 'done' ? pg.block_count + ' blok oluşturuldu'
                                      : pg.status === 'error' ? (pg.error || 'Hata oluştu')
                                      : 'Bekliyor…'">
                            </p>
                        </div>

                        {{-- Düzenle linki --}}
                        <template x-if="pg.status === 'done' && pg.edit_url">
                            <a :href="pg.edit_url"
                               class="flex-shrink-0 text-xs text-indigo-600 hover:text-indigo-800 font-medium flex items-center gap-1 px-2 py-1 bg-white rounded border border-indigo-200 hover:bg-indigo-50">
                                <i class="fas fa-edit"></i> Düzenle
                            </a>
                        </template>
                    </div>
                </template>
            </div>

            {{-- Tamamlandı eylemleri --}}
            <div x-show="wizardDone" x-cloak class="mt-6 space-y-3">
                <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4 text-sm text-emerald-800">
                    <i class="fas fa-info-circle mr-1"></i>
                    Sayfalar <strong>taslak</strong> olarak kaydedildi. Her sayfanın içeriğini düzenleyip yayınlayabilirsiniz.
                </div>
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <button type="button" @click="markComplete()"
                            :disabled="completing"
                            class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 disabled:opacity-50">
                        <i class="fas" :class="completing ? 'fa-spinner fa-spin' : 'fa-flag-checkered'"></i>
                        <span x-text="completing ? 'Tamamlanıyor…' : 'Kurulumu Tamamla → Sayfalara Git'"></span>
                    </button>
                    <a href="{{ route('admin.pages.index', [], false) }}"
                       class="flex-shrink-0 inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200">
                        <i class="fas fa-list"></i> Tüm Sayfalara Git
                    </a>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function siteWizard() {
    return {
        // Step state
        step: 1,

        // Step 1 fields (pre-filled from server)
        company_name:    @js($companyName),
        sector:          @js($sector),
        description:     @js($description),
        target_audience: @js($targetAudience),
        city:            @js($city),
        phone:           @js($phone),
        email:           @js($email),
        css_framework:   @js($cssFramework),

        // Step 1 state
        saving:     false,
        step1Error: '',

        // Step 2 state
        suggesting:    false,
        pages:         [],  // [{title, slug, purpose, selected}]
        suggestError:  '',
        newPageTitle:  '',
        manualMode:    false,
        manualApplied: false,
        hierarchyText: '',

        // Step 3 state
        generating:     false,
        wizardDone:     false,
        generatedPages: [], // [{title, slug, purpose, status, block_count, edit_url, error}]
        completedCount: 0,
        completing:     false,

        // ─────────────────────────────────────────────────────────────

        /** Adım 1: firma bilgilerini kaydet, adım 2'ye geç */
        async saveAndNext() {
            if (!this.company_name.trim() || !this.sector) return;
            this.saving     = true;
            this.step1Error = '';
            try {
                const r = await this._post(@js(route('admin.wizard.save-company', [], false)), {
                    company_name:    this.company_name,
                    sector:          this.sector,
                    description:     this.description,
                    target_audience: this.target_audience,
                    city:            this.city,
                    phone:           this.phone,
                    email:           this.email,
                    css_framework:   this.css_framework,
                });
                const data = await r.json().catch(() => ({ ok: false, message: 'Geçersiz yanıt' }));
                if (!data.ok) { this.step1Error = data.message || 'Kayıt başarısız.'; return; }

                this.step = 2;
                if (this.pages.length === 0) {
                    await this.suggestPages();
                }
            } catch (e) {
                this.step1Error = e.message || 'Ağ hatası.';
            } finally {
                this.saving = false;
            }
        },

        /** Adım 2: AI'dan sayfa önerisi al */
        async suggestPages() {
            this.suggesting   = true;
            this.suggestError = '';
            try {
                const r = await this._post(@js(route('admin.wizard.suggest-pages', [], false)), {
                    company_name:    this.company_name,
                    sector:          this.sector,
                    description:     this.description,
                    target_audience: this.target_audience,
                });
                const data = await r.json().catch(() => ({ ok: false, message: 'Geçersiz yanıt' }));
                if (!data.ok) { this.suggestError = data.message || 'Öneri alınamadı.'; return; }

                this.pages = (data.pages || []).map(p => ({ ...p, selected: true }));
            } catch (e) {
                this.suggestError = e.message || 'Ağ hatası.';
            } finally {
                this.suggesting = false;
            }
        },

        /**
         * Hiyerarşi textarea'sını parse eder → [{title, slug, purpose}] döndürür.
         * Girinti (2+ boşluk veya tab) = alt sayfa, ama flat liste olarak işlenir.
         * Pure function — reaktif hesaplama için x-text içinde çağrılabilir.
         */
        parseHierarchy(text) {
            if (!text || !text.trim()) return [];
            const toSlug = (s) => s.toLowerCase()
                .replace(/ğ/g,'g').replace(/ü/g,'u').replace(/ş/g,'s')
                .replace(/ı/g,'i').replace(/ö/g,'o').replace(/ç/g,'c')
                .replace(/[^a-z0-9]+/g,'-').replace(/^-+|-+$/g,'');

            const pages = [];
            const lines = text.split('\n');
            for (const line of lines) {
                const title = line.replace(/^\s+/, '').replace(/^[-*•]\s*/, '').trim();
                if (!title) continue;
                const isIndented = /^[\s\t]{2,}/.test(line); // alt sayfa ipucu (purpose için)
                pages.push({
                    title,
                    slug: toSlug(title),
                    purpose: isIndented ? `${title} sayfası içeriği` : '',
                    selected: true,
                });
            }
            return pages;
        },

        /** Hiyerarşi textarea'sından sayfaları pages[] dizisine uygular */
        applyHierarchy() {
            const parsed = this.parseHierarchy(this.hierarchyText);
            if (!parsed.length) return;
            this.pages         = parsed;
            this.manualApplied = true;
        },

        /** Kullanıcı özel sayfa ekler */
        addCustomPage() {
            const t = this.newPageTitle.trim();
            if (!t) return;
            const slug = t.toLowerCase()
                .replace(/ğ/g,'g').replace(/ü/g,'u').replace(/ş/g,'s')
                .replace(/ı/g,'i').replace(/ö/g,'o').replace(/ç/g,'c')
                .replace(/[^a-z0-9]+/g,'-').replace(/^-+|-+$/g,'');
            this.pages.push({ title: t, slug, purpose: '', selected: true });
            this.newPageTitle = '';
        },

        toggleAll(val) {
            this.pages.forEach(p => p.selected = val);
        },

        selectedCount() {
            return this.pages.filter(p => p.selected).length;
        },

        errorCount() {
            return this.generatedPages.filter(p => p.status === 'error').length;
        },

        // ─────────────────────────────────────────────────────────────

        /** Adım 3: seçili sayfaları sırayla üret */
        async startGeneration() {
            const selected = this.pages.filter(p => p.selected);
            if (!selected.length) return;

            this.step       = 3;
            this.wizardDone = false;
            this.completedCount = 0;
            this.generatedPages = selected.map(p => ({
                ...p,
                status:      'pending',
                block_count: 0,
                edit_url:    null,
                error:       null,
            }));

            const companyContext = {
                company_name:    this.company_name,
                sector:          this.sector,
                description:     this.description,
                target_audience: this.target_audience,
                city:            this.city,
                phone:           this.phone,
                email:           this.email,
                css_framework:   this.css_framework,
            };
            const plannedPages = selected.map(p => ({ title: p.title, slug: p.slug }));

            for (let i = 0; i < this.generatedPages.length; i++) {
                this.generatedPages[i].status = 'generating';
                try {
                    const r = await this._post(@js(route('admin.wizard.generate-page', [], false)), {
                        title:           this.generatedPages[i].title,
                        slug:            this.generatedPages[i].slug,
                        purpose:         this.generatedPages[i].purpose,
                        company_context: companyContext,
                        planned_pages:   plannedPages,
                    });
                    const data = await r.json().catch(() => ({ ok: false, message: 'Geçersiz yanıt' }));
                    if (data.ok) {
                        this.generatedPages[i].status      = 'done';
                        this.generatedPages[i].edit_url    = data.edit_url;
                        this.generatedPages[i].block_count = data.block_count || 0;
                        this.completedCount++;
                    } else {
                        this.generatedPages[i].status = 'error';
                        this.generatedPages[i].error  = data.message || 'Bilinmeyen hata';
                        this.completedCount++; // hatalı da tamamlandı sayılır (ilerleme için)
                    }
                } catch (e) {
                    this.generatedPages[i].status = 'error';
                    this.generatedPages[i].error  = e.message || 'Ağ hatası';
                    this.completedCount++;
                }
            }
            this.wizardDone = true;
        },

        /** Kurulumu tamamla → flag set et → sayfalara yönlendir */
        async markComplete() {
            this.completing = true;
            try {
                await this._post(@js(route('admin.wizard.complete', [], false)), {});
            } catch {}
            window.location.href = @js(route('admin.pages.index', [], false));
        },

        // ─────────────────────────────────────────────────────────────

        /** Ortak fetch helper (JSON POST + CSRF) */
        async _post(url, body) {
            return fetch(url, {
                method:      'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept':       'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify(body),
            });
        },
    };
}
</script>
@endpush
