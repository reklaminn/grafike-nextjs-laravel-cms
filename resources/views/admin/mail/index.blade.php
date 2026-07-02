@extends('admin.layouts.app')
@section('title', 'Mail Hesapları')
@section('page-title', 'Mail Hesapları')

@section('content')

{{-- ── Agency Admin: Tenant seçici ── --}}
@if($isAgency)
<div class="mb-5 flex items-center gap-3">
    <i class="fas fa-building text-gray-400"></i>
    <select onchange="window.location.href='{{ route('admin.mail.index') }}?tenant='+this.value"
            class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 min-w-[240px]">
        <option value="">— Site seçin —</option>
        @foreach($tenantList as $t)
            <option value="{{ $t['id'] }}" {{ $selectedId === $t['id'] ? 'selected' : '' }}>
                {{ $t['name'] }} ({{ $t['domain'] }})
            </option>
        @endforeach
    </select>
    @if(empty($tenantList))
        <span class="text-xs text-amber-600"><i class="fas fa-exclamation-triangle mr-1"></i>Mailcow domain tanımlı site yok.</span>
    @endif
</div>
@endif

{{-- Tenant seçilmemişse uyarı --}}
@if(!$domain)
<div class="rounded-xl border border-amber-200 bg-amber-50 p-6 text-center">
    <i class="fas fa-envelope-open-text text-4xl text-amber-400 mb-3"></i>
    <p class="text-sm font-medium text-amber-800 mb-1">
        @if($isAgency) Yukarıdan bir site seçin @else Mailcow domain tanımlanmamış @endif
    </p>
    <p class="text-xs text-amber-600">
        @if($isAgency)
            Sadece Mailcow domain'i tanımlı siteler listelenir. Site Detayı → Mailcow Domain bölümünden ekleyin.
        @else
            Yöneticinizle iletişime geçin.
        @endif
    </p>
</div>
@else

{{-- Hata banner --}}
@if($error)
<div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">
    <i class="fas fa-exclamation-triangle mr-1"></i>
    Mailcow API hatası: {{ $error }}
    <a href="{{ route('admin.settings.mailcow') }}" class="ml-2 underline text-red-700 text-xs">Ayarları kontrol et</a>
</div>
@endif

@if(session('success'))
<div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm">
    <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">
    <i class="fas fa-exclamation-triangle mr-1"></i> {{ session('error') }}
</div>
@endif

{{-- Domain başlık --}}
<div class="flex items-center gap-3 mb-6">
    <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
        <i class="fas fa-server text-blue-600"></i>
    </div>
    <div>
        <p class="text-xs text-gray-400">Aktif domain</p>
        <p class="text-base font-semibold text-gray-800">{{ $domain }}</p>
    </div>
    <button onclick="document.getElementById('newMailboxModal').classList.remove('hidden')"
            class="ml-auto inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
        <i class="fas fa-plus"></i> Yeni Hesap
    </button>
</div>

{{-- ── Kota özeti (F1) ─────────────────────────────────────────────────── --}}
@if(!empty($quota))
@php
    $usedGb  = $quota['bytes_used'] > 0 ? round($quota['bytes_used'] / 1073741824, 2) : 0;
    $maxGb   = $quota['bytes_max'] > 0 ? round($quota['bytes_max'] / 1024, 1) : 0; // Mailcow MiB döner
    $diskPct = $maxGb > 0 ? min(100, (int) round($usedGb / $maxGb * 100)) : 0;
@endphp
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-400 mb-1"><i class="fas fa-inbox mr-1"></i>Mail Hesapları</p>
        <p class="text-xl font-bold text-gray-800 tabular-nums">
            {{ $quota['mboxes_used'] }}<span class="text-sm font-normal text-gray-400"> / {{ $quota['mboxes_max'] ?: '∞' }}</span>
        </p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-400 mb-1"><i class="fas fa-random mr-1"></i>Alias'lar</p>
        <p class="text-xl font-bold text-gray-800 tabular-nums">
            {{ $quota['aliases_used'] }}<span class="text-sm font-normal text-gray-400"> / {{ $quota['aliases_max'] ?: '∞' }}</span>
        </p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-400 mb-1"><i class="fas fa-hard-drive mr-1"></i>Disk Kullanımı</p>
        <p class="text-xl font-bold text-gray-800 tabular-nums">
            {{ $usedGb }} GB<span class="text-sm font-normal text-gray-400"> / {{ $maxGb ?: '∞' }} GB</span>
        </p>
        @if($maxGb > 0)
            <div class="mt-2 h-1.5 w-full rounded-full bg-gray-100 overflow-hidden">
                <div class="h-full rounded-full {{ $diskPct >= 90 ? 'bg-red-500' : ($diskPct >= 70 ? 'bg-amber-400' : 'bg-blue-500') }}"
                     style="width: {{ $diskPct }}%"></div>
            </div>
        @endif
    </div>
