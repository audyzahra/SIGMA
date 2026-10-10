@extends('layouts.app')

@section('title')
    {{ $profile->title ?? 'SIGMA' }} -
    {{ $profile->tagline ?? 'Sistem Intelijen Geospasial untuk Mitigasi Karhutla' }}
@endsection

@push('styles')

    <link
        rel="stylesheet"
        href="{{ asset('css/public_sigma.css') }}"
    >

    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    >

@endpush

@section('content')
    <div class="public_sigma">

        {{-- ================= HERO ================= --}}
        <section class="hero">
            <div class="container">

                <div class="hero-content">

                    <span class="badge">
                        SISTEM INFORMASI KARHUTLA
                    </span>

                    <h1>
                        {{ $profile->title ?? 'SIGMA' }}
                        <span>Mitigasi Karhutla</span>
                    </h1>

                    <p>
                        {{ $profile->tagline ?? 'Sistem Intelijen Geospasial untuk Mitigasi Karhutla' }}
                    </p>

                    <div class="hero-actions">

                        <a href="#monitoring" class="btn btn-primary">
                            Lihat Monitoring
                        </a>

                        <a href="#aspirasi" class="btn btn-outline">
                            Sampaikan Aspirasi
                        </a>

                    </div>

                </div>

            </div>
        </section>


                    {{-- ================= MONITORING ================= --}}

<section id="monitoring" class="monitoring">

<div class="container">

    <div class="section-heading">

        <span>
            MONITORING
        </span>

        <h2>
            Pantau Kondisi Karhutla
        </h2>

        <p>
            Pantau kondisi risiko kebakaran hutan dan lahan
            melalui informasi geospasial SIGMA.
        </p>

    </div>


    <div class="monitoring-card">

        {{-- ================= PETA ================= --}}
        <div class="map">

            {{-- Peta Leaflet --}}
            <div
                id="sigma-public-map"
                style="width: 100%; height: 520px;"
            ></div>


            {{-- Informasi Peta --}}
            <div class="map-info">

                <strong>
                    Monitoring Karhutla
                </strong>

                <span>
                    Data Geospasial SIGMA
                </span>

            </div>

        </div>


        {{-- ================= LEGEND ================= --}}
        <div class="map-legend">

            <div>
                <i class="legend-hotspot"></i>
                Hotspot
            </div>

            <div>
                <i class="legend-risk"></i>
                Zona Risiko
            </div>

            <div>
                <i class="legend-incident"></i>
                Kejadian
            </div>

            <div>
                <i class="legend-region"></i>
                Batas Wilayah
            </div>

        </div>

    </div>

</div>

