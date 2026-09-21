@extends('layouts.super_admin')
@section('title','Detail Pengguna | SIGMA')
@section('content')
<section class="page-heading"><div><p class="breadcrumb">Dashboard / Manajemen Pengguna / Detail</p><h1>{{ $user->name }}</h1></div><a class="button button-primary" href="{{ route('super-admin.manage-users.edit',$user) }}">Edit</a></section><section class="panel"><dl><dt>ID</dt><dd>{{ $user->id }}</dd><dt>Email</dt><dd>{{ $user->email }}</dd><dt>Role</dt><dd>{{ $user->getRoleNames()->join(', ') }}</dd><dt>Dibuat</dt><dd>{{ $user->created_at }}</dd><dt>Diperbarui</dt><dd>{{ $user->updated_at }}</dd></dl></section>
@endsection
