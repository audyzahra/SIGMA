@extends('layouts.government')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/government/recommendation-history.css') }}?v={{ time() }}">
@endpush


@section('content')

    <div class="government-page recommendation-history-page">

        <div class="page-header">
            <div>
                <h1>Riwayat Penanganan</h1>
                <p>
                    Daftar penanganan prioritas yang telah dikirim kepada petugas.
                </p>
            </div>

            <a href="{{ route('government.recommendation') }}" class="history-back-button">
                ← Kembali ke Rekomendasi
            </a>
        </div>


        <div class="history-card">

            <div class="history-card-header">
                <div>
                    <h2>Riwayat Penugasan</h2>
                    <p>
                        Menampilkan riwayat pengiriman petugas berdasarkan assignment.
                    </p>
                </div>
            </div>


            @if ($assignments->count())
                <div class="history-list">

                    @foreach ($assignments as $assignment)
                        <div class="history-item">

                            <div class="history-item-main">

                                <div class="history-item-title">
                                    Penanganan #{{ $assignment->id }}
                                </div>

                                <div class="history-item-meta">

                                    <span>
                                        Wilayah:
                                        <strong>{{ $assignment->region?->name ?? '-' }}</strong>
                                    </span>

                                    <span>
                                        Tim:
                                        <strong>
                                            {{ $assignment->team?->name ?? '-' }}
                                        </strong>
                                    </span>

                                    <span>
                                        Status:
                                        <strong>
                                            {{ strtoupper($assignment->status ?? '-') }}
                                        </strong>
                                    </span>

                                    <span>
                                        Ditugaskan:
                                        <strong>
                                            {{ $assignment->assigned_at?->format('d M Y, H:i') ?? '-' }}
                                        </strong>
                                    </span>

                                </div>

                            </div>


                            <div class="history-item-action">

                                <a href="{{ route('government.recommendation.history.show', $assignment) }}"
                                    class="history-detail-button">
                                    Lihat Detail
                                </a>

                            </div>

                        </div>
                    @endforeach

                </div>


                <div class="history-pagination">
                    {{ $assignments->links() }}
                </div>
            @else
                <div class="history-empty">
                    <div class="history-empty-icon">✓</div>

                    <h3>Belum Ada Riwayat Penanganan</h3>

                    <p>
                        Penanganan yang telah dikirim kepada petugas
                        akan muncul di halaman ini.
                    </p>
                </div>
            @endif

        </div>

    </div>

@endsection
