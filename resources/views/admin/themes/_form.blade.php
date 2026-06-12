@php
    $assetsValue = old('assets_json', json_encode($theme->assets_json ?? ['css' => [], 'js' => []], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    $cssPathsValue = old('css_paths', implode("\n", data_get($theme->assets_json, 'css', [])));
    $jsPathsValue = old('js_paths', implode("\n", data_get($theme->assets_json, 'js', [])));
    $tokensValue = old('tokens_json', json_encode($theme->tokens_json ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    $settingsSchemaValue = old('settings_schema_json', json_encode($theme->settings_schema_json ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
@endphp

<div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
    <div class="space-y-6">
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="text-base font-semibold text-gray-900">Temel Bilgiler</h3>
            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-gray-700">Tema Adı *</label>
                    <input type="text" name="name" required value="{{ old('name', $theme->name) }}"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Slug *</label>
                    <input type="text" name="slug" required value="{{ old('slug', $theme->slug) }}"
                           placeholder="porto-furniture"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Engine *</label>
                    <select name="engine" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-indigo-500">
                        <option value="nextjs-basic-html" @selected(old('engine', $theme->engine) === 'nextjs-basic-html')>nextjs-basic-html</option>
                        <option value="nextjs-component" @selected(old('engine', $theme->engine) === 'nextjs-component')>nextjs-component</option>
                        <option value="legacy-blade" @selected(old('engine', $theme->engine) === 'legacy-blade')>legacy-blade</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-gray-700">Açıklama</label>
                    <textarea name="description" rows="4"
                              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-indigo-500">{{ old('description', $theme->description) }}</textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-gray-700">Önizleme Görseli</label>
                    <input type="text" name="preview_image" value="{{ old('preview_image', $theme->preview_image) }}"
                           placeholder="https://..."
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-indigo-500">
                </div>
                <label class="flex items-center gap-2 rounded-lg bg-gray-50 px-3 py-2 md:col-span-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $theme->is_active ?? true))
                           class="h-4 w-4 rounded border-gray-300 text-indigo-600">
                    <span class="text-sm text-gray-700">Aktif</span>
                </label>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="text-base font-semibold text-gray-900">Tema Verileri</h3>
            <div class="mt-4 space-y-4">
                <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                    @if(tenancy()->initialized)
                        <strong>Tenant storage aktif:</strong> Yüklenen CSS/JS dosyaları seçili sitenin storage alanına kaydedilir ve <code>/tenant-assets/...</code> yolu ile temaya eklenir.
                    @else
                        <strong>Genel storage aktif:</strong> Aktif tenant yok. Yüklenen CSS/JS dosyaları merkezi storage alanına kaydedilir ve <code>/storage/...</code> yolu ile temaya eklenir.
                    @endif
                </div>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">CSS Dosyası Yükle</label>
                        <input type="file" name="css_files[]" multiple accept=".css,text/css"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-indigo-50 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-indigo-700 hover:file:bg-indigo-100">
                        <p class="mt-1 text-xs text-gray-500">Birden fazla <code>.css</code> dosyası seçebilirsin. Kaydedilince CSS yollarına eklenir.</p>
                        @error('css_files.*')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">JS Dosyası Yükle</label>
                        <input type="file" name="js_files[]" multiple accept=".js,text/javascript,application/javascript"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-indigo-50 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-indigo-700 hover:file:bg-indigo-100">
                        <p class="mt-1 text-xs text-gray-500">Birden fazla <code>.js</code> dosyası seçebilirsin. Kaydedilince JS yollarına eklenir.</p>
                        @error('js_files.*')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">CSS Yolları</label>
                    <textarea name="css_paths" rows="6"
                              placeholder="/themes/porto/theme.css&#10;/themes/porto/custom.css"
                              class="w-full rounded-lg border border-gray-300 px-3 py-2 font-mono text-xs focus:border-transparent focus:ring-2 focus:ring-indigo-500">{{ $cssPathsValue }}</textarea>
                    <p class="mt-1 text-xs text-gray-500">Her satıra bir CSS dosya yolu yaz.</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">JS Yolları</label>
                    <textarea name="js_paths" rows="6"
                              placeholder="/themes/porto/theme.js&#10;/themes/porto/carousel.js"
                              class="w-full rounded-lg border border-gray-300 px-3 py-2 font-mono text-xs focus:border-transparent focus:ring-2 focus:ring-indigo-500">{{ $jsPathsValue }}</textarea>
                    <p class="mt-1 text-xs text-gray-500">Her satıra bir JS dosya yolu yaz.</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Assets JSON (Gelişmiş)</label>
                    <textarea name="assets_json" rows="12"
                              class="w-full rounded-lg border border-gray-300 px-3 py-2 font-mono text-xs focus:border-transparent focus:ring-2 focus:ring-indigo-500">{{ $assetsValue }}</textarea>
                    <p class="mt-1 text-xs text-gray-500">İstersen CSS/JS yollarını doğrudan JSON olarak da düzenleyebilirsin. Satır alanları doluysa kaydetmede bunlar öncelikli alınır.</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Tokens JSON</label>
                    <textarea name="tokens_json" rows="12"
                              class="w-full rounded-lg border border-gray-300 px-3 py-2 font-mono text-xs focus:border-transparent focus:ring-2 focus:ring-indigo-500">{{ $tokensValue }}</textarea>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Settings Schema JSON</label>
                    <textarea name="settings_schema_json" rows="10"
                              class="w-full rounded-lg border border-gray-300 px-3 py-2 font-mono text-xs focus:border-transparent focus:ring-2 focus:ring-indigo-500">{{ $settingsSchemaValue }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="rounded-xl border border-sky-200 bg-sky-50 p-6 shadow-sm">
            <h3 class="text-base font-semibold text-sky-900">Alan Açıklamaları</h3>
            <div class="mt-3 space-y-3 text-sm text-sky-900">
                <p><strong>Tema Adı:</strong> Panelde göreceğin kullanıcı dostu isimdir.</p>
                <p><strong>Slug:</strong> Teknik anahtar değeridir. Block şablonları ve site atamalarında kullanılır.</p>
                <p><strong>Engine:</strong> Temanın hangi render mantığında çalışacağını belirtir. Faz 1 için çoğunlukla <code>nextjs-basic-html</code> kullanılır.</p>
                <p><strong>CSS / JS Yolları:</strong> Tema asset dosyalarını satır satır girmek için en kolay alandır.</p>
                <p><strong>Assets JSON:</strong> Aynı verinin gelişmiş sürümüdür. Örn: <code>{ "css": ["/themes/porto/theme.css"], "js": ["/themes/porto/theme.js"] }</code></p>
                <p><strong>Tokens JSON:</strong> Renk, radius, container width gibi tasarım token'larını tutar.</p>
                <p><strong>Settings Schema JSON:</strong> Tema ayar ekranında hangi alanların çıkacağını tanımlar.</p>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-indigo-700">
                <i class="fas fa-save"></i> Kaydet
            </button>
            <a href="{{ route('admin.themes.index') }}" class="inline-flex items-center gap-2 rounded-lg bg-gray-100 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-200">
                İptal
            </a>
        </div>
    </div>
</div>
