@extends('admin.layouts.app')

@section('title', 'Cruise Kütüphanesi')
@section('page-title', 'Cruise Kütüphanesi')

@section('content')
<div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-xl text-sm text-blue-800">
    <i class="fas fa-info-circle mr-1"></i>
    Bu <strong>global katalog</strong> tüm acentalar için ortak gemi/liman master'ıdır. Buradaki kayıtlar
    merkezi (central) veritabanında tutulur; acentalar bunları değiştiremez, kendi sitelerine
    <strong>"Kütüphaneden İçeri Al"</strong> ile kopyalar (kopyaladıkları kaydı kendileri düzenleyebilir).
</div>

@if(session('success'))<div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ session('success') }}</div>@endif
@if(session('error'))<div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">{{ session('error') }}</div>@endif

<div class="flex justify-end mb-4">
    <a href="{{ route('admin.library.import.form') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700">
        <i class="fas fa-file-arrow-up"></i> Kütüphaneye Veri Yükle (CSV/Excel)
    </a>
</div>

@php
    $cards = [
        ['key' => 'ship_companies', 'label' => 'Gemi Firmaları', 'icon' => 'fa-flag',        'route' => 'admin.library.ship-companies.index', 'color' => 'indigo'],
        ['key' => 'ships',          'label' => 'Gemiler',        'icon' => 'fa-ship',         'route' => null,                                  'color' => 'sky'],
        ['key' => 'cabins',         'label' => 'Kabinler',       'icon' => 'fa-bed',          'route' => null,                                  'color' => 'teal'],
        ['key' => 'cabin_groups',   'label' => 'Kabin Grupları', 'icon' => 'fa-layer-group',  'route' => null,                                  'color' => 'cyan'],
        ['key' => 'ports',          'label' => 'Limanlar',       'icon' => 'fa-anchor',       'route' => 'admin.library.ports.index',           'color' => 'amber'],
        ['key' => 'destinations',   'label' => 'Destinasyonlar', 'icon' => 'fa-map-location-dot', 'route' => null,                              'color' => 'rose'],
    ];
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach($cards as $card)
        @php $disabled = $card['route'] === null; @endphp
        <a @if(!$disabled) href="{{ route($card['route']) }}" @endif
           class="block bg-white rounded-xl shadow-sm border p-5 transition
                  {{ $disabled ? 'opacity-60 cursor-default' : 'hover:shadow-md hover:border-indigo-300' }}">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-lg bg-{{ $card['color'] }}-50 text-{{ $card['color'] }}-600 flex items-center justify-center">
                        <i class="fas {{ $card['icon'] }}"></i>
                    </span>
                    <div>
                        <div class="font-semibold text-gray-800">{{ $card['label'] }}</div>
                        <div class="text-xs text-gray-500">{{ number_format($counts[$card['key']] ?? 0) }} kayıt</div>
                    </div>
                </div>
                @if($disabled)
                    <span class="text-[10px] uppercase tracking-wide text-gray-400 bg-gray-100 px-2 py-1 rounded">yakında</span>
                @else
                    <i class="fas fa-chevron-right text-gray-300"></i>
                @endif
            </div>
        </a>
    @endforeach
</div>
@endsection
