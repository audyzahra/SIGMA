@extends('layouts.super_admin')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/super_admin/role_permissions.css') }}">
@endpush
@section('title', 'Role & Permission | SIGMA')

@section('content')


    <section class="page-heading">

        <div>

            <p class="breadcrumb">
                Dashboard / Role & Permission
            </p>

            <h1>
                Role & Permission
            </h1>

            <p>
                Kelola hak akses pengguna SIGMA.
            </p>

        </div>


        <a class="button button-primary" href="{{ route('super-admin.role-permissions.create') }}">

            Tambah Role

        </a>


    </section>




    <section class="panel">


        <x-sigma.data-table class="role-permission-table">


            <thead>

                <tr>

                    <th>
                        Role
                    </th>


                    <th>
                        Jumlah Permission
                    </th>


                    <th class="action-column">
                        Aksi
                    </th>


                </tr>

            </thead>



            <tbody>


                @forelse($roles as $role)
                    <tr>


                        <td>

                            <b>
                                {{ $role->name }}
                            </b>

                        </td>



                        <td>


                            <span class="pill">

                                {{ $role->permissions_count }}
                                Permission

                            </span>


                        </td>



                        <td>


                            <div class="table-actions">


                                <a href="{{ route('super-admin.role-permissions.show', \App\Helpers\EncryptHelper::encrypt($role->id)) }}"
                                    class="action-btn detail" title="Detail">


                                    <i data-lucide="eye"></i>


                                </a>



                                <a href="{{ route('super-admin.role-permissions.edit', \App\Helpers\EncryptHelper::encrypt($role->id)) }}"
                                    class="action-btn edit" title="Edit">


                                    <i data-lucide="square-pen"></i>


                                </a>




                                @if ($role->name !== 'super_admin')
                                    <form method="POST"
                                        action="{{ route('super-admin.role-permissions.destroy', \App\Helpers\EncryptHelper::encrypt($role->id)) }}"
                                        class="inline-action delete-form">


                                        @csrf
                                        @method('DELETE')


                                        <button type="submit" class="action-btn delete delete-confirm" title="Hapus">

                                            <i data-lucide="trash-2"></i>

                                        </button>


                                    </form>
                                @endif



                            </div>


                        </td>


                    </tr>



                @empty


                    <tr>

                        <td colspan="3">

                            Belum ada role.

                        </td>

                    </tr>
                @endforelse



            </tbody>


        </x-sigma.data-table>


    </section>


@endsection
