@extends('layouts.super_admin')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/super_admin/organizations.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('js/super_admin/organizations.js') }}"></script>
@endpush
@section('title', 'Organisasi | SIGMA')
@section('content')
    <section class="page-heading">

        <div>
            <p class="breadcrumb">
                Dashboard / Organisasi
            </p>

            <h1>
                Manajemen Organisasi
            </h1>

            <p>
                Direktori organisasi yang terhubung dengan sistem SIGMA.
            </p>
        </div>


        <a class="button button-primary" href="{{ route('super-admin.organizations.create') }}">
            Tambah Organisasi
        </a>

    </section>
    <form class="panel filters organization-filter" id="organization-filter" method="GET" action="{{ route('super-admin.organizations.index') }}">

        <input name="search" type="text" id="search-organization" value="{{ request('search') }}"
            placeholder="Cari organisasi">


        <select name="type" id="type-filter">

            <option value="">
                Semua tipe
            </option>

            @foreach (['government', 'team', 'company'] as $type)
                <option value="{{ $type }}" @selected(request('type') === $type)>
                    {{ ucfirst($type) }}
                </option>
            @endforeach

        </select>



        <select name="status" id="status-filter">

            <option value="">
                Semua status
            </option>


            @foreach (['active', 'inactive'] as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>
                    {{ ucfirst($status) }}
                </option>
            @endforeach


        </select>

        @if (request()->hasAny(['search', 'type', 'status']))
            <a href="{{ route('super-admin.organizations.index') }}" class="button button-light">
                Reset
            </a>
        @endif

    </form>

    <section class="panel" id="organization-result">

        @include('super_admin.organizations.table')

    </section>
@endsection
