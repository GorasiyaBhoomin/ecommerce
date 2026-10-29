<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Order #SK-{{ $order->id }} Details | Shopkart Admin</title>
  <!-- Google Fonts: Plus Jakarta Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
    rel="stylesheet">
  <link href="{{ asset('assets/css/adminorderdetail.css') }}" rel="stylesheet">
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
          <a href="/adminorders" class="menu-link active">
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect width="18" height="18" x="3" y="3" rx="2" />
              <path d="M9 3v18" />
              <path d="m14 9 3 3-3 3" />
            </svg>
            Orders
          </a>
        </li>
        <li>
          <a href="/adminproducts" class="menu-link">
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
      <form id="orderStatusForm" method="POST" action="{{ route('admin.orders.status', $order->id) }}">
        @csrf
        @method('PATCH')
        <input type="hidden" name="order_status" id="orderStatusInput" value="{{ $currentStatus }}">
      </form>
      <header class="top-header">
        <div class="header-title-wrap">
          <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle navigation menu">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="4" x2="20" y1="12" y2="12" />
              <line x1="4" x2="20" y1="6" y2="6" />
              <line x1="4" x2="20" y1="18" y2="18" />
            </svg>
          </button>
          <div class="header-left">
          <div class="breadcrumb">
            <a href="/adminorders">Orders</a>
            <span>/</span>
            <span>Order #SK-{{ $order->id }}</span>
          </div>
          <div class="page-heading-row">
            <h1 class="page-heading">Order #SK-{{ $order->id }}</h1>
            <span class="badge badge-{{ $currentStatus }}" id="headerStatusBadge">
              <span class="badge-dot"></span>
              <span id="headerStatusText">{{ $statusLabels[$currentStatus] }}</span>
            </span>
            <span class="order-date-tag">Placed on {{ $order->created_at->format('d M Y, h:i A') }}</span>
          </div>
        </div>
        </div>

        <div class="header-actions">
          <a href="{{ route('admin.orders.invoice', $order->id) }}" class="btn-secondary">
    Download PDF
</a>
          <a href="/adminorders" class="btn-secondary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="m15 18-6-6 6-6" />
            </svg>
            Back to Orders
          </a>
<form action="{{ route('admin.orders.sendEmail', $order->id) }}"
      method="POST">

    @csrf

    <button type="submit" class="btn-email" id="sendEmailBtn">
        Send Email
    </button>

</form>
          <button class="btn-primary" id="saveStatusBtn" type="submit" form="orderStatusForm">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
              <polyline points="17 21 17 13 7 13 7 21" />
              <polyline points="7 3 7 8 15 8" />
            </svg>
            Save Status
          </button>
          @include('admin.partials.notification-bar')
        </div>
      </header>

      <div class="content-inner">
        @if(session('success'))
          <div class="status-save-bar" role="status">{{ session('success') }}</div>
        @endif
        @if($errors->any())
          <div class="status-save-bar" role="alert">{{ $errors->first() }}</div>
        @endif
        <!-- Clickable 5-Step Status Selector Card -->
        <section class="status-tracker-card" aria-label="Order status control">
          <div class="status-card-header-bar">
            <div class="status-section-title">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10" />
                <polyline points="12 6 12 12 16 14" />
              </svg>
              Fulfillment Lifecycle &bull; Click any step to change status
            </div>
            <div class="live-status-indicator">
              <span>Selected Status:</span>
              <strong id="currentSelectedStatusLabel" style="color: var(--primary-teal);">{{ $statusLabels[$currentStatus] }}</strong>
            </div>
          </div>

          <div class="stepper-container">
            <div class="stepper-line-bg"></div>
            <div class="stepper-line-fill" id="stepperLineFill"></div>

            <!-- Step 1: Received -->
            <button class="step-item" id="step-received" onclick="selectStep('received', 0)" type="button">
              <div class="step-circle">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <polyline points="20 6 9 17 4 12" />
                </svg>
              </div>
              <div class="step-title">Received</div>
              <div class="step-subtitle">{{ $order->created_at->format('d M, h:i A') }}</div>
            </button>

            <!-- Step 2: Process -->
            <button class="step-item" id="step-process" onclick="selectStep('process', 1)" type="button">
              <div class="step-circle">2</div>
              <div class="step-title">Process</div>
              <div class="step-subtitle">In progress</div>
            </button>

            <!-- Step 3: Shipping -->
            <button class="step-item" id="step-shipping" onclick="selectStep('shipping', 2)" type="button">
              <div class="step-circle">3</div>
              <div class="step-title">Shipping</div>
              <div class="step-subtitle">Pending dispatch</div>
            </button>

            <!-- Step 4: Out for delivery -->
            <button class="step-item" id="step-out_for_delivery" onclick="selectStep('out_for_delivery', 3)"
              type="button">
              <div class="step-circle">4</div>
              <div class="step-title">Out for delivery</div>
              <div class="step-subtitle">Local courier hub</div>
            </button>

            <!-- Step 5: Delivered -->
            <button class="step-item" id="step-delivered" onclick="selectStep('delivered', 4)" type="button">
              <div class="step-circle">5</div>
              <div class="step-title">Delivered</div>
              <div class="step-subtitle">Customer signature</div>
            </button>
          </div>

          <div class="status-save-bar">
    <div>
        Click any stage above to preview and click
        <strong>"Save Status"</strong> to apply changes to customer tracking.
    </div>

    {{-- COD cash collection confirmation --}}
    @if(strtolower(trim($order->payment_method ?? '')) === 'cod')
        <div class="cod-payment-confirmation">
            <label>
                <input
                    type="checkbox"
                    name="cod_payment_collected"
                    value="1"
                    form="orderStatusForm"
                    {{ strtolower($order->payment_status ?? '') === 'paid' ? 'checked' : '' }}
                >
                <strong>Confirm COD payment collected</strong>
            </label>

            <small>
                Check this only after receiving cash from the customer.
            </small>
        </div>
    @endif

    <div class="toast-msg" id="toastMessage">
        <svg width="16" height="16" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M20 6 9 17l-5-5"/>
        </svg>
        Status updated successfully!
    </div>
