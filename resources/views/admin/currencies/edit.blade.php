@extends('admin.layouts.app')
@section('title', 'Düzenle: ' . $currency->name)
@section('page-title', 'Dövizi Düzenle')
@section('content')
    <form method="POST" action="{{ route('admin.currencies.update', $currency, false) }}">
        @csrf @method('PUT')
        @include('admin.currencies._form')
    </form>
@endsection
