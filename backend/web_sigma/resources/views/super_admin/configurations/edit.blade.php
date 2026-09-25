@extends('layouts.super_admin')


@push('styles')
    <link rel="stylesheet" href="{{ asset('css/super_admin/configurations.css') }}">
@endpush


@section('title', 'Edit Konfigurasi | SIGMA')


@section('content')


    <section class="page-heading">

        <div>

            <p class="breadcrumb">
                Dashboard / Konfigurasi Sistem / Edit
            </p>


            <h1>
                Edit Konfigurasi
            </h1>


        </div>
        <a class="button button-light"
            href="{{ route('super-admin.configurations.index') }}">
            Kembali
        </a>

    </section>



    <form class="panel configuration-form" method="POST"
        action="{{ route('super-admin.configurations.update', \App\Helpers\EncryptHelper::encrypt($configuration->id)) }}">


        @csrf
        @method('PUT')


        @include('super_admin.configurations.form')


        <div class="form-actions">


            <a class="button button-light"
                href="{{ route('super-admin.configurations.show', \App\Helpers\EncryptHelper::encrypt($configuration->id)) }}">
                Batal
            </a>


            <button class="button button-primary">
                Simpan Perubahan
            </button>


        </div>


    </form>


@endsection
