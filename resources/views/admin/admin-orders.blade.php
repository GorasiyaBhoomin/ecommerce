<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Orders | Shopkart Management</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
    rel="stylesheet">
  <link href="{{ asset('assets/css/adminorders.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/css/admin-notifications.css') }}" rel="stylesheet">
</head>

<body>

  <div class="admin-container">
    <!-- Left Dark Sidebar -->
    <aside class="admin-sidebar" id="adminSidebar">
      <div class="brand-area">
        <div class="brand-logo-icon">
          <!-- SVG Shop Icon -->
          <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
            stroke-linecap="round" stroke-linejoin="round">
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
          <a href="/adminorders" class="menu-link active">
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round">
              <rect width="18" height="18" x="3" y="3" rx="2" />
              <path d="M9 3v18" />
              <path d="m14 9 3 3-3 3" />
            </svg>
            Orders
          </a>
        </li>
        <li>
          <a href="/adminproducts" class="menu-link">
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round">
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
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10" />
              <path d="M8 12h8" />
              <path d="M12 8v8" />
            </svg>
            Add Product
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

    
    </aside>

    <!-- Main Content -->
    <main class="admin-main">
      <!-- Top Header -->
      <header class="top-header">
        <div class="header-title-wrap">
          <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle navigation menu">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="4" x2="20" y1="12" y2="12" />
              <line x1="4" x2="20" y1="6" y2="6" />
              <line x1="4" x2="20" y1="18" y2="18" />
            </svg>
          </button>
          <h1 class="page-heading">Orders Management</h1>
        </div>

        <div class="header-actions">
          <div class="search-box-wrap">
            <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="8" />
              <path d="m21 21-4.3-4.3" />
            </svg>
            <input type="text" id="orderSearchInput" class="search-input"
              placeholder="Search Order ID, customer, item..." aria-label="Search orders">
          </div>
          @include('admin.partials.notification-bar')
        </div>
      </header>

      <div class="content-inner">
        <!-- 5 Status Count Cards -->
    
<section class="status-cards-grid"
         aria-label="Order summary by status">

    @php
        $cards = [
            'received' => ['title' => 'Received', 'class' => 'card-received'],
            'process' => ['title' => 'Process', 'class' => 'card-process'],
            'shipping' => ['title' => 'Shipping', 'class' => 'card-shipping'],
            'out_for_delivery' => ['title' => 'Out for delivery', 'class' => 'card-out'],
            'delivered' => ['title' => 'Delivered', 'class' => 'card-delivered'],
        ];
    @endphp

    @foreach($cards as $status => $card)

        <div class="status-card {{ $card['class'] }}"
             onclick="filterByStatus('{{ $status }}')"
             role="button"
             tabindex="0">

            <div class="status-card-header">
                <span class="status-card-title">
                    {{ $card['title'] }}
                </span>
            </div>

            <div class="status-card-count">
                {{ $summary[$status]['count'] }}
            </div>

            <div class="status-card-val">
                Total:
                ₹{{ number_format($summary[$status]['total'], 2) }}
            </div>

        </div>

    @endforeach

</section>


        <!-- Orders Table Card -->
        <section class="orders-card" aria-label="Customer orders list">
          <div class="orders-toolbar">
            <div class="filter-pills-wrap">
<button class="filter-pill active"
        data-status="all"
        onclick="filterByStatus('all')">
    All ({{ $orders->count() }})
</button>              <button class="filter-pill" data-status="received" onclick="filterByStatus('received')">Received</button>
              <button class="filter-pill" data-status="process" onclick="filterByStatus('process')">Process</button>
              <button class="filter-pill" data-status="shipping" onclick="filterByStatus('shipping')">Shipping</button>
              <button class="filter-pill" data-status="out_for_delivery"
                onclick="filterByStatus('out_for_delivery')">Out for delivery</button>
              <button class="filter-pill" data-status="delivered"
                onclick="filterByStatus('delivered')">Delivered</button>
            </div>
<div class="results-count" id="tableResultCount">
    Showing {{ $orders->count() }} orders
</div>          </div>

          <div class="table-responsive">
            <table class="orders-table">
              <thead>
                <tr>
                  <th>Order ID</th>
                  <th>Customer</th>
                  <th>Items Ordered</th>
                  <th>Total Bill</th>
                  <th>Date</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
        
