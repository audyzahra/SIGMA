@extends('layouts.super_admin')
@section('title','Tambah Pengguna | SIGMA')
@section('content')
<section class="page-heading"><h1>Tambah Pengguna</h1></section><section class="panel"><form method="POST" action="{{ route('super-admin.manage-users.store') }}">@csrf @include('super_admin.manage_users.form')<button class="button button-primary">Simpan</button></form></section>
@endsection
