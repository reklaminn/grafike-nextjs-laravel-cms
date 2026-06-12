@extends('admin.layouts.app')

@section('title', 'Yeni Sayfa')
@section('page-title', 'Yeni Sayfa Oluştur')

@section('content')
    {{-- Relative URL so form submits to the current origin (HTTPS) --}}
    <form method="POST" action="{{ route('admin.pages.store', [], false) }}" enctype="multipart/form-data">
        @csrf
        @include('admin.pages._form')
    </form>
@endsection
