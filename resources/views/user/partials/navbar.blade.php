@php
   
    $active = $active ?? '';
    $showSearch = $search ?? false;

    $userName = session('user_name', 'Customer');
    $nameParts = preg_split('/\s+/', trim($userName)) ?: [];
    $userInitials = strtoupper(substr($nameParts[0] ?? '', 0, 1) . substr($nameParts[1] ?? '', 0, 1));
    $userInitials = $userInitials !== '' ? $userInitials : 'SK';
@endphp

<!-- Site Header -->
<header class="site-header">
  <div class="header-container">
    <a href="/index" class="brand-logo-area">
      <div class="brand-icon-box">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
          <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z" />
          <path d="M3 6h18" />
          <path d="M16 10a4 4 0 0 1-8 0" />
        </svg>
      </div>
      <span class="brand-title">Shopkart</span>
    </a>

    @if ($showSearch)
      <form class="header-search" method="GET" action="{{ route('shop.index') }}" role="search">
        <svg class="search-field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="8" />
          <path d="m21 21-4.3-4.3" />
        </svg>
        <input type="text" id="liveSearchInput" name="search" value="{{ $searchTerm ?? '' }}"
          class="search-input-field" placeholder="Search products by name..." aria-label="Search catalog">
      </form>
    @endif

    <nav class="header-nav-actions" aria-label="Customer navigation">
      <a href="/index" class="nav-btn {{ $active === 'home' ? 'active' : '' }}">Store Catalog</a>
      <a href="/myorders" class="nav-btn {{ $active === 'orders' ? 'active' : '' }}">My Orders</a>
@php
    $cartCount = 0;

    if (session('user_id')) {
        $cartCount = \App\Models\cart_items::where(
            'user_id',
            session('user_id')
        )->sum('quantity');
    }
@endphp

<a href="{{ route('cart.index') }}"
   class="cart-btn-link"
   aria-label="Shopping Cart">

    <svg width="18" height="18"
         viewBox="0 0 24 24"
         fill="none"
         stroke="currentColor"
         stroke-width="2.2">

        <circle cx="8" cy="21" r="1" />
        <circle cx="19" cy="21" r="1" />

        <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12" />
    </svg>

    <span>Cart</span>

    <span class="cart-count-badge" id="cartBadgeCount">
        {{ $cartCount }}
    </span>

</a>

      <a href="/profile" class="user-profile-pill {{ $active === 'profile' ? 'active' : '' }}" title="View Profile">
        <div class="avatar-circle" id="navAvatar">{{ $userInitials }}</div>
        <strong id="navUserName">{{ $userName }}</strong>
      </a>

      <form method="POST" action="/logout" class="logout-form">
        @csrf
        <button type="submit" class="logout-btn" title="Log out" aria-label="Log out">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
            <polyline points="16 17 21 12 16 7" />
            <line x1="21" x2="9" y1="12" y2="12" />
          </svg>
        </button>
      </form>
    </nav>
  </div>
</header>
