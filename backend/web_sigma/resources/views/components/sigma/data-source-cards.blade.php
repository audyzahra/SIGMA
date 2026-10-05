@props([
    'sources' => [],
    'class' => '',
])

{{--
    Kartu transparansi sumber data.

    "available" berasal dari pemeriksaan nyata ke database (probe pada
    config/sigma_priority.php), bukan daftar manual: begitu dataset baru
    diimpor, statusnya berubah sendiri.
--}}

<div {{ $attributes->merge(['class' => 'source-cards priority-sources ' . $class]) }}>

    @foreach ($sources as $key => $source)

        <article class="source-card" data-source="{{ $key }}">

            <h3>{{ $source['label'] }}</h3>

            <span class="status {{ $source['available'] ? 'aktif' : 'pending' }}">
                {{ $source['available'] ? 'Tersedia' : 'Belum tersedia' }}
            </span>

            <p>{{ $source['note'] }}</p>

            @if (! empty($source['role']))
                <small class="source-role">{{ $source['role'] }}</small>
            @endif

        </article>

    @endforeach

</div>
