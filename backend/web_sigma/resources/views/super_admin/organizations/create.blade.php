@extends('layouts.super_admin')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/super_admin/organizations.css') }}">
@endpush

@section('title', 'Tambah Organisasi | SIGMA')

@section('content')

<section class="page-heading">

    <div>

        <p class="breadcrumb">
            Dashboard / Manajemen Organisasi / Tambah
        </p>

        <h1>
            Tambah Organisasi
        </h1>

    </div>

    <a class="button button-light" href="{{ route('super-admin.organizations.index') }}">
        Kembali
    </a>
</section>



<form
    method="POST"
    action="{{ route('super-admin.organizations.store') }}"
    class="panel organization-form"
>

    @csrf


    @include('super_admin.organizations.form')


    <div class="form-actions">
        
        <button
            type="submit"
            class="button button-primary"
        >
            Simpan Organisasi
        </button>


    </div>


</form>


@endsection