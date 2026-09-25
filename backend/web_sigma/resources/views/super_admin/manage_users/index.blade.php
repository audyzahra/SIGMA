@extends('layouts.super_admin')
@section('title', 'Manajemen Pengguna | SIGMA')
@section('content')
    <section class="page-heading">
        <div>
            <p class="breadcrumb">Dashboard / Manajemen Pengguna</p>
            <h1>Manajemen Pengguna</h1>
            <p>Manajemen pengguna SIGMA.</p>
        </div><a class="button button-primary" href="{{ route('super-admin.manage-users.create') }}">Tambah Pengguna</a>
    </section>

    <form class="panel filters" method="GET" id="filterForm">

        <input id="searchInput" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email">

        <select name="role" id="roleFilter">
            <option value="">Semua Role</option>

            @foreach (\Spatie\Permission\Models\Role::orderBy('name')->get() as $role)
                <option value="{{ $role->name }}" @selected(request('role') === $role->name)>
                    {{ $role->name }}
                </option>
            @endforeach

        </select>


        <select name="per_page" id="perPage">

            @foreach ([5, 25, 50, 100] as $size)
                <option value="{{ $size }}" @selected(request('per_page', 5) == $size)>
                    {{ $size }} Data
                </option>
            @endforeach

        </select>


        <button type="submit" class="button button-search">
    <i data-lucide="search"></i>
    <span>Cari</span>
</button>

    </form>
    <section class="panel"><x-sigma.data-table>
            <thead>
                <tr>
                    <th class="text-center">ID</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th class="text-center">Role</th>
                    <th class="text-center action-column">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td class="text-center">{{ $user->id }}</td>
                        <td><b>{{ $user->name }}</b></td>
                        <td>{{ $user->email }}</td>
                        <td class="text-center">{{ $user->getRoleNames()->join(', ') ?: '-' }}</td>
                        <td class="text-center action-column">
                            <div class="table-actions">
                                {{-- Detail --}}
                                <a href="{{ route('super-admin.manage-users.show', \App\Helpers\EncryptHelper::encrypt($user->id)) }}"
                                    class="action-btn detail" title="Detail Pengguna">

                                    <i data-lucide="eye"></i>

                                </a>


                                {{-- Edit --}}
                                <a href="{{ route('super-admin.manage-users.edit', \App\Helpers\EncryptHelper::encrypt($user->id)) }}"
                                    class="action-btn edit" title="Edit Pengguna">

                                    <i data-lucide="square-pen"></i>

                                </a>


                                {{-- Hapus --}}
                                <form class="inline-action" method="POST"
                                    action="{{ route('super-admin.manage-users.destroy', \App\Helpers\EncryptHelper::encrypt($user->id)) }}">

                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="action-btn delete" title="Hapus Pengguna"
                                        onclick="return confirm('Hapus pengguna ini?')">

                                        <i data-lucide="trash-2"></i>

                                    </button>
                                </form>
                            </div>
                        </td>
                </tr>@empty<tr>
                        <td colspan="5">Belum ada pengguna.</td>
                    </tr>
                @endforelse
            </tbody>
        </x-sigma.data-table>{{ $users->links() }}</section>

    <script>
        let timer;

        const search = document.getElementById('searchInput');

        search.addEventListener('keyup', function() {

            clearTimeout(timer);

            timer = setTimeout(() => {

                document.getElementById('filterForm').submit();

            }, 100);

        });


        document.getElementById('perPage')
            .addEventListener('change', function() {

                document.getElementById('filterForm').submit();

            });

        document.getElementById('roleFilter')
            .addEventListener('change', function() {

                document.getElementById('filterForm').submit();

            });

        lucide.createIcons();
    </script>
@endsection
