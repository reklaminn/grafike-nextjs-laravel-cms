@if($editorData->shouldRenderFrontendEditor())
@php
    // Build per-block field error map: { blockId => { fieldName => ['msg',...] } }
    // Server errors arrive as "sections.block_0.fieldName" — map index → block UUID.
    $blockFieldErrors = [];
    if ($errors->any()) {
        $oldJson = old('sections_json');
        if ($oldJson) {
            $decodedOld = json_decode((string) $oldJson, true);
            if (is_array($decodedOld)) {
                $flatBlocks = \App\Support\FrontendSections::flattenBlocks($decodedOld);
                foreach ($errors->toArray() as $errorKey => $messages) {
                    if (preg_match('/^sections\.block_(\d+)\.(.+)$/', $errorKey, $m)) {
                        $idx     = (int) $m[1];
                        $field   = $m[2];
                        $blockId = $flatBlocks[$idx]['id'] ?? null;
                        if ($blockId) {
                            $blockFieldErrors[$blockId][$field] = $messages;
                        }
                    }
                }
            }
        }
    }
@endphp
@php
    $frontendEditorPayload = array_merge(
        $editorData->frontendSectionEditorPayload(),
        ['fieldErrors' => $blockFieldErrors],
    );
@endphp
<div x-show="builderMode === 'frontend'" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6"
     x-data="frontendSectionEditor({{ \Illuminate\Support\Js::from($frontendEditorPayload) }})"
     x-on:frontend-block-focus.window="focusBlock($event.detail.blockId)">

    {{-- Şablon senkronizasyon toast'u (başka sekmede şablon kaydedilince) --}}
    <div x-show="templateSyncToastVisible" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed bottom-6 right-6 z-[90] flex items-center gap-2.5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 shadow-lg">
        <i class="fas fa-arrows-rotate text-emerald-500"></i>
        <span x-text="templateSyncToast"></span>
        <button type="button" @click="templateSyncToastVisible = false"
                class="ml-1 text-emerald-400 hover:text-emerald-600">
            <i class="fas fa-times text-xs"></i>
        </button>
    </div>

    {{-- Editor header --}}
    <div class="flex items-center justify-between gap-4 mb-5">
        <div class="flex items-center gap-3">
            <h3 class="text-base font-semibold text-gray-800">
                <i class="fas fa-layer-group mr-2 text-amber-500"></i>Frontend Section Editörü
            </h3>
            <details class="text-xs text-gray-400">
                <summary class="cursor-pointer select-none hover:text-gray-600 list-none flex items-center gap-1">
                    <i class="fas fa-circle-info"></i> Bilgi
                </summary>
                <div class="absolute z-10 mt-2 w-80 space-y-2 rounded-xl border border-gray-200 bg-white p-4 shadow-lg text-xs">
                    <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-amber-800">
                        <div class="font-semibold">Bu alan yeni sistem içindir.</div>
                        <p class="mt-1 leading-5">Next.js frontend'in kullandığı <code>sections_json</code> yapısını düzenler.</p>
                    </div>
                    <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-emerald-800">
                        <div class="font-semibold">Önerilen akış</div>
                        <p class="mt-1 leading-5">Önce <a href="{{ route('admin.section-templates.create') }}" target="_blank" rel="noopener noreferrer" class="font-medium underline">Block Şablonları</a> ekranında şablon oluştur, sonra burada <strong>Block Ekle</strong> ile kullan.</p>
                    </div>
                </div>
            </details>
        </div>
        <button type="button"
                @click="openFrontendJson = !openFrontendJson"
                class="inline-flex items-center gap-1.5 rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-200">
            <i class="fas fa-code"></i> Ham JSON
        </button>
    </div>

    {{-- Ham JSON alanı --}}
    <div>
        <div x-show="openFrontendJson" class="mb-4">
            <div class="mb-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                Bu alan gelişmiş/teknik kullanım içindir.
            </div>
            <textarea x-model="serializedRegions"
                      rows="12"
                      class="w-full rounded-lg border border-gray-300 px-3 py-2 font-mono text-xs focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-200"></textarea>
        </div>
    </div>

    {{-- Regions --}}
    <div class="space-y-4">
        <template x-for="region in regionNames" :key="region">
            <section class="rounded-2xl border"
                     :class="regionShellClass(region)">

                {{-- Region header --}}
                <div class="flex items-center justify-between gap-3 px-4 py-3">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold uppercase px-2 py-0.5 rounded"
                              :class="regionBadgeClass(region)"
                              x-text="regionLabel(region)"></span>
                        <span class="text-xs text-gray-500"
                              x-text="(regions[region] || []).length + ' satır'"></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <template x-for="preset in getRegionPresets(region)" :key="preset.key">
                            <button type="button"
                                    @click="applyPreset(region, preset.key)"
                                    class="inline-flex items-center gap-1 rounded-lg bg-white px-2 py-1 text-[11px] font-medium text-gray-600 border border-gray-200 hover:border-amber-300 hover:bg-amber-50 hover:text-amber-700 transition-colors">
                                <i class="fas fa-wand-magic-sparkles text-[9px]"></i>
                                <span x-text="preset.label"></span>
                            </button>
                        </template>
                        <button type="button"
                                @click="addRow(region)"
                                class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-medium"
                                :class="regionButtonClass(region)">
                            <i class="fas fa-plus text-[10px]"></i> Satır Ekle
                        </button>
                    </div>
                </div>

                {{-- Empty state --}}
                <template x-if="(regions[region] || []).length === 0">
                    <div class="mx-4 mb-4 rounded-xl border-2 border-dashed border-gray-300 bg-white px-4 py-6 text-center">
                        <div class="text-sm font-medium text-gray-500"
                             x-text="regionLabel(region) + ' bölgesi boş'"></div>
                        <button type="button"
                                @click="addRow(region)"
                                class="mt-2 inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-medium"
                                :class="regionButtonClass(region)">
                            <i class="fas fa-plus text-[10px]"></i> İlk satırı ekle
                        </button>
                    </div>
                </template>

                {{-- Rows --}}
                <div class="px-3 pb-3 space-y-2"
                     @dragover="rowDragOver($event, region, null)"
                     @drop.prevent="dropRow(region, null)">
                    <template x-for="(row, rowIndex) in (regions[region] || [])" :key="row._uid">
                        <div class="rounded-xl border bg-white overflow-hidden"
                             data-row-card
                             :class="[rowShellClass(region), row.is_active ? '' : 'opacity-60',
                                      dragRow && dragRow.region === region && dragRow.rowIndex === rowIndex ? 'opacity-40' : '',
                                      dragRowOver === rowDropKey(region, rowIndex) ? 'ring-2 ring-indigo-400' : '']"
                             @dragover.stop="rowDragOver($event, region, rowIndex)"
                             @drop.prevent.stop="dropRow(region, rowIndex)">

                            {{-- Row header — single line --}}
                            <div class="flex items-center justify-between gap-2 px-3 py-2">
                                <div class="flex items-center gap-2 min-w-0">
                                    <button type="button"
                                            draggable="true"
                                            @dragstart="startRowDrag($event, region, rowIndex)"
                                            @dragend="endRowDrag()"
                                            title="Sürükleyerek taşı"
                                            class="cursor-grab active:cursor-grabbing text-gray-300 hover:text-gray-500 flex-shrink-0 text-[11px]">
                                        <i class="fas fa-grip-vertical"></i>
                                    </button>
                                    <span class="text-xs font-bold uppercase px-1.5 py-0.5 rounded flex-shrink-0"
                                          :class="regionBadgeClass(region)"
                                          x-text="regionLabel(region)"></span>
                                    <span class="text-xs font-semibold text-gray-700"
                                          x-text="'Satır #' + (rowIndex + 1)"></span>
                                    <span x-show="row.css_class || row.wrapper_tag"
                                          class="truncate text-[11px] text-gray-400 max-w-[120px]"
                                          x-text="row.wrapper_tag ? '<' + row.wrapper_tag + '>' : '.' + row.css_class"></span>
                                </div>
                                <div class="flex items-center gap-1 flex-shrink-0">
                                    {{-- Active toggle --}}
                                    <button type="button"
                                            @click="row.is_active = !row.is_active"
                                            :title="row.is_active ? 'Aktif — tıkla devre dışı bırak' : 'Pasif — tıkla etkinleştir'"
                                            class="rounded px-1.5 py-1 text-xs transition-colors"
                                            :class="row.is_active ? 'text-green-500 hover:bg-green-50' : 'text-gray-300 hover:bg-gray-100'">
                                        <i class="fas fa-circle-dot"></i>
                                    </button>
                                    <button type="button" @click="openRowSettings(region, rowIndex)"
                                            title="Satır ayarları"
                                            class="rounded px-1.5 py-1 text-xs text-gray-500 hover:bg-gray-100">
                                        <i class="fas fa-cog"></i>
                                    </button>
                                    <button type="button" @click="toggleRowExpand(region, rowIndex)"
                                            class="rounded px-1.5 py-1 text-xs text-gray-500 hover:bg-gray-100">
                                        <i class="fas" :class="row._expanded === false ? 'fa-chevron-down' : 'fa-chevron-up'"></i>
                                    </button>
                                    <button type="button" @click="moveRow(region, rowIndex, -1)"
                                            title="Yukarı taşı"
                                            class="rounded px-1.5 py-1 text-xs text-gray-400 hover:bg-gray-100">
                                        <i class="fas fa-arrow-up"></i>
                                    </button>
                                    <button type="button" @click="moveRow(region, rowIndex, 1)"
                                            title="Aşağı taşı"
                                            class="rounded px-1.5 py-1 text-xs text-gray-400 hover:bg-gray-100">
                                        <i class="fas fa-arrow-down"></i>
                                    </button>
                                    <button type="button" @click="removeRow(region, rowIndex)"
                                            title="Satırı sil"
                                            class="rounded px-1.5 py-1 text-xs text-red-400 hover:bg-red-50">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>

                            {{-- Row column toolbar --}}
                            <div x-show="row._expanded !== false"
                                 class="flex items-center gap-2 border-t border-gray-100 bg-gray-50 px-3 py-2">
                                <span class="text-[11px] text-gray-400 mr-1">Kolon düzeni:</span>
                                <button type="button" @click="applyColumnPreset(region, rowIndex, [12])"
                                        class="rounded bg-white border border-gray-200 px-2 py-0.5 text-[11px] text-gray-600 hover:border-indigo-300 hover:text-indigo-700">
                                    1 tam
                                </button>
                                <button type="button" @click="applyColumnPreset(region, rowIndex, [6, 6])"
                                        class="rounded bg-white border border-gray-200 px-2 py-0.5 text-[11px] text-gray-600 hover:border-indigo-300 hover:text-indigo-700">
                                    2×6
                                </button>
                                <button type="button" @click="applyColumnPreset(region, rowIndex, [4, 4, 4])"
                                        class="rounded bg-white border border-gray-200 px-2 py-0.5 text-[11px] text-gray-600 hover:border-indigo-300 hover:text-indigo-700">
                                    3×4
                                </button>
                                <button type="button" @click="applyColumnPreset(region, rowIndex, [8, 4])"
                                        class="rounded bg-white border border-gray-200 px-2 py-0.5 text-[11px] text-gray-600 hover:border-indigo-300 hover:text-indigo-700">
                                    8+4
                                </button>
                                <button type="button" @click="addColumn(region, rowIndex)"
                                        class="ml-auto inline-flex items-center gap-1 rounded-lg bg-indigo-50 px-2.5 py-1 text-[11px] font-medium text-indigo-700 hover:bg-indigo-100">
                                    <i class="fas fa-plus text-[9px]"></i> Kolon
                                </button>
                            </div>

                            {{-- Columns grid --}}
                            <div x-show="row._expanded !== false"
                                 class="grid grid-cols-12 gap-3 p-3">
                                <template x-for="(column, columnIndex) in (row.columns || [])" :key="column._uid">
                                    <div class="rounded-xl border border-gray-200 bg-gray-50 overflow-hidden"
                                         :style="editorColumnCanvasStyle(column)"
                                         :class="column.is_active === false ? 'opacity-50' : ''">

                                        {{-- Column header — single compact line --}}
                                        <div class="flex items-center justify-between gap-1 border-b border-gray-200 bg-white px-2 py-1.5">
                                            <div class="flex items-center gap-1.5 min-w-0">
                                                <i class="fas fa-grip-vertical text-gray-300 text-[10px] flex-shrink-0"></i>
                                                <span class="rounded bg-indigo-100 px-1.5 py-0.5 text-[10px] font-bold text-indigo-700 flex-shrink-0"
                                                      x-text="columnClassLabel(column) || 'auto'"></span>
                                                <span class="text-[11px] text-gray-400"
                                                      x-text="'#' + (columnIndex + 1)"></span>
                                            </div>
                                            <div class="flex items-center gap-0.5 flex-shrink-0">
                                                <button type="button"
                                                        @click="column.is_active = !column.is_active"
                                                        :title="column.is_active !== false ? 'Aktif' : 'Pasif'"
                                                        class="rounded px-1 py-0.5 text-[11px] transition-colors"
                                                        :class="column.is_active !== false ? 'text-green-500 hover:bg-green-50' : 'text-gray-300 hover:bg-gray-100'">
                                                    <i class="fas fa-circle-dot"></i>
                                                </button>
                                                <button type="button"
                                                        @click="openColumnSettings(region, rowIndex, columnIndex)"
                                                        title="Kolon ayarları"
                                                        class="rounded px-1 py-0.5 text-[11px] text-indigo-500 hover:bg-indigo-50">
                                                    <i class="fas fa-cog"></i>
                                                </button>
                                                <button type="button"
                                                        @click="moveColumn(region, rowIndex, columnIndex, -1)"
                                                        :disabled="!canMoveColumn(row, columnIndex, -1)"
                                                        class="rounded px-1 py-0.5 text-[11px] transition-colors"
                                                        :class="canMoveColumn(row, columnIndex, -1) ? 'text-gray-500 hover:bg-gray-100' : 'text-gray-200 cursor-not-allowed'">
                                                    <i class="fas fa-arrow-left"></i>
                                                </button>
                                                <button type="button"
                                                        @click="moveColumn(region, rowIndex, columnIndex, 1)"
                                                        :disabled="!canMoveColumn(row, columnIndex, 1)"
                                                        class="rounded px-1 py-0.5 text-[11px] transition-colors"
                                                        :class="canMoveColumn(row, columnIndex, 1) ? 'text-gray-500 hover:bg-gray-100' : 'text-gray-200 cursor-not-allowed'">
                                                    <i class="fas fa-arrow-right"></i>
                                                </button>
                                                <button type="button"
                                                        @click="removeColumn(region, rowIndex, columnIndex)"
                                                        title="Kolonu sil"
                                                        class="rounded px-1 py-0.5 text-[11px] text-red-400 hover:bg-red-50">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>

                                        {{-- Blocks --}}
                                        <div class="p-2 space-y-2"
                                             :class="dragBlock && dragBlockOver === blockDropKey(region, rowIndex, columnIndex, null) ? 'rounded-lg ring-2 ring-indigo-200 bg-indigo-50/50' : ''"
                                             @dragover.stop="blockDragOver($event, region, rowIndex, columnIndex, null)"
                                             @drop.prevent.stop="dropBlock(region, rowIndex, columnIndex, null)">
                                            <template x-for="(block, blockIndex) in (column.blocks || [])" :key="block._uid">
                                                <div class="rounded-lg border bg-white px-3 py-2"
                                                     data-block-card
                                                     :id="'builder-block-' + block.id"
                                                     :class="[fieldErrors[block.id] && Object.keys(fieldErrors[block.id]).length
                                                         ? 'border-red-300 ring-1 ring-red-200'
                                                         : (block.is_active === false ? 'border-gray-200 opacity-60' : 'border-indigo-200'),
                                                         blockIsDragSource(region, rowIndex, columnIndex, blockIndex) ? 'opacity-40' : '',
                                                         dragBlock && dragBlockOver === blockDropKey(region, rowIndex, columnIndex, blockIndex) ? 'ring-2 ring-indigo-300' : '']"
                                                     @dragover.stop="blockDragOver($event, region, rowIndex, columnIndex, blockIndex)"
                                                     @drop.prevent.stop="dropBlock(region, rowIndex, columnIndex, blockIndex)">

                                                    {{-- Block: single-line header --}}
                                                    <div class="flex items-center gap-2">
                                                        <button type="button"
                                                                draggable="true"
                                                                @dragstart="startBlockDrag($event, region, rowIndex, columnIndex, blockIndex)"
                                                                @dragend="endBlockDrag()"
                                                                title="Sürükleyerek taşı"
                                                                class="cursor-grab active:cursor-grabbing text-gray-300 hover:text-gray-500 text-[10px] flex-shrink-0">
                                                            <i class="fas fa-grip-vertical"></i>
                                                        </button>
                                                        <span class="text-xs font-semibold text-gray-800 truncate flex-1 min-w-0"
                                                              x-text="block.template_name || block.type || 'Block'"></span>
                                                        <span class="rounded bg-gray-100 px-1.5 py-0.5 text-[10px] font-medium text-gray-500 flex-shrink-0"
                                                              x-text="block.type"></span>
                                                        <span x-show="block.render_mode && block.render_mode !== 'html'"
                                                              class="rounded bg-blue-100 px-1.5 py-0.5 text-[10px] font-medium text-blue-600 flex-shrink-0"
                                                              x-text="block.render_mode"></span>
                                                        {{-- Error badge --}}
                                                        <template x-if="fieldErrors[block.id] && Object.keys(fieldErrors[block.id]).length">
                                                            <span class="rounded bg-red-100 px-1.5 py-0.5 text-[10px] font-medium text-red-600 flex-shrink-0"
                                                                  :title="Object.keys(fieldErrors[block.id]).join(', ') + ' alanında hata'">
                                                                <i class="fas fa-circle-exclamation mr-0.5"></i>
                                                                <span x-text="Object.keys(fieldErrors[block.id]).length + ' hata'"></span>
                                                            </span>
                                                        </template>

                                                        {{-- Block actions --}}
                                                        <div class="flex items-center gap-0.5 flex-shrink-0">
                                                            <button type="button"
                                                                    @click="block.is_active = !block.is_active"
                                                                    :title="block.is_active !== false ? 'Aktif' : 'Pasif'"
                                                                    class="rounded px-1 py-0.5 text-[11px] transition-colors"
                                                                    :class="block.is_active !== false ? 'text-green-500 hover:bg-green-50' : 'text-gray-300 hover:bg-gray-100'">
                                                                <i class="fas fa-circle-dot"></i>
                                                            </button>
                                                            <button type="button"
                                                                    @click="openBlockSettings(region, rowIndex, columnIndex, blockIndex)"
                                                                    title="Block ayarları"
                                                                    class="rounded px-1 py-0.5 text-[11px] text-indigo-500 hover:bg-indigo-50">
                                                                <i class="fas fa-cog"></i>
                                                            </button>
                                                            <template x-if="block.section_template_id">
                                                                <a :href="@js(url('admin/section-templates')) + '/' + block.section_template_id + '/edit'"
                                                                   target="_blank"
                                                                   title="Block şablonunu düzenle"
                                                                   class="rounded px-1 py-0.5 text-[11px] text-purple-400 hover:bg-purple-50">
                                                                    <i class="fas fa-pen-to-square"></i>
                                                                </a>
                                                            </template>
                                                            <button type="button"
                                                                    @click="duplicateBlock(region, rowIndex, columnIndex, blockIndex)"
                                                                    title="Çoğalt"
                                                                    class="rounded px-1 py-0.5 text-[11px] text-gray-400 hover:bg-gray-100">
                                                                <i class="fas fa-copy"></i>
                                                            </button>
                                                            <button type="button"
                                                                    @click="moveBlock(region, rowIndex, columnIndex, blockIndex, -1)"
                                                                    title="Yukarı taşı"
                                                                    class="rounded px-1 py-0.5 text-[11px] text-gray-400 hover:bg-gray-100">
                                                                <i class="fas fa-arrow-up"></i>
                                                            </button>
                                                            <button type="button"
                                                                    @click="moveBlock(region, rowIndex, columnIndex, blockIndex, 1)"
                                                                    title="Aşağı taşı"
                                                                    class="rounded px-1 py-0.5 text-[11px] text-gray-400 hover:bg-gray-100">
                                                                <i class="fas fa-arrow-down"></i>
                                                            </button>
                                                            <button type="button"
                                                                    @click="removeBlock(region, rowIndex, columnIndex, blockIndex)"
                                                                    title="Sil"
                                                                    class="rounded px-1 py-0.5 text-[11px] text-red-400 hover:bg-red-50">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>

                                                    {{-- Block summary (subtitle) --}}
                                                    <div class="mt-0.5 ml-4 text-[11px] text-gray-400 truncate"
                                                         x-text="blockSummary(block)"
                                                         x-show="blockSummary(block) !== 'Hazır alanlar bu kartın içinde düzenlenir.'"></div>

                                                    {{-- article-list uyarısı --}}
                                                    <div x-show="block.type === 'article-list'"
                                                         class="mt-1.5 rounded bg-amber-50 px-2 py-1 text-[10px] text-amber-700">
                                                        <i class="fas fa-circle-info mr-1"></i>Site içindeki yazılardan kart üretir.
                                                    </div>
                                                </div>
                                            </template>

                                            {{-- Block Ekle — büyük boşken, küçük doluyken --}}
                                            <button type="button"
                                                    @click="openBlockPicker(region, rowIndex, columnIndex)"
                                                    class="flex w-full items-center justify-center gap-1 rounded-lg border border-dashed text-gray-400 hover:border-indigo-300 hover:text-indigo-600 transition-colors"
                                                    :class="(column.blocks || []).length === 0
                                                        ? 'border-gray-300 py-5 text-sm bg-white'
                                                        : 'border-gray-200 py-1.5 text-[11px] bg-transparent'">
                                                <i class="fas fa-plus text-[10px]"></i>
                                                <span x-text="(column.blocks || []).length === 0 ? 'Block Ekle' : 'block ekle'"></span>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>

                        </div>
                    </template>
                </div>

            </section>
        </template>
    </div>

    @include('admin.pages._form.editor-modals')

    <input type="hidden" name="sections_json" x-ref="sectionsJsonInput" :value="serializedRegions">
    <input type="hidden" name="sections_json_dirty" x-ref="sectionsJsonDirtyInput" value="0">

    {{-- Ham JSON (collapsed, toggled from header button) --}}
    <div class="mt-4">
        <button type="button"
                @click="openFrontendJson = !openFrontendJson"
                class="inline-flex items-center gap-1.5 rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-200">
            <i class="fas fa-code"></i>
            <span x-text="openFrontendJson ? 'Ham JSON Gizle' : 'Ham JSON Göster'"></span>
        </button>
        <div x-show="openFrontendJson" class="mt-3">
            <div class="mb-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                Bu alan gelişmiş/teknik kullanım içindir.
            </div>
            <textarea x-model="serializedRegions"
                      rows="14"
                      class="w-full rounded-lg border border-gray-300 px-3 py-2 font-mono text-xs focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-200"></textarea>
        </div>
    </div>

</div>
@endif
