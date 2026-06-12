@extends('admin.layouts.app')

@section('title', 'Düzenle: ' . $role->name)
@section('page-title')
    Rolü Düzenle
    <span class="text-gray-400 font-normal text-sm ml-2">{{ $role->name }}</span>
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.roles.update', $role, false) }}">
        @csrf
        @method('PUT')
        @include('admin.roles._form')
    </form>
@endsection
