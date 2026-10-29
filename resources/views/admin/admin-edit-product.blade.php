<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Edit Product | Shopkart Admin</title>
  <!-- Google Fonts: Plus Jakarta Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
    rel="stylesheet">
  <link href="{{ asset('assets/css/adminaddproduct.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/css/admineditproduct.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/css/admin-notifications.css') }}" rel="stylesheet">
</head>

<body>

  <div class="admin-container">
    <!-- Left Sidebar -->
    <aside class="admin-sidebar" id="adminSidebar">
      <div class="brand-area">
        <div class="brand-logo-icon">
          <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
            <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z" />
            <path d="M3 6h18" />
            <path d="M16 10a4 4 0 0 1-8 0" />
          </svg>
        </div>
        <div>
          <div class="brand-title">Shopkart</div>
          <span class="brand-badge">Admin Suite</span>
        </div>
      </div>

      <div class="nav-label">Management</div>
      <ul class="sidebar-menu">
        <li>
          <a href="/adminorders" class="menu-link">
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect width="18" height="18" x="3" y="3" rx="2" />
              <path d="M9 3v18" />
              <path d="m14 9 3 3-3 3" />
            </svg>
            Orders
          </a>
        </li>
        <li>
          <a href="/adminproducts" class="menu-link active">
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="m7.5 4.27 9 5.15" />
              <path
                d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z" />
              <path d="m3.3 7 8.7 5 8.7-5" />
              <path d="M12 22V12" />
            </svg>
            Products &amp; Stock
          </a>
        </li>
        <li>
          <a href="/adminaddproduct" class="menu-link">
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10" />
              <path d="M8 12h8" />
              <path d="M12 8v8" />
            </svg>
            Add Product
          </a>
        </li>
        <li>
          <a href="/admincategories" class="menu-link">
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2Z" />
            </svg>
            Categories
          </a>
        </li>
        <li>
          <a href="/adminvariants" class="menu-link">
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="4" y1="21" x2="4" y2="14" />
              <line x1="4" y1="10" x2="4" y2="3" />
              <line x1="12" y1="21" x2="12" y2="12" />
              <line x1="12" y1="8" x2="12" y2="3" />
              <line x1="20" y1="21" x2="20" y2="16" />
              <line x1="20" y1="12" x2="20" y2="3" />
              <line x1="1" y1="14" x2="7" y2="14" />
              <line x1="9" y1="8" x2="15" y2="8" />
              <line x1="17" y1="16" x2="23" y2="16" />
            </svg>
            Variants
          </a>
        </li>
        <li>
          <a href="/adminusers" class="menu-link">
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
              <circle cx="9" cy="7" r="4" />
              <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
              <path d="M16 3.13a4 4 0 0 1 0 7.75" />
            </svg>
            Users
          </a>
        </li>
        <li>
          <a href="/adminprofile" class="menu-link">
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect width="18" height="18" x="3" y="3" rx="2" />
              <path d="M9 3v18" />
              <path d="m14 9 3 3-3 3" />
            </svg>
            Profile
          </a>
        </li>
      </ul>

      <div class="sidebar-footer">
        <a href="/index" class="website-switch-btn" title="View Customer Storefront">
          <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M15 3h6v6" />
            <path d="M10 14 21 3" />
            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" />
          </svg>
          <span>Customer Website</span>
        </a>
      </div>
    </aside>

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
              <a href="/adminproducts">Products</a>
              <span>/</span>
              <span>Edit Product</span>
            </div>
            <h1 class="page-heading">Edit Product &amp; Variants</h1>
          </div>
        </div>
        <div class="header-actions">
          <a href="{{ route('shop.product', $product) }}" class="btn-draft" target="_blank" rel="noopener">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
              <circle cx="12" cy="12" r="3" />
            </svg>
            Preview
          </a>
          <a href="/adminproducts" class="btn-draft">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="m15 18-6-6 6-6" />
            </svg>
            Back to Catalog
          </a>
          @include('admin.partials.notification-bar')
        </div>
      </header>

      <!-- Product Meta Strip -->
      <div class="edit-meta-bar">
        <div class="meta-item">
          <span class="meta-label">Product ID</span>
          <span class="meta-value">#{{ $product->id }}</span>
        </div>
        <div class="meta-item">
          <span class="meta-label">Base SKU</span>
          <span class="meta-value">{{ $product->sku ?: '—' }}</span>
        </div>
        <div class="meta-item">
          <span class="meta-label">Last Updated</span>
          <span class="meta-value">{{ $product->updated_at?->format('d M Y, h:i A') ?? '—' }}</span>
        </div>
        <div class="meta-item">
          <span class="meta-label">Status</span>
          <span class="badge {{ $product->is_active ? 'badge-in_stock' : 'badge-low_stock' }}">
            <span class="badge-dot"></span> {{ $product->is_active ? 'Active' : 'Draft' }}
          </span>
        </div>
      </div>

      <form method="POST" action="{{ route('admin.update.product', $product) }}" enctype="multipart/form-data"
        id="editProductForm">
        @csrf

        @if ($errors->any())
          <div
            style="margin:16px 24px 0;padding:12px 16px;border:1px solid #fecaca;background:#fef2f2;color:#991b1b;border-radius:10px;font-size:13px;">
            <strong>Please fix the following:</strong>
            <ul style="margin:6px 0 0 18px;padding:0;">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <div class="content-inner">
          <!-- Left Column: Form Sections -->
          <div class="form-sections-col">
            <!-- Section A: Basic Info -->
            <section class="form-card" aria-labelledby="secBasicInfo">
              <div class="card-header">
                <h2 class="card-title" id="secBasicInfo">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="12" y1="16" x2="12" y2="12" />
                    <line x1="12" y1="8" x2="12.01" y2="8" />
                  </svg>
                  Section A &bull; Basic Information
                </h2>
              </div>
              <div class="card-body">
                <div class="form-row-2">
                  <div class="form-group">
                    <label for="prodName">Product Name *</label>
                    <input type="text" id="prodName" name="name" class="input-text"
                      placeholder="e.g. Nova X5 Flagship" value="{{ old('name', $product->name) }}" required>
                  </div>
                  <div class="form-group">
                    <label for="prodCategory">Category *</label>
                    <select id="prodCategory" name="category_id" class="select-input" required>
                      <option value="">Select a category</option>
                      @foreach ($categories as $category)
                        <option value="{{ $category->id }}"
                          @selected(old('category_id', $product->category_id) == $category->id)>
                          {{ $category->name }}
                        </option>
                      @endforeach
                    </select>
                  </div>
                </div>

                <div class="form-group">
                  <label for="prodDesc">Product Description</label>
                  <textarea id="prodDesc" name="description" class="textarea-input"
                    placeholder="Enter product overview, key specifications, or highlights...">{{ old('description', $product->description) }}</textarea>
                </div>

                <div class="form-group">
                  <label>Product Images *</label>

                  @if ($product->images->isNotEmpty())
                    <div style="font-size:12px;color:var(--muted);margin-bottom:8px;">
                      Current images &mdash; tick "Remove" to delete one on save.
                    </div>
                    <div class="image-preview-grid" id="existingImageGrid">
                      @foreach ($product->images as $image)
                        <div class="image-preview-item" style="position:relative;">
                          <img src="{{ asset($image->path) }}" alt="Product image {{ $loop->iteration }}"
                            style="width:100%;height:100%;object-fit:cover;">
                          <label
                            style="position:absolute;left:0;right:0;bottom:0;background:rgba(0,0,0,.6);color:#fff;font-size:11px;padding:4px 6px;display:flex;gap:4px;align-items:center;cursor:pointer;">
                            <input type="checkbox" name="remove_image_ids[]" value="{{ $image->id }}"
                              class="remove-image-checkbox" @checked(in_array($image->id, old('remove_image_ids', [])))>
                            Remove
                          </label>
                        </div>
                      @endforeach
                    </div>
                  @endif

                  <label class="image-upload-box" for="fileInput" id="imageUploadBox" style="margin-top:12px;">
                    <input type="file" id="fileInput" name="images[]" style="display: none;" accept="image/*"
                      multiple>
                    <svg class="upload-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                      stroke-width="1.8">
                      <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                      <polyline points="17 8 12 3 7 8" />
                      <line x1="12" y1="3" x2="12" y2="15" />
                    </svg>
                    <div class="upload-text">Add More Product Images</div>
                    <div class="upload-sub">PNG, JPG, WebP up to 5MB each (Square 1:1) &bull; at least 3 images in
                      total
                    </div>
                  </label>

                  <div class="image-preview-grid" id="imagePreviewGrid"></div>

                  <div class="image-count-hint" id="imageCountHint">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                      stroke-width="2">
                      <circle cx="12" cy="12" r="10" />
                      <line x1="12" y1="16" x2="12" y2="12" />
                      <line x1="12" y1="8" x2="12.01" y2="8" />
                    </svg>
                    <span id="imageCountText">0 of minimum 3 images</span>
                  </div>
                </div>
              </div>
            </section>

            <!-- Section B: Variant Options -->
            <section class="form-card" aria-labelledby="secVariantOptions">
              <div class="card-header">
                <h2 class="card-title" id="secVariantOptions">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect width="18" height="18" x="3" y="3" rx="2" />
                    <path d="M3 9h18" />
                    <path d="M9 21V9" />
                  </svg>
                  Section B &bull; Variant Options
                </h2>
                <div class="header-select-wrap">
                  <label for="variantGroupSelect">Variant Group</label>
                  <select id="variantGroupSelect" class="select-input compact-select">
                    <option value="">All Variant Groups</option>
                  </select>
                </div>
              </div>
              <div class="card-body">
                <div class="variant-groups-wrap" id="variantGroupsWrap">
                  <div class="variant-empty-state">
                    Variant groups are defined on the
                    <a href="/adminvariants">Variants</a> page and linked to a category.
                  </div>
                </div>
              </div>
            </section>

            <!-- Section C: Generated Variants Table -->
            <section class="form-card" aria-labelledby="secVariantMatrix">
              <div class="card-header">
                <h2 class="card-title" id="secVariantMatrix">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 3h18v18H3zM9 3v18M15 3v18M3 9h18M3 15h18" />
                  </svg>
                  Section C &bull; Generated Variants &amp; Pricing Table
                </h2>
                <div class="header-select-wrap">
                  <label for="pricingGroupSelect">Filter</label>
                  <select id="pricingGroupSelect" class="select-input compact-select">
                    <option value="">All Variants</option>
                  </select>
                  <span class="matrix-count-tag" id="matrixCountTag">0 combinations generated</span>
                </div>
              </div>

              <div class="table-responsive">
                <table class="variants-table">
                  <thead>
                    <tr>
                      <th>Variant</th>
                      <th>SKU</th>
                      <th>Actual Price (&#8377;)</th>
                      <th>Discounted Price (&#8377;)</th>
                      <th>Discount</th>
                      <th>Stock Qty</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody id="variantsTableBody">
                    <tr class="empty-row">
                      <td colspan="7">No variants generated yet. Select variant values in Section B.</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </section>

            <!-- Section D: Technical Specifications -->
            <section class="form-card" id="secTechnicalSpecs">
              <div class="card-header">
                <h2 class="card-title">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z" />
                    <polyline points="14 2 14 8 20 8" />
                    <line x1="16" y1="13" x2="8" y2="13" />
                    <line x1="16" y1="17" x2="8" y2="17" />
                    <line x1="10" y1="9" x2="8" y2="9" />
                  </svg>
                  Section D &bull; Technical Specifications
                </h2>
              </div>
              <div class="card-body">
                <div id="specsContainer"></div>
                <button type="button" class="btn-draft" id="addSpecBtn" style="margin-top:10px;">+ Add
                  specification</button>
              </div>
            </section>
          </div>

          <!-- Right Column: Sticky Summary Card -->
          <aside class="summary-col">
            <div class="summary-card-wrap">
              <h2 class="summary-heading">Product Summary</h2>

              <div class="metric-row">
                <span>Target Category</span>
                <span class="metric-val" id="summaryCategory">&mdash;</span>
              </div>

              <div class="metric-row">
                <span>Total Variants</span>
                <span class="metric-val" id="summaryVariantCount">0 combinations</span>
              </div>

              <div class="metric-row">
                <span>Price Range</span>
                <span class="metric-val" id="summaryPriceRange">&mdash;</span>
              </div>

              <div class="metric-row">
                <span>Offer / Discount</span>
                <span class="metric-val" id="summaryDiscount">&mdash;</span>
              </div>

              <div class="metric-row">
                <span>Total Stock</span>
                <span class="metric-val" id="summaryTotalStock">0 units</span>
              </div>

              <div class="visibility-toggle">
                <input type="checkbox" id="showOnWebsite" name="is_active" value="1"
                  @checked(old('is_active', $product->is_active))>
                <label for="showOnWebsite">
                  <strong>Show on website</strong>
                  <div style="font-size: 11px; color: var(--muted);">Visible to customer storefront catalog</div>
                </label>
              </div>

              <div style="display: flex; flex-direction: column; gap: 10px;">
                <button type="submit" class="btn-publish">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2">
                    <path d="M20 6 9 17l-5-5" />
                  </svg>
                  Update Product
                </button>
                <button type="button" class="btn-draft" id="saveDraftBtn">
                  Save Changes as Draft
                </button>
                <button type="button" class="btn-delete" disabled title="Delete route not added yet">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2">
                    <path d="M3 6h18" />
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                  </svg>
                  Delete Product
                </button>
                <a href="/adminproducts" class="btn-cancel">Cancel and discard</a>
              </div>
            </div>
          </aside>
        </div>
      </form>
    </main>
  </div>

  @php
    $existingVariantsData = $product->variants->map(fn ($v) => [
        'id' => $v->id,
        'variant_value_ids' => $v->values->pluck('id')->map(fn ($id) => (int) $id)->all(),
        'sku' => $v->sku,
        'actual_price' => $v->actual_price,
        'discounted_price' => $v->discounted_price,
        'stock_qty' => $v->stock_qty,
    ])->values()->all();

    $existingSpecsData = $product->specifications->map(fn ($s) => [
        'label' => $s->label,
        'value' => $s->value,
    ])->values()->all();
  @endphp

  <script>
    window.variantGroups = @json($variantGroups);
    window.productSku = @json($product->sku);
    // After a failed validation, show what the admin typed instead of the saved data
    window.existingVariants = @json(old('variants', $existingVariantsData));
    window.existingSpecs = @json(old('specs', $existingSpecsData));

    (function () {
      /* ---------- helpers ---------- */
      function $(id) { return document.getElementById(id); }

      function el(tag, props, kids) {
        var n = document.createElement(tag);
        Object.keys(props || {}).forEach(function (k) {
          if (k === 'text') n.textContent = props[k];
          else if (k === 'class') n.className = props[k];
          else n.setAttribute(k, props[k]);
        });
        (kids || []).forEach(function (c) { n.appendChild(c); });
        return n;
      }

      function money(n) {
        return '\u20B9' + Number(n).toLocaleString('en-IN', { maximumFractionDigits: 2 });
      }

      function keyOf(ids) {
        return ids.map(Number).sort(function (a, b) { return a - b; }).join(',');
      }

      /* ---------- mobile sidebar ---------- */
      var menuBtn = $('mobileMenuBtn');
      var sidebar = $('adminSidebar');
      if (menuBtn && sidebar) {
        menuBtn.addEventListener('click', function () { sidebar.classList.toggle('open'); });
      }

      /* ---------- data ---------- */
      var groups = window.variantGroups || [];
      var existing = window.existingVariants || [];
      var existingSpecs = window.existingSpecs || [];

      var valueInfo = {};   // valueId -> { text, suffix, groupId }
      groups.forEach(function (g) {
        g.values.forEach(function (v) {
          valueInfo[v.id] = { text: v.value, suffix: v.sku_suffix, groupId: g.id };
        });
      });

      var selected = {};    // valueId -> true
      var rowState = {};    // combo key -> saved row data
      var currentCombos = [];

      existing.forEach(function (v) {
        var ids = (v.variant_value_ids || []).map(Number);
        ids.forEach(function (id) { selected[id] = true; });
        rowState[keyOf(ids)] = {
          id: v.id || '',
          sku: v.sku || '',
          actual: v.actual_price === null || v.actual_price === undefined ? '' : v.actual_price,
          discounted: v.discounted_price || '',
          stock: v.stock_qty === null || v.stock_qty === undefined ? 0 : v.stock_qty
        };
        currentCombos.push(ids);
      });

      /* ---------- elements ---------- */
      var categorySelect = $('prodCategory');
      var groupsWrap = $('variantGroupsWrap');
      var groupFilter = $('variantGroupSelect');
      var rowFilter = $('pricingGroupSelect');
      var tbody = $('variantsTableBody');

      function categoryId() { return parseInt(categorySelect.value, 10) || 0; }

      function availableGroups() {
        var cat = categoryId();
        return groups.filter(function (g) {
          return g.category_ids.indexOf(cat) !== -1 ||
            g.values.some(function (v) { return selected[v.id]; });
        });
      }

      /* ---------- images ---------- */
      var fileInput = $('fileInput');
      var previewGrid = $('imagePreviewGrid');
      var countText = $('imageCountText');
      var countHint = $('imageCountHint');

      function existingImageTotal() {
        return document.querySelectorAll('#existingImageGrid .image-preview-item').length;
      }

      function updateImageCount() {
        var removed = document.querySelectorAll('.remove-image-checkbox:checked').length;
        var added = fileInput.files ? fileInput.files.length : 0;
        var total = existingImageTotal() - removed + added;
        countText.textContent = total + ' of minimum 3 images';
        countHint.style.color = total >= 3 ? '#15803d' : '#b45309';
        return total;
      }

      fileInput.addEventListener('change', function () {
        previewGrid.innerHTML = '';
        Array.prototype.forEach.call(fileInput.files, function (file) {
          var img = el('img', { alt: file.name, style: 'width:100%;height:100%;object-fit:cover;' });
          img.src = URL.createObjectURL(file);
          previewGrid.appendChild(el('div', { class: 'image-preview-item' }, [img]));
        });
        updateImageCount();
      });

      document.querySelectorAll('.remove-image-checkbox').forEach(function (cb) {
        cb.addEventListener('change', updateImageCount);
      });

      /* ---------- Section B: variant value picker ---------- */
      function renderFilters() {
        var avail = availableGroups();

        var keepGroup = groupFilter.value;
        groupFilter.innerHTML = '';
        groupFilter.appendChild(el('option', { value: '', text: 'All Variant Groups' }));
        avail.forEach(function (g) {
          groupFilter.appendChild(el('option', { value: g.id, text: g.label || g.name }));
        });
        groupFilter.value = avail.some(function (g) { return String(g.id) === keepGroup; }) ? keepGroup : '';

        var keepRow = rowFilter.value;
        rowFilter.innerHTML = '';
        rowFilter.appendChild(el('option', { value: '', text: 'All Variants' }));
        avail.forEach(function (g) {
          var og = el('optgroup', { label: g.label || g.name });
          g.values.forEach(function (v) {
            og.appendChild(el('option', { value: v.id, text: v.value }));
          });
          rowFilter.appendChild(og);
        });
        rowFilter.value = keepRow;
        if (rowFilter.value !== keepRow) rowFilter.value = '';
      }

      function renderGroups() {
        groupsWrap.innerHTML = '';
        var avail = availableGroups();
        var only = groupFilter.value;

        if (!avail.length) {
          var empty = el('div', { class: 'variant-empty-state' });
          empty.appendChild(document.createTextNode(
            categoryId()
              ? 'No variant groups are linked to this category. Link one on the '
              : 'Select a category to see its variant groups. Groups are defined on the '
          ));
          empty.appendChild(el('a', { href: '/adminvariants', text: 'Variants' }));
          empty.appendChild(document.createTextNode(' page.'));
          groupsWrap.appendChild(empty);
          return;
        }

        avail.forEach(function (g) {
          if (only && String(g.id) !== only) return;

          var chips = el('div', { style: 'display:flex;flex-wrap:wrap;gap:8px;' });
          g.values.forEach(function (v) {
            var cb = el('input', { type: 'checkbox', value: v.id });
            cb.checked = !!selected[v.id];
            cb.addEventListener('change', function () {
              if (cb.checked) selected[v.id] = true; else delete selected[v.id];
              rebuildTable();
              renderFilters();
            });
            chips.appendChild(el('label', {
              style: 'display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border:1px solid var(--border,#e5e7eb);border-radius:999px;font-size:13px;cursor:pointer;'
            }, [cb, el('span', { text: v.value })]));
          });

          groupsWrap.appendChild(el('div', { class: 'variant-group-block', style: 'margin-bottom:14px;' }, [
            el('div', { text: g.label || g.name, style: 'font-weight:700;font-size:13px;margin-bottom:8px;' }),
            chips
          ]));
        });
      }

      /* ---------- Section C: variants table ---------- */
      function captureRows() {
        tbody.querySelectorAll('tr[data-key]').forEach(function (tr) {
          rowState[tr.dataset.key] = {
            id: tr.dataset.variantId || '',
            sku: tr.querySelector('.f-sku').value,
            actual: tr.querySelector('.f-actual').value,
            discounted: tr.querySelector('.f-discounted').value,
            stock: tr.querySelector('.f-stock').value
          };
        });
      }

      function cartesian() {
        var lists = availableGroups().map(function (g) {
          return g.values.filter(function (v) { return selected[v.id]; }).map(function (v) { return v.id; });
        }).filter(function (l) { return l.length; });

        if (!lists.length) return [];
        return lists.reduce(function (acc, list) {
          var out = [];
          acc.forEach(function (a) { list.forEach(function (b) { out.push(a.concat([b])); }); });
          return out;
        }, [[]]);
      }

     function autoSku(ids) {
  var parts = ids.map(function (id) {
    var info = valueInfo[id];
    return String(info.suffix || info.text).toUpperCase().replace(/[^A-Z0-9]+/g, '');
  });
  // Use the prefix of the first saved variant (e.g. IP15P) instead of the long product SKU
  var firstSku = existing.length && existing[0].sku ? existing[0].sku : '';
  var prefix = firstSku ? firstSku.split('-')[0] : (window.productSku || '');
  return [prefix].concat(parts).filter(Boolean).join('-');
}

      function rebuildTable() {
        captureRows();
        currentCombos = cartesian();
        renderTable();
      }

      function numInput(cls, name, value, min) {
        return el('input', {
          type: 'number', class: 'input-text ' + cls, name: name, min: min, step: min === '0' && cls === 'f-stock' ? '1' : '0.01',
          value: value === null || value === undefined ? '' : value, style: 'min-width:90px;'
        });
      }

      function renderTable() {
        tbody.innerHTML = '';

        if (!currentCombos.length) {
          tbody.appendChild(el('tr', { class: 'empty-row' }, [
            el('td', { colspan: '7', text: 'No variants generated yet. Select variant values in Section B.' })
          ]));
          updateSummary();
          return;
        }

        currentCombos.forEach(function (ids, i) {
          var key = keyOf(ids);
          var st = rowState[key] || { id: '', sku: autoSku(ids), actual: '', discounted: '', stock: 0 };
          var prefix = 'variants[' + i + ']';

          var nameCell = el('td', {
            text: ids.map(function (id) { return valueInfo[id].text; }).join(' / '),
            style: 'font-weight:600;white-space:nowrap;'
          });
          if (st.id) nameCell.appendChild(el('input', { type: 'hidden', name: prefix + '[id]', value: st.id }));
          ids.forEach(function (id) {
            nameCell.appendChild(el('input', { type: 'hidden', name: prefix + '[variant_value_ids][]', value: id }));
          });

          var sku = el('input', {
            type: 'text', class: 'input-text f-sku', name: prefix + '[sku]', value: st.sku || '', style: 'min-width:130px;'
          });
          var actual = numInput('f-actual', prefix + '[actual_price]', st.actual, '0');
          actual.required = true;
          var discounted = numInput('f-discounted', prefix + '[discounted_price]', st.discounted, '0');
          var stock = numInput('f-stock', prefix + '[stock_qty]', st.stock, '0');

          var discountCell = el('td', { class: 'f-discount-cell', text: '\u2014' });
          var statusCell = el('td');

          var tr = el('tr', { 'data-key': key, 'data-values': ids.join(',') }, [
            nameCell,
            el('td', {}, [sku]),
            el('td', {}, [actual]),
            el('td', {}, [discounted]),
            discountCell,
            el('td', {}, [stock]),
            statusCell
          ]);
          if (st.id) tr.dataset.variantId = st.id;

          [actual, discounted, stock].forEach(function (inp) {
            inp.addEventListener('input', function () { refreshRow(tr); updateSummary(); });
          });

          tbody.appendChild(tr);
          refreshRow(tr);
        });

        applyRowFilter();
        updateSummary();
      }

      function refreshRow(tr) {
        var actual = parseFloat(tr.querySelector('.f-actual').value) || 0;
        var disc = parseFloat(tr.querySelector('.f-discounted').value) || 0;
        var stock = parseInt(tr.querySelector('.f-stock').value, 10) || 0;

        var pct = actual > 0 && disc > 0 && disc <= actual ? Math.round((1 - disc / actual) * 100) : 0;
        tr.querySelector('.f-discount-cell').textContent = pct ? pct + '% off' : '\u2014';

        var cls = 'badge-in_stock', label = 'In Stock';
        if (stock === 0) { cls = 'badge-out_of_stock'; label = 'Out of Stock'; }
        else if (stock <= 5) { cls = 'badge-low_stock'; label = 'Low Stock'; }

        var statusCell = tr.lastElementChild;
        statusCell.innerHTML = '';
        statusCell.appendChild(el('span', { class: 'badge ' + cls }, [
          el('span', { class: 'badge-dot' }),
          document.createTextNode(' ' + label)
        ]));
      }

      function applyRowFilter() {
        var f = rowFilter.value;
        tbody.querySelectorAll('tr[data-key]').forEach(function (tr) {
          var ids = tr.dataset.values.split(',');
          tr.style.display = !f || ids.indexOf(f) !== -1 ? '' : 'none';
        });
      }

      /* ---------- Summary ---------- */
      function updateSummary() {
        var opt = categorySelect.options[categorySelect.selectedIndex];
        $('summaryCategory').textContent = opt && opt.value ? opt.text.trim() : '\u2014';

        var rows = tbody.querySelectorAll('tr[data-key]');
        $('matrixCountTag').textContent = rows.length + ' combinations generated';
        $('summaryVariantCount').textContent = rows.length + ' combinations';

        var prices = [], maxPct = 0, stock = 0;
        rows.forEach(function (tr) {
          var actual = parseFloat(tr.querySelector('.f-actual').value) || 0;
          var disc = parseFloat(tr.querySelector('.f-discounted').value) || 0;
          var eff = disc > 0 && disc <= actual ? disc : actual;
          if (eff > 0) prices.push(eff);
          if (actual > 0 && disc > 0 && disc <= actual) {
            maxPct = Math.max(maxPct, Math.round((1 - disc / actual) * 100));
          }
          stock += parseInt(tr.querySelector('.f-stock').value, 10) || 0;
        });

        if (prices.length) {
          var lo = Math.min.apply(null, prices), hi = Math.max.apply(null, prices);
          $('summaryPriceRange').textContent = lo === hi ? money(lo) : money(lo) + ' \u2013 ' + money(hi);
        } else {
          $('summaryPriceRange').textContent = '\u2014';
        }
        $('summaryDiscount').textContent = maxPct ? 'Up to ' + maxPct + '% off' : '\u2014';
        $('summaryTotalStock').textContent = stock + ' units';
      }

      /* ---------- Section D: specifications ---------- */
      var specsContainer = $('specsContainer');

      function renumberSpecs() {
        specsContainer.querySelectorAll('.spec-row').forEach(function (row, i) {
          row.querySelector('.s-label').name = 'specs[' + i + '][label]';
          row.querySelector('.s-value').name = 'specs[' + i + '][value]';
        });
      }

      function addSpecRow(label, value) {
        var row = el('div', { class: 'spec-row form-row-2', style: 'margin-bottom:10px;align-items:center;' });
        var l = el('input', { type: 'text', class: 'input-text s-label', placeholder: 'Label (e.g. Battery)', maxlength: '255' });
        var v = el('input', { type: 'text', class: 'input-text s-value', placeholder: 'Value (e.g. 5000 mAh)', maxlength: '255' });
        l.value = label || '';
        v.value = value || '';
        var rm = el('button', { type: 'button', class: 'btn-draft', text: 'Remove', style: 'flex:0 0 auto;' });
        rm.addEventListener('click', function () { row.remove(); renumberSpecs(); });

        row.appendChild(l);
        row.appendChild(el('div', { style: 'display:flex;gap:8px;' }, [v, rm]));
        specsContainer.appendChild(row);
        renumberSpecs();
      }

      $('addSpecBtn').addEventListener('click', function () { addSpecRow('', ''); });

      /* ---------- Events ---------- */
      categorySelect.addEventListener('change', function () {
        // Drop selected values that belong to groups not linked to the new category
        var cat = categoryId();
        groups.forEach(function (g) {
          if (g.category_ids.indexOf(cat) === -1) {
            g.values.forEach(function (v) { delete selected[v.id]; });
          }
        });
        renderFilters();
        renderGroups();
        rebuildTable();
      });

      groupFilter.addEventListener('change', renderGroups);
      rowFilter.addEventListener('change', applyRowFilter);

      $('saveDraftBtn').addEventListener('click', function () {
        $('showOnWebsite').checked = false;
        $('editProductForm').requestSubmit();
      });

      $('editProductForm').addEventListener('submit', function (e) {
        if (updateImageCount() < 3) {
          e.preventDefault();
          alert('A product needs at least 3 images.');
          return;
        }
        if (!tbody.querySelector('tr[data-key]') &&
          !confirm('No variants are selected. Saving will remove all existing variants. Continue?')) {
          e.preventDefault();
        }
      });

      /* ---------- Initial render ---------- */
      renderFilters();
      renderGroups();
      renderTable();           // existing variants as saved (not a full cartesian product)
      (existingSpecs.length ? existingSpecs : [{ label: '', value: '' }]).forEach(function (s) {
        addSpecRow(s.label, s.value);
      });
      updateImageCount();
    })();
  </script>
</body>

</html>