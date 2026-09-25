@extends('layouts.super_admin')
@section('title', 'Detail Wilayah | SIGMA')
@section('content')
    <section class="page-heading">
        <div>
            <p class="breadcrumb">
                Dashboard / Manajemen Wilayah / Detail
            </p>

            <h1>{{ $region->name }}</h1>
        </div>

        <a class="button button-primary"
            href="{{ route('super-admin.regions.edit', \App\Helpers\EncryptHelper::encrypt($region->id)) }}">
            Edit Wilayah
        </a>
    </section>

    <section class="panel info-card">
        <b>Kode</b>
        <strong>{{ $region->code ?? '—' }}</strong>

        <b>Level</b>
        <strong>{{ ucfirst($region->level) }}</strong>

        <b>Wilayah Induk</b>
        <strong>{{ $region->parent?->name ?? '—' }}</strong>

        <b>Luas Area</b>
        <strong>{{ $region->area_size ?? '—' }}</strong>

        <b>Subwilayah</b>
        <strong>
            {{ $region->children->pluck('name')->join(', ') ?: '—' }}
        </strong>

        <b>Organisasi</b>
        <strong>
            {{ $region->organizations->pluck('name')->join(', ') ?: '—' }}
        </strong>
    </section>
@endsection
