{{-- Shared article form partial --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">

        {{-- ─── Temel Bilgiler ──────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-base font-semibold text-gray-800 mb-4">Temel Bilgiler</h3>
            <div class="space-y-4">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Yazı Başlığı *</label>
                    <input type="text" id="title" name="title" required
                           value="{{ old('title', $article->title ?? '') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
                <div>
                    <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">URL Slug</label>
                    <input type="text" id="slug" name="slug"
                           value="{{ old('slug', $article->slug ?? '') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           placeholder="otomatik-olusturulur">
                </div>
                <div>
                    <label for="excerpt" class="block text-sm font-medium text-gray-700 mb-1">Kısa Özet</label>
                    <textarea id="excerpt" name="excerpt" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    >{{ old('excerpt', $article->excerpt ?? '') }}</textarea>
                </div>
            </div>
        </div>

        {{-- ─── İçerik (Blok Editörü) ──────────────────────────────────── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-semibold text-gray-800">İçerik</h3>
                <span class="text-xs text-gray-400">Blok tabanlı editör</span>
            </div>
            @include('admin.articles._content-builder')
        </div>

        {{-- ─── Ek Bilgiler ─────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-base font-semibold text-gray-800 mb-4">Ek Bilgiler</h3>
            <textarea id="extra_info" name="extra_info" rows="4"
                      placeholder="Ek notlar, özel alan değerleri…"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
            >{{ old('extra_info', $article->extra_info ?? '') }}</textarea>
        </div>

        {{-- ─── Galeri ──────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6" x-data="{ showGallery: true }">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-semibold text-gray-800">
                    <i class="fas fa-images mr-2 text-purple-500"></i>Galeri
                </h3>
                @if(isset($article) && $article->getMedia('gallery')->count() > 0)
                    <span class="text-xs text-gray-400">{{ $article->getMedia('gallery')->count() }} görsel</span>
                @endif
            </div>

            {{-- Mevcut galeri görselleri --}}
            @if(isset($article) && $article->getMedia('gallery')->count() > 0)
                <div class="grid grid-cols-3 sm:grid-cols-4 gap-3 mb-4">
                    @foreach($article->getMedia('gallery') as $media)
                        <div class="relative group rounded-lg overflow-hidden border border-gray-200">
                            <img src="{{ $media->getUrl() }}"
                                 class="w-full aspect-square object-cover" alt="{{ $media->file_name }}">
                            <form method="POST"
                                  action="{{ route('admin.media.destroy', $media) }}"
                                  class="absolute inset-0 flex items-center justify-center bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        onclick="return confirm('Bu görseli silmek istediğinize emin misiniz?')"
                                        class="p-2 bg-red-600 text-white rounded-lg text-xs hover:bg-red-700">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Yeni Görsel Ekle (çoklu seçim)</label>
                <input type="file" name="gallery_images[]" multiple accept="image/*"
                       class="w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
                <p class="mt-1 text-xs text-gray-400">Max 5 MB / görsel. JPG, PNG, WebP desteklenir.</p>
            </div>
        </div>

        {{-- ─── SEO ─────────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6"
             x-data="{ open: {{ isset($article) && $article->seo ? 'true' : 'false' }} }">
            <button type="button" @click="open = !open" class="flex items-center justify-between w-full">
                <h3 class="text-base font-semibold text-gray-800">
                    <i class="fas fa-magnifying-glass mr-2 text-blue-500"></i>SEO Ayarları
                </h3>
                <i :class="open ? 'fa-chevron-up' : 'fa-chevron-down'" class="fas text-gray-400"></i>
            </button>
            <div x-show="open" x-collapse class="mt-4 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Meta Başlık</label>
                    <input type="text" name="seo_title"
                           value="{{ old('seo_title', $article->seo?->meta_title ?? '') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Meta Açıklama</label>
                    <textarea name="seo_description" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    >{{ old('seo_description', $article->seo?->meta_description ?? '') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Anahtar Kelimeler</label>
                    <input type="text" name="seo_keywords"
                           value="{{ old('seo_keywords', $article->seo?->meta_keywords ?? '') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
            </div>
        </div>

        {{-- ─── Custom CSS / JS ─────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6"
             x-data="{ open: {{ (isset($article) && ($article->custom_css || $article->custom_js)) ? 'true' : 'false' }} }">
            <button type="button" @click="open = !open" class="flex items-center justify-between w-full">
                <h3 class="text-base font-semibold text-gray-800">
                    <i class="fas fa-code mr-2 text-gray-400"></i>Özel CSS / JS
                </h3>
                <i :class="open ? 'fa-chevron-up' : 'fa-chevron-down'" class="fas text-gray-400"></i>
            </button>
            <div x-show="open" x-collapse class="mt-4 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Özel CSS</label>
                    <textarea name="custom_css" rows="5"
                              placeholder=".my-class { color: red; }"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs font-mono focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    >{{ old('custom_css', $article->custom_css ?? '') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Özel JS</label>
                    <textarea name="custom_js" rows="5"
                              placeholder="console.log('hello');"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs font-mono focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    >{{ old('custom_js', $article->custom_js ?? '') }}</textarea>
                </div>
            </div>
        </div>

    </div>{{-- /main column --}}

    {{-- ─── Sidebar ──────────────────────────────────────────────────────── --}}
    <div class="space-y-6">

        {{-- Yayın kutusu --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-base font-semibold text-gray-800 mb-4">Yayın</h3>
            <div class="space-y-4">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Durum *</label>
                    <select name="status" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                        <option value="draft"     {{ old('status', $article->status ?? 'draft') === 'draft'     ? 'selected' : '' }}>Taslak</option>
                        <option value="published" {{ old('status', $article->status ?? '') === 'published' ? 'selected' : '' }}>Yayında</option>
                        <option value="archived"  {{ old('status', $article->status ?? '') === 'archived'  ? 'selected' : '' }}>Arşivlenmiş</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Yayın Tarihi</label>
                    <input type="datetime-local" name="published_at"
                           value="{{ old('published_at', isset($article) && $article->published_at ? $article->published_at->format('Y-m-d\TH:i') : '') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Görüntülenecek Tarih</label>
                    <input type="text" name="display_date"
                           value="{{ old('display_date', $article->display_date ?? '') }}"
                           placeholder="ör. 15 Ocak 2025"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <p class="mt-1 text-xs text-gray-400">Frontend'de gösterilecek serbest biçim tarih metni.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dil *</label>
                    <select name="language_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                        @foreach($languages as $lang)
                            <option value="{{ $lang->id }}"
                                {{ old('language_id', $article->language_id ?? '') == $lang->id ? 'selected' : '' }}>
                                {{ $lang->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bağlı Sayfa</label>
                    <select name="page_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                        <option value="">— Seçiniz —</option>
                        @foreach($pages as $page)
                            <option value="{{ $page->id }}"
                                {{ old('page_id', $article->page_id ?? $selectedPageId ?? '') == $page->id ? 'selected' : '' }}>
                                {{ \Illuminate\Support\Str::limit($page->title, 40) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Yazar</label>
                    <select name="author_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                        <option value="">— Seçiniz —</option>
                        @foreach($admins as $admin)
                            <option value="{{ $admin->id }}"
                                {{ old('author_id', $article->author_id ?? auth('admin')->id()) == $admin->id ? 'selected' : '' }}>
                                {{ $admin->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sıralama</label>
                    <input type="number" name="sort_order" min="0"
                           value="{{ old('sort_order', $article->sort_order ?? 0) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>

                <label class="flex items-center gap-2">
                    <input type="hidden" name="is_featured" value="0">
                    <input type="checkbox" name="is_featured" value="1"
                           {{ old('is_featured', $article->is_featured ?? false) ? 'checked' : '' }}
                           class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm text-gray-700">Öne çıkan yazı</span>
                </label>

            </div>

            <div class="mt-6 flex gap-3">
                <button type="submit"
                        class="flex-1 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                    <i class="fas fa-save mr-1"></i> {{ isset($article) ? 'Güncelle' : 'Oluştur' }}
                </button>
                <a href="{{ route('admin.articles.index', [], false) }}"
                   class="px-4 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200">İptal</a>
            </div>
        </div>

        {{-- Kapak Görseli --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-base font-semibold text-gray-800 mb-4">Kapak Görseli</h3>

            @if(isset($article) && $article->getFirstMediaUrl('cover'))
                <div class="relative mb-4 group">
                    <img src="{{ $article->getFirstMediaUrl('cover') }}"
                         class="w-full rounded-lg object-cover max-h-48" alt="">
                    <form method="POST" action="{{ route('admin.articles.cover-destroy', $article->id, false) }}"
                          class="absolute top-2 right-2">
                        @csrf @method('DELETE')
                        <button type="submit"
                                onclick="return confirm('Kapak görselini kaldırmak istiyor musunuz?')"
                                class="p-1.5 bg-red-600 text-white rounded-lg text-xs hover:bg-red-700 shadow-sm">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </form>
                </div>
            @endif

            <input type="file" name="cover_image" accept="image/*"
                   class="w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
        </div>

        {{-- Form --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-base font-semibold text-gray-800 mb-4">Form Bağlantısı</h3>
            <select name="form_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                <option value="">— Form yok —</option>
                @foreach($forms as $form)
                    <option value="{{ $form->id }}"
                        {{ old('form_id', $article->form_id ?? '') == $form->id ? 'selected' : '' }}>
                        {{ $form->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Varyantlar --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-base font-semibold text-gray-800 mb-4">
                <i class="fas fa-palette mr-2 text-amber-500"></i>Şablon Varyantları
            </h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Liste Varyantı</label>
                    <input type="text" name="listing_variant"
                           value="{{ old('listing_variant', $article->listing_variant ?? '') }}"
                           placeholder="default"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <p class="mt-1 text-xs text-gray-400">Yazı kartının liste görünümünde kullanılacak varyant.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Detay Varyantı</label>
                    <input type="text" name="detail_variant"
                           value="{{ old('detail_variant', $article->detail_variant ?? '') }}"
                           placeholder="default"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <p class="mt-1 text-xs text-gray-400">Yazı detay sayfasında kullanılacak varyant.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Harici URL</label>
                    <input type="url" name="external_url"
                           value="{{ old('external_url', $article->external_url ?? '') }}"
                           placeholder="https://"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Link Hedefi</label>
                    <select name="link_target"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                        <option value="_self"  {{ old('link_target', $article->link_target ?? '_self') === '_self'  ? 'selected' : '' }}>Aynı sekme</option>
                        <option value="_blank" {{ old('link_target', $article->link_target ?? '') === '_blank' ? 'selected' : '' }}>Yeni sekme</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Translation panel (edit only) --}}
        @if(isset($article) && $article->exists)
            @include('admin.articles._translations-panel')
        @endif

    </div>{{-- /sidebar --}}
</div>
