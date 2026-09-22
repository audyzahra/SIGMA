@extends('layouts.super_admin')
@section('title','Edit Organisasi | SIGMA')
@section('content')<section class="page-heading"><h1>Edit Organisasi</h1></section><form method="POST" action="{{ route('super-admin.organizations.update', $organization) }}" class="panel">@csrf @method('PUT') @include('super_admin.organizations.form')<div class="modal-actions"><a class="button button-light" href="{{ route('super-admin.organizations.show', $organization) }}">Batal</a><button class="button button-primary">Simpan Perubahan</button></div></form>@endsection
