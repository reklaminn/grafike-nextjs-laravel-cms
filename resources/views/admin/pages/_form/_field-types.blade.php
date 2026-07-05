{{--
    Unified field-type renderer
    Must be included inside:  <div x-data="blockFieldInput(parentRef, fieldKey, fieldSchema)">
    Available from component:  type, parentRef, fieldKey, fieldSchema
    Field value binding:       parentRef[fieldKey]
--}}

{{-- text / string (default / unknown type) --}}
<template x-if="!['url','email','color','textarea','number','boolean','select','enum','image','media_id','media_url','rich-text','html','icon','page_link'].includes(type)">
    <input type="text"
           x-model="parentRef[fieldKey]"
           :placeholder="fieldSchema.placeholder || ''"
           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-200">
</template>

{{-- icon (Font Awesome görsel seçici) --}}
<template x-if="type === 'icon'">
    <div class="space-y-2">
        <div class="flex gap-2">
            <div class="relative flex-1">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex w-9 items-center justify-center text-gray-500">
                    <i :class="iconClass(parentRef[fieldKey]) || 'fas fa-icons text-gray-300'" class="text-sm"></i>
                </span>
                <input type="text"
                       x-model="parentRef[fieldKey]"
                       placeholder="fa-star"
                       class="w-full rounded-lg border border-gray-300 py-2 pl-9 pr-3 font-mono text-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-200">
            </div>
            <button type="button"
                    @@click="openIconPicker()"
                    class="inline-flex shrink-0 items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-medium text-gray-600 hover:bg-gray-50 active:bg-gray-100">
                <i class="fas fa-icons"></i> Seç
            </button>
        </div>

        {{-- Icon Picker Modal (teleported to body) --}}
        <template x-teleport="body">
            <div x-show="iconPickerOpen"
                 x-cloak
                 class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 p-4"
                 @@click.self="closeIconPicker()"
                 @@keydown.escape.window="if (iconPickerOpen) { closeIconPicker(); $event.stopPropagation(); }">
                <div class="flex w-full max-w-lg flex-col overflow-hidden rounded-2xl bg-white shadow-2xl"
                     style="max-height: calc(100vh - 4rem)">
                    {{-- Header --}}
                    <div class="flex items-center gap-3 border-b border-gray-100 px-5 py-3">
                        <h5 class="flex-1 text-sm font-semibold text-gray-900">İkon Seç</h5>
                        <div class="relative">
                            <i class="fas fa-magnifying-glass pointer-events-none absolute left-2.5 top-1/2 -translate-y-1/2 text-xs text-gray-400"></i>
                            <input type="text"
                                   x-model="iconSearch"
                                   placeholder="İkon ara (star, phone…)"
                                   class="w-52 rounded-lg border border-gray-300 py-1.5 pl-8 pr-3 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <button type="button"
                                @@click="closeIconPicker()"
                                class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                            <i class="fas fa-xmark"></i>
                        </button>
                    </div>
                    {{-- Grid --}}
                    <div class="flex-1 overflow-y-auto p-4">
                        <template x-if="filteredIcons().length === 0">
                            <div class="py-14 text-center text-sm text-gray-400">
                                <i class="fas fa-icons mb-2 block text-2xl text-gray-200"></i>
                                İkon bulunamadı.
                            </div>
                        </template>
                        <div class="grid grid-cols-6 gap-2 sm:grid-cols-8">
                            <template x-for="ic in filteredIcons()" :key="ic">
                                <button type="button"
                                        @@click="selectIcon(ic)"
                                        :title="ic"
                                        class="flex aspect-square items-center justify-center rounded-lg border text-gray-600 hover:border-indigo-400 hover:bg-indigo-50 hover:text-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                        :class="iconValueMatches(ic) ? 'border-indigo-400 bg-indigo-50 text-indigo-600' : 'border-gray-200'">
                                    <i :class="'fas ' + ic"></i>
                                </button>
                            </template>
                        </div>
                    </div>
                    {{-- Footer: clear --}}
                    <div class="flex items-center justify-between border-t border-gray-100 px-5 py-2.5">
                        <span class="text-xs text-gray-400" x-text="filteredIcons().length + ' ikon'"></span>
                        <button type="button"
                                @@click="selectIcon('')"
                                class="rounded-lg px-3 py-1.5 text-xs font-medium text-gray-500 hover:bg-gray-100">
                            <i class="fas fa-eraser mr-1"></i> Temizle
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

