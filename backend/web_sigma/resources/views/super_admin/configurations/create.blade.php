@extends('layouts.super_admin')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/super_admin/configurations.css') }}">
@endpush


@section('title', 'Tambah Konfigurasi | SIGMA')


@section('content')


    <section class="page-heading">

        <div>

            <p class="breadcrumb">
                Dashboard / Konfigurasi Sistem / Tambah
            </p>

            <h1>
                Tambah Konfigurasi
            </h1>

        </div>
        <a class="button button-light"
            href="{{ route('super-admin.configurations.index') }}">
            Kembali
        </a>

    </section>



    <form class="panel configuration-form" method="POST" action="{{ route('super-admin.configurations.store') }}">

        @csrf


        @include('super_admin.configurations.form')



        <div class="form-actions">


            <button class="button button-primary">
                Simpan Konfigurasi
            </button>


        </div>


    </form>


@endsection
