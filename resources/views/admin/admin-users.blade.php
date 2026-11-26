<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Users | Shopkart Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
    rel="stylesheet">
  <link href="{{ asset('assets/css/adminproducts.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/css/admin-notifications.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/css/admin-tables.css') }}" rel="stylesheet">
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
            <h1 class="page-heading">{{ __('Users') }}</h1>
            <div style="font-size: 13px; color: var(--muted);">Registered customers and their order activity</div>
          </div>
        </div>

        <div class="header-actions">
          <div class="search-box-wrap" style="position: relative; display: flex; align-items: center;">
            <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="8" />
              <path d="m21 21-4.3-4.3" />
            </svg>
            <input type="text" id="userSearchInput" class="search-input" placeholder="Search name, email, phone, city..."
              aria-label="Search users" value="{{ request('search') ?? request('q') }}" oninput="filterUsers()" autocomplete="off">
            <button type="button" id="clearUserSearchBtn"
              style="display: {{ (request('search') || request('q')) ? 'block' : 'none' }}; position: absolute; right: 12px; background: none; border: none; color: var(--muted); cursor: pointer; font-size: 14px;"
              onclick="clearUserSearch()" title="Clear search">✕</button>
          </div>

          @include('admin.partials.notification-bar')
        </div>
      </header>

      <div class="content-inner">
        <!-- Users Toolbar (Search, Role Filter, Status Filter, Reset) -->
        <div class="table-toolbar-card">
          <form method="GET" action="{{ url('/adminusers') }}" class="table-toolbar-form" id="usersFilterForm">
            @if (request('sort_by'))
              <input type="hidden" name="sort_by" value="{{ request('sort_by') }}">
            @endif
            @if (request('sort_dir'))
              <input type="hidden" name="sort_dir" value="{{ request('sort_dir') }}">
            @endif

            <div class="table-toolbar-left">
              <div class="table-search-box">
                <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="11" cy="11" r="8" />
                  <path d="m21 21-4.3-4.3" />
                </svg>
                <input type="text" name="search" class="search-input" placeholder="Search name, email, phone, city..."
                  value="{{ request('search') ?? request('q') }}" autocomplete="off" aria-label="Search users">
                <button type="button" class="search-clear-btn" style="{{ (request('search') || request('q')) ? 'display:flex;' : 'display:none;' }}" onclick="clearTableSearch(this)" title="Clear search">✕</button>
              </div>

              <!-- Role Filter -->
              <select name="role" class="table-filter-select {{ request('role') ? 'has-value' : '' }}" aria-label="Filter by role">
                <option value="">{{ __('All Roles') }}</option>
                <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>Customer (User)</option>
                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
              </select>

              <!-- Status Filter -->
              <select name="status" class="table-filter-select {{ request('status') ? 'has-value' : '' }}" aria-label="Filter by activity status">
                <option value="">{{ __('All Statuses') }}</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
              </select>
            </div>

            <div class="table-toolbar-right">
              <button type="button" class="btn-bulk-delete" style="display: none;" onclick="executeBulkDelete('users', '{{ route('admin.users.bulk-delete') }}')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="3 6 5 6 21 6"/>
                  <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                </svg>
                Delete Selected (<span class="selected-count">0</span>)
              </button>

              @if (request('search') || request('q') || request('role') || request('status') || request('sort_by'))
                <a href="{{ url('/adminusers') }}" class="btn-table-reset" title="Reset all filters and search">
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

        <section class="category-section-card" id="userTableCard">
          <div class="category-card-header">
            <div class="category-title-wrap">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                <circle cx="9" cy="7" r="4" />
                <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
              </svg>
              <h2 class="category-title">{{ __('All Users') }}</h2>
              <span class="category-badge-pill" id="userCountPill">{{ $users->total() }} {{ __('Users') }}</span>
            </div>
          </div>

          <div class="table-responsive">
            <table class="products-table">
              <thead>
                <tr>
                  <th style="width: 44px; text-align: center;">
                    <input type="checkbox" class="form-check-input select-all-checkbox" aria-label="Select all users">
                  </th>
                  @include('admin.partials.sort-th', ['column' => 'name', 'label' => 'Customer'])
                  <th>Role</th>
                  <th>Phone</th>
                  <th>Address</th>
                  @include('admin.partials.sort-th', ['column' => 'orders_count', 'label' => 'Orders'])
                  @include('admin.partials.sort-th', ['column' => 'total_spent', 'label' => 'Total Spent'])
                  <th>Status</th>
                  @include('admin.partials.sort-th', ['column' => 'created_at', 'label' => 'Joined'])
                  <th style="text-align: right;">Actions</th>
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
                    $status = $isActive ? 'active' : 'inactive';
                  @endphp
                  <tr data-status="{{ $status }}"
                    data-search="{{ strtolower(trim($user->name.' '.$user->email.' '.($user->phone ?? '').' '.($user->city ?? '').' '.($user->state ?? '').' '.($user->address ?? ''))) }}">
                    <td style="text-align: center;">
                      <input type="checkbox" class="form-check-input row-checkbox" value="{{ $user->id }}" aria-label="Select user">
                    </td>
                    <td>
                      <div class="product-meta-cell">
                        <div class="customer-avatar">{{ $initials ?: 'U' }}</div>
                        <div>
                          <div class="product-name">{{ $user->name }}</div>
                          <div class="product-subtitle">{{ $user->email }}</div>
                        </div>
                      </div>
                    </td>
                    <td>
                      <span class="badge {{ $user->role === 'admin' ? 'bg-primary' : 'bg-info text-dark' }}">
                        {{ ucfirst($user->role) }}
                      </span>
                    </td>
                    <td>{{ $user->phone ?: '—' }}</td>
                    <td><div class="cell-address">{{ $fullAddress ?: '—' }}</div></td>
                    <td><span class="variant-summary-tag">{{ $user->orders_count > 0 ? number_format($user->orders_count).' orders' : '0 orders' }}</span></td>
                    <td><span class="amount-spent">₹{{ number_format($user->total_spent, 2) }}</span></td>
                    <td>
                      <div class="status-toggle-wrap">
                        <label class="form-check form-switch" title="Toggle status">
                          <input type="checkbox" class="form-check-input status-toggle-input"
                            data-url="{{ route('admin.users.toggle', $user->id) }}"
                            data-id="{{ $user->id }}"
                            {{ $isActive ? 'checked' : '' }}>
                        </label>
                        <span class="badge {{ $isActive ? 'bg-success' : 'bg-secondary' }} status-badge-{{ $user->id }}">
                          {{ $isActive ? 'Active' : 'Inactive' }}
                        </span>
                      </div>
                    </td>
                    <td>{{ $user->created_at?->format('d M Y') }}</td>
                    <td>
                      <div class="table-actions">
                        <button type="button" class="edit-btn" onclick="openEditUserModal('{{ $user->id }}')" title="Edit User">
                          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 20h9" />
                            <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z" />
                          </svg>
                          <span>Edit</span>
                        </button>
                        <button type="button" class="view-btn" onclick="viewCustomer('{{ $user->id }}')" title="View Details">
                          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                            <circle cx="12" cy="12" r="3" />
                          </svg>
                          <span>View</span>
                        </button>
                        <form method="POST" action="/adminusers/{{ $user->id }}" style="display: inline; margin: 0;"
                          onsubmit="return confirmRecordDelete('{{ addslashes($user->name) }}', 'user')">
                          @csrf
                          @method('DELETE')
                          <button type="button" class="delete-btn ajax-delete"
                            data-url="/adminusers/{{ $user->id }}"
                            data-id="{{ $user->id }}"
                            data-confirm="Are you sure you want to delete user &quot;{{ addslashes($user->name) }}&quot;?"
                            data-table="#userTableCard"
                            title="Delete User">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                              <path d="M3 6h18" />
                              <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                            </svg>
                            <span>Delete</span>
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>
                @empty
                  @include('admin.partials.empty-state', [
                    'colspan' => 10,
                    'title' => 'No users found',
                    'message' => 'No customers match your search or filter criteria.',
                    'resetUrl' => url('/adminusers'),
                    'hasFilters' => (bool) (request('search') || request('q') || request('role') || request('status') || request('sort_by')),
                  ])
                @endforelse
              </tbody>
            </table>
          </div>

          @include('admin.partials.table-pagination', [
            'paginator' => $users,
            'recordName' => 'users'
          ])
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

  <!-- Edit User Modal -->
  <div class="modal-overlay" id="editUserModal" onclick="if (event.target === this) closeEditUserModal()">
    <div class="modal-box" style="max-width: 540px;">
      <div class="modal-header">
        <h3 class="modal-title">{{ __('Edit User') }}</h3>
        <button type="button" class="modal-close-btn" onclick="closeEditUserModal()" aria-label="Close">&times;</button>
      </div>
      <form id="editUserForm" onsubmit="submitEditUserForm(event)">
        <div class="modal-body" style="display: flex; flex-direction: column; gap: 14px; padding: 20px;">
          <input type="hidden" id="editUserId" name="user_id">
          <div>
            <label style="display: block; font-size: 12px; font-weight: 700; margin-bottom: 5px;">{{ __('Full Name') }}</label>
            <input type="text" id="editUserName" name="name" class="search-input" style="width: 100%; border: 1px solid var(--tbl-border); padding: 8px 12px; border-radius: 8px;" required>
          </div>
          <div>
            <label style="display: block; font-size: 12px; font-weight: 700; margin-bottom: 5px;">{{ __('Email Address') }}</label>
            <input type="email" id="editUserEmail" name="email" class="search-input" style="width: 100%; border: 1px solid var(--tbl-border); padding: 8px 12px; border-radius: 8px;" required>
          </div>
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
            <div>
              <label style="display: block; font-size: 12px; font-weight: 700; margin-bottom: 5px;">{{ __('Phone') }}</label>
              <input type="text" id="editUserPhone" name="phone" class="search-input" style="width: 100%; border: 1px solid var(--tbl-border); padding: 8px 12px; border-radius: 8px;">
            </div>
            <div>
              <label style="display: block; font-size: 12px; font-weight: 700; margin-bottom: 5px;">{{ __('Role') }}</label>
              <select id="editUserRole" name="role" class="table-filter-select" style="width: 100%;">
                <option value="user">Customer (User)</option>
                <option value="admin">Administrator</option>
              </select>
            </div>
          </div>
          <div>
            <label style="display: block; font-size: 12px; font-weight: 700; margin-bottom: 5px;">{{ __('Address') }}</label>
            <input type="text" id="editUserAddress" name="address" class="search-input" style="width: 100%; border: 1px solid var(--tbl-border); padding: 8px 12px; border-radius: 8px;">
          </div>
          <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
            <div>
              <label style="display: block; font-size: 12px; font-weight: 700; margin-bottom: 5px;">{{ __('City') }}</label>
              <input type="text" id="editUserCity" name="city" class="search-input" style="width: 100%; border: 1px solid var(--tbl-border); padding: 8px 10px; border-radius: 8px;">
            </div>
            <div>
              <label style="display: block; font-size: 12px; font-weight: 700; margin-bottom: 5px;">{{ __('State') }}</label>
              <input type="text" id="editUserState" name="state" class="search-input" style="width: 100%; border: 1px solid var(--tbl-border); padding: 8px 10px; border-radius: 8px;">
            </div>
            <div>
              <label style="display: block; font-size: 12px; font-weight: 700; margin-bottom: 5px;">{{ __('Postal Code') }}</label>
              <input type="text" id="editUserPostalCode" name="postal_code" class="search-input" style="width: 100%; border: 1px solid var(--tbl-border); padding: 8px 10px; border-radius: 8px;">
            </div>
          </div>
        </div>
        <div style="padding: 14px 20px; background: #F8FAFC; border-top: 1px solid var(--tbl-border); display: flex; justify-content: flex-end; gap: 10px;">
          <button type="button" class="btn-table-reset" onclick="closeEditUserModal()">{{ __('Cancel') }}</button>
          <button type="submit" class="btn-primary" id="saveUserBtn">{{ __('Save Changes') }}</button>
        </div>
      </form>
    </div>
  </div>

  @php
    $customerDetails = $users->mapWithKeys(function ($user) {
        $fullAddress = collect([$user->address, $user->city, $user->state, $user->postal_code])
            ->filter()
            ->implode(', ');

        return [$user->id => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone ?: '',
            'role' => $user->role,
            'address' => $user->address ?: '',
            'city' => $user->city ?: '',
            'state' => $user->state ?: '',
            'postal_code' => $user->postal_code ?: '',
            'fullAddress' => $fullAddress ?: '—',
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
      return '₹' + Number(amount).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
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

    function clearUserSearch() {
      const input = document.getElementById('userSearchInput');
      const btn = document.getElementById('clearUserSearchBtn');
      if (input) {
        input.value = '';
        if (btn) btn.style.display = 'none';
        filterUsers();
        input.focus();
      }
    }

    function filterUsers() {
      const searchInput = document.getElementById('userSearchInput');
      const term = (searchInput ? searchInput.value : '').trim().toLowerCase();
      const clearBtn = document.getElementById('clearUserSearchBtn');
      if (clearBtn) {
        clearBtn.style.display = term ? 'block' : 'none';
      }

      const rows = document.querySelectorAll('#userTableBody tr[data-search]');
      let visible = 0;
      rows.forEach(row => {
        const matchesSearch = !term || (row.dataset.search || '').includes(term);
        const matchesStatus = activeStatus === 'all' || row.dataset.status === activeStatus;
        const match = matchesSearch && matchesStatus;
        row.style.display = match ? '' : 'none';
        if (match) visible++;
      });

      const noMatchRow = document.getElementById('noUserMatchRow');
      if (noMatchRow) {
        noMatchRow.style.display = (visible === 0 && rows.length > 0) ? '' : 'none';
      }

      const countPill = document.getElementById('userCountPill');
      if (countPill) {
        countPill.textContent = `${visible} Users`;
      }
    }

    document.addEventListener('DOMContentLoaded', () => {
      const urlParams = new URLSearchParams(window.location.search);
      const paramSearch = urlParams.get('search') || urlParams.get('q');
      const input = document.getElementById('userSearchInput');
      if (paramSearch && input) {
        input.value = paramSearch;
      }
      filterUsers();
    });

    function viewCustomer(id) {
      const customer = customerDetails[id];
      if (!customer) return;

      document.getElementById('modalCustomerName').textContent = customer.name;
      document.getElementById('modalEmail').textContent = customer.email;
      document.getElementById('modalPhone').textContent = customer.phone || '—';
      document.getElementById('modalAddress').textContent = customer.fullAddress || '—';
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

    function openEditUserModal(id) {
      const customer = customerDetails[id];
      if (!customer) return;

      document.getElementById('editUserId').value = customer.id;
      document.getElementById('editUserName').value = customer.name;
      document.getElementById('editUserEmail').value = customer.email;
      document.getElementById('editUserPhone').value = customer.phone || '';
      document.getElementById('editUserRole').value = customer.role || 'user';
      document.getElementById('editUserAddress').value = customer.address || '';
      document.getElementById('editUserCity').value = customer.city || '';
      document.getElementById('editUserState').value = customer.state || '';
      document.getElementById('editUserPostalCode').value = customer.postal_code || '';

      document.getElementById('editUserModal').classList.add('open');
    }

    function closeEditUserModal() {
      document.getElementById('editUserModal').classList.remove('open');
    }

    function submitEditUserForm(event) {
      event.preventDefault();
      const id = document.getElementById('editUserId').value;
      const btn = document.getElementById('saveUserBtn');

      const payload = {
        name: document.getElementById('editUserName').value,
        email: document.getElementById('editUserEmail').value,
        phone: document.getElementById('editUserPhone').value,
        role: document.getElementById('editUserRole').value,
        address: document.getElementById('editUserAddress').value,
        city: document.getElementById('editUserCity').value,
        state: document.getElementById('editUserState').value,
        postal_code: document.getElementById('editUserPostalCode').value,
      };

      const csrfMeta = document.querySelector('meta[name="csrf-token"]');
      const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

      btn.disabled = true;
      btn.textContent = 'Saving...';

      fetch(`/adminusers/${id}/update`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
      })
      .then(async res => {
        const data = await res.json();
        if (!res.ok || data.success === false) {
          throw new Error(data.message || 'Failed to update user.');
        }
        return data;
      })
      .then(data => {
        closeEditUserModal();
        showAdminToast(data.message || 'User updated successfully.', 'success');
        btn.disabled = false;
        btn.textContent = 'Save Changes';
        if (typeof refreshTable === 'function') {
          refreshTable('#userTableCard');
        }
      })
      .catch(err => {
        btn.disabled = false;
        btn.textContent = 'Save Changes';
        showAdminToast(err.message || 'Error updating user.', 'error');
      });
    }

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') {
        closeCustomerModal();
        closeEditUserModal();
      }
    });
  </script>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="{{ asset('assets/js/admin-tables.js') }}"></script>
  <script src="{{ asset('assets/js/ajax.js') }}"></script>
</body>

</html>
