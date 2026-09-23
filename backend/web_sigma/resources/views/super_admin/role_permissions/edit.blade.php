@extends('layouts.super_admin')
@section('title', 'Edit Role | SIGMA')
@section('content')
    <section class="page-heading">
        <h1>Edit Role: {{ $role->name }}</h1>
    </section>
    <section class="panel">
        <form method="POST"
            action="{{ route('super-admin.role-permissions.update', \App\Helpers\EncryptHelper::encrypt($role->id)) }}">
            @error('name')
                <small>{{ $message }}</small>
            @enderror
            </label>
            <h3>Permission</h3>
            <div class="form-grid">
                @foreach ($permissions as $permission)
                    <label><input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                            @checked(in_array($permission->name, old('permissions', $role->permissions->pluck('name')->all())))> {{ $permission->name }}</label>
                @endforeach
            </div><button class="button button-primary">Simpan Perubahan</button>
        </form>
    </section>
@endsection
