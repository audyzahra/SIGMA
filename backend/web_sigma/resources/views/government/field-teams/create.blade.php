@extends('layouts.government')

@section('title', 'Tambah Tim Pemadam | SIGMA')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/government/field_team.css') }}">
@endpush

@section('content')

    <section class="page-heading">

        <div>
            <p class="breadcrumb">
                Beranda › Tim Pemadam › Tambah
            </p>

            <h1>
                Tambah Tim Pemadam
            </h1>

            <p>
                Tambahkan tim petugas lapangan baru ke dalam sistem.
            </p>
        </div>

        <a href="{{ route('government.field-teams.index') }}"
           class="button button-light">
            Kembali
        </a>

    </section>


    <section class="panel">

        <form method="POST"
              action="{{ route('government.field-teams.store') }}">

            @csrf

            <div class="field-team-form">

                @include('government.field-teams.form')

                <div class="form-actions">

                    <a href="{{ route('government.field-teams.index') }}"
                       class="button button-light">
                        Batal
                    </a>

                    <button type="submit"
                            class="button button-primary">
                        Simpan Tim
                    </button>

                </div>

            </div>

        </form>

    </section>

@endsection