@extends('admin.layouts.app')

@section('title', 'Yeni Block Şablonu')
@section('page-title', 'Yeni Block Şablonu')

@section('content')
    <form method="POST" action="{{ route('admin.section-templates.store', [], false) }}" enctype="multipart/form-data">
        @csrf
        @include('admin.section-templates._form')
    </form>
@endsection
