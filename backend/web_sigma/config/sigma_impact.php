<?php

/*
|--------------------------------------------------------------------------
| Konfigurasi Analisis Dampak (Impact Analysis)
|--------------------------------------------------------------------------
|
| Semua angka metodologi ada di sini supaya analisis reproducible,
| transparan, dan mudah diaudit (bukan hardcode di controller/Blade/JS).
|
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Versi metodologi
    |--------------------------------------------------------------------------
    |
    | Disimpan bersama setiap hasil analisis agar hasil lama tetap dapat
    | dijelaskan walaupun rumus diperbarui.
    |
    */

    'methodology_version' => 'impact-spatial-v1.0',

    /*
    |--------------------------------------------------------------------------
    | Pilihan radius analisis (km)
    |--------------------------------------------------------------------------
    |
    | Nilai ini HANYA pilihan di UI. Perhitungan selalu memakai nilai yang
    | dikirim request setelah divalidasi (min/max di bawah).
    |
    */

    'radius_options' => [1, 3, 5, 10],

    'radius_default' => 5,

    'radius_min_km' => 0.5,

    'radius_max_km' => 50,

    /*
    |--------------------------------------------------------------------------
    | Bobot komponen Impact Score
    |--------------------------------------------------------------------------
    |
    | Total = 1.0. Bobot hanya dipakai untuk komponen yang datanya TERSEDIA.
    | Jika sebuah komponen tidak tersedia, bobotnya dikeluarkan lalu bobot
    | komponen tersisa dinormalisasi ulang (lihat ImpactAnalysisService).
    |
    | Komponen:
    |  - hotspot_intensity        : intensitas hotspot FIRMS (FRP + brightness + jumlah)
    |  - fire_risk_context        : risiko karhutla wilayah terdampak (fire_risks)
    |  - administrative_exposure  : banyak kecamatan terdampak (beban koordinasi)
    |  - incident_pressure        : insiden aktif di dalam radius
    |
    | Metrik yang TIDAK dijadikan komponen skor (dilaporkan sebagai informasi):
    |  - luas irisan wilayah (km2/ha) dan cakupan zona, karena untuk geometri
    |    administratif yang saling menutup nilainya hampir selalu penuh
    |    sehingga tidak informatif bila dijadikan skor.
    |
    | Komponen berikut BELUM tersedia di database SIGMA sehingga
    | TIDAK dihitung (dilaporkan sebagai tidak tersedia, bukan diisi 0):
    |  - population_exposure, settlement_exposure, critical_facility_exposure,
    |    infrastructure_exposure, environmental_exposure
    |
    */

    'weights' => [
        'hotspot_intensity' => 0.35,
        'fire_risk_context' => 0.30,
        'administrative_exposure' => 0.20,
        'incident_pressure' => 0.15,
    ],

    /*
    |--------------------------------------------------------------------------
    | Catatan metodologi (dibaca UI sebagai penjelasan)
    |--------------------------------------------------------------------------
    */

    'methodology_notes' => [
        'Impact Score = rata-rata berbobot komponen yang datanya TERSEDIA, dinormalisasi ulang bila ada komponen yang tidak tersedia.',
        'hotspot_intensity = 50% FRP maksimum + 30% brightness maksimum + 20% kerapatan hotspot (semua dari NASA FIRMS).',
        'fire_risk_context = rata-rata risk_score wilayh terdampak, dibobot luas irisan wilayah (dari fire_risks hasil AI).',
        'administrative_exposure = jumlah kecamatan terdampak dinormalisasi (beban koordinasi lintas wilayah).',
        'incident_pressure = jumlah insiden di dalam radius, dibatasi pada nilai referensi.',
        'Luas terdampak (km2/ha), cakupan zona, dan jumlah hotspot dilaporkan sebagai metrik informasi, bukan komponen skor.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Normalisasi komponen
    |--------------------------------------------------------------------------
    */

    'normalization' => [

        /* FRP hotspot maksimum (MW) yang dianggap ekstrem (persentil atas FIRMS) */
        'hotspot_frp_reference' => 50.0,

        /* Brightness (K) yang dianggap ekstrem */
        'hotspot_brightness_reference' => 360.0,

        /* Jumlah hotspot yang dianggap padat dalam satu zona analisis */
        'hotspot_count_reference' => 10,

        /* Banyak kecamatan terdampak yang dianggap luas */
        'district_count_reference' => 6,

        /* Banyak insiden aktif yang dianggap tinggi */
        'incident_count_reference' => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Ambang Impact Level
    |--------------------------------------------------------------------------
    */

    'levels' => [
        'critical' => 80,
        'high' => 60,
        'medium' => 40,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache hasil analisis (detik)
    |--------------------------------------------------------------------------
    */

    'cache_seconds' => 300,

    /*
    |--------------------------------------------------------------------------
    | Daftar sumber data & ketersediaannya
    |--------------------------------------------------------------------------
    |
    | Dipakai UI untuk menampilkan "Data tidak tersedia" secara jujur.
    | status = available | unavailable
    |
    */

    'data_sources' => [

        'region_geometry' => [
            'label' => 'Geometri Wilayah (GIS Database)',
            'status' => 'available',
            'note' => 'Tabel regions (province/regency/district), kolom spatial geometry SRID 4326.',
        ],

        'fire_risk' => [
            'label' => 'Risiko Karhutla (AI Service)',
            'status' => 'available',
            'note' => 'Hasil AI (NASA POWER + NASA FIRMS + model) pada tabel fire_risks.',
        ],

        'hotspot' => [
            'label' => 'Hotspot Satelit (NASA FIRMS)',
            'status' => 'available',
            'note' => 'Tabel hotspots.',
        ],

        'incident' => [
            'label' => 'Insiden',
            'status' => 'available',
            'note' => 'Tabel incidents (hotspot/report/manual).',
        ],

        'citizen_report' => [
            'label' => 'Laporan Masyarakat',
            'status' => 'available',
            'note' => 'Tabel citizen_reports sebagai informasi tambahan, bukan pengganti AI.',
        ],

        'population' => [
            'label' => 'Data Penduduk',
            'status' => 'unavailable',
            'note' => 'Belum ada dataset penduduk (grid/BPS) di database SIGMA.',
        ],

        'settlement' => [
            'label' => 'Permukiman / Bangunan',
            'status' => 'unavailable',
            'note' => 'Belum ada dataset bangunan/permukiman di database SIGMA.',
        ],

        'critical_facility' => [
            'label' => 'Fasilitas Kritis (Sekolah, Kesehatan)',
            'status' => 'unavailable',
            'note' => 'Belum ada dataset fasilitas pendidikan/kesehatan di database SIGMA.',
        ],

        'infrastructure' => [
            'label' => 'Infrastruktur Jalan',
            'status' => 'unavailable',
            'note' => 'Belum ada dataset jaringan jalan di database SIGMA.',
        ],

        'land_cover' => [
            'label' => 'Tutupan Lahan / Hutan',
            'status' => 'unavailable',
            'note' => 'ESA WorldCover masih raster di ai_service, belum diimpor ke database SIGMA.',
        ],
    ],
];