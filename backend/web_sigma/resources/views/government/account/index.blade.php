@extends('layouts.government')

@section('title', 'Pengaturan | SIGMA')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/government/account/index.css') }}">
@endpush

@section('content')

    <div class="dashboard-page">

        <section class="page-heading">
            <div>
                <p class="breadcrumb">
                    BERANDA › PENGATURAN
                </p>

                <h1>Pengaturan</h1>

                <p>
                    Kelola akun dan preferensi Pemerintah SIGMA.
                </p>
            </div>
        </section>


        <section class="settings-grid">

            {{-- Profil --}}
            <a href="{{ route('government.account.profile') }}" class="settings-card">

                <div class="settings-icon">
                    <i data-lucide="user"></i>
                </div>

                <div class="settings-content">
                    <h3>Profil Saya</h3>

                    <p>
                        Kelola informasi dasar akun Pemerintah SIGMA.
                    </p>
                </div>

                <i data-lucide="chevron-right" class="settings-arrow"></i>

            </a>


            {{-- Keamanan --}}
            <a href="{{ route('government.account.password') }}" class="settings-card">

                <div class="settings-icon">
                    <i data-lucide="shield-check"></i>
                </div>

                <div class="settings-content">
                    <h3>Keamanan</h3>

                    <p>
                        Ubah password dan kelola keamanan akun.
                    </p>
                </div>

                <i data-lucide="chevron-right" class="settings-arrow"></i>

            </a>


            {{-- Tampilan --}}
            <a href="{{ route('government.account.appearance') }}" class="settings-card">

                <div class="settings-icon">
                    <i data-lucide="palette"></i>
                </div>

                <div class="settings-content">
                    <h3>Tampilan</h3>

                    <p>
                        Atur tema dan preferensi tampilan dashboard.
                    </p>
                </div>

                <i data-lucide="chevron-right" class="settings-arrow"></i>

            </a>


            {{-- Notifikasi --}}
            <a href="{{ route('government.account.notifications') }}" class="settings-card">

                <div class="settings-icon">
                    <i data-lucide="bell"></i>
                </div>

                <div class="settings-content">
                    <h3>Notifikasi</h3>

                    <p>
                        Atur jenis notifikasi yang ingin diterima.
                    </p>
                </div>

                <i data-lucide="chevron-right" class="settings-arrow"></i>

            </a>

            {{-- Aktivitas --}}
            <a href="{{ route('government.account.activity') }}" class="settings-card">
                <div class="settings-icon">
                    <i data-lucide="history"></i>
                </div>

                <div class="settings-content">
                    <h3>Riwayat Aktivitas</h3>

                    <p>
                        Lihat aktivitas akun Pemerintah SIGMA.
                    </p>
                </div>

                <i data-lucide="chevron-right" class="settings-arrow"></i>

            </a>

        </section>

    </div>

@endsection
