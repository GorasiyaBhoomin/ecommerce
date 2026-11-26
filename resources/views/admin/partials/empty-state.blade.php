@props([
    'colspan' => 6,
    'title' => 'No records found',
    'message' => 'No records match your current search and filter criteria.',
    'resetUrl' => url()->current(),
    'hasFilters' => false,
])

<tr class="table-empty-row">
    <td colspan="{{ $colspan }}">
        <div class="table-empty-state">
            <div class="table-empty-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="11" cy="11" r="8" />
                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                    <line x1="8" y1="11" x2="14" y2="11" />
                </svg>
            </div>
            <h4 class="table-empty-title">{{ $title }}</h4>
            <p class="table-empty-desc">{{ $message }}</p>
            @if ($hasFilters)
                <div style="margin-top: 6px;">
                    <a href="{{ $resetUrl }}" class="btn-table-reset">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="1 4 1 10 7 10" />
                            <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10" />
                        </svg>
                        Reset All Filters
                    </a>
                </div>
            @endif
        </div>
    </td>
</tr>
