@extends('layouts.government')

@section('title', 'Rekomendasi Tindakan | SIGMA')

@section('content')

    @php
        /*
        |--------------------------------------------------------------------------
        | PRIORITAS AKTIF
        |--------------------------------------------------------------------------
        |
        | Sumber wilayah sekarang:
        |
        | PriorityResult
        |      ↓
        | region_id
        |      ↓
        | Region
        |      ↓
        | name
        |
        | Tidak lagi mengambil nama wilayah dari Incident.
        |
        */

        $priority = $activePriority ?? null;

        $region = $priority?->region;

        /*
        |--------------------------------------------------------------------------
        | Kompatibilitas form Kirim Petugas
        |--------------------------------------------------------------------------
        |
        | PriorityResult tidak memiliki relasi incident.
        | Variabel tetap disediakan supaya bagian form tidak error.
        |
        */

        $incident = null;

        /*
        |--------------------------------------------------------------------------
        | IDENTITAS WILAYAH
        |--------------------------------------------------------------------------
        */

        $location = $region?->name
            ?? 'Lokasi belum tersedia';

        $priorityLevel = $priority?->priority_level;

        $priorityLabel = match ($priorityLevel) {
            'critical' => 'Kritis',
            'high' => 'Tinggi',
            'medium' => 'Sedang',
            'low' => 'Rendah',
            default => 'Belum tersedia',
        };

        /*
        |--------------------------------------------------------------------------
        | SKOR PRIORITAS
        |--------------------------------------------------------------------------
        */

        $riskScore = $priority?->risk_score;

        $impactScore = $priority?->impact_score;

        $priorityScore = $priority?->priority_score;

        $rankingPosition = $priority?->ranking_position;

        /*
        |--------------------------------------------------------------------------
        | DATA AI
        |--------------------------------------------------------------------------
        */

        $aiOutput = is_array($aiOutput ?? null)
            ? $aiOutput
            : [];

        $aiActions = is_array($aiOutput['actions'] ?? null)
            ? $aiOutput['actions']
            : [];

        $aiRecommendation =
            $aiOutput['recommendation'] ?? null;

        $aiPriorityAction =
            $aiOutput['priority_action'] ?? null;

        $aiReasoning =
            $aiOutput['reasoning'] ?? null;

        $aiAvailable =
            (bool) ($aiOutput['available'] ?? false);

        $queue = $queue ?? collect();

        $aiStatusLabel = $aiAvailable
            ? 'AI Engine Aktif'
            : 'AI Engine';
    @endphp


    {{-- ===================================================================== --}}
    {{-- HEADER --}}
    {{-- ===================================================================== --}}

    <section class="page-heading">

        <div>

            <p class="breadcrumb">
                Beranda › Rekomendasi
            </p>

            <h1>
                Rekomendasi Tindakan
            </h1>

            <p>
                Rekomendasi tindakan operasional berdasarkan analisis AI.
            </p>

        </div>

        <span class="system-status">
            ● {{ $aiStatusLabel }}
        </span>

    </section>


    {{-- ===================================================================== --}}
    {{-- MAIN RECOMMENDATION --}}
    {{-- ===================================================================== --}}

    <div class="recommendation-layout">


        {{-- ================================================================ --}}
        {{-- SUMMARY --}}
        {{-- ================================================================ --}}

        <section class="panel recommendation-summary">

            <div>

                <span class="fire-icon">
                    ♨
                </span>

                <h3>
                    {{ $location }}
                </h3>

                <span class="priority-badge priority-{{ strtolower($priorityLabel) }}">
                    {{ $priorityLabel }}
                </span>

            </div>


            <hr>


            <div class="score-pair">

                <span>

                    Risk

                    <b>
                        {{ $riskScore !== null ? $riskScore : 'Belum tersedia' }}
                    </b>

                </span>


                <span>

                    Impact

                    <b>
                        {{ $impactScore !== null ? $impactScore : 'Belum tersedia' }}
                    </b>

                </span>

            </div>

        </section>


        {{-- ================================================================ --}}
        {{-- REKOMENDASI TINDAKAN --}}
        {{-- ================================================================ --}}

        <section class="panel">

            <h3>
                Rekomendasi Tindakan
            </h3>


            <div class="recommendation-actions">

                @if (! $activePriority)

                    <p>
                        Belum ada wilayah dengan prioritas CRITICAL atau HIGH.
                    </p>


                @elseif (! $aiAvailable)

                    <p>
                        Rekomendasi AI belum tersedia.
                    </p>


                @else

                    @forelse ($aiActions as $item)

                        @php

                            /*
                            |--------------------------------------------------------------------------
                            | Normalisasi output AI
                            |--------------------------------------------------------------------------
                            */

                            $actionType =
                                $item['type']
                                ?? 'field_response';

                            $actionTitle =
                                $item['title']
                                ?? 'Rekomendasi Tindakan';

                            $actionDetail =
                                $item['detail']
                                ?? 'Tindakan berdasarkan analisis AI.';

                            $actionPriority =
                                $item['priority']
                                ?? $priorityLevel
                                ?? 'medium';

                            /*
                            |--------------------------------------------------------------------------
                            | Icon
                            |--------------------------------------------------------------------------
                            */

                            $actionIcon = match ($actionType) {

                                'field_response' => '♙',

                                'monitoring' => '⌁',

                                'public_safety' => '♬',

                                'coordination' => '◉',

                                'resource' => '♧',

                                default => '•',

                            };

                        @endphp


                        <button
                            type="button"
                            data-modal="send-officer-modal"
                        >

                            <b>
                                {{ $actionIcon }}
                            </b>


                            <span>

                                {{ $actionTitle }}

                                <small>
                                    {{ $actionDetail }}
                                </small>

                            </span>

                        </button>


                    @empty

                        @if ($aiPriorityAction)

                            <button
                                type="button"
                                data-modal="send-officer-modal"
                            >

                                <b>
                                    ♙
                                </b>

                                <span>

                                    {{ $aiPriorityAction }}

                                    <small>
                                        Prioritas AI
                                    </small>

                                </span>

                            </button>

                        @else

                            <p>
                                Belum ada rekomendasi AI yang tersedia.
                            </p>

                        @endif

                    @endforelse

                @endif

            </div>

        </section>

    </div>


    {{-- ===================================================================== --}}
    {{-- FOOTER --}}
    {{-- ===================================================================== --}}

    <section class="panel recommendation-footer">

        <div>

            <h3>
                Koordinasi Tindakan Cepat
            </h3>


            <div class="quick-info">

                <p>

                    <strong>
                        Lokasi:
                    </strong>

                    {{ $location }}

                </p>


                <p>

                    <strong>
                        Status Risiko:
                    </strong>

                    {{ $priorityLabel }}

                </p>


                <p>

                    <strong>
                        Skor Risiko:
                    </strong>

                    {{ $riskScore !== null
                        ? $riskScore
                        : 'Belum tersedia'
                    }}

                </p>


                <p>

                    <strong>
                        Skor Dampak:
                    </strong>

                    {{ $impactScore !== null
                        ? $impactScore
                        : 'Belum tersedia'
                    }}

                </p>


                <p>

                    <strong>
                        Skor Prioritas:
                    </strong>

                    {{ $priorityScore !== null
                        ? $priorityScore
                        : 'Belum tersedia'
                    }}

                </p>


                <p>

                    <strong>
                        Peringkat:
                    </strong>

                    {{ $rankingPosition !== null
                        ? '#' . $rankingPosition
                        : 'Belum tersedia'
                    }}

                </p>


                <p>

                    <strong>
                        Analisis AI:
                    </strong>


                    @if ($aiRecommendation)

                        {{ $aiRecommendation }}

                    @elseif ($aiPriorityAction)

                        {{ $aiPriorityAction }}

                    @else

                        Belum tersedia

                    @endif

                </p>


                <p>

                    <strong>
                        Instruksi:
                    </strong>


                    @if ($aiReasoning)

                        {{ $aiReasoning }}

                    @else

                        Belum tersedia dari analisis AI.

                    @endif

                </p>


            </div>

        </div>


        <button
            class="button button-primary"
            data-modal="send-officer-modal"
        >
            Kirim Petugas →
        </button>

    </section>


    {{-- ===================================================================== --}}
    {{-- QUEUE PRIORITAS --}}
    {{-- ===================================================================== --}}

    @if ($queue->count() > 0)

        <section class="panel">

            <h3>
                Antrian Prioritas Berikutnya
            </h3>


            <div class="quick-info">

                @foreach ($queue as $queuedPriority)

                    @php

                        /*
                        |--------------------------------------------------------------------------
                        | Queue sekarang juga memakai PriorityResult -> Region
                        |--------------------------------------------------------------------------
                        */

                        $queuedRegion =
                            $queuedPriority->region;

                        $queuedLocation =
                            $queuedRegion?->name
                            ?? 'Lokasi belum tersedia';

                        $queuedLevel =
                            $queuedPriority->priority_level;

                        $queuedLabel = match ($queuedLevel) {

                            'critical' => 'Kritis',

                            'high' => 'Tinggi',

                            'medium' => 'Sedang',

                            'low' => 'Rendah',

                            default =>
                                ucfirst(
                                    $queuedLevel
                                    ?? 'Belum tersedia'
                                ),

                        };

                    @endphp


                    <p>

                        <strong>
                            {{ $queuedLabel }}
                        </strong>

                        —

                        {{ $queuedLocation }}

                        —

                        Score:

                        {{
                            $queuedPriority->priority_score !== null
                                ? $queuedPriority->priority_score
                                : 'Belum tersedia'
                        }}

                    </p>

                @endforeach

            </div>

        </section>

    @endif


    {{-- ===================================================================== --}}
    {{-- MODAL DETAIL / KIRIM PETUGAS --}}
    {{-- ===================================================================== --}}

    <x-sigma.modal
        id="send-officer-modal"
        title="Kirim Petugas Penanganan"
    >

        <form
            method="POST"
            action="{{ route('government.recommendation.assign') }}"
        >

            @csrf


            {{-- ============================================================ --}}
            {{-- INCIDENT --}}
            {{-- ============================================================ --}}

            @if ($incident)

                <input
                    type="hidden"
                    name="incident_id"
                    value="{{ $incident->id }}"
                >

            @endif


            {{-- ============================================================ --}}
            {{-- WILAYAH --}}
            {{-- ============================================================ --}}

            <div class="form-group">

                <label>
                    Wilayah Penanganan
                </label>


                <select
                    name="region"
                    class="form-control"
                >

                    <option value="">
                        Pilih Wilayah
                    </option>


                    @foreach ($regions as $regionOption)

                        <option
                            value="{{ $regionOption->name }}"
                            @selected(
                                $regionOption->id === $region?->id
                            )
                        >
                            {{ $regionOption->name }}
                        </option>

                    @endforeach

                </select>

            </div>


            {{-- ============================================================ --}}
            {{-- PETUGAS --}}
            {{-- ============================================================ --}}

            <div class="form-group">

                <label>
                    Pilih Petugas
                </label>


                <select
                    name="team_id"
                    class="form-control"
                >

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


            {{-- ============================================================ --}}
            {{-- JENIS PENANGANAN --}}
            {{-- ============================================================ --}}

            <div class="form-group">

                <label>
                    Jenis Penanganan
                </label>


                <select
                    name="action_type"
                    class="form-control"
                >

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


            {{-- ============================================================ --}}
            {{-- INSTRUKSI --}}
            {{-- ============================================================ --}}

            <div class="form-group">

                <label>
                    Instruksi Tambahan
                </label>


                <textarea
                    name="instruction"
                    class="form-control"
                    rows="3"
                    placeholder="Masukkan arahan untuk petugas"
                ></textarea>

            </div>


            {{-- ============================================================ --}}
            {{-- ACTION --}}
            {{-- ============================================================ --}}

            <div class="modal-actions">

                <button
                    type="button"
                    class="button button-light"
                    data-modal-close
                >
                    Batal
                </button>


                <button
                    type="submit"
                    class="button button-primary"
                >
                    Kirim Petugas
                </button>

            </div>


        </form>

    </x-sigma.modal>

@endsection
