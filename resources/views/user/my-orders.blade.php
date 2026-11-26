<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Orders &amp; Live Tracking | Shopkart</title>
  <!-- Google Fonts: Plus Jakarta Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/myorder.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/user-navbar.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/admin-tables.css') }}">
</head>
<body>

  @include('user.partials.navbar', ['active' => 'orders'])
  
@if(session('success'))
    <div class="alert alert-success"
         style="padding:15px; background:#d1fae5; color:#065f46; border-radius:8px; margin-bottom:20px;">

        {{ session('success') }}

    </div>
@endif
  <!-- Main Content -->

<main class="main-content main-content-wide">

    <div class="page-header-row" style="align-items: flex-start; gap: 16px; flex-wrap: wrap;">
        <div>
            <h1 class="page-title">
                My Orders &amp; Tracking
            </h1>
            <p>Track all your orders, view item details, and download tax invoices.</p>
        </div>

        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <!-- View Mode Switcher -->
            <div style="display: inline-flex; background: #E2E8F0; padding: 3px; border-radius: 8px;">
                <a href="{{ request()->fullUrlWithQuery(['view' => 'cards']) }}"
                   class="btn-action-outline"
                   style="border: none; padding: 6px 12px; font-size: 12px; font-weight: 700; border-radius: 6px; text-decoration: none; {{ $viewMode !== 'table' ? 'background: #FFFFFF; color: var(--primary-teal); box-shadow: 0 1px 3px rgba(0,0,0,0.1);' : 'background: transparent; color: var(--muted);' }}"
                   title="Card view with live progress trackers">
                    📦 Tracker Cards
                </a>
                <a href="{{ request()->fullUrlWithQuery(['view' => 'table']) }}"
                   class="btn-action-outline"
                   style="border: none; padding: 6px 12px; font-size: 12px; font-weight: 700; border-radius: 6px; text-decoration: none; {{ $viewMode === 'table' ? 'background: #FFFFFF; color: var(--primary-teal); box-shadow: 0 1px 3px rgba(0,0,0,0.1);' : 'background: transparent; color: var(--muted);' }}"
                   title="Data table view with columns and sorting">
                    📋 Orders Table
                </a>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="table-toolbar-card" style="margin-bottom: 20px;">
        <form method="GET" action="{{ route('orders.index') }}" class="table-toolbar-form" id="myOrdersFilterForm">
            @if (request('view'))
                <input type="hidden" name="view" value="{{ request('view') }}">
            @endif
            @if (request('per_page'))
                <input type="hidden" name="per_page" value="{{ request('per_page') }}">
            @endif

            <div class="table-toolbar-left">
                <div class="table-search-box">
                    <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8" />
                        <path d="m21 21-4.3-4.3" />
                    </svg>
                    <input type="text" name="search" class="search-input" placeholder="Search by Order #, product, or payment ID..."
                           value="{{ request('search') ?? request('q') }}" autocomplete="off" aria-label="Search my orders">
                    <button type="button" class="search-clear-btn" style="{{ (request('search') || request('q')) ? 'display:flex;' : 'display:none;' }}" onclick="clearTableSearch(this)" title="Clear search">✕</button>
                </div>

                <!-- Status Filter Dropdown -->
                <select name="status" class="table-filter-select {{ request('status') ? 'has-value' : '' }}" aria-label="Filter by order status">
                    <option value="">All Orders ({{ $allCount ?? $orders->total() }})</option>
                    <option value="progress" {{ request('status') === 'progress' ? 'selected' : '' }}>In Progress ({{ $inProgressCount ?? 0 }})</option>
                    <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered ({{ $deliveredCount ?? 0 }})</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled ({{ $cancelledCount ?? 0 }})</option>
                </select>

                <!-- Sorting Dropdown -->
                <select name="sort" class="table-filter-select" aria-label="Sort orders" onchange="this.form.submit()">
                    <option value="created_at:desc" {{ (request('sort') === 'created_at:desc' || (!request('sort') && request('sort_by', 'created_at') === 'created_at' && request('sort_dir', 'desc') === 'desc')) ? 'selected' : '' }}>Newest First</option>
                    <option value="created_at:asc" {{ (request('sort') === 'created_at:asc' || (request('sort_by') === 'created_at' && request('sort_dir') === 'asc')) ? 'selected' : '' }}>Oldest First</option>
                    <option value="total_amount:desc" {{ (request('sort') === 'total_amount:desc' || (request('sort_by') === 'total_amount' && request('sort_dir', 'desc') === 'desc')) ? 'selected' : '' }}>Highest Total</option>
                    <option value="total_amount:asc" {{ (request('sort') === 'total_amount:asc' || (request('sort_by') === 'total_amount' && request('sort_dir') === 'asc')) ? 'selected' : '' }}>Lowest Total</option>
                    <option value="id:desc" {{ (request('sort') === 'id:desc' || (request('sort_by') === 'id' && request('sort_dir', 'desc') === 'desc')) ? 'selected' : '' }}>Order ID (High &rarr; Low)</option>
                    <option value="id:asc" {{ (request('sort') === 'id:asc' || (request('sort_by') === 'id' && request('sort_dir') === 'asc')) ? 'selected' : '' }}>Order ID (Low &rarr; High)</option>
                </select>
            </div>

            <div class="table-toolbar-right">
                @if (request('search') || request('q') || request('status') || request('sort_by') || request('sort'))
                    <a href="{{ route('orders.index', array_filter(['view' => request('view')])) }}" class="btn-table-reset" title="Reset all filters">
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

    @if ($viewMode === 'table')
        <!-- TABLE VIEW -->
        <div class="table-card-wrapper">
            <div class="table-responsive">
                <table class="products-table data-table orders-data-table">
                    <thead>
                        <tr>
                            @include('admin.partials.sort-th', ['column' => 'id', 'label' => 'Order #', 'style' => 'width: 110px; min-width: 105px;'])
                            @include('admin.partials.sort-th', ['column' => 'created_at', 'label' => 'Date Placed', 'style' => 'width: 135px; min-width: 130px;'])
                            <th style="min-width: 220px;">Products Ordered</th>
                            @include('admin.partials.sort-th', ['column' => 'total_amount', 'label' => 'Total Amount', 'style' => 'width: 135px; min-width: 130px;'])
                            <th style="width: 180px; min-width: 170px;">Payment Method</th>
                            @include('admin.partials.sort-th', ['column' => 'order_status', 'label' => 'Live Status', 'style' => 'width: 145px; min-width: 140px;'])
                            <th style="text-align: right; width: 195px; min-width: 190px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            @php
                                $status = strtolower($order->order_status ?? 'pending');
                                $statusBadgeClass = match($status) {
                                    'delivered' => 'badge-delivered',
                                    'cancelled' => 'badge-cancelled',
                                    'received', 'pending' => 'badge-received',
                                    'shipping', 'shipped' => 'badge-shipping',
                                    'out_for_delivery' => 'badge-out_for_delivery',
                                    default => 'badge-process',
                                };
                            @endphp
                            <tr id="orderRow{{ $order->id }}">
                                <td style="white-space: nowrap;">
                                    <span class="order-id-badge">#SK-{{ $order->id }}</span>
                                </td>
                                <td style="white-space: nowrap;">
                                    <div style="font-size: 13px; color: var(--tbl-ink); font-weight: 700;">{{ $order->created_at->format('d M Y') }}</div>
                                    <div style="font-size: 11px; color: var(--tbl-muted); margin-top: 2px;">{{ $order->created_at->format('h:i A') }}</div>
                                </td>
                                <td>
                                    <div style="display: flex; flex-direction: column; gap: 5px;">
                                        @foreach($order->items as $item)
                                            <div style="font-size: 13px; color: var(--tbl-ink); display: flex; align-items: baseline; gap: 6px; flex-wrap: wrap;">
                                                <span class="product-qty-badge">{{ $item->quantity }}&times;</span>
                                                <span style="font-weight: 600;">{{ $item->product_name }}</span>
                                                <span style="font-size: 11px; color: var(--tbl-muted);">(₹{{ number_format($item->price, 2) }})</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                                <td style="white-space: nowrap;">
                                    <div style="font-size: 14px; font-weight: 800; color: var(--tbl-ink);">₹{{ number_format($order->total_amount, 2) }}</div>
                                    @if($order->delivery_fee > 0)
                                        <div style="font-size: 11px; color: var(--tbl-muted); margin-top: 2px;">Delivery: ₹{{ number_format($order->delivery_fee, 2) }}</div>
                                    @else
                                        <span class="delivery-free-badge">Free Delivery</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                        <span class="payment-method-badge">{{ strtoupper($order->payment_method ?? 'COD') }}</span>
                                        <span class="payment-status-pill status-{{ strtolower($order->payment_status) }}">{{ ucfirst($order->payment_status) }}</span>
                                    </div>
                                    @if(strtolower($order->payment_method ?? '') === 'razorpay' && ($order->razorpay_payment_id || $order->payment_id))
                                        <div class="payment-id-tag" title="Razorpay ID: {{ $order->razorpay_payment_id ?: $order->payment_id }}">
                                            <span style="opacity: 0.65; margin-right: 3px;">ID:</span>{{ Str::limit($order->razorpay_payment_id ?: $order->payment_id, 14, '…') }}
                                        </div>
                                    @elseif(strtolower($order->payment_method ?? '') === 'stripe' && ($order->stripe_payment_id || $order->payment_id))
                                        <div class="payment-id-tag" title="Stripe ID: {{ $order->stripe_payment_id ?: $order->payment_id }}">
                                            <span style="opacity: 0.65; margin-right: 3px;">ID:</span>{{ Str::limit($order->stripe_payment_id ?: $order->payment_id, 14, '…') }}
                                        </div>
                                    @endif
                                </td>
                                <td style="white-space: nowrap;">
                                    <span class="badge {{ $statusBadgeClass }}">
                                        <span class="badge-dot"></span>
                                        {{ ucwords(str_replace('_', ' ', $status)) }}
                                    </span>
                                </td>
                                <td style="text-align: right; white-space: nowrap;">
                                    <div class="tbl-actions-group">
                                        <a href="{{ route('orders.invoice', $order->id) }}" class="btn-tbl-action btn-tbl-invoice" title="Download Tax Invoice (PDF)">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3">
                                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                                <polyline points="7 10 12 15 17 10"/>
                                                <line x1="12" y1="15" x2="12" y2="3"/>
                                            </svg>
                                            Invoice
                                        </a>
                                        <button type="button" class="btn-tbl-action btn-tbl-details" onclick="toggleDetails('tableDrawer{{ $order->id }}')" title="View Order & Shipping Details">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3">
                                                <circle cx="12" cy="12" r="10"/>
                                                <line x1="12" y1="16" x2="12" y2="12"/>
                                                <line x1="12" y1="8" x2="12.01" y2="8"/>
                                            </svg>
                                            Details
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <!-- Hidden / Expandable details row for table -->
                            <tr id="tableDrawer{{ $order->id }}" class="table-drawer-row" style="display: none;">
                                <td colspan="7">
                                    <div class="table-drawer-content">
                                        <div>
                                            <div class="drawer-section-title">📦 Delivery Address</div>
                                            <div class="drawer-section-body">
                                                {{ $order->address }}, {{ $order->city }}, {{ $order->state }} &ndash; <strong>{{ $order->postal_code }}</strong>
                                                @if($order->landmark)
                                                    <div style="font-size: 12px; color: var(--tbl-muted); margin-top: 2px;">Landmark: {{ $order->landmark }}</div>
                                                @endif
                                            </div>
                                        </div>
                                        <div>
                                            <div class="drawer-section-title">👤 Recipient Contact</div>
                                            <div class="drawer-section-body">
                                                <strong>{{ $order->customer_name }}</strong><br>
                                                Phone: <a href="tel:{{ $order->phone }}" style="color: var(--tbl-teal); text-decoration: none;">{{ $order->phone }}</a>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="drawer-section-title">💳 Payment Reference</div>
                                            <div class="drawer-section-body" style="font-size: 12px;">
                                                <div><strong>Method:</strong> {{ strtoupper($order->payment_method ?? 'COD') }} &bull; {{ ucfirst($order->payment_status) }}</div>
                                                @if($order->razorpay_payment_id || $order->payment_id)
                                                    <div style="margin-top: 3px;"><strong>Payment ID:</strong> <code style="background: #E2E8F0; padding: 2px 5px; border-radius: 4px; font-size: 11px;">{{ $order->razorpay_payment_id ?: ($order->stripe_payment_id ?: $order->payment_id) }}</code></div>
                                                @endif
                                                @if($order->razorpay_order_id)
                                                    <div style="margin-top: 3px;"><strong>Order Ref:</strong> <code style="background: #E2E8F0; padding: 2px 5px; border-radius: 4px; font-size: 11px;">{{ $order->razorpay_order_id }}</code></div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            @include('admin.partials.empty-state', [
                                'colspan' => 7,
                                'title' => 'No orders found',
                                'message' => 'No orders match your search or filter criteria.',
                                'resetUrl' => route('orders.index', ['view' => 'table']),
                                'hasFilters' => (bool) (request('search') || request('q') || request('status') || request('sort_by') || request('sort')),
                            ])
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <!-- CARDS VIEW (DEFAULT) -->
        @forelse($orders as $order)
            @php
                $status = strtolower($order->order_status ?? 'pending');
                $category = $status === 'delivered' ? 'delivered' : 'progress';
                $steps = [
                    'pending' => 0,
                    'received' => 0,
                    'processing' => 1,
                    'process' => 1,
                    'shipped' => 2,
                    'shipping' => 2,
                    'out_for_delivery' => 3,
                    'delivered' => 4,
                ];
                $currentStep = $steps[$status] ?? -1;
                $trackingSteps = [
                    'Received',
                    'Process',
                    'Shipping',
                    'Out for delivery',
                    'Delivered'
                ];
            @endphp

            <article class="order-card" data-category="{{ $category }}">
                <!-- ORDER HEADER -->
                <div class="order-card-header">
                    <div class="order-meta-group">
                        <span class="order-id-tag">
                            Order #SK-{{ $order->id }}
                        </span>
                        <span class="order-date-text">
                            Placed on {{ $order->created_at->format('d M Y, h:i A') }}
                        </span>
                    </div>

                    <div>
                        <span class="badge {{ $status === 'delivered' ? 'badge-delivered' : 'badge-process' }}">
                            <span class="badge-dot"></span>
                            {{ ucwords(str_replace('_', ' ', $status)) }}
                        </span>
                    </div>
                </div>

                <!-- ORDER TRACKING -->
                @if($status !== 'cancelled')
                    <section class="tracker-section">
                        <div class="tracker-bar-wrap">
                            @foreach($trackingSteps as $index => $step)
                                <div class="tracker-step {{ $index <= $currentStep ? 'completed' : '' }}">
                                    <div class="step-segment-fill"></div>
                                    <div class="step-label-row">
                                        <span>{{ $step }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @else
                    <p style="padding:15px; color:#dc2626;">
                        This order has been cancelled.
                    </p>
                @endif

                <!-- ORDER PRODUCTS -->
                <div class="order-card-body">
                    <div class="items-list">
                        @foreach($order->items as $item)
                            <div class="item-entry">
                                <div>
                                    <span class="item-entry-title">
                                        {{ $item->product_name }}
                                    </span>
                                    <span class="item-entry-qty">
                                        × {{ $item->quantity }}
                                    </span>
                                    <span class="item-entry-variant">
                                        ₹{{ number_format($item->price, 2) }} each
                                    </span>
                                </div>

                                <span class="item-entry-price">
                                    ₹{{ number_format($item->subtotal, 2) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- ORDER FOOTER -->
                <div class="order-card-footer">
                    <div class="total-bill-box">
                        <span class="total-bill-label">
                            Total Bill (Subtotal ₹{{ number_format($order->subtotal, 2) }} + Delivery {{ $order->delivery_fee > 0 ? '₹'.number_format($order->delivery_fee, 2) : 'FREE' }}):
                        </span>
                        <span class="total-bill-amount">
                            ₹{{ number_format($order->total_amount, 2) }}
                        </span>
                        <span style="font-size:12px;">
                            @if(strtolower($order->payment_method ?? '') === 'razorpay')
                                Razorpay • {{ ucfirst($order->payment_status) }}
                                @if($order->razorpay_payment_id || $order->payment_id)
                                    <small style="display:block; color:#0d9488; font-weight:600;">Razorpay ID: {{ $order->razorpay_payment_id ?: $order->payment_id }}</small>
                                @endif
                                @if($order->razorpay_order_id)
                                    <small style="display:block; color:#64748b; font-weight:600;">Order ID: {{ $order->razorpay_order_id }}</small>
                                @endif
                            @elseif(strtolower($order->payment_method ?? '') === 'stripe')
                                Stripe • {{ ucfirst($order->payment_status) }}
                                @if($order->stripe_payment_id || $order->payment_id)
                                    <small style="display:block; color:#4f46e5; font-weight:600;">Payment ID: {{ $order->stripe_payment_id ?: $order->payment_id }}</small>
                                @endif
                            @else
                                {{ strtoupper($order->payment_method ?? 'COD') }} • {{ ucfirst($order->payment_status) }}
                                @if($order->payment_id)
                                    <small style="display:block; color:#64748b;">Payment ID: {{ $order->payment_id }}</small>
                                @endif
                            @endif
                        </span>
                    </div>

                    <div class="card-actions-group">
                        <a href="{{ route('orders.invoice', $order->id) }}" class="btn-action-outline">
                            Download Invoice
                        </a>
                        <button type="button" class="btn-action-outline" onclick="toggleDetails('drawer{{ $order->id }}')">
                            View Details
                        </button>
                    </div>
                </div>

                <!-- DELIVERY DETAILS DRAWER -->
                <div class="details-drawer" id="drawer{{ $order->id }}">
                    <strong>Delivery Address:</strong>
                    {{ $order->address }}, {{ $order->city }}, {{ $order->state }} - {{ $order->postal_code }}<br>
                    @if($order->landmark)
                        <strong>Landmark:</strong> {{ $order->landmark }}<br>
                    @endif
                    <strong>Recipient:</strong> {{ $order->customer_name }} ({{ $order->phone }})<br>
                    <strong>Payment Method:</strong>
                    @if(strtolower($order->payment_method ?? '') === 'razorpay')
                        Razorpay (UPI / Cards / Net Banking)
                    @elseif(strtolower($order->payment_method ?? '') === 'stripe')
                        Stripe (Card Payment)
                    @elseif(strtolower($order->payment_method ?? '') === 'cod')
                        Cash on Delivery (COD)
                    @else
                        {{ strtoupper($order->payment_method ?? 'N/A') }}
                    @endif
                    <br>
                    <strong>Payment Status:</strong> {{ ucfirst($order->payment_status) }}
                    @if(strtolower($order->payment_method ?? '') === 'razorpay')
                        @if($order->razorpay_payment_id || $order->payment_id)
                            <br><strong>Razorpay Payment ID:</strong> <code>{{ $order->razorpay_payment_id ?: $order->payment_id }}</code>
                        @endif
                        @if($order->razorpay_order_id)
                            <br><strong>Razorpay Order ID:</strong> <code>{{ $order->razorpay_order_id }}</code>
                        @endif
                    @elseif(strtolower($order->payment_method ?? '') === 'stripe')
                        @if($order->stripe_payment_id || $order->payment_id)
                            <br><strong>Stripe Payment ID:</strong> <code>{{ $order->stripe_payment_id ?: $order->payment_id }}</code>
                        @endif
                    @elseif($order->payment_id)
                        <br><strong>Payment ID:</strong> <code>{{ $order->payment_id }}</code>
                    @endif
                </div>
            </article>
        @empty
            <div style="text-align:center; padding:60px 20px; background:#FFFFFF; border-radius:12px; border:1px solid var(--border);">
                <h2>No Orders Found!</h2>
                <p style="color:var(--muted); margin: 8px 0 16px 0;">You haven't placed any orders matching your criteria.</p>
                <a href="{{ route('shop.index') }}" class="btn-action-primary">
                    Start Shopping
                </a>
            </div>
        @endforelse
    @endif

    <!-- Responsive Pagination Bar -->
    @include('admin.partials.table-pagination', [
        'paginator' => $orders,
        'recordName' => 'orders'
    ])

</main>

<script src="{{ asset('assets/js/admin-tables.js') }}"></script>
<script>
    function toggleDetails(drawerId) {
        const drawer = document.getElementById(drawerId);
        if (!drawer) return;
        if (drawer.tagName === 'TR') {
            drawer.style.display = (drawer.style.display === 'none' || !drawer.style.display) ? 'table-row' : 'none';
        } else {
            drawer.classList.toggle('open');
        }
    }
</script>

</body>
</html>
