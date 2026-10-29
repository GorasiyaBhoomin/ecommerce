<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Checkout &amp; Order Placement | Shopkart</title>
  <!-- Google Fonts: Plus Jakarta Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/checkout.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/user-navbar.css') }}">
</head>

<body>

  @include('user.partials.navbar')
  @if(session('success'))
    <div style="padding:12px; background:#d1fae5; color:#065f46;">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div style="padding:12px; background:#fee2e2; color:#991b1b;">
        {{ session('error') }}
    </div>
@endif

@if($errors->any())
    <div style="padding:12px; background:#fee2e2; color:#991b1b;">
        {{ $errors->first() }}
    </div>
@endif
  <!-- Main Content -->
  <main class="main-content">
    <div class="page-title-row">
      <h1 class="page-heading">Review Order &amp; Checkout</h1>
      <div class="secure-badge">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect width="18" height="11" x="3" y="11" rx="2" ry="2" />
          <path d="M7 11V7a5 5 0 0 1 10 0v4" />
        </svg>
        <span>256-Bit SSL Encrypted &bull; Cash on Delivery Available</span>
      </div>
    </div>

    <div class="checkout-grid">
      <!-- Left Column: Cart, Address, Payment -->
      <div class="checkout-forms-col">
        <!-- 1. Cart Items Review -->
        <section class="checkout-card" aria-labelledby="cartReviewTitle">
          <div class="card-header">
            <h2 class="card-title" id="cartReviewTitle">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="8" cy="21" r="1" />
                <circle cx="19" cy="21" r="1" />
                <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12" />
              </svg>
Step 1 &bull; Items in Your Order ({{ $cartItems->count() }} Products)            </h2>
          </div>
          <div class="card-body" id="cartItemsList">

    @forelse($cartItems as $item)

                  @php
              $price = $item->variant?->discounted_price
                  ?? $item->variant?->actual_price
                  ?? 0;

              $actualPrice = $item->variant?->actual_price ?? $price;

              $itemSubtotal = $price * $item->quantity;
          @endphp

        <div class="cart-item-row" id="cartRow{{ $item->id }}">

            <!-- Product Information -->
            <div class="item-meta">

    <div class="item-name">
        {{ $item->product?->name ?? 'Product unavailable' }}
    </div>

    <div class="item-variant-tag">
        @if($item->variant)
            {{ $item->variant->values->pluck('value')->join(' • ') }}
        @endif
    </div>

    <div class="item-price-unit">

        <strong>
            ₹{{ number_format($price, 2) }}
        </strong>

        @if($actualPrice > $price)
            <del style="color:#999; margin-left:8px;">
                ₹{{ number_format($actualPrice, 2) }}
            </del>
        @endif

        <span>each</span>

    </div>

</div>

            <!-- Quantity -->
            <form action="{{ route('cart.update', $item->id) }}"
                  method="POST"
                  class="qty-box">

                @csrf

                <button class="qty-btn"
                        type="button"
                        onclick="changeCartQty(this, -1)">
                    &minus;
                </button>

                <input type="number"
                       name="quantity"
                       class="qty-input"
                       id="itemQty{{ $item->id }}"
                       value="{{ $item->quantity }}"
                       min="1"
                       max="{{ $item->variant?->stock_qty ?? 1 }}"
                       readonly>

                <button class="qty-btn"
                        type="button"
                        onclick="changeCartQty(this, 1)">
                    &#43;
                </button>

            </form>

            <!-- Item Subtotal -->
            <div class="item-subtotal" id="itemSubtotal{{ $item->id }}">
                &#8377;{{ number_format($itemSubtotal, 0) }}
            </div>

            <!-- Remove Product -->
            <form action="{{ route('cart.remove', $item->id) }}"
                  method="POST">

                @csrf

                <button class="remove-btn"
                        type="submit"
                        title="Remove item"
                        aria-label="Remove product">

                    <svg width="18" height="18"
                         viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="2">

                        <path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />

                    </svg>

                </button>

            </form>

        </div>

    @empty

        <div style="text-align:center; padding:30px;">

            <h3>Your cart is empty!</h3>

            <p>Add some products to your cart.</p>

            <a href="{{ route('shop.index') }}">
                Continue Shopping
            </a>

        </div>

    @endforelse

