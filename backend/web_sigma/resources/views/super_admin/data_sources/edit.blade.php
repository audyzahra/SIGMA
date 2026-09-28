@extends('layouts.super_admin')


@push('styles')
<link rel="stylesheet" href="{{ asset('css/super_admin/data_sources.css') }}">
@endpush


@section('title', 'Edit Data Source | SIGMA')


@section('content')


<section class="page-heading">

    <div>

        <p class="breadcrumb">
            Dashboard / Sumber Data / Edit
        </p>


        <h1>
            Edit Data Source
        </h1>


    </div>

     <a class="button button-light"
            href="{{ route('super-admin.data-sources.index') }}">
            Kembali
        </a>

</section>




<form
    class="panel data-source-form"
    method="POST"
    action="{{ route(
        'super-admin.data-sources.update',
        \App\Helpers\EncryptHelper::encrypt($dataSource->id)
    ) }}"
>


    @csrf

    @method('PUT')


    @include('super_admin.data_sources.form')



    <div class="form-actions">


        <a
            class="button button-light"
            href="{{ route(
                'super-admin.data-sources.show',
                \App\Helpers\EncryptHelper::encrypt($dataSource->id)
            ) }}"
        >
            Batal
        </a>



        <button
            type="submit"
            class="button button-primary"
        >
            Simpan Perubahan
        </button>


    </div>


</form>


@endsection