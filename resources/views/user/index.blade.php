<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Shopkart &bull; Online Store &bull; Quality Tech &amp; Daily Essentials</title>
  <!-- Google Fonts: Plus Jakarta Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/index.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/user-navbar.css') }}">
</head>
<body>

  @include('user.partials.navbar', ['active' => 'home', 'search' => true, 'searchTerm' => $search])

  <!-- Main Storefront Page -->
  <main class="main-content">
    <!-- Hero Banner -->
    <section class="hero-banner" aria-label="Featured deals banner">
      <div class="hero-content">
        <span class="hero-chip">Special Launch Offer</span>
        <h1 class="hero-title">Quality Tech &amp; Daily Essentials</h1>
        <p class="hero-desc">
          Browse {{ $totalProducts }} {{ \Illuminate\Support\Str::plural('product', $totalProducts) }}
          across {{ $categories->count() }} {{ \Illuminate\Support\Str::plural('category', $categories->count()) }}.
          Delivered fast to your doorstep.
        </p>
        <div class="hero-actions">
          @forelse ($categories->take(2) as $index => $category)
            <a href="{{ route('shop.index', ['category' => $category->slug]) }}"
              class="hero-btn-{{ $index === 0 ? 'primary' : 'secondary' }}">
              Shop {{ $category->name }}
            </a>
          @empty
            <a href="#catalogSection" class="hero-btn-primary">Explore Catalog</a>
          @endforelse
        </div>
      </div>

      @if ($featured)
        <div class="hero-highlight-box">
          <div class="highlight-stat-label">Featured Product</div>
          <div class="highlight-stat-val">{{ $featured['name'] }}</div>
          <div style="font-size: 13px; color: #E6F4F1;">{{ $featured['variant_summary'] }}</div>
          <div style="margin-top: 6px;">
            <a href="{{ route('shop.product', $featured['slug']) }}"
              style="color: #14B8A6; font-weight: 700; font-size: 13px; text-decoration: none;">
              @if ($featured['from_price'] !== null)
                Buy from &#8377;{{ number_format($featured['from_price'], 0) }} &rarr;
              @else
                View product &rarr;
              @endif
            </a>
          </div>
        </div>
      @endif
    </section>

    <!-- Catalog Toolbar & Filters -->
    <section class="catalog-toolbar" id="catalogSection">
      <div>
        <h2 class="section-title">Explore Store Catalog</h2>
        <div style="font-size: 13px; color: var(--muted);" id="catalogResultCount">
          @if ($search !== '')
            Showing {{ count($products) }} of {{ $totalProducts }} products for &ldquo;{{ $search }}&rdquo;
          @else
            Showing {{ count($products) }} of {{ $totalProducts }} products
          @endif
        </div>
      </div>

      <div class="filter-pills-wrap">
        <a href="{{ route('shop.index', array_filter(['search' => $search !== '' ? $search : null])) }}"
          class="filter-pill {{ $categorySlug === '' ? 'active' : '' }}">
          All Products ({{ $totalProducts }})
        </a>
        @foreach ($categories as $category)
          <a href="{{ route('shop.index', array_filter(['category' => $category->slug, 'search' => $search !== '' ? $search : null])) }}"
            class="filter-pill {{ $categorySlug === $category->slug ? 'active' : '' }}">
            {{ $category->name }} ({{ $category->products_count }})
          </a>
        @endforeach
      </div>
    </section>

    <!-- Product Cards Grid -->
    <section class="products-grid" id="productGrid" aria-label="Product listings">
      @forelse ($products as $product)
        <article class="product-card">
          <div class="card-image-wrap">
            <span class="card-stock-tag">
              <span class="badge badge-{{ $product['stock_state'] }}"><span class="badge-dot"></span> {{ $product['stock_label'] }}</span>
            </span>
            @if ($product['image'])
              <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}">
            @else
              <div style="display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; color: var(--muted);">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                  <rect width="14" height="20" x="5" y="2" rx="2"/>
                  <path d="M12 18h.01"/>
                </svg>
              </div>
            @endif
          </div>
          <div class="card-body">
            <span class="product-category-text">{{ $product['category'] }}</span>
            <h3 style="margin: 0;">
              <a href="{{ route('shop.product', $product['slug']) }}" class="product-name-link">{{ $product['name'] }}</a>
            </h3>
            <span class="variant-hint-pill">{{ $product['variant_summary'] }}</span>
            <div class="card-price-row">
              <div class="price-box">
                <span class="price-label">From</span>
                <span class="price-val">
                  @if ($product['from_price'] !== null)
                    &#8377;{{ number_format($product['from_price'], 0) }}
                  @else
                    &mdash;
                  @endif
                </span>
              </div>
              @if ($product['stock_state'] === 'out_of_stock')
                <span class="btn-view-product disabled" aria-disabled="true">Out of stock</span>
              @else
                <a href="{{ route('shop.product', $product['slug']) }}" class="btn-view-product">
                  View
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M5 12h14M12 5l7 7-7 7"/>
                  </svg>
                </a>
              @endif
            </div>
          </div>
        </article>
      @empty
        <div class="catalog-empty">
          <strong>No products found.</strong>
          <span>Try a different category or search term.</span>
          <a href="{{ route('shop.index') }}" class="btn-view-product">Reset filters</a>
        </div>
      @endforelse
    </section>
  </main>

  <!-- Footer -->
  <footer class="site-footer">
    <div class="footer-inner">
      <div class="footer-grid">
        <div>
          <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
            <div class="brand-icon-box" style="width: 28px; height: 28px;">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/>
              </svg>
            </div>
            <span style="font-weight: 800; font-size: 16px;">Shopkart</span>
          </div>
          <p style="font-size: 13px; color: var(--muted); line-height: 1.6; max-width: 320px;">
            Direct-to-consumer store delivering genuine electronics and daily essentials with fast doorstep delivery.
          </p>
        </div>

        <div>
          <h4 class="footer-col-title">Categories</h4>
          <ul class="footer-links">
            @forelse ($categories->take(3) as $category)
              <li><a href="{{ route('shop.index', ['category' => $category->slug]) }}">{{ $category->name }}</a></li>
            @empty
              <li><a href="{{ route('shop.index') }}">All Products</a></li>
            @endforelse
          </ul>
        </div>

        <div>
          <h4 class="footer-col-title">Account</h4>
          <ul class="footer-links">
            <li><a href="/myorders">Track My Orders</a></li>
            <li><a href="/checkout">Shopping Cart</a></li>
            <li><a href="/profile">My Profile</a></li>
          </ul>
        </div>

        <div>
          <h4 class="footer-col-title">Help &amp; Support</h4>
          <ul class="footer-links">
            <li><a href="/myorders">Shipping &amp; Delivery</a></li>
            <li><a href="mailto:support@shopkart.com">support@shopkart.com</a></li>
          </ul>
        </div>
      </div>
    </div>
  </footer>

</body>
</html>
