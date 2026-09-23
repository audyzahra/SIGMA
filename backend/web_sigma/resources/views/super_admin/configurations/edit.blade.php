@extends('layouts.super_admin')

@section('content')
    <section class="page-heading">
        <h1>Edit Konfigurasi</h1>
    </section>

    <form class="panel" method="POST"
        action="{{ route('super-admin.configurations.update', \App\Helpers\EncryptHelper::encrypt($configuration->id)) }}">
        @csrf
        @method('PUT')

        @include('super_admin.configurations.form')

        <button class="button button-primary">
            Simpan
        </button>
    </form>
@endsection
