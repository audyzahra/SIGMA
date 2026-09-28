@extends('layouts.super_admin')


@push('styles')
    <link rel="stylesheet" href="{{ asset('css/super_admin/ai_models.css') }}">
@endpush


@section('title', 'Detail Model AI | SIGMA')


@section('content')


    <section class="page-heading">

        <div>

            <p class="breadcrumb">
                Dashboard / Model AI / Detail
            </p>


            <h1>
                {{ $aiModel->name }}
            </h1>


        </div>



        <a class="button button-primary"
            href="{{ route('super-admin.ai-models.edit', \App\Helpers\EncryptHelper::encrypt($aiModel->id)) }}">
            Edit Model
        </a>


    </section>



    <section class="panel info-card">


        <div class="info-item">
            <b>Kode</b>
            <strong>{{ $aiModel->code }}</strong>
        </div>


        <div class="info-item">
            <b>Jenis</b>
            <strong>
                {{ ucfirst($aiModel->type) }} /
                {{ ucfirst($aiModel->input_type) }}
            </strong>
        </div>


        <div class="info-item">
            <b>Versi</b>
            <strong>
                {{ $aiModel->version ?? '—' }}
            </strong>
        </div>


        <div class="info-item">
            <b>Framework</b>
            <strong>
                {{ $aiModel->framework ?? '—' }}
            </strong>
        </div>


        <div class="info-item">
            <b>Akurasi</b>
            <strong>
                {{ $aiModel->accuracy ?? '—' }}
            </strong>
        </div>


        <div class="info-item">
            <b>Status</b>
            <strong>
                {{ ucfirst($aiModel->status) }}
            </strong>
        </div>


        <div class="info-item full">
            <b>Deskripsi</b>

            <strong>
                {{ $aiModel->description ?? '—' }}
            </strong>

        </div>


    </section>


@endsection
