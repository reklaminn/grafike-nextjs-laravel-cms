@extends('admin.layouts.app')
@section('title', 'Düzenle: ' . $smtp_profile->name)
@section('page-title', 'SMTP Profilini Düzenle')
@section('content')
    <form method="POST" action="{{ route('admin.smtp-profiles.update', $smtp_profile, false) }}">
        @csrf @method('PUT')
        @include('admin.smtp-profiles._form')
    </form>
@endsection