{{-- page_link (iç sayfa seçici + serbest URL) --}}
<template x-if="type === 'page_link'">
    <div class="space-y-2" x-init="ensurePagesLoaded()">
        <div class="flex gap-2">
            <div class="relative flex-1">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex w-9 items-center justify-center text-gray-400">
                    <i class="fas fa-link text-xs"></i>
                </span>
                <input type="text"
                       x-model="parentRef[fieldKey]"
                       placeholder="/tr/hizmetler veya https://"
                       class="w-full rounded-lg border border-gray-300 py-2 pl-9 pr-3 text-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-200">
            </div>
            <button type="button"
                    @@click="openPagePicker()"
                    class="inline-flex shrink-0 items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-medium text-gray-600 hover:bg-gray-50 active:bg-gray-100">
                <i class="fas fa-sitemap"></i> Sayfa
            </button>
        </div>

        {{-- Seçili sayfa rozeti --}}
        <template x-if="matchedPageTitle(parentRef[fieldKey])">
            <p class="flex items-center gap-1.5 text-xs text-gray-500">
                <i class="fas fa-circle-check text-green-500"></i>
                <span x-text="matchedPageTitle(parentRef[fieldKey])"></span>
            </p>
        </template>

        {{-- Page Picker Modal (teleported to body) --}}
        <template x-teleport="body">
            <div x-show="pagePickerOpen"
                 x-cloak
                 class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 p-4"
                 @@click.self="closePagePicker()"
                 @@keydown.escape.window="if (pagePickerOpen) { closePagePicker(); $event.stopPropagation(); }">
                <div class="flex w-full max-w-lg flex-col overflow-hidden rounded-2xl bg-white shadow-2xl"
                     style="max-height: calc(100vh - 4rem)">
                    {{-- Header --}}
                    <div class="flex items-center gap-3 border-b border-gray-100 px-5 py-3">
                        <h5 class="flex-1 text-sm font-semibold text-gray-900">Sayfa Seç</h5>
                        <div class="relative">
                            <i class="fas fa-magnifying-glass pointer-events-none absolute left-2.5 top-1/2 -translate-y-1/2 text-xs text-gray-400"></i>
                            <input type="text"
                                   x-model="pageSearch"
                                   placeholder="Sayfa ara…"
                                   class="w-52 rounded-lg border border-gray-300 py-1.5 pl-8 pr-3 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <button type="button"
                                @@click="closePagePicker()"
                                class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                            <i class="fas fa-xmark"></i>
                        </button>
                    </div>
                    {{-- List --}}
                    <div class="flex-1 overflow-y-auto p-3">
                        <template x-if="pagesLoading">
                            <div class="flex items-center justify-center py-14">
                                <i class="fas fa-spinner fa-spin text-3xl text-gray-300"></i>
                            </div>
                        </template>
                        <template x-if="!pagesLoading && filteredPages().length === 0">
                            <div class="py-14 text-center text-sm text-gray-400">
                                <i class="fas fa-sitemap mb-2 block text-2xl text-gray-200"></i>
                                Yayınlanmış sayfa bulunamadı.
                            </div>
                        </template>
                        <div class="space-y-1">
                            <template x-for="pg in filteredPages()" :key="pg.id">
                                <button type="button"
                                        @@click="selectPage(pg)"
                                        class="flex w-full items-center gap-3 rounded-lg border border-gray-100 px-3 py-2 text-left hover:border-indigo-300 hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-300">
                                    <i class="fas fa-file-lines text-gray-300"></i>
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-sm font-medium text-gray-800" x-text="pg.title"></span>
                                        <span class="block truncate font-mono text-[11px] text-gray-400" x-text="pg.path"></span>
                                    </span>
                                    <span x-show="pg.language" class="shrink-0 rounded bg-gray-100 px-1.5 py-0.5 text-[10px] font-medium text-gray-500" x-text="pg.locale"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

{{-- url --}}
<template x-if="type === 'url'">
    <div class="relative">
        <span class="pointer-events-none absolute inset-y-0 left-0 flex w-9 items-center justify-center text-gray-400">
            <i class="fas fa-link text-xs"></i>
        </span>
        <input type="url"
               x-model="parentRef[fieldKey]"
               placeholder="https://"
               class="w-full rounded-lg border border-gray-300 py-2 pl-9 pr-3 text-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-200">
    </div>
