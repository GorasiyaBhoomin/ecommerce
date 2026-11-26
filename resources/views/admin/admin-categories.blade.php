<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Categories | Shopkart Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
    rel="stylesheet">
  <link href="{{ asset('assets/css/adminproducts.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/css/admin-notifications.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/css/admin-tables.css') }}" rel="stylesheet">
</head>

<body>

  <div class="admin-container">
    @include('admin.partials.admin-sidebar', ['active' => 'categories'])

    <!-- Main Area -->
    <main class="admin-main">
      <header class="top-header">
        <div class="header-title-wrap">
          <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle navigation menu">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="4" x2="20" y1="12" y2="12" />
              <line x1="4" x2="20" y1="6" y2="6" />
              <line x1="4" x2="20" y1="18" y2="18" />
            </svg>
          </button>
          <div>
            <div class="breadcrumb">
              <a href="/adminproducts">{{ __('Catalog') }}</a>
              <span>/</span>
              <span>{{ __('Categories') }}</span>
            </div>
            <h1 class="page-heading">{{ __('Categories') }}</h1>
            <div style="font-size: 13px; color: var(--muted);">{{ __('Organise products into storefront categories') }}
            </div>
          </div>
        </div>

        <div class="header-actions">
          <div class="search-box-wrap" style="position: relative; display: flex; align-items: center;">
            <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="8" />
              <path d="m21 21-4.3-4.3" />
            </svg>
            <input type="text" id="categorySearchInput" class="search-input"
              placeholder="{{ __('Search category...') }}" aria-label="{{ __('Search categories') }}"
              value="{{ request('search') ?? request('q') }}" oninput="filterCategories(this.value)" autocomplete="off">
            <button type="button" id="clearCatSearchBtn"
              style="display: {{ (request('search') || request('q')) ? 'block' : 'none' }}; position: absolute; right: 12px; background: none; border: none; color: var(--muted); cursor: pointer; font-size: 14px;"
              onclick="clearCategorySearch()" title="Clear search">✕</button>
          </div>

          <a href="/adminaddcategory" class="btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="12" x2="12" y1="5" y2="19" />
              <line x1="5" x2="19" y1="12" y2="12" />
            </svg>
            {{ __('Add Category') }}
          </a>

          @include('admin.partials.notification-bar')
        </div>
      </header>

      <div class="content-inner">
        @if (session('success'))
          <div
            style="background: #ECFDF5; border: 1px solid #A7F3D0; color: #065F46; border-radius: 10px; padding: 12px 16px; margin-bottom: 20px; font-size: 13px; font-weight: 600;">
            {{ session('success') }}
          </div>
        @endif

        <!-- Category Toolbar (Search, Parent Filter, Status Filter, Reset) -->
        <div class="table-toolbar-card">
          <form method="GET" action="{{ url('/admincategories') }}" class="table-toolbar-form"
            id="categoriesFilterForm">
            @if (request('sort_by'))
              <input type="hidden" name="sort_by" value="{{ request('sort_by') }}">
            @endif
            @if (request('sort_dir'))
              <input type="hidden" name="sort_dir" value="{{ request('sort_dir') }}">
            @endif

            <div class="table-toolbar-left">
              <div class="table-search-box">
                <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="11" cy="11" r="8" />
                  <path d="m21 21-4.3-4.3" />
                </svg>
                <input type="text" name="search" class="search-input" placeholder="{{ __('Search category...') }}"
                  value="{{ request('search') ?? request('q') }}" autocomplete="off"
                  aria-label="{{ __('Search categories') }}">
                <button type="button" class="search-clear-btn"
                  style="{{ (request('search') || request('q')) ? 'display:flex;' : 'display:none;' }}"
                  onclick="clearTableSearch(this)" title="Clear search">✕</button>
              </div>

              <!-- Parent Category Filter -->
              <select name="parent_id" class="table-filter-select {{ request('parent_id') ? 'has-value' : '' }}"
                aria-label="Filter by parent category">
                <option value="">{{ __('All Levels') }}</option>
                <option value="root" {{ request('parent_id') === 'root' ? 'selected' : '' }}>{{ __('Top Level Only') }}
                </option>
                @foreach ($parentCategories as $pCat)
                  <option value="{{ $pCat->id }}" {{ request('parent_id') == $pCat->id ? 'selected' : '' }}>
                    {{ $pCat->name }}
                  </option>
                @endforeach
              </select>

              <!-- Status Filter -->
              <select name="status" class="table-filter-select {{ request('status') ? 'has-value' : '' }}"
                aria-label="Filter by status">
                <option value="">{{ __('All Statuses') }}</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                <option value="hidden" {{ request('status') === 'hidden' ? 'selected' : '' }}>{{ __('Hidden') }}</option>
              </select>
            </div>

            <div class="table-toolbar-right">
              <button type="button" class="btn-bulk-delete" style="display: none;"
                onclick="executeBulkDelete('categories', '{{ route('admin.categories.bulk-delete') }}')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="3 6 5 6 21 6" />
                  <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                </svg>
                {{ __('Delete Selected') }} (<span class="selected-count">0</span>)
              </button>

              @if (request('search') || request('q') || request('parent_id') || request('status') || request('sort_by'))
                <a href="{{ url('/admincategories') }}" class="btn-table-reset" title="Reset all filters and search">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="1 4 1 10 7 10" />
                    <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10" />
                  </svg>
                  Reset Filters
                </a>
              @endif
            </div>
          </form>
        </div>

        <section class="category-section-card" id="categoryTableCard">
          <div class="category-card-header">
            <div class="category-title-wrap">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2Z" />
              </svg>
              <h2 class="category-title">{{ __('All Categories') }}</h2>
              <span class="category-badge-pill" id="categoryCountPill">{{ $categories->total() }}
                {{ __('Categories') }}</span>
            </div>
          </div>

          <div class="table-responsive">
            <table class="products-table">
              <thead>
                <tr>
                  <th style="width: 44px; text-align: center;">
                    <input type="checkbox" class="form-check-input select-all-checkbox"
                      aria-label="{{ __('Select all categories') }}">
                  </th>
                  @include('admin.partials.sort-th', ['column' => 'name', 'label' => __('Category')])
                  @include('admin.partials.sort-th', ['column' => 'slug', 'label' => __('Slug')])
                  <th>{{ __('Parent') }}</th>
                  @include('admin.partials.sort-th', ['column' => 'display_order', 'label' => __('Order')])
                  @include('admin.partials.sort-th', ['column' => 'is_active', 'label' => __('Status')])
                  <th style="text-align: right;">{{ __('Actions') }}</th>
                </tr>
              </thead>
              <tbody id="categoryTableBody">
                @forelse ($categories as $category)
                  @php
                    $parentName = $category->parent->name ?? null;
                    $searchText = strtolower(trim(
                      $category->name . ' ' . $category->slug . ' ' . ($parentName ?? '') . ' ' . ($category->description ?? '')
                    ));
                  @endphp
                  <tr data-search="{{ $searchText }}">
                    <td style="text-align: center;">
                      <input type="checkbox" class="form-check-input row-checkbox" value="{{ $category->id }}"
                        aria-label="{{ __('Select category') }}">
                    </td>
                    <td>
                      <div class="product-meta-cell">
                        <div class="product-icon-wrap">
                          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2Z" />
                          </svg>
                        </div>
                        <div>
                          <div class="product-name">{{ $category->name }}</div>
                          @if ($category->description)
                            <div class="product-subtitle">{{ \Illuminate\Support\Str::limit($category->description, 42) }}
                            </div>
                          @endif
                        </div>
                      </div>
                    </td>
                    <td><code
                        style="font-size: 11px; background: #F1F5F9; padding: 2px 6px; border-radius: 4px;">{{ $category->slug }}</code>
                    </td>
                    <td>{{ $parentName ?? '—' }}</td>
                    <td><span class="stock-count-val">{{ $category->display_order }}</span></td>
                    <td>
                      <div class="status-toggle-wrap">
                        <label class="form-check form-switch" title="{{ __('Toggle status') }}">
                          <input type="checkbox" class="form-check-input status-toggle-input"
                            data-url="{{ route('admin.categories.toggle', $category->id) }}" data-id="{{ $category->id }}"
                            {{ $category->is_active ? 'checked' : '' }}>
                        </label>
                        <span
                          class="badge {{ $category->is_active ? 'bg-success' : 'bg-secondary' }} status-badge-{{ $category->id }}">
                          {{ $category->is_active ? __('Active') : __('Inactive') }}
                        </span>
                      </div>
                    </td>
                    <td>
                      <div class="table-actions">
                        <a href="/admineditcategory/{{ $category->id }}" class="edit-btn"
                          title="{{ __('Edit Category') }}">
                          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M12 20h9" />
                            <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z" />
                          </svg>
                          <span>{{ __('Edit') }}</span>
                        </a>
                        <form method="POST" action="/admincategories/{{ $category->id }}"
                          onsubmit="return confirmRecordDelete('{{ addslashes($category->name) }}', 'category')"
                          style="margin: 0;">
                          @csrf
                          @method('DELETE')
                          <button type="button" class="delete-btn ajax-delete"
                            data-url="/admincategories/{{ $category->id }}"
                            data-id="{{ $category->id }}"
                            data-confirm="Delete the &quot;{{ addslashes($category->name) }}&quot; category?"
                            data-table="#categoryTableCard"
                            title="{{ __('Delete Category') }}">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                              stroke-width="2">
                              <path d="M3 6h18" />
                              <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                            </svg>
                            <span>{{ __('Delete') }}</span>
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>
                @empty
                  @include('admin.partials.empty-state', [
                    'colspan' => 7,
                    'title' => __('No categories found'),
                    'message' => __('No categories match your search or filter criteria.'),
                    'resetUrl' => url('/admincategories'),
                    'hasFilters' => (bool) (request('search') || request('q') || request('parent_id') || request('status') || request('sort_by')),
                  ])
                @endforelse
              </tbody>
            </table>
          </div>

          @include('admin.partials.table-pagination', [
            'paginator' => $categories,
            'recordName' => 'categories'
          ])
        </section>
      </div>
    </main>
  </div>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="{{ asset('assets/js/admin-tables.js') }}"></script>
  <script src="{{ asset('assets/js/ajax.js') }}"></script>
  <script>
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const adminSidebar = document.getElementById('adminSidebar');
    if (mobileMenuBtn && adminSidebar) {
      mobileMenuBtn.addEventListener('click', function () {
        adminSidebar.classList.toggle('open');
      });
    }

    function confirmDelete(name) {
      return window.confirm(`Delete the "${name}" category? Products will not be deleted.`);
    }
  </script>
</body>

</html>