@extends('admin.layouts.app')

@section('title', 'Block Şablonu Düzenle')
@section('page-title', 'Block Şablonu Düzenle')

@section('content')
    <form method="POST" action="{{ route('admin.section-templates.update', $sectionTemplate, false) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.section-templates._form')
    </form>
@endsection

@if(session('success'))
@push('scripts')
<script>
// Açık sayfa editörü sekmelerine "şablon değişti" sinyali gönder.
// storage event'i aynı origin'deki DİĞER sekmelerde tetiklenir.
try {
    localStorage.setItem('grafike:section-template-updated', JSON.stringify({
        id: @json($sectionTemplate->id),
        name: @json($sectionTemplate->name),
        ts: Date.now(),
    }));
} catch (e) { /* localStorage kullanılamıyorsa sessizce geç */ }
</script>
@endpush
@endif
