@extends('layouts.super_admin')


@push('styles')
<link rel="stylesheet" href="{{ asset('css/super_admin/data_sources.css') }}">
@endpush


@section('title', 'Detail Sumber Data | SIGMA')


@section('content')


<section class="page-heading">

    <div>

        <p class="breadcrumb">
            Dashboard / Sumber Data / Detail
        </p>


        <h1>
            {{ $dataSource->name }}
        </h1>


    </div>



    <a
        class="button button-primary"
        href="{{ route(
            'super-admin.data-sources.edit',
            \App\Helpers\EncryptHelper::encrypt($dataSource->id)
        ) }}"
    >
        Edit Data
    </a>


</section>




<section class="panel info-card">


    <div class="info-item">

        <b>
            Provider
        </b>

        <strong>
            {{ $dataSource->provider }}
        </strong>

    </div>



    <div class="info-item">

        <b>
            Tipe
        </b>

        <strong>
            {{ ucfirst($dataSource->type) }}
        </strong>

    </div>




    <div class="info-item">

        <b>
            Endpoint API
        </b>

        <strong>
            {{ $dataSource->api_endpoint ?? '—' }}
        </strong>

    </div>




    <div class="info-item">

        <b>
            Status
        </b>

        <strong>
            {{ ucfirst($dataSource->status) }}
        </strong>

    </div>




    <div class="info-item">

        <b>
            Credential
        </b>

        <strong>
            {{ $dataSource->credentials_key
                ? 'Tersimpan (disamarkan)'
                : '—'
            }}
        </strong>

    </div>


</section>


@endsection