@extends('admin.layouts.app')
@section('title', 'Yeni SMTP Profili')
@section('page-title', 'Yeni SMTP Profili')
@section('content')
    <form method="POST" action="{{ route('admin.smtp-profiles.store', [], false) }}">
        @csrf
        @include('admin.smtp-profiles._form')
    </form>
@endsection
