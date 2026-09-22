@extends('layouts.super_admin')
@section('title','Tambah Role | SIGMA')
@section('content')<section class="page-heading"><h1>Tambah Role</h1></section><section class="panel"><form method="POST" action="{{ route('super-admin.role-permissions.store') }}">@csrf @include('super_admin.role_permissions.form')<button class="button button-primary">Simpan</button></form></section>@endsection
