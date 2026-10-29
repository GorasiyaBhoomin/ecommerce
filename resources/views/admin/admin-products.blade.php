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
    <!-- Left Sidebar -->
    <aside class="admin-sidebar" id="adminSidebar">
      <div class="brand-area">
        <div class="brand-logo-icon">
          <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
            <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z" />
            <path d="M3 6h18" />
            <path d="M16 10a4 4 0 0 1-8 0" />
          </svg>
        </div>
        <div>
          <div class="brand-title">Shopkart</div>
          <span class="brand-badge">Admin Suite</span>
        </div>
      </div>

      <div class="nav-label">Management</div>
      <ul class="sidebar-menu">
        <li>
          <a href="/adminorders" class="menu-link">
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect width="18" height="18" x="3" y="3" rx="2" />
              <path d="M9 3v18" />
              <path d="m14 9 3 3-3 3" />
            </svg>
            Orders
          </a>
        </li>
        <li>
          <a href="/adminproducts" class="menu-link active">
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="m7.5 4.27 9 5.15" />
              <path
                d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z" />
              <path d="m3.3 7 8.7 5 8.7-5" />
              <path d="M12 22V12" />
            </svg>
            Products &amp; Stock
          </a>
        </li>
        <li>
          <a href="/adminaddproduct" class="menu-link">
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10" />
              <path d="M8 12h8" />
              <path d="M12 8v8" />
            </svg>
            Add Product
          </a>
        </li>
        <li>
          <a href="/adminimportproducts" class="menu-link">
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
              <polyline points="7 10 12 15 17 10" />
              <line x1="12" y1="15" x2="12" y2="3" />
            </svg>
            Bulk Import
          </a>
        </li>
        <li>
          <a href="/admincategories" class="menu-link">
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2Z" />
            </svg>
            Categories
          </a>
        </li>
        <li>
          <a href="/adminvariants" class="menu-link">
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="4" y1="21" x2="4" y2="14" />
              <line x1="4" y1="10" x2="4" y2="3" />
              <line x1="12" y1="21" x2="12" y2="12" />
              <line x1="12" y1="8" x2="12" y2="3" />
              <line x1="20" y1="21" x2="20" y2="16" />
              <line x1="20" y1="12" x2="20" y2="3" />
              <line x1="1" y1="14" x2="7" y2="14" />
              <line x1="9" y1="8" x2="15" y2="8" />
              <line x1="17" y1="16" x2="23" y2="16" />
            </svg>
            Variants
          </a>
        </li>
        <li>
          <a href="/adminusers" class="menu-link">
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
              <circle cx="9" cy="7" r="4" />
              <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
              <path d="M16 3.13a4 4 0 0 1 0 7.75" />
            </svg>
            Users
          </a>
        </li>
        <li>
          <a href="/adminprofile" class="menu-link">
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect width="18" height="18" x="3" y="3" rx="2" />
              <path d="M9 3v18" />
              <path d="m14 9 3 3-3 3" />
            </svg>
            Profile
          </a>
        </li>
      </ul>

      <div class="sidebar-footer">
       
      </div>
    </aside>

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
            <h1 class="page-heading">Products &amp; Stock</h1>
            <div style="font-size: 13px; color: var(--muted);">{{ $categories->count() }} Categories &bull;
              {{ $products->count() }} Products</div>
          </div>
        </div>

        <div class="header-actions">
          <div class="search-box-wrap">
            <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="8" />
              <path d="m21 21-4.3-4.3" />
            </svg>
            <input type="text" id="productSearchInput" class="search-input" placeholder="Search product or variant..."
              aria-label="Search products">
          </div>

          <a href="/admincategories" class="btn-secondary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M4 6h16M4 12h16M4 18h7" />
            </svg>
            Manage Categories
          </a>

          <a href="{{ route('admin.products.import', $selectedCategory ? ['category_id' => $selectedCategory->id] : []) }}" class="btn-secondary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
              <polyline points="7 10 12 15 17 10" />
              <line x1="12" y1="15" x2="12" y2="3" />
            </svg>
            Bulk Import CSV
          </a>

          <a href="/adminaddproduct" class="btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="12" x2="12" y1="5" y2="19" />
              <line x1="5" x2="19" y1="12" y2="12" />
            </svg>
            Add Product
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
            <a href="{{ route('admin.products.index') }}"
              class="filter-pill {{ $selectedCategory ? '' : 'active' }}">All Categories
              ({{ $categories->sum('products_count') }})</a>
            @foreach ($categories as $category)
              <a href="{{ route('admin.products.index', ['category' => $category->slug]) }}"
                class="filter-pill {{ $selectedCategory?->id === $category->id ? 'active' : '' }}">{{ $category->name }}
                ({{ $category->products_count }})</a>
            @endforeach
          </div>
        </div>

        <section class="category-section-card">
          <div class="category-card-header">
            <div class="category-title-wrap">
              <h2 class="category-title">{{ $selectedCategory?->name ?? 'All Products' }}</h2>
              <span class="category-badge-pill">{{ $products->count() }} Products &bull;
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
              <tbody>
                @forelse ($products as $product)
                <tr>
                  <td>
                    <div class="product-meta-cell">
                      @php
                        $firstImg = $product->images->first();
                      @endphp
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
                  <td colspan="6" class="text-muted">No products have been added yet.</td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </section>

       
      </div>
    </main>
  </div>

  <script>
    function confirmDelete(name) {
      return confirm('Are you sure you want to delete "' + name + '"? This will permanently remove the product and its variants.');
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
