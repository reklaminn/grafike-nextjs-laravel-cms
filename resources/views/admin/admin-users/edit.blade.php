@extends('admin.layouts.app')

@section('title', 'Düzenle: ' . $admin_user->name)
@section('page-title')
    Yöneticiyi Düzenle
    <span class="text-gray-400 font-normal text-sm ml-2">#{{ $admin_user->id }}</span>
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.admin-users.update', $admin_user, false) }}">
        @csrf
        @method('PUT')
        @include('admin.admin-users._form')
    </form>
@endsection
