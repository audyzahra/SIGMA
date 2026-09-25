@extends('layouts.super_admin')


@push('styles')
<link rel="stylesheet" href="{{ asset('css/super_admin/data_sources.css') }}">
@endpush


@section('title', 'Tambah Data Source | SIGMA')


@section('content')


<section class="page-heading">

    <div>

        <p class="breadcrumb">
            Dashboard / Sumber Data / Tambah
        </p>


        <h1>
            Tambah Data Source
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
    action="{{ route('super-admin.data-sources.store') }}"
>

    @csrf


    @include('super_admin.data_sources.form')



    <div class="form-actions">

        <button
            type="submit"
            class="button button-primary"
        >
            Simpan Data Source
        </button>


    </div>


</form>


@endsection