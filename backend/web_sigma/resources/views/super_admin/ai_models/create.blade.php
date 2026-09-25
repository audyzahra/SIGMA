@extends('layouts.super_admin')


@push('styles')
    <link rel="stylesheet" href="{{ asset('css/super_admin/ai_models.css') }}">
@endpush


@section('title', 'Tambah Model AI | SIGMA')


@section('content')


    <section class="page-heading">

        <div>

            <p class="breadcrumb">
                Dashboard / Model AI / Tambah
            </p>


            <h1>
                Tambah Model AI
            </h1>

        </div>
        <a class="button button-light"
            href="{{ route('super-admin.ai-models.index') }}">
            Kembali
        </a>

    </section>



    <form class="panel ai-model-form" method="POST" action="{{ route('super-admin.ai-models.store') }}">

        @csrf


        @include('super_admin.ai_models.form')


        <div class="form-actions">


            <button class="button button-primary">
                Simpan Model
            </button>


        </div>


    </form>


@endsection