</div>
        </section>

        <!-- Two-Column Order Information Grid -->
        <div class="order-details-grid">
          <!-- Left: Items & Billing -->
          <div style="display: flex; flex-direction: column; gap: 24px;">
            <!-- Items Table Card -->
            <div class="card">
              <div class="card-header">
                <h2 class="card-title">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="m7.5 4.27 9 5.15" />
                    <path
                      d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z" />
                  </svg>
                  Ordered Items ({{ $order->items->count() }} products)
                </h2>
              </div>
              <div class="table-responsive">
                <table class="items-table">
                  <thead>
                    <tr>
                      <th>Product</th>
                      <th>Variant</th>
                      <th>Price</th>
                      <th>Qty</th>
                      <th>Subtotal</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse ($order->items as $item)
                    <tr>
                      <td>
                        <div class="product-title-cell">{{ $item->product_name }}</div>
                        <div class="product-category-sub">Category: {{ $item->product?->category?->name ?? '—' }}</div>
                      </td>
                      <td>
                        <span class="variant-badge-chip">{{ $item->variant?->values?->pluck('value')->join(' • ') ?: '—' }}</span>
                      </td>
                      <td>₹{{ number_format($item->price, 2) }}</td>
                      <td><strong>{{ $item->quantity }}</strong></td>
                      <td><strong>₹{{ number_format($item->subtotal, 2) }}</strong></td>
                    </tr>
                    @empty
                    <tr><td colspan="5">No items found for this order.</td></tr>
                    @endforelse
                  </tbody>
                </table>
              </div>

              <!-- Bill Calculations (Consistent Sample Numbers) -->
              <div class="bill-calc-rows">
                <div class="bill-row">
                  <span>Items Subtotal ({{ $order->items->sum('quantity') }} items)</span>
                  <span>                  
                          ₹{{ number_format($order->subtotal, 2) }}</span>
                </div>
                <div class="bill-row">
                  <span>Standard Express Delivery Charge</span>
                  <span>₹{{ number_format($order->delivery_fee, 2) }}</span>
                </div>
                
                <div class="bill-row total-row">
                  <span>Total Bill Amount</span>
                  <span style="color: var(--primary-teal);">                   
                         ₹{{ number_format($order->total_amount, 2) }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Right: Customer, Delivery Address, Payment -->
          <div class="side-cards-col">
            <!-- Customer Card -->
            <div class="card">
              <div class="card-header">
                <h2 class="card-title">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                    <circle cx="12" cy="7" r="4" />
                  </svg>
                  Customer Details
                </h2>
              </div>
              <div class="card-body">
                <div class="info-list">
                  <div class="info-item">
                    <span class="info-label">Customer Name</span>
                    <span class="info-value">{{ $order->customer_name }}</span>
                  </div>
                  <div class="info-item">
                    <span class="info-label">Email Address</span>
                    <span class="info-value">{{ $order->user?->email ?? '—' }}</span>
                  </div>
                  <div class="info-item">
                    <span class="info-label">Phone Number</span>
                    <span class="info-value">{{ $order->phone }}</span>
                  </div>
                  <div class="info-item">
                    <span class="info-label">Account Tier</span>
                    <span class="info-value">{{ $order->user?->orders_count ?? $order->user?->orders()->count() ?? 0 }} Orders</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Delivery Address Card -->
            <div class="card">
              <div class="card-header">
                <h2 class="card-title">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                    <circle cx="12" cy="10" r="3" />
                  </svg>
                  Delivery Address
                </h2>
              </div>
              <div class="card-body">
                <div class="address-box">
                                     <strong>{{ $order->customer_name }}</strong><br>
                   {{ $order->address }}<br>
                   @if($order->landmark) {{ $order->landmark }}<br> @endif
                   {{ $order->city }}, {{ $order->state }} – {{ $order->postal_code }}<br>
                   <span style="font-size: 12px; color: var(--muted); margin-top: 6px; display: block;">
                     Contact: {{ $order->phone }}
                   </span>
                 </div>
                  </div>
                  <center>
                  
<div class="info-item">
    <span class="info-label">Payment Method</span>
    <span class="info-value">
        {{ ucfirst($order->payment_method) }}
    </span>
</div>
<div class="info-item">
    <span class="info-label">Payment ID</span>
    <span class="info-value" style="font-size: 12px; overflow-wrap: anywhere;">
        {{ $order->payment_id ?: '—' }}
    </span>
</div>

<div class="info-item">
    <span class="info-label">Payment Status</span>
    <span class="info-value">
        {{ ucfirst($order->payment_status) }}
    </span>
</div>

<div class="info-item">
    <span class="info-label">Collection Amount</span>
    <span class="info-value"
          style="font-size: 16px; font-weight: 800;">
        ₹{{ number_format($order->total_amount, 2) }}
    </span>
</div>

                </center>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>

  <!-- Email confirmation toast -->
  <div class="email-toast" id="emailToast" role="status" aria-live="polite">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
      <path d="M20 6 9 17l-5-5" />
    </svg>
    <span id="emailToastText">Email sent</span>
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

    // Status Configuration matching the 5 specified stages
    const statusConfig = {
      received: {
        index: 0,
        label: 'Received',
        badgeClass: 'badge-received',
        fillPercent: '0%'
      },
      process: {
        index: 1,
        label: 'Process',
        badgeClass: 'badge-process',
        fillPercent: '25%'
      },
      shipping: {
        index: 2,
        label: 'Shipping',
        badgeClass: 'badge-shipping',
        fillPercent: '50%'
      },
      out_for_delivery: {
        index: 3,
        label: 'Out for delivery',
        badgeClass: 'badge-out_for_delivery',
        fillPercent: '75%'
      },
      delivered: {
        index: 4,
        label: 'Delivered',
        badgeClass: 'badge-delivered',
        fillPercent: '100%'
      }
    };

    const statusKeys = ['received', 'process', 'shipping', 'out_for_delivery', 'delivered'];
    let currentStatus = @json($currentStatus);

    function selectStep(statusKey, index) {
      currentStatus = statusKey;
      const config = statusConfig[statusKey];
      document.getElementById('orderStatusInput').value = statusKey;

      // Update Connecting Bar
      const lineFill = document.getElementById('stepperLineFill');
      if (lineFill) {
        lineFill.style.width = config.fillPercent;
      }

      // Update Steps classes
      statusKeys.forEach((key, i) => {
        const stepElem = document.getElementById('step-' + key);
        if (!stepElem) return;

        stepElem.classList.remove('completed', 'active');
        const circle = stepElem.querySelector('.step-circle');

        if (i < index) {
          stepElem.classList.add('completed');
          circle.innerHTML = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>`;
        } else if (i === index) {
          stepElem.classList.add('active');
          circle.textContent = (i + 1);
        } else {
          circle.textContent = (i + 1);
        }
      });

      // Update Header Badge Live
      const headerBadge = document.getElementById('headerStatusBadge');
      const headerText = document.getElementById('headerStatusText');
      const labelElem = document.getElementById('currentSelectedStatusLabel');

      if (headerBadge && headerText) {
        headerBadge.className = 'badge ' + config.badgeClass;
        headerText.textContent = config.label;
      }

      if (labelElem) {
        labelElem.textContent = config.label;
      }
    }

         // Initialize the tracker from the status stored in the database.
     selectStep(currentStatus, statusConfig[currentStatus].index);
</script>
</body>

</html>