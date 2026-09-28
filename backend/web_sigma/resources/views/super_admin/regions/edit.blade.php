@extends('layouts.super_admin')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/super_admin/regions.css') }}">
@endpush

@section('title', 'Edit Wilayah | SIGMA')

@section('content')

    <section class="page-heading">
        <div>

            <p class="breadcrumb">
                Dashboard / Manajemen Wilayah / Edit
            </p>

            <h1>
                Edit Wilayah
            </h1>

        </div>
        <a class="button button-light"
            href="{{ route('super-admin.regions.show', \App\Helpers\EncryptHelper::encrypt($region->id)) }}">
            Kembali
        </a>
    </section>

    <form class="panel region-form" method="POST"
        action="{{ route('super-admin.regions.update', \App\Helpers\EncryptHelper::encrypt($region->id)) }}">
        @csrf
        @method('PUT')
        @include('super_admin.regions.form')

        <div class="form-actions">
            <a class="button button-light"
                href="{{ route('super-admin.regions.show', \App\Helpers\EncryptHelper::encrypt($region->id)) }}">
                Batal
            </a>

            <button type="submit" class="button button-primary">
                Simpan Perubahan
            </button>
        </div>
    </form>
@endsection
