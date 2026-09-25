@extends('layouts.super_admin')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/super_admin/regions.css') }}">
@endpush

@section('title', 'Tambah Wilayah | SIGMA')

@section('content')

    <section class="page-heading">

        <div>
            <p class="breadcrumb">
                Dashboard / Manajemen Wilayah / Tambah
            </p>

            <h1>
                Tambah Wilayah
            </h1>
        </div>
        <a class="button button-light" href="{{ route('super-admin.regions.index') }}">
            Kembali
        </a>

    </section>

    <form class="panel region-form" method="POST" action="{{ route('super-admin.regions.store') }}">
        @csrf
        @include('super_admin.regions.form')

        <div class="form-actions">

            <button type="submit" class="button button-primary">
                Simpan Wilayah
            </button>

        </div>
    </form>

@endsection
