@extends('layouts.government')

@section('title', 'Prioritas Penanganan | SIGMA')

@section('content')

    <section class="page-heading">

        <div>
            <p class="breadcrumb">
                Beranda › Prioritas Penanganan
            </p>

            <h1>
                Prioritas Penanganan
            </h1>

            <p>
                Urutan prioritas penanganan berdasarkan risiko dan dampak.
            </p>
        </div>

        <button class="button button-light" data-toast="Prioritas dihitung ulang">
            Hitung Ulang Prioritas
        </button>

    </section>


    <section class="panel filters">

        <input type="search" data-priority-search placeholder="Cari wilayah...">

        <select data-priority-filter>
            <option value="">Semua Prioritas</option>
            <option value="kritis">Kritis</option>
            <option value="tinggi">Tinggi</option>
            <option value="sedang">Sedang</option>
            <option value="rendah">Rendah</option>
        </select>

        <button type="button" data-priority-reset>
            Reset Filter
        </button>

    </section>


    <section class="panel priority-table">

        <x-sigma.data-table>

            <thead>

                <tr>
                    <th>Ranking</th>
                    <th>Wilayah</th>
                    <th>Risiko</th>
                    <th>Dampak</th>
                    <th>Prioritas</th>
                    <th>Aksi</th>
                </tr>

            </thead>


            <tbody data-priority-rows>

                @forelse($priorities as $index => $priority)
                    @php
                        $incident = $priority->incident;

                        $location = $incident?->location_description ?? 'Lokasi tidak tersedia';

                        $priorityLevel = $priority->priority_level;

                        $priorityLabel = match ($priorityLevel) {
                            'critical' => 'Kritis',
                            'high' => 'Tinggi',
                            'medium' => 'Sedang',
                            'low' => 'Rendah',
                            default => ucfirst($priorityLevel ?? '-'),
                        };

                        $searchText = strtolower($location);

                        $riskClass = $priority->risk_score >= 75 ? 'danger' : '';

                        $impactClass = $priority->impact_score >= 80 ? 'danger' : '';

                        $detail =
                            $location .
                            ' memiliki skor risiko ' .
                            $priority->risk_score .
                            ' dan skor dampak ' .
                            $priority->impact_score .
                            '. Skor prioritas ' .
                            $priority->priority_score .
                            ' dengan tingkat prioritas ' .
                            $priorityLabel .
                            '.';
                    @endphp

                    <tr data-priority="{{ strtolower($priorityLabel) }}" data-region="{{ $searchText }}">

                        <td>
                            <b>
                                {{ $index + 1 }}
                            </b>
                        </td>


                        <td>

                            <b>
                                {{ $location }}
                            </b>

                            <small>
                                Insiden #{{ $incident?->id ?? '-' }}
                            </small>

                        </td>


                        <td class="{{ $riskClass }}">

                            <b>
                                {{ $priority->risk_score }}
                            </b>

                        </td>


                        <td class="{{ $impactClass }}">

                            <b>
                                {{ $priority->impact_score }}
                            </b>

                        </td>


                        <td>

                            <span class="priority-badge priority-{{ strtolower($priorityLabel) }}">
                                {{ $priorityLabel }}
                            </span>

                        </td>


                        <td>

                            <button class="text-action" data-modal="priority-modal" data-detail="{{ $detail }}">
                                Lihat Detail
                            </button>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="6" style="text-align: center;">
                            Belum ada data prioritas penanganan.
                        </td>

                    </tr>
                @endforelse

            </tbody>

        </x-sigma.data-table>

    </section>


    <x-sigma.modal id="priority-modal" title="Detail Prioritas">

        <p data-priority-detail>
            Data prioritas belum tersedia.
        </p>

        <div class="modal-actions">

            <button class="button button-primary" data-modal-close>
                Tutup
            </button>

        </div>

    </x-sigma.modal>

@endsection
