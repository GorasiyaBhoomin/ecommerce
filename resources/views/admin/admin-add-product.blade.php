<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Add Product | Shopkart Admin</title>
  <!-- Google Fonts: Plus Jakarta Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
    rel="stylesheet">
  <link href="{{ asset('assets/css/adminaddproduct.css') }}" rel="stylesheet">
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
          <a href="/adminproducts" class="menu-link">
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
          <a href="/adminaddproduct" class="menu-link active">
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
              <span>New Product</span>
            </div>
            <h1 class="page-heading">Add Product &amp; Variants</h1>
          </div>
        </div>
        <div class="header-actions">
          <a href="/adminproducts" class="btn-draft">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="m15 18-6-6 6-6" />
            </svg>
            Back to Catalog
          </a>
          @include('admin.partials.notification-bar')
        </div>
      </header>

      @if (session('success') || $errors->any())
        <div style="padding: 20px 28px 0;">
          @if (session('success'))
            <div
              style="background: #ECFDF5; border: 1px solid #A7F3D0; color: #065F46; border-radius: 10px; padding: 12px 16px; margin-bottom: 20px; font-size: 13px; font-weight: 600;">
              {{ session('success') }}
            </div>
          @endif
          @if ($errors->any())
            <div
              style="background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; border-radius: 10px; padding: 12px 16px; margin-bottom: 20px; font-size: 13px; font-weight: 600;">
              <ul style="margin: 0; padding-left: 18px;">
                @foreach ($errors->all() as $message)
                  <li>{{ $message }}</li>
                @endforeach
              </ul>
            </div>
          @endif
        </div>
      @endif

      <form class="content-inner" id="addProductForm" method="POST" action="{{ route('admin.products.create') }}"
        enctype="multipart/form-data" onsubmit="return validateProductForm(event)">
        @csrf

        <!-- Left Column: 3 Form Sections -->
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
                    placeholder="e.g. Nova X5 Flagship" value="{{ old('name') }}" maxlength="255" required>
                </div>
                <div class="form-group">
                  <label for="prodCategory">Category *</label>
                  <select id="prodCategory" name="category_id" class="select-input" required>
                    <option value="">Select a category</option>
                    @foreach ($categories as $category)
                      <option value="{{ $category->id }}"
                        {{ (string) old('category_id') === (string) $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                      </option>
                    @endforeach
                  </select>
                </div>
              </div>

              <div class="form-group">
                <label for="prodDesc">Product Description</label>
                <textarea id="prodDesc" name="description" class="textarea-input"
                  placeholder="Enter product overview, key specifications, or highlights...">{{ old('description') }}</textarea>
              </div>

              <div class="form-group">
                <label>Product Images *</label>
                <label class="image-upload-box" for="fileInput" id="imageUploadBox">
                  <input type="file" id="fileInput" name="images[]" style="display: none;" accept="image/*" multiple
                    required>
                  <svg class="upload-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                    <polyline points="17 8 12 3 7 8" />
                    <line x1="12" y1="3" x2="12" y2="15" />
                  </svg>
                  <div class="upload-text">Upload High-Res Product Images</div>
                  <div class="upload-sub">Select at least 3 images &bull; PNG, JPG, WebP up to 5MB each (Square 1:1)
                  </div>
                </label>

                <div class="image-preview-grid" id="imagePreviewGrid"></div>

                <div class="image-count-hint" id="imageCountHint">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="12" y1="16" x2="12" y2="12" />
                    <line x1="12" y1="8" x2="12.01" y2="8" />
                  </svg>
                  <span id="imageCountText">0 of minimum 3 images uploaded</span>
                </div>
              </div>
            </div>
          </section>

          <!-- Section B: Variant Options (values defined on the Variants page) -->
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
                  @foreach ($variantGroups as $variantGroup)
                    <option value="{{ $variantGroup['id'] }}">
                      {{ $variantGroup['label'] ?: $variantGroup['name'] }}
                    </option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="card-body">
              <div class="variant-groups-wrap" id="variantGroupsWrap">
                <div class="variant-empty-state">Select a category to load its variant groups.</div>
              </div>
            </div>
          </section>

          <!-- Section C: Auto-generated Variants Table -->
          <section class="form-card" aria-labelledby="secVariantMatrix">
            <div class="card-header">
              <h2 class="card-title" id="secVariantMatrix">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M3 3h18v18H3zM9 3v18M15 3v18M3 9h18M3 15h18" />
                </svg>
                Section C &bull; Generated Variants &amp; Pricing Table
              </h2>
              <div class="header-select-wrap">
                <label for="pricingGroupSelect">Variant Group</label>
                <select id="pricingGroupSelect" class="select-input compact-select">
                  <option value="">All Variants</option>
                  @foreach ($variantGroups as $variantGroup)
                    <option value="{{ $variantGroup['id'] }}">
                      {{ $variantGroup['label'] ?: $variantGroup['name'] }}
                    </option>
                  @endforeach
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
            <div class="card-body" id="specsContainer">
              <div class="variant-groups-wrap" id="specsList">
                @php($specRows = old('specs', [['label' => '', 'value' => '']]))
                @foreach ($specRows as $index => $specRow)
                  <div class="spec-row">
                    <input type="text" class="input-text" name="specs[{{ $index }}][label]"
                      value="{{ $specRow['label'] ?? '' }}" placeholder="Specification (e.g. Display)" maxlength="255">
                    <input type="text" class="input-text" name="specs[{{ $index }}][value]"
                      value="{{ $specRow['value'] ?? '' }}" placeholder="Value (e.g. 6.7-inch AMOLED)" maxlength="255">
                    <button type="button" class="chip-remove-btn" aria-label="Remove specification">&#215;</button>
                  </div>
                @endforeach
              </div>
              <button type="button" class="btn-draft" id="addSpecBtn" style="align-self: flex-start;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="12" x2="12" y1="5" y2="19" />
                  <line x1="5" x2="19" y1="12" y2="12" />
                </svg>
                Add specification
              </button>
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
                {{ old('is_active', '1') ? 'checked' : '' }}>
              <label for="showOnWebsite">
                <strong>Show on website</strong>
                <div style="font-size: 11px; color: var(--muted);">Visible to customer storefront catalog</div>
              </label>
            </div>

            <div style="display: flex; flex-direction: column; gap: 10px;">
              <button type="submit" name="draft" value="0" class="btn-publish">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M5 12h14M12 5l7 7-7 7" />
                </svg>
                Publish Product
              </button>
              <button type="submit" name="draft" value="1" class="btn-draft">
                Save as Draft
              </button>
              <a href="/adminproducts" class="btn-cancel">Cancel and discard</a>
            </div>
          </div>
        </aside>
      </form>
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
    <span id="toastMessage">Product saved.</span>
  </div>

  <script>
    (function () {
      'use strict';

      var variantGroups = @json($variantGroups);
      var oldVariants = @json(old('variants', []));
      var minimumImages = 3;

      var nameInput = document.getElementById('prodName');
      var categorySelect = document.getElementById('prodCategory');
      var groupFilter = document.getElementById('variantGroupSelect');
      var pricingFilter = document.getElementById('pricingGroupSelect');
      var groupsWrap = document.getElementById('variantGroupsWrap');
      var tableBody = document.getElementById('variantsTableBody');
      var countTag = document.getElementById('matrixCountTag');
      var form = document.getElementById('addProductForm');

      var summaryCategory = document.getElementById('summaryCategory');
      var summaryVariantCount = document.getElementById('summaryVariantCount');
      var summaryPriceRange = document.getElementById('summaryPriceRange');
      var summaryDiscount = document.getElementById('summaryDiscount');
      var summaryTotalStock = document.getElementById('summaryTotalStock');

      var valueIndex = {};
      var groupOrder = {};

      variantGroups.forEach(function (group, index) {
        groupOrder[group.id] = index;
        group.values.forEach(function (value) {
          valueIndex[value.id] = {
            value: value.value,
            skuSuffix: value.sku_suffix,
            groupId: group.id
          };
        });
      });

      // Selected variant value ids, plus per-combination pricing entered by the admin.
      var selectedValues = new Set();
      var rowState = {};
      var previousBaseSku = '';
      var lastRows = [];

      // Restore a previously submitted (invalid) matrix so entered prices are not lost.
      oldVariants.forEach(function (variant) {
        var ids = (variant.variant_value_ids || []).map(Number).filter(function (id) {
          return !isNaN(id);
        });

        ids.forEach(function (id) {
          selectedValues.add(id);
        });

        rowState[combinationKey(ids)] = {
          sku: variant.sku || '',
          actual: variant.actual_price != null ? String(variant.actual_price) : '',
          discounted: variant.discounted_price != null ? String(variant.discounted_price) : '',
          stock: variant.stock_qty != null ? String(variant.stock_qty) : '0'
        };
      });

      function combinationKey(valueIds) {
        return valueIds.slice().sort(function (a, b) {
          return a - b;
        }).join(',');
      }

      function escapeHtml(text) {
        return String(text == null ? '' : text)
          .replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;');
      }

      function baseSku() {
        return (nameInput.value || '').toUpperCase().replace(/[^A-Z0-9]+/g, '-').replace(/^-+|-+$/g, '');
      }

      function defaultSku(valueIds) {
        var base = baseSku();
        if (!base) {
          return '';
        }

        var parts = valueIds.map(function (id) {
          var info = valueIndex[id];
          return info ? (info.skuSuffix || info.value) : '';
        }).map(function (part) {
          return String(part).toUpperCase().replace(/[^A-Z0-9]+/g, '');
        }).filter(function (part) {
          return part !== '';
        });

        return parts.length ? base + '-' + parts.join('-') : base;
      }

      function selectedGroups() {
        var byGroup = {};

        selectedValues.forEach(function (valueId) {
          var info = valueIndex[valueId];
          if (!info) {
            return;
          }
          if (!byGroup[info.groupId]) {
            byGroup[info.groupId] = [];
          }
          byGroup[info.groupId].push(valueId);
        });

        return Object.keys(byGroup).map(Number).sort(function (a, b) {
          return groupOrder[a] - groupOrder[b];
        }).map(function (groupId) {
          return {
            groupId: groupId,
            valueIds: byGroup[groupId]
          };
        });
      }

      function buildCombinations() {
        var groups = selectedGroups();
        if (!groups.length) {
          return [];
        }

        var combinations = [[]];

        groups.forEach(function (group) {
          var next = [];
          combinations.forEach(function (combination) {
            group.valueIds.forEach(function (valueId) {
              next.push(combination.concat([valueId]));
            });
          });
          combinations = next;
        });

        return combinations;
      }

      function renderGroups() {
        var categoryId = parseInt(categorySelect.value, 10);
        var filterId = groupFilter.value ? parseInt(groupFilter.value, 10) : null;
        var rows = [];

        if (!categoryId) {
          groupsWrap.innerHTML =
            '<div class="variant-empty-state">Select a category to load its variant groups.</div>';
          return;
        }

        variantGroups.forEach(function (group) {
          if (group.category_ids.indexOf(categoryId) === -1) {
            return;
          }
          if (filterId && group.id !== filterId) {
            return;
          }
          rows.push(group);
        });

        if (!rows.length) {
          groupsWrap.innerHTML =
            '<div class="variant-empty-state">No variant groups are linked to this category. Define them on the <a href="/adminvariants">Variants</a> page.</div>';
          return;
        }

        groupsWrap.innerHTML = rows.map(function (group) {
          var chips = group.values.map(function (value) {
            var isSelected = selectedValues.has(value.id);
            return '<button type="button" class="chip' + (isSelected ? ' is-selected' : '') + '" data-value-id="' +
              value.id + '" aria-pressed="' + (isSelected ? 'true' : 'false') + '">' +
              escapeHtml(value.value) + '</button>';
          }).join('');

          return '<div class="variant-group-card">' +
            '<div class="variant-group-header">' +
            '<span class="custom-checkbox-label">' + escapeHtml(group.label || group.name) + '</span>' +
            '<span class="tag-pill">' + group.values.length + ' values</span>' +
            '</div>' +
            '<div class="chips-container">' + chips + '</div>' +
            '</div>';
        }).join('');
      }

      function discountLabel(actual, discounted) {
        if (!actual || actual <= 0 || !discounted || discounted <= 0 || discounted > actual) {
          return '—';
        }
        var percentage = Math.round((actual - discounted) / actual * 100);
        return percentage > 0 ? percentage + '% off' : '—';
      }

      function applyStatusBadge(element, stock) {
        if (stock <= 0) {
          element.className = 'badge badge-out_of_stock';
          element.innerHTML = '<span class="badge-dot"></span>Out of Stock';
        } else if (stock <= 10) {
          element.className = 'badge badge-low_stock';
          element.innerHTML = '<span class="badge-dot"></span>Low Stock';
        } else {
          element.className = 'badge badge-in_stock';
          element.innerHTML = '<span class="badge-dot"></span>In Stock';
        }
      }

      function formatMoney(amount) {
        return Number(amount).toLocaleString('en-IN', {
          minimumFractionDigits: 0,
          maximumFractionDigits: 2
        });
      }

      function renderRow(row, index, isHidden) {
        var state = rowState[row.key];
        var actual = parseFloat(state.actual) || 0;
        var discounted = parseFloat(state.discounted) || 0;
        var stock = parseInt(state.stock, 10) || 0;

        var hiddenValueInputs = row.valueIds.map(function (valueId) {
          return '<input type="hidden" name="variants[' + index + '][variant_value_ids][]" value="' +
            valueId + '">';
        }).join('');

        var statusClass = stock <= 0 ? 'badge-out_of_stock' : (stock <= 10 ? 'badge-low_stock' : 'badge-in_stock');
        var statusText = stock <= 0 ? 'Out of Stock' : (stock <= 10 ? 'Low Stock' : 'In Stock');
        var discount = discountLabel(actual, discounted);

        return '<tr data-key="' + row.key + '"' + (isHidden ? ' style="display: none;"' : '') + '>' +
          '<td><div class="product-name">' + escapeHtml(row.label) + '</div>' + hiddenValueInputs + '</td>' +
          '<td><input type="text" class="table-input" data-field="sku" name="variants[' + index +
          '][sku]" value="' + escapeHtml(state.sku) + '" maxlength="255"></td>' +
          '<td><input type="number" class="table-input price-actual-input" data-field="actual" name="variants[' +
          index + '][actual_price]" min="0" step="0.01" value="' + escapeHtml(state.actual) + '" required></td>' +
          '<td><input type="number" class="table-input price-discount-input" data-field="discounted" name="variants[' +
          index + '][discounted_price]" min="0" step="0.01" value="' + escapeHtml(state.discounted) + '"></td>' +
          '<td><span class="discount-badge' + (discount === '—' ? ' is-none' : '') + '">' + discount + '</span></td>' +
          '<td><input type="number" class="table-input stock-input" data-field="stock" name="variants[' + index +
          '][stock_qty]" min="0" value="' + escapeHtml(state.stock) + '"></td>' +
          '<td class="status-cell"><span class="badge ' + statusClass + '"><span class="badge-dot"></span>' +
          statusText + '</span></td>' +
          '</tr>';
      }

      function renderMatrix() {
        var combinations = buildCombinations();
        var filterId = pricingFilter.value ? parseInt(pricingFilter.value, 10) : null;

        lastRows = combinations.map(function (valueIds) {
          var key = combinationKey(valueIds);

          if (!rowState[key]) {
            rowState[key] = {
              sku: defaultSku(valueIds),
              actual: '',
              discounted: '',
              stock: '0'
            };
          }

          return {
            key: key,
            valueIds: valueIds,
            label: valueIds.map(function (valueId) {
              return valueIndex[valueId].value;
            }).join(' + ')
          };
        });

        var visibleCount = 0;

        if (!lastRows.length) {
          tableBody.innerHTML =
            '<tr class="empty-row"><td colspan="7">No variants generated yet. Select variant values in Section B.</td></tr>';
        } else {
          tableBody.innerHTML = lastRows.map(function (row, index) {
            var matches = !filterId || row.valueIds.some(function (valueId) {
              return valueIndex[valueId].groupId === filterId;
            });
            if (matches) {
              visibleCount++;
            }
            return renderRow(row, index, !matches);
          }).join('');
        }

        if (!lastRows.length) {
          countTag.textContent = '0 combinations generated';
        } else if (filterId) {
          countTag.textContent = visibleCount + ' of ' + lastRows.length + ' combinations';
        } else {
          countTag.textContent = lastRows.length + ' combinations generated';
        }

        updateSummary();
      }

      function updateSummary() {
        var total = lastRows.length;
        var min = null;
        var max = null;
        var stock = 0;
        var bestDiscount = 0;

        lastRows.forEach(function (row) {
          var state = rowState[row.key] || {};
          var actual = parseFloat(state.actual) || 0;
          var discounted = parseFloat(state.discounted) || 0;
          var sellingPrice = discounted > 0 ? discounted : actual;

          if (sellingPrice > 0) {
            min = min === null ? sellingPrice : Math.min(min, sellingPrice);
            max = max === null ? sellingPrice : Math.max(max, sellingPrice);
          }

          stock += parseInt(state.stock, 10) || 0;

          if (actual > 0 && discounted > 0 && discounted <= actual) {
            bestDiscount = Math.max(bestDiscount, Math.round((actual - discounted) / actual * 100));
          }
        });

        summaryVariantCount.textContent = total + (total === 1 ? ' combination' : ' combinations');
        summaryPriceRange.textContent = min === null
          ? '—'
          : '\u20B9' + formatMoney(min) + ' \u2013 \u20B9' + formatMoney(max);
        summaryDiscount.textContent = bestDiscount > 0 ? bestDiscount + '% off' : '—';
        summaryTotalStock.textContent = stock + ' units';

        var option = categorySelect.options[categorySelect.selectedIndex];
        summaryCategory.textContent = categorySelect.value && option ? option.text : '—';
      }

      function refreshRow(tr) {
        var key = tr.dataset.key;
        var state = rowState[key];

        state.actual = tr.querySelector('[data-field="actual"]').value;
        state.discounted = tr.querySelector('[data-field="discounted"]').value;
        state.stock = tr.querySelector('[data-field="stock"]').value;
        state.sku = tr.querySelector('[data-field="sku"]').value;

        var discount = discountLabel(parseFloat(state.actual) || 0, parseFloat(state.discounted) || 0);
        var badge = tr.querySelector('.discount-badge');
        badge.textContent = discount;
        badge.classList.toggle('is-none', discount === '—');

        applyStatusBadge(tr.querySelector('.status-cell .badge'), parseInt(state.stock, 10) || 0);
        updateSummary();
      }

      // Variant values: toggle selection and rebuild the pricing matrix.
      groupsWrap.addEventListener('click', function (event) {
        var chip = event.target.closest('.chip');
        if (!chip) {
          return;
        }

        var valueId = parseInt(chip.dataset.valueId, 10);

        if (selectedValues.has(valueId)) {
          selectedValues.delete(valueId);
          chip.classList.remove('is-selected');
          chip.setAttribute('aria-pressed', 'false');
        } else {
          selectedValues.add(valueId);
          chip.classList.add('is-selected');
          chip.setAttribute('aria-pressed', 'true');
        }

        renderMatrix();
      });

      categorySelect.addEventListener('change', function () {
        selectedValues.clear();
        renderGroups();
        renderMatrix();
      });

      groupFilter.addEventListener('change', function () {
        renderGroups();
      });

      pricingFilter.addEventListener('change', function () {
        renderMatrix();
      });

      tableBody.addEventListener('input', function (event) {
        var input = event.target.closest('input[data-field]');
        if (!input) {
          return;
        }
        refreshRow(input.closest('tr'));
      });

      // Product images: preview, de-duplicate and allow removal before submit.
      var fileInput = document.getElementById('fileInput');
      var previewGrid = document.getElementById('imagePreviewGrid');
      var imageCountHint = document.getElementById('imageCountHint');
      var imageCountText = document.getElementById('imageCountText');
      var imageUploadBox = document.getElementById('imageUploadBox');
      var selectedImages = [];

      function syncFileInput() {
        var transfer = new DataTransfer();
        selectedImages.forEach(function (file) {
          transfer.items.add(file);
        });
        fileInput.files = transfer.files;
      }

      function renderPreviews() {
        previewGrid.innerHTML = '';

        selectedImages.forEach(function (file, index) {
          var item = document.createElement('div');
          item.className = 'image-preview-item';

          var image = document.createElement('img');
          image.src = URL.createObjectURL(file);
          image.alt = file.name;

          var removeButton = document.createElement('button');
          removeButton.type = 'button';
          removeButton.className = 'image-preview-remove';
          removeButton.setAttribute('aria-label', 'Remove image');
          removeButton.innerHTML = '&#215;';
          removeButton.addEventListener('click', function () {
            selectedImages.splice(index, 1);
            syncFileInput();
            renderPreviews();
          });

          item.appendChild(image);
          item.appendChild(removeButton);
          previewGrid.appendChild(item);
        });

        var count = selectedImages.length;
        imageCountText.textContent = count + ' of minimum ' + minimumImages + ' images uploaded';
        imageCountHint.classList.toggle('is-ok', count >= minimumImages);
        imageCountHint.classList.toggle('is-error', count < minimumImages);
        imageUploadBox.classList.toggle('has-error', count > 0 && count < minimumImages);
      }

      fileInput.addEventListener('change', function () {
        Array.prototype.slice.call(fileInput.files).forEach(function (file) {
          var duplicate = selectedImages.some(function (selected) {
            return selected.name === file.name && selected.size === file.size;
          });
          if (!duplicate) {
            selectedImages.push(file);
          }
        });

        syncFileInput();
        renderPreviews();
      });

      // Specifications: add and remove free key/value rows.
      var specsList = document.getElementById('specsList');
      var specIndex = specsList.querySelectorAll('.spec-row').length;

      function addSpecRow() {
        var row = document.createElement('div');
        row.className = 'spec-row';
        row.innerHTML = '<input type="text" class="input-text" name="specs[' + specIndex +
          '][label]" placeholder="Specification (e.g. Display)" maxlength="255">' +
          '<input type="text" class="input-text" name="specs[' + specIndex +
          '][value]" placeholder="Value (e.g. 6.7-inch AMOLED)" maxlength="255">' +
          '<button type="button" class="chip-remove-btn" aria-label="Remove specification">&#215;</button>';
        specsList.appendChild(row);
        specIndex++;
      }

      document.getElementById('addSpecBtn').addEventListener('click', addSpecRow);

      specsList.addEventListener('click', function (event) {
        var button = event.target.closest('.chip-remove-btn');
        if (!button) {
          return;
        }

        var rows = specsList.querySelectorAll('.spec-row');
        if (rows.length <= 1) {
          rows[0].querySelectorAll('input').forEach(function (input) {
            input.value = '';
          });
          return;
        }

        button.closest('.spec-row').remove();
      });

      function showToast(message, isError) {
        var toast = document.getElementById('toast');
        document.getElementById('toastMessage').textContent = message;
        toast.classList.toggle('is-error', !!isError);
        toast.style.display = 'flex';
        setTimeout(function () {
          toast.style.display = 'none';
        }, 2600);
      }

      window.validateProductForm = function (event) {
        if (selectedImages.length < minimumImages) {
          event.preventDefault();
          imageUploadBox.classList.add('has-error');
          showToast('Upload at least ' + minimumImages + ' product images.', true);
          return false;
        }

        if (!lastRows.length) {
          event.preventDefault();
          showToast('Select variant values to generate at least one variant.', true);
          return false;
        }

        var invalid = lastRows.some(function (row) {
          var state = rowState[row.key] || {};
          var actual = parseFloat(state.actual) || 0;
          var discounted = parseFloat(state.discounted) || 0;
          return actual <= 0 || discounted > actual;
        });

        if (invalid) {
          event.preventDefault();
          showToast('Set an actual price above zero for every variant, and keep discounts below it.', true);
          return false;
        }

        return true;
      };

      // Mobile sidebar drawer (layout only)
      (function () {
        var menuBtn = document.getElementById('mobileMenuBtn');
        var sidebar = document.getElementById('adminSidebar');
        if (menuBtn && sidebar) {
          menuBtn.addEventListener('click', function () {
            sidebar.classList.toggle('open');
          });
        }
      })();

      previousBaseSku = baseSku();
      renderGroups();
      renderMatrix();
      renderPreviews();
    })();
  </script>
</body>

</html>
