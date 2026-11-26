@if ($paginator->hasPages())
    <nav class="participant-pagination" role="navigation" aria-label="Navigasi halaman data peserta">
        @if ($paginator->onFirstPage())
            <span class="participant-pagination-control" aria-disabled="true" aria-label="Halaman sebelumnya">
                <svg viewBox="0 0 20 20" fill="none" aria-hidden="true">
                    <path d="m12.5 4.5-5 5.5 5 5.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
        @else
            <a class="participant-pagination-control" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Halaman sebelumnya">
                <svg viewBox="0 0 20 20" fill="none" aria-hidden="true">
                    <path d="m12.5 4.5-5 5.5 5 5.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="participant-pagination-ellipsis" aria-hidden="true">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="participant-pagination-page" aria-current="page" aria-label="Halaman {{ $page }}, halaman aktif">{{ $page }}</span>
                    @else
                        <a class="participant-pagination-page" href="{{ $url }}" aria-label="Buka halaman {{ $page }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a class="participant-pagination-control" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Halaman berikutnya">
                <svg viewBox="0 0 20 20" fill="none" aria-hidden="true">
                    <path d="m7.5 4.5 5 5.5-5 5.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
        @else
            <span class="participant-pagination-control" aria-disabled="true" aria-label="Halaman berikutnya">
                <svg viewBox="0 0 20 20" fill="none" aria-hidden="true">
                    <path d="m7.5 4.5 5 5.5-5 5.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
        @endif
    </nav>
@endif
