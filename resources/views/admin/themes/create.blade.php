@extends('admin.layouts.app')

@section('title', 'Yeni Tema')
@section('page-title', 'Yeni Tema')

@section('content')
    <form method="POST" action="{{ route('admin.themes.store', [], false) }}" enctype="multipart/form-data">
        @csrf
        @include('admin.themes._form')
    </form>
@endsection
