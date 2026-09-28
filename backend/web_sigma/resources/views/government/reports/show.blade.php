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


        @forelse($report->statusHistories->sortBy('created_at') as $history)
            <div class="report-history-item">

                <div>

                    <strong>

                        {{ match (true) {
                            $history->status === 'submitted' => 'Laporan Dikirim',
                        
                            $history->status === 'pending' => 'Menunggu Verifikasi',
                        
                            $history->status === 'verified' => 'Laporan Diterima',
                        
                            $history->status === 'rejected' => 'Laporan Ditolak',
                        
                            $history->status === 'process' && str_contains(strtolower($history->description ?? ''), 'menerima tugas')
                                => 'Tugas Diterima Petugas',
                        
                            $history->status === 'process' &&
                                str_contains(strtolower($history->description ?? ''), 'mulai melakukan penanganan')
                                => 'Penanganan Dimulai',
                        
                            $history->status === 'process' => 'Laporan Diproses',
                        
                            $history->status === 'completed' => 'Penanganan Selesai',
                        
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

    @if ($report->verification_status === 'pending' || $report->verification_status === 'verified')
        <section class="panel">

            <div class="section-title">

                <h2>
                    Tindakan Pemerintah
                </h2>

                <p>
                    Kelola proses verifikasi dan penanganan laporan.
                </p>

            </div>


            <div class="modal-actions">


                {{-- BELUM DIVERIFIKASI --}}
                @if ($report->verification_status === 'pending')
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



                    {{-- SUDAH DITERIMA --}}
                @elseif($report->verification_status === 'verified')
                    <button type="button" class="button button-primary" data-modal="send-officer-modal">

                        Kirim Petugas →

                    </button>
                @endif


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

    {{-- ==========================================================
        MODAL KIRIM PETUGAS
    =========================================================== --}}

    <x-sigma.modal id="send-officer-modal" title="Kirim Petugas Penanganan">


        <form method="POST" action="{{ route('government.reports.assign') }}">
            @csrf

            <input type="hidden" name="report_id" value="{{ $report->id }}">


            <div class="form-group">

                <label>
                    Wilayah Penanganan
                </label>


                <select name="region" class="form-control">


                    <option value="">
                        Pilih Wilayah
                    </option>


                    @foreach ($regions as $region)
                        <option value="{{ $region->name }}">

                            {{ $region->name }}

                        </option>
                    @endforeach


                </select>

            </div>



            <div class="form-group">

                <label>
                    Pilih Petugas
                </label>


                <select name="team_id" class="form-control">


                    <option value="">
                        Pilih Tim Pemadam
                    </option>


                    @foreach ($teams as $team)
                        <option value="{{ $team->id }}">

                            {{ $team->team_name }}

                        </option>
                    @endforeach


                </select>

            </div>



            <div class="form-group">

                <label>
                    Jenis Penanganan
                </label>


                <select name="action_type" class="form-control">


                    <option value="">
                        Pilih Jenis Penanganan
                    </option>


                    @foreach ($actions as $action)
                        <option value="{{ $action->name }}">

                            {{ $action->name }}

                        </option>
                    @endforeach


                </select>

            </div>



            <div class="form-group">

                <label>
                    Instruksi Tambahan
                </label>


                <textarea name="instruction" class="form-control" rows="3" placeholder="Masukkan arahan untuk petugas">
</textarea>


            </div>




            <div class="modal-actions">


                <button type="button" class="button button-light" data-modal-close>
                    Batal
                </button>



                <button type="submit" class="button button-primary">

                    Kirim Petugas

                </button>



            </div>


        </form>


    </x-sigma.modal>


@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const reportId = @json($report->id);

            let lastHistoryId = @json($report->statusHistories->max('id'));

            async function checkReportHistory() {

                try {

                    const response = await fetch(
                        `{{ url('/government/reports') }}/${reportId}/history`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        }
                    );

                    if (!response.ok) {
                        return;
                    }

                    const data = await response.json();

                    if (!data.histories || !data.histories.length) {
                        return;
                    }

                    const latest = data.histories[0];

                    if (latest.id > lastHistoryId) {

                        lastHistoryId = latest.id;

                        window.location.reload();
                    }

                } catch (error) {

                    console.error(
                        'Gagal mengecek update laporan:',
                        error
                    );

                }

            }

            setInterval(checkReportHistory, 3000);

        });
    </script>
@endpush
