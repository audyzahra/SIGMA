@extends('layouts.auth')

@section('title', 'Login | SIGMA')

@section('content')
<main class="flex min-h-screen items-center justify-center px-6 py-12">
    <section class="w-full max-w-md rounded-2xl bg-white p-8 shadow-xl shadow-slate-200/70">
        <div class="mb-8 flex items-center gap-4">
            <img
                src="{{ asset('images/logo.png') }}"
                alt="Logo SIGMA"
                class="h-14 w-14 rounded-2xl object-cover shadow-sm"
            >
            <div>
                <p class="text-2xl font-black tracking-tight">SIGMA</p>
                <p class="text-sm text-slate-500">Disaster Intelligence</p>
            </div>
        </div>

        <div class="mb-6 flex items-center gap-2 rounded-xl bg-green-50 px-4 py-3 text-sm text-green-700">
            <span class="h-2 w-2 rounded-full bg-sigma-green"></span>
            Sistem operasional
        </div>

        <h1 class="text-2xl font-bold">Masuk ke command center</h1>
        <p class="mt-2 text-sm text-slate-500">{{ $role === 'government' ? 'Login Pemerintah' : 'Login Super Admin' }}</p>

        @if ($errors->any())
            <div class="mt-5 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login.store', ['access' => $access]) }}" class="mt-6 space-y-5">
            @csrf
            <input type="hidden" name="role" value="{{ $role }}">
            <label class="block text-sm font-semibold">
                Email
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                    class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none transition focus:border-sigma-red focus:ring-2 focus:ring-red-100">
            </label>
            <label class="block text-sm font-semibold">
                Password
                <input type="password" name="password" required
                    class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none transition focus:border-sigma-red focus:ring-2 focus:ring-red-100">
            </label>
            <button type="submit" class="w-full rounded-xl bg-sigma-red px-4 py-3 font-bold text-white transition hover:bg-red-800">
                Masuk
            </button>
        </form>
    </section>
</main>
@endsection
