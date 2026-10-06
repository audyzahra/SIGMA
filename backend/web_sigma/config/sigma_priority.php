<?php

/*
|--------------------------------------------------------------------------
| Konfigurasi Prioritas Penanganan (Priority Calculation Engine)
|--------------------------------------------------------------------------
|
| Semua angka metodologi modul Prioritas Penanganan ada di sini supaya
| perhitungan reproducible, transparan, dan dapat diaudit.
|
| Tidak ada bobot, ambang kategori, atau label faktor yang ditulis ulang di
| Controller / Blade / JavaScript.
|
| Alur data:
|   Data Source (hotspot, risiko kebakaran, cuaca, tutupan lahan, riwayat
|   kebakaran, penduduk, bangunan, fasilitas publik, jaringan jalan)
|       -> Risk Analysis   (fire_risks, hasil AI Service)
|       -> Impact Analysis (impact_assessments, hasil analisis spasial GIS)
|       -> Priority Calculation Engine (PriorityCalculationService)
|       -> Prioritas Penanganan (priority_results)
|
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Versi metodologi
    |--------------------------------------------------------------------------
    |
    | Disimpan bersama setiap hasil perhitungan agar hasil lama tetap dapat
    | dijelaskan walaupun rumus/bobot diperbarui.
    |
    */

    'methodology_version' => 'priority-hybrid-v1.0',

    /*
    |--------------------------------------------------------------------------
    | Bobot komponen Priority Score
    |--------------------------------------------------------------------------
    |
    | Priority Score = (Risk Score x weight.risk) + (Impact Score x weight.impact)
    |
    | Bobot HANYA dipakai untuk komponen yang datanya tersedia. Bila salah
    | satu komponen belum tersedia (mis. wilayah belum pernah dianalisis
    | dampaknya), bobot komponen tersebut dikeluarkan lalu bobot komponen
    | tersisa dinormalisasi ulang. Angka 0 TIDAK dipakai sebagai pengganti
    | data yang belum tersedia, karena 0 berarti "tidak berisiko".
    |
    */

    'weights' => [
        'risk' => 0.6,
        'impact' => 0.4,
    ],

    /*
    |--------------------------------------------------------------------------
    | Label & asal komponen skor
    |--------------------------------------------------------------------------
    |
    | Dipakai di tabel ranking, halaman detail, dan keluaran JSON.
    | "source" adalah penyebutan singkat model/dataset, "source_detail"
    | menjelaskan tabel asalnya.
    |
    */

    'components' => [
        'risk' => [
            'label' => 'Risiko',
            'label_long' => 'Skor Risiko Karhutla',
            'source' => 'Fire Risk Model',
            'source_detail' => 'fire_risks — hasil AI Service (NASA POWER + NASA FIRMS + model risiko).',
        ],
        'impact' => [
            'label' => 'Dampak',
            'label_long' => 'Skor Dampak Wilayah',
            'source' => 'Population Dataset + GIS Analysis',
            'source_detail' => 'impact_assessments — hasil analisis dampak spasial (geometri wilayah, hotspot, insiden, laporan).',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Kategori prioritas
    |--------------------------------------------------------------------------
    |
    | threshold : batas bawah skor untuk kategori tersebut
    | label     : teks kategori di UI
    | badge     : kelas lencana (.priority-badge di app.css)
    | accent    : warna kartu ringkasan
    |
    | Urutan penilaian mengikuti "level_order" (skor tertinggi diperiksa
    | lebih dahulu).
    |
    */

    'level_order' => ['critical', 'high', 'medium', 'low'],

    'levels' => [
        'critical' => [
            'threshold' => 80,
            'label' => 'Kritis',
            'badge' => 'priority-kritis',
            'accent' => 'danger',
            'note' => 'Wajib ditangani lebih dahulu: risiko tinggi dan dampak besar.',
        ],
        'high' => [
            'threshold' => 60,
            'label' => 'Tinggi',
            'badge' => 'priority-tinggi',
            'accent' => 'orange',
            'note' => 'Perlu penanganan segera dan penyiapan sumber daya.',
        ],
        'medium' => [
            'threshold' => 40,
            'label' => 'Sedang',
            'badge' => 'priority-sedang',
            'accent' => 'warning',
            'note' => 'Dipantau berkala dengan penanganan terjadwal.',
        ],
        'low' => [
            'threshold' => 0,
            'label' => 'Rendah',
            'badge' => 'priority-rendah',
            'accent' => 'green',
            'note' => 'Kondisi relatif aman, tetap dipantau.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Wilayah yang dihitung
    |--------------------------------------------------------------------------
    */

    'region_levels' => ['province', 'regency', 'district'],

    'region_level_labels' => [
        'country' => 'Nasional',
        'province' => 'Provinsi',
        'regency' => 'Kabupaten / Kota',
        'district' => 'Kecamatan',
        'village' => 'Desa / Kelurahan',
    ],

    /*
    |--------------------------------------------------------------------------
    | Tabel ranking
    |--------------------------------------------------------------------------
    */

    'per_page' => 10,

    'per_page_max' => 100,

    /*
    |--------------------------------------------------------------------------
    | Katalog faktor penyebab
    |--------------------------------------------------------------------------
    |
    | Faktor risiko dibaca dari kolom tabel fire_risks (hasil AI Service).
    | Faktor dampak dibaca dari kolom impact_assessments (indikator mentah)
    | dan dari kolom components (rincian komponen skor analisis spasial).
    |
    | decimals  : jumlah angka di belakang koma saat ditampilkan
    | influence : penjelasan arah pengaruh faktor terhadap risiko
    |
    | Nilai kolom NULL berarti "belum tersedia", bukan nol.
    |
    */

    'risk_factors' => [
        'temperature' => [
            'label' => 'Suhu Udara',
            'unit' => '°C',
            'decimals' => 1,
            'influence' => 'Suhu tinggi mempercepat kekeringan bahan bakar.',
        ],
        'humidity' => [
            'label' => 'Kelembapan Udara',
            'unit' => '%',
            'decimals' => 1,
            'influence' => 'Kelembapan rendah menaikkan peluang penyalaan.',
        ],
        'rainfall' => [
            'label' => 'Curah Hujan',
            'unit' => 'mm',
            'decimals' => 1,
            'influence' => 'Hujan minim berarti bahan bakar tidak terbasahi.',
        ],
        'wind_speed' => [
            'label' => 'Kecepatan Angin',
            'unit' => 'm/s',
            'decimals' => 1,
            'influence' => 'Angin kencang mempercepat perluasan api.',
        ],
        'vegetation_index' => [
            'label' => 'Indeks Vegetasi',
            'unit' => 'NDVI',
            'decimals' => 3,
            'influence' => 'Vegetasi kering menambah beban bahan bakar.',
        ],
        'ai_confidence' => [
            'label' => 'Keyakinan Model AI',
            'unit' => '%',
            'decimals' => 2,
            'influence' => 'Tingkat keyakinan model terhadap hasil prediksi risiko.',
        ],
    ],

    'impact_indicators' => [
        'affected_district_count' => [
            'label' => 'Kecamatan Terdampak',
            'unit' => 'kecamatan',
            'decimals' => 0,
        ],
        'hotspot_count' => [
            'label' => 'Hotspot dalam Zona',
            'unit' => 'titik',
            'decimals' => 0,
        ],
        'incident_count' => [
            'label' => 'Insiden dalam Zona',
            'unit' => 'insiden',
            'decimals' => 0,
        ],
        'affected_area' => [
            'label' => 'Luas Zona Analisis',
            'unit' => 'km²',
            'decimals' => 2,
        ],
        'risk_weighted_score' => [
            'label' => 'Rata-rata Risiko Wilayah Terdampak',
            'unit' => 'skor',
            'decimals' => 2,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Sumber data & ketersediaannya
    |--------------------------------------------------------------------------
    |
    | "probe" adalah cara service memeriksa ketersediaan dataset secara nyata
    | di database (bukan daftar manual):
    |   'nama_tabel'                              -> tabel memiliki baris
    |   ['table' => 'regions', 'column' => 'x']   -> kolom terisi pada >= 1 baris
    |   null                                      -> dataset belum ada di SIGMA
    |
    | Saat dataset baru diimpor (penduduk, bangunan, fasilitas publik,
    | jaringan jalan, tutupan lahan, rekaman cuaca), cukup isi "probe"-nya:
    | seluruh modul langsung mengenali dataset tersebut tanpa perubahan kode.
    |
    */

    'data_sources' => [

        'region_geometry' => [
            'label' => 'Geometri Wilayah (GIS)',
            'probe' => ['table' => 'regions', 'column' => 'geometry'],
            'note' => 'Batas administrasi provinsi/kabupaten/kecamatan (SRID 4326).',
            'role' => 'Basis analisis spasial: luas, irisan wilayah, dan titik tengah.',
        ],

        'fire_risk' => [
            'label' => 'Risiko Karhutla (AI)',
            'probe' => 'fire_risks',
            'note' => 'Skor risiko hasil AI Service (NASA POWER + NASA FIRMS + model).',
            'role' => 'Sumber Risk Score pada perhitungan prioritas.',
        ],

        'fire_history' => [
            'label' => 'Riwayat Kebakaran',
            'probe' => 'fire_risk_histories',
            'note' => 'Riwayat hasil analisis risiko per wilayah dan waktu.',
            'role' => 'Tren risiko dan dasar kalibrasi model.',
        ],

        'hotspot' => [
            'label' => 'Hotspot Satelit (NASA FIRMS)',
            'probe' => 'hotspots',
            'note' => 'Titik panas harian beserta FRP dan brightness.',
            'role' => 'Intensitas hotspot di dalam zona analisis dampak.',
        ],

        'incident' => [
            'label' => 'Insiden',
            'probe' => 'incidents',
            'note' => 'Insiden karhutla (dari hotspot, laporan, atau input manual).',
            'role' => 'Tekanan insiden pada perhitungan dampak.',
        ],

        'weather' => [
            'label' => 'Dataset Cuaca',
            'probe' => 'weather_records',
            'note' => 'Rekaman cuaca per koordinat dan tanggal (suhu, kelembapan, hujan, angin).',
            'role' => 'Masukan model risiko dan masukan AI Recommendation.',
        ],

        'land_cover' => [
            'label' => 'Tutupan Lahan',
            'probe' => 'region_land_covers',
            'note' => 'ESA WorldCover 2021 (~10 m) hasil impor pada tabel region_land_covers (hutan kelas 10, lahan basah/gambut kelas 90).',
            'role' => 'Bahan bakar (gambut/vegetasi) untuk analisis risiko dan dampak.',
        ],

        'population' => [
            'label' => 'Penduduk (WorldPop / BPS)',
            'probe' => 'region_populations',
            'note' => 'Agregasi WorldPop UN-adjusted 2020 per wilayah pada tabel region_populations.',
            'role' => 'Menghitung jumlah penduduk terpapar.',
        ],

        'building' => [
            'label' => 'Bangunan / Permukiman',
            'probe' => null,
            'note' => 'Belum ada dataset bangunan; rumah tangga terpapar diestimasi dari penduduk terpapar dibagi rata-rata jiwa per rumah tangga (BPS).',
            'role' => 'Menghitung rumah dan bangunan terpapar.',
        ],

        'public_facility' => [
            'label' => 'Fasilitas Publik',
            'probe' => 'facilities',
            'note' => 'Fasilitas OSM/HOTOSM (sekolah, puskesmas, rumah sakit) hasil impor pada tabel facilities.',
            'role' => 'Menghitung fasilitas kritis terdampak.',
        ],

        'road_network' => [
            'label' => 'Jaringan Jalan',
            'probe' => null,
            'note' => 'Belum ada dataset jaringan jalan di database SIGMA.',
            'role' => 'Analisis akses dan hambatan mobilisasi tim.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Recommendation (disiapkan, belum diaktifkan)
    |--------------------------------------------------------------------------
    |
    | Modul Prioritas hanya MENYEDIAKAN data. Pemanggilan model bahasa
    | (Gemini/LLM) belum diimplementasikan; selama "enabled" masih false,
    | AIRecommendationService mengembalikan status belum tersedia beserta
    | struktur keluaran yang sudah ditetapkan (recommendation,
    | priority_action) supaya UI dan kontrak data tidak perlu diubah lagi.
    |
    */

    'ai_recommendation' => [
        'enabled' => false,
        'provider' => 'gemini',
        'endpoint' => env('AI_RECOMMENDATION_URL'),
        'timeout' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Catatan metodologi (dibaca UI sebagai penjelasan)
    |--------------------------------------------------------------------------
    */

    'methodology_notes' => [
        'Priority Score = (Risk Score x 60%) + (Impact Score x 40%); bobot diatur di config/sigma_priority.php.',
        'Risk Score berasal dari tabel fire_risks (hasil AI Service), Impact Score berasal dari impact_assessments (analisis spasial GIS).',
        'Bila salah satu komponen belum tersedia, bobot komponen tersisa dinormalisasi ulang dan kolom kelengkapan data menunjukkan persentasenya.',
        'Wilayah yang belum memiliki data risiko maupun dampak tidak diranking, tetapi dilaporkan pada ringkasan sebagai wilayah belum dianalisis.',
        'Ranking dihitung oleh PriorityCalculationService pada tabel priority_results, bukan ditentukan manual.',
    ],

];
