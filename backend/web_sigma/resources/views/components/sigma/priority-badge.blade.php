@props([
    'level' => null,
    'label' => null,
])

{{--
    Lencana kategori prioritas.

    Label dan kelas CSS diambil dari config/sigma_priority.php (levels), jadi
    menambah atau mengubah kategori cukup dilakukan di konfigurasi.
--}}

@php
    $meta = $level ? (array) config("sigma_priority.levels.{$level}", []) : [];

    $text = $label ?? ($meta['label'] ?? null);

    /* Kategori di luar config tetap tampil dengan gaya netral, bukan kosong */
    $badge = $meta['badge'] ?? 'priority-unknown';
@endphp

@if ($text !== null)

    <span {{ $attributes->merge(['class' => 'priority-badge ' . $badge]) }}>
        {{ $text }}
    </span>

@else

    {{-- Wilayah yang prioritasnya belum dihitung --}}
    <x-sigma.unavailable {{ $attributes }} />

@endif

