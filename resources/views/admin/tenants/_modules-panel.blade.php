{{--
    Vertical Modüller paneli.

    Tenant başına aktif vertical modülleri (Tours, ileride Commerce) toggle
    eder.  Sadece config/tenant_modules.php'de `user_installable=true` olan
    modüller burada gösterilir; internal dependency modülleri (`payments`)
    bir Tour aktive edildiğinde ModuleManager tarafından otomatik kurulur.

    Variables (passed via include):
      - $tenant            Tenant instance
      - $availableModules  ['tours' => ['slug','label','description','requires','enabled'], …]
      - $canManage         bool — sadece agency admin toggle yapabilir
--}}
<div class="bg-white rounded-xl shadow-sm border p-5">
    <div class="flex items-center justify-between mb-4">
        <h2 class="font-semibold text-gray-700 flex items-center gap-2">
            <i class="fas fa-cubes text-amber-500"></i> Modüller
        </h2>
        @php
            $enabledCount = collect($availableModules)->where('enabled', true)->count();
        @endphp
        <span class="text-xs px-2 py-1 rounded-full
            {{ $enabledCount > 0 ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600' }}">
            {{ $enabledCount === 0 ? 'Sadece Kurumsal CMS' : $enabledCount . ' modül aktif' }}
        </span>
    </div>

    <p class="text-xs text-gray-500 mb-4">
        Bu sitenin kurumsal sayfa builder'ına ek olarak çalışmasını istediğiniz <strong>vertical modülleri</strong>
        buradan açıp kapatabilirsiniz.  Modül etkinleştirildiğinde gerekli veritabanı tabloları otomatik kurulur;
        kapattığınızda <strong>veriler korunur</strong> (geri açtığınızda kaldığı yerden devam eder).
        Kalıcı silme için <code class="bg-gray-100 px-1 rounded">php artisan tenant:module:uninstall &lt;modül&gt; --drop-tables</code>.
    </p>

    @if(empty($availableModules))
        <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-500">
            Şu anda etkinleştirilebilir bir vertical modül yok.
            Yeni modüller <code class="bg-white px-1 rounded">config/tenant_modules.php</code>'de tanımlanır.
        </div>
    @elseif($canManage)
        <form method="POST" action="{{ route('admin.tenants.modules.update', $tenant, false) }}" class="space-y-3">
            @csrf @method('PUT')

            @foreach($availableModules as $mod)
                <label class="flex items-start gap-3 p-3 border rounded-lg cursor-pointer hover:bg-gray-50 transition
                              {{ $mod['enabled'] ? 'border-amber-200 bg-amber-50/30' : 'border-gray-200' }}">
                    <input type="checkbox"
                           name="modules[]"
                           value="{{ $mod['slug'] }}"
                           {{ $mod['enabled'] ? 'checked' : '' }}
                           class="mt-1 h-4 w-4 text-amber-600 border-gray-300 rounded focus:ring-amber-500">
                    <span class="flex-1">
                        <span class="flex items-center gap-2">
                            <span class="text-sm font-medium text-gray-700">{{ $mod['label'] }}</span>
                            <span class="text-[10px] font-mono text-gray-400">{{ $mod['slug'] }}</span>
                            @if(!empty($mod['requires']))
                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-blue-50 text-blue-600">
                                    + {{ implode(', ', $mod['requires']) }}
                                </span>
                            @endif
                        </span>
                        <span class="block text-xs text-gray-500 mt-1">{{ $mod['description'] }}</span>
                    </span>
                </label>
            @endforeach

            <div class="flex items-center justify-between pt-2">
                <p class="text-xs text-gray-400">
                    <i class="fas fa-info-circle"></i>
                    Modülü açtığınızda tenant veritabanında migration çalışır
                    (~1-2 saniye, idempotent).
                </p>
                <button type="submit"
                        class="px-4 py-2 bg-amber-600 text-white rounded-lg text-sm hover:bg-amber-700 font-medium">
                    <i class="fas fa-save mr-1"></i> Modülleri Kaydet
                </button>
            </div>
        </form>
    @else
        <div class="space-y-2">
            @foreach($availableModules as $mod)
                <div class="flex items-start gap-3 p-3 border border-gray-200 rounded-lg">
                    <span class="mt-0.5 inline-flex items-center justify-center w-4 h-4 rounded
                                {{ $mod['enabled'] ? 'bg-amber-500 text-white' : 'bg-gray-200 text-gray-400' }}">
                        @if($mod['enabled'])<i class="fas fa-check text-[10px]"></i>@endif
                    </span>
                    <span class="flex-1">
                        <span class="text-sm font-medium text-gray-700">{{ $mod['label'] }}</span>
                        <span class="block text-xs text-gray-500 mt-0.5">{{ $mod['description'] }}</span>
                    </span>
                </div>
            @endforeach
        </div>
    @endif
</div>
