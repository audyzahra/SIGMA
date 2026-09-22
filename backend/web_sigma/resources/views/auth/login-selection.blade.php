@extends('layouts.auth')

@section('title', 'SIGMA Command Center')

@section('content')
<main class="flex min-h-screen items-center justify-center px-6 py-12">
    <section class="w-full max-w-3xl rounded-2xl bg-white p-8 shadow-xl shadow-slate-200/70">
        <div class="mb-8 flex items-center gap-4">
            <img
                src="{{ asset('images/logo.png') }}"
                alt="Logo SIGMA"
                class="h-14 w-14 rounded-2xl object-cover shadow-sm"
            >
            <div>
                <p class="text-2xl font-black tracking-tight">SIGMA Command Center</p>
                <p class="text-sm text-slate-500">Disaster Intelligence System</p>
            </div>
        </div>
        <h1 class="text-2xl font-bold">Pilih akses dashboard</h1>
        <div class="mt-6 grid gap-5 md:grid-cols-2">
            <a href="{{ route('login.show', ['access' => 'super-admin']) }}" class="rounded-2xl border border-slate-200 p-6 shadow-sm transition hover:-translate-y-1 hover:border-sigma-red hover:shadow-lg">
                <span class="text-3xl">⚙</span>
                <h2 class="mt-4 text-xl font-bold">Super Admin</h2>
                <p class="mt-2 text-sm text-slate-500">Mengelola sistem, pengguna, dan konfigurasi.</p>
                <span class="mt-6 inline-block font-bold text-sigma-red">Login Super Admin →</span>
            </a>
            <a href="{{ route('login.show', ['access' => 'pemerintah']) }}" class="rounded-2xl border border-slate-200 p-6 shadow-sm transition hover:-translate-y-1 hover:border-sigma-orange hover:shadow-lg">
                <span class="text-3xl">⌖</span>
                <h2 class="mt-4 text-xl font-bold">Pemerintah</h2>
                <p class="mt-2 text-sm text-slate-500">Monitoring wilayah, laporan dan kondisi karhutla.</p>
                <span class="mt-6 inline-block font-bold text-sigma-orange">Login Pemerintah →</span>
            </a>
        </div>
    </section>
</main>
@endsection
