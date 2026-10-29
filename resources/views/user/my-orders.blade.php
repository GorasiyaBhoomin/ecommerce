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

<main class="main-content">

    <div class="page-header-row">
        <div>
            <h1 class="page-title">
                My Orders & Tracking
            </h1>
            <p>Track all your orders here.</p>
        </div>

        <div class="filter-pills-wrap">
            <button class="filter-pill active"
                    data-filter="all"
                    onclick="filterOrders('all')">
                All Orders ({{ $orders->count() }})
            </button>

            <button class="filter-pill"
                    data-filter="progress"
                    onclick="filterOrders('progress')">
                In Progress
                ({{ $orders->whereNotIn('order_status', ['delivered', 'cancelled'])->count() }})
            </button>

            <button class="filter-pill"
                    data-filter="delivered"
                    onclick="filterOrders('delivered')">
                Delivered
                ({{ $orders->where('order_status', 'delivered')->count() }})
            </button>
        </div>
    </div>

    @forelse($orders as $order)

        @php
            $status = strtolower($order->order_status ?? 'pending');

            $category = $status === 'delivered'
                ? 'delivered'
                : 'progress';

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

        <article class="order-card"
                 data-category="{{ $category }}">

            <!-- ORDER HEADER -->
            <div class="order-card-header">

                <div class="order-meta-group">

                    <span class="order-id-tag">
                        Order #SK-{{ $order->id }}
                    </span>

                    <span class="order-date-text">
                        Placed on
                        {{ $order->created_at->format('d M Y, h:i A') }}
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
                                    ₹{{ number_format($item->price, 2) }}
                                    each
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
                        Total Bill
                        (Subtotal ₹{{ number_format($order->subtotal, 2) }}
                        + Delivery {{ $order->delivery_fee > 0 ? '₹'.number_format($order->delivery_fee, 2) : 'FREE' }}):
                    </span>

                    <span class="total-bill-amount">
                        ₹{{ number_format($order->total_amount, 2) }}
                    </span>

                    <span style="font-size:12px;">
                        {{ strtoupper($order->payment_method) }}
                        • {{ ucfirst($order->payment_status) }}
                    </span>

                </div>

                <div class="card-actions-group">
<a href="{{ route('orders.invoice', $order->id) }}" class="btn-action-outline">
    Download Invoice
</a>
                    <button type="button"
                            class="btn-action-outline"
                            onclick="toggleDetails('drawer{{ $order->id }}')">
                        View Details
                    </button>

                </div>

            </div>

            <!-- DELIVERY DETAILS -->
            <div class="details-drawer"
                 id="drawer{{ $order->id }}">

                <strong>Delivery Address:</strong>

                {{ $order->address }},
                {{ $order->city }},
                {{ $order->state }} -
                {{ $order->postal_code }}

                <br>

                @if($order->landmark)
                    <strong>Landmark:</strong>
                    {{ $order->landmark }}
                    <br>
                @endif

                <strong>Recipient:</strong>
                {{ $order->customer_name }}
                ({{ $order->phone }})

                <br>

                <strong>Payment Status:</strong>
                {{ ucfirst($order->payment_status) }}

            </div>

        </article>

    @empty

        <div style="text-align:center; padding:60px 20px;">

            <h2>No Orders Found!</h2>

            <p>You haven't placed any orders yet.</p>

            <a href="{{ route('shop.index') }}"
               class="btn-action-primary">
                Start Shopping
            </a>

        </div>

    @endforelse

</main>


 
<script>
    function filterOrders(filter) {

        document.querySelectorAll('.filter-pill').forEach(button => {
            button.classList.toggle(
                'active',
                button.dataset.filter === filter
            );
        });

        document.querySelectorAll('.order-card').forEach(card => {

            const category = card.dataset.category;

            if (filter === 'all' || category === filter) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }

        });
    }

    function toggleDetails(drawerId) {

        const drawer = document.getElementById(drawerId);

        if (drawer) {
            drawer.classList.toggle('open');
        }

    }
</script>

</body>
</html>
