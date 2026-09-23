@extends('layouts.government')

@section('title', 'Risiko Karhutla | SIGMA')

@section('content')

@php
    $riskByRegion = $fireRisks->keyBy('region_id');

    $selectedRegion = $regions->first();

    $selectedRisk = $selectedRegion
        ? $riskByRegion->get($selectedRegion->id)
        : null;

    $sigmaRegions = $regions->map(function ($region) use ($riskByRegion) {
        $risk = $riskByRegion->get($region->id);

        return [
            'id' => $region->id,
            'name' => $region->name,
            'risk_score' => $risk?->risk_score,
            'risk_level' => $risk?->risk_level,
            'temperature' => $risk?->temperature,
            'humidity' => $risk?->humidity,
            'wind_speed' => $risk?->wind_speed,
            'rainfall' => $risk?->rainfall,
        ];
    })->values();
@endphp

<section class="page-heading">
    <div>
        <p class="breadcrumb">Beranda › Risiko Karhutla</p>

        <h1>Analisis Risiko Karhutla</h1>

        <p>
            Analisis tingkat risiko kebakaran di setiap wilayah.
        </p>
    </div>

    <button
        class="button button-light"
        data-toast="Analisis risiko diperbarui"
    >
        Perbarui Analisis
    </button>
</section>


<div class="government-grid risk-grid">

    {{-- PETA RISIKO --}}
    <section class="panel">

        <div class="map-placeholder monitoring-map">

            <div class="map-controls">
                +
                <hr>
                −
            </div>

            <div class="island island-one"></div>

            <div class="island island-two">
                <i></i>
                <i></i>
            </div>

            <div class="island island-three"></div>

            <div class="island island-four"></div>

            <div class="map-legend">
                <span class="success">● Rendah</span>
                <span class="warning">● Sedang</span>
                <span class="orange-text">● Tinggi</span>
                <span class="danger">● Ekstrem</span>
            </div>

        </div>

    </section>


    {{-- DETAIL WILAYAH --}}
    <aside class="panel detail-panel">

        <h3>Detail Wilayah</h3>

        <label>
            Pilih Wilayah

            <select data-region-select>

                @foreach($regions as $region)

                    <option
                        value="{{ $region->id }}"
                        {{ $selectedRegion?->id === $region->id ? 'selected' : '' }}
                    >
                        {{ $region->name }}
                    </option>

                @endforeach

            </select>
        </label>


        {{-- SCORE --}}
        <div class="score-ring">

            <b data-risk-score>
                {{ $selectedRisk?->risk_score ?? 0 }}
            </b>

            <span>/100</span>

        </div>


        {{-- LEVEL --}}
        <p class="score-label">
            Risiko

            <b
                class="
                    {{
                        match ($selectedRisk?->risk_level) {
                            'low' => 'success',
                            'medium' => 'warning',
                            'high' => 'orange-text',
                            'extreme' => 'danger',
                            default => 'danger',
                        }
                    }}
                "
                data-risk-level
            >
                {{
                    $selectedRisk?->risk_level
                        ? ucfirst($selectedRisk->risk_level)
                        : 'Belum tersedia'
                }}
            </b>
        </p>


        {{-- SUHU --}}
        <div class="parameter">

            <span>Suhu</span>

            <b data-risk-temperature>
                {{
                    $selectedRisk?->temperature !== null
                        ? $selectedRisk->temperature . '°C'
                        : '-'
                }}
            </b>

        </div>


        {{-- KELEMBAPAN --}}
        <div class="parameter">

            <span>Kelembapan</span>

            <b data-risk-humidity>
                {{
                    $selectedRisk?->humidity !== null
                        ? $selectedRisk->humidity . '%'
                        : '-'
                }}
            </b>

        </div>


        {{-- KECEPATAN ANGIN --}}
        <div class="parameter">

            <span>Kecepatan Angin</span>

            <b data-risk-wind>
                {{
                    $selectedRisk?->wind_speed !== null
                        ? $selectedRisk->wind_speed . ' km/jam'
                        : '-'
                }}
            </b>

        </div>


        {{-- CURAH HUJAN --}}
        <div class="parameter">

            <span>Curah Hujan</span>

            <b data-risk-rainfall>
                {{
                    $selectedRisk?->rainfall !== null
                        ? $selectedRisk->rainfall . ' mm'
                        : '-'
                }}
            </b>

        </div>

    </aside>

</div>


<script>
    window.sigmaRegions = @json($sigmaRegions);
</script>

@endsection

<script src="{{ asset('js/government/fire-risk.js') }}?v={{ time() }}"></script>