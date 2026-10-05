@extends('layouts.government')

@section('title', 'Profil Saya | SIGMA')

@section('content')

<div class="dashboard-page">

    <section class="page-heading">
        <div>
            <p class="breadcrumb">Beranda › Pengaturan › Profil Saya</p>

            <h1>Profil Saya</h1>

            <p>
                Kelola informasi akun Pemerintah SIGMA.
            </p>
        </div>
    </section>


    <section class="dashboard-card">

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <form
            action="{{ route('government.account.profile.update') }}"
            method="POST"
        >
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="name">
                    Nama
                </label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', $user->name) }}"
                    required
                >

                @error('name')
                    <small class="text-danger">
                        {{ $message }}
                    </small>
                @enderror
            </div>


            <div class="form-group">
                <label for="email">
                    Email
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email', $user->email) }}"
                    required
                >

                @error('email')
                    <small class="text-danger">
                        {{ $message }}
                    </small>
                @enderror
            </div>


            <div class="form-actions">

                <button type="submit">
                    Simpan Perubahan
                </button>

            </div>

        </form>

    </section>

</div>

@endsection