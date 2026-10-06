@extends('layouts.government')

@section('title', 'Riwayat Aktivitas | SIGMA')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/government/account/activity.css') }}">
@endpush

@section('content')

    <div class="activity-page">

        {{-- Header --}}

        <section class="page-heading">
            <div>
                <p class="breadcrumb">Beranda › Pengaturan › Riwayat Aktivitas</p>

                <h1>Riwayat Aktivitas</h1>

                <p>
                    Pantau aktivitas yang dilakukan pada akun Pemerintah SIGMA.
                </p>
            </div>
        </section>

        {{-- Back --}}
            <a href="{{ route('government.account.index') }}" class="settings-back">
                <i data-lucide="arrow-left"></i>
                Kembali ke Pengaturan
            </a>

        {{-- Filter --}}
        <section class="activity-filter">

            <form
                method="GET"
                action="{{ route('government.account.activity') }}"
                id="activityFilterForm"
            >

                <div class="filter-grid">

                    {{-- Search --}}
                    <div class="filter-field filter-search">

                        <label for="search">
                            Cari Aktivitas
                        </label>

                        <div class="input-wrapper">

                            <i data-lucide="search"></i>

                            <input
                                type="text"
                                id="search"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Cari aktivitas..."
                            >

                        </div>

                    </div>


                    {{-- Module --}}
                    <div class="filter-field">

                        <label for="module">
                            Modul
                        </label>

                        <div class="select-wrapper">

                            <select name="module" id="module">

                                <option value="">
                                    Semua modul
                                </option>

                                @foreach ($modules as $module)

                                    <option
                                        value="{{ $module }}"
                                        @selected(request('module') === $module)
                                    >
                                        {{ ucwords(str_replace(['_', '-'], ' ', $module)) }}
                                    </option>

                                @endforeach

                            </select>

                            <i data-lucide="chevron-down"></i>

                        </div>

                    </div>


                    {{-- Event --}}
                    <div class="filter-field">

                        <label for="event">
                            Aktivitas
                        </label>

                        <div class="select-wrapper">

                            <select name="event" id="event">

                                <option value="">
                                    Semua aktivitas
                                </option>

                                @foreach ($events as $event)

                                    <option
                                        value="{{ $event }}"
                                        @selected(request('event') === $event)
                                    >
                                        {{ ucwords(str_replace(['_', '-'], ' ', $event)) }}
                                    </option>

                                @endforeach

                            </select>

                            <i data-lucide="chevron-down"></i>

                        </div>

                    </div>


                    {{-- Date From --}}
                    <div class="filter-field">

                        <label for="date_from">
                            Dari Tanggal
                        </label>

                        <input
                            type="date"
                            id="date_from"
                            name="date_from"
                            value="{{ request('date_from') }}"
                        >

                    </div>


                    {{-- Date To --}}
                    <div class="filter-field">

                        <label for="date_to">
                            Sampai Tanggal
                        </label>

                        <input
                            type="date"
                            id="date_to"
                            name="date_to"
                            value="{{ request('date_to') }}"
                        >

                    </div>

                </div>


                <div class="filter-actions">

                    <button
                        type="submit"
                        class="filter-button"
                    >
                        <i data-lucide="filter"></i>
                        Terapkan Filter
                    </button>

                    <a
                        href="{{ route('government.account.activity') }}"
                        class="reset-button"
                    >
                        <i data-lucide="rotate-ccw"></i>
                        Reset
                    </a>

                </div>

            </form>

        </section>


        {{-- Activity --}}
        <section class="activity-card">

            <div class="activity-card-header">

                <div>
                    <h2>Aktivitas Akun</h2>

                    <p>
                        {{ $activities->total() }} aktivitas ditemukan
                    </p>
                </div>

                <div class="activity-status">

                    <span class="status-dot"></span>

                    Aktivitas akun

                </div>

            </div>


            @if ($activities->count())

                <div class="activity-list">

                    @foreach ($activities as $activity)

                        @php

                            $event = strtolower($activity->event ?? '');

                            $icon = match ($event) {

                                'created' => 'plus-circle',

                                'updated' => 'pencil',

                                'deleted' => 'trash-2',

                                'login' => 'log-in',

                                'logout' => 'log-out',

                                'verified' => 'badge-check',

                                default => 'activity',

                            };

                            $eventLabel = match ($event) {

                                'created' => 'Data Ditambahkan',

                                'updated' => 'Data Diperbarui',

                                'deleted' => 'Data Dihapus',

                                'login' => 'Login',

                                'logout' => 'Logout',

                                'verified' => 'Verifikasi',

                                default => $activity->description,

                            };

                        @endphp


                        <article class="activity-item">

                            <div class="activity-icon">
                                <i data-lucide="{{ $icon }}"></i>
                            </div>


                            <div class="activity-content">

                                <div class="activity-main">

                                    <div>

                                        <div class="activity-title-row">

                                            <h3>
                                                {{ $eventLabel }}
                                            </h3>

                                            @if ($activity->log_name)
                                                <span class="activity-module">
                                                    {{ ucwords(str_replace(['_', '-'], ' ', $activity->log_name)) }}
                                                </span>
                                            @endif

                                        </div>


                                        <p>
                                            {{ $activity->description }}
                                        </p>

                                    </div>


                                    <time
                                        datetime="{{ $activity->created_at?->toIso8601String() }}"
                                        class="activity-time"
                                    >
                                        {{ $activity->created_at?->translatedFormat('d M Y, H:i') }}
                                    </time>

                                </div>


                                <div class="activity-meta">

                                    @if ($activity->properties?->get('ip'))
                                        <span>
                                            <i data-lucide="globe"></i>
                                            IP {{ $activity->properties->get('ip') }}
                                        </span>
                                    @endif


                                    @if ($activity->properties?->get('user_agent'))

                                        <span
                                            class="device-info"
                                            title="{{ $activity->properties->get('user_agent') }}"
                                        >
                                            <i data-lucide="monitor"></i>
                                            Perangkat / Browser
                                        </span>

                                    @endif


                                    @if ($activity->subject_type)

                                        <span>
                                            <i data-lucide="file-text"></i>
                                            {{ class_basename($activity->subject_type) }}
                                        </span>

                                    @endif

                                </div>

                            </div>

                        </article>

                    @endforeach

                </div>


                {{-- Pagination --}}
                @if ($activities->hasPages())

                    <div class="activity-pagination">

                        <div class="pagination-info">

                            Menampilkan

                            <strong>
                                {{ $activities->firstItem() }}
                            </strong>

                            –

                            <strong>
                                {{ $activities->lastItem() }}
                            </strong>

                            dari

                            <strong>
                                {{ $activities->total() }}
                            </strong>

                            aktivitas

                        </div>


                        <div class="pagination-links">

                            @if ($activities->onFirstPage())

                                <span class="pagination-button disabled">
                                    <i data-lucide="chevron-left"></i>
                                </span>

                            @else

                                <a
                                    href="{{ $activities->previousPageUrl() }}"
                                    class="pagination-button"
                                >
                                    <i data-lucide="chevron-left"></i>
                                </a>

                            @endif


                            @foreach (
                                $activities->getUrlRange(
                                    max(1, $activities->currentPage() - 2),
                                    min($activities->lastPage(), $activities->currentPage() + 2)
                                ) as $page => $url
                            )

                                <a
                                    href="{{ $url }}"
                                    class="pagination-button {{ $page == $activities->currentPage() ? 'active' : '' }}"
                                >
                                    {{ $page }}
                                </a>

                            @endforeach


                            @if ($activities->hasMorePages())

                                <a
                                    href="{{ $activities->nextPageUrl() }}"
                                    class="pagination-button"
                                >
                                    <i data-lucide="chevron-right"></i>
                                </a>

                            @else

                                <span class="pagination-button disabled">
                                    <i data-lucide="chevron-right"></i>
                                </span>

                            @endif

                        </div>

                    </div>

                @endif


            @else

                {{-- Empty State --}}
                <div class="empty-state">

                    <div class="empty-icon">
                        <i data-lucide="history"></i>
                    </div>

                    <h3>
                        Tidak Ada Aktivitas
                    </h3>

                    <p>
                        Belum ditemukan aktivitas akun yang sesuai dengan filter.
                    </p>

                    @if (request()->hasAny([
                        'search',
                        'module',
                        'event',
                        'date_from',
                        'date_to'
                    ]))

                        <a
                            href="{{ route('government.account.activity') }}"
                            class="empty-reset"
                        >
                            Tampilkan Semua Aktivitas
                        </a>

                    @endif

                </div>

            @endif

        </section>

    </div>

@endsection


@push('scripts')
    <script src="{{ asset('js/government/account/activity.js') }}"></script>
@endpush