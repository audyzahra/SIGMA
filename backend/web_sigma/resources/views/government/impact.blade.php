@extends('layouts.government')

@section('title', 'Analisis Dampak | SIGMA')

@section('content')

@php
    $impact = $impactAssessments->first();
@endphp

<section class="page-heading">
    <div>
        <p class="breadcrumb">Beranda › Analisis Dampak</p>

        <h1>Analisis Dampak</h1>

        <p>
            Analisis potensi dampak kejadian terhadap masyarakat dan lingkungan.
        </p>
    </div>

    <button
        class="button button-light"
        data-toast="Layer dampak diperbarui"
    >
        ⌖ Tampilkan Layer
    </button>
</section>


<div class="government-grid risk-grid">

    {{-- MAP --}}
    <section class="panel">

        <div class="map-placeholder impact-map">

            <div class="map-controls">
                ＋
                <hr>
                −
            </div>

            <div class="impact-rings">
                <i></i>
                <i></i>
                <i></i>
                <b>♨</b>
            </div>

            <div class="impact-marker marker-one">
                ♙
            </div>

            <div class="impact-marker marker-two">
                ⌂
            </div>

            <div class="impact-marker marker-three">
                ♧
            </div>

        </div>

    </section>


    {{-- DETAIL DAMPAK --}}
    <aside class="panel impact-detail">

        <h3>
            Dampak Potensial
        </h3>


        {{-- PENDUDUK --}}
        <div class="impact-metric">

            <span>♙</span>

            <div>

                <b>
                    {{ $impact?->affected_population !== null
                        ? number_format($impact->affected_population)
                        : '0'
                    }}
                </b>

                <small>
                    Penduduk
                </small>

            </div>

        </div>


        {{-- PERMUKIMAN --}}
        <div class="impact-metric">

            <span>⌂</span>

            <div>

                <b>
                    {{ $impact?->affected_households !== null
                        ? number_format($impact->affected_households)
                        : '0'
                    }}
                </b>

                <small>
                    Permukiman
                </small>

            </div>

        </div>


        {{-- SEKOLAH --}}
        <div class="impact-metric">

            <span>▰</span>

            <div>

                <b>
                    {{ $impact?->school_count !== null
                        ? number_format($impact->school_count)
                        : '0'
                    }}
                </b>

                <small>
                    Sekolah
                </small>

            </div>

        </div>


        {{-- AREA HUTAN --}}
        <div class="impact-metric">

            <span>♠</span>

            <div>

                <b>
                    {{ $impact?->forest_area !== null
                        ? number_format($impact->forest_area, 2) . ' Ha'
                        : '0 Ha'
                    }}
                </b>

                <small>
                    Area Hutan
                </small>

            </div>

        </div>


        {{-- IMPACT SCORE --}}
        <div class="impact-score">

            <span>
                Impact Score
            </span>

            <b>
                {{ $impact?->impact_score ?? 0 }}

                <small>
                    /100
                </small>
            </b>

            <strong>
                @if(($impact?->impact_score ?? 0) >= 80)
                    Sangat Berdampak
                @elseif(($impact?->impact_score ?? 0) >= 60)
                    Berdampak Tinggi
                @elseif(($impact?->impact_score ?? 0) >= 40)
                    Berdampak Sedang
                @else
                    Berdampak Rendah
                @endif
            </strong>

        </div>

    </aside>

</div>

@endsection