@extends('layouts.government')

@section('title', 'Keamanan Akun | SIGMA')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/government/account/password.css') }}">
@endpush

@section('content')

    <div class="dashboard-page">

        <section class="page-heading">
            <div>
                <p class="breadcrumb">
                    Beranda › Pengaturan › Keamanan
                </p>

                <h1>Keamanan Akun</h1>

                <p>
                    Kelola keamanan dan password akun Pemerintah SIGMA.
                </p>
            </div>
        </section>

        {{-- Back --}}
            <a href="{{ route('government.account.index') }}" class="settings-back">
                <i data-lucide="arrow-left"></i>
                Kembali ke Pengaturan
            </a>

        <section class="dashboard-card">

            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif


            <form action="{{ route('government.account.password.update') }}" method="POST">

                @csrf
                @method('PUT')


                <div class="form-group">

                    <label for="current_password">
                        Password Saat Ini
                    </label>

                    <input type="password" id="current_password" name="current_password" required>

                    @error('current_password')
                        <small class="text-danger">
                            {{ $message }}
                        </small>
                    @enderror

                </div>


                <div class="form-group">

                    <label for="password">
                        Password Baru
                    </label>

                    <input type="password" id="password" name="password" required>

                    @error('password')
                        <small class="text-danger">
                            {{ $message }}
                        </small>
                    @enderror

                </div>


                <div class="form-group">

                    <label for="password_confirmation">
                        Konfirmasi Password Baru
                    </label>

                    <input type="password" id="password_confirmation" name="password_confirmation" required>

                </div>


                <div class="form-actions">

                    <button type="submit">
                        <i data-lucide="save"></i>
                        Simpan Password
                    </button>

                </div>

            </form>

        </section>

    </div>

@endsection
