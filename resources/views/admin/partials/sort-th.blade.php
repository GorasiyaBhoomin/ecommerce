@props([
    'column',
    'label',
    'currentSortBy' => request('sort_by'),
    'currentSortDir' => request('sort_dir', 'desc'),
    'style' => '',
])

@php
    $isActive = ($currentSortBy === $column);
    $nextDir = ($isActive && strtolower($currentSortDir) === 'asc') ? 'desc' : 'asc';
    $queryParams = request()->query();
    unset($queryParams['sort']);
    $queryParams['sort_by'] = $column;
    $queryParams['sort_dir'] = $nextDir;
    $queryParams['page'] = 1;
    $url = url()->current() . '?' . http_build_query($queryParams);
@endphp

<th class="th-sortable {{ $isActive ? 'th-sort-active' : '' }}" style="{{ $style }}">
    <a href="{{ $url }}" class="th-sort-link" title="Sort by {{ $label }} ({{ $nextDir === 'asc' ? 'Ascending' : 'Descending' }})">
        <span>{{ $label }}</span>
        <span class="th-sort-icon">
            @if ($isActive && strtolower($currentSortDir) === 'asc')
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="18 15 12 9 6 15" />
                </svg>
            @elseif ($isActive && strtolower($currentSortDir) === 'desc')
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="6 9 12 15 18 9" />
                </svg>
            @else
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" opacity="0.4">
                    <polyline points="7 15 12 20 17 15" />
                    <polyline points="7 9 12 4 17 9" />
                </svg>
            @endif
        </span>
    </a>
</th>
