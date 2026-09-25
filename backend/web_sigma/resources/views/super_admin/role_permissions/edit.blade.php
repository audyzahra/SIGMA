@extends('layouts.super_admin')

@section('title', 'Edit Role | SIGMA')

@section('content')

    <section class="page-heading">
        <div>
            <p class="breadcrumb">
                Dashboard / Role & Permission / Edit
            </p>

            <h1>
                Edit Role
            </h1>
        </div>

        <a class="button button-light"
            href="{{ route('super-admin.role-permissions.show', \App\Helpers\EncryptHelper::encrypt($role->id)) }}">
            Kembali
        </a>
    </section>

    <section class="panel">
        <form method="POST"
            action="{{ route('super-admin.role-permissions.update', \App\Helpers\EncryptHelper::encrypt($role->id)) }}">
            @csrf
            @method('PUT')

            <label>
                Nama Role
                <input type="text" name="name" value="{{ old('name', $role->name) }}" required>
                @error('name')
                    <small>
                        {{ $message }}
                    </small>
                @enderror
            </label>

            <h3>
                Permission
            </h3>

            <div class="form-grid">
                @foreach ($permissions as $permission)
                    <label>
                        <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                            @checked(in_array($permission->name, old('permissions', $role->permissions->pluck('name')->all())))>
                        {{ $permission->name }}
                    </label>
                @endforeach
            </div>

            <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:24px;">
                <a class="button button-light"
                    href="{{ route('super-admin.role-permissions.show', \App\Helpers\EncryptHelper::encrypt($role->id)) }}">
                    Batal
                </a>
                <button type="submit" class="button button-primary">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </section>
@endsection
