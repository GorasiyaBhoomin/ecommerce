<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Products &amp; Stock Management | Shopkart Admin</title>
  <!-- Google Fonts: Plus Jakarta Sans -->
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
        @include('admin.partials.admin-sidebar', ['active' => 'products'])

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
            <h1 class="page-heading">{{ __('Products & Stock') }}</h1>
            <div style="font-size: 13px; color: var(--muted);">{{ $categories->count() }} Categories &bull;
              {{ $products->count() }} Products</div>
          </div>
        </div>

        <div class="header-actions">
          <form method="GET" action="{{ route('admin.products.index') }}" class="search-box-wrap" id="productSearchForm" style="display: flex; align-items: center; position: relative;">
            @if (request('category'))
              <input type="hidden" name="category" value="{{ request('category') }}">
            @endif
            @if (request('stock'))
              <input type="hidden" name="stock" value="{{ request('stock') }}">
            @endif
            <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="8" />
              <path d="m21 21-4.3-4.3" />
            </svg>
            <input type="text" id="productSearchInput" name="search" class="search-input" placeholder="Search product, SKU, variant..."
              aria-label="Search products" value="{{ request('search') ?? request('q') }}" autocomplete="off">
            @if (request('search') || request('q'))
              <a href="{{ route('admin.products.index', array_filter(['category' => request('category'), 'stock' => request('stock')])) }}"
                 style="position: absolute; right: 12px; color: var(--muted); text-decoration: none; font-size: 13px; font-weight: bold;" title="Clear search">✕</a>
            @endif
          </form>

          <a href="/admincategories" class="btn-secondary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M4 6h16M4 12h16M4 18h7" />
            </svg>
            {{ __('Categories') }}
          </a>

          <a href="{{ route('admin.products.import', $selectedCategory ? ['category_id' => $selectedCategory->id] : []) }}" class="btn-secondary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
              <polyline points="7 10 12 15 17 10" />
              <line x1="12" y1="15" x2="12" y2="3" />
            </svg>
            {{ __('Bulk Import') }}
          </a>

          <a href="/adminaddproduct" class="btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="12" x2="12" y1="5" y2="19" />
              <line x1="5" x2="19" y1="12" y2="12" />
            </svg>
            {{ __('Add Product') }}
          </a>

          @include('admin.partials.notification-bar')
        </div>
      </header>

      <div class="content-inner">
        @if (session('success') || session('warning') || session('error') || session('import_errors'))
          <div style="margin-bottom: 20px;">
            @if (session('success'))
              <div style="background: #ECFDF5; border: 1px solid #A7F3D0; color: #065F46; border-radius: 10px; padding: 14px 18px; margin-bottom: 12px; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 10px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                  <path d="M20 6 9 17l-5-5" />
                </svg>
                <span>{{ session('success') }}</span>
              </div>
            @endif
            @if (session('warning'))
              <div style="background: #FFFBEB; border: 1px solid #FDE68A; color: #92400E; border-radius: 10px; padding: 14px 18px; margin-bottom: 12px; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 10px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                  <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z" />
                  <line x1="12" y1="9" x2="12" y2="13" />
                  <line x1="12" y1="17" x2="12.01" y2="17" />
                </svg>
                <span>{{ session('warning') }}</span>
              </div>
            @endif
            @if (session('error'))
              <div style="background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; border-radius: 10px; padding: 14px 18px; margin-bottom: 12px; font-size: 13px; font-weight: 600;">
                {{ session('error') }}
              </div>
            @endif
            @if (session('import_errors') && is_array(session('import_errors')))
              <div style="background: #FFF1F2; border: 1px solid #FFE4E6; color: #BE123C; border-radius: 10px; padding: 14px 18px; margin-bottom: 12px; font-size: 12px;">
                <strong style="font-size: 13px; display: block; margin-bottom: 6px;">Row Validation Notes:</strong>
                <ul style="margin: 0; padding-left: 20px; max-height: 160px; overflow-y: auto;">
                  @foreach (session('import_errors') as $err)
                    <li>{{ $err }}</li>
                  @endforeach
                </ul>
              </div>
            @endif
          </div>
        @endif
        <!-- Unified Table Toolbar (Search, Category, Stock, Status, Per-Page, Reset) -->
        <div class="table-toolbar-card">
          <form method="GET" action="{{ route('admin.products.index') }}" class="table-toolbar-form" id="productsFilterForm">
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
                <input type="text" name="search" class="search-input" placeholder="Search product, SKU, variant..."
                  value="{{ request('search') ?? request('q') }}" autocomplete="off" aria-label="Search products">
                <button type="button" class="search-clear-btn" style="{{ (request('search') || request('q')) ? 'display:flex;' : 'display:none;' }}" onclick="clearTableSearch(this)" title="Clear search">✕</button>
              </div>

              <!-- Category Filter Dropdown -->
              <select name="category" class="table-filter-select {{ request('category') ? 'has-value' : '' }}" aria-label="Filter by category">
                <option value="">All Categories ({{ $categories->sum('products_count') }})</option>
                @foreach ($categories as $cat)
                  <option value="{{ $cat->slug }}" {{ request('category') === $cat->slug ? 'selected' : '' }}>
                    {{ $cat->name }} ({{ $cat->products_count }})
                  </option>
                @endforeach
              </select>

              <!-- Stock Filter Dropdown -->
              <select name="stock" class="table-filter-select {{ request('stock') ? 'has-value' : '' }}" aria-label="Filter by stock status">
                <option value="">All Stock Levels</option>
                <option value="in_stock" {{ request('stock') === 'in_stock' ? 'selected' : '' }}>In Stock (> 10)</option>
                <option value="low_stock" {{ request('stock') === 'low_stock' ? 'selected' : '' }}>Low Stock (&le; 10)</option>
                <option value="out_of_stock" {{ request('stock') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
              </select>

              <!-- Active Status Filter Dropdown -->
              <select name="status" class="table-filter-select {{ request('status') ? 'has-value' : '' }}" aria-label="Filter by status">
                <option value="">All Statuses</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
              </select>
            </div>

            <div class="table-toolbar-right">
              <button type="button" class="btn-bulk-delete" style="display: none;" onclick="executeBulkDelete('products', '{{ route('admin.products.bulk-delete') }}')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="3 6 5 6 21 6"/>
                  <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                </svg>
                Delete Selected (<span class="selected-count">0</span>)
              </button>

              @if (request('search') || request('q') || request('category') || request('stock') || request('status') || request('sort_by'))
                <a href="{{ route('admin.products.index') }}" class="btn-table-reset" title="Reset all filters and search">
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

        <section class="category-section-card" id="productTableCard">
          <div class="category-card-header">
            <div class="category-title-wrap">
              <h2 class="category-title">{{ $selectedCategory?->name ?? 'All Products' }}</h2>
              <span class="category-badge-pill" id="productCountPill">
                {{ $products->total() }} Products
                @if ($products->total() > 0)
                  &bull; Showing {{ $products->firstItem() }}-{{ $products->lastItem() }}
                @endif
              </span>
            </div>
          </div>
          <div class="table-responsive">
            <table class="products-table">
              <thead>
                <tr>
                  <th style="width: 44px; text-align: center;">
                    <input type="checkbox" class="form-check-input select-all-checkbox" aria-label="Select all products">
                  </th>
                  @include('admin.partials.sort-th', ['column' => 'name', 'label' => 'Product'])
                  @include('admin.partials.sort-th', ['column' => 'sku', 'label' => 'Variants / SKU'])
                  <th>Price Range</th>
                  <th>Total Stock</th>
                  @include('admin.partials.sort-th', ['column' => 'is_active', 'label' => 'Status'])
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="productTableBody">
                @forelse ($products as $product)
                @php
                  $firstImg = $product->images->first();
                  $stockState = $product->total_stock > 10 ? 'in_stock' : ($product->total_stock > 0 ? 'low_stock' : 'out_of_stock');
                  $variantSkus = $product->variants->pluck('sku')->implode(' ');
                  $searchText = strtolower(trim($product->name.' '.$product->sku.' '.($product->category->name ?? '').' '.$variantSkus));
                @endphp
                <tr data-search="{{ $searchText }}" data-stock="{{ $stockState }}">
                  <td style="text-align: center;">
                    <input type="checkbox" class="form-check-input row-checkbox" value="{{ $product->id }}" aria-label="Select product">
                  </td>
                  <td>
                    <div class="product-meta-cell">
                      @if ($firstImg)
                        <div class="product-icon-wrap" style="overflow: hidden; padding: 0; background: #f1f5f9;">
                          <img src="{{ \Illuminate\Support\Str::startsWith($firstImg->path, ['http://', 'https://']) ? $firstImg->path : asset($firstImg->path) }}"
                               alt="{{ $product->name }}"
                               style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
                        </div>
                      @else
                        <div class="product-icon-wrap">
                          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8">
                            <rect width="14" height="20" x="5" y="2" rx="2" />
                            <path d="M12 18h.01" />
                          </svg>
                        </div>
                      @endif
                      <div>
                        <div class="product-name">{{$product->name}}</div>
                        <div class="product-subtitle">SKU: {{$product->sku}}</div>
                      </div>
                    </div>
                  </td>
                  <td>
                    <div class="variant-tags-container">
                      @forelse ($product->variants as $variant)
                        <span class="variant-summary-tag">{{$variant->sku}}</span>
                      @empty
                        <span class="text-muted">No variants</span>
                      @endforelse
                    </div>
                  </td>
                  <td>
                    <span class="price-range-val">{{ $product->price_range }}</span>
                  </td>
                  <td>
                    <span class="stock-count-val">{{ $product->total_stock }}</span>
                    <div style="margin-top: 4px;">
                      @if($product->total_stock > 10)
                        <span class="badge bg-success" style="font-size: 10px; padding: 2px 6px;">In Stock</span>
                      @elseif($product->total_stock > 0)
                        <span class="badge bg-warning text-dark" style="font-size: 10px; padding: 2px 6px;">Low Stock</span>
                      @else
                        <span class="badge bg-danger" style="font-size: 10px; padding: 2px 6px;">Out of Stock</span>
                      @endif
                    </div>
                  </td>
                  <td>
                    <div class="status-toggle-wrap">
                      <label class="form-check form-switch" title="Toggle status">
                        <input type="checkbox" class="form-check-input status-toggle-input"
                          data-url="{{ route('admin.products.toggle', $product->id) }}"
                          data-id="{{ $product->id }}"
                          {{ $product->is_active ? 'checked' : '' }}>
                      </label>
                      <span class="badge {{ $product->is_active ? 'bg-success' : 'bg-secondary' }} status-badge-{{ $product->id }}">
                        {{ $product->is_active ? 'Active' : 'Inactive' }}
                      </span>
                    </div>
                  </td>
                  <td>
                    <div class="table-actions">
                      <a href="{{ route('admin.edit.product', $product) }}" class="edit-btn">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                          stroke-width="2">
                          <path d="M12 20h9" />
                          <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z" />
                        </svg>
                        Edit
                      </a>
                      <form method="POST" action="/adminproducts/{{ $product->id }}"
                        onsubmit="return confirmRecordDelete('{{ addslashes($product->name) }}', 'product')" style="margin: 0; display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="delete-btn ajax-delete"
                          data-url="/adminproducts/{{ $product->id }}"
                          data-id="{{ $product->id }}"
                          data-confirm="Are you sure you want to delete &quot;{{ addslashes($product->name) }}&quot;? This will permanently remove the product and its variants."
                          data-table="#productTableCard">
                          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M3 6h18" />
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                          </svg>
                          Delete
                        </button>
                      </form>
                    </div>
                  </td>
                </tr>
                @empty
                  @include('admin.partials.empty-state', [
                    'colspan' => 7,
                    'title' => 'No products found',
                    'message' => 'No products match your search or filter criteria. Try adjusting your filters or search terms.',
                    'resetUrl' => route('admin.products.index'),
                    'hasFilters' => (bool) (request('search') || request('q') || request('category') || request('stock') || request('status') || request('sort_by')),
                  ])
                @endforelse
              </tbody>
            </table>
          </div>

          @include('admin.partials.table-pagination', [
            'paginator' => $products,
            'recordName' => 'products'
          ])
        </section>
      </div>
    </main>
  </div>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="{{ asset('assets/js/admin-tables.js') }}"></script>
  <script src="{{ asset('assets/js/ajax.js') }}"></script>
  <script>
    function confirmDelete(name) {
      return confirm('Are you sure you want to delete "' + name + '"? This will permanently remove the product and its variants.');
    }

    // Mobile sidebar drawer
    (function () {
      var menuBtn = document.getElementById('mobileMenuBtn');
      var sidebar = document.getElementById('adminSidebar');
      if (menuBtn && sidebar) {
        menuBtn.addEventListener('click', function () {
          sidebar.classList.toggle('open');
        });
      }
    })();
  </script>
</body>

</html>
