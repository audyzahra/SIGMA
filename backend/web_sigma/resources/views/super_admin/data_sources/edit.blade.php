@extends('layouts.super_admin')
@section('content')
    <section class="page-heading">
        <h1>Edit Data Source</h1>
    </section>
    <form class="panel" method="POST"
        action="{{ route('super-admin.data-sources.update', \App\Helpers\EncryptHelper::encrypt($dataSource->id)) }}">
        @csrf
        @method('PUT') @include('super_admin.data_sources.form')<button class="button button-primary">Simpan</button></form>
@endsection
