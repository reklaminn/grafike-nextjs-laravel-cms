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
         ADIM 2 — Sayfa Seçimi (Unified textarea + live preview)
    ══════════════════════════════════════════════════════════════════ --}}
    <div x-show="step === 2" x-cloak x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">

            {{-- Başlık --}}
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h2 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-list-check text-indigo-500"></i> Sayfaları Belirle
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">
                        Her satıra bir sayfa yazın. Alt sayfa için 2+ boşluk ekleyin.
                        AI butonu otomatik doldurur, üzerine yazabilirsiniz.
                    </p>
                </div>
                {{-- AI'dan Doldur --}}
                <button type="button" @click="suggestAndFill()"
                        :disabled="suggesting"
                        class="flex-shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 border border-indigo-300 text-indigo-600 text-xs font-medium rounded-lg hover:bg-indigo-50 disabled:opacity-50 transition-colors">
                    <i class="fas" :class="suggesting ? 'fa-spinner fa-spin' : 'fa-wand-magic-sparkles'"></i>
                    <span x-text="suggesting ? 'Yükleniyor…' : 'AI\'dan Öner'"></span>
                </button>
            </div>

            {{-- AI hata --}}
            <div x-show="suggestError && !suggesting" x-cloak
                 class="mb-3 bg-amber-50 border border-amber-200 rounded-lg p-3 text-xs text-amber-700 flex items-center gap-2">
                <i class="fas fa-exclamation-triangle flex-shrink-0"></i>
                <span x-text="suggestError"></span>
                <span class="text-amber-500">— Elle yazabilirsiniz.</span>
            </div>

            {{-- ── TEXTAREA ────────────────────────────────────────────── --}}
            <div class="relative">
                <textarea
                    x-model="hierarchyText"
                    @input="syncFromText()"
                    rows="9"
                    spellcheck="false"
                    placeholder="Ana Sayfa&#10;Hizmetler&#10;  Hizmet 1&#10;  Hizmet 2&#10;Hakkımızda&#10;Galeri&#10;Blog&#10;İletişim"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm font-mono focus:ring-2 focus:ring-indigo-500 resize-none bg-gray-50 leading-6"
                ></textarea>
                {{-- Format ipucu --}}
                <div class="absolute top-2 right-2 text-[10px] text-gray-300 pointer-events-none select-none leading-4 text-right">
                    <span>her satır = sayfa</span><br>
                    <span class="text-gray-400">2 boşluk = alt sayfa</span><br>
                    <span class="text-green-400">3 boşluk = yazı</span>
                </div>
            </div>

            {{-- ── CANLI ÖNİZLEME ─────────────────────────────────────── --}}
            <div x-show="pages.length > 0" x-cloak class="mt-4">

                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-medium text-gray-500">
                        <span x-text="pages.length"></span> sayfa algılandı — oluşturulacakları seçin:
                    </span>
                    <div class="flex gap-3 text-xs">
                        <button type="button" @click="toggleAll(true)"
                                class="text-indigo-600 hover:underline">Tümünü Seç</button>
                        <button type="button" @click="toggleAll(false)"
                                class="text-gray-400 hover:underline">Tümünü Kaldır</button>
                    </div>
                </div>

                <div class="space-y-1 max-h-72 overflow-y-auto pr-0.5">
                    <template x-for="(page, i) in pages" :key="i">
                        <label class="flex items-center gap-2.5 px-3 py-2 rounded-lg border cursor-pointer transition-colors"
                               :style="'margin-left: ' + (page.indent * 18) + 'px'"
                               :class="page.selected
                                   ? 'bg-indigo-50 border-indigo-200'
                                   : 'bg-gray-50 border-gray-200 opacity-50'">
                            <input type="checkbox" x-model="page.selected"
                                   class="h-3.5 w-3.5 text-indigo-600 rounded border-gray-300 flex-shrink-0">
                            {{-- İndent ikonu --}}
                            <template x-if="page.indent > 0">
                                <i class="fas fa-corner-down-right text-gray-300 text-xs flex-shrink-0"></i>
                            </template>
                            <template x-if="page.indent === 0">
                                <i class="fas fa-file-alt text-indigo-400 text-xs flex-shrink-0"></i>
                            </template>
                            <span class="text-sm font-medium text-gray-800 flex-1 truncate" x-text="page.title"></span>
                            <template x-if="page.type === 'article'">
                                <span class="text-[10px] font-medium px-1.5 py-0.5 rounded bg-green-100 text-green-700 flex-shrink-0">yazı</span>
                            </template>
                            <span class="text-[11px] font-mono text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded flex-shrink-0"
                                  x-text="page.slug ? '/'+page.slug : '/'"></span>
                        </label>
                    </template>
                </div>

                {{-- Seçim özeti --}}
                <div class="mt-3 flex items-center gap-2 text-xs text-gray-500">
                    <span class="font-semibold text-gray-800" x-text="selectedCount()"></span>
                    <span>/ <span x-text="pages.length"></span> sayfa seçildi</span>
                    <span x-show="selectedCount() === 0" class="text-amber-600 font-medium">
                        — en az 1 sayfa seçin
                    </span>
                </div>
            </div>

            {{-- Boş durum --}}
            <div x-show="!suggesting && pages.length === 0 && !hierarchyText.trim()" x-cloak
                 class="mt-4 py-6 text-center text-gray-400 text-sm border-2 border-dashed border-gray-200 rounded-lg">
                <i class="fas fa-file-circle-plus text-2xl mb-2 block text-gray-300"></i>
                <p>Yukarıya sayfa isimlerini yazın <br>veya <button type="button" @click="suggestAndFill()" :disabled="suggesting" class="text-indigo-500 underline hover:text-indigo-700">AI'dan öneri alın</button></p>
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
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <template x-if="pg.indent > 0">
                                    <span class="text-[10px] text-gray-400 font-mono">↳</span>
                                </template>
                                <p class="text-sm font-medium text-gray-900 truncate" x-text="pg.title"></p>
                                <template x-if="pg.type === 'article'">
                                    <span class="text-[10px] font-medium px-1.5 py-0.5 rounded bg-green-100 text-green-700">yazı</span>
                                </template>
                            </div>
                            <p class="text-[11px] mt-0.5"
                               :class="pg.status === 'error' ? 'text-red-600' : 'text-gray-400'"
                               x-text="pg.status === 'generating' ? 'Taslak oluşturuluyor…'
                                      : pg.status === 'done' ? 'Taslak oluşturuldu ✓'
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
        suggestError:  '',
        hierarchyText: '',
        pagePurposes:  {},   // title.toLowerCase() → purpose (AI'dan gelir)
        pages:         [],   // [{title, slug, purpose, selected, indent}]

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
            } catch (e) {
                this.step1Error = e.message || 'Ağ hatası.';
            } finally {
                this.saving = false;
            }
        },

        /**
         * AI'dan sayfa önerisi al → textarea'ya yaz → canlı önizleme güncelle.
         * Purpose verisi pagePurposes map'inde saklanır; parseHierarchy() okur.
         */
        async suggestAndFill() {
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
                if (!data.ok) { this.suggestError = data.message || 'AI önerisi alınamadı.'; return; }

                // Purpose'leri map'e kaydet; textarea'ya sadece başlıkları yaz
                this.pagePurposes = {};
                const lines = (data.pages || []).map(p => {
                    if (p.title) this.pagePurposes[p.title.toLowerCase()] = p.purpose || '';
                    return p.title || '';
                }).filter(Boolean);
                this.hierarchyText = lines.join('\n');
                this.syncFromText();  // canlı önizlemeyi güncelle
            } catch (e) {
                this.suggestError = e.message || 'Ağ hatası.';
            } finally {
                this.suggesting = false;
            }
        },

        /**
         * Textarea @input → pages[] güncelle.
         * Daha önce kullanıcının uncheck ettiği sayfalar korunur.
         */
        syncFromText() {
            const prevDeselected = new Set(
                this.pages.filter(p => !p.selected).map(p => p.title.toLowerCase())
            );
            this.pages = this._parseLines(this.hierarchyText, prevDeselected);
        },

        /**
         * Satırları parse eder → [{title, slug, purpose, selected, indent}]
         * indent: 0 = ana sayfa, 1+ = alt sayfa (görsel girinti için)
         */
        _parseLines(text, deselected = new Set()) {
            if (!text || !text.trim()) return [];
            const toSlug = (s) => s.toLowerCase()
                .replace(/ğ/g,'g').replace(/ü/g,'u').replace(/ş/g,'s')
                .replace(/ı/g,'i').replace(/ö/g,'o').replace(/ç/g,'c')
                .replace(/[^a-z0-9]+/g,'-').replace(/^-+|-+$/g,'') || 'sayfa';

            const pages = [];
            for (const line of text.split('\n')) {
                const indentMatch = line.match(/^([\s\t]*)/);
                const rawIndent   = (indentMatch?.[1] || '').replace(/\t/g, '   ').length;

                // 3 boşluk = yazı (article), 2 boşluk = alt sayfa (page)
                // indent seviyesi: her 2 boşluk = 1 seviye (yazılar için de aynı)
                const isArticle = rawIndent > 0 && (rawIndent % 3 === 0 || rawIndent === 3);
                const indent    = isArticle
                    ? Math.floor(rawIndent / 3)
                    : Math.floor(rawIndent / 2);

                const title = line.replace(/^[\s\t]+/, '').replace(/^[-*•]\s*/, '').trim();
                if (!title) continue;

                const titleLower = title.toLowerCase();
                pages.push({
                    title,
                    indent,
                    type:     isArticle ? 'article' : 'page',
                    slug:     title === 'Ana Sayfa' ? '' : toSlug(title),
                    purpose:  this.pagePurposes[titleLower] || '',
                    selected: !deselected.has(titleLower),
                });
            }
            return pages;
        },

        toggleAll(val) {
            this.pages = this.pages.map(p => ({ ...p, selected: val }));
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

            // parentIds[indent] = son oluşturulan o seviyedeki sayfa ID'si
            // Örn: indent=0 Hizmetler oluşunca parentIds[0]=42
            //      indent=1 Saç Ekimi → parent_id=42
            const parentIds = {};

            for (let i = 0; i < this.generatedPages.length; i++) {
                const pg = this.generatedPages[i];
                this.generatedPages[i].status = 'generating';

                // Bir üst indent seviyesindeki son sayfa bu sayfanın parent'ı
                const parentId = (pg.indent > 0) ? (parentIds[pg.indent - 1] ?? null) : null;

                try {
                    const url = pg.type === 'article'
                        ? @js(route('admin.wizard.generate-article', [], false))
                        : @js(route('admin.wizard.generate-page', [], false));

                    const r = await this._post(url, {
                        title:       pg.title,
                        slug:        pg.slug,
                        sort_order:  i,
                        parent_id:   parentId,   // article için page_id olarak kullanılır
                        language_id: null,
                    });
                    const data = await r.json().catch(() => ({ ok: false, message: 'Geçersiz yanıt' }));
                    if (data.ok) {
                        this.generatedPages[i].status   = 'done';
                        this.generatedPages[i].edit_url = data.edit_url;
                        this.completedCount++;

                        // Bu sayfanın ID'sini indent seviyesine kaydet
                        // Daha derin seviyeleri temizle (yeni dal başladığında)
                        parentIds[pg.indent] = data.page_id;
                        Object.keys(parentIds).forEach(lvl => {
                            if (parseInt(lvl) > pg.indent) delete parentIds[lvl];
                        });
                    } else {
                        this.generatedPages[i].status = 'error';
                        this.generatedPages[i].error  = data.message || 'Bilinmeyen hata';
                        this.completedCount++;
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
