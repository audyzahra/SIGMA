<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'SIGMA Pemerintah')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest"></script>

    {{-- Leaflet GIS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">

    @stack('styles')
</head>

<body>

    @php
        $navigation = [
            ['government.dashboard', 'Dashboard', '▦'],
            ['government.fire-risk', 'Risiko Karhutla', '♨'],
            ['government.impact', 'Analisis Dampak', '◉'],
            ['government.priority', 'Prioritas Penanganan', '⌁'],
            ['government.recommendation', 'Rekomendasi', '▣'],
            ['government.field-teams.index', 'Tim Pemadam', '♟'],
            ['government.reports.index', 'Laporan Masyarakat', '▤'],
        ];
    @endphp

    <div class="app-shell">

        <aside class="sidebar" id="sidebar">

            <a class="brand" href="{{ route('government.dashboard') }}">
                <img src="{{ asset('images/logo.png') }}" alt="Logo SIGMA">

                <span>
                    <strong>SIGMA</strong>
                    <small>Karhutla Command</small>
                </span>
            </a>

            <div class="institution">
                <i></i>
                PEMERINTAH
            </div>

            <p class="menu-label">
                MENU MONITORING
            </p>

            <nav>
                @foreach ($navigation as [$route, $label, $icon])
                    <a href="{{ route($route) }}"
                        class="nav-link {{ request()->routeIs($route) ? 'active' : '' }}">
                        <span>{{ $icon }}</span>
                        {{ $label }}
                    </a>
                @endforeach
            </nav>

            <div class="profile-card">

                <span class="avatar">
                    PS
                </span>

                <span>
                    <b>Pemerintah SIGMA</b>
                    <small>Pemerintah</small>
                </span>

                <div>
                    <a href="#" data-toast="Profil Pemerintah SIGMA">
                        ⚙ Pengaturan
                    </a>

                    <button type="button" data-modal="logout-modal">
                        ⇥ Keluar
                    </button>
                </div>

            </div>

        </aside>

        <div class="main-wrap">

            <header class="topbar">

                <button class="mobile-menu" type="button" data-sidebar-toggle>
                    ☰
                </button>

                <div class="header-brand">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo SIGMA">

                    <span>
                        Command Center Karhutla Nasional
                    </span>
                </div>

                <div class="topbar-meta">

                    <span class="system-status">
                        ● Sistem Normal
                    </span>

                    <span id="realtime-clock">
                        ◷ Memuat waktu...
                    </span>

                    <button type="button"
                        class="icon-button"
                        data-toast="Tidak ada notifikasi baru">
                        <i data-lucide="bell"></i>
                    </button>

                </div>

            </header>

            <main class="page-content">
                @yield('content')
            </main>

        </div>

    </div>

    <x-sigma.modal id="logout-modal" title="Keluar dari SIGMA">

        <p>
            Apakah Anda yakin ingin mengakhiri sesi Pemerintah?
        </p>

        <form method="POST"
            action="{{ route('logout') }}"
            class="modal-actions">

            @csrf

            <button type="button"
                class="button button-light"
                data-modal-close>
                Batal
            </button>

            <button class="button button-primary">
                Keluar
            </button>

        </form>

    </x-sigma.modal>

    <div id="toast" class="toast" role="status"></div>

    {{-- Success Toast dari Session --}}
    @if (session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const toast = document.getElementById('toast');

                if (toast) {
                    toast.textContent = @json(session('success'));
                    toast.classList.add('show');

                    setTimeout(() => {
                        toast.classList.remove('show');
                    }, 2600);
                }
            });
        </script>
    @endif

    {{-- Leaflet GIS JS --}}
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    <script>
        // Modal
        document.querySelectorAll('[data-modal]').forEach(button => {
            button.addEventListener('click', () => {
                document
                    .getElementById(button.dataset.modal)
                    ?.classList.add('is-open');
            });
        });

        // Close Modal
        document.querySelectorAll('[data-modal-close]').forEach(button => {
            button.addEventListener('click', () => {
                button
                    .closest('.modal-backdrop')
                    ?.classList.remove('is-open');
            });
        });

        // Toast
        document.querySelectorAll('[data-toast]').forEach(button => {
            button.addEventListener('click', () => {
                const toast = document.getElementById('toast');

                if (!toast) {
                    return;
                }

                toast.textContent = button.dataset.toast;
                toast.classList.add('show');

                setTimeout(() => {
                    toast.classList.remove('show');
                }, 2600);
            });
        });

        // Mobile Sidebar
        document.querySelectorAll('[data-sidebar-toggle]').forEach(button => {
            button.addEventListener('click', () => {
                document
                    .getElementById('sidebar')
                    ?.classList.toggle('open');
            });
        });
    </script>

    <script>
        lucide.createIcons();
    </script>

    <script>
    function updateClock() {
        const now = new Date();

        const days = [
            'Minggu',
            'Senin',
            'Selasa',
            'Rabu',
            'Kamis',
            'Jumat',
            'Sabtu'
        ];

        const months = [
            'Jan',
            'Feb',
            'Mar',
            'Apr',
            'Mei',
            'Jun',
            'Jul',
            'Agu',
            'Sep',
            'Okt',
            'Nov',
            'Des'
        ];

        // WIB (UTC+7)
        const wib = new Date(
            now.toLocaleString('en-US', {
                timeZone: 'Asia/Jakarta'
            })
        );

        const day = days[wib.getDay()];
        const date = wib.getDate();
        const month = months[wib.getMonth()];
        const year = wib.getFullYear();

        const hours = String(wib.getHours()).padStart(2, '0');
        const minutes = String(wib.getMinutes()).padStart(2, '0');
        const seconds = String(wib.getSeconds()).padStart(2, '0');

        document.getElementById('realtime-clock').innerHTML =
            `◷ ${day}, ${date} ${month} ${year} • ${hours}:${minutes}:${seconds} WIB`;
    }

    updateClock();

    setInterval(updateClock, 1000);
</script>

    @stack('scripts')

</body>

</html>
