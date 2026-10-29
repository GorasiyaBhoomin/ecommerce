<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Variants | Shopkart Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
    rel="stylesheet">
  <link href="{{ asset('assets/css/adminproducts.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/css/admin-notifications.css') }}" rel="stylesheet">
</head>

<body>

  <div class="admin-container">
    @include('admin.partials.admin-sidebar', ['active' => 'variants'])

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
              <a href="/adminproducts">Catalog</a>
              <span>/</span>
              <span>Variants</span>
            </div>
            <h1 class="page-heading">Variant Options</h1>
            <div style="font-size: 13px; color: var(--muted);">Reusable variant groups applied across products</div>
          </div>
        </div>

        <div class="header-actions">
          <div class="search-box-wrap">
            <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="8" />
              <path d="m21 21-4.3-4.3" />
            </svg>
            <input type="text" id="variantSearchInput" class="search-input" placeholder="Search variant group..."
              aria-label="Search variants" oninput="filterVariants(this.value)">
          </div>

          <a href="/adminaddvariant" class="btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="12" x2="12" y1="5" y2="19" />
              <line x1="5" x2="19" y1="12" y2="12" />
            </svg>
            Add Variant
          </a>

          @include('admin.partials.notification-bar')
        </div>
      </header>

      <div class="content-inner">
        @if (session('success'))
          <div
            style="background: #ECFDF5; border: 1px solid #A7F3D0; color: #065F46; border-radius: 10px; padding: 12px 16px; margin-bottom: 20px; font-size: 13px; font-weight: 600;">
            {{ session('success') }}
          </div>
        @endif

        <section class="category-section-card">
          <div class="category-card-header">
            <div class="category-title-wrap">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="4" y1="21" x2="4" y2="14" />
                <line x1="4" y1="10" x2="4" y2="3" />
                <line x1="12" y1="21" x2="12" y2="12" />
                <line x1="12" y1="8" x2="12" y2="3" />
                <line x1="20" y1="21" x2="20" y2="16" />
                <line x1="20" y1="12" x2="20" y2="3" />
              </svg>
              <h2 class="category-title">All Variant Groups</h2>
              <span class="category-badge-pill" id="variantCountPill">{{ $variantGroups->count() }} Groups</span>
            </div>
          </div>

          <div class="table-responsive">
            <table class="products-table">
              <thead>
                <tr>
                  <th>Variant Group</th>
                  <th>Values</th>
                  <th>Applied To</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="variantTableBody">
                @forelse ($variantGroups as $variantGroup)
                  @php
                    $appliedCategories = $variantGroup->categories->pluck('name');
                    $valueNames = $variantGroup->values->pluck('value');
                    $searchTerm = strtolower(implode(' ', array_filter(array_merge(
                        [$variantGroup->name, $variantGroup->label],
                        $valueNames->all(),
                        $appliedCategories->all(),
                    ))));
                  @endphp
                  <tr data-search="{{ $searchTerm }}">
                    <td>
                      <div class="product-meta-cell">
                        <div class="product-icon-wrap">
                          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <rect width="18" height="18" x="3" y="3" rx="2" />
                          </svg>
                        </div>
                        <div>
                          <div class="product-name">{{ $variantGroup->name }}</div>
                          <div class="product-subtitle">{{ $variantGroup->label ?: '—' }}</div>
                        </div>
                      </div>
                    </td>
                    <td>
                      <span class="variant-summary-tag">
                        {{ $valueNames->isNotEmpty() ? $valueNames->implode(', ') : '—' }}
                      </span>
                    </td>
                    <td>
                      <span class="stock-count-val">
                        {{ $appliedCategories->isNotEmpty() ? $appliedCategories->implode(', ') : 'Unassigned' }}
                      </span>
                    </td>
                    <td>
                      @if ($variantGroup->is_active)
                        <span class="badge badge-in_stock"><span class="badge-dot"></span>Active</span>
                      @else
                        <span class="badge badge-low_stock"><span class="badge-dot"></span>Hidden</span>
                      @endif
                    </td>
                    <td>
                      <div class="table-actions">
                        <a href="/admineditvariant/{{ $variantGroup->id }}" class="edit-btn">Edit</a>
                        <form method="POST" action="/adminvariants/{{ $variantGroup->id }}" class="delete-variant-form"
                          data-name="{{ $variantGroup->name }}" style="display: inline;">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="delete-btn">Delete</button>
                        </form>
                      </div>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="5" style="text-align: center; padding: 32px; color: var(--muted);">
                      No variant groups yet.
                      <a href="/adminaddvariant" style="color: var(--teal); font-weight: 600;">Add your first variant
                        group</a>.
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </section>
      </div>
    </main>
  </div>

  <script>
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const adminSidebar = document.getElementById('adminSidebar');
    if (mobileMenuBtn && adminSidebar) {
      mobileMenuBtn.addEventListener('click', function () {
        adminSidebar.classList.toggle('open');
      });
    }

    function filterVariants(query) {
      const term = query.trim().toLowerCase();
      const rows = document.querySelectorAll('#variantTableBody tr');
      let visible = 0;
      rows.forEach(row => {
        const match = !term || (row.dataset.search || '').includes(term);
        row.style.display = match ? '' : 'none';
        if (match) visible++;
      });
      document.getElementById('variantCountPill').textContent = `${visible} Groups`;
    }

    document.querySelectorAll('.delete-variant-form').forEach(form => {
      form.addEventListener('submit', function (event) {
        const name = form.dataset.name;
        if (!window.confirm(`Delete the "${name}" variant group? Its values will be removed too.`)) {
          event.preventDefault();
        }
      });
    });
  </script>
</body>

</html>
