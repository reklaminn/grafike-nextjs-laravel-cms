@extends('admin.layouts.app')

@section('title', 'Yeni Yazı')
@section('page-title', 'Yeni Yazı Oluştur')

@section('content')
    @if($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-5 py-4">
            <div class="flex items-center gap-2 font-semibold text-red-800 mb-2">
                <i class="fas fa-circle-exclamation"></i> Kayıt başarısız — lütfen hataları düzeltin:
            </div>
            <ul class="list-disc list-inside space-y-1 text-sm text-red-700">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.articles.store', [], false) }}" enctype="multipart/form-data">
        @csrf
        @include('admin.articles._form')
    </form>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css">
<style>
    .ql-toolbar.ql-snow   { border-radius: 0.5rem 0.5rem 0 0; border-color: #d1d5db; background: #f9fafb; }
    .ql-container.ql-snow { border-radius: 0 0 0.5rem 0.5rem; border-color: #d1d5db; }
    [data-quill] .ql-editor { min-height: 120px; font-size: 14px; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
@endpush
