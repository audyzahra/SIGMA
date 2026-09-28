@extends('layouts.government')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/government/field_team.css') }}">
@endpush

@section('title', 'Edit Tim Pemadam | SIGMA')

@section('content')

    <section class="page-heading">

        <div>

            <p class="breadcrumb">
                Beranda › Tim Pemadam › Edit
            </p>

            <h1>
                Edit Tim Pemadam
            </h1>

            <p>
                Perbarui informasi dan anggota tim petugas lapangan.
            </p>

        </div>


        <a href="{{ route('government.field-teams.index') }}"
           class="button button-light">
            Kembali
        </a>

    </section>


    <section class="panel">

        <form method="POST"
              action="{{ route(
                  'government.field-teams.update',
                  \App\Helpers\EncryptHelper::encrypt($team->id)
              ) }}">

            @csrf
            @method('PUT')

            <div class="field-team-form">

                @include('government.field-teams.form')

                <div class="form-actions">

                    <a href="{{ route('government.field-teams.index') }}"
                       class="button button-light">
                        Batal
                    </a>

                    <button type="submit"
                            class="button button-primary">
                        Simpan Perubahan
                    </button>

                </div>

            </div>

        </form>

    </section>

@endsection