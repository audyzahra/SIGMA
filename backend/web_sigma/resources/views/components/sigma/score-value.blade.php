@props([
    'value' => null,
    'available' => null,
    'decimals' => 0,
    'unit' => null,
    'scale' => null,
])

{{--
    Nilai skor dengan format angka Indonesia.

    Bila nilainya belum tersedia, komponen menampilkan teks standar lewat
    <x-sigma.unavailable>, sehingga tidak pernah ada sel kosong atau angka 0
    yang menyesatkan.
--}}

@php
    $isAvailable = $available ?? ($value !== null);

    $formatted = $isAvailable
        ? number_format((float) $value, (int) $decimals, ',', '.')
        : null;

    $suffix = $scale !== null ? (string) $scale : $unit;
@endphp

@if ($isAvailable)

    <span {{ $attributes->merge(['class' => 'score-value']) }}>
        <b>{{ $formatted }}</b>@if ($suffix)<small>{{ $scale !== null ? '/ ' . $suffix : $suffix }}</small>@endif
    </span>

@else

    <x-sigma.unavailable {{ $attributes->merge(['class' => 'score-value']) }} />

@endif
