@extends('admin.layouts.app')

@section('title', 'Düzenle: ' . $form->name)
@section('page-title', 'Form Düzenle: ' . $form->name)

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Form Settings -->
        <div>
            <form method="POST" action="{{ route('admin.forms.update', $form, false) }}">
                @csrf @method('PUT')
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                    <h3 class="text-base font-semibold text-gray-800">Form Ayarları</h3>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Form Adı</label>
                        <input type="text" name="name" required value="{{ $form->name }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Açıklama</label>
                        <textarea name="description" rows="2"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        >{{ $form->description }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alıcı E-posta</label>
                        <input type="email" name="notification_email" value="{{ $form->notification_email }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                    <div class="pt-2 border-t border-gray-100">
                        <label class="block text-sm font-medium text-gray-700 mb-1">SendPulse / Webhook URL</label>
                        <input type="url" name="webhook_url" value="{{ $form->webhook_url }}" placeholder="https://...sendpulse..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <p class="text-xs text-gray-400 mt-1">Form gönderimleri buraya da JSON olarak POST edilir (alan adı → değer).</p>
                    </div>
                    <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                        <input type="checkbox" name="webhook_enabled" value="1" {{ $form->webhook_enabled ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        Webhook'a göndermeyi aç
                    </label>
                    <button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700">
                        <i class="fas fa-save mr-1"></i> Kaydet
                    </button>
                </div>
            </form>
        </div>

        <!-- Form Fields -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-semibold text-gray-800">Form Alanları</h3>
                <span class="text-xs text-gray-400">{{ $form->fields->count() }} alan</span>
            </div>

            <div class="space-y-3 mb-4">
                @forelse($form->fields as $field)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <span class="text-sm font-medium text-gray-700">{{ $field->label }}</span>
                            <span class="text-xs text-gray-400 ml-2">({{ $field->type }})</span>
                            @if($field->is_required)
                                <span class="text-xs text-red-500 ml-1">*</span>
                            @endif
                        </div>
                        <button onclick="deleteField({{ $form->id }}, {{ $field->id }})"
                                class="text-gray-400 hover:text-red-600 transition-colors">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 text-center py-4">Henüz alan eklenmemiş.</p>
                @endforelse
            </div>

            <hr class="my-4">
            <h4 class="text-sm font-medium text-gray-700 mb-3">Yeni Alan Ekle</h4>
            <form id="addFieldForm" class="space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <input type="text" name="label" required placeholder="Alan Adı"
                           class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <input type="text" name="name" required placeholder="field_name"
                           class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <select name="type" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                        <option value="text">Metin</option>
                        <option value="email">E-posta</option>
                        <option value="tel">Telefon</option>
                        <option value="textarea">Metin Alanı</option>
                        <option value="select">Seçim Kutusu</option>
                        <option value="checkbox">Onay Kutusu</option>
                        <option value="radio">Radyo Buton</option>
                        <option value="number">Sayı</option>
                        <option value="date">Tarih</option>
                        <option value="file">Dosya</option>
                    </select>
                    <label class="flex items-center gap-2 px-3">
                        <input type="checkbox" name="is_required" value="1"
                               class="h-4 w-4 rounded border-gray-300 text-indigo-600">
                        <span class="text-sm text-gray-700">Zorunlu</span>
                    </label>
                </div>
                <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700">
                    <i class="fas fa-plus mr-1"></i> Alan Ekle
                </button>
            </form>
        </div>
    </div>

    <div class="mt-6">
        <a href="{{ route('admin.forms.submissions', $form) }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-orange-50 text-orange-700 rounded-lg hover:bg-orange-100 text-sm font-medium">
            <i class="fas fa-inbox"></i> Mesajları Görüntüle ({{ $form->submissions()->count() }})
        </a>
    </div>
@endsection

@push('scripts')
<script>
document.getElementById('addFieldForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    data.is_required = formData.has('is_required');
    fetch('{{ route("admin.forms.save-field", $form) }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    }).then(r => r.json()).then(d => { if(d.success) location.reload(); });
});

function deleteField(formId, fieldId) {
    if (!confirm('Bu alanı silmek istediğinize emin misiniz?')) return;
    fetch(`/admin/forms/${formId}/fields/${fieldId}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    }).then(r => r.json()).then(d => { if(d.success) location.reload(); });
}
</script>
@endpush
