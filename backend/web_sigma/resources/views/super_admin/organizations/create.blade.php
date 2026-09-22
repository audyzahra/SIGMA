@extends('layouts.super_admin')
@section('title','Tambah Organisasi | SIGMA')
@section('content')<section class="page-heading"><h1>Tambah Organisasi</h1></section><form method="POST" action="{{ route('super-admin.organizations.store') }}" class="panel">@csrf @include('super_admin.organizations.form')<div class="modal-actions"><a class="button button-light" href="{{ route('super-admin.organizations.index') }}">Batal</a><button class="button button-primary">Simpan</button></div></form>@endsection
