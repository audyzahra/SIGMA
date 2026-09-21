@extends('layouts.super_admin')
@section('title','Edit Pengguna | SIGMA')
@section('content')
<section class="page-heading"><h1>Edit Pengguna</h1></section><section class="panel"><form method="POST" action="{{ route('super-admin.manage-users.update',$user) }}">@csrf @method('PUT') @include('super_admin.manage_users.form')<button class="button button-primary">Simpan Perubahan</button></form></section>
@endsection
