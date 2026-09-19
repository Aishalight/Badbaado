<nav class="filter-bar mt-4 flex items-center justify-between gap-4 border-t border-slate-100 pt-4">
    @php($from = $pager->firstItem())
    @php($to = $pager->lastItem())
    <p class="text-xs text-slate-400">Showing @if ($from){{ $from }}–{{ $to }} of {{ $pager->total() }}@else{{ $pager->total() }}@endif entries</p>
    <div class="pager">
        @if ($pager->onFirstPage())
            <span class="disabled">‹</span>
        @else
            <a href="{{ $pager->previousPageUrl() }}">‹</a>
        @endif
        @foreach ($pager->getUrlRange(max(1, $pager->currentPage() - 2), min($pager->lastPage(), $pager->currentPage() + 2)) as $page => $url)
            @if ($pager->currentPage() === $page)
                <span class="active">{{ $page }}</span>
            @else
                <a href="{{ $url }}">{{ $page }}</a>
            @endif
        @endforeach
        @if ($pager->hasMorePages())
            <a href="{{ $pager->nextPageUrl() }}">›</a>
        @else
            <span class="disabled">›</span>
        @endif
    </div>
</nav>