</div>
@endif

{{-- ── DNS sağlığı: MX / SPF / DKIM (F2) ──────────────────────────────── --}}
@if(!empty($dns))
@php
    $dnsBadge = fn (string $status) => match ($status) {
        'ok'            => ['bg-emerald-100 text-emerald-700', 'fa-check', 'Doğru'],
        'partial'       => ['bg-amber-100 text-amber-700', 'fa-triangle-exclamation', 'Kontrol edin'],
        'missing'       => ['bg-red-100 text-red-700', 'fa-xmark', 'Eksik'],
        'not_generated' => ['bg-gray-100 text-gray-500', 'fa-minus', 'Üretilmemiş'],
        default         => ['bg-gray-100 text-gray-500', 'fa-question', 'Bilinmiyor'],
    };
@endphp
<div class="bg-white rounded-xl border border-gray-200 p-4 mb-6" x-data="{ openDns: false }">
    <button type="button" @click="openDns = !openDns" class="flex w-full items-center gap-3 text-left">
        <span class="text-sm font-semibold text-gray-700"><i class="fas fa-shield-halved mr-1.5 text-blue-500"></i>DNS Durumu</span>
        <span class="flex items-center gap-1.5 ml-2">
            @foreach(['mx' => 'MX', 'spf' => 'SPF', 'dkim' => 'DKIM'] as $key => $label)
                @php [$cls, $icon] = $dnsBadge($dns[$key]['status']); @endphp
                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-medium {{ $cls }}">
                    <i class="fas {{ $icon }} text-[9px]"></i>{{ $label }}
                </span>
            @endforeach
        </span>
        <i class="fas ml-auto text-gray-400 text-xs" :class="openDns ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
    </button>

    <div x-show="openDns" x-cloak class="mt-4 space-y-3 text-xs">
        @foreach(['mx' => 'MX Kaydı', 'spf' => 'SPF Kaydı', 'dkim' => 'DKIM İmzası'] as $key => $title)
            @php $row = $dns[$key]; [$cls, $icon, $statusLabel] = $dnsBadge($row['status']); @endphp
            <div class="rounded-lg border border-gray-100 bg-gray-50 p-3">
                <div class="flex items-center gap-2 mb-1">
                    <span class="font-semibold text-gray-700">{{ $title }}</span>
                    <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-medium {{ $cls }}">
                        <i class="fas {{ $icon }} text-[8px]"></i>{{ $statusLabel }}
                    </span>
                </div>
                @if($row['found'])
                    <p class="font-mono text-[11px] text-gray-500 break-all">Bulunan: {{ \Illuminate\Support\Str::limit($row['found'], 120) }}</p>
                @endif
                @if(in_array($row['status'], ['missing', 'partial']) && !empty($row['expected']))
                    <div class="mt-1.5 flex items-start gap-2">
                        <p class="font-mono text-[11px] text-blue-700 break-all flex-1 rounded bg-blue-50 px-2 py-1.5"
                           id="dns_expected_{{ $key }}">{{ $key === 'dkim' ? $row['expected'] : ($key === 'mx' ? 'MX 10 '.$row['expected'] : $row['expected']) }}</p>
                        <button type="button"
                                onclick="navigator.clipboard.writeText(document.getElementById('dns_expected_{{ $key }}').textContent.trim()); this.innerHTML='<i class=\'fas fa-check\'></i>'"
                                title="Önerilen kaydı kopyala"
                                class="shrink-0 rounded bg-blue-100 px-2 py-1 text-blue-700 hover:bg-blue-200">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                    @if($key === 'dkim')
                        <p class="mt-1 text-[10px] text-gray-400">DNS host: <code>{{ $row['selector'] ?? 'dkim' }}._domainkey.{{ $domain }}</code> (TXT)</p>
                    @endif
                @endif
                @if($row['status'] === 'not_generated')
                    <p class="mt-1 text-gray-400">Mailcow panelinde bu domain için DKIM anahtarı üretilmemiş (Configuration → ARC/DKIM Keys).</p>
                @endif
            </div>
        @endforeach
        <p class="text-[10px] text-gray-400">DNS sonuçları 5 dakika önbelleklenir. Kayıt değişikliklerinin yayılması saatler sürebilir.</p>
    </div>
