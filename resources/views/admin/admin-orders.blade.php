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
          <div class="search-box-wrap" style="position: relative; display: flex; align-items: center;">
            <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="8" />
              <path d="m21 21-4.3-4.3" />
            </svg>
            <input type="text" id="orderSearchInput" class="search-input"
              placeholder="{{ __('Search Order ID, customer, item...') }}" aria-label="{{ __('Search orders') }}"
              value="{{ request('search') ?? request('q') }}" autocomplete="off">
            <button type="button" id="clearOrderSearchBtn"
              style="display: none; position: absolute; right: 12px; background: none; border: none; color: var(--muted); cursor: pointer; font-size: 14px;"
              title="Clear search">✕</button>
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
             data-card-status="{{ $status }}"
             onclick="filterByStatus('{{ $status }}')"
             role="button"
             tabindex="0"
             style="cursor: pointer; transition: all 0.2s ease; {{ ($statusFilter ?? '') === $status ? 'outline: 2px solid #0d9488; transform: translateY(-2px);' : '' }}">

            <div class="status-card-header">
                <span class="status-card-title">
                    {{ $card['title'] }}
                </span>
            </div>

            <div class="status-card-count">
                {{ $summary[$status]['count'] ?? 0 }}
            </div>

            <div class="status-card-val">
                {{ __('Total') }}:
                ₹{{ number_format($summary[$status]['total'] ?? 0, 2) }}
            </div>

        </div>

    @endforeach