</section>


        {{-- ================= INFORMASI ================= --}}
        <section class="information">

            <div class="container">

                <div class="section-heading">

                    <span>
                        INFORMASI
                    </span>

                    <h2>
                        Kenali dan Cegah Karhutla
                    </h2>

                    <p>
                        Informasi SIGMA membantu masyarakat memahami risiko
                        kebakaran hutan dan lahan.
                    </p>

                </div>


                <div class="info-grid">

                    <div class="info-card">

                        <div class="info-number">
                            01
                        </div>

                        <h3>
                            Kenali Risiko
                        </h3>

                        <p>
                            Kenali wilayah dengan potensi risiko kebakaran
                            hutan dan lahan.
                        </p>

                    </div>


                    <div class="info-card">

                        <div class="info-number">
                            02
                        </div>

                        <h3>
                            Pantau Hotspot
                        </h3>

                        <p>
                            Pantau informasi titik panas yang terdeteksi
                            pada wilayah Indonesia.
                        </p>

                    </div>


                    <div class="info-card">

                        <div class="info-number">
                            03
                        </div>

                        <h3>
                            Mitigasi Bersama
                        </h3>

                        <p>
                            Masyarakat dapat berpartisipasi dalam upaya
                            pencegahan dan penanganan karhutla.
                        </p>

                    </div>

                </div>

            </div>

        </section>


        {{-- ================= ASPIRASI ================= --}}
        <section id="aspirasi" class="aspirasi">

            <div class="container">

                <div class="section-heading">

                    <span>
                        PARTISIPASI MASYARAKAT
                    </span>

                    <h2>
                        Sampaikan Informasi Karhutla
                    </h2>

                    <p>
                        Temukan atau lihat potensi kejadian kebakaran hutan
                        dan lahan? Sampaikan informasi kepada kami melalui
                        formulir berikut.
                    </p>

                </div>


                <div class="aspirasi-form-wrapper">

                    <form action="{{ route('public_sigma.aspirations.store') }}" method="POST" class="aspirasi-form">
                        @csrf

                        <div class="form-grid">

                            {{-- NAMA --}}
                            <div class="form-group">

                                <label for="name">
                                    Nama
                                    <span>*</span>
                                </label>

                                <input type="text" id="name" name="name" placeholder="Masukkan nama lengkap"
                                    value="{{ old('name') }}" required>

                            </div>


                            {{-- NO TELEPON --}}
                            <div class="form-group">

                                <label for="phone">
                                    No. Telepon
                                    <span>*</span>
                                </label>

                                <input type="tel" id="phone" name="phone" placeholder="Contoh: 081234567890"
                                    value="{{ old('phone') }}" required>

                            </div>


                            {{-- EMAIL --}}
                            <div class="form-group">

                                <label for="email">
                                    Email
                                    <span>*</span>
                                </label>

                                <input type="email" id="email" name="email" placeholder="Contoh: nama@email.com"
                                    value="{{ old('email') }}" required>

                            </div>


                            {{-- ORGANISASI --}}
                            <div class="form-group">

                                <label for="organization">
                                    Organisasi
                                    <small>(Opsional)</small>
                                </label>

                                <input type="text" id="organization" name="organization" placeholder="Nama organisasi"
                                    value="{{ old('organization') }}">

                            </div>


                            {{-- DAERAH --}}
                            <div class="form-group form-group-full">

                                <label for="region">
                                    Daerah
                                    <small>(Opsional)</small>
                                </label>

                                <input type="text" id="region" name="region"
                                    placeholder="Contoh: Kabupaten Indramayu" value="{{ old('region') }}">

                            </div>


                            {{-- DESKRIPSI --}}
                            <div class="form-group form-group-full">

                                <label for="description">
                                    Deskripsi
                                    <span>*</span>
                                </label>

                                <textarea id="description" name="description" rows="6"
                                    placeholder="Jelaskan informasi, lokasi, atau kejadian yang Anda temukan..." required>{{ old('description') }}</textarea>

                            </div>

                        </div>


                        <div class="form-footer">

                            <p>
                                <span>*</span>
                                Wajib diisi
                            </p>

                            <button type="submit" class="btn btn-primary">
                                Kirim Informasi
                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </section>


        
{{-- ================= FOOTER ================= --}}
<footer class="public-footer">
    <div class="container">

        <div class="footer-grid">

            {{-- Identitas SIGMA --}}
            <div class="footer-about">
                <h3 class="footer-brand">
                    {{ $profile->title ?? 'SIGMA' }}
                </h3>

                <p>
                    Sistem Intelijen Geospasial untuk Mitigasi
                    Karhutla.
                </p>

                <div class="footer-tagline">
                    Pantau <span>•</span>
                    Analisis <span>•</span>
                    Prediksi <span>•</span>
                    Aksi
                </div>
            </div>

            {{-- Tentang SIGMA --}}
            <div class="footer-information">
                <h4>Tentang SIGMA</h4>

                <p>
                    Platform pendukung keputusan berbasis AI dan GIS
                    untuk membantu pemantauan risiko, analisis dampak,
                    dan penentuan prioritas penanganan karhutla.
                </p>
            </div>

            {{-- Fitur --}}
            <div class="footer-features">
                <h4>Fitur Utama</h4>

                <ul>
                    <li>
                        <a href="#monitoring">Monitoring Karhutla</a>
                    </li>
                    <li>Analisis Risiko dan Dampak</li>
                    <li>Prioritas Penanganan</li>
                    <li>Rekomendasi Tindakan</li>
                    <li>
                        <a href="#aspirasi">Laporan Masyarakat</a>
                    </li>
                </ul>
            </div>
            

        </div>

        <div class="footer-bottom">
            <span>
                © {{ date('Y') }}
                {{ $profile->title ?? 'SIGMA' }}.
                Hak cipta dilindungi.
            </span>

            <span>
                Sistem Intelijen Geospasial untuk Mitigasi Karhutla
            </span>
        </div>

    </div>
</footer>

        

    </div>
@endsection

@push('scripts')

{{-- Data wilayah dari Laravel --}}
<script>
    window.sigmaRegions = @json($sigmaRegions);
</script>

{{-- Leaflet --}}
<script
    src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js">
</script>

{{-- GIS Map --}}
<script
    src="{{ asset('js/government/gis-map.js') }}?v={{ time() }}">
</script>

@endpush