</template>

{{-- email --}}
<template x-if="type === 'email'">
    <div class="relative">
        <span class="pointer-events-none absolute inset-y-0 left-0 flex w-9 items-center justify-center text-gray-400">
            <i class="fas fa-envelope text-xs"></i>
        </span>
        <input type="email"
               x-model="parentRef[fieldKey]"
               placeholder="ornek@domain.com"
               class="w-full rounded-lg border border-gray-300 py-2 pl-9 pr-3 text-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-200">
    </div>
</template>

{{-- color --}}
<template x-if="type === 'color'">
    <div class="flex items-center gap-2">
        <input type="color"
               x-model="parentRef[fieldKey]"
               class="h-9 w-12 cursor-pointer rounded-lg border border-gray-300 bg-white p-0.5">
        <input type="text"
               x-model="parentRef[fieldKey]"
               placeholder="#000000"
               class="flex-1 rounded-lg border border-gray-300 px-3 py-2 font-mono text-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-200">
    </div>
</template>

{{-- textarea --}}
<template x-if="type === 'textarea'">
    <textarea x-model="parentRef[fieldKey]"
              rows="4"
              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-200"></textarea>
</template>

{{-- number --}}
<template x-if="type === 'number'">
    <input type="number"
           x-model="parentRef[fieldKey]"
           :min="fieldSchema.min ?? ''"
           :max="fieldSchema.max ?? ''"
           :step="fieldSchema.step || 'any'"
           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-200">
</template>

{{-- boolean --}}
<template x-if="type === 'boolean'">
    <label class="flex cursor-pointer items-center gap-2 rounded-lg bg-gray-50 px-3 py-2 text-sm text-gray-700">
        <input type="checkbox"
               x-model="parentRef[fieldKey]"
               class="h-4 w-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500">
        <span x-text="parentRef[fieldKey] ? 'Aktif (true)' : 'Pasif (false)'" class="select-none"></span>
    </label>
</template>

{{-- select / enum --}}
<template x-if="['select', 'enum'].includes(type)">
    <select x-model="parentRef[fieldKey]"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-200">
        <option value="">— Seçin —</option>
        <template x-for="opt in (Array.isArray(fieldSchema.options) ? fieldSchema.options : [])"
                  :key="(opt !== null && typeof opt === 'object') ? opt.value : opt">
            <option :value="(opt !== null && typeof opt === 'object') ? opt.value : opt"
                    x-text="(opt !== null && typeof opt === 'object') ? (opt.label || opt.value) : opt"></option>
        </template>
    </select>
</template>

{{-- image / media_id / media_url — hepsi URL saklar + medya kütüphanesi seçici (repeater içinde de parentRef[fieldKey] ile çalışır) --}}
<template x-if="['image', 'media_id', 'media_url'].includes(type)">
    <div class="space-y-2">
        <div class="flex gap-2">
            <input type="url"
                   x-model="parentRef[fieldKey]"
                   placeholder="https://…"
                   class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-200">
            <button type="button"
                    @@click="openMediaPicker()"
                    class="inline-flex shrink-0 items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-medium text-gray-600 hover:bg-gray-50 active:bg-gray-100">
                <i class="fas fa-images"></i> Seç
            </button>
        </div>

        <template x-if="parentRef[fieldKey]">
            <img :src="parentRef[fieldKey]"
                 alt=""
                 class="h-20 w-full rounded-lg border border-gray-200 object-cover object-center"
                 @@error="$el.style.display = 'none'">
        </template>
    </div>
</template>

