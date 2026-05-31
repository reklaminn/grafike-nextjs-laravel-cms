{{-- ── AI ile Sayfayı Oluştur (sadece edit modunda göster) ── --}}
@isset($page)
<div class="bg-gradient-to-br from-indigo-50 to-purple-50 border border-indigo-200 rounded-xl p-4"
     x-data="{
         generating: false,
         done: false,
         error: '',
         purpose: '',
         showPurpose: false,
         async generate() {
             this.generating = true;
             this.error = '';
             try {
                 const r = await fetch(@js(route('admin.pages.ai-generate-blocks', $page, false)), {
                     method: 'POST',
                     credentials: 'same-origin',
                     headers: {
                         'Content-Type': 'application/json',
                         'Accept': 'application/json',
                         'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '',
                     },
                     body: JSON.stringify({ purpose: this.purpose }),
                 });
                 const data = await r.json().catch(() => ({ ok: false, message: 'Geçersiz yanıt' }));
                 if (data.ok) {
                     this.done = true;
                     setTimeout(() => window.location.reload(), 1200);
                 } else {
                     this.error = data.message || 'Hata oluştu.';
                 }
             } catch(e) {
                 this.error = e.message || 'Ağ hatası.';
             } finally {
                 this.generating = false;
             }
         }
     }">
    <div class="flex items-center gap-2 mb-2">
        <i class="fas fa-wand-magic-sparkles text-indigo-500"></i>
        <span class="text-sm font-semibold text-indigo-800">AI ile İçerik Oluştur</span>
    </div>
    <p class="text-xs text-indigo-600 mb-3">
        Sayfa başlığı ve firma bilgilerine göre blokları otomatik doldurur.
        Mevcut içerik varsa üzerine yazar.
    </p>

    <div x-show="showPurpose" x-cloak class="mb-2">
        <textarea x-model="purpose" rows="2"
                  placeholder="Sayfanın amacını belirtin (isteğe bağlı)…"
                  class="w-full px-2.5 py-2 border border-indigo-200 rounded-lg text-xs bg-white focus:ring-2 focus:ring-indigo-400 resize-none"></textarea>
    </div>

    <div class="flex items-center gap-2">
        <button type="button" @click="generate()"
                :disabled="generating || done"
                class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-indigo-600 text-white text-xs font-medium rounded-lg hover:bg-indigo-700 disabled:opacity-50 transition-colors">
            <i class="fas" :class="done ? 'fa-check' : (generating ? 'fa-spinner fa-spin' : 'fa-wand-magic-sparkles')"></i>
            <span x-text="done ? 'Tamamlandı! Yenileniyor…' : (generating ? 'Oluşturuluyor…' : 'AI ile Oluştur')"></span>
        </button>
        <button type="button" @click="showPurpose = !showPurpose"
                title="Amaç notu ekle"
                class="px-2.5 py-2 bg-white border border-indigo-200 text-indigo-500 text-xs rounded-lg hover:bg-indigo-50">
            <i class="fas fa-comment-dots"></i>
        </button>
    </div>
    <p x-show="error" x-cloak class="mt-2 text-xs text-red-600 bg-red-50 rounded p-2" x-text="error"></p>
</div>
@endisset

<!-- Publish box -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <h3 class="text-base font-semibold text-gray-800 mb-4">Yayın</h3>

    <div class="space-y-4">
        <div>
            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Durum *</label>
            <select id="status" name="status" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                <option value="draft" {{ old('status', $page->status ?? 'draft') === 'draft' ? 'selected' : '' }}>Taslak</option>
                <option value="published" {{ old('status', $page->status ?? '') === 'published' ? 'selected' : '' }}>Yayında</option>
                <option value="archived" {{ old('status', $page->status ?? '') === 'archived' ? 'selected' : '' }}>Arşivlenmiş</option>
            </select>
        </div>

        <div>
            <label for="language_id" class="block text-sm font-medium text-gray-700 mb-1">Dil *</label>
            <select id="language_id" name="language_id" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                @foreach($languages as $lang)
                    <option value="{{ $lang->id }}" {{ old('language_id', $page->language_id ?? '') == $lang->id ? 'selected' : '' }}>
                        {{ $lang->name }} ({{ $lang->code }})
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="parent_id" class="block text-sm font-medium text-gray-700 mb-1">Üst Sayfa</label>
            <select id="parent_id" name="parent_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                <option value="">— Ana Sayfa (Kök) —</option>
                @foreach($parentPages as $parentPage)
                    <option value="{{ $parentPage->id }}" {{ old('parent_id', $page->parent_id ?? '') == $parentPage->id ? 'selected' : '' }}>
                        {{ $parentPage->title }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-1">Sıralama</label>
            <input type="number" id="sort_order" name="sort_order" min="0"
                   value="{{ old('sort_order', $page->sort_order ?? 0) }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
        </div>

        <div>
            <label for="template" class="block text-sm font-medium text-gray-700 mb-1">Şablon</label>
            <input type="text" id="template" name="template"
                   value="{{ old('template', $page->template ?? '') }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                   placeholder="default">
        </div>

        <label class="flex items-start gap-3 rounded-lg bg-indigo-50 px-3 py-3 text-sm">
            <input type="checkbox" name="is_homepage" value="1"
                   class="mt-0.5 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                   {{ old('is_homepage', isset($page, $homepageId) && (string) $homepageId === (string) $page->id) ? 'checked' : '' }}>
            <span>
                <span class="block font-medium text-indigo-900">Bu sayfayı anasayfa yap</span>
                <span class="mt-0.5 block text-xs text-indigo-700">Seçili olduğunda frontend <code>/tr</code> veya <code>/en</code> kök adresinde bu sayfayı açar. Slug yine doğrudan URL olarak kullanılabilir.</span>
            </span>
        </label>

        @isset($page)
            @if($page->isSystemPage())
                <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                    <strong>Sistem sayfası:</strong> Bu sayfa silinemez. Tasarım ve içerik değişiklikleri bu düzenleme ekranından yapılır.
                </div>
            @endif
        @endisset
    </div>

    <div class="mt-6 flex gap-3">
        <button type="submit"
                class="flex-1 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
            <i class="fas fa-save mr-1"></i>
            {{ isset($page) ? 'Güncelle' : 'Oluştur' }}
        </button>
        <a href="{{ route('admin.pages.index') }}"
           class="px-4 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200 transition-colors">
            İptal
        </a>
    </div>
</div>
