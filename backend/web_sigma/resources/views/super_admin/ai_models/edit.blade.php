@extends('layouts.super_admin')


@push('styles')
    <link rel="stylesheet" href="{{ asset('css/super_admin/ai_models.css') }}">
@endpush


@section('title', 'Edit Model AI | SIGMA')


@section('content')


    <section class="page-heading">

        <div>

            <p class="breadcrumb">
                Dashboard / Model AI / Edit
            </p>


            <h1>
                Edit Model AI
            </h1>

        </div>

         <a class="button button-light"
            href="{{ route('super-admin.ai-models.index') }}">
            Kembali
        </a>

    </section>



    <form class="panel ai-model-form" method="POST"
        action="{{ route('super-admin.ai-models.update', \App\Helpers\EncryptHelper::encrypt($aiModel->id)) }}">


        @csrf
        @method('PUT')


        @include('super_admin.ai_models.form')



        <div class="form-actions">


            <a class="button button-light"
                href="{{ route('super-admin.ai-models.show', \App\Helpers\EncryptHelper::encrypt($aiModel->id)) }}">
                Batal
            </a>


            <button class="button button-primary">
                Simpan Perubahan
            </button>


        </div>


    </form>


@endsection
