<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Admin Orders | Shopkart Management</title>

  <!-- Google Fonts: Plus Jakarta Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
    rel="stylesheet">

  <!-- Bootstrap 5 CSS & Icons (official CDNs) -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <!-- Bootstrap Table v1.28.0 official CDN -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-table@1.28.0/dist/bootstrap-table.min.css">

  <!-- Shopkart Admin Themes (matching Admin Products layout) -->
  <link href="{{ asset('assets/css/adminproducts.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/css/adminorders.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/css/admin-notifications.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/css/admin-tables.css') }}" rel="stylesheet">

  <style>
    /* Global Reset to Preserve Shopkart Admin Theme */
    body {
      font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
      background-color: var(--admin-bg, #F4F6F5) !important;
      color: var(--ink, #0F172A) !important;
      display: flex !important;
      min-height: 100vh !important;
      margin: 0 !important;
    }

    a {
      text-decoration: none !important;
      color: inherit;
    }

    .admin-container {
      display: flex;
      width: 100%;
      min-height: 100vh;
    }

    /* Clean Card Layout matching Admin Products */
    .category-section-card {
      background: var(--card-bg, #FFFFFF);
      border: 1px solid var(--border, #E2E8F0);
      border-radius: 14px;
      overflow: hidden;
      margin-bottom: 24px;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
    }

    .category-card-header {
      padding: 16px 20px;
      background: #FFFFFF;
      border-bottom: 1px solid var(--border, #E2E8F0);
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      flex-wrap: wrap;
    }

    .category-title-wrap {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .category-title {
      font-size: 16px;
      font-weight: 800;
      color: var(--ink, #0F172A);
      margin: 0;
    }

    .category-badge-pill {
      background: #F1F5F9;
      color: var(--muted, #64748B);
      border: 1px solid var(--border, #E2E8F0);
      font-size: 11px;
      font-weight: 700;
      padding: 3px 10px;
      border-radius: 12px;
    }

    /* Bootstrap Table Clean Styling - Match Admin Products (NO vertical lines) */
    .bootstrap-table .fixed-table-container {
      border: none !important;
      padding-bottom: 0 !important;
    }

    .bootstrap-table .table {
      width: 100% !important;
      border-collapse: collapse !important;
      border: none !important;
      margin-bottom: 0 !important;
      font-size: 13px !important;
      color: var(--ink, #0F172A) !important;
    }

    .bootstrap-table .table th {
      background: #F8FAFC !important;
      color: var(--muted, #64748B) !important;
      font-weight: 700 !important;
      font-size: 11.5px !important;
      text-transform: uppercase !important;
      letter-spacing: 0.04em !important;
      padding: 14px 18px !important;
      border-top: none !important;
      border-left: none !important;
      border-right: none !important;
      border-bottom: 1px solid var(--border, #E2E8F0) !important;
      vertical-align: middle !important;
    }

    .bootstrap-table .table td {
      padding: 16px 18px !important;
      border-top: none !important;
      border-left: none !important;
      border-right: none !important;
      border-bottom: 1px solid var(--border, #E2E8F0) !important;
      color: var(--ink, #0F172A) !important;
      vertical-align: middle !important;
      background-color: transparent !important;
    }

    .bootstrap-table .table tbody tr {
      transition: background-color 0.15s ease !important;
    }

    .bootstrap-table .table tbody tr:hover td {
      background-color: #F8FAFC !important;
    }

    /* Clean Order ID Link */
    .order-id-link {
      font-weight: 700 !important;
      color: var(--primary-teal, #0F766E) !important;
      font-size: 13.5px !important;
      letter-spacing: -0.2px;
      transition: color 0.15s;
    }

    .order-id-link:hover {
      color: var(--primary-hover, #0D655E) !important;
      text-decoration: underline !important;
    }

    /* Action Buttons - Identical to Admin Products */
    .table-actions {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      flex-wrap: wrap;
    }

    .edit-btn,
    .delete-btn {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      background: #FFFFFF;
      border: 1px solid var(--border, #E2E8F0);
      color: var(--ink, #0F172A);
      font-weight: 600;
      font-size: 12px;
      padding: 6px 12px;
      border-radius: 6px;
      text-decoration: none;
      cursor: pointer;
      transition: all 0.15s ease;
      font-family: inherit;
      line-height: 1;
    }

    .edit-btn:hover {
      border-color: var(--primary-teal, #0F766E);
      color: var(--primary-teal, #0F766E);
      background: #F8FAFC;
    }

    .delete-btn:hover {
      border-color: #DC2626;
      color: #DC2626;
      background: #FEF2F2;
    }

    /* Header Tools in Card Header (Refresh, Columns, Fullscreen) */
    .table-tools-box {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .bootstrap-table .fixed-table-toolbar {
      display: none !important;
    }

    .btn-table-tool {
      background: #FFFFFF;
      border: 1px solid var(--border, #E2E8F0);
      color: var(--muted, #64748B);
      border-radius: 8px;
      padding: 6px 10px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      transition: all 0.15s ease;
      line-height: 1;
    }

    .btn-table-tool:hover {
      background: #F8FAFC;
      border-color: var(--primary-teal, #0F766E);
      color: var(--primary-teal, #0F766E);
    }

    /* Bootstrap Table Pagination matching Shopkart */
    .bootstrap-table .fixed-table-pagination {
      padding: 16px 20px !important;
      display: flex !important;
      flex-wrap: wrap !important;
      align-items: center !important;
      justify-content: space-between !important;
      gap: 12px !important;
      border-top: 1px solid var(--border, #E2E8F0) !important;
      margin-top: 0 !important;
    }

    .bootstrap-table .pagination-detail {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 13px;
      color: var(--muted, #64748B);
    }

    .bootstrap-table .pagination-detail .page-list {
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }

    .bootstrap-table .pagination-detail .btn-group .btn {
      background: #FFFFFF !important;
      border: 1px solid var(--border, #E2E8F0) !important;
      color: var(--ink, #0F172A) !important;
      font-weight: 600 !important;
      font-size: 12.5px !important;
      border-radius: 6px !important;
      padding: 4px 10px !important;
      box-shadow: none !important;
    }

    .bootstrap-table .pagination {
      margin: 0 !important;
      display: flex !important;
      list-style: none !important;
      padding-left: 0 !important;
      gap: 4px !important;
    }

    .bootstrap-table .page-item .page-link {
      border: 1px solid var(--border, #E2E8F0) !important;
      border-radius: 6px !important;
      padding: 6px 12px !important;
      font-size: 12.5px !important;
      font-weight: 600 !important;
      color: #475569 !important;
      background: #FFFFFF !important;
      box-shadow: none !important;
      transition: all 0.15s ease;
    }

    .bootstrap-table .page-item .page-link:hover {
      background: #E6F4F1 !important;
      border-color: #99F6E4 !important;
      color: var(--primary-teal, #0F766E) !important;
    }

    .bootstrap-table .page-item.active .page-link {
      background: var(--primary-teal, #0F766E) !important;
      border-color: var(--primary-teal, #0F766E) !important;
      color: #FFFFFF !important;
    }

    /* Column dropdown menu */
    .bootstrap-table .dropdown-menu {
      border: 1px solid var(--border, #E2E8F0) !important;
      border-radius: 10px !important;
      box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
      padding: 8px 0 !important;
      font-size: 13px !important;
      background: #FFFFFF !important;
      z-index: 1050;
    }

    .bootstrap-table .dropdown-item {
      padding: 6px 16px !important;
      color: #334155 !important;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .bootstrap-table .dropdown-item:hover {
      background: #F1F5F9 !important;
      color: var(--primary-teal, #0F766E) !important;
    }

    /* Status Badge styling matching Admin Products */
    .badge {
      display: inline-flex;
      align-items: center;
      font-size: 11.5px;
      font-weight: 700;
      padding: 4px 9px;
      border-radius: 6px;
      white-space: nowrap;
      text-transform: capitalize;
    }

    .badge-in_stock,
    .bg-success {
      background-color: #DCFCE7 !important;
      color: #166534 !important;
    }

    .badge-low_stock,
    .bg-warning {
      background-color: #FEF3C7 !important;
      color: #92400E !important;
    }

    .badge-out_of_stock,
    .bg-danger {
      background-color: #FEE2E2 !important;
      color: #991B1B !important;
    }

    .bg-primary {
      background-color: #EDE9FE !important;
      color: #5B21B6 !important;
    }

    .bg-info {
      background-color: #DBEAFE !important;
      color: #1E40AF !important;
    }

    .bg-secondary {
      background-color: #F1F5F9 !important;
      color: #475569 !important;
    }

    /* Price and Variant Tag styles */
    .price-range-val {
      font-weight: 700;
      color: var(--ink, #0F172A);
      font-size: 14px;
      letter-spacing: -0.2px;
    }

    .variant-summary-tag {
      display: inline-block;
      background: #F1F5F9;
      border: 1px solid var(--border, #E2E8F0);
      color: var(--primary-teal, #0F766E);
      padding: 2px 10px;
      border-radius: 20px;
      font-size: 10.5px;
      font-weight: 700;
      line-height: 1.6;
      white-space: nowrap;
    }

    .payment-ref-sub {
      font-size: 11px;
      color: var(--muted, #64748B);
      font-family: monospace;
      margin-top: 2px;
      max-width: 140px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .form-check-input:checked {
      background-color: var(--primary-teal, #0F766E) !important;
      border-color: var(--primary-teal, #0F766E) !important;
    }
  </style>
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
          <div>
            <h1 class="page-heading">{{ __('Orders Management') }}</h1>
            <div style="font-size: 13px; color: var(--muted);">
              {{ $totalOrdersCount ?? (isset($orders) ? $orders->total() : 0) }} Orders Recorded &bull; Shopkart Admin
              Suite
            </div>
          </div>
        </div>

        <div class="header-actions">
          @include('admin.partials.notification-bar')
        </div>
      </header>

      <div class="content-inner">
        <!-- 5 Status Count Cards -->
        <section class="status-cards-grid" aria-label="Order summary by status">
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
            <div class="status-card {{ $card['class'] }}" data-card-status="{{ $status }}"
              onclick="filterByStatus('{{ $status }}')" role="button" tabindex="0"
              style="cursor: pointer; transition: all 0.2s ease; {{ ($statusFilter ?? '') === $status ? 'outline: 2px solid #0d9488; transform: translateY(-2px);' : '' }}">
              <div class="status-card-header">
                <span class="status-card-title">{{ $card['title'] }}</span>
              </div>
              <div class="status-card-count">
                {{ $summary[$status]['count'] ?? 0 }}
              </div>
              <div class="status-card-val">
                {{ __('Total') }}: ₹{{ number_format($summary[$status]['total'] ?? 0, 2) }}
              </div>
            </div>
          @endforeach
        </section>

        <!-- Status Filter Pills -->
        <div class="orders-toolbar" style="margin-top: 16px;">
          <div class="filter-pills-wrap">
            <button type="button"
              class="filter-pill {{ empty($statusFilter) || $statusFilter === 'all' ? 'active' : '' }}"
              data-status="all" onclick="filterByStatus('all')">
              {{ __('All Orders') }} ({{ $totalOrdersCount ?? (isset($orders) ? $orders->total() : 0) }})
            </button>
            <button type="button" class="filter-pill {{ ($statusFilter ?? '') === 'received' ? 'active' : '' }}"
              data-status="received" onclick="filterByStatus('received')">
              {{ __('Received') }} ({{ $summary['received']['count'] ?? 0 }})
            </button>
            <button type="button" class="filter-pill {{ ($statusFilter ?? '') === 'process' ? 'active' : '' }}"
              data-status="process" onclick="filterByStatus('process')">
              {{ __('Process') }} ({{ $summary['process']['count'] ?? 0 }})
            </button>
            <button type="button" class="filter-pill {{ ($statusFilter ?? '') === 'shipping' ? 'active' : '' }}"
              data-status="shipping" onclick="filterByStatus('shipping')">
              {{ __('Shipping') }} ({{ $summary['shipping']['count'] ?? 0 }})
            </button>
            <button type="button" class="filter-pill {{ ($statusFilter ?? '') === 'out_for_delivery' ? 'active' : '' }}"
              data-status="out_for_delivery" onclick="filterByStatus('out_for_delivery')">
              {{ __('Out for delivery') }} ({{ $summary['out_for_delivery']['count'] ?? 0 }})
            </button>
            <button type="button" class="filter-pill {{ ($statusFilter ?? '') === 'delivered' ? 'active' : '' }}"
              data-status="delivered" onclick="filterByStatus('delivered')">
              {{ __('Delivered') }} ({{ $summary['delivered']['count'] ?? 0 }})
            </button>
            <button type="button" class="filter-pill {{ ($statusFilter ?? '') === 'cancelled' ? 'active' : '' }}"
              data-status="cancelled" onclick="filterByStatus('cancelled')">
              {{ __('Cancelled') }} ({{ $summary['cancelled']['count'] ?? 0 }})
            </button>
          </div>
        </div>

        <!-- Unified Table Toolbar (Search & Filters inline - EXACTLY like Admin Products) -->
        <div class="table-toolbar-card">
          <div class="table-toolbar-form">
            <div class="table-toolbar-left">
              <!-- Search box -->
              <div class="table-search-box">
                <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="11" cy="11" r="8" />
                  <path d="m21 21-4.3-4.3" />
                </svg>
                <input type="text" id="orderSearchInput" class="search-input"
                  placeholder="Search Order ID, customer, payment, item..."
                  value="{{ request('search') ?? request('q') }}" autocomplete="off" aria-label="Search orders">
                <button type="button" id="clearOrderSearchBtn" class="search-clear-btn" style="display: none;"
                  title="Clear search">✕</button>
              </div>

              <!-- Payment Method Filter Dropdown -->
              <select id="filterPaymentMethod"
                class="table-filter-select {{ request('payment_method') ? 'has-value' : '' }}"
                aria-label="Filter by payment method">
                <option value="">All Payment Methods</option>
                <option value="cod" {{ request('payment_method') === 'cod' ? 'selected' : '' }}>COD (Cash on Delivery)
                </option>
                <option value="razorpay" {{ request('payment_method') === 'razorpay' ? 'selected' : '' }}>Razorpay
                </option>
                <option value="stripe" {{ request('payment_method') === 'stripe' ? 'selected' : '' }}>Stripe</option>
              </select>

              <!-- Payment Status Filter Dropdown -->
              <select id="filterPaymentStatus"
                class="table-filter-select {{ request('payment_status') ? 'has-value' : '' }}"
                aria-label="Filter by payment status">
                <option value="">All Payment Statuses</option>
                <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="failed" {{ request('payment_status') === 'failed' ? 'selected' : '' }}>Failed</option>
                <option value="refunded" {{ request('payment_status') === 'refunded' ? 'selected' : '' }}>Refunded
                </option>
              </select>

              <!-- Order Status Filter Dropdown -->
              <select id="filterOrderStatus" class="table-filter-select {{ request('status') ? 'has-value' : '' }}"
                aria-label="Filter by order status">
                <option value="">All Order Statuses</option>
                <option value="received" {{ request('status') === 'received' ? 'selected' : '' }}>Received</option>
                <option value="process" {{ request('status') === 'process' ? 'selected' : '' }}>Processing</option>
                <option value="shipping" {{ request('status') === 'shipping' ? 'selected' : '' }}>Shipping</option>
                <option value="out_for_delivery" {{ request('status') === 'out_for_delivery' ? 'selected' : '' }}>Out for
                  Delivery</option>
                <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
              </select>

              <!-- Date Range Filter -->
              <div style="display: flex; align-items: center; gap: 6px;">
                <input type="date" id="filterDateFrom" class="table-filter-date" value="{{ request('date_from') }}"
                  title="Date From" aria-label="Date From">
                <span style="color: var(--tbl-muted, #64748B); font-size: 12px;">to</span>
                <input type="date" id="filterDateTo" class="table-filter-date" value="{{ request('date_to') }}"
                  title="Date To" aria-label="Date To">
              </div>
            </div>

            <div class="table-toolbar-right">
              <!-- Delete Selected Bulk Action Button -->
              <button type="button" id="btnBulkDelete" class="btn-bulk-delete" style="display: none;"
                onclick="deleteSelectedOrders()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="3 6 5 6 21 6" />
                  <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                </svg>
                Delete Selected (<span class="selected-count" id="selectedCountBadge">0</span>)
              </button>

              <!-- Reset Filters Button -->
              <button type="button" id="btnResetFilters" class="btn-table-reset" title="Reset all filters and search">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="1 4 1 10 7 10" />
                  <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10" />
                </svg>
                Reset Filters
              </button>
            </div>
          </div>
        </div>

        <!-- Section Card matching Admin Products -->
        <section class="category-section-card">
          <div class="category-card-header">
            <div class="category-title-wrap">
              <h2 class="category-title">{{ __('All Orders') }}</h2>
              <span class="category-badge-pill" id="ordersCountPill">
                {{ $totalOrdersCount ?? (isset($orders) ? $orders->total() : 0) }} Orders
                @if (isset($orders) && $orders->total() > 0)
                  &bull; Showing {{ $orders->firstItem() }}-{{ $orders->lastItem() }}
                @endif
              </span>
            </div>

            <!-- Table Tools on Header Right (Refresh, Columns, Fullscreen) -->
            <div class="table-tools-box">
              <button type="button" class="btn-table-tool" onclick="$('#ordersTable').bootstrapTable('refresh')"
                title="Refresh Table">
                <i class="bi bi-arrow-clockwise"></i>
                <span>Refresh</span>
              </button>
              <div class="dropdown d-inline-block">
                <button type="button" class="btn-table-tool dropdown-toggle" data-bs-toggle="dropdown"
                  aria-expanded="false" title="Show / Hide Columns">
                  <i class="bi bi-view-list"></i>
                  <span>Columns</span>
                </button>
                <div class="dropdown-menu dropdown-menu-end shadow" id="columnsDropdownMenu" style="min-width: 180px;">
                  <label class="dropdown-item"><input type="checkbox" checked
                      onchange="toggleTableCol('id', this.checked)"> Order ID</label>
                  <label class="dropdown-item"><input type="checkbox" checked
                      onchange="toggleTableCol('customer_name', this.checked)"> Customer</label>
                  <label class="dropdown-item"><input type="checkbox" checked
                      onchange="toggleTableCol('items_summary', this.checked)"> Products</label>
                  <label class="dropdown-item"><input type="checkbox" checked
                      onchange="toggleTableCol('total_amount', this.checked)"> Total</label>
                  <label class="dropdown-item"><input type="checkbox" checked
                      onchange="toggleTableCol('payment_method', this.checked)"> Payment</label>
                  <label class="dropdown-item"><input type="checkbox" checked
                      onchange="toggleTableCol('payment_status', this.checked)"> Payment Status</label>
                  <label class="dropdown-item"><input type="checkbox" checked
                      onchange="toggleTableCol('order_status', this.checked)"> Order Status</label>
                  <label class="dropdown-item"><input type="checkbox" checked
                      onchange="toggleTableCol('created_at', this.checked)"> Date</label>
                  <label class="dropdown-item"><input type="checkbox" checked
                      onchange="toggleTableCol('actions', this.checked)"> Actions</label>
                </div>
              </div>

            </div>
          </div>

          <div class="table-responsive">
            <table id="ordersTable" class="products-table orders-table" data-toggle="table"
              data-url="{{ route('admin.orders.table-data') }}" data-side-pagination="server" data-pagination="true"
              data-page-size="{{ request('per_page', 10) }}" data-page-list="[5,10, 25, 50, 100]"
              data-total-rows="{{ $totalOrdersCount ?? (isset($orders) ? $orders->total() : 0) }}" data-search="false"
              data-show-refresh="false" data-show-columns="false" data-show-fullscreen="false" data-sort-name="id"
              data-sort-order="desc" data-query-params="ordersQueryParams" data-icons-prefix="bi" data-id-field="id"
              data-unique-id="id" data-escape="false">
              <thead>
    <tr>
        <th data-field="state"
            data-checkbox="true"
            data-click-to-select="true"
            class="select-all-checkbox"
            style="width: 44px;">
        </th>

        <th data-field="id"
            data-sortable="true"
            data-formatter="orderIdFormatter">
            Order ID
        </th>

        <th data-field="customer_name"
            data-sortable="true"
            data-formatter="customerFormatter">
            Customer
        </th>

        <th data-field="items_summary"
            data-formatter="productsFormatter">
            Products
        </th>

        <th data-field="total_amount"
            data-sortable="true"
            data-formatter="amountFormatter">
            Total
        </th>

        <th data-field="payment_method"
            data-sortable="true"
            data-formatter="paymentMethodFormatter">
            Payment
        </th>

        <th data-field="payment_status"
            data-sortable="true"
            data-formatter="paymentStatusFormatter">
            Payment Status
        </th>

        <th data-field="order_status"
            data-sortable="true"
            data-formatter="orderStatusFormatter">
            Order Status
        </th>

        <th data-field="created_at"
            data-sortable="true"
            data-formatter="dateFormatter">
            Date
        </th>

        <th data-field="actions"
            data-formatter="actionsFormatter"
            data-escape="false">
            Actions
        </th>
    </tr>
</thead>
              <tbody id="ordersTableBody">
                @if(isset($orders))
                  @forelse($orders as $order)
                    @php
                      $rawStatus = strtolower($order->order_status ?? 'pending');
                      $displayStatus = match ($rawStatus) {
                        'pending', 'received' => 'received',
                        'processing', 'process' => 'process',
                        'shipped', 'shipping' => 'shipping',
                        'out_for_delivery' => 'out_for_delivery',
                        'delivered' => 'delivered',
                        'cancelled' => 'cancelled',
                        default => $rawStatus
                      };
                      $itemsSummary = $order->items->map(fn($i) => $i->product_name . ' (x' . $i->quantity . ')')->implode(', ');
                    @endphp
                    <tr>
                      <td style="text-align: center;">
                        <input type="checkbox" class="form-check-input row-checkbox" value="{{ $order->id }}"
                          aria-label="Select order">
                      </td>
                      <td>
                        <a href="{{ route('admin.orders.show', $order->id) }}"
                          class="order-id-link">#SK-{{ $order->id }}</a>
                      </td>
                      <td>
                        <div>
                          <div class="product-name" style="font-size: 13.5px;">{{ $order->customer_name }}</div>
                          <div class="product-subtitle" style="font-size: 12px; color: var(--muted, #64748B);">
                            {{ $order->phone }} {{ $order->city ? '• ' . $order->city : '' }}</div>
                        </div>
                      </td>
                      <td>
                        <div style="display: flex; flex-direction: column; gap: 4px; max-width: 280px;">
                          <div>
                            <span class="variant-summary-tag">{{ $order->items->count() }}
                              {{ $order->items->count() === 1 ? 'Item' : 'Items' }}</span>
                          </div>
                          <div style="font-size: 12.5px; color: #475569; line-height: 1.4;" title="{{ $itemsSummary }}">
                            {{ $itemsSummary }}
                          </div>
                        </div>
                      </td>
                      <td>
                        <span class="price-range-val">₹{{ number_format($order->total_amount, 2) }}</span>
                      </td>
                      <td>
                        <div class="payment-cell">
                          <span class="badge bg-secondary"
                            style="font-size: 11px; padding: 3px 8px; text-transform: uppercase;">{{ strtoupper($order->payment_method ?? 'N/A') }}</span>
                          @if($order->razorpay_payment_id)
                            <div class="payment-ref-sub">{{ $order->razorpay_payment_id }}</div>
                          @endif
                          @if($order->razorpay_order_id)
                            <div class="payment-ref-sub">{{ $order->razorpay_order_id }}</div>
                          @endif
                          @if($order->stripe_payment_id)
                            <div class="payment-ref-sub">{{ $order->stripe_payment_id }}</div>
                          @endif
                          @if($order->payment_id && $order->payment_id !== $order->razorpay_payment_id && $order->payment_id !== $order->stripe_payment_id)
                            <div class="payment-ref-sub">{{ $order->payment_id }}</div>
                          @endif
                        </div>
                      </td>
                      <td>
                        @php
                          $ps = strtolower($order->payment_status ?? 'pending');
                          $psClass = match ($ps) {
                            'paid' => 'bg-success',
                            'pending' => 'bg-warning text-dark',
                            'failed' => 'bg-danger',
                            default => 'bg-secondary'
                          };
                        @endphp
                        <span class="badge {{ $psClass }}"
                          style="font-size: 11px; padding: 4px 8px;">{{ ucfirst($order->payment_status ?? 'Pending') }}</span>
                      </td>
                      <td>
                        @php
                          $osClass = match ($displayStatus) {
                            'delivered' => 'bg-success',
                            'shipping' => 'bg-primary',
                            'out_for_delivery' => 'bg-warning text-dark',
                            'process' => 'bg-warning text-dark',
                            'cancelled' => 'bg-danger',
                            default => 'bg-info text-dark'
                          };
                        @endphp
                        <span class="badge {{ $osClass }}"
                          style="font-size: 11px; padding: 4px 8px;">{{ ucwords(str_replace('_', ' ', $displayStatus)) }}</span>
                      </td>
                      <td>
                        <span
                          style="color: var(--muted, #64748B); font-size: 12.5px; white-space: nowrap;">{{ $order->created_at?->format('d M Y, h:i A') }}</span>
                      </td>
                      <td>
                        <div class="table-actions">
                          <a href="{{ route('admin.orders.show', $order->id) }}" class="edit-btn">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                              stroke-width="2">
                              <path d="M12 20h9" />
                              <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z" />
                            </svg>
                            Edit
                          </a>
                          <button type="button" class="delete-btn"
                            onclick="deleteSingleOrder({{ $order->id }}, '#SK-{{ $order->id }}')">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                              stroke-width="2">
                              <path d="M3 6h18" />
                              <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                            </svg>
                            Delete
                          </button>
                        </div>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="10" style="text-align: center; padding: 24px; color: var(--muted, #64748B);">No orders
                        found.</td>
                    </tr>
                  @endforelse
                @endif
              </tbody>
            </table>
          </div>

          @if(isset($orders))
            <div id="serverPaginationFallback" class="server-pagination-fallback">
              @include('admin.partials.table-pagination', [
                'paginator' => $orders,
                'recordName' => 'orders'
              ])
            </div>
          @endif
        </section>
      </div>
    </main>
  </div>

  <!-- jQuery & Bootstrap Bundle -->
 <!-- jQuery first -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Your admin table scripts -->
<script src="{{ asset('assets/js/admin-tables.js') }}"></script>
<script src="{{ asset('assets/js/ajax.js') }}"></script>

<!-- Bootstrap Table -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-table@1.28.0/dist/bootstrap-table.min.js"></script>
  <!-- Global Formatters & Handlers Defined Before Bootstrap Table Loads -->
  <script>
    window.currentStatusFilter = @json($statusFilter ?: 'all');

  // Escape HTML to prevent unsafe HTML output
window.escapeHtml = function (text) {
    if (text === null || text === undefined) return '';

    return String(text).replace(/[&<>"']/g, function (char) {
        return {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        }[char];
    });
};


// 1. Order ID Formatter
window.orderIdFormatter = function (value, row) {
    const id = Number(row.id);

    if (!Number.isSafeInteger(id) || id <= 0) return '';

    const url = window.escapeHtml(row.edit_url || '#');

    return `
        <a href="${url}" class="order-id-link">
            #SK-${id}
        </a>
    `;
};


// 2. Customer Formatter
window.customerFormatter = function (value, row) {
    return `
        <div>
            <div class="product-name" style="font-size:13.5px;">
                ${window.escapeHtml(row.customer_name || 'Guest Customer')}
            </div>

            <div class="product-subtitle"
                 style="font-size:12px;color:var(--muted,#64748B);">
                ${window.escapeHtml(row.phone || '-')}
                ${row.city ? '&bull; ' + window.escapeHtml(row.city) : ''}
            </div>
        </div>
    `;
};


// 3. Products Formatter
window.productsFormatter = function (value, row) {
    const count = Number(row.item_count || 0);
    const summary = window.escapeHtml(row.items_summary || 'No items');

    return `
        <div style="display:flex;flex-direction:column;gap:4px;max-width:280px;">
            <div>
                <span class="variant-summary-tag">
                    ${count} ${count === 1 ? 'Item' : 'Items'}
                </span>
            </div>

            <div style="font-size:12.5px;color:#475569;line-height:1.4;">
                ${summary}
            </div>
        </div>
    `;
};


// 4. Amount Formatter
window.amountFormatter = function (value, row) {
    const amount = Number(value ?? 0);

    if (!Number.isFinite(amount)) return '₹0.00';

    return `
        <span class="price-range-val">
            ₹${amount.toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            })}
        </span>
    `;
};


// 5. Payment Method Formatter
window.paymentMethodFormatter = function (value, row) {
    const method = String(value || '').toLowerCase().trim();

    const config = {
        razorpay: { label: 'RAZORPAY', className: 'bg-info' },
        stripe: { label: 'STRIPE', className: 'bg-primary' },
        cod: { label: 'COD', className: 'bg-warning' }
    };

    const item = config[method] || {
        label: method.toUpperCase() || 'N/A',
        className: 'bg-secondary'
    };

    const reference = method === 'cod'
        ? ''
        : window.escapeHtml(row.payment_reference || '');

    return `
        <div class="payment-cell">
            <span class="badge ${item.className}">
                ${item.label}
            </span>

            ${reference
                ? `<div class="payment-ref-sub" title="${reference}">${reference}</div>`
                : ''
            }
        </div>
    `;
};


// 6. Payment Status Formatter
window.paymentStatusFormatter = function (value) {
    const status = String(value || 'pending').toLowerCase().trim();

    const config = {
        paid: { label: 'Paid', className: 'bg-success' },
        completed: { label: 'Paid', className: 'bg-success' },
        success: { label: 'Paid', className: 'bg-success' },
        pending: { label: 'Pending', className: 'bg-warning' },
        failed: { label: 'Failed', className: 'bg-danger' },
        declined: { label: 'Failed', className: 'bg-danger' },
        refunded: { label: 'Refunded', className: 'bg-secondary' }
    };

    const item = config[status] || {
        label: status.replace(/_/g, ' '),
        className: 'bg-secondary'
    };

    return `<span class="badge ${item.className}">${window.escapeHtml(item.label)}</span>`;
};


// 7. Order Status Formatter
window.orderStatusFormatter = function (value) {
    const status = String(value || 'received').toLowerCase().trim();

    const config = {
        received: { label: 'Received', className: 'bg-info' },
        pending: { label: 'Received', className: 'bg-info' },
        process: { label: 'Processing', className: 'bg-warning' },
        processing: { label: 'Processing', className: 'bg-warning' },
        shipping: { label: 'Shipping', className: 'bg-primary' },
        shipped: { label: 'Shipping', className: 'bg-primary' },
        out_for_delivery: { label: 'Out for Delivery', className: 'bg-warning' },
        delivered: { label: 'Delivered', className: 'bg-success' },
        cancelled: { label: 'Cancelled', className: 'bg-danger' }
    };

    const item = config[status] || {
        label: status.replace(/_/g, ' '),
        className: 'bg-secondary'
    };

    return `<span class="badge ${item.className}">${window.escapeHtml(item.label)}</span>`;
};


// 8. Date Formatter
window.dateFormatter = function (value, row) {
    const date = row.created_at_formatted || value || '-';

    return `
        <span style="color:var(--muted,#64748B);font-size:12.5px;white-space:nowrap;">
            ${window.escapeHtml(date)}
        </span>
    `;
};


// 9. Actions Formatter
window.actionsFormatter = function (value, row) {
    const id = Number(row.id);

    if (!Number.isSafeInteger(id) || id <= 0) return '';

    const editUrl = window.escapeHtml(row.edit_url || '#');
    const invoiceUrl = window.escapeHtml(row.invoice_url || '');

    return `
        <div class="table-actions">

            <a href="${editUrl}" class="edit-btn">
                <i class="bi bi-pencil"></i> Edit
            </a>

            ${invoiceUrl ? `
                <a href="${invoiceUrl}" class="edit-btn"
                   title="Download Invoice">
                    <i class="bi bi-download"></i>
                </a>
            ` : ''}

            <button type="button"
                    class="delete-btn"
                    onclick="deleteSingleOrder(${id}, '#SK-${id}')">
                <i class="bi bi-trash"></i> Delete
            </button>

        </div>
    `;
};

    // Query Params sent with Bootstrap Table requests
    window.ordersQueryParams = function (params) {
      const statusFromDropdown = $('#filterOrderStatus').val();
      if (statusFromDropdown) {
        params.status = statusFromDropdown;
      } else if (window.currentStatusFilter && window.currentStatusFilter !== 'all') {
        params.status = window.currentStatusFilter;
      }

      const paymentMethod = $('#filterPaymentMethod').val();
      if (paymentMethod) {
        params.payment_method = paymentMethod;
      }
      const paymentStatus = $('#filterPaymentStatus').val();
      if (paymentStatus) {
        params.payment_status = paymentStatus;
      }
      const dateFrom = $('#filterDateFrom').val();
      if (dateFrom) {
        params.date_from = dateFrom;
      }
      const dateTo = $('#filterDateTo').val();
      if (dateTo) {
        params.date_to = dateTo;
      }

      const searchVal = $('#orderSearchInput').val()?.trim();
      if (searchVal) {
        params.search = searchVal;
      }

      return params;
    };
  </script>

  <!-- Bootstrap Table v1.28.0 (loaded after formatters are defined on window) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap-table@1.28.0/dist/bootstrap-table.min.js"></script>

  <script>
    // Toggle column visibility
    function toggleTableCol(field, isVisible) {
      if (isVisible) {
        $('#ordersTable').bootstrapTable('showColumn', field);
      } else {
        $('#ordersTable').bootstrapTable('hideColumn', field);
      }
    }

    // Status Filter Handler (Cards and Pills)
    function filterByStatus(status) {
      window.currentStatusFilter = status || 'all';

      // Update pills
      $('.filter-pill').removeClass('active');
      $(`.filter-pill[data-status="${window.currentStatusFilter}"]`).addClass('active');

      // Update dropdown
      $('#filterOrderStatus').val(window.currentStatusFilter === 'all' ? '' : window.currentStatusFilter);
      if (window.currentStatusFilter !== 'all') {
        $('#filterOrderStatus').addClass('has-value');
      } else {
        $('#filterOrderStatus').removeClass('has-value');
      }

      // Update cards outline
      $('.status-card').css({
        'outline': 'none',
        'transform': 'none'
      });
      if (window.currentStatusFilter !== 'all') {
        $(`.status-card[data-card-status="${window.currentStatusFilter}"]`).css({
          'outline': '2px solid #0D9488',
          'transform': 'translateY(-2px)'
        });
      }

      $('#ordersTable').bootstrapTable('refresh', { pageNumber: 1 });
    }

    // Single Record Deletion via AJAX
    function deleteSingleOrder(orderId, orderLabel) {
      if (!confirm(`Are you sure you want to delete "${orderLabel}"? This action cannot be undone.`)) {
        return;
      }

      const csrfToken = $('meta[name="csrf-token"]').attr('content');

      fetch(`/adminorders/${orderId}`, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json'
        }
      })
        .then(async response => {
          const data = await response.json();
          if (!response.ok || data.success === false) {
            throw new Error(data.message || 'Failed to delete order.');
          }
          return data;
        })
        .then(data => {
          if (typeof showAdminToast === 'function') {
            showAdminToast(data.message || `Order ${orderLabel} deleted successfully.`, 'success');
          } else {
            alert(data.message || `Order ${orderLabel} deleted successfully.`);
          }
          $('#ordersTable').bootstrapTable('refresh');
        })
        .catch(err => {
          if (typeof showAdminToast === 'function') {
            showAdminToast(err.message || 'Error deleting order.', 'error');
          } else {
            alert(err.message || 'Error deleting order.');
          }
        });
    }
    window.deleteSingleOrder = deleteSingleOrder;

    // Bulk Delete Selected Records via AJAX
    function deleteSelectedOrders() {
      const selections = $('#ordersTable').bootstrapTable('getSelections');
      const ids = selections.map(r => r.id);

      if (!ids.length) {
        if (typeof showAdminToast === 'function') {
          showAdminToast('Please select at least one order to delete.', 'error');
        } else {
          alert('Please select at least one order to delete.');
        }
        return;
      }

      if (!confirm(`Are you sure you want to delete the ${ids.length} selected orders? This action cannot be undone.`)) {
        return;
      }

      const csrfToken = $('meta[name="csrf-token"]').attr('content');
      const $bulkBtn = $('#btnBulkDelete');

      $bulkBtn.prop('disabled', true).text('Deleting...');

      fetch("{{ route('admin.orders.bulk-delete') }}", {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json'
        },
        body: JSON.stringify({ ids: ids })
      })
        .then(async response => {
          const data = await response.json();
          if (!response.ok || data.success === false) {
            throw new Error(data.message || 'Bulk delete failed.');
          }
          return data;
        })
        .then(data => {
          if (typeof showAdminToast === 'function') {
            showAdminToast(data.message || `${ids.length} orders deleted successfully.`, 'success');
          } else {
            alert(data.message || `${ids.length} orders deleted successfully.`);
          }
          $('#ordersTable').bootstrapTable('refresh');
        })
        .catch(err => {
          if (typeof showAdminToast === 'function') {
            showAdminToast(err.message || 'Error deleting orders.', 'error');
          } else {
            alert(err.message || 'Error deleting orders.');
          }
        })
        .finally(() => {
          $bulkBtn.prop('disabled', true).html(`
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="3 6 5 6 21 6" />
            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
          </svg>
          Delete Selected (<span class="selected-count" id="selectedCountBadge">0</span>)
        `).hide();
        });
    }
    window.deleteSelectedOrders = deleteSelectedOrders;

    // Document Ready Initialization
    $(document).ready(function () {
      // Hide static fallback pagination bar
      $('#serverPaginationFallback').hide();

      // Mobile menu toggle
      $('#mobileMenuBtn').on('click', function () {
        $('#adminSidebar').toggleClass('open');
      });

      // Synchronize selection changes with Bulk Delete Button
      function updateBulkDeleteState() {
        const selections = $('#ordersTable').bootstrapTable('getSelections');
        const count = selections.length;
        const $btn = $('#btnBulkDelete');
        const $badge = $('#selectedCountBadge');

        if (count > 0) {
          $btn.show().prop('disabled', false);
          $badge.text(count);
        } else {
          $btn.hide().prop('disabled', true);
          $badge.text(0);
        }
      }

      $('#ordersTable').on(
        'check.bs.table uncheck.bs.table check-all.bs.table uncheck-all.bs.table refresh.bs.table page-change.bs.table load-success.bs.table',
        function (e, data) {
          updateBulkDeleteState();

          // Update pill showing count
          if (data && data.total !== undefined) {
            const pageNum = $('#ordersTable').bootstrapTable('getOptions').pageNumber || 1;
            const pageSize = $('#ordersTable').bootstrapTable('getOptions').pageSize || 10;
            const start = data.total === 0 ? 0 : ((pageNum - 1) * pageSize) + 1;
            const end = Math.min(pageNum * pageSize, data.total);
            $('#ordersCountPill').html(`${data.total} Orders &bull; Showing ${start}-${end}`);
          }
        }
      );

      // Custom dropdown filters change triggers table refresh
      $('#filterPaymentMethod, #filterPaymentStatus, #filterOrderStatus, #filterDateFrom, #filterDateTo').on('change', function () {
        const hasVal = $(this).val() !== '';
        $(this).toggleClass('has-value', hasVal);

        if ($(this).attr('id') === 'filterOrderStatus') {
          const st = $(this).val() || 'all';
          window.currentStatusFilter = st;
          $('.filter-pill').removeClass('active');
          $(`.filter-pill[data-status="${st}"]`).addClass('active');
        }

        $('#ordersTable').bootstrapTable('refresh', { pageNumber: 1 });
      });

      // Reset filters button
      $('#btnResetFilters').on('click', function () {
        $('#filterPaymentMethod').val('').removeClass('has-value');
        $('#filterPaymentStatus').val('').removeClass('has-value');
        $('#filterOrderStatus').val('').removeClass('has-value');
        $('#filterDateFrom').val('');
        $('#filterDateTo').val('');
        $('#orderSearchInput').val('');
        $('#clearOrderSearchBtn').hide();
        filterByStatus('all');
      });

      // Search input live debounce
      let searchTimeout = null;
      $('#orderSearchInput').on('input', function () {
        const q = $(this).val().trim();
        $('#clearOrderSearchBtn').toggle(q.length > 0);

        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function () {
          $('#ordersTable').bootstrapTable('refresh', { pageNumber: 1 });
        }, 400);
      });

      $('#orderSearchInput').on('keydown', function (e) {
        if (e.key === 'Enter') {
          e.preventDefault();
          clearTimeout(searchTimeout);
          $('#ordersTable').bootstrapTable('refresh', { pageNumber: 1 });
        }
      });

      $('#clearOrderSearchBtn').on('click', function () {
        $('#orderSearchInput').val('');
        $(this).hide();
        $('#ordersTable').bootstrapTable('refresh', { pageNumber: 1 });
      });

      // Automatically refresh table on page load to fetch server data with formatters
      $('#ordersTable').bootstrapTable('refresh');
    });
  </script>
</body>

</html>