<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Categories | Shopkart Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
    rel="stylesheet">
  <link href="{{ asset('assets/css/adminproducts.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/css/admin-notifications.css') }}" rel="stylesheet">
</head>

<body>

  <div class="admin-container">
    @include('admin.partials.admin-sidebar', ['active' => 'categories'])

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
              <span>Categories</span>
            </div>
            <h1 class="page-heading">Categories</h1>
            <div style="font-size: 13px; color: var(--muted);">Organise products into storefront categories</div>
          </div>
        </div>

        <div class="header-actions">
          <div class="search-box-wrap">
            <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="8" />
              <path d="m21 21-4.3-4.3" />
            </svg>
            <input type="text" id="categorySearchInput" class="search-input" placeholder="Search category..."
              aria-label="Search categories" oninput="filterCategories(this.value)">
          </div>

          <a href="/adminaddcategory" class="btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="12" x2="12" y1="5" y2="19" />
              <line x1="5" x2="19" y1="12" y2="12" />
            </svg>
            Add Category
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
                <path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2Z" />
              </svg>
              <h2 class="category-title">All Categories</h2>
              <span class="category-badge-pill" id="categoryCountPill">{{ $categories->count() }} Categories</span>
            </div>
          </div>

          <div class="table-responsive">
            <table class="products-table">
              <thead>
                <tr>
                  <th>Category</th>
                  <th>Slug</th>
                  <th>Parent</th>
                  <th>Order</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="categoryTableBody">
                @forelse ($categories as $category)
                  @php
                    $parentName = $category->parent->name ?? null;
                    $searchText = strtolower(trim(
                        $category->name.' '.$category->slug.' '.($parentName ?? '').' '.($category->description ?? '')
                    ));
                  @endphp
                  <tr data-search="{{ $searchText }}">
                    <td>
                      <div class="product-meta-cell">
                        <div class="product-icon-wrap">
                          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2Z" />
                          </svg>
                        </div>
                        <div>
                          <div class="product-name">{{ $category->name }}</div>
                          @if ($category->description)
                            <div class="product-subtitle">{{ \Illuminate\Support\Str::limit($category->description, 42) }}</div>
                          @endif
                        </div>
                      </div>
                    </td>
                    <td><code style="font-size: 11px; background: #F1F5F9; padding: 2px 6px; border-radius: 4px;">{{ $category->slug }}</code></td>
                    <td>{{ $parentName ?? '—' }}</td>
                    <td><span class="stock-count-val">{{ $category->display_order }}</span></td>
                    <td>
                      @if ($category->is_active)
                        <span class="badge badge-in_stock"><span class="badge-dot"></span>Active</span>
                      @else
                        <span class="badge badge-low_stock"><span class="badge-dot"></span>Hidden</span>
                      @endif
                    </td>
                    <td>
                      <div class="table-actions">
                        <a href="/admineditcategory/{{ $category->id }}" class="edit-btn">Edit</a>
                        <form method="POST" action="/admincategories/{{ $category->id }}"
                          onsubmit="return confirmDelete('{{ $category->name }}')" style="margin: 0;">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="delete-btn">Delete</button>
                        </form>
                      </div>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="6" style="text-align: center; padding: 36px 16px; color: var(--muted); font-size: 13px;">
                      No categories yet.
                      <a href="/adminaddcategory" style="color: var(--primary-teal); font-weight: 700;">Add your first category</a>.
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

    function filterCategories(query) {
      const term = query.trim().toLowerCase();
      const rows = document.querySelectorAll('#categoryTableBody tr');
      let visible = 0;
      rows.forEach(row => {
        const match = !term || (row.dataset.search || '').includes(term);
        row.style.display = match ? '' : 'none';
        if (match) visible++;
      });
      document.getElementById('categoryCountPill').textContent = `${visible} Categories`;
    }

    function confirmDelete(name) {
      return window.confirm(`Delete the "${name}" category? Products will not be deleted.`);
    }
  </script>
</body>

</html>