</section>


        <!-- Orders Table Card -->
        <section class="orders-card" aria-label="Customer orders list">
          <div class="orders-toolbar">
            <div class="filter-pills-wrap">
              <button class="filter-pill {{ empty($statusFilter) || $statusFilter === 'all' ? 'active' : '' }}" data-status="all" onclick="filterByStatus('all')">
                  {{ __('All Orders') }} ({{ $totalOrdersCount ?? $orders->total() }})
              </button>
              <button class="filter-pill {{ ($statusFilter ?? '') === 'received' ? 'active' : '' }}" data-status="received" onclick="filterByStatus('received')">
                  {{ __('Received') }} ({{ $summary['received']['count'] ?? 0 }})
              </button>
              <button class="filter-pill {{ ($statusFilter ?? '') === 'process' ? 'active' : '' }}" data-status="process" onclick="filterByStatus('process')">
                  {{ __('Process') }} ({{ $summary['process']['count'] ?? 0 }})
              </button>
              <button class="filter-pill {{ ($statusFilter ?? '') === 'shipping' ? 'active' : '' }}" data-status="shipping" onclick="filterByStatus('shipping')">
                  {{ __('Shipping') }} ({{ $summary['shipping']['count'] ?? 0 }})
              </button>
              <button class="filter-pill {{ ($statusFilter ?? '') === 'out_for_delivery' ? 'active' : '' }}" data-status="out_for_delivery" onclick="filterByStatus('out_for_delivery')">
                  {{ __('Out for delivery') }} ({{ $summary['out_for_delivery']['count'] ?? 0 }})
              </button>
              <button class="filter-pill {{ ($statusFilter ?? '') === 'delivered' ? 'active' : '' }}" data-status="delivered" onclick="filterByStatus('delivered')">
                  {{ __('Delivered') }} ({{ $summary['delivered']['count'] ?? 0 }})
              </button>
              <button class="filter-pill {{ ($statusFilter ?? '') === 'cancelled' ? 'active' : '' }}" data-status="cancelled" onclick="filterByStatus('cancelled')">
                  {{ __('Cancelled') }} ({{ $summary['cancelled']['count'] ?? 0 }})
              </button>
            </div>
            <div class="results-count" id="tableResultCount">
                @if ($orders->total() === 0)
                    {{ __('Showing 0 Orders') }}
                @else
                    {{ __('Showing') }} {{ $orders->firstItem() }}-{{ $orders->lastItem() }} {{ __('of') }} {{ $orders->total() }} {{ __('Orders') }}
                @endif
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

            $payRef = implode(' ', array_filter([
                $order->payment_method,
                $order->payment_id,
                $order->razorpay_payment_id,
                $order->razorpay_order_id,
                $order->stripe_payment_id,
            ]));
        @endphp

        <tr
            data-status="{{ $displayStatus }}"
            data-search="{{ strtolower(
                '#sk-'.$order->id.' '.
                'sk-'.$order->id.' '.
                '#'.$order->id.' '.
                $order->id.' '.
                $order->customer_name.' '.
                $order->phone.' '.
                ($order->city ?? '').' '.
                $productNames.' '.
                $payRef
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
                <div style="font-size: 11px; margin-top: 3px; font-weight: 600;">
                    @if(strtolower($order->payment_method ?? '') === 'razorpay')
                        <span style="color: #0d9488;" title="Razorpay Payment">Razorpay: {{ $order->razorpay_payment_id ?: $order->payment_id ?: $order->razorpay_order_id ?: 'Paid' }}</span>
                    @elseif(strtolower($order->payment_method ?? '') === 'stripe')
                        <span style="color: #4f46e5;" title="Stripe Payment">Stripe: {{ $order->stripe_payment_id ?: $order->payment_id ?: 'Paid' }}</span>
                    @elseif(strtolower($order->payment_method ?? '') === 'cod')
                        <span style="color: #b45309;" title="Cash on Delivery">COD ({{ ucfirst($order->payment_status) }})</span>
                    @else
                        <span style="color: #64748b;">{{ strtoupper($order->payment_method ?? 'N/A') }} ({{ ucfirst($order->payment_status) }})</span>
                    @endif
                </div>
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
                        @if(strtolower($order->payment_method ?? '') === 'razorpay')
                            Razorpay (UPI / Cards / Net Banking)
                        @elseif(strtolower($order->payment_method ?? '') === 'stripe')
                            Stripe (Card Payment)
                        @elseif(strtolower($order->payment_method ?? '') === 'cod')
                            Cash on Delivery (COD)
                        @else
                            {{ strtoupper($order->payment_method ?? 'N/A') }}
                        @endif
                        ({{ ucfirst($order->payment_status) }})
                    </p>

                    @if(strtolower($order->payment_method ?? '') === 'razorpay')
                        @if($order->razorpay_payment_id || $order->payment_id)
                            <p>
                                <strong>Razorpay Payment ID:</strong>
                                <code>{{ $order->razorpay_payment_id ?: $order->payment_id }}</code>
                            </p>
                        @endif
                        @if($order->razorpay_order_id)
                            <p>
                                <strong>Razorpay Order ID:</strong>
                                <code>{{ $order->razorpay_order_id }}</code>
                            </p>
                        @endif
                    @elseif(strtolower($order->payment_method ?? '') === 'stripe')
                        @if($order->stripe_payment_id || $order->payment_id)
                            <p>
                                <strong>Stripe Payment ID:</strong>
                                <code>{{ $order->stripe_payment_id ?: $order->payment_id }}</code>
                            </p>
                        @endif
                    @elseif($order->payment_id)
                        <p>
                            <strong>Payment ID:</strong>
                            <code>{{ $order->payment_id }}</code>
                        </p>
                    @endif

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

    <tr id="noOrderMatchRow" style="display: none;">
        <td colspan="7" style="text-align: center; padding: 30px; color: var(--muted);">
            No orders match your filter criteria.
        </td>
    </tr>

