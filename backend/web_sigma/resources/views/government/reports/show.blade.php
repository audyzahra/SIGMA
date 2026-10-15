
@extends('layouts.government')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/government/reports/show.css') }}">
@endpush

@section('title', 'Detail Laporan Masyarakat | SIGMA')

@section('content')
    {{-- HEADER --}}
    <section class="page-heading">
        <div>
            <p class="breadcrumb">Beranda › Laporan Masyarakat › Detail</p>
            <h1>Detail Laporan Masyarakat</h1>
            <p>Informasi lengkap laporan yang dikirimkan oleh masyarakat.</p>
        </div>

        <a href="{{ route('government.reports.index') }}" class="button button-light">
            ← Kembali
        </a>
    </section>

    {{-- INFORMASI LAPORAN --}}
    <section class="panel">
        <div class="report-detail-header">
            <div>
                <small>ID Laporan</small>
                <h2>#{{ $report->id }}</h2>
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
            <div>
                <small>Pelapor</small>
                <strong>{{ $report->user?->name ?? 'Masyarakat' }}</strong>
            </div>

            <div>
                <small>Email</small>
                <strong>{{ $report->user?->email ?? '-' }}</strong>
            </div>

            <div>
                <small>Jenis Laporan</small>
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

            <div>
                <small>Waktu Laporan</small>
                <strong>
                    {{ $report->created_at?->format('d M Y, H:i') ?? '-' }} WIB
                </strong>
            </div>

            <div>
                <small>Latitude</small>
                <strong>{{ $report->latitude ?? '-' }}</strong>
            </div>

            <div>
                <small>Longitude</small>
                <strong>{{ $report->longitude ?? '-' }}</strong>
            </div>
        </div>
    </section>

    {{-- KETERANGAN LAPORAN --}}
    <section class="panel">
        <div class="section-title">
            <h2>Keterangan Laporan</h2>
        </div>

        <p>{{ $report->description ?: 'Tidak ada keterangan.' }}</p>
    </section>

    {{-- MEDIA LAPORAN ASLI: FOTO DAN VIDEO --}}
    @if ($report->photo || $report->video)
        <section class="panel">
            <div class="section-title">
                <h2>Bukti Laporan</h2>
            </div>

            <div class="report-media">
                @if ($report->photo)
                    <div>
                        <small>Foto</small>
                        <a
                            href="{{ asset('storage/' . $report->photo) }}"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            <img
                                src="{{ asset('storage/' . $report->photo) }}"
                                alt="Foto laporan masyarakat"
                                class="report-photo"
                                loading="lazy"
                            >
                        </a>
                    </div>
                @endif

                @if ($report->video)
                    <div>
                        <small>Video</small>
                        <video controls class="report-video">
                            <source src="{{ asset('storage/' . $report->video) }}">
                            Browser tidak mendukung pemutaran video.
                        </video>
                    </div>
                @endif
            </div>
        </section>
    @endif

    {{-- ANALISIS AI YOLO11 --}}
    <section class="panel ai-analysis-panel">
        <div class="section-title ai-section-heading">
            <div>
                <h2>Analisis AI YOLO11</h2>
                <p>Hasil deteksi api dan asap berdasarkan foto laporan masyarakat.</p>
            </div>

            @if ($aiVerification)
                <span class="ai-status-badge">
                    {{ ucfirst($aiVerification->verification_status) }}
                </span>
            @endif
        </div>

        @if ($aiVerification)
            @php
                $aiObjects = is_array($aiVerification->detected_objects)
                    ? $aiVerification->detected_objects
                    : [];

                $detections = is_array($aiObjects['detections'] ?? null)
                    ? $aiObjects['detections']
                    : [];

                $fireDetected = (bool) ($aiObjects['fire_detected'] ?? false);
                $smokeDetected = (bool) ($aiObjects['smoke_detected'] ?? false);
            @endphp

            <div class="ai-summary-grid">
                <div class="ai-summary-card">
                    <small>Prediksi Utama</small>
                    <strong>
                        {{ match ($aiVerification->prediction_result) {
                            'fire' => 'Api Terdeteksi',
                            'smoke' => 'Asap Terdeteksi',
                            'no_fire' => 'Tidak Ada Api',
                            default => 'Belum Dapat Dipastikan',
                        } }}
                    </strong>
                </div>

                <div class="ai-summary-card">
                    <small>Confidence Utama</small>
                    <strong>
                        {{ $aiVerification->confidence_score !== null
                            ? number_format((float) $aiVerification->confidence_score, 2) . '%'
                            : 'Tidak tersedia' }}
                    </strong>
                </div>

                <div class="ai-summary-card">
                    <small>Model AI</small>
                    <strong>
                        {{ $aiObjects['model'] ?? $aiVerification->model?->model_file ?? 'YOLO11' }}
                    </strong>
                </div>
            </div>

            <div class="ai-detection-flags">
                <span class="ai-detection-badge {{ $fireDetected ? 'detected' : 'not-detected' }}">
                    <span class="ai-indicator"></span>
                    Api: {{ $fireDetected ? 'Terdeteksi' : 'Tidak terdeteksi' }}
                </span>

                <span class="ai-detection-badge {{ $smokeDetected ? 'detected' : 'not-detected' }}">
                    <span class="ai-indicator"></span>
                    Asap: {{ $smokeDetected ? 'Terdeteksi' : 'Tidak terdeteksi' }}
                </span>
            </div>

            {{-- FOTO ASLI DAN HASIL ANOTASI --}}
            <div class="ai-image-section">
                <div class="ai-image-card">
                    <div class="ai-image-heading">
                        <strong>Foto Asli</strong>
                        <small>Foto yang dikirim oleh pelapor</small>
                    </div>

                    @if ($report->photo)
                        <a
                            href="{{ asset('storage/' . $report->photo) }}"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            <img
                                src="{{ asset('storage/' . $report->photo) }}"
                                alt="Foto asli laporan masyarakat"
                                class="ai-result-image"
                                loading="lazy"
                            >
                        </a>
                    @else
                        <div class="ai-image-empty">
                            Foto asli tidak tersedia.
                        </div>
                    @endif
                </div>

                <div class="ai-image-card">
                    <div class="ai-image-heading">
                        <strong>Hasil Deteksi YOLO11</strong>
                        <small>Foto dengan bounding box hasil deteksi AI</small>
                    </div>

                    @if ($aiVerification->annotated_image_path)
                        <a
                            href="{{ asset('storage/' . $aiVerification->annotated_image_path) }}"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            <img
                                src="{{ asset('storage/' . $aiVerification->annotated_image_path) }}"
                                alt="Hasil deteksi api dan asap oleh YOLO11"
                                class="ai-result-image"
                                loading="lazy"
                            >
                        </a>
                    @else
                        <div class="ai-image-empty">
                            Gambar anotasi belum tersedia untuk laporan ini.
                        </div>
                    @endif
                </div>
            </div>

            {{-- RINCIAN DETEKSI --}}
            <div class="ai-detections-section">
                <h3>Rincian Objek Terdeteksi</h3>

                @if (count($detections))
                    <div class="ai-table-wrapper">
                        <table class="ai-detection-table">
                            <thead>
                                <tr>
                                    <th>Objek</th>
                                    <th>Confidence</th>
                                    <th>Koordinat Bounding Box</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($detections as $detection)
                                    <tr>
                                        <td>
                                            <span class="ai-object-name">
                                                {{ $detection['class_name'] ?? 'Tidak diketahui' }}
                                            </span>
                                        </td>

                                        <td>
                                            {{ isset($detection['confidence'])
                                                ? number_format((float) $detection['confidence'] * 100, 2) . '%'
                                                : '-' }}
                                        </td>

                                        <td>
                                            <code>
                                                {{ isset($detection['bbox']) && is_array($detection['bbox'])
                                                    ? implode(', ', array_map(
                                                        fn ($value) => number_format((float) $value, 2),
                                                        $detection['bbox']
                                                    ))
                                                    : '-' }}
                                            </code>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="ai-no-detections">
                        Tidak ada rincian objek yang tersimpan pada hasil deteksi ini.
                    </p>
                @endif
            </div>
        @else
            <div class="ai-empty-state">
                <strong>Hasil analisis AI belum tersedia</strong>
                <p>
                    Belum ada data verifikasi YOLO11 yang tersimpan untuk laporan ini.
                </p>
            </div>
        @endif
    </section>

    {{-- RIWAYAT STATUS --}}
    <section class="panel">
        <div class="section-title">
            <h2>Riwayat Laporan</h2>
        </div>

        @forelse ($report->statusHistories->sortBy('created_at') as $history)
            <div class="report-history-item">
                <div>
                    <strong>
                        {{ match (true) {
                            $history->status === 'submitted' => 'Laporan Dikirim',
                            $history->status === 'pending' => 'Menunggu Verifikasi',
                            $history->status === 'verified' => 'Laporan Diterima',
                            $history->status === 'rejected' => 'Laporan Ditolak',
                            $history->status === 'process' && str_contains(strtolower($history->description ?? ''), 'menerima tugas') => 'Tugas Diterima Petugas',
                            $history->status === 'process' && str_contains(strtolower($history->description ?? ''), 'mulai melakukan penanganan') => 'Penanganan Dimulai',
                            $history->status === 'process' => 'Laporan Diproses',
                            $history->status === 'completed' => 'Penanganan Selesai',
                            default => ucfirst($history->status),
                        } }}
                    </strong>

                    <small>
                        {{ $history->created_at?->format('d M Y, H:i') ?? '-' }} WIB
                    </small>
                </div>

                <p>{{ $history->description ?: 'Tidak ada keterangan.' }}</p>

                @if ($history->updater)
                    <small>
                        Diproses oleh: {{ $history->updater->name }}
                    </small>
                @endif
            </div>
        @empty
            <p>Belum ada riwayat perubahan status.</p>
        @endforelse
    </section>

    {{-- AKSI PEMERINTAH --}}
    @if ($report->verification_status === 'pending' || $report->verification_status === 'verified')
        <section class="panel">
            <div class="section-title">
                <h2>Tindakan Pemerintah</h2>
                <p>Kelola proses verifikasi dan penanganan laporan.</p>
            </div>

            <div class="modal-actions">
                @if ($report->verification_status === 'pending')
                    <button
                        type="button"
                        class="button button-light"
                        data-modal="reject-report-modal"
                    >
                        Tolak Laporan
                    </button>

                    <form
                        method="POST"
                        action="{{ route('government.reports.verify', $report) }}"
                    >
                        @csrf
                        @method('PATCH')

                        <button type="submit" class="button button-primary">
                            Terima Laporan
                        </button>
                    </form>
                @elseif ($report->verification_status === 'verified')
                    <button
                        type="button"
                        class="button button-primary"
                        data-modal="send-officer-modal"
                    >
                        Kirim Petugas →
                    </button>
                @endif
            </div>
        </section>
    @endif

    {{-- MODAL TOLAK LAPORAN --}}
    <x-sigma.modal id="reject-report-modal" title="Tolak Laporan">
        <p>Masukkan alasan mengapa laporan ini ditolak.</p>

        <form
            method="POST"
            action="{{ route('government.reports.reject', $report) }}"
        >
            @csrf
            @method('PATCH')

            <div class="form-group">
                <label for="description">Alasan Penolakan</label>
                <textarea
                    id="description"
                    name="description"
                    rows="4"
                    required
                    placeholder="Masukkan alasan penolakan..."
                ></textarea>
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

    {{-- MODAL KIRIM PETUGAS --}}
    <x-sigma.modal id="send-officer-modal" title="Kirim Petugas Penanganan">
        <form method="POST" action="{{ route('government.reports.assign') }}">
            @csrf

            <input type="hidden" name="report_id" value="{{ $report->id }}">

            <div class="form-group">
                <label for="region">Wilayah Penanganan</label>
                <select id="region" name="region" class="form-control" required>
                    <option value="">Pilih Wilayah</option>

                    @foreach ($regions as $region)
                        <option value="{{ $region->name }}">
                            {{ $region->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="team_id">Pilih Petugas</label>
                <select id="team_id" name="team_id" class="form-control" required>
                    <option value="">Pilih Tim Pemadam</option>

                    @foreach ($teams as $team)
                        <option value="{{ $team->id }}">
                            {{ $team->team_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="action_type">Jenis Penanganan</label>
                <select id="action_type" name="action_type" class="form-control" required>
                    <option value="">Pilih Jenis Penanganan</option>

                    @foreach ($actions as $action)
                        <option value="{{ $action->name }}">
                            {{ $action->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="instruction">Instruksi Tambahan</label>
                <textarea
                    id="instruction"
                    name="instruction"
                    class="form-control"
                    rows="3"
                    placeholder="Masukkan arahan untuk petugas"
                ></textarea>
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
        document.addEventListener('DOMContentLoaded', function () {
            const reportId = @json($report->id);
            let lastHistoryId = @json($report->statusHistories->max('id') ?? 0);

            async function checkReportHistory() {
                try {
                    const response = await fetch(
                        `{{ url('/government/reports') }}/${reportId}/history`,
                        {
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

                    const latest = data.histories.reduce(
                        (current, item) => item.id > current.id ? item : current,
                        data.histories[0]
                    );

                    if (latest.id > lastHistoryId) {
                        lastHistoryId = latest.id;
                        window.location.reload();
                    }
                } catch (error) {
                    console.error('Gagal mengecek update laporan:', error);
                }
            }

            setInterval(checkReportHistory, 3000);
        });
    </script>
@endpush
