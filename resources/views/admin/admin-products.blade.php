<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Products &amp; Stock Management | Shopkart Admin</title>
  <!-- Google Fonts: Plus Jakarta Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
    rel="stylesheet">
  <link href="{{ asset('assets/css/adminproducts.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/css/admin-notifications.css') }}" rel="stylesheet">
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
        <!-- Category Filter Pills -->
        <div class="toolbar-bar">
          <div class="filter-pills-wrap">
            <a href="{{ route('admin.products.index', array_filter(['search' => request('search'), 'stock' => request('stock')])) }}"
              class="filter-pill {{ $selectedCategory ? '' : 'active' }}">All Categories
              ({{ $categories->sum('products_count') }})</a>
            @foreach ($categories as $category)
              <a href="{{ route('admin.products.index', array_filter(['category' => $category->slug, 'search' => request('search'), 'stock' => request('stock')])) }}"
                class="filter-pill {{ $selectedCategory?->id === $category->id ? 'active' : '' }}">{{ $category->name }}
                ({{ $category->products_count }})</a>
            @endforeach
          </div>
        </div>

        <!-- Stock Status Filter Pills -->
        <div class="toolbar-bar" style="margin-top: 10px;">
          <div class="filter-pills-wrap">
            <a href="{{ route('admin.products.index', array_filter(['category' => request('category'), 'search' => request('search')])) }}"
              class="filter-pill {{ !request('stock') ? 'active' : '' }}">All Stock</a>
            <a href="{{ route('admin.products.index', array_filter(['category' => request('category'), 'search' => request('search'), 'stock' => 'in_stock'])) }}"
              class="filter-pill {{ request('stock') === 'in_stock' ? 'active' : '' }}">In Stock</a>
            <a href="{{ route('admin.products.index', array_filter(['category' => request('category'), 'search' => request('search'), 'stock' => 'low_stock'])) }}"
              class="filter-pill {{ request('stock') === 'low_stock' ? 'active' : '' }}">Low Stock (&le; 10)</a>
            <a href="{{ route('admin.products.index', array_filter(['category' => request('category'), 'search' => request('search'), 'stock' => 'out_of_stock'])) }}"
              class="filter-pill {{ request('stock') === 'out_of_stock' ? 'active' : '' }}">Out of Stock</a>
          </div>
        </div>

        <section class="category-section-card">
          <div class="category-card-header">
            <div class="category-title-wrap">
              <h2 class="category-title">{{ $selectedCategory?->name ?? 'All Products' }}</h2>
              <span class="category-badge-pill" id="productCountPill">{{ $products->total() }} Products &bull;
                {{ $products->sum(fn ($product) => $product->variants->count()) }} Variants</span>
            </div>
          </div>
          <div class="table-responsive">
            <table class="products-table">
              <thead>
                <tr>
                  <th>Product</th>
                  <th>Variants</th>
                  <th>Price Range</th>
                  <th>Total Stock</th>
                  <th>Status</th>
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
                  </td>
                  <td>
                    @if($product->total_stock > 10)
                      <span class="badge badge-in_stock"><span class="badge-dot"></span>In Stock</span>
                    @elseif($product->total_stock > 0)
                      <span class="badge badge-low_stock"><span class="badge-dot"></span>Low Stock</span>
                    @else
                      <span class="badge badge-out_of_stock"><span class="badge-dot"></span>Out of Stock</span>
                    @endif
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
                        onsubmit="return confirmDelete('{{ addslashes($product->name) }}')" style="margin: 0; display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="delete-btn">
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
                <tr>
                  <td colspan="6" class="text-muted" style="text-align: center; padding: 30px;">No products found.</td>
                </tr>
                @endforelse
                <tr id="noProductMatchRow" style="display: none;">
                  <td colspan="6" class="text-muted" style="text-align: center; padding: 30px;">No products match your search.</td>
                </tr>
              </tbody>
            </table>
          </div>

          @if ($products->hasPages())
            <div style="padding: 16px 20px; border-top: 1px solid #E2E8F0;">
              {{ $products->links() }}
            </div>
          @endif
        </section>

       
      </div>
    </main>
  </div>

  <script>
    function confirmDelete(name) {
      return confirm('Are you sure you want to delete "' + name + '"? This will permanently remove the product and its variants.');
    }

    // Live search for products
    const productSearchInput = document.getElementById('productSearchInput');
    if (productSearchInput) {
      productSearchInput.addEventListener('input', function () {
        const term = this.value.trim().toLowerCase();
        const rows = document.querySelectorAll('#productTableBody tr[data-search]');
        let visible = 0;
        rows.forEach(row => {
          const match = !term || (row.dataset.search || '').includes(term);
          row.style.display = match ? '' : 'none';
          if (match) visible++;
        });

        const emptyRow = document.getElementById('noProductMatchRow');
        if (emptyRow) {
          emptyRow.style.display = (visible === 0 && rows.length > 0) ? '' : 'none';
        }

        const countPill = document.getElementById('productCountPill');
        if (countPill && term) {
          countPill.textContent = `${visible} Products shown`;
        }
      });
    }

    // Mobile sidebar drawer (layout only)
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
