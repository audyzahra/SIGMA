@extends('layouts.super_admin')


@push('styles')
    <link rel="stylesheet" href="{{ asset('css/super_admin/configurations.css') }}">
@endpush


@section('title', 'Detail Konfigurasi | SIGMA')


@section('content')


    <section class="page-heading">

        <div>

            <p class="breadcrumb">
                Dashboard / Konfigurasi Sistem / Detail
            </p>


            <h1>
                {{ $configuration->key }}
            </h1>

        </div>

        <a class="button button-primary"
            href="{{ route('super-admin.configurations.edit', \App\Helpers\EncryptHelper::encrypt($configuration->id)) }}">
            Edit Konfigurasi
        </a>


    </section>




    <section class="panel info-card">


        <div class="info-item">

            <b>
                Tipe
            </b>

            <strong>
                {{ ucfirst($configuration->type) }}
            </strong>

        </div>



        <div class="info-item">

            <b>
                Nilai
            </b>

            <strong>
                {{ $configuration->value }}
            </strong>

        </div>



        <div class="info-item full">

            <b>
                Deskripsi
            </b>

            <strong>
                {{ $configuration->description ?? '—' }}
            </strong>

        </div>


    </section>


@endsection