</div>
@endif

{{-- Tab navigation --}}
<div x-data="{ tab: 'mailboxes' }">
    <div class="flex gap-1 border-b border-gray-200 mb-6">
        <button @click="tab='mailboxes'"
                :class="tab==='mailboxes' ? 'border-b-2 border-blue-600 text-blue-700' : 'text-gray-500 hover:text-gray-700'"
                class="px-4 py-2 text-sm font-medium transition-colors">
            <i class="fas fa-inbox mr-1"></i>
            Mail Hesapları
            <span class="ml-1 bg-gray-100 text-gray-600 text-xs px-1.5 py-0.5 rounded-full">{{ count($mailboxes) }}</span>
        </button>
        <button @click="tab='aliases'"
                :class="tab==='aliases' ? 'border-b-2 border-blue-600 text-blue-700' : 'text-gray-500 hover:text-gray-700'"
                class="px-4 py-2 text-sm font-medium transition-colors">
            <i class="fas fa-random mr-1"></i>
            Alias'lar
            <span class="ml-1 bg-gray-100 text-gray-600 text-xs px-1.5 py-0.5 rounded-full">{{ count($aliases) }}</span>
        </button>
    </div>

    {{-- ── Mailboxes ── --}}
    <div x-show="tab==='mailboxes'">
        @if(empty($mailboxes))
            <div class="text-center py-12 text-gray-400">
                <i class="fas fa-inbox text-4xl mb-3"></i>
                <p class="text-sm">Henüz mail hesabı yok.</p>
            </div>
        @else
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">E-posta</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Ad</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Kota</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Durum</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">İşlem</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($mailboxes as $mb)
                    @php
                        $used     = (int) ($mb['used'] ?? 0);
                        $quota    = (int) ($mb['quota'] ?? 0);
                        $usedMb   = round($used / 1024 / 1024, 1);
                        $quotaMb  = round($quota / 1024 / 1024, 1);
                        $pct      = $quota > 0 ? min(100, round(($used / $quota) * 100)) : 0;
                        $pctColor = $pct >= 90 ? 'bg-red-500' : ($pct >= 70 ? 'bg-amber-400' : 'bg-blue-500');
                        $active   = (int) ($mb['active'] ?? 1);
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <span class="font-medium text-gray-800">{{ $mb['username'] ?? '—' }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $mb['name'] ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-gray-200 rounded-full h-1.5 min-w-[60px]">
                                    <div class="{{ $pctColor }} h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                                </div>
                                <span class="text-xs text-gray-500 whitespace-nowrap">{{ $usedMb }}/{{ $quotaMb }} MB</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($active)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Aktif</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">Pasif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                {{-- Düzenle --}}
                                <button onclick="openEditMailbox({{ json_encode($mb) }})"
                                        class="px-2 py-1 text-xs bg-indigo-50 text-indigo-700 rounded hover:bg-indigo-100">
                                    <i class="fas fa-edit"></i>
                                </button>
                                {{-- Şifre --}}
                                <button onclick="openPasswordModal('{{ $mb['username'] ?? '' }}')"
                                        class="px-2 py-1 text-xs bg-amber-50 text-amber-700 rounded hover:bg-amber-100">
                                    <i class="fas fa-key"></i>
                                </button>
                                {{-- Sil --}}
                                <button onclick="deleteMailbox('{{ $mb['username'] ?? '' }}')"
                                        class="px-2 py-1 text-xs bg-red-50 text-red-600 rounded hover:bg-red-100">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- ── Aliases ── --}}
    <div x-show="tab==='aliases'" x-cloak>
        <div class="flex justify-end mb-3">
            <button onclick="document.getElementById('newAliasModal').classList.remove('hidden')"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-plus"></i> Yeni Alias
            </button>
        </div>
        @if(empty($aliases))
            <div class="text-center py-12 text-gray-400">
                <i class="fas fa-random text-4xl mb-3"></i>
                <p class="text-sm">Henüz alias yok.</p>
            </div>
        @else
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Kimden (from)</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Kime (to)</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Durum</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">İşlem</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($aliases as $alias)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-800">
                            {{ $alias['address'] ?? '—' }}
                            @if(str_starts_with((string) ($alias['address'] ?? ''), '@'))
                                <span class="ml-1 inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-medium text-amber-700">
                                    <i class="fas fa-asterisk mr-1 text-[8px]"></i>catch-all
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $alias['goto'] ?? '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($alias['active'] ?? 1)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Aktif</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">Pasif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <form method="POST" action="{{ route('admin.mail.aliases.destroy') }}"
                                  onsubmit="return confirm('Bu alias silinsin mi?')">
                                @csrf
                                <input type="hidden" name="selected_tenant" value="{{ $selectedId ?? $tenant?->id }}">
                                <input type="hidden" name="id" value="{{ $alias['id'] ?? '' }}">
                                <button type="submit"
                                        class="px-2 py-1 text-xs bg-red-50 text-red-600 rounded hover:bg-red-100">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div><!-- /x-data tab -->

