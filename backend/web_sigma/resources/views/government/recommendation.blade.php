@extends('layouts.government')

@section('title', 'Rekomendasi Tindakan | SIGMA')

@section('content')

@php

    /*
    |--------------------------------------------------------------------------
    | Incident utama
    |--------------------------------------------------------------------------
    | Recommendation pertama digunakan untuk menentukan
    | incident yang sedang ditampilkan.
    */

    $recommendation = $recommendations->first();

    $incident = $recommendation?->incident;

    $priority = $incident?->priority;


    /*
    |--------------------------------------------------------------------------
    | Data incident
    |--------------------------------------------------------------------------
    */

    $location = $incident?->location_description
        ?? 'Lokasi belum tersedia';


    /*
    |--------------------------------------------------------------------------
    | Priority
    |--------------------------------------------------------------------------
    */

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
    | Semua rekomendasi untuk incident yang sama
    |--------------------------------------------------------------------------
    */

    $incidentRecommendations = $recommendations
        ->where('incident_id', $incident?->id)
        ->values();


    /*
    |--------------------------------------------------------------------------
    | Status keputusan
    |--------------------------------------------------------------------------
    */

    $decisionStatus = match ($recommendation?->decision_status) {

        'pending' => 'Menunggu Persetujuan',

        'accepted' => 'Disetujui',

        'rejected' => 'Ditolak',

        default => 'Belum tersedia',

    };

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
        ● AI Engine Aktif
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


            <span
                class="priority-badge priority-{{ strtolower($priorityLabel) }}"
            >
                {{ $priorityLabel }}
            </span>

        </div>


        <hr>


        <div class="score-pair">

            <span>

                Risk

                <b>
                    {{ $priority?->risk_score ?? 0 }}
                </b>

            </span>


            <span>

                Impact

                <b>
                    {{ $priority?->impact_score ?? 0 }}
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


            @forelse($incidentRecommendations as $item)

                @php

                    $action = match ($item->recommendation_type) {

                        'deploy_team' => [
                            'icon' => '♙',
                            'label' => 'Kirim Tim Pemadam',
                            'detail' => '2 tim',
                        ],

                        'aerial_patrol' => [
                            'icon' => '⌁',
                            'label' => 'Patroli Udara',
                            'detail' => 'Drone / Helikopter',
                        ],

                        'community_alert' => [
                            'icon' => '♬',
                            'label' => 'Peringatan Masyarakat',
                            'detail' => 'Wilayah sekitar',
                        ],

                        'water_source_check' => [
                            'icon' => '◉',
                            'label' => 'Cek Sumber Air',
                            'detail' => 'Radius 10 km',
                        ],

                        'evacuation' => [
                            'icon' => '♧',
                            'label' => 'Evakuasi',
                            'detail' => 'Wilayah terdampak',
                        ],

                        default => [
                            'icon' => '•',
                            'label' => 'Rekomendasi Tindakan',
                            'detail' => 'AI',
                        ],

                    };

                @endphp


                <button
                    type="button"
                    data-toast="Rekomendasi {{ $action['label'] }} dipilih"
                >

                    <b>
                        {{ $action['icon'] }}
                    </b>


                    <span>

                        {{ $action['label'] }}

                        <small>
                            {{ $action['detail'] }}
                        </small>

                    </span>

                </button>


            @empty

                <p>
                    Belum ada rekomendasi AI yang tersedia.
                </p>

            @endforelse


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


        <p>

            {{ $recommendation?->risk_summary
                ?? 'Rekomendasi dibuat dari indikator risiko, dampak, dan kesiapan sumber daya.'
            }}

        </p>

    </div>


    <button
        class="button button-primary"
        data-modal="recommendation-modal"
    >
        Lihat Detail →
    </button>

</section>



{{-- ===================================================================== --}}
{{-- MODAL DETAIL --}}
{{-- ===================================================================== --}}

<x-sigma.modal
    id="recommendation-modal"
    title="Detail Rekomendasi"
>


    @if($recommendation)

        <p>
            {{ $recommendation->recommendation_text }}
        </p>


        <div style="margin-top: 12px;">

            <small>

                Confidence:

                <strong>
                    {{ $recommendation->confidence_score ?? 0 }}%
                </strong>

            </small>


            <br>


            <small>

                Model:

                <strong>
                    {{ $recommendation->model_version ?? '-' }}
                </strong>

            </small>


            <br>


            <small>

                Status:

                <strong>
                    {{ $decisionStatus }}
                </strong>

            </small>

        </div>

    @else

        <p>
            Belum ada rekomendasi AI yang tersedia.
        </p>

    @endif



    <div class="modal-actions">


        <button
            class="button button-light"
            data-modal-close
        >
            Tutup
        </button>


        @if($recommendation)

            <button
                class="button button-primary"
                data-modal-close
                data-toast="Rencana tindakan diproses"
            >
                Kirim Rencana
            </button>

        @endif


    </div>


</x-sigma.modal>


@endsection