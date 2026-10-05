@props([
    'label' => '',
    'value' => null,
    'available' => null,
    'decimals' => 0,
    'unit' => null,
])

{{--
    Satu baris parameter: label di kiri, nilai di kanan.

    Nilai dirender oleh <x-sigma.score-value> sehingga aturan "belum tersedia"
    berlaku sama di seluruh halaman.
--}}

<div {{ $attributes->merge(['class' => 'parameter']) }}>

    <span>{{ $label }}</span>

    <x-sigma.score-value
        :value="$value"
        :available="$available"
        :decimals="$decimals"
        :unit="$unit"
        class="parameter-value" />

</div>
