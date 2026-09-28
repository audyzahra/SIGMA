@extends('layouts.super_admin')

@section('title', 'Tambah Pengguna | SIGMA')

@section('content')
    <section class="page-heading">
        <div>
            <p class="breadcrumb">
                Dashboard / Manajemen Pengguna / Tambah
            </p>

            <h1>
                Tambah Pengguna
            </h1>
        </div>

        <a class="button button-light"
            href="{{ route('super-admin.manage-users.index') }}">
            Kembali
        </a>
    </section>

    <section class="panel">
        <form method="POST" action="{{ route('super-admin.manage-users.store') }}">
            @csrf

            @include('super_admin.manage_users.form')

            <div style="display: flex; justify-content: flex-end; margin-top: 24px;">
                <button type="submit" class="button button-primary">
                    Simpan
                </button>
            </div>
        </form>
    </section>
@endsection