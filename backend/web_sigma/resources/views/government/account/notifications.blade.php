@extends('layouts.government')

@section('title', 'Notifikasi | SIGMA')

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('css/government/account/notification.css') }}"
    >
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

        {{-- Back --}}
            <a href="{{ route('government.account.index') }}" class="settings-back">
                <i data-lucide="arrow-left"></i>
                Kembali ke Pengaturan
            </a>

        <section class="notification-page-card">

            {{-- Notification Header --}}
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


                @if ($notifications->whereNull('read_at')->count())

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


            {{-- Notification List --}}
            <div class="notification-list-page">

                @forelse ($notifications as $notification)

                    @php
                        $data = is_array($notification->data)
                            ? $notification->data
                            : json_decode($notification->data, true);

                        $type = $data['type']
                            ?? $notification->type
                            ?? 'default';

                        $title = $data['title']
                            ?? 'Notifikasi SIGMA';

                        $message = $data['message']
                            ?? '-';
                    @endphp


                    <div
                        class="notification-card {{ $notification->read_at ? '' : 'unread' }}"
                        onclick="openNotification(
                            '{{ $notification->id }}',
                            @js($title),
                            @js($message),
                            '{{ $notification->created_at->format('d M Y, H:i') }}',
                            '{{ $type }}',
                            {{ $notification->read_at ? 'true' : 'false' }}
                        )"
                    >

                        {{-- Icon --}}
                        <div class="notification-icon">

                            @if ($type === 'risk')

                                <i data-lucide="flame"></i>

                            @elseif ($type === 'report')

                                <i data-lucide="file-warning"></i>

                            @elseif ($type === 'ai')

                                <i data-lucide="brain"></i>

                            @else

                                <i data-lucide="bell"></i>

                            @endif

                        </div>


                        {{-- Content --}}
                        <div class="notification-content">

                            <h3>
                                {{ $title }}
                            </h3>

                            <p>
                                {{ $message }}
                            </p>

                            <small>
                                {{ $notification->created_at->diffForHumans() }}
                            </small>

                        </div>


                        {{-- Unread Indicator --}}
                        @if (!$notification->read_at)

                            <span class="notification-dot"></span>

                        @endif

                    </div>

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


    {{-- Notification Detail Modal --}}
    <div
        id="notificationDetail"
        class="notification-detail-overlay"
        onclick="closeNotification(event)"
    >

        <div
            class="notification-detail-modal"
            onclick="event.stopPropagation()"
        >

            <button
                type="button"
                class="notification-detail-close"
                onclick="closeNotification()"
            >
                <i data-lucide="x"></i>
            </button>


            <div class="notification-detail-icon">

                <i
                    id="notificationDetailIcon"
                    data-lucide="bell"
                ></i>

            </div>


            <h2 id="notificationDetailTitle">
                Notifikasi SIGMA
            </h2>


            <small
                id="notificationDetailTime"
                class="notification-detail-time"
            >
                -
            </small>


            <div
                id="notificationDetailMessage"
                class="notification-detail-message"
            >
                -
            </div>

        </div>

    </div>


    <script>

        function openNotification(
            notificationId,
            title,
            message,
            time,
            type,
            isRead
        ) {

            /*
             * Tandai sebagai sudah dibaca
             * hanya jika sebelumnya belum dibaca.
             */
            if (!isRead) {

                fetch(
                    "{{ url('/government/notifications') }}/"
                    + notificationId
                    + "/read",
                    {
                        method: 'POST',

                        headers: {
                            'X-CSRF-TOKEN':
                                document
                                    .querySelector('meta[name="csrf-token"]')
                                    .getAttribute('content'),

                            'Accept': 'application/json'
                        }
                    }
                )
                .then(response => {

                    if (!response.ok) {
                        throw new Error(
                            'Gagal menandai notifikasi sebagai dibaca.'
                        );
                    }

                    return response.json();

                })
                .then(() => {

                    const card =
                        document.querySelector(
                            `[onclick*="'${notificationId}'"]`
                        );

                    if (card) {

                        card.classList.remove('unread');

                        const dot =
                            card.querySelector('.notification-dot');

                        if (dot) {
                            dot.remove();
                        }
                    }

                })
                .catch(error => {
                    console.error(error);
                });
            }


            /*
             * Set detail notification.
             */
            document.getElementById(
                'notificationDetailTitle'
            ).textContent = title;


            document.getElementById(
                'notificationDetailMessage'
            ).textContent = message;


            document.getElementById(
                'notificationDetailTime'
            ).textContent = time;


            /*
             * Set icon berdasarkan tipe.
             */
            let iconName = 'bell';


            if (type === 'risk') {
                iconName = 'flame';
            } else if (type === 'report') {
                iconName = 'file-warning';
            } else if (type === 'ai') {
                iconName = 'brain';
            }


            const icon =
                document.getElementById(
                    'notificationDetailIcon'
                );


            icon.setAttribute(
                'data-lucide',
                iconName
            );


            /*
             * Tampilkan modal.
             */
            document
                .getElementById('notificationDetail')
                .classList.add('active');


            document.body.style.overflow = 'hidden';


            /*
             * Render ulang icon Lucide.
             */
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }


        function closeNotification(event) {

            if (
                event &&
                event.target !==
                document.getElementById('notificationDetail')
            ) {
                return;
            }


            document
                .getElementById('notificationDetail')
                .classList.remove('active');


            document.body.style.overflow = '';
        }


        /*
         * Tutup modal dengan tombol Escape.
         */
        document.addEventListener(
            'keydown',
            function (event) {

                if (event.key === 'Escape') {
                    closeNotification();
                }

            }
        );

    </script>

@endsection