{{-- Media Picker Modal — ÜST SEVİYE teleport (image x-if'in DIŞINDA olmalı).
     Alpine v3'te x-if içine yuvalanmış x-teleport içeriğindeki @change/@click
     event'leri güvenilir bağlanmıyor → dosya "Yükle" tıklaması/seçimi hiç istek
     atmıyordu. Bileşen kökünde durup x-show="mediaPickerOpen" + openMediaPicker()
     ile kontrol edilir; image olmayan alanlarda asla açılmaz (zararsız gizli). --}}

{{-- Modal artık TELEPORT EDİLMİYOR (inline). Teleport, file-input'un @change'ini
     bozuyordu (4 deneme). Üst zincirde transform/filter YOK → position:fixed
     viewport'a göre konumlanır ve overflow-hidden onu KIRPMAZ → inline güvenli;
     böylece @click VE @change normal DOM'da güvenilir çalışır. --}}
            <div x-show="mediaPickerOpen"
                 x-cloak
                 class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 p-4"
                 @@click.self="closeMediaPicker()"
                 @@keydown.escape.window="if (mediaPickerOpen) { closeMediaPicker(); $event.stopPropagation(); }"
                 @@keydown="onMediaKey($event)"
                 @@paste="onMediaPaste($event)">
                <div class="flex w-full max-w-5xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl"
                     style="max-height: calc(100vh - 3rem)">

                    {{-- Header / filtre bar --}}
                    <div class="flex flex-wrap items-center gap-2 border-b border-gray-100 px-5 py-3">
                        <h5 class="mr-1 text-sm font-semibold text-gray-900">Medya Kütüphanesi</h5>
                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[11px] text-gray-500"
                              x-text="mediaTotal + ' görsel'"></span>

                        <div class="relative ml-auto">
                            <i class="fas fa-magnifying-glass pointer-events-none absolute left-2.5 top-1/2 -translate-y-1/2 text-xs text-gray-400"></i>
                            <input type="text"
                                   x-model="mediaSearch"
                                   @@input="onMediaSearchInput()"
                                   placeholder="Dosya adı ara…"
                                   class="w-48 rounded-lg border border-gray-300 py-1.5 pl-8 pr-3 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>

                        {{-- Collection / klasör filtresi --}}
                        <template x-if="mediaCollections.length > 0">
                            <select @@change="setMediaCollection($event.target.value)"
                                    class="rounded-lg border border-gray-300 py-1.5 pl-2 pr-7 text-xs text-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">Tüm klasörler</option>
                                <template x-for="c in mediaCollections" :key="c">
                                    <option :value="c" x-text="c"></option>
                                </template>
                            </select>
                        </template>

                        <label class="inline-flex shrink-0 cursor-pointer items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-700"
                               :class="mediaUploading ? 'pointer-events-none opacity-60' : ''">
                            <i class="fas" :class="mediaUploading ? 'fa-spinner fa-spin' : 'fa-upload'"></i>
                            <span x-text="mediaUploading ? 'Yükleniyor…' : 'Yükle'"></span>
                            <input type="file" class="hidden" accept="image/*" multiple @@change="onMediaFileInput($event)">
                        </label>
                        <button type="button"
                                @@click="closeMediaPicker()"
                                class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                            <i class="fas fa-xmark"></i>
                        </button>
                    </div>

                    {{-- Yükleme hatası --}}
                    <template x-if="mediaUploadError">
                        <div class="flex items-center gap-2 border-b border-red-100 bg-red-50 px-5 py-2 text-xs text-red-700">
                            <i class="fas fa-triangle-exclamation"></i>
                            <span class="flex-1" x-text="mediaUploadError"></span>
                            <button type="button" @@click="mediaUploadError = ''" class="text-red-400 hover:text-red-600"><i class="fas fa-xmark"></i></button>
                        </div>
                    </template>

                    {{-- Gövde: sol grid + sağ detay --}}
                    <div class="flex min-h-0 flex-1">
                        {{-- Sol: grid + drag-drop --}}
                        <div class="relative min-h-0 flex-1 overflow-y-auto p-4"
                             @@scroll.passive="onMediaScroll($event)"
                             @@dragover.prevent="mediaDragOver = true"
                             @@dragleave.prevent="mediaDragOver = false"
                             @@drop.prevent="onMediaDrop($event)">

                            {{-- Drag overlay --}}
                            <div x-show="mediaDragOver" x-cloak
                                 class="pointer-events-none absolute inset-2 z-10 flex items-center justify-center rounded-xl border-2 border-dashed border-indigo-400 bg-indigo-50/90 text-sm font-medium text-indigo-600">
                                <span><i class="fas fa-cloud-arrow-up mr-1"></i> Görselleri buraya bırakın</span>
                            </div>

                            <template x-if="mediaLoading">
                                <div class="flex items-center justify-center py-14">
                                    <i class="fas fa-spinner fa-spin text-3xl text-gray-300"></i>
                                </div>
                            </template>

                            {{-- Boş durum + yükleme CTA --}}
                            <template x-if="!mediaLoading && mediaItems.length === 0">
                                <label class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-gray-200 py-16 text-center text-sm text-gray-400 hover:border-indigo-300 hover:text-indigo-500">
                                    <i class="fas fa-cloud-arrow-up text-3xl text-gray-300"></i>
                                    <span x-text="mediaSearch ? 'Aramayla eşleşen görsel yok.' : 'Henüz görsel yok — yüklemek için tıklayın veya sürükleyin.'"></span>
                                    <input type="file" class="hidden" accept="image/*" multiple @@change="onMediaFileInput($event)">
                                </label>
                            </template>

                            <template x-if="!mediaLoading && mediaItems.length > 0">
                                <div>
                                    <div class="grid grid-cols-3 gap-2 sm:grid-cols-4 md:grid-cols-5">
                                        <template x-for="m in mediaItems" :key="m.id">
                                            <button type="button"
                                                    :id="'media-cell-' + m.id"
                                                    @@click="onItemClick(m)"
                                                    @@dblclick="mediaMultiple ? null : useMedia(m)"
                                                    class="group flex flex-col overflow-hidden rounded-lg border bg-white text-left focus:outline-none"
                                                    :class="(mediaMultiple ? isChosen(m) : (mediaActive && mediaActive.url === m.url)) ? 'border-indigo-500 ring-2 ring-indigo-300' : 'border-gray-200 hover:border-indigo-300'">
                                                <div class="relative aspect-square w-full overflow-hidden bg-gray-100">
                                                    <img :src="m.thumbnail_url || m.url"
                                                         :alt="m.file_name || m.name"
                                                         class="h-full w-full object-cover"
                                                         @@load="captureDims(m, $el)"
                                                         @@error="$el.style.opacity = '0.3'">
                                                    {{-- Çoklu seçim onay rozeti --}}
                                                    <template x-if="mediaMultiple && isChosen(m)">
                                                        <span class="absolute right-1 top-1 flex h-5 w-5 items-center justify-center rounded-full bg-indigo-600 text-[10px] text-white"><i class="fas fa-check"></i></span>
                                                    </template>
                                                </div>
                                                {{-- Dosya adı — küçük resmin ALTINDA sabit (eskiden hover overlay'di) --}}
                                                <span class="block w-full truncate px-1.5 py-1 text-[11px] leading-tight text-gray-600"
                                                      :title="m.file_name || m.name"
                                                      x-text="m.file_name || m.name"></span>
                                            </button>
                                        </template>
                                    </div>

                                    {{-- Daha fazla yükleniyor / yükle --}}
                                    <div class="py-4 text-center">
                                        <template x-if="mediaLoadingMore">
                                            <i class="fas fa-spinner fa-spin text-gray-300"></i>
                                        </template>
                                        <template x-if="!mediaLoadingMore && mediaHasMore">
                                            <button type="button" @@click="loadMedia({ reset: false })"
                                                    class="text-xs text-indigo-600 hover:underline">Daha fazla göster</button>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Sağ: detay paneli (tekli modda aktif öğe) --}}
                        <template x-if="!mediaMultiple && mediaActive">
                            <div class="hidden w-72 shrink-0 flex-col overflow-y-auto border-l border-gray-100 bg-gray-50 p-4 md:flex">
                                <div class="mb-3 overflow-hidden rounded-lg border border-gray-200 bg-white">
                                    <img :src="mediaActive.url" :alt="mediaActive.name"
                                         class="max-h-44 w-full object-contain">
                                </div>

                                {{-- Metadata --}}
                                <dl class="mb-3 space-y-1 text-[11px] text-gray-500">
                                    <div class="flex justify-between gap-2">
                                        <dt>Dosya</dt>
                                        <dd class="truncate text-right text-gray-700" x-text="mediaActive.file_name || mediaActive.name"></dd>
                                    </div>
                                    <div class="flex justify-between gap-2" x-show="mediaActive.width">
                                        <dt>Ölçü</dt>
                                        <dd class="text-gray-700"><span x-text="mediaActive.width"></span>×<span x-text="mediaActive.height"></span> px</dd>
                                    </div>
                                    <div class="flex justify-between gap-2" x-show="mediaActive.size">
                                        <dt>Boyut</dt>
                                        <dd class="text-gray-700" x-text="formatSize(mediaActive.size)"></dd>
                                    </div>
                                    <div class="flex justify-between gap-2" x-show="mediaActive.created_at">
                                        <dt>Tarih</dt>
                                        <dd class="text-gray-700" x-text="mediaActive.created_at"></dd>
                                    </div>
                                </dl>

                                {{-- Ad düzenle --}}
                                <label class="mb-0.5 block text-[11px] font-medium text-gray-600">Ad</label>
                                <input type="text" x-model="mediaNameDraft"
                                       class="mb-1.5 w-full rounded-lg border border-gray-300 px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                {{-- Dosya adını URL-güvenli (slug) yap: file_name + disk taşıma + linkleri güncelle --}}
                                <button type="button" @@click="renameMediaFile()"
                                        :disabled="mediaRenaming || String(mediaActive.id).startsWith('up_')"
                                        class="mb-2 inline-flex w-full items-center justify-center gap-1 rounded-lg border border-indigo-200 bg-indigo-50 px-2.5 py-1.5 text-[11px] font-medium text-indigo-700 hover:bg-indigo-100 disabled:opacity-50"
                                        title="Dosya adını yukarıdaki 'Ad'a göre URL-güvenli (slug) yapar, diski taşır ve bu görseli kullanan sayfa/yazı linklerini otomatik günceller">
                                    <i class="fas" :class="mediaRenaming ? 'fa-spinner fa-spin' : 'fa-link'"></i> Dosya adını URL-güvenli yap
                                </button>

                                {{-- Alt metni + AI --}}
                                <div class="mb-0.5 flex items-center justify-between">
                                    <label class="text-[11px] font-medium text-gray-600">Alt metni (SEO)</label>
                                    <button type="button" @@click="generateActiveAlt()"
                                            :disabled="mediaGenAltLoading || String(mediaActive.id).startsWith('up_')"
                                            class="inline-flex items-center gap-1 text-[10px] text-indigo-600 hover:underline disabled:opacity-50">
                                        <i class="fas" :class="mediaGenAltLoading ? 'fa-spinner fa-spin' : 'fa-wand-magic-sparkles'"></i> AI üret
                                    </button>
                                </div>
                                <textarea x-model="mediaAltDraft" rows="2"
                                          placeholder="Görseli betimleyen kısa metin"
                                          class="mb-2 w-full rounded-lg border border-gray-300 px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>

                                <div class="mb-3 flex items-center gap-2">
                                    <button type="button" @@click="saveMediaMeta()"
                                            :disabled="mediaSavingMeta || String(mediaActive.id).startsWith('up_')"
                                            class="inline-flex items-center gap-1 rounded-lg bg-gray-200 px-2.5 py-1.5 text-[11px] font-medium text-gray-700 hover:bg-gray-300 disabled:opacity-50">
                                        <i class="fas" :class="mediaSavingMeta ? 'fa-spinner fa-spin' : 'fa-floppy-disk'"></i> Bilgileri kaydet
                                    </button>
                                    <button type="button" @@click="deleteMedia(mediaActive)"
                                            class="ml-auto inline-flex items-center gap-1 rounded-lg px-2 py-1.5 text-[11px] text-red-500 hover:bg-red-50">
                                        <i class="fas fa-trash"></i> Sil
                                    </button>
                                </div>

                                <button type="button" @@click="useMedia(mediaActive)"
                                        class="mt-auto w-full rounded-lg bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                                    <i class="fas fa-check mr-1"></i> Bu Görseli Kullan
                                </button>
                            </div>
                        </template>
                    </div>

                    {{-- Footer: çoklu seçim onayı --}}
                    <template x-if="mediaMultiple">
                        <div class="flex items-center justify-between gap-2 border-t border-gray-100 px-5 py-3">
                            <span class="text-xs text-gray-500" x-text="mediaChosen.length + ' görsel seçildi'"></span>
                            <button type="button" @@click="confirmSelection()"
                                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                                <i class="fas fa-check mr-1"></i> Seçilenleri Kullan
                            </button>
                        </div>
                    </template>
                </div>
            </div>

{{-- rich-text / html (Quill WYSIWYG) --}}
<template x-if="['rich-text', 'html'].includes(type)">
    <div class="overflow-hidden rounded-lg border border-gray-300">
        <div x-init="initQuill($el)"
             style="min-height: 140px;"
             class="bg-white text-sm"></div>
    </div>
</template>