</div>
        </section>

        <!-- 2. Delivery Address Form -->
       
<!-- Delivery Address -->
<section class="checkout-card">

    <div class="card-header">
        <h2 class="card-title">
            Step 2 • Delivery Address
        </h2>
    </div>

    <div class="card-body">

        <div class="form-grid-2">

            <div class="form-group">
                <label class="form-label">
                    Full Name *
                </label>

                <input
                    type="text"
                    name="customer_name"
                    form="checkoutForm"
                    class="form-input"
                    value="{{ old('customer_name', $user->name) }}"
                    required>
            </div>

            <div class="form-group">
                <label class="form-label">
                    Phone Number *
                </label>

                <input
                    type="tel"
                    name="phone"
                    form="checkoutForm"
                    class="form-input"
                    value="{{ old('phone', $user->phone) }}"
                    required>
            </div>

        </div>

        <div class="form-group">
            <label class="form-label">
                Address *
            </label>

            <input
                type="text"
                name="address"
                form="checkoutForm"
                class="form-input"
                value="{{ old('address', $user->address) }}"
                required>
        </div>

        <div class="form-grid-2">

            <div class="form-group">
                <label class="form-label">
                    City *
                </label>

                <input
                    type="text"
                    name="city"
                    form="checkoutForm"
                    class="form-input"
                    value="{{ old('city', $user->city) }}"
                    required>
            </div>

            <div class="form-group">
                <label class="form-label">
                    State *
                </label>

                <input
                    type="text"
                    name="state"
                    form="checkoutForm"
                    class="form-input"
                    value="{{ old('state', $user->state) }}"
                    required>
            </div>

        </div>

        <div class="form-grid-2">

            <div class="form-group">
                <label class="form-label">
                    PIN Code *
                </label>

                <input
                    type="text"
                    name="postal_code"
                    form="checkoutForm"
                    class="form-input"
                    value="{{ old('postal_code', $user->postal_code) }}"
                    pattern="[0-9]{6}"
                    maxlength="6"
                    required>
            </div>

            <div class="form-group">
                <label class="form-label">
                    Landmark (Optional)
                </label>

                <input
                    type="text"
                    name="landmark"
                    form="checkoutForm"
                    class="form-input"
                    value="{{ old('landmark') }}">
            </div>

        </div>

    </div>

</section>


        <!-- 3. Payment Method -->
        <section class="checkout-card" aria-labelledby="paymentMethodTitle">
          <div class="card-header">
            <h2 class="card-title" id="paymentMethodTitle">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect width="20" height="14" x="2" y="5" rx="2" />
                <line x1="2" x2="22" y1="10" y2="10" />
              </svg>
              Step 3 &bull; Payment Method
            </h2>
          </div>
          <div class="card-body">
            <div class="payment-options-list">
              <!-- Option 1: Cash on Delivery -->
              <div class="payment-option-card active" id="payOptCod" onclick="selectPayment('cod')">
                <div class="pay-opt-left">
                  <input type="radio" name="paymentMethod" id="radioCod" checked>
                  <div>
                    <div class="pay-opt-title">Cash on Delivery (COD)</div>
                  </div>
                </div>
              </div>

              <!-- Option 2: Credit/Debit Card (Stripe) -->
              <div class="payment-option-card" id="payOptStripe" onclick="selectPayment('stripe')">
                <div class="pay-opt-left">
                  <input type="radio" name="paymentMethod" id="radioStripe">
                  <div>
                    <div class="pay-opt-title">Credit / Debit Card (Stripe)</div>
                  </div>
                </div>
                <div class="pay-opt-right">
                    <img src="https://coresg-normal.trae.ai/api/ide/v1/text_to_image?prompt=visa+mastercard+stripe+logo+icons+payment+methods&image_size=landscape_4_3" 
                         alt="Cards" style="height: 24px;">
                </div>
              </div>
            
            </div>
          </div>
        </section>
      </div>