<tbody id="ordersTableBody">

    @forelse($orders as $order)

        @php
            $status = strtolower($order->order_status ?? 'pending');

            $displayStatus = match($status) {
                'pending', 'received' => 'received',
                'processing', 'process' => 'process',
                'shipped', 'shipping' => 'shipping',
                'out_for_delivery' => 'out_for_delivery',
                'delivered' => 'delivered',
                default => $status
            };

            $productNames = $order->items
                ->pluck('product_name')
                ->implode(', ');
        @endphp

        <tr
            data-status="{{ $displayStatus }}"
            data-search="{{ strtolower(
                'sk-'.$order->id.' '.
                $order->customer_name.' '.
                $order->phone.' '.
                $productNames
            ) }}"
        >

            <!-- ORDER ID -->
            <td>
                <span class="order-id-link">
                    #SK-{{ $order->id }}
                </span>
            </td>

            <!-- CUSTOMER -->
            <td>
                <div class="customer-cell">

                    <span class="customer-name">
                        {{ $order->customer_name }}
                    </span>

                    <span class="customer-phone">
                        {{ $order->phone }}
                    </span>

                </div>
            </td>

            <!-- PRODUCTS -->
            <td>
                <div class="items-desc"
                     title="{{ $productNames }}">

                    {{ $order->items->count() }}
                    products •

                    @foreach($order->items as $item)
                        {{ $item->product_name }}
                        (x{{ $item->quantity }})
                        @if(!$loop->last), @endif
                    @endforeach

                </div>
            </td>

            <!-- TOTAL BILL -->
            <td>
                <span class="total-amount">
                    ₹{{ number_format($order->total_amount, 2) }}
                </span>
            </td>

            <!-- ORDER DATE -->
            <td>
                <span class="order-date">
                    {{ $order->created_at->format('d M Y, h:i A') }}
                </span>
            </td>

            <!-- ORDER STATUS -->
            <td>
                <span class="badge badge-{{ $displayStatus }}">
                    <span class="badge-dot"></span>

                    {{ ucwords(str_replace('_', ' ', $displayStatus)) }}
                </span>
            </td>

            <!-- ACTION -->
            <td>
              <a href="{{ route('admin.orders.show', $order->id) }}"
   class="action-btn-view">
    View →
</a>
            </td>

        </tr>

        <!-- ORDER DETAILS -->
        <tr id="details{{ $order->id }}"
            class="order-details-row"
            style="display:none;">

            <td colspan="7">

                <div style="padding:20px;">

                    <h3>Order #SK-{{ $order->id }}</h3>

                    <p>
                        <strong>Customer:</strong>
                        {{ $order->customer_name }}
                    </p>

                    <p>
                        <strong>Phone:</strong>
                        {{ $order->phone }}
                    </p>

                    <p>
                        <strong>Delivery Address:</strong>
                        {{ $order->address }},
                        {{ $order->city }},
                        {{ $order->state }} -
                        {{ $order->postal_code }}
                    </p>

                    @if($order->landmark)
                        <p>
                            <strong>Landmark:</strong>
                            {{ $order->landmark }}
                        </p>
                    @endif

                    <p>
                        <strong>Payment:</strong>
                        {{ strtoupper($order->payment_method) }}
                        ({{ ucfirst($order->payment_status) }})
                    </p>

                    <p>
                        <strong>Subtotal:</strong>
                        ₹{{ number_format($order->subtotal, 2) }}
                    </p>

                    <p>
                        <strong>Delivery:</strong>
                        ₹{{ number_format($order->delivery_fee, 2) }}
                    </p>

                    <p>
                        <strong>Total:</strong>
                        ₹{{ number_format($order->total_amount, 2) }}
                    </p>

                </div>

            </td>
        </tr>

    @empty

        <tr>
            <td colspan="7" style="text-align:center; padding:30px;">
                No orders found.
            </td>
        </tr>

    @endforelse

</tbody>

            </table>
          </div>

          <div class="pagination-bar">
    <div>
        Showing
        <span id="visibleRowCount">{{ $orders->count() }}</span>
        of {{ $orders->count() }} orders recorded
    </div>
</div>
        </section>
      </div>
    </main>
  </div>

  <script>
    // Mobile Sidebar Toggle
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const adminSidebar = document.getElementById('adminSidebar');
    if (mobileMenuBtn && adminSidebar) {
      mobileMenuBtn.addEventListener('click', () => {
        adminSidebar.classList.toggle('open');
      });
    }

    // Filter Table by Status
    let currentFilter = 'all';

    function filterByStatus(status) {
      currentFilter = status;
      const pills = document.querySelectorAll('.filter-pill');
      pills.forEach(pill => {
        if (pill.getAttribute('data-status') === status) {
          pill.classList.add('active');
        } else {
          pill.classList.remove('active');
        }
      });

      applyFilters();
    }

    // Live Search
    const searchInput = document.getElementById('orderSearchInput');
    if (searchInput) {
      searchInput.addEventListener('input', () => {
        applyFilters();
      });
    }

    function applyFilters() {
 
function applyFilters() {
    const searchInput = document.getElementById('orderSearchInput');
    const query = searchInput ? searchInput.value.toLowerCase().trim() : '';

    const rows = document.querySelectorAll(
        '#ordersTableBody tr[data-status]'
    );

    let visibleCount = 0;

    rows.forEach(row => {
        const status = row.dataset.status;
        const search = row.dataset.search || '';

        const matchesStatus =
            currentFilter === 'all' || status === currentFilter;

        const matchesSearch =
            query === '' || search.includes(query);

        const visible = matchesStatus && matchesSearch;

        row.style.display = visible ? '' : 'none';

        const details = row.nextElementSibling;

        if (details && details.classList.contains('order-details-row')) {
            details.style.display = 'none';
        }

        if (visible) {
            visibleCount++;
        }
    });

    document.getElementById('visibleRowCount').textContent = visibleCount;

    document.getElementById('tableResultCount').textContent =
        `Showing ${visibleCount} orders`;
}

function toggleOrderDetails(id) {
    const details = document.getElementById(id);

    if (!details) return;

    details.style.display =
        details.style.display === 'none' ? 'table-row' : 'none';
}

    // TODO: Connect to backend API: GET /api/admin/orders?status={status}&q={query}
  </script>
</body>

</html>