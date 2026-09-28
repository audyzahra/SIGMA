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

        $location = $incident?->location_description ?? 'Lokasi belum tersedia';

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

        $incidentRecommendations = $recommendations->where('incident_id', $incident?->id)->values();

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


                <span class="priority-badge priority-{{ strtolower($priorityLabel) }}">
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


                    <button type="button" data-modal="send-officer-modal">

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


            <div class="quick-info">

                <p>
                    <strong>Lokasi:</strong>
                    {{ $location }}
                </p>


                <p>
                    <strong>Status Risiko:</strong>
                    {{ $priorityLabel }}
                </p>


                <p>
                    <strong>Analisis AI:</strong>
                    {{ $recommendation?->recommendation_text ?? 'Belum tersedia' }}
                </p>


                <p>
                    <strong>Instruksi:</strong>
                    Tim pemadam diarahkan untuk melakukan verifikasi lokasi,
                    pemadaman awal, dan mitigasi penyebaran api.
                </p>


            </div>

        </div>


        <button class="button button-primary" data-modal="send-officer-modal">
            Kirim Petugas →
        </button>


    </section>



    {{-- ===================================================================== --}}
    {{-- MODAL DETAIL --}}
    {{-- ===================================================================== --}}

    <x-sigma.modal id="send-officer-modal" title="Kirim Petugas Penanganan">


        <form method="POST" action="{{ route('government.recommendation.assign') }}">
            @csrf

            <input type="hidden" name="incident_id" value="{{ $incident->id }}">


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