<form id="checkoutForm"
      action="{{ route('orders.place') }}"
      method="POST">
    @csrf

    <input type="hidden"
           name="payment_method"
           value="cod">
</form>
      <!-- Right Column: Order Summary Card -->
      <aside class="summary-col">
       <div class="summary-card">
@if($totalSavings > 0)

    <div class="savings-badge-box">

        <svg width="16" height="16"
             viewBox="0 0 24 24"
             fill="none"
             stroke="currentColor"
             stroke-width="2.5">

            <polyline points="20 6 9 17 4 12" />

        </svg>

        <span>
            You are saving
            ₹{{ number_format($totalSavings, 2) }}
            on this order
        </span>

    </div>

@endif
    <h2 class="summary-title">Order Bill Summary</h2>

    <div class="calc-row">
        <span>Items Subtotal</span>

        <span id="billSubtotal">
            &#8377;{{ number_format($subtotal, 0) }}
        </span>
    </div>

    <div class="calc-row">
        <span>Standard Express Delivery</span>

        <span id="billDelivery">
            @if($deliveryFee > 0)
                &#8377;{{ number_format($deliveryFee, 0) }}
            @else
                <span style="color: #166534; font-weight: 700;">FREE</span>
            @endif
        </span>
    </div>

    <div class="calc-row total-row">

        <span>Total Bill Amount</span>

        <span style="color: var(--primary-teal);" id="billTotal">
            &#8377;{{ number_format($total, 0) }}
        </span>

    </div>

   <button type="submit"
        form="checkoutForm"
        class="btn-place-order"
        @if($cartItems->isEmpty()) disabled @endif>

    Place Order •
    ₹{{ number_format($total, 0) }}

</button>

    <div style="font-size: 11px; color: var(--muted); text-align: center; line-height: 1.4;">
        By placing your order, you agree to Shopkart's Terms of Sale &amp; Delivery Policies.
    </div>

</div>
      </aside>
    </div>
  </main>

  <!-- Order Confirmation Modal -->
  <div class="modal-overlay" id="orderConfirmModal">
    <div class="modal-box">
      <div class="success-icon-circle">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
          <polyline points="20 6 9 17 4 12" />
        </svg>
      </div>
      <div>
        <h2 style="font-size: 20px; font-weight: 800; color: var(--ink);">Order Placed Successfully!</h2>
        <div style="font-size: 14px; color: var(--muted); margin-top: 6px;">
          Order ID: <strong style="color: var(--primary-teal);">#SK-1048</strong>
        </div>
      </div>
      <p style="font-size: 13px; color: var(--muted); line-height: 1.5;">
        Thank you, Hiral Patel! Your order has been received and scheduled for dispatch to 402, Shivalik Highstreet,
        Vastrapur, Ahmedabad. Total to collect on delivery: <strong>&#8377;26,958</strong>.
      </p>
      <div style="display: flex; gap: 12px; margin-top: 8px;">
        <a href="/myorders" class="modal-btn-track">
          Track Order Status &rarr;
        </a>
      </div>
    </div>
  </div>

  <script>
    function selectPayment(mode) {
      document.getElementById('payOptCod').classList.toggle('active', mode === 'cod');
      document.getElementById('payOptStripe').classList.toggle('active', mode === 'stripe');
      
      document.getElementById('radioCod').checked = (mode === 'cod');
      document.getElementById('radioStripe').checked = (mode === 'stripe');
      
      document.querySelector('input[name="payment_method"]').value = mode;
    }

    function changeCartQty(button, change) {
        const form = button.closest('form');
        const input = form.querySelector('input[name="quantity"]');
        const currentQty = parseInt(input.value, 10);
        const maxQty = parseInt(input.getAttribute('max'), 10) || 99;
        const newQty = currentQty + change;

        if (newQty < 1 || newQty > maxQty) {
            return;
        }

        input.value = newQty;
        form.submit();
    }
  </script>
</body>

</html>