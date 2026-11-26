@props([
    'paginator',
    'recordName' => 'records',
])

@php
    $total = $paginator->total();
    $current = $paginator->currentPage();
    $last = max(1, $paginator->lastPage());
    $perPage = (int) request('per_page', $paginator->perPage());
    if (!in_array($perPage, [6, 10, 25, 50, 100], true)) {
        $perPage = 10;
    }
    $perPageOptions = [10, 25, 50, 100];
@endphp

<div class="table-pagination-bar">
    <div class="table-pagination-info">
        <span>
            @if ($total === 0)
                Showing 0 {{ $recordName }}
            @else
                Showing {{ $paginator->firstItem() }}-{{ $paginator->lastItem() }} of {{ $total }} {{ $recordName }} recorded
                <span style="opacity: 0.75; font-size: 12px; margin-left: 4px;">(Page {{ $current }} of {{ $last }})</span>
            @endif
        </span>

        <div class="table-per-page-wrap" style="margin-left: 8px;">
            <label for="perPageSelect-{{ Str::slug($recordName) }}" style="font-size: 12px; color: var(--tbl-muted);">Per page:</label>
            <select id="perPageSelect-{{ Str::slug($recordName) }}"
                    class="table-per-page-select"
                    aria-label="Records per page"
                    onchange="window.location.href = this.value">
                @foreach ($perPageOptions as $option)
                    <option value="{{ request()->fullUrlWithQuery(['per_page' => $option, 'page' => 1]) }}"
                            {{ $perPage === $option ? 'selected' : '' }}>
                        {{ $option }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    @if ($paginator->hasPages())
        <nav class="pagination-controls" aria-label="{{ ucfirst($recordName) }} pagination">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="pagination-btn disabled" aria-disabled="true" title="First page">&larr; Previous</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="pagination-btn" rel="prev" title="Previous page">&larr; Previous</a>
            @endif

            {{-- Page Numbers (Sliding Window) --}}
            <div class="pagination-numbers">
                @php
                    $start = max(1, $current - 2);
                    $end = min($last, $current + 2);
                @endphp

                @if ($start > 1)
                    <a href="{{ $paginator->url(1) }}" class="pagination-number {{ $current === 1 ? 'active' : '' }}">1</a>
                    @if ($start > 2)
                        <span class="pagination-number disabled" style="border:none; background:transparent; cursor:default; min-width:18px; padding:0;">&hellip;</span>
                    @endif
                @endif

                @for ($page = $start; $page <= $end; $page++)
                    @if ($page == $current)
                        <span class="pagination-number active" aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $paginator->url($page) }}" class="pagination-number">{{ $page }}</a>
                    @endif
                @endfor

                @if ($end < $last)
                    @if ($end < $last - 1)
                        <span class="pagination-number disabled" style="border:none; background:transparent; cursor:default; min-width:18px; padding:0;">&hellip;</span>
                    @endif
                    <a href="{{ $paginator->url($last) }}" class="pagination-number {{ $current === $last ? 'active' : '' }}">{{ $last }}</a>
                @endif
            </div>

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="pagination-btn" rel="next" title="Next page">Next &rarr;</a>
            @else
                <span class="pagination-btn disabled" aria-disabled="true" title="Last page">Next &rarr;</span>
            @endif
        </nav>
    @endif
</div>
