@extends('layouts.super_admin')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/super_admin/organizations.css') }}">
@endpush
@section('title', 'Edit Organisasi | SIGMA')
@section('content')
    <section class="page-heading">
        <div>
            <p class="breadcrumb">
                Dashboard / Manajemen Organisasi / Edit
            </p>
            <h1>Edit Organisasi</h1>
        </div>
        <a class="button button-light"
            href="{{ route('super-admin.organizations.show', \App\Helpers\EncryptHelper::encrypt($organization->id)) }}">
            Kembali
        </a>
    </section>
    <form method="POST"
        action="{{ route('super-admin.organizations.update', \App\Helpers\EncryptHelper::encrypt($organization->id)) }}"
        class="panel">@csrf
        @method('PUT') @include('super_admin.organizations.form')<div class="modal-actions"><a class="button button-light"
                href="{{ route('super-admin.organizations.show', \App\Helpers\EncryptHelper::encrypt($organization->id)) }}">Batal</a><button
                class="button button-primary">Simpan Perubahan</button></div>
</form>@endsection
