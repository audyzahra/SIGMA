@extends('layouts.government')

@section('content')
    <div class="government-page recommendation-history-detail-page">

        <div class="page-header">
            <div>
                <h1>Detail Penanganan</h1>
                <p>
                    Informasi lengkap mengenai penugasan dan proses penanganan.
                </p>
            </div>

            <a href="{{ route('government.recommendation.history') }}" class="history-back-button">
                ← Kembali ke Riwayat
            </a>
        </div>

        <div class="history-detail-grid">

            {{-- INFORMASI PENUGASAN --}}
            <div class="history-detail-card">

                <div class="history-detail-card-header">
                    <h2>Informasi Penugasan</h2>
                </div>

                <div class="history-detail-content">

                    <div class="history-detail-row">
                        <span>ID Penanganan</span>
                        <strong>#{{ $assignment->id }}</strong>
                    </div>

                    <div class="history-detail-row">
                        <span>Tim Petugas</span>
                        <strong>
                            {{ $assignment->team?->team_name ?? ($assignment->team?->name ?? '-') }}
                        </strong>
                    </div>

                    <div class="history-detail-row">
                        <span>Prioritas</span>
                        <strong>
                            {{ strtoupper($assignment->priority_level ?? '-') }}
                        </strong>
                    </div>

                    <div class="history-detail-row">
                        <span>Status</span>
                        <strong>
                            {{ strtoupper($assignment->status ?? '-') }}
                        </strong>
                    </div>

                    <div class="history-detail-row">
                        <span>Ditugaskan Oleh</span>
                        <strong>
                            {{ $assignment->assigner?->name ?? '-' }}
                        </strong>
                    </div>

                    <div class="history-detail-row">
                        <span>Waktu Penugasan</span>
                        <strong>
                            {{ $assignment->assigned_at?->format('d M Y, H:i') ?? '-' }}
                        </strong>
                    </div>

                    <div class="history-detail-row">
                        <span>Waktu Selesai</span>
                        <strong>
                            {{ $assignment->completed_at?->format('d M Y, H:i') ?? '-' }}
                        </strong>
                    </div>

                </div>
            </div>


            {{-- TIMELINE PENANGANAN --}}
            <div class="history-detail-card">

                <div class="history-detail-card-header">
                    <h2>Timeline Penanganan</h2>
                </div>

                <div class="handling-timeline">

                    @forelse ($assignment->statusHistories->sortBy('created_at') as $history)
                        <div class="handling-history-row">

                            <div class="handling-history-header">
                                <strong>
                                    {{ match ($history->status) {
                                        'assigned' => 'Petugas Ditugaskan',
                                        'accepted' => 'Tugas Diterima Petugas',
                                        'traveling' => 'Petugas Dalam Perjalanan',
                                        'arrived' => 'Petugas Tiba di Lokasi',
                                        'handling' => 'Penanganan Dimulai',
                                        'extinguishing' => 'Proses Pemadaman',
                                        'cooling' => 'Proses Pendinginan',
                                        'completed' => 'Penanganan Selesai',
                                        'cancelled' => 'Penugasan Dibatalkan',
                                        default => ucfirst(str_replace('_', ' ', $history->status)),
                                    } }}
                                </strong>

                                <span>
                                    {{ $history->created_at?->format('d M Y, H:i') ?? '-' }} WIB
                                </span>
                            </div>

                            <p>
                                {{ $history->description ?? 'Tidak ada keterangan.' }}
                            </p>

                            <small>
                                Diproses oleh:
                                {{ $history->updater?->name ?? 'Petugas SIGMA' }}
                            </small>

                        </div>

                    @empty

                        <div class="handling-history-row">
                            <div class="handling-history-header">
                                <strong>Petugas Ditugaskan</strong>

                                <span>
                                    {{ $assignment->assigned_at?->format('d M Y, H:i') ?? '-' }} WIB
                                </span>
                            </div>

                            <p>
                                Belum ada riwayat perubahan status penugasan.
                            </p>

                            <small>
                                Diproses oleh:
                                {{ $assignment->assigner?->name ?? 'Pemerintah SIGMA' }}
                            </small>
                        </div>
                    @endforelse

                </div>

            </div>


        </div>
    </div>
@endsection
