<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="color-scheme" content="light">
<meta name="supported-color-schemes" content="light">
<title>Order #{{ $order->id }} | SHOP KART</title>
<style>
  /* ---------- Reset ---------- */
  body, table, td, a { -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; }
  table, td { mso-table-lspace:0; mso-table-rspace:0; border-collapse:collapse; }
  img { border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic; }
  body { margin:0 !important; padding:0 !important; width:100% !important; background:#eef1f6; }

  /* ---------- Base ---------- */
  .body-bg   { background:#eef1f6; }
  .wrapper   { padding:32px 12px; }
  .container { width:640px; max-width:640px; background:#ffffff; border-radius:18px; overflow:hidden;
               box-shadow:0 8px 30px rgba(23,35,56,.10); }
  body, td, th, p, div { font-family:'Segoe UI',Roboto,Helvetica,Arial,sans-serif; color:#172338; }

  /* ---------- Header ---------- */
  .header       { background:#2874f0; background-image:linear-gradient(135deg,#1a5fd0 0%,#2874f0 55%,#4a90ff 100%); padding:30px 36px 26px; }
  .logo         { color:#ffffff; font-size:26px; font-weight:800; letter-spacing:1.5px; }
  .logo span    { color:#ffe500; }
  .header-tag   { text-align:right; font-size:11px; font-weight:700; letter-spacing:2px; color:#ffe500; }
  .tagline      { color:#c9d8f5; font-size:11px; letter-spacing:1.5px; margin-top:8px; }
  .accent-bar   { height:4px; background:#ffe500; font-size:0; line-height:0; }

  /* ---------- Hero ---------- */
  .hero         { padding:38px 36px 26px; text-align:center; }
  .eyebrow      { font-size:11px; font-weight:700; letter-spacing:2.5px; color:#2874f0; text-transform:uppercase; }
  .hero h1      { font-size:28px; line-height:1.25; margin:12px 0 10px; color:#172337; font-weight:800; }
  .hero p       { margin:0; font-size:14px; line-height:1.8; color:#68768a; }

  /* ---------- Summary card ---------- */
  .section      { padding:0 36px 26px; }
  .summary      { background:#f5f8fd; border:1px solid #e3eaf5; border-radius:12px; }
  .summary td   { padding:16px 18px; vertical-align:top; }
  .label        { font-size:10px; font-weight:700; letter-spacing:1.2px; color:#8390a1; text-transform:uppercase; }
  .value        { font-size:15px; font-weight:700; margin-top:6px; color:#172338; }
  .pill         { display:inline-block; padding:5px 12px; border-radius:999px; font-size:12px; font-weight:700; margin-top:6px; letter-spacing:.3px; }

  /* ---------- Headings ---------- */
  .h2           { font-size:18px; font-weight:800; margin:0 0 14px; color:#172338; }
  .h2 small     { font-size:12px; font-weight:500; color:#8591a0; }

  /* ---------- Items ---------- */
  .items th     { background:#f3f6fa; padding:12px; font-size:10.5px; font-weight:700; letter-spacing:1px; color:#607087; text-transform:uppercase; }
  .items th:first-child { border-radius:8px 0 0 8px; }
  .items th:last-child  { border-radius:0 8px 8px 0; }
  .items td     { padding:16px 12px; border-bottom:1px solid #edf0f5; font-size:13px; vertical-align:top; }
  .item-name    { font-size:14px; font-weight:700; line-height:1.4; color:#172338; }
  .item-meta    { font-size:12px; color:#718095; margin-top:5px; }
  .item-amount  { font-size:14px; font-weight:800; white-space:nowrap; }
  .qty-badge    { display:inline-block; min-width:26px; padding:3px 8px; background:#eef3fc; color:#2874f0; border-radius:6px; font-weight:700; }

  /* ---------- Totals ---------- */
  .totals td    { padding:9px 0; font-size:14px; color:#64748b; }
  .totals td.r  { text-align:right; color:#172338; font-weight:600; }
  .grand        { background:#2874f0; background-image:linear-gradient(135deg,#1a5fd0,#2874f0); border-radius:12px; }
  .grand td     { padding:20px 22px; }
  .grand .t     { color:#ffe500; font-size:12px; font-weight:800; letter-spacing:1px; text-transform:uppercase; }
  .grand .a     { color:#ffffff; font-size:26px; font-weight:800; text-align:right; }

  /* ---------- Info cards ---------- */
  .card         { background:#f5f7fa; border:1px solid #e8edf4; border-radius:12px; padding:18px; vertical-align:top; }
  .card-title   { color:#2874f0; font-size:11px; font-weight:800; letter-spacing:1.2px; margin-bottom:10px; text-transform:uppercase; }
  .card-body    { font-size:13px; line-height:1.9; color:#2b3a50; word-break:break-word; overflow-wrap:anywhere; }
  .gap          { width:14px; }

  /* ---------- Payment ---------- */
  .box          { border:1px solid #e6ebf2; border-left:4px solid #2874f0; border-radius:12px; }
  .box td       { padding:18px 20px; }
  .box p        { font-size:13px; line-height:1.95; margin:10px 0 0; color:#2b3a50; }

  /* ---------- Help / Footer ---------- */
  .help         { background:#fffbe5; padding:24px 30px; text-align:center; border-top:1px solid #f6ecb0; }
  .help .t      { font-size:17px; font-weight:800; }
  .help p       { font-size:13px; color:#786d5d; line-height:1.7; margin:8px 0 0; }
  .footer       { background:#172338; padding:28px 30px; text-align:center; color:#9aa7bb; font-size:11px; line-height:2; }
  .footer .logo { font-size:20px; }

  /* ---------- Mobile ---------- */
  @media only screen and (max-width:640px) {
    .wrapper   { padding:0 !important; }
    .container { border-radius:0 !important; width:100% !important; }
    .header, .hero, .section { padding-left:20px !important; padding-right:20px !important; }
    .hero h1   { font-size:23px !important; }
    .stack     { display:block !important; width:100% !important; box-sizing:border-box; }
    .gap       { display:block !important; width:100% !important; height:12px !important; }
    .grand .a  { font-size:21px !important; }
    .summary td { padding:14px !important; }
  }
</style>
</head>
<body>
@php
  $status = strtolower((string) $order->order_status);
  $statusText = ucwords(str_replace('_',' ',$status));
  $paymentText = strtoupper((string) $order->payment_method) === 'COD' ? 'Cash on Delivery' : ucwords(str_replace('_',' ',(string) $order->payment_method));
  $paymentStatus = ucwords(str_replace('_',' ',(string) $order->payment_status));
  $items = $order->items ?? collect();
  $itemCount = $items->sum('quantity');

  // Status badge colors: [text, background]
  $palette = [
    'pending'    => ['#a15c00', '#fff3dc'],
    'processing' => ['#1a5fd0', '#e6efff'],
    'confirmed'  => ['#1a5fd0', '#e6efff'],
    'shipped'    => ['#6d3fc0', '#efe8fc'],
    'delivered'  => ['#0e8555', '#e2f6ec'],
    'cancelled'  => ['#c0392b', '#fdeaea'],
    'refunded'   => ['#c0392b', '#fdeaea'],
  ];
  [$statusColor, $statusBg] = $palette[$status] ?? ['#0e8555', '#e2f6ec'];

  $payKey = strtolower((string) $order->payment_status);
  [$payColor, $payBg] = in_array($payKey, ['paid','success','completed'])
      ? ['#0e8555', '#e2f6ec']
      : (in_array($payKey, ['failed','refunded'])
          ? ['#c0392b', '#fdeaea']
          : ['#a15c00', '#fff3dc']);
@endphp

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="body-bg">
<tr><td align="center" class="wrapper">

<table role="presentation" width="640" cellpadding="0" cellspacing="0" class="container">

  <!-- Header -->
  <tr><td class="header">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>
      <td class="logo">SHOP <span>KART</span></td>
      <td class="header-tag">ORDER UPDATE</td>
    </tr></table>
    <div class="tagline">SHOP SMART. SHOP HAPPY.</div>
  </td></tr>
  <tr><td class="accent-bar">&nbsp;</td></tr>

  <!-- Hero -->
  <tr><td class="hero">
    <div class="eyebrow">Order Update</div>
    <h1>Your order update is here!</h1>
    <p>Hi {{ $order->customer_name }}, here are the details of your order and its latest status.</p>
  </td></tr>

  <!-- Summary -->
  <tr><td class="section">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="summary">
      <tr>
        <td width="50%" class="stack"><div class="label">Order Number</div><div class="value">#{{ $order->id }}</div></td>
        <td class="stack"><div class="label">Order Date</div><div class="value">{{ optional($order->created_at)->format('d M Y, h:i A') ?? 'N/A' }}</div></td>
      </tr>
      <tr>
        <td class="stack"><div class="label">Order Status</div><span class="pill" style="color:{{ $statusColor }};background:{{ $statusBg }};">{{ $statusText }}</span></td>
        <td class="stack"><div class="label">Payment Status</div><span class="pill" style="color:{{ $payColor }};background:{{ $payBg }};">{{ $paymentStatus }}</span></td>
      </tr>
    </table>
  </td></tr>

  <!-- Items -->
  <tr><td class="section" style="padding-bottom:10px;">
    <h2 class="h2">Items in your order <small>({{ $itemCount }} total)</small></h2>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="items">
      <tr>
        <th align="left">Product / Variant</th>
        <th align="center">Qty</th>
        <th align="right">Amount</th>
      </tr>
      @forelse($items as $item)
      <tr>
        <td>
          <div class="item-name">{{ $item->product->name ?? $item->product_name ?? 'Product' }}</div>
          @if($item->variant ?? false)<div class="item-meta">{{ $item->variant->name ?? $item->variant->sku ?? '' }}</div>@endif
          <div class="item-meta">Unit price: ₹{{ number_format((float) $item->price,2) }}</div>
        </td>
        <td align="center"><span class="qty-badge">{{ $item->quantity }}</span></td>
        <td align="right" class="item-amount">₹{{ number_format((float) $item->price * (int) $item->quantity,2) }}</td>
      </tr>
      @empty
      <tr><td colspan="3" style="padding:18px;color:#7b8796;text-align:center;">No line items were loaded for this order.</td></tr>
      @endforelse
    </table>
  </td></tr>

  <!-- Totals -->
  <tr><td class="section" style="padding-top:18px;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="totals">
      <tr><td>Subtotal</td><td class="r">₹{{ number_format((float) $order->subtotal,2) }}</td></tr>
      <tr><td>Delivery fee</td><td class="r">₹{{ number_format((float) $order->delivery_fee,2) }}</td></tr>
    </table>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="grand" style="margin-top:12px;">
      <tr><td class="t">Amount Payable / Order Total</td><td class="a">₹{{ number_format((float) $order->total_amount,2) }}</td></tr>
    </table>
  </td></tr>

  <!-- Customer & Delivery -->
  <tr><td class="section">
    <h2 class="h2">Customer &amp; delivery</h2>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>
      <td valign="top" width="50%" class="card stack">
        <div class="card-title">Customer</div>
        <div class="card-body">
          <strong>{{ $order->customer_name }}</strong><br>
          Phone: {{ $order->phone }}<br>
          Email: {{ $order->user->email ?? 'Not available' }}<br>
          Customer ID: #{{ $order->user_id }}
        </div>
      </td>
      <td class="gap">&nbsp;</td>
      <td valign="top" width="50%" class="card stack">
        <div class="card-title">Delivery Address</div>
        <div class="card-body">
          {{ $order->address }}<br>
          @if($order->landmark)Landmark: {{ $order->landmark }}<br>@endif
          {{ $order->city }}, {{ $order->state }}<br>
          PIN: {{ $order->postal_code }}
        </div>
      </td>
    </tr></table>
  </td></tr>

  <!-- Payment & Tracking -->
  <tr><td class="section" style="padding-bottom:32px;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="box"><tr><td>
      <div class="card-title" style="margin-bottom:0;">Payment &amp; Tracking</div>
      <p>
        Payment method: <strong>{{ $paymentText }}</strong><br>
        Payment status: <strong>{{ $paymentStatus }}</strong><br>
        Order status: <strong>{{ $statusText }}</strong><br>
        Last updated: <strong>{{ optional($order->updated_at)->format('d M Y, h:i A') ?? 'N/A' }}</strong>
      </p>
    </td></tr></table>
  </td></tr>

  <!-- Help -->
  <tr><td class="help">
    <div class="t">Need help with your order?</div>
    <p>Contact our support team and mention order #{{ $order->id }}.</p>
  </td></tr>

  <!-- Footer -->
  <tr><td class="footer">
    <div class="logo">SHOP <span>KART</span></div>
    <div>Great deals. Happy shopping.</div>
    <div>© {{ date('Y') }} SHOP KART. All rights reserved.</div>
  </td></tr>

</table>
</td></tr></table>
</body>
</html>