<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Profile &amp; Account Settings | Shopkart</title>
  <!-- Google Fonts: Plus Jakarta Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/profile.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/user-navbar.css') }}">
</head>
<body>

  @include('user.partials.navbar', ['active' => 'profile', 'search' => true])

  @php
      /** Split the stored full name into first / last for the profile forms. */
      $nameParts = preg_split('/\s+/', trim((string) $user->name)) ?: [];
      $firstName = $nameParts[0] ?? '';
      $lastName = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : '';
      $userInitials = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
      $userInitials = $userInitials !== '' ? $userInitials : 'SK';

      /** "City, State" for the hero card, or an empty string when both are missing. */
      $userLocation = trim(
          ($user->city ?? '') . (($user->city && $user->state) ? ', ' : '') . ($user->state ?? '')
      );

      /** The users table stores a single address, so this is 0 or 1. */
      $addressCount = $user->address ? 1 : 0;
  @endphp

  <!-- Main Profile Page Container -->
  <main class="main-content">
    
    <!-- Breadcrumb -->
    <nav class="breadcrumb-trail" aria-label="Breadcrumb">
      <a href="/index">Home</a>
      <span>&rsaquo;</span>
      <a href="/myorders">Account</a>
      <span>&rsaquo;</span>
      <span class="current">My Profile</span>
    </nav>

    @if (session('success'))
      <div class="profile-alert profile-alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
      <div class="profile-alert profile-alert-error">
        <ul style="margin: 0; padding-left: 18px;">
          @foreach ($errors->all() as $message)
            <li>{{ $message }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <!-- Profile Hero Card -->
    <section class="profile-hero-card" aria-label="Customer Profile Summary">
      <div class="profile-hero-left">
        <div class="profile-avatar-wrapper">
          <div class="profile-avatar-large" id="heroAvatar">{{ $userInitials }}</div>
          <button class="avatar-edit-btn" title="Change profile photo" onclick="document.getElementById('avatarFileInput').click()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
              <circle cx="12" cy="13" r="4"/>
            </svg>
          </button>
          <input type="file" id="avatarFileInput" accept="image/*" style="display:none" onchange="previewUploadedAvatar(this)">
        </div>

        <div class="profile-identity-info">
          <div class="profile-name-row">
            <h1 class="profile-user-name" id="heroDisplayName">{{ $user->name }}</h1>
            
          </div>
          
          <div class="profile-contact-meta">
            <span class="profile-meta-item">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect width="20" height="16" x="2" y="4" rx="2"/>
                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
              </svg>
              <span id="heroEmail">{{ $user->email }}</span>
            </span>
            <span class="profile-meta-item">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
              </svg>
              <span id="heroPhone">{{ $user->phone ?: 'Not provided' }}</span>
            </span>
            <span class="profile-meta-item">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                <circle cx="12" cy="10" r="3"/>
              </svg>
              {{ $userLocation ?: 'Location not set' }}
            </span>
          </div>
        </div>
      </div>

      <div class="profile-hero-actions">
        @if($activeOrders->count() > 0)
        <a href="/myorders" class="btn-hero-primary">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
          </svg>
          Track Live Order (#SK-{{ $activeOrders->first()->id }})
        </a>
        @endif
        <a href="/index" class="btn-hero-secondary">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/>
          </svg>
          Browse Store
        </a>
      </div>
    </section>

    <!-- Quick Stats Grid -->
    <section class="profile-stats-grid" aria-label="Account Activity Metrics">
      <div class="stat-metric-card">
        <div class="stat-icon-wrapper stat-icon-teal">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect width="16" height="20" x="4" y="2" rx="2"/>
            <line x1="8" x2="16" y1="6" y2="6"/>
            <line x1="8" x2="16" y1="10" y2="10"/>
            <line x1="8" x2="12" y1="14" y2="14"/>
          </svg>
        </div>
        <div class="stat-details">
          <span class="stat-number">{{ $user->orders_count }}</span>
          <span class="stat-label">Total Orders Placed</span>
        </div>
      </div>

      <div class="stat-metric-card">
        <div class="stat-icon-wrapper stat-icon-amber">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <polyline points="12 6 12 12 16 14"/>
          </svg>
        </div>
        <div class="stat-details">
          <span class="stat-number">{{ $activeOrders->count() }} Active</span>
          <span class="stat-label">
            @if($activeOrders->count() > 0)
              Order #SK-{{ $activeOrders->first()->id }} {{ ucfirst($activeOrders->first()->order_status) }}
            @else
              No active orders
            @endif
          </span>
        </div>
      </div>

      <div class="stat-metric-card">
        <div class="stat-icon-wrapper stat-icon-green">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect width="20" height="14" x="2" y="5" rx="2"/>
            <line x1="2" x2="22" y1="10" y2="10"/>
          </svg>
        </div>
        <div class="stat-details">
          <span class="stat-number">&#8377;{{ number_format((float) $user->total_spent, 0) }}</span>
          <span class="stat-label">Total Amount Spent</span>
        </div>
      </div>

      <div class="stat-metric-card">
        <div class="stat-icon-wrapper stat-icon-blue">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
            <circle cx="12" cy="10" r="3"/>
          </svg>
        </div>
        <div class="stat-details">
          <span class="stat-number">{{ $addressCount }} Addresses</span>
          <span class="stat-label">Saved Delivery Locations</span>
        </div>
      </div>
    </section>

    <!-- Main Dashboard Two-Column Grid -->
    <div class="dashboard-grid">
      
      <!-- Left Column: Navigation Menu -->
      <aside class="dashboard-nav-card" aria-label="Profile navigation tabs">
        <div class="nav-section-title">My Account</div>
        
        <button class="dash-nav-item active" onclick="switchProfileTab('personal', this)">
          <div class="nav-item-left">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
              <circle cx="12" cy="7" r="4"/>
            </svg>
            <span>Personal Information</span>
          </div>
        </button>

        <button class="dash-nav-item" onclick="switchProfileTab('addresses', this)">
          <div class="nav-item-left">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
              <circle cx="12" cy="10" r="3"/>
            </svg>
            <span>Saved Addresses</span>
          </div>
          <span class="nav-badge-pill">{{ $addressCount }}</span>
        </button>

        <button class="dash-nav-item" onclick="switchProfileTab('orders', this)">
          <div class="nav-item-left">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect width="16" height="20" x="4" y="2" rx="2"/>
              <line x1="8" x2="16" y1="6" y2="6"/>
              <line x1="8" x2="16" y1="10" y2="10"/>
              <line x1="8" x2="12" y1="14" y2="14"/>
            </svg>
            <span>Orders &amp; Tracking</span>
          </div>
          @if($activeOrders->count() > 0)
          <span class="nav-badge-pill" style="background:#FEF3C7; color:#92400E;">{{ $activeOrders->count() }} Active</span>
          @endif
        </button>

        

        <div class="dash-nav-divider"></div>
        <div class="nav-section-title">Preferences &amp; Security</div>

        <button class="dash-nav-item" onclick="switchProfileTab('security', this)">
          <div class="nav-item-left">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
              <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
            <span>Password &amp; Security</span>
          </div>
        </button>

       

        <div class="dash-nav-divider"></div>

        <form method="POST" action="/logout" style="margin:0;">
          @csrf
          <button type="submit" class="dash-nav-item danger-link"
            style="width:100%; background:transparent; border:none; cursor:pointer; font-family:inherit; text-align:left;">
            <div class="nav-item-left">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                <polyline points="16 17 21 12 16 7"/>
                <line x1="21" x2="9" y1="12" y2="12"/>
              </svg>
              <span>Sign Out</span>
            </div>
          </button>
        </form>
      </aside>

      <!-- Right Column: Interactive Tab Panes -->
      <section style="display: flex; flex-direction: column; gap: 24px; min-width: 0;">
        
        <!-- Tab 1: Personal Information -->
        <div class="tab-content-panel active" id="tab-personal">
          <div class="panel-card">
            <div class="panel-card-header">
              <div class="panel-heading-group">
                <h2 class="panel-title">Personal Information</h2>
                <div class="panel-subtitle">Manage your personal identification, phone and communication details</div>
              </div>
             
            </div>

            <form id="personalInfoForm" method="POST" action="/profile/update">
              @csrf
              <div class="form-grid-2">
                <div class="form-group">
                  <label class="form-label" for="inpName">Full Name</label>
                  <input type="text" id="inpName" name="name" class="form-input" value="{{ old('name', $user->name) }}" required>
                </div>

                <div class="form-group">
                  <label class="form-label" for="inpEmail">
                    Email Address
                  </label>
                  <div class="input-with-icon">
                    <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <rect width="20" height="16" x="2" y="4" rx="2"/>
                      <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                    </svg>
                    <input type="email" id="inpEmail" name="email" class="form-input" value="{{ old('email', $user->email) }}" required>
                  </div>
                </div>

                <div class="form-group">
                  <label class="form-label" for="inpPhone">
                    Phone Number
                  </label>
                  <div class="input-with-icon">
                    <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                    </svg>
                    <input type="tel" id="inpPhone" name="phone" class="form-input" value="{{ old('phone', $user->phone) }}">
                  </div>
                </div>

                <div class="form-group">
                  <label class="form-label" for="inpGender">Gender</label>
                  <select id="inpGender" name="gender" class="form-input">
                    <option value="Female" {{ old('gender', $user->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                    <option value="Male" {{ old('gender', $user->gender) == 'Male' ? 'selected' : '' }}>Male</option>
                    <option value="Other" {{ old('gender', $user->gender) == 'Other' ? 'selected' : '' }}>Other / Prefer not to say</option>
                  </select>
                </div>

                <div class="form-group">
                  <label class="form-label" for="inpCity">Primary City / Region</label>
                  <input type="text" id="inpCity" class="form-input" value="{{ $userLocation }}" readonly style="background:#F8FAFC;">
                </div>
              </div>

              <div style="display: flex; gap: 12px; margin-top: 24px; flex-wrap: wrap;">
                <button type="submit" class="btn-primary">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                    <polyline points="17 21 17 13 7 13 7 21"/>
                    <polyline points="7 3 7 8 15 8"/>
                  </svg>
                  Save Profile Changes
                </button>
                <button type="reset" class="btn-secondary">Reset</button>
              </div>
            </form>
          </div>
        </div>

        <!-- Tab 2: Saved Delivery Addresses -->
        <div class="tab-content-panel" id="tab-addresses">
          <div class="panel-card">
            <div class="panel-card-header">
              <div class="panel-heading-group">
                <h2 class="panel-title">Saved Delivery Addresses</h2>
                <div class="panel-subtitle">Keep your shipping address up to date for rapid doorstep checkout</div>
              </div>
              <button class="btn-primary" onclick="toggleAddressForm()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                  <path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4Z"/>
                </svg>
                Edit Address
              </button>
            </div>

            <!-- Saved Address -->
            <div class="address-cards-grid" id="addressCardsList">
              <div class="address-item-card is-default" id="addr-card-1">
                <div class="address-header">
                  <div class="address-tag-group">
                    <span class="address-type-badge">Home</span>
                    <span class="address-default-badge">&#10003; Default</span>
                  </div>
                </div>
                <div class="address-body">
                  <div class="address-recipient-name">{{ $user->name }}</div>
                  <div>{{ $user->address ?: 'No address saved yet.' }}</div>
                  @if ($userLocation)
                    <div>{{ $userLocation }}{{ $user->postal_code ? ' – ' . $user->postal_code : '' }}</div>
                  @endif
                  <div class="address-contact-phone">Mobile: {{ $user->phone ?: 'Not provided' }}</div>
                </div>
              </div>
            </div>

            <!-- Edit Address Form (Hidden unless open or invalid) -->
            <div class="new-address-form-box" id="addressFormBox"
              style="{{ ($errors->has('address') || $errors->has('city') || $errors->has('state') || $errors->has('postal_code')) ? 'display: flex;' : 'display: none;' }}">
              <h3 style="font-size: 16px; font-weight: 700; color: var(--ink);">Update Delivery Address</h3>
              <form method="POST" action="/profile/address">
                @csrf
                <div class="form-grid-2">
                  <div class="form-group" style="grid-column: 1 / -1;">
                    <label class="form-label" for="addrAddress">Flat, House No., Building, Area</label>
                    <input type="text" id="addrAddress" name="address" class="form-input"
                      value="{{ old('address', $user->address) }}" placeholder="e.g. Flat 301, Titanium City Center">
                  </div>
                  <div class="form-group">
                    <label class="form-label" for="addrCity">Town / City</label>
                    <input type="text" id="addrCity" name="city" class="form-input"
                      value="{{ old('city', $user->city) }}" placeholder="e.g. Ahmedabad">
                  </div>
                  <div class="form-group">
                    <label class="form-label" for="addrState">State</label>
                    <input type="text" id="addrState" name="state" class="form-input"
                      value="{{ old('state', $user->state) }}" placeholder="e.g. Gujarat">
                  </div>
                  <div class="form-group">
                    <label class="form-label" for="addrPincode">Pincode</label>
                    <input type="text" id="addrPincode" name="postal_code" class="form-input"
                      maxlength="10" value="{{ old('postal_code', $user->postal_code) }}" placeholder="e.g. 380015">
                  </div>
                </div>

                <div style="display: flex; gap: 12px; margin-top: 20px;">
                  <button type="submit" class="btn-primary">Save Address</button>
                  <button type="button" class="btn-secondary" onclick="toggleAddressForm()">Cancel</button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <!-- Tab 3: Orders & Tracking -->
        <div class="tab-content-panel" id="tab-orders">
          <div class="panel-card">
            <div class="panel-card-header">
              <div class="panel-heading-group">
                <h2 class="panel-title">Active Orders &amp; Dispatch Status</h2>
                <div class="panel-subtitle">Instant status of your incoming electronics &amp; grocery packages</div>
              </div>
              <a href="/myorders" class="btn-outline-teal">
                View All Orders &rarr;
              </a>
            </div>

            <!-- Active Orders List (Latest Only) -->
            <div style="display: flex; flex-direction: column; gap: 20px;">
              @if($order = $activeOrders->first())
                @php
                    $statusMap = [
                        'pending' => 'Stage 1: Received',
                        'received' => 'Stage 1: Received',
                        'processing' => 'Stage 2: Process (In Warehouse)',
                        'process' => 'Stage 2: Process (In Warehouse)',
                        'shipped' => 'Stage 3: Shipped',
                        'shipping' => 'Stage 3: Shipped',
                        'out_for_delivery' => 'Stage 4: Out for Delivery',
                        'delivered' => 'Stage 5: Delivered',
                    ];
                    $currentStatusLabel = $statusMap[$order->order_status] ?? ucfirst($order->order_status);
                @endphp
                <div class="live-order-banner">
                  <div class="live-order-top">
                    <div class="live-order-id">
                      <span>Order #SK-{{ $order->id }}</span>
                      <span class="live-order-badge">
                        <span class="live-order-pulse"></span>
                        {{ $currentStatusLabel }}
                      </span>
                    </div>
                    <div style="font-size: 13px; color: var(--muted); font-weight: 600;">Placed {{ $order->created_at->format('d M Y, h:i A') }}</div>
                  </div>

                  <div class="live-order-items">
                    <strong>Items:</strong> 
                    @foreach($order->items as $item)
                      {{ $item->product->name }} ({{ $item->variant->values->pluck('value')->implode('/') }}) &times; {{ $item->quantity }}{{ !$loop->last ? ', ' : '' }}
                    @endforeach
                  </div>

                  <div class="live-order-bottom">
                    <div style="font-size: 14px;"><strong>Total Paid:</strong> &#8377;{{ number_format($order->total_amount, 2) }} &bull; {{ $order->payment_method === 'cod' ? 'Cash on Delivery' : 'Paid Online' }}</div>
                    <a href="/myorders" class="btn-primary" style="padding: 8px 16px; font-size: 13px;">
                      Open 5-Step Live Tracker &rarr;
                    </a>
                  </div>
                </div>
              @else
                <div style="padding: 40px; text-align: center; background: #F8FAFC; border-radius: 12px; border: 1px dashed #CBD5E1;">
                  <div style="color: #64748B; font-weight: 600; margin-bottom: 8px;">No active orders found</div>
                  <a href="/index" style="color: var(--primary-teal); font-weight: 700; text-decoration: none; font-size: 14px;">Start Shopping &rarr;</a>
                </div>
              @endif
            </div>

            <!-- Past Order Preview -->
            <div style="display: flex; flex-direction: column; gap: 12px; margin-top: 24px;">
              <h3 style="font-size: 15px; font-weight: 700; color: #334155;">Previous Orders</h3>
              
              @forelse($previousOrders as $order)
                <div style="border: 1px solid var(--border); border-radius: var(--radius-md); padding: 16px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                  <div>
                    <div style="font-weight: 700; font-size: 14px; color: var(--ink);">Order #SK-{{ $order->id }} &bull; &#8377;{{ number_format($order->total_amount, 2) }}</div>
                    <div style="font-size: 13px; color: var(--muted); margin-top: 2px;">
                      Delivered on {{ $order->updated_at->format('d M Y') }} &bull; 
                      {{ $order->items->first()->product->name }} 
                      @if($order->items->count() > 1)
                        and {{ $order->items->count() - 1 }} other {{ Str::plural('item', $order->items->count() - 1) }}
                      @endif
                    </div>
                  </div>
                  <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="background: #DCFCE7; color: #166534; font-size: 12px; font-weight: 700; padding: 3px 10px; border-radius: var(--radius-full);">&#10003; Delivered</span>
                    <a href="/myorders" class="address-action-btn">View Details</a>
                  </div>
                </div>
              @empty
                <div style="padding: 20px; text-align: center; color: #94A3B8; font-size: 13px;">
                  No previous orders found.
                </div>
              @endforelse
            </div>
          </div>
        </div>

        <!-- Tab 4: Wallet & Payments -->
        <div class="tab-content-panel" id="tab-wallet">
          <div class="panel-card">
            <div class="panel-card-header">
              <div class="panel-heading-group">
                <h2 class="panel-title">Shopkart Wallet &amp; Saved Cards</h2>
                <div class="panel-subtitle">Instant 1-click checkout with stored balances and payment methods</div>
              </div>
            </div>

            <!-- Wallet Balance Card -->
            <div class="wallet-balance-box">
              <div class="wallet-balance-info">
                <span style="font-size: 13px; font-weight: 600; opacity: 0.9;">Available Wallet Cash</span>
                <span class="wallet-amount">&#8377;450.00</span>
                <span style="font-size: 12px; opacity: 0.85;">Cashback earned from festival launch orders</span>
              </div>
              <button class="btn-hero-primary" onclick="showToast('Recharge simulated! &#8377;100 added.')">
                + Add Money
              </button>
            </div>

            <!-- Saved Payment Methods -->
            <div style="display: flex; flex-direction: column; gap: 14px;">
              <h3 style="font-size: 15px; font-weight: 700; color: #334155;">Saved Payment Instruments</h3>

              <div style="border: 1px solid var(--border); border-radius: var(--radius-md); padding: 16px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 14px;">
                  <div style="width: 40px; height: 40px; border-radius: 8px; background: #EEF2FF; color: #4F46E5; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 12px;">
                    UPI
                  </div>
                  <div>
                    <div style="font-weight: 700; font-size: 14px;">hiral.patel@okhdfcbank</div>
                    <div style="font-size: 12px; color: var(--muted);">Google Pay / BHIM UPI &bull; Verified</div>
                  </div>
                </div>
                <span style="background: #DCFCE7; color: #166534; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: var(--radius-full);">Default</span>
              </div>

              <div style="border: 1px solid var(--border); border-radius: var(--radius-md); padding: 16px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 14px;">
                  <div style="width: 40px; height: 40px; border-radius: 8px; background: #FEF3C7; color: #D97706; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 11px;">
                    VISA
                  </div>
                  <div>
                    <div style="font-weight: 700; font-size: 14px;">HDFC Platinum Debit Card &bull;&bull;&bull;&bull; 4921</div>
                    <div style="font-size: 12px; color: var(--muted);">Expires 09/29 &bull; Hiral Patel</div>
                  </div>
                </div>
                <button class="address-action-btn delete" onclick="showToast('Card removed')">Remove</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Tab 5: Password & Security -->
        <div class="tab-content-panel" id="tab-security">
          <div class="panel-card">
            <div class="panel-card-header">
              <div class="panel-heading-group">
                <h2 class="panel-title">Password &amp; Account Security</h2>
                <div class="panel-subtitle">Change credentials and secure your account with multi-factor verification</div>
              </div>
            </div>

            <form method="POST" action="/profile/security">
              @csrf
              <div class="form-grid-2">
                <div class="form-group" style="grid-column: 1 / -1;">
                  <label class="form-label" for="secCurrentPass">Current Password</label>
                  <input type="password" id="secCurrentPass" name="current_password" class="form-input" placeholder="Enter existing password" required>
                </div>
                
                <div class="form-group">
                  <label class="form-label" for="secNewPass">New Password</label>
                  <input type="password" id="secNewPass" name="password" class="form-input" placeholder="Minimum 8 characters" required oninput="checkPassStrength(this.value)">
                  <div id="passStrengthIndicator" style="font-size: 11px; margin-top: 4px; color: var(--muted);">Password strength: <span id="passStrengthText" style="font-weight: 700;">Normal</span></div>
                </div>

                <div class="form-group">
                  <label class="form-label" for="secConfirmPass">Confirm New Password</label>
                  <input type="password" id="secConfirmPass" name="password_confirmation" class="form-input" placeholder="Repeat new password" required>
                </div>
              </div>

             

              <div style="display: flex; gap: 12px; margin-top: 24px;">
                <button type="submit" class="btn-primary">Update Password</button>
              </div>
            </form>
          </div>
        </div>

        <!-- Tab 6: Notifications & Preferences -->
        <div class="tab-content-panel" id="tab-notifications">
          <div class="panel-card">
            <div class="panel-card-header">
              <div class="panel-heading-group">
                <h2 class="panel-title">Notification &amp; Communication Preferences</h2>
                <div class="panel-subtitle">Choose how you'd like to receive order dispatches, OTPs and offers</div>
              </div>
            </div>

            <div>
              <div class="setting-switch-row">
                <div class="switch-label-group">
                  <span class="switch-title">WhatsApp Live Tracking Alerts</span>
                  <span class="switch-desc">Receive real-time delivery status updates and driver location on {{ $user->phone ?: 'your mobile number' }}</span>
                </div>
                <label class="toggle-switch">
                  <input type="checkbox" checked onchange="showToast('WhatsApp notifications updated')">
                  <span class="slider-round"></span>
                </label>
              </div>

              <div class="setting-switch-row">
                <div class="switch-label-group">
                  <span class="switch-title">SMS Order &amp; Delivery Messages</span>
                  <span class="switch-desc">Receive direct text SMS when order is packed, shipped, and out for delivery</span>
                </div>
                <label class="toggle-switch">
                  <input type="checkbox" checked onchange="showToast('SMS notifications updated')">
                  <span class="slider-round"></span>
                </label>
              </div>

              <div class="setting-switch-row">
                <div class="switch-label-group">
                  <span class="switch-title">Email Invoices &amp; Order Summaries</span>
                  <span class="switch-desc">Receive PDF GST tax invoice on {{ $user->email }} upon purchase</span>
                </div>
                <label class="toggle-switch">
                  <input type="checkbox" checked onchange="showToast('Email preferences updated')">
                  <span class="slider-round"></span>
                </label>
              </div>

              <div class="setting-switch-row">
                <div class="switch-label-group">
                  <span class="switch-title">Festive Deals &amp; Price Drop Alerts</span>
                  <span class="switch-desc">Be the first to hear about Gujarat festive specials on grocery &amp; tech</span>
                </div>
                <label class="toggle-switch">
                  <input type="checkbox" onchange="showToast('Promotional preferences updated')">
                  <span class="slider-round"></span>
                </label>
              </div>
            </div>

            <div style="margin-top: 16px;">
              <button class="btn-primary" onclick="showToast('Preferences saved successfully!')">Save Preferences</button>
            </div>
          </div>
        </div>

      </section>
    </div>
  </main>

  <!-- Toast Notification Feedback -->
  <div class="toast-notice" id="toastNotice" role="alert" aria-live="polite">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#5EEAD4" stroke-width="2.5">
      <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
      <polyline points="22 4 12 14.01 9 11.01"/>
    </svg>
    <span id="toastNoticeText">Profile information saved!</span>
  </div>

  <!-- Site Footer -->
  <footer class="site-footer">
    <div style="max-width: 1240px; margin: 0 auto; padding: 0 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
      <div>&copy; 2026 Shopkart Gujarat Store. All rights reserved.</div>
      <div style="display: flex; gap: 16px;">
        <a href="/index" style="color: var(--muted); text-decoration: none;">Catalog</a>
        <a href="/myorders" style="color: var(--muted); text-decoration: none;">My Orders</a>
        <a href="/profile" style="color: var(--primary-teal); font-weight: 700; text-decoration: none;">My Profile</a>
      </div>
    </div>
  </footer>

  <script>
    // Tab Switching Logic
    function switchProfileTab(tabId, clickedBtn) {
      document.querySelectorAll('.tab-content-panel').forEach(panel => {
        panel.classList.remove('active');
      });
      document.querySelectorAll('.dash-nav-item').forEach(btn => {
        btn.classList.remove('active');
      });

      const target = document.getElementById('tab-' + tabId);
      if (target) {
        target.classList.add('active');
      }
      if (clickedBtn) {
        clickedBtn.classList.add('active');
      }

      window.location.hash = tabId;
    }

    // Auto-select tab if URL hash exists
    window.addEventListener('DOMContentLoaded', () => {
      const hash = window.location.hash.replace('#', '');
      if (hash) {
        const btn = document.querySelector(`.dash-nav-item[onclick*="${hash}"]`);
        if (btn) {
          switchProfileTab(hash, btn);
        }
      }
    });

    // Toast Notice Helper
    let toastTimeout;
    function showToast(message) {
      const toast = document.getElementById('toastNotice');
      const text = document.getElementById('toastNoticeText');
      if (!toast || !text) return;

      text.textContent = message;
      toast.classList.add('visible');

      clearTimeout(toastTimeout);
      toastTimeout = setTimeout(() => {
        toast.classList.remove('visible');
      }, 3500);
    }

    // Handle Password Update
    function handlePasswordUpdate(e) {
      e.preventDefault();
      const cur = document.getElementById('secCurrentPass').value;
      const newP = document.getElementById('secNewPass').value;
      const conf = document.getElementById('secConfirmPass').value;

      if (newP !== conf) {
        showToast('New passwords do not match!');
        return;
      }

      showToast('Password updated successfully!');
      document.getElementById('secCurrentPass').value = '';
      document.getElementById('secNewPass').value = '';
      document.getElementById('secConfirmPass').value = '';
    }

    // Password strength check
    function checkPassStrength(val) {
      const txt = document.getElementById('passStrengthText');
      if (!txt) return;
      if (val.length === 0) {
        txt.textContent = 'Normal';
        txt.style.color = 'var(--muted)';
      } else if (val.length < 6) {
        txt.textContent = 'Weak';
        txt.style.color = '#EF4444';
      } else if (val.length < 10) {
        txt.textContent = 'Good';
        txt.style.color = '#F59E0B';
      } else {
        txt.textContent = 'Strong';
        txt.style.color = '#10B981';
      }
    }

    // Avatar preview simulation
    function previewUploadedAvatar(input) {
      if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
          const heroAvatar = document.getElementById('heroAvatar');
          heroAvatar.textContent = '';
          heroAvatar.style.backgroundImage = `url(${e.target.result})`;
          heroAvatar.style.backgroundSize = 'cover';
          heroAvatar.style.backgroundPosition = 'center';

          const navAvatar = document.getElementById('navAvatar');
          navAvatar.textContent = '';
          navAvatar.style.backgroundImage = `url(${e.target.result})`;
          navAvatar.style.backgroundSize = 'cover';
          navAvatar.style.backgroundPosition = 'center';

          showToast('Profile photo updated!');
        };
        reader.readAsDataURL(input.files[0]);
      }
    }

    // Address edit form toggle
    function toggleAddressForm() {
      const formBox = document.getElementById('addressFormBox');
      if (!formBox) return;

      const isHidden = formBox.style.display === 'none';
      formBox.style.display = isHidden ? 'flex' : 'none';
      if (isHidden) {
        formBox.scrollIntoView({ behavior: 'smooth' });
      }
    }
  </script>
</body>
</html>
