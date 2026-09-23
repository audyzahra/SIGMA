@extends('layouts.super_admin')

@section('title', 'Edit Pengguna | SIGMA')

@section('content')

    <section class="page-heading">

        <div>
            <p class="breadcrumb">
                Dashboard / Manajemen Pengguna / Edit
            </p>

            <h1>
                Edit Pengguna
            </h1>
        </div>


        <a class="button button-light"
            href="{{ route('super-admin.manage-users.show', \App\Helpers\EncryptHelper::encrypt($user->id)) }}">

            Kembali

        </a>

    </section>

    <section class="panel">

        <form method="POST"
            action="{{ route('super-admin.manage-users.update', \App\Helpers\EncryptHelper::encrypt($user->id)) }}">

            @csrf
            @method('PUT')


            @include('super_admin.manage_users.form')


            <div style="display: flex; justify-content: flex-end; gap:12px; margin-top:24px;">

                <a class="button button-light"
                    href="{{ route('super-admin.manage-users.show', \App\Helpers\EncryptHelper::encrypt($user->id)) }}">

                    Batal

                </a>


                <button type="submit" class="button button-primary">

                    Simpan Perubahan

                </button>

            </div>


        </form>

    </section>


@endsection
