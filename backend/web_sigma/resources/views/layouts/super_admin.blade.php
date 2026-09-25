<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SIGMA Command Center')</title>
    @vite(['resources/css/app.css', 'resources/css/super-admin.css', 'resources/js/app.js'])
    @stack('styles')

    {{-- CSS khusus Dashboard Super Admin --}}
    <link rel="stylesheet" href="{{ asset('css/super_admin/dashboard.css') }}">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="super-admin-layout">
    @php($navigation = [['super-admin.dashboard', 'super-admin.dashboard', 'Dashboard', '⌂'], ['super-admin.manage-users.index', 'super-admin.manage-users.*', 'Manajemen Pengguna', '♙'], ['super-admin.role-permissions.index', 'super-admin.role-permissions.*', 'Role & Permission', '◇'], ['super-admin.organizations.index', 'super-admin.organizations.*', 'Organisasi', '▣'], ['super-admin.regions.index', 'super-admin.regions.*', 'Manajemen Wilayah', '⌖'], ['super-admin.data-sources.index', 'super-admin.data-sources.*', 'Sumber Data', '◉'], ['super-admin.ai-models.index', 'super-admin.ai-models.*', 'Model AI', '✦'], ['super-admin.configurations.index', 'super-admin.configurations.*', 'Konfigurasi Sistem', '⚙'], ['super-admin.audit-logs.index', 'super-admin.audit-logs.*', 'Audit Trail', '◌'], ['super-admin.aspirations.index', 'super-admin.aspirations.*', 'Aspirasi Masyarakat', '✉']])
    <div class="super-admin-shell">
        <aside class="super-admin-sidebar" id="super-admin-sidebar">
            <a class="super-admin-brand" href="{{ route('super-admin.dashboard') }}"><img
                    src="{{ asset('images/logo.png') }}" alt="Logo SIGMA"><span><strong>SIGMA</strong><small>Karhutla
                        Command</small></span></a>
            <div class="super-admin-institution"><i></i> INSTITUSI PUSAT</div>
            <p class="super-admin-menu-label">MENU OPERASIONAL</p>
            <nav class="super-admin-nav" aria-label="Navigasi Super Admin">
                @foreach ($navigation as [$route, $activePattern, $label, $icon])
                    <a href="{{ route($route) }}"
                        class="super-admin-nav-item {{ request()->routeIs($activePattern) ? 'is-active' : '' }}"><span
                            class="super-admin-nav-icon" aria-hidden="true">{{ $icon }}</span><span
                            class="super-admin-nav-label">{{ $label }}</span></a>
                @endforeach
            </nav>
            <div class="super-admin-profile"><span
                    class="avatar">SA</span><span><b>{{ auth()->user()->name ?? 'Super Admin' }}</b><small>Super Admin
                        Pusat</small></span><button type="button" data-modal="logout-modal">Keluar</button></div>
        </aside>
        <div class="super-admin-main">
            <header class="super-admin-header"><button class="super-admin-menu-toggle" type="button"
                    data-sidebar-toggle aria-label="Buka menu">☰</button>
                <div class="header-brand"><img src="{{ asset('images/logo.png') }}" alt="Logo SIGMA"><span>Command
                        Center Karhutla Nasional</span></div>
                <div class="topbar-meta"><span class="system-status">Sistem
                        Normal</span><span>{{ now()->translatedFormat('d M Y H:i') }} WIB</span></div>
            </header>
            <main class="super-admin-content">

                @if (session('success'))
                    <div class="alert success">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert danger">
                        {{ $errors->first() }}
                    </div>
                @endif

                @yield('content')

            </main>
        </div>
    </div>
    <x-sigma.modal id="logout-modal" title="Keluar dari SIGMA">
        <p>Apakah Anda yakin ingin mengakhiri sesi Super Admin?</p>
        <form method="POST" action="{{ route('logout') }}" class="modal-actions">@csrf <button type="button"
                class="button button-light" data-modal-close>Batal</button><button
                class="button button-primary">Keluar</button></form>
    </x-sigma.modal>
    <script>
        document.querySelectorAll('[data-modal]').forEach(button => button.addEventListener('click', () => document
            .getElementById(button.dataset.modal)?.classList.add('is-open')));
        document.querySelectorAll('[data-modal-close]').forEach(button => button.addEventListener('click', () => button
            .closest('.modal-backdrop').classList.remove('is-open')));
        document.querySelectorAll('[data-sidebar-toggle]').forEach(button => button.addEventListener('click', () => document
            .getElementById('super-admin-sidebar').classList.toggle('is-open')))

        lucide.createIcons();
    </script>
</body>

</html>
