@extends('admin.layouts.app')

@section('title', 'Tema Düzenle')
@section('page-title', 'Tema Düzenle')

@section('content')
    <form method="POST" action="{{ route('admin.themes.update', $theme) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.themes._form')
    </form>
@endsection
