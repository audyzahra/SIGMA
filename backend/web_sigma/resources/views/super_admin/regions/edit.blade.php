@extends('layouts.super_admin')

@section('content')
    <section class="page-heading">
        <h1>Edit Wilayah</h1>
    </section>
    <form class="panel" method="POST"
        action="{{ route('super-admin.regions.update', \App\Helpers\EncryptHelper::encrypt($region->id)) }}">
        @csrf
        @method('PUT')
        @include('super_admin.regions.form')

        <div class="modal-actions">

            <a class="button button-light"
                href="{{ route('super-admin.regions.show', \App\Helpers\EncryptHelper::encrypt($region->id)) }}">
                Batal
            </a>

            <button class="button button-primary">
                Simpan
            </button>

        </div>
    </form>
@endsection
