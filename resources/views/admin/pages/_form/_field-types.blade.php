{{--
    Unified field-type renderer
    Must be included inside:  <div x-data="blockFieldInput(parentRef, fieldKey, fieldSchema)">
    Available from component:  type, parentRef, fieldKey, fieldSchema
    Field value binding:       parentRef[fieldKey]
--}}

{{-- text / string (default / unknown type) --}}
<template x-if="!['url','email','color','textarea','number','boolean','select','enum','image','media_id','rich-text','html','icon','page_link'].includes(type)">
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

{{-- image / media_id --}}
<template x-if="['image', 'media_id'].includes(type)">
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

        {{-- Media Picker Modal (teleported to body to escape stacking-context issues) --}}
        <template x-teleport="body">
            <div x-show="mediaPickerOpen"
                 x-cloak
                 class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 p-4"
                 @@click.self="closeMediaPicker()"
                 @@keydown.escape.window="if (mediaPickerOpen) { closeMediaPicker(); $event.stopPropagation(); }">
                <div class="flex w-full max-w-2xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl"
                     style="max-height: calc(100vh - 4rem)">

                    {{-- Header --}}
                    <div class="flex items-center gap-3 border-b border-gray-100 px-5 py-3">
                        <h5 class="flex-1 text-sm font-semibold text-gray-900">Medya Seç</h5>
                        <div class="relative">
                            <i class="fas fa-magnifying-glass pointer-events-none absolute left-2.5 top-1/2 -translate-y-1/2 text-xs text-gray-400"></i>
                            <input type="text"
                                   x-model="mediaSearch"
                                   placeholder="Dosya adı ara…"
                                   class="w-52 rounded-lg border border-gray-300 py-1.5 pl-8 pr-3 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        {{-- Yükle: yeni görsel seç + doğrudan alana ata --}}
                        <label class="inline-flex shrink-0 cursor-pointer items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-700"
                               :class="mediaUploading ? 'pointer-events-none opacity-60' : ''">
                            <i class="fas" :class="mediaUploading ? 'fa-spinner fa-spin' : 'fa-upload'"></i>
                            <span x-text="mediaUploading ? 'Yükleniyor…' : 'Yükle'"></span>
                            <input type="file" class="hidden" accept="image/*" @@change="uploadMedia($event)">
                        </label>
                        <button type="button"
                                @@click="closeMediaPicker()"
                                class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                            <i class="fas fa-xmark"></i>
                        </button>
                    </div>

                    {{-- Yükleme hatası --}}
                    <template x-if="mediaUploadError">
                        <div class="border-b border-red-100 bg-red-50 px-5 py-2 text-xs text-red-700"
                             x-text="mediaUploadError"></div>
                    </template>

                    {{-- Grid --}}
                    <div class="flex-1 overflow-y-auto p-4">
                        <template x-if="mediaLoading">
                            <div class="flex items-center justify-center py-14">
                                <i class="fas fa-spinner fa-spin text-3xl text-gray-300"></i>
                            </div>
                        </template>

                        <template x-if="!mediaLoading && filteredMedia().length === 0">
                            <div class="py-14 text-center text-sm text-gray-400">
                                <i class="fas fa-images mb-2 block text-2xl text-gray-200"></i>
                                Medya bulunamadı.
                            </div>
                        </template>

                        <template x-if="!mediaLoading && filteredMedia().length > 0">
                            <div class="grid grid-cols-3 gap-2 sm:grid-cols-4 md:grid-cols-5">
                                <template x-for="m in filteredMedia()" :key="m.id">
                                    <button type="button"
                                            @@click="selectMedia(m)"
                                            class="group relative aspect-square overflow-hidden rounded-lg border border-gray-200 bg-gray-100 hover:border-indigo-400 hover:ring-2 hover:ring-indigo-200 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                        <img :src="m.thumbnail_url || m.url"
                                             :alt="m.file_name || m.name"
                                             class="h-full w-full object-cover"
                                             @@error="$el.style.opacity = '0.3'">
                                        <div class="absolute inset-x-0 bottom-0 truncate bg-black/50 px-1 py-0.5 text-[10px] leading-tight text-white opacity-0 transition-opacity group-hover:opacity-100"
                                             x-text="m.file_name || m.name"></div>
                                    </button>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

{{-- rich-text / html (Quill WYSIWYG) --}}
<template x-if="['rich-text', 'html'].includes(type)">
    <div class="overflow-hidden rounded-lg border border-gray-300">
        <div x-init="initQuill($el)"
             style="min-height: 140px;"
             class="bg-white text-sm"></div>
    </div>
</template>
