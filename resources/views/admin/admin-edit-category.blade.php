<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit {{ $category->name }} | Shopkart Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
    rel="stylesheet">
  <link href="{{ asset('assets/css/adminaddproduct.css') }}" rel="stylesheet">
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
              <a href="/admincategories">Categories</a>
              <span>/</span>
              <span>Edit Category</span>
            </div>
            <h1 class="page-heading">Edit Category</h1>
          </div>
        </div>

        <div class="header-actions">
          <a href="/admincategories" class="btn-secondary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="m12 19-7-7 7-7" />
              <path d="M19 12H5" />
            </svg>
            Back to Categories
          </a>
          @include('admin.partials.notification-bar')
        </div>
      </header>

      <div class="content-inner single-col">
        <div class="form-card" style="margin-bottom: 0;">
          <div class="card-body" style="flex-direction: row; flex-wrap: wrap; gap: 12px; align-items: center;">
            <span class="category-badge-pill">Category ID: CAT-{{ str_pad($category->id, 3, '0', STR_PAD_LEFT) }}</span>
            <span style="font-size: 13px; color: var(--muted);">Last updated
              {{ $category->updated_at?->format('d M Y, h:i A') }}</span>
            <span style="font-size: 13px; color: var(--muted);">Created
              {{ $category->created_at?->format('d M Y') }}</span>
          </div>
        </div>

        @if ($errors->any())
          <div
            style="background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; border-radius: 10px; padding: 12px 16px; margin-top: 24px; font-size: 13px; font-weight: 600;">
            <ul style="margin: 0; padding-left: 18px;">
              @foreach ($errors->all() as $message)
                <li>{{ $message }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form id="categoryForm" method="POST" action="/admineditcategory/{{ $category->id }}"
          onsubmit="return saveCategory(event)" style="margin-top: 24px;">
          @csrf
          <section class="form-card">
            <div class="card-header">
              <h2 class="card-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2Z" />
                </svg>
                Category Details
              </h2>
            </div>
            <div class="card-body">
              <div class="form-row-2">
                <div class="form-group">
                  <label for="categoryName">Category Name</label>
                  <input type="text" id="categoryName" name="name" class="input-text" maxlength="255"
                    value="{{ old('name', $category->name) }}" oninput="autoSlug(this.value)" required>
                </div>
                <div class="form-group">
                  <label for="categorySlug">Slug (URL)</label>
                  <input type="text" id="categorySlug" name="slug" class="input-text" maxlength="255"
                    value="{{ old('slug', $category->slug) }}" required>
                </div>
              </div>

              <div class="form-row-2">
                <div class="form-group">
                  <label for="categoryParent">Parent Category</label>
                  <select id="categoryParent" name="parent_id" class="select-input">
                    <option value="">— None (Top level) —</option>
                    @foreach ($parentCategories as $parentCategory)
                      <option value="{{ $parentCategory->id }}"
                        {{ (string) old('parent_id', $category->parent_id) === (string) $parentCategory->id ? 'selected' : '' }}>
                        {{ $parentCategory->name }}
                      </option>
                    @endforeach
                  </select>
                </div>
                <div class="form-group">
                  <label for="categoryOrder">Display Order</label>
                  <input type="number" id="categoryOrder" name="display_order" class="input-text" min="1"
                    value="{{ old('display_order', $category->display_order) }}">
                </div>
              </div>

              <div class="form-group">
                <label for="categoryDescription">Description</label>
                <textarea id="categoryDescription" name="description" class="textarea-input">{{ old('description', $category->description) }}</textarea>
              </div>
            </div>
          </section>

          <section class="form-card">
            <div class="card-header">
              <h2 class="card-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="10" />
                  <path d="M15 9a3 3 0 0 0-3-2h-1a2.5 2.5 0 0 0 0 5h2a2.5 2.5 0 0 1 0 5h-1a3 3 0 0 1-3-2" />
                  <path d="M12 5v14" />
                </svg>
                Default Pricing (Optional)
              </h2>
            </div>
            <div class="card-body">
              <div class="form-row-2">
                <div class="form-group">
                  <label for="categoryActualPrice">Actual Price / MRP (&#8377;)</label>
                  <input type="number" id="categoryActualPrice" name="actual_price" class="input-text" min="0"
                    step="0.01" value="{{ old('actual_price', $category->actual_price) }}" oninput="updateDiscount()">
                </div>
                <div class="form-group">
                  <label for="categoryDiscountedPrice">Discounted Price (&#8377;)</label>
                  <input type="number" id="categoryDiscountedPrice" name="discounted_price" class="input-text"
                    min="0" step="0.01" value="{{ old('discounted_price', $category->discounted_price) }}"
                    oninput="updateDiscount()">
                </div>
              </div>
              <div class="form-group">
                <label>Discount</label>
                <div>
                  <span class="discount-badge is-none" id="categoryDiscountBadge">—</span>
                </div>
                <div style="font-size: 12px; color: var(--muted);">
                  Used as the fallback price for products without their own pricing.
                </div>
              </div>
            </div>
          </section>

          <section class="form-card">
            <div class="card-header">
              <h2 class="card-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z" />
                  <circle cx="12" cy="12" r="3" />
                </svg>
                Visibility
              </h2>
            </div>
            <div class="card-body">
              <div class="visibility-toggle">
                <input type="checkbox" id="categoryActive" name="is_active" value="1"
                  {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                <label for="categoryActive">Show this category on the storefront</label>
              </div>
            </div>
          </section>

          <div class="form-actions">
            <button type="submit" class="btn-publish">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="m5 12 5 5L20 7" />
              </svg>
              Save Changes
            </button>
            <button type="submit" form="deleteCategoryForm" class="btn-draft">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 6h18" />
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" />
                <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
              </svg>
              Delete Category
            </button>
            <a href="/admincategories" class="btn-cancel">Cancel</a>
          </div>
        </form>

        <form id="deleteCategoryForm" method="POST" action="/admincategories/{{ $category->id }}"
          onsubmit="return confirmDelete('{{ $category->name }}')" style="display: none;">
          @csrf
          @method('DELETE')
        </form>
      </div>
    </main>
  </div>

  <div class="toast-alert" id="toast">
    <span class="toast-icon-success">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
        <path d="M20 6 9 17l-5-5" />
      </svg>
    </span>
    <span class="toast-icon-error">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
        <circle cx="12" cy="12" r="9" />
        <path d="M12 8v4" />
        <path d="M12 16h.01" />
      </svg>
    </span>
    <span id="toastMessage">Category updated.</span>
  </div>

  <script>
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const adminSidebar = document.getElementById('adminSidebar');
    if (mobileMenuBtn && adminSidebar) {
      mobileMenuBtn.addEventListener('click', function () {
        adminSidebar.classList.toggle('open');
      });
    }

    function autoSlug(value) {
      document.getElementById('categorySlug').value = value
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
    }

    function formatDiscount(mrp, price) {
      if (!mrp || mrp <= 0 || !price || price <= 0 || price > mrp) return '—';
      const pct = Math.round(((mrp - price) / mrp) * 100);
      return pct > 0 ? `${pct}% off` : '—';
    }

    function updateDiscount() {
      const mrp = parseFloat(document.getElementById('categoryActualPrice').value) || 0;
      const price = parseFloat(document.getElementById('categoryDiscountedPrice').value) || 0;
      const badge = document.getElementById('categoryDiscountBadge');
      const text = formatDiscount(mrp, price);
      badge.textContent = text;
      badge.classList.toggle('is-none', text === '—');
    }

    function showToast(message, isError) {
      const toast = document.getElementById('toast');
      document.getElementById('toastMessage').textContent = message;
      toast.classList.toggle('is-error', !!isError);
      toast.style.display = 'flex';
      setTimeout(() => { toast.style.display = 'none'; }, 2600);
    }

    function saveCategory(event) {
      const mrp = parseFloat(document.getElementById('categoryActualPrice').value) || 0;
      const price = parseFloat(document.getElementById('categoryDiscountedPrice').value) || 0;
      if (mrp > 0 && price > mrp) {
        event.preventDefault();
        showToast('Discounted price cannot exceed the actual price.', true);
        return false;
      }
      return true;
    }

    function confirmDelete(name) {
      return window.confirm(`Delete the "${name}" category? Products will not be deleted.`);
    }

    updateDiscount();
  </script>
</body>

</html>
