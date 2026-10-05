@props([
    'text' => 'Data belum tersedia',
])

{{--
    Teks standar untuk data yang belum tersedia.

    Komponen ini adalah SATU-SATUNYA tempat teks tersebut ditulis, sehingga
    seluruh halaman Prioritas Penanganan (tabel ranking, detail wilayah,
    faktor penyebab, sampai keluaran AI Recommendation) menampilkan kalimat
    yang konsisten tanpa duplikasi.
--}}

<span {{ $attributes->merge(['class' => 'data-unavailable']) }}>{{ $text }}</span>

