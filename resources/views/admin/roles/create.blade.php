@extends('admin.layouts.app')

@section('title', 'Yeni Rol')
@section('page-title', 'Yeni Rol Oluştur')

@section('content')
    <form method="POST" action="{{ route('admin.roles.store', [], false) }}">
        @csrf
        @include('admin.roles._form', ['rolePermissions' => []])
    </form>
@endsection
