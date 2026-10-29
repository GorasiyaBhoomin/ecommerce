<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Invoice SK-{{ $order->id }}</title>
  @php
    $payStatus = strtolower($order->payment_status ?? 'pending');
    $badge = match ($payStatus) {
        'paid'     => ['PAID',     '#166534', '#dcfce7'],
        'refunded' => ['REFUNDED', '#991b1b', '#fee2e2'],
        'failed'   => ['FAILED',   '#991b1b', '#fee2e2'],
        default    => [strtoupper($order->payment_method) === 'COD' ? 'PAY ON DELIVERY' : 'PAYMENT PENDING', '#92400e', '#fef3c7'],
    };
  @endphp
  <style>
    @page { margin: 0; }
    * { box-sizing: border-box; }
    body {
      font-family: 'DejaVu Sans', sans-serif;
      font-size: 11px;
      color: #0f172a;
      margin: 0;
      line-height: 1.5;
    }

    /* ---------- Header band ---------- */
    .band { background: #0f766e; color: #ffffff; padding: 32px 40px 28px 40px; }
    .band table { width: 100%; border-collapse: collapse; }
    .brand { font-size: 26px; font-weight: bold; letter-spacing: 0.5px; }
    .tagline { font-size: 10px; color: #99f6e4; margin-top: 2px; letter-spacing: 1px; }
    .inv-title { font-size: 28px; font-weight: bold; letter-spacing: 4px; text-align: right; }
    .inv-no { text-align: right; font-size: 12px; color: #ccfbf1; margin-top: 2px; }

    .accent { height: 5px; background: #14b8a6; }

    /* ---------- Body ---------- */
    .wrap { padding: 26px 40px 90px 40px; }

    .meta { width: 100%; border-collapse: collapse; margin-bottom: 22px; }
    .meta td { vertical-align: top; padding: 0; }
    .card {
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 6px;
      padding: 14px 16px;
    }
    .label {
      font-size: 9px;
      font-weight: bold;
      color: #0f766e;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      margin-bottom: 6px;
    }
    .name { font-size: 13px; font-weight: bold; margin-bottom: 2px; }
    .muted { color: #64748b; }
    .kv { width: 100%; border-collapse: collapse; }
    .kv td { padding: 2px 0; font-size: 10.5px; }
    .kv td.k { color: #64748b; width: 42%; }
    .kv td.v { font-weight: bold; text-align: right; word-break: break-all; }

    .badge {
      display: inline-block;
      padding: 3px 10px;
      border-radius: 10px;
      font-size: 9px;
      font-weight: bold;
      letter-spacing: 1px;
    }

    /* ---------- Items ---------- */
    table.items { width: 100%; border-collapse: collapse; margin-top: 4px; }
    .items thead th {
      background: #0f172a;
      color: #ffffff;
      font-size: 9.5px;
      text-transform: uppercase;
      letter-spacing: 1px;
      padding: 10px 10px;
      text-align: left;
    }
    .items tbody td { padding: 11px 10px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
    .items tbody tr.alt td { background: #f8fafc; }
    .items .num { text-align: right; white-space: nowrap; }
    .items .center { text-align: center; }
    .pname { font-weight: bold; font-size: 11.5px; }
    .variant { font-size: 9.5px; color: #64748b; margin-top: 2px; }

    /* ---------- Totals ---------- */
    .bottom { width: 100%; border-collapse: collapse; margin-top: 22px; }
    .bottom td { vertical-align: top; padding: 0; }
    .note-box {
      border-left: 3px solid #14b8a6;
      background: #f0fdfa;
      padding: 10px 14px;
      font-size: 10px;
      color: #134e4a;
    }
    table.totals { width: 100%; border-collapse: collapse; }
    .totals td { padding: 6px 4px; font-size: 11px; }
    .totals td.r { text-align: right; font-weight: bold; }
    .totals tr.sep td { border-top: 1px solid #e2e8f0; }
    .grand td {
      background: #0f766e;
      color: #ffffff;
      font-size: 14px;
      font-weight: bold;
      padding: 10px 12px;
    }

    /* ---------- Footer ---------- */
    .footer {
      position: fixed;
      bottom: 0; left: 0; right: 0;
      padding: 14px 40px;
      border-top: 3px solid #0f766e;
      background: #f8fafc;
      text-align: center;
      font-size: 9.5px;
      color: #64748b;
    }
    .footer strong { color: #0f766e; }
  </style>
</head>
<body>

  {{-- Header --}}
  <div class="band">
    <table>
      <tr>
        <td>
          <div class="brand">Shopkart</div>
          <div class="tagline">SHOP SMART. LIVE BETTER.</div>
        </td>
        <td>
          <div class="inv-title">INVOICE</div>
          <div class="inv-no">#SK-{{ $order->id }}</div>
        </td>
      </tr>
    </table>
  </div>
  <div class="accent"></div>

  <div class="wrap">

    {{-- Billing + Order info --}}
    <table class="meta">
      <tr>
        <td style="width: 52%; padding-right: 10px;">
          <div class="card">
            <div class="label">Billed &amp; Shipped To</div>
            <div class="name">{{ $order->customer_name }}</div>
            <div>
              {{ $order->address }}<br>
              @if($order->landmark) {{ $order->landmark }}<br> @endif
              {{ $order->city }}, {{ $order->state }} &ndash; {{ $order->postal_code }}
            </div>
            <div class="muted" style="margin-top: 6px;">
              Phone: {{ $order->phone }}
              @if($order->user?->email)<br>Email: {{ $order->user->email }}@endif
            </div>
          </div>
        </td>
        <td style="width: 48%; padding-left: 10px;">
          <div class="card">
            <div class="label">Order Details</div>
            <table class="kv">
              <tr><td class="k">Invoice date</td><td class="v">{{ $order->created_at->format('d M Y') }}</td></tr>
              <tr><td class="k">Order time</td><td class="v">{{ $order->created_at->format('h:i A') }}</td></tr>
              <tr><td class="k">Payment method</td><td class="v">{{ strtoupper($order->payment_method) }}</td></tr>
              @if($order->payment_id)
                <tr><td class="k">Payment ID</td><td class="v" style="font-size: 8.5px;">{{ $order->payment_id }}</td></tr>
              @endif
              <tr><td class="k">Order status</td><td class="v">{{ ucwords(str_replace('_', ' ', $order->order_status)) }}</td></tr>
              <tr>
                <td class="k">Payment</td>
                <td class="v">
                  <span class="badge" style="color: {{ $badge[1] }}; background: {{ $badge[2] }};">{{ $badge[0] }}</span>
                </td>
              </tr>
            </table>
          </div>
        </td>
      </tr>
    </table>

    {{-- Items --}}
    <table class="items">
      <thead>
        <tr>
          <th style="width: 6%;">#</th>
          <th>Product</th>
          <th class="num" style="width: 16%; text-align: right;">Price</th>
          <th class="center" style="width: 8%; text-align: center;">Qty</th>
          <th class="num" style="width: 18%; text-align: right;">Amount</th>
        </tr>
      </thead>
      <tbody>
        @foreach($order->items as $i => $item)
          <tr class="{{ $i % 2 ? 'alt' : '' }}">
            <td class="muted">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</td>
            <td>
              <div class="pname">{{ $item->product_name }}</div>
              @php $variantText = $item->variant?->values?->pluck('value')->join(' / '); @endphp
              @if($variantText)
                <div class="variant">{{ $variantText }}</div>
              @endif
            </td>
            <td class="num">&#8377;{{ number_format($item->price, 2) }}</td>
            <td class="center">{{ $item->quantity }}</td>
            <td class="num"><strong>&#8377;{{ number_format($item->subtotal, 2) }}</strong></td>
          </tr>
        @endforeach
      </tbody>
    </table>

    {{-- Notes + Totals --}}
    <table class="bottom">
      <tr>
        <td style="width: 52%; padding-right: 20px;">
          <div class="note-box">
            <strong>Thank you for your order!</strong><br>
            @if($payStatus === 'paid')
              Payment received in full. No further payment is due.
            @elseif($payStatus === 'refunded')
              This order has been refunded to the original payment method.
            @elseif(strtolower($order->payment_method) === 'cod')
              Please keep the exact amount ready to pay at delivery.
            @else
              Payment for this order is awaiting confirmation.
            @endif
          </div>
        </td>
        <td style="width: 48%;">
          <table class="totals">
            <tr>
              <td>Subtotal</td>
              <td class="r">&#8377;{{ number_format($order->subtotal, 2) }}</td>
            </tr>
            <tr>
              <td>Delivery</td>
              <td class="r">{{ $order->delivery_fee > 0 ? '₹'.number_format($order->delivery_fee, 2) : 'FREE' }}</td>
            </tr>
            <tr class="sep"><td colspan="2" style="padding: 0;"></td></tr>
            <tr class="grand">
              <td>Total</td>
              <td style="text-align: right;">&#8377;{{ number_format($order->total_amount, 2) }}</td>
            </tr>
          </table>
        </td>
      </tr>
    </table>

  </div>

  {{-- Footer --}}
  <div class="footer">
    <strong>Shopkart</strong> &bull; This is a computer-generated invoice and does not require a signature.<br>
    Questions about your order? Contact support with your order number #SK-{{ $order->id }}.
  </div>

</body>
</html>