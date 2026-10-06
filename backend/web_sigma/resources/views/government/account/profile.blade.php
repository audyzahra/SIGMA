@extends('layouts.government')

@section('title', 'Profil Saya | SIGMA')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/government/account/profile.css') }}">

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

        {{-- Back --}}
            <a href="{{ route('government.account.index') }}" class="settings-back">
                <i data-lucide="arrow-left"></i>
                Kembali ke Pengaturan
            </a>

        <section class="government-profile-card">

            {{-- Success Message --}}
            @if (session('success'))
                <div class="government-profile-alert government-profile-alert--success">
                    <span class="government-profile-alert__icon">
                        ✓
                    </span>

                    <span>
                        {{ session('success') }}
                    </span>
                </div>
            @endif

            <form action="{{ route('government.account.profile.update') }}" method="POST" class="government-profile-form">
                @csrf
                @method('PUT')


                {{-- Nama --}}
                <div class="government-profile-field">

                    <label for="name">
                        Nama
                    </label>

                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}"
                        placeholder="Masukkan nama" autocomplete="name" required>

                    @error('name')
                        <small class="government-profile-error">
                            {{ $message }}
                        </small>
                    @enderror

                </div>


                {{-- Email --}}
                <div class="government-profile-field">

                    <label for="email">
                        Email
                    </label>

                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
                        placeholder="Masukkan email" autocomplete="email" required>

                    @error('email')
                        <small class="government-profile-error">
                            {{ $message }}
                        </small>
                    @enderror

                </div>


                {{-- Button --}}
                <div class="government-profile-actions">

                    <button type="submit" class="government-profile-button">
                        <i data-lucide="save"></i>
                        Simpan Perubahan
                    </button>

                </div>

            </form>

        </section>

    </div>

@endsection
