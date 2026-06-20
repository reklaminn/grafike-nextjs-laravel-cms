<div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
    <h3 class="text-base font-semibold text-gray-900">HTML Template</h3>
    <div class="mt-4">
        <p class="mb-3 text-xs leading-relaxed text-gray-500">
            Ham HTML yapıştır → <strong class="text-indigo-600">Şablona Dönüştür</strong> ile placeholder'lı şablona + şema alanlarına çevir.
            Hazır <strong>menü/sistem alanlarını</strong> imlece ekleyebilir, <strong>tekrar</strong> ve <strong>görsel</strong> alanlarını otomatik bulabilirsin.
        </p>

        {{-- Araç çubuğu — 2 grup: (1) imlece ekle  (2) otomatik HTML→şema --}}
        <div class="mb-3 flex flex-col gap-3 rounded-lg border border-gray-100 bg-gray-50/70 p-2.5 xl:flex-row xl:flex-wrap xl:items-start xl:gap-4">

            {{-- ── Grup 1: İmlece Ekle ──────────────────────────────────────── --}}
            <div class="min-w-0">
                <div class="mb-1.5 flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wider text-gray-400">
                    <i class="fas fa-i-cursor text-[9px]"></i> İmlece Ekle
                </div>
                <div class="flex flex-wrap items-center gap-1.5">
                    {{-- Menü placeholder --}}
                    <select id="menu_placeholder_select" title="Bir menü seç; sonra HTML veya Items ile imlece ekle" class="min-w-36 rounded-lg border border-gray-300 px-2 py-1.5 text-xs focus:ring-2 focus:ring-indigo-500">
                        <option value="">Menü seç…</option>
                        @foreach($menuPlaceholders as $placeholder)
                            <option value="{{ $placeholder['html_token'] }}" data-items-token="{{ $placeholder['items_token'] }}">
                                {{ $placeholder['label'] }}
                            </option>
                        @endforeach
                    </select>
                    <button type="button" id="insert_menu_html"
                            title="Seçili menünün hazır HTML çıktısını imlece ekle"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-sky-50 px-2.5 py-1.5 text-xs font-medium text-sky-700 hover:bg-sky-100">
                        <i class="fas fa-bars"></i> HTML
                    </button>
                    <button type="button" id="insert_menu_items"
                            title="Seçili menünün item-tekrar placeholder'ını ekle (kendi HTML'ini sararsın)"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-sky-50 px-2.5 py-1.5 text-xs font-medium text-sky-700 hover:bg-sky-100">
                        <i class="fas fa-list"></i> Items
                    </button>
                    <button type="button" id="reload_menu_placeholders"
                            title="Menü listesini yeniden yükle"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-gray-50 px-2 py-1.5 text-xs font-medium text-gray-500 hover:bg-gray-100">
                        <i class="fas fa-rotate"></i>
                    </button>

                    <span class="text-gray-200">|</span>

                    {{-- Sistem placeholder — role göre iki grup: "Genel" herkese,
                         "Gelişmiş" yalnızca superadmin'e (müşteride listeyi sadeleştirir) --}}
                    @php
                        $sysAll   = collect($systemPlaceholders ?? [])->filter(fn ($p) => ($p['audience'] ?? 'all') === 'all');
                        $sysAdmin = collect($systemPlaceholders ?? [])->filter(fn ($p) => ($p['audience'] ?? 'all') === 'admin');
                    @endphp
                    <select id="system_placeholder_select" title="Site/iletişim alanı (logo, telefon, e-posta…) seç" class="min-w-36 rounded-lg border border-gray-300 px-2 py-1.5 text-xs focus:ring-2 focus:ring-indigo-500">
                        <option value="">Sistem alanı seç</option>
                        <optgroup label="Genel (içerik & iletişim)">
                            @foreach($sysAll as $ph)
                                <option value="{{ $ph['token'] }}" data-source="{{ $ph['source'] }}">{{ $ph['label'] }}</option>
                            @endforeach
                        </optgroup>
                        @if(($isSuperAdmin ?? false) && $sysAdmin->isNotEmpty())
                            <optgroup label="Gelişmiş / Sistem (sadece yönetici)">
                                @foreach($sysAdmin as $ph)
                                    <option value="{{ $ph['token'] }}" data-source="{{ $ph['source'] }}">{{ $ph['label'] }}</option>
                                @endforeach
                            </optgroup>
                        @endif
                    </select>
                    <button type="button" id="insert_system_placeholder"
                            title="Seçili sistem alanını imlece ekle"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 px-2.5 py-1.5 text-xs font-medium text-emerald-700 hover:bg-emerald-100">
                        <i class="fas fa-plus"></i> Ekle
                    </button>
                </div>
            </div>

            {{-- dikey ayraç (geniş ekran) --}}
            <div class="hidden w-px self-stretch bg-gray-200 xl:block"></div>

            {{-- ── Grup 2: Otomatik (HTML'i analiz et → şema) ────────────────── --}}
            <div class="min-w-0">
                <div class="mb-1.5 flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wider text-gray-400">
                    <i class="fas fa-wand-magic-sparkles text-[9px]"></i> Otomatik · HTML → Şema
                </div>
                <div class="flex flex-wrap items-center gap-1.5">
                    {{-- Birincil: ham HTML → şablon --}}
                    <button type="button" id="generate_from_template"
                            title="Ham HTML'i placeholder'lı şablona ÇEVİR ve şema alanlarını otomatik üret"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700">
                        <i class="fas fa-wand-magic-sparkles"></i> Şablona Dönüştür
                    </button>
                    <select id="generate_mode_select" title="Dönüştürürken: mevcut şemayla birleştir mi, sıfırdan mı üret" class="rounded-lg border border-gray-300 px-2 py-1.5 text-xs focus:ring-2 focus:ring-indigo-500">
                        <option value="merge">↪ Şema ile birleştir</option>
                        <option value="replace">⟳ Şemayı yenile</option>
                    </select>

                    <span class="text-gray-200">|</span>

                    <button type="button" id="find_repeat_candidates"
                            title="Tekrarlayan blokları bul → tek tıkla repeater (çoğaltılabilir) alana çevir"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-amber-50 px-2.5 py-1.5 text-xs font-medium text-amber-700 hover:bg-amber-100">
                        <i class="fas fa-layer-group"></i> Repeat Alan Bul
                    </button>
                    <button type="button" id="find_asset_fields"
                            title="Şemaya bağlı olmayan sabit görselleri bul → düzenlenebilir görsel alanına çevir"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-sky-50 px-2.5 py-1.5 text-xs font-medium text-sky-700 hover:bg-sky-100">
                        <i class="fas fa-image"></i> Eksik Görsel
                    </button>
                </div>
            </div>
        </div>

        {{-- CodeMirror wrapper --}}
        <div id="cm_html_wrapper" class="relative">
            <textarea id="html_template_input" name="html_template" rows="18"
                      class="w-full rounded-lg border border-gray-300 px-3 py-2 font-mono text-xs focus:border-transparent focus:ring-2 focus:ring-indigo-500">{{ old('html_template', $sectionTemplate->html_template) }}</textarea>
        </div>
        <p class="mt-1 text-xs text-gray-500">Ham HTML yapıştırabilir veya mevcut placeholder'lı template kullanabilirsin.</p>

        {{-- HTML ↔ Schema diff --}}
        <div id="html_schema_diff" class="mt-3 hidden rounded-lg border border-gray-200 bg-gray-50 px-3 py-3 text-xs">
            <div class="flex flex-wrap items-center gap-2">
                <span class="font-semibold text-gray-700">HTML / Schema Kontrolü</span>
                <span id="diff_ok_badge" class="hidden rounded-full bg-green-100 px-2 py-0.5 font-medium text-green-700">Uyumlu</span>
            </div>
            <div class="mt-2 grid grid-cols-1 gap-3 md:grid-cols-2">
                <div id="diff_missing_schema_wrapper" class="hidden rounded-lg border border-red-200 bg-red-50 p-2 text-red-800">
                    <div class="font-medium">HTML'de var, schema'da yok</div>
                    <div id="diff_missing_schema" class="mt-1 flex flex-wrap gap-1"></div>
                </div>
                <div id="diff_unused_schema_wrapper" class="hidden rounded-lg border border-gray-200 bg-white p-2 text-gray-600">
                    <div class="font-medium">Schema'da var, HTML'de yok</div>
                    <div id="diff_unused_schema" class="mt-1 flex flex-wrap gap-1"></div>
                </div>
            </div>
        </div>

        {{-- Repeat candidate panel --}}
        <div id="repeat_candidate_panel" class="mt-3 hidden rounded-lg border border-amber-200 bg-amber-50 px-3 py-3 text-xs text-amber-900">
            <div class="flex flex-wrap items-end gap-2">
                <div class="min-w-64 flex-1">
                    <label class="mb-1 block font-medium">Repeat adayı</label>
                    <select id="repeat_candidate_select" class="w-full rounded-lg border border-amber-300 bg-white px-2 py-1.5 text-xs focus:ring-2 focus:ring-amber-500">
                        <option value="">Aday seç</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block font-medium">Alan anahtarı</label>
                    <input id="repeat_field_key" type="text" placeholder="slides"
                           class="w-44 rounded-lg border border-amber-300 bg-white px-2 py-1.5 text-xs focus:ring-2 focus:ring-amber-500">
                </div>
                <button type="button" id="apply_repeat_candidate"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-amber-700">
                    <i class="fas fa-check"></i> Repeater Schema Ekle
                </button>
            </div>
            <p id="repeat_candidate_help" class="mt-2 text-amber-800">
                Aynı parent içindeki aynı tag/class tekrarlarını bulur.
            </p>
            <div id="repeat_candidate_meta" class="mt-2 hidden rounded-lg border border-amber-200 bg-white px-3 py-2 text-amber-900"></div>

            {{-- Manuel repeat --}}
            <div class="mt-3 rounded-lg border border-amber-200 bg-white p-3">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <div class="font-semibold text-amber-900">Manuel Repeat</div>
                        <p class="mt-1 text-amber-800">Tek item HTML'ini ver, sistem <code>@verbatim{{{items_html}}}@endverbatim</code> placeholder'ı ekler.</p>
                    </div>
                    <select id="manual_repeat_type" class="rounded-lg border border-amber-300 bg-white px-2 py-1.5 text-xs focus:ring-2 focus:ring-amber-500">
                        <option value="slides">Slayt / Owl</option>
                        <option value="cards">Card</option>
                        <option value="list_items">Liste</option>
                        <option value="features">Özellik</option>
                        <option value="gallery_items">Galeri / Logo</option>
                        <option value="items">Genel Item</option>
                    </select>
                </div>
                <div class="mt-3 grid gap-2 lg:grid-cols-[180px_1fr_auto_auto] lg:items-end">
                    <div>
                        <label class="mb-1 block font-medium">Alan anahtarı</label>
                        <input id="manual_repeat_key" type="text" value="slides"
                               class="w-full rounded-lg border border-amber-300 bg-white px-2 py-1.5 text-xs focus:ring-2 focus:ring-amber-500">
                    </div>
                    <div>
                        <label class="mb-1 block font-medium">Tek item HTML</label>
                        <textarea id="manual_repeat_item_html" rows="3"
                                  placeholder='<div class="owl-item"><h2>Başlık</h2><p>Açıklama</p></div>'
                                  class="w-full rounded-lg border border-amber-300 bg-white px-2 py-1.5 font-mono text-xs focus:ring-2 focus:ring-amber-500"></textarea>
                    </div>
                    <button type="button" id="fill_manual_repeat_snippet"
                            class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-white px-3 py-2 text-xs font-medium text-amber-800 ring-1 ring-amber-200 hover:bg-amber-50">
                        <i class="fas fa-wand-magic-sparkles"></i> Örnek Doldur
                    </button>
                    <button type="button" id="apply_manual_repeat"
                            class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-amber-100 px-3 py-2 text-xs font-medium text-amber-800 hover:bg-amber-200">
                        <i class="fas fa-code"></i> Ekle
                    </button>
                </div>
            </div>
        </div>

        {{-- Eksik görsel alanı paneli — sabit görselleri düzenlenebilir alana çevirir --}}
        <div id="asset_field_panel" class="mt-3 hidden rounded-lg border border-sky-200 bg-sky-50 px-3 py-3 text-xs text-sky-900">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div class="font-semibold text-sky-900">
                    <i class="fas fa-image mr-1"></i> Şemaya bağlı olmayan sabit görseller
                </div>
                <button type="button" id="apply_all_asset_fields"
                        class="hidden inline-flex items-center gap-1.5 rounded-lg bg-sky-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-sky-700">
                    <i class="fas fa-check-double"></i> Tümünü Alan Yap
                </button>
            </div>
            <p id="asset_field_help" class="mt-2 text-sky-800"></p>
            <div id="asset_field_list" class="mt-2 space-y-2"></div>
        </div>

        {{-- Placeholder kuralı --}}
        <details class="mt-3">
            <summary class="cursor-pointer text-xs font-medium text-gray-500 hover:text-gray-700">
                <i class="fas fa-circle-info mr-1"></i> Placeholder kuralları
            </summary>
            <div class="mt-2 rounded-lg border border-blue-200 bg-blue-50 px-3 py-3 text-xs text-blue-900">
                <div class="space-y-1">
@verbatim
                    <div><code>{{title}}</code>, <code>{{button_text}}</code>: düz alan</div>
                    <div><code>{{{body_html}}}</code>, <code>{{items_html}}</code>: HTML / repeat çıktı alanı</div>
                    <div><code>{{image_url}}</code>, <code>{{logo_url}}</code>: görsel URL alanı</div>
                    <div><code>{{slide_1_title}}</code>, <code>{{slide_2_image_url}}</code>: indeksli alan</div>
                    <div><code>{{site_name}}</code>, <code>{{phone}}</code>, <code>{{email}}</code>, <code>{{address}}</code>: sistem alanları</div>
                    <div><code>{{{menu_header_html}}}</code>, <code>{{{menu_footer_html}}}</code>: menü HTML</div>
@endverbatim
                </div>
            </div>
        </details>
    </div>
</div>
