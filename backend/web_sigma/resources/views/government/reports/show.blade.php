@extends('layouts.government')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/government/reports/show.css') }}">
@endpush


@section('title', 'Detail Laporan Masyarakat | SIGMA')

@section('content')

    <section class="page-heading">

        <div>

            <p class="breadcrumb">
                Beranda › Laporan Masyarakat › Detail
            </p>

            <h1>
                Detail Laporan Masyarakat
            </h1>

            <p>
                Informasi lengkap laporan yang dikirimkan oleh masyarakat.
            </p>

        </div>

        <a href="{{ route('government.reports.index') }}" class="button button-light">
            ← Kembali
        </a>

    </section>


    {{-- ==========================================================
        INFORMASI LAPORAN
    =========================================================== --}}

    <section class="panel">

        <div class="report-detail-header">

            <div>

                <small>
                    ID Laporan
                </small>

                <h2>
                    #{{ $report->id }}
                </h2>

            </div>


            @php

                $statusLabel = match ($report->verification_status) {
                    'pending' => 'Pending',

                    'verified' => 'Diterima',

                    'rejected' => 'Ditolak',

                    default => ucfirst($report->verification_status ?? '-'),
                };

                $statusClass = match ($report->verification_status) {
                    'pending' => 'priority-sedang',

                    'verified' => 'priority-rendah',

                    'rejected' => 'priority-kritis',

                    default => 'priority-sedang',
                };

            @endphp


            <span class="priority-badge {{ $statusClass }}">
                {{ $statusLabel }}
            </span>

        </div>


        <div class="report-detail-grid">

            {{-- PELAPOR --}}

            <div>

                <small>
                    Pelapor
                </small>

                <strong>
                    {{ $report->user?->name ?? 'Masyarakat' }}
                </strong>

            </div>


            {{-- EMAIL --}}

            <div>

                <small>
                    Email
                </small>

                <strong>
                    {{ $report->user?->email ?? '-' }}
                </strong>

            </div>


            {{-- JENIS --}}

            <div>

                <small>
                    Jenis Laporan
                </small>

                <strong>

                    {{ match ($report->report_type) {
                        'fire' => 'Kebakaran',

                        'smoke' => 'Asap',

                        'burning_activity' => 'Aktivitas Pembakaran',

                        'other' => 'Lainnya',

                        default => ucfirst(str_replace('_', ' ', $report->report_type ?? '-')),
                    } }}

                </strong>

            </div>


            {{-- TANGGAL --}}

            <div>

                <small>
                    Waktu Laporan
                </small>

                <strong>
                    {{ $report->created_at?->format('d M Y, H:i') }} WIB
                </strong>

            </div>


            {{-- LATITUDE --}}

            <div>

                <small>
                    Latitude
                </small>

                <strong>
                    {{ $report->latitude }}
                </strong>

            </div>


            {{-- LONGITUDE --}}

            <div>

                <small>
                    Longitude
                </small>

                <strong>
                    {{ $report->longitude }}
                </strong>

            </div>

        </div>

    </section>


    {{-- ==========================================================
        KETERANGAN
    =========================================================== --}}

    <section class="panel">

        <div class="section-title">

            <h2>
                Keterangan Laporan
            </h2>

        </div>


        <p>

            {{ $report->description ?: 'Tidak ada keterangan.' }}

        </p>

    </section>


    {{-- ==========================================================
        MEDIA LAPORAN
    =========================================================== --}}

    @if ($report->photo || $report->video)

        <section class="panel">

            <div class="section-title">

                <h2>
                    Bukti Laporan
                </h2>

            </div>


            <div class="report-media">

                @if ($report->photo)
                    <div>

                        <small>
                            Foto
                        </small>

                        <img src="{{ asset('storage/' . $report->photo) }}" alt="Foto laporan masyarakat"
                            class="report-photo">

                    </div>
                @endif


                @if ($report->video)
                    <div>

                        <small>
                            Video
                        </small>

                        <video controls class="report-video">

                            <source src="{{ asset('storage/' . $report->video) }}">

                            Browser tidak mendukung pemutaran video.

                        </video>

                    </div>
                @endif

            </div>

        </section>

    @endif


    {{-- ==========================================================
        RIWAYAT STATUS
    =========================================================== --}}

    <section class="panel">

        <div class="section-title">

            <h2>
                Riwayat Laporan
            </h2>

        </div>


        @forelse($report->statusHistories as $history)
            <div class="report-history-item">

                <div>

                    <strong>

                        {{ match ($history->status) {
                            'submitted' => 'Laporan Dikirim',

                            'pending' => 'Menunggu Verifikasi',

                            'verified' => 'Laporan Diterima',

                            'rejected' => 'Laporan Ditolak',

                            'process' => 'Laporan Diproses',
                        
                            'completed' => 'Penanganan Selesai',

                            default => ucfirst($history->status),
                        } }}
                    </strong>


                    <small>

                        {{ $history->created_at?->format('d M Y, H:i') }}
                        WIB

                    </small>

                </div>


                <p>

                    {{ $history->description ?: 'Tidak ada keterangan.' }}

                </p>


                @if ($history->updater)
                    <small>

                        Diproses oleh:
                        {{ $history->updater->name }}

                    </small>
                @endif

            </div>

        @empty

            <p>
                Belum ada riwayat perubahan status.
            </p>
        @endforelse

    </section>


    {{-- ==========================================================
        AKSI PEMERINTAH
    =========================================================== --}}

    @if ($report->verification_status === 'pending')
        <section class="panel">

            <div class="section-title">

                <h2>
                    Tindakan Pemerintah
                </h2>

                <p>
                    Periksa informasi laporan sebelum menentukan statusnya.
                </p>

            </div>


            <div class="modal-actions">

                <button type="button" class="button button-light" data-modal="reject-report-modal">
                    Tolak Laporan
                </button>


                <form method="POST" action="{{ route('government.reports.verify', $report) }}">

                    @csrf
                    @method('PATCH')

                    <button type="submit" class="button button-primary">
                        Terima Laporan
                    </button>

                </form>

            </div>

        </section>
    @endif


    {{-- ==========================================================
        MODAL TOLAK LAPORAN
    =========================================================== --}}

    <x-sigma.modal id="reject-report-modal" title="Tolak Laporan">

        <p>
            Masukkan alasan mengapa laporan ini ditolak.
        </p>


        <form method="POST" action="{{ route('government.reports.reject', $report) }}">

            @csrf
            @method('PATCH')


            <div class="form-group">

                <label for="description">
                    Alasan Penolakan
                </label>

                <textarea id="description" name="description" rows="4" required placeholder="Masukkan alasan penolakan..."></textarea>

            </div>


            <div class="modal-actions">

                <button type="button" class="button button-light" data-modal-close>
                    Batal
                </button>


                <button type="submit" class="button button-primary">
                    Tolak Laporan
                </button>

            </div>

        </form>

    </x-sigma.modal>

@endsection
