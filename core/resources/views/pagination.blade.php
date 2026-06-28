@if ($paginator->hasPages())
    <nav aria-label="pagination">
        <ul class="pagination justify-content-center">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled"><a class="page-link">{{ __('Previous') }}</a></li>
            @else
                <li class="page-item"><a class="page-link"
                        href="{{ $paginator->previousPageUrl() }}">{{ __('Previous') }}</a></li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li class="page-item shadow disabled"><a class="page-link active">{{ $element }}</a></li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @php
                        $currentPage = $paginator->currentPage();
                        $lastPage = $paginator->lastPage();
                        $start = max(1, $currentPage - 2);
                        $end = min($lastPage, $currentPage + 1);

                        // Adjust start and end to always show 4 buttons if possible
                        if ($currentPage == 1) {
                            $end = min(4, $lastPage);
                        } elseif ($currentPage == 2) {
                            $end = min(4, $lastPage);
                        } elseif ($currentPage == $lastPage) {
                            $start = max($lastPage - 3, 1);
                        } elseif ($currentPage == $lastPage - 1) {
                            $start = max($lastPage - 3, 1);
                        } else {
                            $start = max(1, $currentPage - 2);
                            $end = min($lastPage, $currentPage + 1);
                        }
                    @endphp

                    @foreach ($element as $page => $url)
                        @if ($page >= $start && $page <= $end)
                            @if ($page == $currentPage)
                                <li class="page-item shadow disabled"><a class="page-link active">{{ $page }}</a></li>
                            @else
                                <li class='page-item'><a class='page-link' href="{{ $url }}">{{ $page }}</a></li>
                            @endif
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item"><a class="page-link"
                        href="{{ $paginator->nextPageUrl() }}">{{ __('Next') }}</a></li>
            @else
                <li class="page-item disabled"><a class="page-link">{{ __('Next') }}</a></li>
            @endif
        </ul>
    </nav>
@endif
