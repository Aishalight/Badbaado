<nav aria-label="Pagination" class="filter-bar mt-4 flex items-center justify-between gap-4 border-t border-slate-100 pt-4">
    @php($from = $pager->firstItem())
    @php($to = $pager->lastItem())
    <p class="text-xs text-slate-400">Showing @if ($from){{ $from }}–{{ $to }} of {{ $pager->total() }}@else{{ $pager->total() }}@endif entries</p>
    <div class="pager">
        @if ($pager->onFirstPage())
            <span class="disabled" aria-hidden="true">‹</span>
            <span class="sr-only">On first page</span>
        @else
            <a href="{{ $pager->previousPageUrl() }}" rel="prev" aria-label="Previous page">‹</a>
        @endif
        @foreach ($pager->getUrlRange(max(1, $pager->currentPage() - 2), min($pager->lastPage(), $pager->currentPage() + 2)) as $page => $url)
            @if ($pager->currentPage() === $page)
                <span class="active" aria-current="page">{{ $page }}</span>
            @else
                <a href="{{ $url }}" aria-label="Page {{ $page }}">{{ $page }}</a>
            @endif
        @endforeach
        @if ($pager->hasMorePages())
            <a href="{{ $pager->nextPageUrl() }}" rel="next" aria-label="Next page">›</a>
        @else
            <span class="disabled" aria-hidden="true">›</span>
            <span class="sr-only">On last page</span>
        @endif
    </div>
</nav>
