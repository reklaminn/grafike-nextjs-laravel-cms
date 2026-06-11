{{-- Block / Row / Column settings modals — lives inside frontendSectionEditor() Alpine scope --}}

{{-- ── Block Picker Modal ──────────────────────────────────────────────── --}}
<div x-show="pickerModalOpen" x-cloak
     class="fixed inset-0 z-[80] flex items-start justify-center bg-black/60 px-4 pt-12 pb-8"
     @click.self="closeBlockPicker()"
     @keydown.escape.window="closeBlockPicker()">
    <div class="flex w-full max-w-2xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl"
         style="max-height: calc(100vh - 5rem)">

        {{-- Header --}}
        <div class="flex items-center gap-3 border-b border-gray-100 px-5 py-4">
            <div class="flex-1">
                <h4 class="text-base font-semibold text-gray-900">Block Seç</h4>
                <p class="mt-0.5 text-xs text-gray-500">
                    Sayfaya eklenecek block şablonunu seç
                </p>
            </div>
            <button type="button" @click="refreshTemplateCatalog()"
                    :disabled="catalogRefreshing"
                    title="Şablon listesini yenile (yeni eklenen şablonlar görünür)"
                    class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-indigo-600 disabled:opacity-50">
                <i class="fas fa-arrows-rotate text-sm" :class="catalogRefreshing && 'fa-spin'"></i>
            </button>
            <button type="button" @click="closeBlockPicker()"
                    class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                <i class="fas fa-xmark text-sm"></i>
            </button>
        </div>

        {{-- Search --}}
        <div class="border-b border-gray-100 px-4 py-3">
            <div class="relative">
                <i class="fas fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="text"
                       x-model="pickerSearch"
                       x-ref="pickerSearchInput"
                       placeholder="Block ara (ad, type, variation)…"
                       @keydown.escape.stop="pickerSearch ? pickerSearch = '' : closeBlockPicker()"
                       class="w-full rounded-lg border border-gray-300 py-2 pl-8 pr-3 text-sm focus:border-transparent focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>

        {{-- Template list --}}
        <div class="flex-1 overflow-y-auto p-4">

            {{-- Group by type --}}
            <template x-if="getFilteredTemplates(pickerSearch).length > 0">
                <div class="space-y-4">
                    <template x-for="typeGroup in groupedTemplates(pickerSearch)" :key="typeGroup.type">
                        <div>
                            <p class="mb-2 text-[10px] font-semibold uppercase tracking-widest text-gray-400"
                               x-text="typeGroup.label || typeGroup.type"></p>
                            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                <template x-for="template in typeGroup.templates" :key="template.id">
                                    <button type="button"
                                            @click="pickTemplate(template.id)"
                                            class="group flex items-start gap-3 rounded-xl border border-gray-200 bg-white p-3 text-left transition hover:border-indigo-300 hover:bg-indigo-50">
                                        {{-- Thumbnail or icon --}}
                                        <template x-if="template.preview_image_url">
                                            <img :src="template.preview_image_url"
                                                 class="mt-0.5 h-10 w-14 shrink-0 rounded-lg object-cover border border-gray-200">
                                        </template>
                                        <template x-if="!template.preview_image_url">
                                            <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-400 group-hover:bg-indigo-100 group-hover:text-indigo-600">
                                                <i class="fas text-sm"
                                                   :class="{
                                                       'fa-image': ['hero','hero-banner','slider'].includes(template.type),
                                                       'fa-bars': template.type === 'header',
                                                       'fa-grip-lines': template.type === 'footer',
                                                       'fa-align-left': ['rich-text','content-block'].includes(template.type),
                                                       'fa-newspaper': template.type === 'article-list',
                                                       'fa-star': template.type === 'features',
                                                       'fa-bullhorn': template.type === 'cta',
                                                       'fa-images': template.type === 'gallery',
                                                       'fa-quote-left': template.type === 'testimonials',
                                                       'fa-id-card': template.type === 'cards',
                                                       'fa-play-circle': template.type === 'video-embed',
                                                       'fa-heading': template.type === 'page-header',
                                                       'fa-cubes': !['hero','hero-banner','slider','header','footer','rich-text','content-block','article-list','features','cta','gallery','testimonials','cards','video-embed','page-header'].includes(template.type),
                                                   }"></i>
                                            </div>
                                        </template>
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-sm font-semibold text-gray-900 group-hover:text-indigo-700"
                                               x-text="template.name"></p>
                                            <div class="mt-1 flex flex-wrap gap-1">
                                                <span class="rounded-full px-2 py-0.5 text-[10px] font-medium"
                                                      :class="template.render_mode === 'component' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'"
                                                      x-text="template.render_mode"></span>
                                                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-medium text-gray-600"
                                                      x-text="template.variation"></span>
                                            </div>
                                        </div>
                                        <i class="fas fa-arrow-right mt-2 shrink-0 text-[10px] text-gray-300 group-hover:text-indigo-400"></i>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            {{-- Empty state --}}
            <template x-if="getFilteredTemplates(pickerSearch).length === 0">
                <div class="rounded-xl border border-dashed border-gray-300 px-4 py-10 text-center">
                    <i class="fas fa-magnifying-glass mb-3 text-2xl text-gray-300"></i>
                    <p class="text-sm text-gray-400">
                        "<span x-text="pickerSearch"></span>" için şablon bulunamadı.
                    </p>
                    <button type="button" @click="pickerSearch = ''"
                            class="mt-3 text-xs text-indigo-600 hover:underline">Aramayı temizle</button>
                </div>
            </template>
        </div>
    </div>
