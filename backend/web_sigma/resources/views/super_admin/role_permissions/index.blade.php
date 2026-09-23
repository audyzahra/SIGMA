@extends('layouts.super_admin')
@section('title', 'Role & Permission | SIGMA')
@section('content')
    <section class="page-heading">
        <h1>Role & Permission</h1><a class="button button-primary"
            href="{{ route('super-admin.role-permissions.create') }}">Tambah Role</a>
    </section>
    <section class="panel"><x-sigma.data-table>
            <thead>
                <tr>
                    <th>Role</th>
                    <th>Permission</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($roles as $role)
                    <tr>
                        <td>{{ $role->name }}</td>
                        <td>{{ $role->permissions_count }}</td>
                        <td><a
                                href="{{ route('super-admin.role-permissions.show', \App\Helpers\EncryptHelper::encrypt($role->id)) }}">
                                Detail
                            </a>
                            <a
                                href="{{ route('super-admin.role-permissions.edit', \App\Helpers\EncryptHelper::encrypt($role->id)) }}">
                                Edit
                            </a>
                            <form method="POST"
                                action="{{ route('super-admin.role-permissions.destroy', \App\Helpers\EncryptHelper::encrypt($role->id)) }}"
                                style="display:inline" onsubmit="return confirm('Hapus role?')">

                                @csrf
                                @method('DELETE')

                                <button>Hapus</button>
                            </form>
                        </td>
                </tr>@empty<tr>
                        <td colspan="3">Belum ada role.</td>
                    </tr>
                @endforelse
            </tbody>
        </x-sigma.data-table>{{ $roles->links() }}</section>
@endsection