@endif {{-- /domain --}}

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- MODALS --}}
{{-- ═══════════════════════════════════════════════════════ --}}

{{-- ── Yeni Mailbox ── --}}
<div id="newMailboxModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl p-6">
        <h3 class="text-base font-semibold text-gray-800 mb-4"><i class="fas fa-plus text-blue-500 mr-2"></i>Yeni Mail Hesabı</h3>
        <form method="POST" action="{{ route('admin.mail.mailboxes.store') }}">
            @csrf
            <input type="hidden" name="selected_tenant" value="{{ $selectedId ?? $tenant?->id }}">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kullanıcı adı *</label>
                    <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-blue-500">
                        <input type="text" name="local_part" required
                               placeholder="kullanici"
                               class="flex-1 px-3 py-2 text-sm border-0 focus:outline-none">
                        <span class="px-3 py-2 bg-gray-50 text-gray-500 text-sm border-l border-gray-300">{{ $domain }}</span>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ad Soyad *</label>
                    <input type="text" name="name" required
                           placeholder="Ad Soyad"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Şifre *</label>
                    <input type="password" name="password" required minlength="8"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Şifre Tekrar *</label>
                    <input type="password" name="password_confirmation" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kota (MB) *</label>
                    <input type="number" name="quota" value="1024" min="100" max="102400" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-400 mt-1">Minimum 100 MB, maksimum 100 GB</p>
                </div>

                {{-- SMTP profili otomatik oluştur (F3) --}}
                <label class="flex items-start gap-2.5 rounded-lg bg-indigo-50 px-3 py-2.5 cursor-pointer">
                    <input type="checkbox" name="create_smtp_profile" value="1"
                           class="mt-0.5 h-4 w-4 rounded border-gray-300 text-indigo-600">
                    <span class="text-xs">
                        <span class="block font-medium text-indigo-900">SMTP profili olarak da ekle</span>
                        <span class="text-indigo-600">Form bildirimleri ve sistem mailleri bu hesaptan gönderilebilir
                        (Ayarlar → SMTP Profilleri'nde görünür).</span>
                    </span>
                </label>
            </div>
            <div class="mt-5 flex gap-3">
                <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                    Oluştur
                </button>
                <button type="button" onclick="document.getElementById('newMailboxModal').classList.add('hidden')"
                        class="px-4 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200">
                    İptal
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── Mailbox Düzenle ── --}}
<div id="editMailboxModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl p-6">
        <h3 class="text-base font-semibold text-gray-800 mb-4"><i class="fas fa-edit text-indigo-500 mr-2"></i>Hesabı Düzenle</h3>
        <form method="POST" action="{{ route('admin.mail.mailboxes.update') }}">
            @csrf
            <input type="hidden" name="selected_tenant" value="{{ $selectedId ?? $tenant?->id }}">
            <input type="hidden" name="address" id="editAddress">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">E-posta</label>
                    <input type="text" id="editAddressDisplay" disabled
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-gray-50 text-gray-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ad Soyad *</label>
                    <input type="text" name="name" id="editName" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kota (MB) *</label>
                    <input type="number" name="quota" id="editQuota" min="100" max="102400" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="active" value="1" id="editActive"
                           class="h-4 w-4 rounded border-gray-300 text-blue-600">
                    <label for="editActive" class="text-sm text-gray-700">Aktif</label>
                </div>
            </div>
            <div class="mt-5 flex gap-3">
                <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                    Güncelle
                </button>
                <button type="button" onclick="document.getElementById('editMailboxModal').classList.add('hidden')"
                        class="px-4 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200">
                    İptal
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── Şifre Sıfırla ── --}}
<div id="passwordModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl p-6">
        <h3 class="text-base font-semibold text-gray-800 mb-1"><i class="fas fa-key text-amber-500 mr-2"></i>Şifre Sıfırla</h3>
        <p class="text-xs text-gray-400 mb-4" id="passwordModalAddress">—</p>
        <form method="POST" action="{{ route('admin.mail.mailboxes.password') }}">
            <input type="hidden" name="selected_tenant" value="{{ $selectedId ?? $tenant?->id }}">
            @csrf
            <input type="hidden" name="address" id="passwordAddress">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Yeni Şifre *</label>
                    <input type="password" name="password" required minlength="8"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Şifre Tekrar *</label>
                    <input type="password" name="password_confirmation" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500">
                </div>
            </div>
            <div class="mt-5 flex gap-3">
                <button type="submit" class="flex-1 px-4 py-2 bg-amber-500 text-white text-sm font-medium rounded-lg hover:bg-amber-600">
                    Şifreyi Güncelle
                </button>
                <button type="button" onclick="document.getElementById('passwordModal').classList.add('hidden')"
                        class="px-4 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200">
                    İptal
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── Yeni Alias ── --}}
<div id="newAliasModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl p-6">
        <h3 class="text-base font-semibold text-gray-800 mb-4"><i class="fas fa-random text-blue-500 mr-2"></i>Yeni Alias</h3>
        <form method="POST" action="{{ route('admin.mail.aliases.store') }}"
              x-data="{ catchAll: false }">
            @csrf
            <input type="hidden" name="selected_tenant" value="{{ $selectedId ?? $tenant?->id }}">
            <div class="space-y-4">
                {{-- Catch-all seçeneği (F4) --}}
                <label class="flex items-start gap-2.5 rounded-lg bg-amber-50 px-3 py-2.5 cursor-pointer">
                    <input type="checkbox" name="catch_all" value="1" x-model="catchAll"
                           class="mt-0.5 h-4 w-4 rounded border-gray-300 text-amber-600">
                    <span class="text-xs">
                        <span class="block font-medium text-amber-900">Catch-all (tümünü yakala)</span>
                        <span class="text-amber-700">{{ '@' . $domain }} — hiçbir hesaba/alias'a uymayan TÜM mailler hedefe yönlenir.</span>
                    </span>
                </label>

                <div x-show="!catchAll">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kimden (From) *</label>
                    <input type="email" name="address" :required="!catchAll"
                           placeholder="{{ 'info@' . $domain }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-400 mt-1">Bu adrese gelen mailler yönlendirilir</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kime (To) *</label>
                    <input type="text" name="goto" required
                           placeholder="{{ 'gercek@' . $domain }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-400 mt-1">Birden fazla adres için virgülle ayırın</p>
                </div>
            </div>
            <div class="mt-5 flex gap-3">
                <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                    Oluştur
                </button>
                <button type="button" onclick="document.getElementById('newAliasModal').classList.add('hidden')"
                        class="px-4 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200">
                    İptal
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Delete Mailbox hidden form --}}
<form id="deleteMailboxForm" method="POST" action="{{ route('admin.mail.mailboxes.destroy') }}" class="hidden">
    @csrf
    <input type="hidden" name="selected_tenant" value="{{ $selectedId ?? $tenant?->id }}">
    <input type="hidden" name="address" id="deleteMailboxAddress">
</form>

@endsection

@push('scripts')
<script>
function openEditMailbox(mb) {
    document.getElementById('editAddress').value        = mb.username || '';
    document.getElementById('editAddressDisplay').value = mb.username || '';
    document.getElementById('editName').value           = mb.name    || '';
    document.getElementById('editQuota').value          = Math.round((mb.quota || 0) / 1024 / 1024) || 1024;
    document.getElementById('editActive').checked       = (mb.active == 1);
    document.getElementById('editMailboxModal').classList.remove('hidden');
}

function openPasswordModal(address) {
    document.getElementById('passwordAddress').value    = address;
    document.getElementById('passwordModalAddress').textContent = address;
    document.getElementById('passwordModal').classList.remove('hidden');
}

function deleteMailbox(address) {
    if (!confirm(address + ' silinsin mi? Bu işlem geri alınamaz.')) return;
    document.getElementById('deleteMailboxAddress').value = address;
    document.getElementById('deleteMailboxForm').submit();
}

// Modal dışına tıkla → kapat
['newMailboxModal','editMailboxModal','passwordModal','newAliasModal'].forEach(id => {
    document.getElementById(id).addEventListener('click', function(e) {
        if (e.target === this) this.classList.add('hidden');
    });
});
</script>
@endpush
