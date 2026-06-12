@extends('admin.layouts.app')

@section('title', 'Yeni Yönetici')
@section('page-title', 'Yeni Yönetici')

@section('content')
    <form method="POST" action="{{ route('admin.admin-users.store', [], false) }}">
        @csrf
        @include('admin.admin-users._form')
    </form>
@endsection
