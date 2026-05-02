@extends('admin.layouts.app')
@section('title', 'Yeni Döviz')
@section('page-title', 'Yeni Döviz Ekle')
@section('content')
    <form method="POST" action="{{ route('admin.currencies.store', [], false) }}">
        @csrf
        @include('admin.currencies._form')
    </form>
@endsection
