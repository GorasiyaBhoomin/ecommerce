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
    @include('admin.partials.admin-sidebar', ['active' => 'orders'])

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
          <h1 class="page-heading">{{ __('Orders Management') }}</h1>
        </div>

        <div class="header-actions">
          <div class="search-box-wrap">
            <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="8" />
              <path d="m21 21-4.3-4.3" />
            </svg>
            <input type="text" id="orderSearchInput" class="search-input"
              placeholder="{{ __('Search Order ID, customer, item...') }}" aria-label="{{ __('Search orders') }}">
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
            'received' => ['title' => __('Received'), 'class' => 'card-received'],
            'process' => ['title' => __('Process'), 'class' => 'card-process'],
            'shipping' => ['title' => __('Shipping'), 'class' => 'card-shipping'],
            'out_for_delivery' => ['title' => __('Out for delivery'), 'class' => 'card-out'],
            'delivered' => ['title' => __('Delivered'), 'class' => 'card-delivered'],
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
                {{ __('Total') }}:
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
    {{ __('All Orders') }} ({{ $orders->count() }})
</button>              <button class="filter-pill" data-status="received" onclick="filterByStatus('received')">{{ __('Received') }}</button>
              <button class="filter-pill" data-status="process" onclick="filterByStatus('process')">{{ __('Process') }}</button>
              <button class="filter-pill" data-status="shipping" onclick="filterByStatus('shipping')">{{ __('Shipping') }}</button>
              <button class="filter-pill" data-status="out_for_delivery"
                onclick="filterByStatus('out_for_delivery')">{{ __('Out for delivery') }}</button>
              <button class="filter-pill" data-status="delivered"
                onclick="filterByStatus('delivered')">{{ __('Delivered') }}</button>
            </div>
<div class="results-count" id="tableResultCount">
    {{ __('Showing') }} {{ $orders->count() }} {{ __('Orders') }}
</div>          </div>

          <div class="table-responsive">
            <table class="orders-table">
              <thead>
                <tr>
                  <th>{{ __('Order ID') }}</th>
                  <th>{{ __('Customer') }}</th>
                  <th>{{ __('Items') }}</th>
                  <th>{{ __('Total') }}</th>
                  <th>{{ __('Date') }}</th>
                  <th>{{ __('Status') }}</th>
                  <th>{{ __('Actions') }}</th>
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