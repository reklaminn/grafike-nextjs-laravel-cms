@extends('admin.layouts.app')
@section('title', 'Yeni Site Oluştur')

@section('content')
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.tenants.index') }}" class="text-gray-400 hover:text-gray-600">
        <i class="fas fa-arrow-left"></i>
    </a>
    <h1 class="text-2xl font-bold text-gray-800">Yeni Site</h1>
</div>

<div class="max-w-2xl">
    <form method="POST" action="{{ route('admin.tenants.store', [], false) }}" class="space-y-6">
        @csrf

        <div class="bg-white rounded-xl shadow-sm border p-6 space-y-5">
            <h2 class="font-semibold text-gray-700 text-sm uppercase tracking-wide border-b pb-2">Site Bilgileri</h2>

            {{-- Name --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Site Adı <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-400 @enderror"
                       placeholder="Nuh Çiçek Mobilya">
                @error('name')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Slug / Tenant ID --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Slug (Tenant ID) <span class="text-red-500">*</span>
                </label>
                <div class="flex">
                    <span class="inline-flex items-center px-3 rounded-l-lg border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">tenant_</span>
                    <input type="text" name="slug" id="slug" value="{{ old('slug') }}" required
                           class="flex-1 px-3 py-2 border border-gray-300 rounded-r-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-mono @error('slug') border-red-400 @enderror"
                           placeholder="nuhcicek"
                           pattern="[a-z0-9\-_]+"
                           title="Sadece küçük harf, rakam, tire ve alt çizgi">
                </div>
                <p class="text-xs text-gray-400 mt-1">Veritabanı adı: <code class="bg-gray-100 px-1 rounded" id="db-preview">tenant_nuhcicek</code></p>
                @error('slug')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Primary Domain --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Ana Domain <span class="text-red-500">*</span>
                </label>
                <input type="text" name="domain" value="{{ old('domain') }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-mono @error('domain') border-red-400 @enderror"
                       placeholder="nuhcicek.com.tr veya firma1.{{ env('APP_DOMAIN') }}">
                <p class="text-xs text-gray-400 mt-1">
                    <strong>Özel domain:</strong> <code class="font-mono">nuhcicek.com.tr</code> — www. varyantı otomatik eklenir.<br>
                    <strong>Subdomain:</strong> <code class="font-mono">firma1.{{ env('APP_DOMAIN') }}</code> — www. eklenmez, DNS wildcard (<code>*.{{ env('APP_DOMAIN') }}</code>) yeterli.
                </p>
                @error('domain')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Theme --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tema</label>
                <select name="theme_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Tema seç (sonra da atanabilir)</option>
                    @foreach($themes as $theme)
                    <option value="{{ $theme->id }}" {{ old('theme_id') == $theme->id ? 'selected' : '' }}>
                        {{ $theme->name }} ({{ $theme->engine }})
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Paket (kullanıcı kotası + AI planı) --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Paket
                    <span class="ml-1 text-xs text-gray-400 font-normal">(yönetici kullanıcı kotası + AI planı)</span>
                </label>
                <select name="package"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    @foreach(\App\Models\Package::allKeyed() as $key => $pkg)
                    <option value="{{ $key }}" {{ old('package', \App\Models\Package::defaultKey()) === $key ? 'selected' : '' }}>
                        {{ $pkg['label'] }} — {{ $pkg['max_users'] === null ? 'sınırsız kullanıcı' : $pkg['max_users'].' kullanıcı' }} · AI: {{ $pkg['ai_plan'] }}
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Industry / Site Template --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Sektör
                    <span class="ml-1 text-xs text-gray-400 font-normal">(opsiyonel — demo içerik ekler)</span>
                </label>
                <select id="industry-select"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Sektör seç</option>
                    @foreach($industries as $code => $label)
                    <option value="{{ $code }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div id="template-wrapper" class="hidden">
                <label class="block text-sm font-medium text-gray-700 mb-1">Site Şablonu</label>
                <select name="site_template_id" id="template-select"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Şablon seç (opsiyonel)</option>
                </select>
                <p class="text-xs text-gray-400 mt-1">Seçilirse migration sonrası hazır sayfalar ve menü otomatik oluşturulur.</p>
            </div>

            {{-- Module hint — shown when chosen industry typically pairs
                 with a vertical module (Turizm → Tours, E-Ticaret → Commerce).
                 Pure suggestion: actual module activation happens via the
                 Modüller panel on the tenant's detail page after creation. --}}
            <div id="module-hint" class="hidden bg-amber-50 border border-amber-200 rounded-lg p-3 text-xs text-amber-800">
                <i class="fas fa-lightbulb mr-1"></i>
                <span id="module-hint-text"></span>
                Site oluşturulduktan sonra <strong>Site detay → Modüller</strong> panelinden
                tek tıkla etkinleştirebilirsiniz.
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border p-6 space-y-5">
            <label class="flex items-start gap-3">
                <input type="checkbox"
                       name="create_company_admin"
                       value="1"
                       {{ old('create_company_admin') ? 'checked' : '' }}
                       class="mt-1 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                <span>
                    <span class="block font-semibold text-gray-800">Firma yöneticisi oluştur</span>
                    <span class="block text-xs text-gray-500 mt-1">
                        Bu kullanıcı sadece oluşturulan siteye atanır ve girişte varsayılan olarak bu site açılır.
                    </span>
                </span>
            </label>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ad Soyad</label>
                    <input type="text" name="company_admin_name" value="{{ old('company_admin_name') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 @error('company_admin_name') border-red-400 @enderror"
                           placeholder="Firma Yetkilisi">
                    @error('company_admin_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kullanıcı Adı</label>
                    <input type="text" name="company_admin_username" value="{{ old('company_admin_username') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 @error('company_admin_username') border-red-400 @enderror"
                           placeholder="firmaadmin">
                    @error('company_admin_username')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">E-posta</label>
                    <input type="email" name="company_admin_email" value="{{ old('company_admin_email') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 @error('company_admin_email') border-red-400 @enderror"
                           placeholder="yetkili@firma.com">
                    @error('company_admin_email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div></div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Şifre</label>
                    <input type="password" name="company_admin_password"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 @error('company_admin_password') border-red-400 @enderror">
                    @error('company_admin_password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Şifre Tekrar</label>
                    <input type="password" name="company_admin_password_confirmation"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
        </div>

        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-800">
            <i class="fas fa-info-circle mr-1"></i>
            <strong>Sonraki adım:</strong> Site oluşturulduktan sonra <em>Provision</em> butonuna tıklayarak
            tenant veritabanını hazırlayın (migration'lar çalıştırılır).
        </div>

        <div class="flex gap-3">
            <button type="submit"
                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 font-medium">
                <i class="fas fa-save mr-1"></i> Siteyi Oluştur
            </button>
            <a href="{{ route('admin.tenants.index') }}"
               class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">
                İptal
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.getElementById('slug').addEventListener('input', function () {
    document.getElementById('db-preview').textContent = 'tenant_' + (this.value || '…');
});

// Industry → Site Template cascade + Module hint
const templatesByIndustry  = @json($templatesByIndustry);
const industryModuleHints  = @json($industryModuleHints);
const moduleLabels         = { tours: 'Turizm (Tours)', commerce: 'E-Ticaret (Commerce)' };

document.getElementById('industry-select').addEventListener('change', function () {
    const industry   = this.value;
    const wrapper    = document.getElementById('template-wrapper');
    const tplSelect  = document.getElementById('template-select');
    const hintBox    = document.getElementById('module-hint');
    const hintText   = document.getElementById('module-hint-text');

    // Module hint banner (independent of template availability)
    const hints = industry ? (industryModuleHints[industry] || []) : [];
    if (hints.length > 0) {
        const pretty = hints.map(s => moduleLabels[s] || s).join(' + ');
        hintText.textContent = `Bu sektör genellikle ${pretty} modülü ile birlikte kullanılır.`;
        hintBox.classList.remove('hidden');
    } else {
        hintBox.classList.add('hidden');
    }

    // Clear current options
    tplSelect.innerHTML = '<option value="">Şablon seç (opsiyonel)</option>';

    if (! industry || ! templatesByIndustry[industry]) {
        wrapper.classList.add('hidden');
        return;
    }

    const templates = templatesByIndustry[industry];
    templates.forEach(function (tpl) {
        const opt = document.createElement('option');
        opt.value = tpl.id;
        opt.textContent = tpl.name;
        tplSelect.appendChild(opt);
    });

    wrapper.classList.remove('hidden');

    // Auto-select first template if only one exists
    if (templates.length === 1) {
        tplSelect.value = templates[0].id;
    }
});
</script>
@endpush
@endsection
