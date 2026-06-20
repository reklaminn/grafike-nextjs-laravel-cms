@extends('admin.layouts.app')
@section('title', 'Ekip / Yöneticiler')

@section('content')
<div class="max-w-3xl">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Ekip / Yöneticiler</h1>
        <p class="text-sm text-gray-500 mt-1">
            <span class="font-medium text-gray-700">{{ $tenant->name }}</span> sitesine yönetici (manager) veya editör ekleyin.
            @if($max !== null)
                <span class="ml-1">Kota: <strong>{{ $current }}/{{ $max }}</strong> kullanıcı.</span>
            @endif
        </p>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg p-4 mb-6 text-sm">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-4 mb-6 text-sm">
            <i class="fas fa-circle-exclamation mr-2"></i>{{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-4 mb-6 text-sm">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    {{-- ─── Mevcut ekip ─────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-base font-semibold text-gray-800 mb-4">
            <i class="fas fa-users text-gray-400 mr-1.5"></i> Ekip Üyeleri
        </h2>

        <div class="divide-y divide-gray-100">
            @forelse($members as $access)
                @php $m = $access->admin; $isOwner = $access->role === 'owner'; $isSelf = $m->id === auth('admin')->id(); @endphp
                <div class="flex flex-wrap items-center gap-3 py-3">
                    <div class="min-w-0 flex-1">
                        <div class="text-sm font-medium text-gray-800 truncate">
                            {{ $m->name }}
                            @if($isSelf)<span class="ml-1 text-[11px] text-gray-400">(siz)</span>@endif
                        </div>
                        <div class="text-xs text-gray-500 truncate">{{ $m->email }}</div>
                    </div>

                    @if($isOwner)
                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-800">
                            <i class="fas fa-crown text-[10px]"></i> Site Sahibi
                        </span>
                    @else
                        {{-- Rol değiştir --}}
                        @php $currentRole = $m->roles->first()?->name; @endphp
                        <form method="POST" action="{{ route('admin.team.update', $m, false) }}" class="flex items-center gap-1.5">
                            @csrf @method('PUT')
                            <select name="role" class="rounded-lg border border-gray-300 px-2 py-1.5 text-xs focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                                <option value="">Tam yetki (kısıtlamasız)</option>
                                @foreach($roles as $r)
                                    <option value="{{ $r->name }}" @selected($currentRole === $r->name)>{{ $r->name }}</option>
                                @endforeach
                            </select>
                            <button type="submit" title="Rolü kaydet"
                                    class="rounded-lg bg-gray-100 px-2.5 py-1.5 text-xs text-gray-600 hover:bg-gray-200">
                                <i class="fas fa-floppy-disk"></i>
                            </button>
                        </form>
                        {{-- Çıkar --}}
                        @unless($isSelf)
                            <form method="POST" action="{{ route('admin.team.destroy', $m, false) }}"
                                  onsubmit="return confirm('«{{ $m->name }}» bu siteden çıkarılsın mı?\n(Başka site erişimi yoksa hesabı tamamen kapatılır.)')">
                                @csrf @method('DELETE')
                                <button type="submit" title="Ekipten çıkar"
                                        class="rounded-lg px-2.5 py-1.5 text-xs text-red-500 hover:bg-red-50">
                                    <i class="fas fa-user-minus"></i>
                                </button>
                            </form>
                        @endunless
                    @endif
                </div>
            @empty
                <p class="py-3 text-sm text-gray-400">Henüz ekip üyesi yok.</p>
            @endforelse
        </div>
    </div>

    {{-- ─── Üye ekle ────────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-base font-semibold text-gray-800 mb-1">
            <i class="fas fa-user-plus text-gray-400 mr-1.5"></i> Ekip Üyesi Ekle
        </h2>
        <p class="text-xs text-gray-400 mb-4">
            E-posta zaten kayıtlıysa ad/şifre yok sayılır; mevcut hesaba yalnızca bu siteye erişim verilir.
        </p>

        @if($max !== null && $current >= $max)
            <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                <i class="fas fa-triangle-exclamation mr-1"></i>
                Paket kotası dolu ({{ $current }}/{{ $max }}). Yeni üye eklemek için ajansınıza başvurun.
            </div>
        @else
        <form method="POST" action="{{ route('admin.team.store', [], false) }}" class="space-y-4">
            @csrf
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">E-posta <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Yetki (rol)</label>
                    <select name="role"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                        <option value="">Tam yetki (kısıtlamasız)</option>
                        @foreach($roles as $r)
                            <option value="{{ $r->name }}" @selected(old('role') === $r->name)>{{ $r->name }}</option>
                        @endforeach
                    </select>
                    @if($roles->isEmpty())
                        <p class="mt-1 text-[11px] text-amber-600">Kısıtlı rol tanımlı değil — ajanstan Roller/Yetkiler'de müşteri rolü oluşturmasını isteyin. Şimdilik eklenen üye tam yetkili olur.</p>
                    @endif
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Ad <span class="text-gray-400">(yeni hesap için)</span></label>
                    <input type="text" name="name" value="{{ old('name') }}"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                </div>
                <div></div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Şifre <span class="text-gray-400">(yeni hesap için)</span></label>
                    <input type="password" name="password" autocomplete="new-password"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Şifre (tekrar)</label>
                    <input type="password" name="password_confirmation" autocomplete="new-password"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-indigo-700">
                    <i class="fas fa-user-plus"></i> Ekibe Ekle
                </button>
            </div>
        </form>
        @endif
    </div>

    <p class="mt-4 text-xs text-gray-400">
        <i class="fas fa-circle-info mr-0.5"></i>
        Yetki = Roller/Yetkiler'de tanımlı rolün izinleri. <strong>Tam yetki</strong> seçilirse üye sitenizde her şeyi yapabilir; bir rol seçilirse yalnızca o rolün izinleriyle sınırlanır. Site sahibi (owner) ataması yalnızca ajans tarafından yapılır.
    </p>
</div>
@endsection