</div>

<div x-show="settingsModalOpen" x-cloak
     class="fixed inset-0 z-[70] flex items-center justify-center bg-black/50 p-4"
     @click.self="closeBlockSettings()">
    <div class="w-full max-w-3xl rounded-2xl bg-white shadow-2xl border border-gray-200 p-5 max-h-[85vh] overflow-y-auto">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h4 class="text-base font-semibold text-gray-900">Block Ayarları</h4>
                <p class="mt-1 text-xs text-gray-500" x-text="settingsBlock ? (settingsBlock.template_name || settingsBlock.type) : ''"></p>
            </div>
            <div class="flex items-center gap-2">
                <a x-show="settingsBlock"
                   x-cloak
                   :href="settingsBlock?.section_template_id
                           ? (@js(url('admin/section-templates')) + '/' + settingsBlock.section_template_id + '/edit')
                           : @js(route('admin.section-templates.index'))"
                   :title="settingsBlock?.section_template_id ? 'Block şablonunu düzenle' : 'Block şablonları listesi'"
                   target="_blank"
                   class="inline-flex items-center gap-1.5 rounded-lg bg-purple-50 px-3 py-2 text-xs font-medium text-purple-700 hover:bg-purple-100 transition-colors">
                    <i class="fas fa-pen-to-square text-[11px]"></i>
                    <span>Şablonu Düzenle</span>
                </a>
                <button type="button" x-show="settingsBlock?.section_template_id" x-cloak
                        @click="refreshTemplateCatalog()"
                        :disabled="catalogRefreshing"
                        title="Şablondan yenile — şablonda yapılan son değişiklikleri bu bloğa uygula"
                        class="rounded-lg bg-gray-100 px-3 py-2 text-xs text-gray-600 hover:bg-gray-200 hover:text-indigo-600 disabled:opacity-50">
                    <i class="fas fa-arrows-rotate" :class="catalogRefreshing && 'fa-spin'"></i>
                </button>
                <button type="button"
                        @click="closeBlockSettings()"
                        class="rounded-lg bg-gray-100 px-3 py-2 text-xs text-gray-600 hover:bg-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <template x-if="settingsBlock">
            <div class="mt-5 space-y-4">
                {{-- Şablon güncellik uyarısı — blok schema'sı katalogdan farklıysa --}}
                <div x-show="blockSchemaIsStale(settingsBlock)" x-cloak
                     class="flex items-center justify-between gap-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3">
                    <div class="flex items-center gap-2 text-xs text-amber-800">
                        <i class="fas fa-triangle-exclamation text-amber-500"></i>
                        <span>Bu bloğun alan yapısı şablonun güncel haliyle eşleşmiyor.</span>
                    </div>
                    <button type="button"
                            @click="applyTemplateToBlock(settingsDraft); queueSerializedRegionsSync()"
                            class="shrink-0 rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-amber-700">
                        <i class="fas fa-arrows-rotate mr-1"></i> Şablondan Yenile
                    </button>
                </div>

                {{-- Per-block validation error summary --}}
                <template x-if="settingsBlock && fieldErrors[settingsBlock.id] && Object.keys(fieldErrors[settingsBlock.id]).length > 0">
                    <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3">
                        <div class="flex items-center gap-2 text-xs font-semibold text-red-800 mb-1">
                            <i class="fas fa-circle-exclamation"></i>
                            Bu blokta doğrulama hatası var — İçerik sekmesinden düzeltin:
                        </div>
                        <ul class="list-disc list-inside space-y-0.5">
                            <template x-for="[fld, msgs] in Object.entries(fieldErrors[settingsBlock.id] || {})" :key="fld">
                                <template x-for="msg in msgs" :key="msg">
                                    <li class="text-xs text-red-700" x-text="msg"></li>
                                </template>
                            </template>
                        </ul>
                    </div>
                </template>

                <div class="flex flex-wrap gap-2 border-b border-gray-200 pb-3">
                    <button type="button" @click="settingsTab = 'content'" class="rounded-lg px-3 py-1.5 text-xs font-medium"
                            :class="settingsTab === 'content' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'">
                        İçerik
                    </button>
                    <button type="button" @click="settingsTab = 'style'" class="rounded-lg px-3 py-1.5 text-xs font-medium"
                            :class="settingsTab === 'style' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'">
                        Stil
                    </button>
                    <button type="button" @click="settingsTab = 'code'" class="rounded-lg px-3 py-1.5 text-xs font-medium"
                            :class="settingsTab === 'code' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'">
                        Kod
                    </button>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="flex items-center gap-2 rounded-lg bg-gray-50 px-3 py-2 text-sm text-gray-700">
                        <input type="checkbox" x-model="settingsBlock.is_active" class="h-4 w-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500">
                        Block aktif
                    </label>
                    <label class="flex items-center gap-2 rounded-lg bg-indigo-50 px-3 py-2 text-sm text-indigo-700"
                           title="Sadece giriş yapmış üyeler bu bloğu görebilir">
                        <input type="checkbox" x-model="settingsBlock.is_member_only" class="h-4 w-4 rounded border-indigo-300 text-indigo-600 focus:ring-indigo-500">
                        <i class="fas fa-lock text-xs text-indigo-400 mr-0.5"></i> Sadece üyeler
                    </label>
                    <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-600 sm:col-span-2">
                        <span class="font-medium text-gray-700">Özet:</span>
                        <span x-text="blockSummary(settingsBlock)"></span>
                    </div>

                    {{-- Group restriction for block --}}
                    @php $blockGroups = $memberGroups ?? collect(); @endphp
                    @if($blockGroups->isNotEmpty())
                    <div class="sm:col-span-2 rounded-lg border border-purple-200 bg-purple-50 px-3 py-2.5">
                        <p class="text-xs font-semibold text-purple-700 mb-2 flex items-center gap-1">
                            <i class="fas fa-users text-purple-400"></i> Grup kısıtlaması
                            <span class="font-normal text-purple-500">(boş = herkese açık)</span>
                        </p>
                        <div class="flex flex-wrap gap-3">
                            @foreach($blockGroups as $group)
                            <label class="flex items-center gap-1.5 text-xs text-purple-800 cursor-pointer">
                                <input type="checkbox"
                                       :value="{{ $group->id }}"
                                       x-model="settingsBlock.allowed_group_ids"
                                       class="h-3.5 w-3.5 rounded border-purple-300 text-purple-600 focus:ring-purple-500">
                                {{ $group->name }}
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                <div x-show="settingsTab === 'content'" class="grid gap-3">

                    {{-- ─── AI yardımcısı (FAZ 4.2) ───────────────────── --}}
                    <div class="rounded-xl border border-indigo-100 bg-gradient-to-r from-purple-50 to-indigo-50 p-3">
                        <div class="flex items-center gap-2 mb-2">
                            <i class="fas fa-wand-magic-sparkles text-indigo-500"></i>
                            <span class="text-xs font-semibold text-gray-700">AI Yardımcısı</span>
                            <span class="text-[10px] text-gray-500 ml-auto">Bu blokun içeriğini AI ile dönüştür</span>
                        </div>
                        <div class="flex flex-wrap gap-2 items-stretch">
                            <select x-model="aiAction"
                                    :disabled="aiLoading"
                                    class="flex-1 min-w-[180px] px-3 py-2 border border-indigo-200 rounded-lg text-xs bg-white focus:ring-2 focus:ring-indigo-500 disabled:opacity-50">
                                <option value="shorten">📏 Daha kısa yaz</option>
                                <option value="lengthen">📜 Daha uzun yaz</option>
                                <option value="professional">💼 Profesyonel ton</option>
                                <option value="casual">😊 Samimi ton</option>
                                <option value="seo_optimize">🔍 SEO odaklı yaz</option>
                                <option value="rephrase">🔄 Yeniden yaz</option>
                                <option value="fix_typos">✏️ Sadece yazım hatalarını düzelt</option>
                                <option value="translate_en">🇬🇧 İngilizceye çevir</option>
                                <option value="translate_tr">🇹🇷 Türkçeye çevir</option>
                                <option value="translate_de">🇩🇪 Almancaya çevir</option>
                                <option value="translate_ru">🇷🇺 Rusçaya çevir</option>
                                <option value="translate_ar">🇸🇦 Arapçaya çevir</option>
                                <option value="custom">⚡ Özel komut</option>
                            </select>
                            <template x-if="!aiStreaming">
                                <button type="button"
                                        @click="applyAiTransform()"
                                        :disabled="aiLoading"
                                        class="px-4 py-2 bg-indigo-600 text-white text-xs font-medium rounded-lg hover:bg-indigo-700 disabled:bg-gray-300 disabled:cursor-not-allowed flex items-center gap-1.5 whitespace-nowrap">
                                    <i class="fas fa-bolt"></i>
                                    <span>Uygula</span>
                                </button>
                            </template>
                            <template x-if="aiStreaming">
                                <button type="button"
                                        @click="abortAiTransform()"
                                        class="px-4 py-2 bg-red-500 text-white text-xs font-medium rounded-lg hover:bg-red-600 flex items-center gap-1.5 whitespace-nowrap">
                                    <i class="fas fa-stop"></i>
                                    <span>Durdur</span>
                                </button>
                            </template>
                        </div>
                        <div x-show="aiAction === 'custom'" x-cloak class="mt-2">
                            <input type="text" x-model="aiCustomPrompt"
                                   :disabled="aiLoading"
                                   maxlength="500"
                                   placeholder="Örn: Cümlelerin başına emoji ekle ama içeriği değiştirme"
                                   class="w-full px-3 py-2 border border-indigo-200 rounded-lg text-xs focus:ring-2 focus:ring-indigo-500 disabled:opacity-50">
                        </div>

                        {{-- Typewriter preview — gösterim sırasında canlı chunk birikir --}}
                        <div x-show="aiStreaming && aiStreamText" x-cloak class="mt-2">
                            <div class="bg-white border border-indigo-200 rounded-lg p-2 text-xs text-gray-700 max-h-24 overflow-y-auto font-mono leading-relaxed whitespace-pre-wrap"
                                 x-text="aiStreamText"></div>
                            <p class="text-[10px] text-indigo-500 mt-0.5 flex items-center gap-1">
                                <span class="inline-block w-1.5 h-1.5 rounded-full bg-indigo-400 animate-ping"></span>
                                AI yazıyor…
                            </p>
                        </div>

                        <div x-show="aiStatus" x-cloak class="mt-2 text-xs rounded-md p-2"
                             :class="aiStatusOk ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                                                : 'bg-red-50 text-red-700 border border-red-200'">
                            <i class="fas" :class="aiStatusOk ? 'fa-check-circle' : 'fa-exclamation-triangle'"></i>
                            <span x-text="aiStatus"></span>
                        </div>
                        <p class="mt-2 text-[10px] text-gray-500">
                            AI sadece metin alanlarını değiştirir; medya, link ve renk gibi alanlara dokunmaz.
                            Yanıtı Kaydet'e basana kadar uygulanmaz — beğenmezseniz Vazgeç'e basın.
                        </p>
                    </div>

                    <template x-for="[fieldName, fieldSchema] in Object.entries(settingsBlock.schema || {})" :key="fieldName">
                        <div>
                            <template x-if="(fieldSchema.type || 'text') === 'repeater'">
                                <div class="rounded-xl border border-amber-200 bg-amber-50/50 p-3"
                                     x-init="ensureRepeaterContent(settingsBlock, fieldName)">
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <div>
                                            <label class="block text-xs font-semibold text-amber-900" x-text="fieldLabel(fieldName, fieldSchema)"></label>
                                            <p class="mt-1 text-xs text-amber-700">
                                                <span x-text="(settingsBlock.content[fieldName] || []).length"></span> item
                                            </p>
                                        </div>
                                        <button type="button"
                                                @click="addRepeaterItem(settingsBlock, fieldName, fieldSchema)"
                                                class="rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-amber-700">
                                            <i class="fas fa-plus mr-1"></i> Item Ekle
                                        </button>
                                    </div>

                                    <div class="mt-3 space-y-3">
                                        <template x-for="(item, itemIndex) in settingsBlock.content[fieldName]" :key="item._uid || itemIndex">
                                            <div class="rounded-lg border border-amber-200 bg-white p-3">
                                                <div class="mb-3 flex flex-wrap items-center justify-between gap-2 border-b border-amber-100 pb-2">
                                                    <div class="text-xs font-semibold text-gray-700">
                                                        Item #<span x-text="itemIndex + 1"></span>
                                                    </div>
                                                    <div class="flex items-center gap-1">
                                                        <button type="button"
                                                                @click="moveRepeaterItem(settingsBlock, fieldName, itemIndex, -1)"
                                                                :disabled="itemIndex === 0"
                                                                class="rounded bg-gray-100 px-2 py-1 text-xs text-gray-600 hover:bg-gray-200 disabled:cursor-not-allowed disabled:opacity-40">
                                                            <i class="fas fa-arrow-up"></i>
                                                        </button>
                                                        <button type="button"
                                                                @click="moveRepeaterItem(settingsBlock, fieldName, itemIndex, 1)"
                                                                :disabled="itemIndex >= (settingsBlock.content[fieldName] || []).length - 1"
                                                                class="rounded bg-gray-100 px-2 py-1 text-xs text-gray-600 hover:bg-gray-200 disabled:cursor-not-allowed disabled:opacity-40">
                                                            <i class="fas fa-arrow-down"></i>
                                                        </button>
                                                        <button type="button"
                                                                @click="duplicateRepeaterItem(settingsBlock, fieldName, itemIndex)"
                                                                class="rounded bg-gray-100 px-2 py-1 text-xs text-gray-600 hover:bg-gray-200">
                                                            <i class="fas fa-copy"></i>
                                                        </button>
                                                        <button type="button"
                                                                @click="removeRepeaterItem(settingsBlock, fieldName, itemIndex)"
                                                                class="rounded bg-red-50 px-2 py-1 text-xs text-red-600 hover:bg-red-100">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>

                                                <div class="grid gap-3 sm:grid-cols-2">
                                                    <template x-for="[itemFieldName, itemFieldSchema] in Object.entries(repeaterFieldSchema(fieldSchema))" :key="itemFieldName">
                                                        <div :class="['textarea','rich-text','html'].includes(itemFieldSchema.type || 'text') ? 'sm:col-span-2' : ''">
                                                            <label class="mb-1 block text-xs font-medium text-gray-600"
                                                                   x-text="fieldLabel(itemFieldName, itemFieldSchema)"></label>
                                                            <div x-data="blockFieldInput(item, itemFieldName, itemFieldSchema)">
                                                                @include('admin.pages._form._field-types')
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>

                                        <div x-show="(settingsBlock.content[fieldName] || []).length === 0"
                                             class="rounded-lg border border-dashed border-amber-300 bg-white px-3 py-4 text-center text-xs text-amber-700">
                                            Bu repeater alanında item yok.
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <template x-if="(fieldSchema.type || 'text') !== 'repeater'">
                                <div>
                                    <label class="mb-1 block text-xs font-medium transition-colors"
                                           :class="(fieldErrors[settingsBlock.id] || {})[fieldName] ? 'text-red-600' : 'text-gray-600'"
                                           x-text="fieldLabel(fieldName, fieldSchema)"></label>
                                    <div x-data="blockFieldInput(settingsBlock.content, fieldName, fieldSchema)"
                                         :class="(fieldErrors[settingsBlock.id] || {})[fieldName] ? 'ring-2 ring-red-300 rounded-lg' : ''">
                                        @include('admin.pages._form._field-types')
                                    </div>
                                    <template x-if="(fieldErrors[settingsBlock.id] || {})[fieldName]">
                                        <div class="mt-1 space-y-0.5">
                                            <template x-for="errMsg in ((fieldErrors[settingsBlock.id] || {})[fieldName] || [])" :key="errMsg">
                                                <p class="text-xs text-red-600 flex items-center gap-1">
                                                    <i class="fas fa-circle-exclamation text-[10px]"></i>
                                                    <span x-text="errMsg"></span>
                                                </p>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                <div x-show="settingsTab === 'style'" class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Wrapper Tag</label>
                        <input type="text" x-model="settingsBlock.wrapper_tag"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-200">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">CSS Sınıf</label>
                        <input type="text" x-model="settingsBlock.css_class"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-200">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Element ID</label>
                        <input type="text" x-model="settingsBlock.element_id"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-200">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Inline Style</label>
                        <input type="text" x-model="settingsBlock.inline_style"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-200">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-xs font-medium text-gray-600">Diğer Attributes</label>
                        <input type="text" x-model="settingsBlock.custom_attributes"
                               placeholder='data-animate="fade-up" aria-label="..."'
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-200">
                    </div>
                </div>

                <div x-show="settingsTab === 'code'" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Render Önizleme</label>
                        <div class="rounded-lg border border-gray-200 bg-white p-3 max-h-56 overflow-auto">
                            <div class="origin-top-left scale-[0.82] transform-gpu rounded-lg border border-dashed border-gray-200 p-3"
                                 style="width:122%;"
                                 x-html="blockRenderedHtml(settingsBlock)"></div>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Üretilen HTML Kodu</label>
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 text-xs text-gray-700 font-mono overflow-x-auto"
                             x-text="blockCodePreview(settingsBlock)"></div>
                    </div>
                    <div x-show="settingsBlock.render_mode === 'html'">
                        <label class="mb-1 block text-xs font-medium text-gray-600">HTML Override</label>
                        <textarea x-model="settingsBlock.html_override"
                                  rows="8"
                                  placeholder="Boş bırakırsan şablonun varsayılan HTML'i kullanılır."
                                  class="w-full rounded-lg border border-gray-300 px-3 py-2 font-mono text-xs focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-200"></textarea>
                    </div>
                    <div x-show="settingsBlock.render_mode !== 'html'" class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs text-blue-800">
                        Component mode için ham HTML override yerine props ve stil alanları kullanılmalı.
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-4">
                    <button type="button"
                            @click="closeBlockSettings()"
                            class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">
                        İptal
                    </button>
                    <button type="button"
                            @click="saveBlockSettings()"
                            class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-medium text-white hover:bg-amber-700">
                        Kaydet
                    </button>
                </div>
            </div>
        </template>
    </div>
</div>

<div x-show="rowSettingsModalOpen" x-cloak
     class="fixed inset-0 z-[70] flex items-center justify-center bg-black/50 p-4"
     @click.self="closeRowSettings()">
    <div class="w-full max-w-2xl rounded-2xl bg-white shadow-2xl border border-gray-200 p-5 max-h-[85vh] overflow-y-auto">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h4 class="text-base font-semibold text-gray-900">Satır Ayarları</h4>
                <p class="mt-1 text-xs text-gray-500">Container, wrapper ve stil ayarları.</p>
            </div>
            <button type="button"
                    @click="closeRowSettings()"
                    class="rounded-lg bg-gray-100 px-3 py-2 text-xs text-gray-600 hover:bg-gray-200">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <template x-if="settingsRow">
            <div class="mt-5 space-y-4">
                <div class="flex flex-wrap gap-2 border-b border-gray-200 pb-3">
                    <button type="button" @click="rowSettingsTab = 'layout'" class="rounded-lg px-3 py-1.5 text-xs font-medium"
                            :class="rowSettingsTab === 'layout' ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'">
                        Yerleşim
                    </button>
                    <button type="button" @click="rowSettingsTab = 'style'" class="rounded-lg px-3 py-1.5 text-xs font-medium"
                            :class="rowSettingsTab === 'style' ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'">
                        Stil
                    </button>
                    <button type="button" @click="rowSettingsTab = 'code'" class="rounded-lg px-3 py-1.5 text-xs font-medium"
                            :class="rowSettingsTab === 'code' ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'">
                        Kod
                    </button>
                </div>

                <div x-show="rowSettingsTab === 'layout'" class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Container</label>
                        <select x-model="settingsRow.container"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                            <option value="container">container</option>
                            <option value="container-fluid">container-fluid</option>
                            <option value="">Yok</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Wrapper Tag</label>
                        <input type="text" x-model="settingsRow.wrapper_tag"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    </div>
                    <label class="flex items-center gap-2 rounded-lg bg-gray-50 px-3 py-2 text-sm text-gray-700 sm:col-span-2">
                        <input type="checkbox" x-model="settingsRow.is_active" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        Satır aktif
                    </label>
                </div>

                <div x-show="rowSettingsTab === 'style'" class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">CSS Sınıf</label>
                        <input type="text" x-model="settingsRow.css_class"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Element ID</label>
                        <input type="text" x-model="settingsRow.element_id"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-xs font-medium text-gray-600">Inline Style</label>
                        <input type="text" x-model="settingsRow.inline_style"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-xs font-medium text-gray-600">Diğer Attributes</label>
                        <input type="text" x-model="settingsRow.custom_attributes"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    </div>
                </div>

                <div x-show="rowSettingsTab === 'code'" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Wrapper HTML</label>
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 text-xs text-gray-700 font-mono overflow-x-auto"
                             x-text="rowPreview(settingsRow)"></div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-4">
                    <button type="button"
                            @click="closeRowSettings()"
                            class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">
                        İptal
                    </button>
                    <button type="button"
                            @click="saveRowSettings()"
                            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                        Kaydet
                    </button>
                </div>
            </div>
        </template>
    </div>
</div>

<div x-show="columnSettingsModalOpen" x-cloak
     class="fixed inset-0 z-[70] flex items-center justify-center bg-black/50 p-4"
     @click.self="closeColumnSettings()">
    <div class="w-full max-w-3xl rounded-2xl bg-white shadow-2xl border border-gray-200 p-5 max-h-[85vh] overflow-y-auto">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h4 class="text-base font-semibold text-gray-900">Kolon Ayarları</h4>
                <p class="mt-1 text-xs text-gray-500">Responsive genişlik, CSS ve wrapper ayarları.</p>
            </div>
            <button type="button"
                    @click="closeColumnSettings()"
                    class="rounded-lg bg-gray-100 px-3 py-2 text-xs text-gray-600 hover:bg-gray-200">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <template x-if="columnSettingsDraft">
            <div class="mt-5 space-y-4">
                <div class="flex flex-wrap gap-2 border-b border-gray-200 pb-3">
                    <button type="button" @click="columnSettingsTab = 'layout'" class="rounded-lg px-3 py-1.5 text-xs font-medium"
                            :class="columnSettingsTab === 'layout' ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'">
                        Yerleşim
                    </button>
                    <button type="button" @click="columnSettingsTab = 'style'" class="rounded-lg px-3 py-1.5 text-xs font-medium"
                            :class="columnSettingsTab === 'style' ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'">
                        Stil
                    </button>
                    <button type="button" @click="columnSettingsTab = 'code'" class="rounded-lg px-3 py-1.5 text-xs font-medium"
                            :class="columnSettingsTab === 'code' ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'">
                        Kod
                    </button>
                </div>

                <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-600">
                    Yerleşim etiketi: <span class="font-semibold text-gray-800" x-text="columnLayoutSummary(columnSettingsDraft)"></span>
                </div>

                <div x-show="columnSettingsTab === 'layout'" class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Genel (col-)</label>
                        <select
                                x-effect="$el.value = String(columnSettingsDraft.width ?? '')"
                                @change="columnSettingsDraft.width = $event.target.value; normalizeResponsive(columnSettingsDraft)"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                            <option value="">Yok</option>
                            <template x-for="width in [1,2,3,4,5,6,7,8,9,10,11,12]" :key="'base-' + width">
                                <option :value="String(width)" x-text="'col-' + width"></option>
                            </template>
                        </select>
                    </div>
                    <template x-for="breakpoint in ['sm', 'md', 'lg', 'xl']" :key="breakpoint">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600" x-text="breakpoint.toUpperCase() + ' (col-' + breakpoint + '-)'"></label>
                            <select
                                    x-effect="$el.value = String(columnSettingsDraft.responsive[breakpoint] ?? '')"
                                    @change="columnSettingsDraft.responsive[breakpoint] = $event.target.value"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                                <option value="">Yok</option>
                                <template x-for="width in [1,2,3,4,5,6,7,8,9,10,11,12]" :key="breakpoint + '-' + width">
                                    <option :value="String(width)" x-text="'col-' + breakpoint + '-' + width"></option>
                                </template>
                            </select>
                        </div>
                    </template>
                    <label class="flex items-center gap-2 rounded-lg bg-gray-50 px-3 py-2 text-sm text-gray-700 sm:col-span-2">
                        <input type="checkbox" x-model="columnSettingsDraft.is_active" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        Kolon aktif
                    </label>
                </div>

                <div x-show="columnSettingsTab === 'style'" class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">CSS Sınıf</label>
                        <input type="text" x-model="columnSettingsDraft.css_class"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Element ID</label>
                        <input type="text" x-model="columnSettingsDraft.element_id"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-xs font-medium text-gray-600">Inline Style</label>
                        <input type="text" x-model="columnSettingsDraft.inline_style"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-xs font-medium text-gray-600">Diğer Attributes</label>
                        <input type="text" x-model="columnSettingsDraft.custom_attributes"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    </div>
                </div>

                <div x-show="columnSettingsTab === 'code'" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Wrapper HTML</label>
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 text-xs text-gray-700 font-mono overflow-x-auto"
                             x-text="columnPreview(columnSettingsDraft)"></div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-4">
                    <button type="button"
                            @click="closeColumnSettings()"
                            class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">
                        İptal
                    </button>
                    <button type="button"
                            @click="saveColumnSettings()"
                            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                        Kaydet
                    </button>
                </div>
            </div>
        </template>
    </div>
</div>
