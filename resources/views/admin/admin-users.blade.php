<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Users | Shopkart Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
    rel="stylesheet">
  <link href="{{ asset('assets/css/adminproducts.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/css/admin-notifications.css') }}" rel="stylesheet">
</head>

<body>

  <div class="admin-container">
    @include('admin.partials.admin-sidebar', ['active' => 'users'])

    <!-- Main Area -->
    <main class="admin-main">
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
            <div class="breadcrumb">
              <a href="/adminorders">Customers</a>
              <span>/</span>
              <span>Users</span>
            </div>
            <h1 class="page-heading">Users</h1>
            <div style="font-size: 13px; color: var(--muted);">Registered customers and their order activity</div>
          </div>
        </div>

        <div class="header-actions">
          <div class="search-box-wrap">
            <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="8" />
              <path d="m21 21-4.3-4.3" />
            </svg>
            <input type="text" id="userSearchInput" class="search-input" placeholder="Search name, email, city..."
              aria-label="Search users" oninput="filterUsers()">
          </div>

          @include('admin.partials.notification-bar')
        </div>
      </header>

      <div class="content-inner">
        <div class="toolbar-bar">
          <div class="filter-pills-wrap">
            <button type="button" class="filter-pill active" data-status="all" onclick="setStatusFilter(this)">All
              Customers</button>
            <button type="button" class="filter-pill" data-status="active"
              onclick="setStatusFilter(this)">Active</button>
            <button type="button" class="filter-pill" data-status="inactive"
              onclick="setStatusFilter(this)">Inactive</button>
            <button type="button" class="filter-pill" data-status="blocked"
              onclick="setStatusFilter(this)">Blocked</button>
          </div>
        </div>

        <section class="category-section-card">
          <div class="category-card-header">
            <div class="category-title-wrap">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                <circle cx="9" cy="7" r="4" />
                <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
              </svg>
              <h2 class="category-title">All Users</h2>
              <span class="category-badge-pill" id="userCountPill">{{ $users->count() }} Users</span>
            </div>
          </div>

          <div class="table-responsive">
            <table class="products-table">
              <thead>
                <tr>
                  <th>Customer</th>
                  <th>Phone</th>
                  <th>Address</th>
                  <th>Orders</th>
                  <th>Total Spent</th>
                  <th>Status</th>
                  <th>Joined</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="userTableBody">
                @forelse ($users as $user)
                  @php
                    $initials = collect(explode(' ', trim($user->name)))
                      ->filter()
                      ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
                      ->take(2)
                      ->implode('');
                    $fullAddress = collect([$user->address, $user->city, $user->state, $user->postal_code])
                      ->filter()
                      ->implode(', ');
                    $isActive = $user->last_login_at !== null;
                    $status = $isActive ? '  `' : 'inactive';
                  @endphp
                  <tr data-status="{{ $status }}"
                    data-search="{{ strtolower(trim($user->name.' '.$user->email.' '.($user->phone ?? '').' '.($user->city ?? ''))) }}">
                    <td>
                      <div class="product-meta-cell">
                        <div class="customer-avatar">{{ $initials ?: 'U' }}</div>
                        <div>
                          <div class="product-name">{{ $user->name }}</div>
                          <div class="product-subtitle">{{ $user->email }}</div>
                        </div>
                      </div>
                    </td>
                    <td>{{ $user->phone ?: '—' }}</td>
                    <td><div class="cell-address">{{ $fullAddress ?: '—' }}</div></td>
                    <td><span class="variant-summary-tag">{{ $user->orders_count > 0 ? $user->orders_count.' orders' : 'No orders' }}</span></td>
                    <td><span class="amount-spent">₹{{ number_format($user->total_spent, 0) }}</span></td>
                    <td>
                      <span class="badge {{ $isActive ? 'badge-in_stock' : 'badge-low_stock' }}"><span class="badge-dot"></span>{{ $isActive ? 'Active' : 'Inactive' }}</span>
                    </td>
                    <td>{{ $user->created_at?->format('d M Y') }}</td>
                    <td>
                      <div class="table-actions">
                        <button type="button" class="edit-btn" onclick="viewCustomer('{{ $user->id }}')">View</button>
                        <button type="button" class="delete-btn" onclick='deleteUser(@js($user->name))'>Delete</button>
                      </div>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="8" style="text-align: center; padding: 24px; color: var(--muted);">No users found.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </section>
      </div>
    </main>
  </div>

  <!-- Customer Detail Modal -->
  <div class="modal-overlay" id="customerModal" onclick="if (event.target === this) closeCustomerModal()">
    <div class="modal-box modal-lg">
      <div class="modal-header">
        <h3 class="modal-title" id="modalCustomerName">Customer</h3>
        <button type="button" class="modal-close-btn" onclick="closeCustomerModal()" aria-label="Close">&times;</button>
      </div>
      <div class="modal-body">
        <div class="detail-grid">
          <div class="detail-item">
            <label>Email</label>
            <span id="modalEmail">—</span>
          </div>
          <div class="detail-item">
            <label>Phone</label>
            <span id="modalPhone">—</span>
          </div>
          <div class="detail-item">
            <label>Address</label>
            <span id="modalAddress">—</span>
          </div>
          <div class="detail-item">
            <label>Joined</label>
            <span id="modalJoined">—</span>
          </div>
          <div class="detail-item">
            <label>Total Orders</label>
            <span id="modalOrderCount">—</span>
          </div>
          <div class="detail-item">
            <label>Total Spent</label>
            <span id="modalSpent">—</span>
          </div>
        </div>

        <div>
          <div class="modal-section-title">Recent Orders</div>
          <div class="order-mini-list" id="modalOrderList"></div>
        </div>
      </div>
    </div>
  </div>

  @php
    $customerDetails = $users->mapWithKeys(function ($user) {
        $fullAddress = collect([$user->address, $user->city, $user->state, $user->postal_code])
            ->filter()
            ->implode(', ');

        return [$user->id => [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone ?: '—',
            'address' => $fullAddress ?: '—',
            'joined' => $user->created_at?->format('d M Y') ?? '—',
            'orderCount' => $user->orders_count,
            'spent' => (float) $user->total_spent,
            'orders' => [],
        ]];
    });
  @endphp

  <script>
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const adminSidebar = document.getElementById('adminSidebar');
    if (mobileMenuBtn && adminSidebar) {
      mobileMenuBtn.addEventListener('click', function () {
        adminSidebar.classList.toggle('open');
      });
    }

    const customerDetails = @json($customerDetails);

    function formatINR(amount) {
      return '₹' + Number(amount).toLocaleString('en-IN');
    }

    function escapeHtml(text) {
      return String(text).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    let activeStatus = 'all';

    function setStatusFilter(btn) {
      document.querySelectorAll('.filter-pill').forEach(pill => pill.classList.remove('active'));
      btn.classList.add('active');
      activeStatus = btn.dataset.status;
      filterUsers();
    }

    function filterUsers() {
      const term = (document.getElementById('userSearchInput').value || '').trim().toLowerCase();
      const rows = document.querySelectorAll('#userTableBody tr');
      let visible = 0;
      rows.forEach(row => {
        const matchesSearch = !term || (row.dataset.search || '').includes(term);
        const matchesStatus = activeStatus === 'all' || row.dataset.status === activeStatus;
        const match = matchesSearch && matchesStatus;
        row.style.display = match ? '' : 'none';
        if (match) visible++;
      });
      document.getElementById('userCountPill').textContent = `${visible} Users`;
    }

    function viewCustomer(id) {
      const customer = customerDetails[id];
      if (!customer) return;

      document.getElementById('modalCustomerName').textContent = customer.name;
      document.getElementById('modalEmail').textContent = customer.email;
      document.getElementById('modalPhone').textContent = customer.phone;
      document.getElementById('modalAddress').textContent = customer.address;
      document.getElementById('modalJoined').textContent = customer.joined;
      document.getElementById('modalOrderCount').textContent = `${customer.orderCount} orders`;
      document.getElementById('modalSpent').textContent = formatINR(customer.spent);

      const list = document.getElementById('modalOrderList');
      if (!customer.orders.length) {
        list.innerHTML = '<div class="empty-orders-note">No orders placed yet.</div>';
      } else {
        list.innerHTML = customer.orders.map(order => `
          <div class="order-mini-row">
            <div>
              <div class="order-mini-id">${escapeHtml(order.id)}</div>
              <div class="order-mini-date">${escapeHtml(order.date)}</div>
            </div>
            <div class="order-mini-meta">
              <span class="order-mini-amount">${formatINR(order.amount)}</span>
              <span class="order-status-tag">${escapeHtml(order.status)}</span>
            </div>
          </div>
        `).join('');
      }

      document.getElementById('customerModal').classList.add('open');
    }

    function closeCustomerModal() {
      document.getElementById('customerModal').classList.remove('open');
    }

    function deleteUser(name) {
      if (window.confirm(`Delete the account for "${name}"? Their order history will be archived.`)) {
        window.location.href = '/adminusers';
      }
    }

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') {
        closeCustomerModal();
      }
    });
  </script>
</body>

</html>
