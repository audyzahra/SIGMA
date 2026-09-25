@extends('layouts.super_admin')
@section('title', 'Detail Organisasi | SIGMA')
@section('content')
    <section class="page-heading">
        <div>
            <p class="breadcrumb">
                Dashboard / Manajemen Organisasi / Detail
            </p>
            <h1>{{ $organization->name }}</h1>
        </div>
        <a class="button button-primary"
            href="{{ route('super-admin.organizations.edit', \App\Helpers\EncryptHelper::encrypt($organization->id)) }}">
            Edit Organisasi
        </a>
    </section>
    <section class="panel info-card">
        <b>Tipe</b><strong>{{ ucfirst($organization->type) }}</strong><b>Status</b><strong>{{ ucfirst($organization->status) }}</strong><b>Wilayah</b><strong>{{ $organization->region?->name ?? '—' }}</strong><b>Email</b><strong>{{ $organization->email ?? '—' }}</strong><b>Telepon</b><strong>{{ $organization->phone ?? '—' }}</strong><b>Alamat</b><strong>{{ $organization->address ?? '—' }}</strong>
    </section>
@endsection
