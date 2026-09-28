@extends('layouts.super_admin')


@push('styles')
    <link rel="stylesheet" href="{{ asset('css/super_admin/audit_logs.css') }}">
@endpush


@section('title', 'Detail Audit Trail | SIGMA')


@section('content')


    <section class="page-heading">


        <div>


            <p class="breadcrumb">
                Dashboard / Audit Trail / Detail
            </p>


            <h1>
                Detail Audit Trail
            </h1>

        </div>



    </section>




    <section class="panel info-card">


        <div class="info-item">

            <b>
                Pengguna
            </b>


            <strong>
                {{ $auditLog->user?->name ?? 'Sistem' }}
            </strong>


        </div>



        <div class="info-item">

            <b>
                Aksi
            </b>


            <strong>
                {{ $auditLog->action }}
            </strong>


        </div>



        <div class="info-item">

            <b>
                Modul
            </b>


            <strong>
                {{ $auditLog->module }}
            </strong>


        </div>



        <div class="info-item">

            <b>
                Waktu
            </b>


            <strong>
                {{ $auditLog->created_at }}
            </strong>


        </div>



        <div class="info-item full">

            <b>
                Deskripsi
            </b>


            <strong>
                {{ $auditLog->description }}
            </strong>


        </div>


    </section>




    <section class="panel json-panel">


        <h3>
            Nilai Lama
        </h3>


        <pre>
{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
</pre>


        <h3>
            Nilai Baru
        </h3>


        <pre>
{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
</pre>


    </section>


@endsection
