@extends('layouts.government')

@section('title', 'Notifikasi | SIGMA')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/government/account.css') }}">
@endpush


@section('content')

    <div class="dashboard-page">

        {{-- Header --}}
        <section class="page-heading">

            <div>

                <p class="breadcrumb">
                    BERANDA › NOTIFIKASI
                </p>

                <h1>
                    Notifikasi
                </h1>

                <p>
                    Riwayat pemberitahuan aktivitas SIGMA Pemerintah.
                </p>

            </div>

        </section>


        <section class="notification-page-card">

            <div class="notification-header">

                <div>

                    <h2>
                        Semua Notifikasi
                    </h2>

                    <p>
                        Informasi terbaru terkait monitoring karhutla,
                        laporan masyarakat, dan sistem AI.
                    </p>

                </div>


                @if (auth()->user()->unreadNotifications->count())

                    <form
                        method="POST"
                        action="{{ route('government.notifications.readAll') }}"
                    >

                        @csrf

                        <button
                            type="submit"
                            class="notification-read-button"
                        >
                            Tandai Semua Dibaca
                        </button>

                    </form>

                @endif

            </div>


            <div class="notification-list-page">

                @forelse($notifications as $notification)

                    @php

                        $data = is_array($notification->data)
                            ? $notification->data
                            : json_decode($notification->data, true);

                        $url = null;

                        if ($notification->type === 'report') {

                            $reportId = $data['report_id'] ?? null;

                            if ($reportId) {
                                $url = route(
                                    'government.reports.show',
                                    $reportId
                                );
                            }

                        }

                    @endphp


                    @if ($url)

                        <a
                            href="{{ $url }}"
                            class="notification-card {{ $notification->read_at ? '' : 'unread' }}"
                        >

                    @else

                        <div
                            class="notification-card {{ $notification->read_at ? '' : 'unread' }}"
                        >

                    @endif


                        <div class="notification-icon">

                            @if ($notification->type === 'risk')

                                <i data-lucide="flame"></i>

                            @elseif($notification->type === 'report')

                                <i data-lucide="file-warning"></i>

                            @elseif($notification->type === 'ai')

                                <i data-lucide="brain"></i>

                            @else

                                <i data-lucide="bell"></i>

                            @endif

                        </div>


                        <div class="notification-content">

                            <h3>
                                {{ $data['title'] ?? 'Notifikasi SIGMA' }}
                            </h3>


                            <p>
                                {{ $data['message'] ?? '-' }}
                            </p>


                            <small>
                                {{ $notification->created_at->diffForHumans() }}
                            </small>

                        </div>


                        @if (!$notification->read_at)

                            <span class="notification-dot"></span>

                        @endif


                    @if ($url)

                        </a>

                    @else

                        </div>

                    @endif

                @empty

                    <div class="notification-empty">

                        <i data-lucide="bell-off"></i>

                        <h3>
                            Belum Ada Notifikasi
                        </h3>

                        <p>
                            Notifikasi aktivitas SIGMA akan muncul di sini.
                        </p>

                    </div>

                @endforelse

            </div>

        </section>

    </div>

@endsection