</tbody>

            </table>
          </div>

          <div class="pagination-bar">
            <div>
                Showing
                <span id="visibleRowCount">
                    @if ($orders->total() === 0)
                        0
                    @else
                        {{ $orders->firstItem() }}-{{ $orders->lastItem() }}
                    @endif
                </span>
                of {{ $orders->total() }} orders recorded
            </div>
            @if ($orders->hasPages())
                <nav class="pagination-controls" aria-label="Orders pagination">
                    {{-- Previous Page Link --}}
                    @if ($orders->onFirstPage())
                        <span class="pagination-btn disabled" aria-disabled="true">&larr; Previous</span>
                    @else
                        <a href="{{ $orders->previousPageUrl() }}" class="pagination-btn" rel="prev">&larr; Previous</a>
                    @endif

                    {{-- Page Numbers --}}
                    <div class="pagination-numbers">
                        @for ($page = 1; $page <= $orders->lastPage(); $page++)
                            @if ($page == $orders->currentPage())
                                <span class="pagination-number active" aria-current="page">{{ $page }}</span>
                            @else
                                <a href="{{ $orders->url($page) }}" class="pagination-number">{{ $page }}</a>
                            @endif
                        @endfor
                    </div>

                    {{-- Next Page Link --}}
                    @if ($orders->hasMorePages())
                        <a href="{{ $orders->nextPageUrl() }}" class="pagination-btn" rel="next">Next &rarr;</a>
                    @else
                        <span class="pagination-btn disabled" aria-disabled="true">Next &rarr;</span>
                    @endif
                </nav>
            @endif
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
    let currentFilter = @json($statusFilter ?: 'all');

    function filterByStatus(status) {
      const url = new URL(window.location.href);
      if (status && status !== 'all') {
        url.searchParams.set('status', status);
      } else {
        url.searchParams.delete('status');
      }
      url.searchParams.delete('page');
      window.location.href = url.toString();
    }

    // Live Search & Enter to Search across all pages
    const searchInput = document.getElementById('orderSearchInput');
    const clearOrderSearchBtn = document.getElementById('clearOrderSearchBtn');

    if (searchInput) {
      searchInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
          e.preventDefault();
          const q = searchInput.value.trim();
          const url = new URL(window.location.href);
          if (q) {
            url.searchParams.set('search', q);
          } else {
            url.searchParams.delete('search');
          }
          url.searchParams.delete('page');
          window.location.href = url.toString();
        }
      });

      searchInput.addEventListener('input', () => {
        if (clearOrderSearchBtn) {
          clearOrderSearchBtn.style.display = searchInput.value ? 'block' : 'none';
        }
        applyFilters();
      });
    }

    if (clearOrderSearchBtn) {
      clearOrderSearchBtn.addEventListener('click', () => {
        if (searchInput) {
          searchInput.value = '';
          clearOrderSearchBtn.style.display = 'none';
          const url = new URL(window.location.href);
          if (url.searchParams.has('search') || url.searchParams.has('q')) {
            url.searchParams.delete('search');
            url.searchParams.delete('q');
            url.searchParams.delete('page');
            window.location.href = url.toString();
          } else {
            applyFilters();
            searchInput.focus();
          }
        }
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

      const noMatchRow = document.getElementById('noOrderMatchRow');
      if (noMatchRow) {
          noMatchRow.style.display = (visibleCount === 0 && rows.length > 0) ? '' : 'none';
      }

      const visibleEl = document.getElementById('visibleRowCount');
      if (visibleEl) {
          visibleEl.textContent = visibleCount;
      }

      const resultCountEl = document.getElementById('tableResultCount');
      if (resultCountEl) {
          resultCountEl.textContent = `Showing ${visibleCount} orders`;
      }
    }

    function toggleOrderDetails(id) {
        const details = document.getElementById(id);

        if (!details) return;

        details.style.display =
            details.style.display === 'none' ? 'table-row' : 'none';
    }

    // Read initial URL params on page load
    document.addEventListener('DOMContentLoaded', () => {
      const urlParams = new URLSearchParams(window.location.search);
      const paramStatus = urlParams.get('status');
      const paramSearch = urlParams.get('search') || urlParams.get('q');

      if (paramSearch && searchInput) {
        searchInput.value = paramSearch;
        if (clearOrderSearchBtn) clearOrderSearchBtn.style.display = 'block';
      }

      if (paramStatus) {
        filterByStatus(paramStatus);
      } else {
        applyFilters();
      }
    });
</body>

</html>