@props(['paginator'])

<div class="pagination">

    {{-- Informasi data --}}
    <span>
        @if ($paginator->total() > 0)
            Menampilkan
            {{ $paginator->firstItem() }}
            -
            {{ $paginator->lastItem() }}
            dari
            {{ $paginator->total() }}
            data terdaftar
        @else
            Tidak ada data terdaftar
        @endif
    </span>

    @if ($paginator->lastPage() > 1)
        <div>

            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <button type="button" disabled>
                    ‹
                </button>
            @else
                <a
                    href="{{ $paginator->previousPageUrl() }}"
                    class="pagination-link"
                >
                    ‹
                </a>
            @endif


            {{-- Nomor halaman --}}
            @php
                $currentPage = $paginator->currentPage();
                $lastPage = $paginator->lastPage();

                $startPage = max(1, $currentPage - 2);
                $endPage = min($lastPage, $currentPage + 2);
            @endphp


            {{-- Halaman pertama --}}
            @if ($startPage > 1)

                <a
                    href="{{ $paginator->url(1) }}"
                    class="pagination-link"
                >
                    1
                </a>

                @if ($startPage > 2)
                    <span class="pagination-dots">...</span>
                @endif

            @endif


            {{-- Halaman --}}
            @for ($page = $startPage; $page <= $endPage; $page++)

                @if ($page == $currentPage)

                    <button
                        type="button"
                        class="current"
                        disabled
                    >
                        {{ $page }}
                    </button>

                @else

                    <a
                        href="{{ $paginator->url($page) }}"
                        class="pagination-link"
                    >
                        {{ $page }}
                    </a>

                @endif

            @endfor


            {{-- Halaman terakhir --}}
            @if ($endPage < $lastPage)

                @if ($endPage < $lastPage - 1)
                    <span class="pagination-dots">...</span>
                @endif

                <a
                    href="{{ $paginator->url($lastPage) }}"
                    class="pagination-link"
                >
                    {{ $lastPage }}
                </a>

            @endif


            {{-- Next --}}
            @if ($paginator->hasMorePages())

                <a
                    href="{{ $paginator->nextPageUrl() }}"
                    class="pagination-link"
                >
                    ›
                </a>

            @else

                <button type="button" disabled>
                    ›
                </button>

            @endif

        </div>
    @endif

</div>