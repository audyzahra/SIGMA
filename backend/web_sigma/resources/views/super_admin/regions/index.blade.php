@extends('layouts.super_admin')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/super_admin/regions.css') }}">
@endpush
@push('scripts')
    <script src="{{ asset('js/super_admin/regions.js') }}"></script>
@endpush
@section('title', 'Wilayah | SIGMA')
@section('content')
    <section class="page-heading">
        <div>
            <p class="breadcrumb">
                Dashboard / Manajemen Wilayah
            </p>
            <h1>Manajemen Wilayah</h1>
            <p>Wilayah administratif SIGMA.</p>
        </div>
        <a class="button button-primary" href="{{ route('super-admin.regions.create') }}">Tambah Wilayah</a>
    </section>

    <form class="panel filters region-filter" method="GET" id="region-filter"
        action="{{ route('super-admin.regions.index') }}">
        <input name="search" id="search-region" value="{{ request('search') }}" placeholder="Cari nama atau kode">
        <select
            name="level" id="level-filter">
            <option value="">Semua level</option>
            @foreach (['province', 'regency', 'district'] as $level)
                <option value="{{ $level }}" @selected(request('level') === $level)>{{ ucfirst($level) }}</option>
            @endforeach
        </select>

        @if (request()->hasAny(['search', 'level']))
            <a href="{{ route('super-admin.regions.index') }}" class="button button-light">
                Reset
            </a>
        @endif

    </form>

    <section class="panel">
        <div id="region-result">

            @include('super_admin.regions.table')

        </div>
    </section>
@endsection
