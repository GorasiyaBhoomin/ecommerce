<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Manage Variants | Shopkart Admin</title>
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
              <a href="/adminvariants">Variants</a>
              <span>/</span>
              <span>Manage Variants</span>
            </div>
            <h1 class="page-heading">Manage Variants</h1>
            <div style="font-size: 13px; color: var(--muted);">Create, edit, and organize product variant options and values</div>
          </div>
        </div>

        <div class="header-actions">
          <button type="button" class="btn-primary" onclick="cancelEditMode()" title="Create new variant group">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="12" x2="12" y1="5" y2="19" />
              <line x1="5" x2="19" y1="12" y2="12" />
            </svg>
            Add Variant
          </button>
          @include('admin.partials.notification-bar')
        </div>
      </header>

      <div class="content-inner" style="display: block;">
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

        <!-- 2-Column Unified Manage Layout (Matches eSchool SaaS Manage Layout) -->
        <div class="variant-mgmt-container">
          
          <!-- LEFT COLUMN: Create / Edit Variant Group Form -->
          <div class="variant-card" id="variantFormCard">
            <div class="variant-card-header">
              <h2 class="variant-card-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect width="18" height="18" x="3" y="3" rx="2" />
                  <path d="M3 9h18" />
                  <path d="M9 21V9" />
                </svg>
                <span id="formCardTitle">{{ $editGroup ? 'Edit Variant Group' : 'Create Variant Group' }}</span>
              </h2>
              @if ($editGroup)
                <span class="category-badge-pill" id="formModeBadge">Editing #{{ $editGroup->id }}</span>
              @endif
            </div>

            <div class="variant-card-body">
              <form id="variantForm" method="POST" class="ajax-form" data-table="#variantTableCard" data-add-url="{{ url('/adminaddvariant') }}"
                action="{{ $editGroup ? url('/admineditvariant/' . $editGroup->id) : url('/adminaddvariant') }}">
                @csrf
                <input type="hidden" id="editingGroupId" value="{{ $editGroup?->id }}">
                <div class="ajax-form-errors" id="ajaxFormErrors" style="display: none;"></div>

                <!-- Group Name -->
                <div class="variant-form-group">
                  <label for="variantName">Name <span style="color: #EF4444;">*</span></label>
                  <input type="text" id="variantName" name="name" class="input-text"
                    placeholder="e.g. Size, Color, Storage"
                    value="{{ old('name', $editGroup?->name) }}" maxlength="255" required>
                </div>

                <!-- Internal Label -->
                <div class="variant-form-group">
                  <label for="variantLabel">Internal Label</label>
                  <input type="text" id="variantLabel" name="label" class="input-text"
                    placeholder="e.g. Memory size, Color shade"
                    value="{{ old('label', $editGroup?->label) }}" maxlength="255">
                </div>

                <!-- Category -->
                <div class="variant-form-group">
                  <label for="variantCategory">Applied To Category</label>
                  @php
                    $selectedCategoryId = old('category_id', $editGroup?->categories->first()?->id);
                  @endphp
                  <select id="variantCategory" name="category_id" class="select-input">
                    <option value="">— Unassigned —</option>
                    @foreach ($categories as $category)
                      <option value="{{ $category->id }}"
                        {{ (string) $selectedCategoryId === (string) $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                      </option>
                    @endforeach
                  </select>
                </div>

                <!-- Values Builder -->
                <div class="values-builder-card">
                  <div class="values-builder-header">
                    <span style="font-size: 13px; font-weight: 700; color: var(--tbl-ink);">Variant Values <span style="color: #EF4444;">*</span></span>
                    <button type="button" class="btn-add-val" onclick="addValueRow('', '')">
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="12" x2="12" y1="5" y2="19" />
                        <line x1="5" x2="19" y1="12" y2="12" />
                      </svg>
                      Add Value
                    </button>
                  </div>

                  <table class="values-builder-table">
                    <thead>
                      <tr>
                        <th>Value *</th>
                        <th>SKU Suffix</th>
                        <th style="width: 28px;"></th>
                      </tr>
                    </thead>
                    <tbody id="valueTableBody">
                      @php
                        if ($editGroup) {
                            $initValues = old('values', $editGroup->values->map(fn($v) => ['id' => $v->id, 'value' => $v->value, 'sku_suffix' => $v->sku_suffix])->all());
                        } else {
                            $initValues = old('values', [['value' => '', 'sku_suffix' => '']]);
                        }
                        if (empty($initValues)) {
                            $initValues = [['value' => '', 'sku_suffix' => '']];
                        }
                      @endphp

                      @foreach ($initValues as $index => $row)
                        <tr>
                          @if (isset($row['id']))
                            <input type="hidden" name="values[{{ $index }}][id]" value="{{ $row['id'] }}">
                          @endif
                          <td>
                            <input type="text" name="values[{{ $index }}][value]"
                              value="{{ $row['value'] ?? '' }}" placeholder="e.g. Red, XL" required>
                          </td>
                          <td>
                            <input type="text" name="values[{{ $index }}][sku_suffix]"
                              value="{{ $row['sku_suffix'] ?? '' }}" placeholder="e.g. RED, XL">
                          </td>
                          <td style="text-align: center;">
                            <button type="button" class="btn-remove-val" onclick="removeValueRow(this)" aria-label="Remove value">&times;</button>
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>

                <!-- Active Toggle -->
                <div class="variant-form-group" style="display: flex; align-items: center; gap: 10px; margin-top: 14px; margin-bottom: 20px;">
                  <label class="form-check form-switch" style="margin: 0; cursor: pointer;">
                    <input type="checkbox" name="is_active" id="variantIsActive" value="1"
                      class="form-check-input"
                      {{ old('is_active', $editGroup ? $editGroup->is_active : true) ? 'checked' : '' }}>
                  </label>
                  <label for="variantIsActive" style="margin: 0; font-size: 13px; font-weight: 600; cursor: pointer;">
                    Active in storefront
                  </label>
                </div>

                <!-- Submit & Cancel Buttons -->
                <div style="display: flex; align-items: center; gap: 10px;">
                  <button type="submit" id="submitBtn" class="btn-primary" style="padding: 10px 22px; font-size: 14px; font-weight: 700;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                      <path d="M5 12l5 5L20 7"/>
                    </svg>
                    <span>{{ $editGroup ? 'Update Variant Group' : 'Submit' }}</span>
                  </button>

                  <button type="button" id="cancelEditBtn" class="btn-secondary"
                    style="{{ $editGroup ? 'display: inline-flex;' : 'display: none;' }} padding: 10px 18px; font-size: 13px;"
                    onclick="cancelEditMode()">
                    Cancel
                  </button>
                </div>
              </form>
            </div>
          </div>

          <!-- RIGHT COLUMN: List Variant Groups Table -->
          <div class="variant-card" id="variantTableCard">
            <div class="variant-card-header" style="flex-wrap: wrap; gap: 12px;">
              <h2 class="variant-card-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="8" y1="6" x2="21" y2="6"/>
                  <line x1="8" y1="12" x2="21" y2="12"/>
                  <line x1="8" y1="18" x2="21" y2="18"/>
                  <line x1="3" y1="6" x2="3.01" y2="6"/>
                  <line x1="3" y1="12" x2="3.01" y2="12"/>
                  <line x1="3" y1="18" x2="3.01" y2="18"/>
                </svg>
                List Variant Groups
              </h2>

              <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <!-- Bulk Delete button (appears when checked) -->
                <button type="button" class="btn-bulk-delete" style="display: none;" onclick="executeBulkDelete('variant groups', '{{ route('admin.variants.bulk-delete') }}')">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                  </svg>
                  Delete Selected (<span class="selected-count">0</span>)
                </button>

                <!-- Search Input Form -->
                <form method="GET" action="{{ url('/adminvariants') }}" id="searchVariantForm" style="margin: 0; display: flex; align-items: center; gap: 6px;">
                  @if (request('category_id'))
                    <input type="hidden" name="category_id" value="{{ request('category_id') }}">
                  @endif
                  @if (request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                  @endif

                  <div class="table-search-box" style="min-width: 180px; max-width: 240px;">
                    <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <circle cx="11" cy="11" r="8" />
                      <path d="m21 21-4.3-4.3" />
                    </svg>
                    <input type="text" name="search" id="variantTableSearch" class="search-input"
                      placeholder="Search..." value="{{ request('search') ?? request('q') }}"
                      autocomplete="off" aria-label="Search variants">
                    <button type="button" class="search-clear-btn"
                      style="{{ (request('search') || request('q')) ? 'display:flex;' : 'display:none;' }}"
                      onclick="clearTableSearch(this)" title="Clear search">&times;</button>
                  </div>

                  <!-- Refresh Button (clears search and refreshes) -->
                  <a href="{{ url('/adminvariants') }}" class="table-tool-icon-btn" title="Refresh list">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <polyline points="1 4 1 10 7 10" />
                      <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10" />
                    </svg>
                  </a>
                </form>
              </div>
            </div>

            <div class="table-responsive">
              <table class="products-table">
                <thead>
                  <tr>
                    <th style="width: 40px; text-align: center;">
                      <input type="checkbox" class="form-check-input select-all-checkbox" aria-label="Select all variant groups">
                    </th>
                    <th style="width: 50px;">NO.</th>
                    @include('admin.partials.sort-th', ['column' => 'name', 'label' => 'NAME'])
                    <th>VALUES</th>
                    @include('admin.partials.sort-th', ['column' => 'is_active', 'label' => 'STATUS'])
                    <th style="text-align: right; width: 100px;">ACTION</th>
                  </tr>
                </thead>
                <tbody id="variantTableBody">
                  @forelse ($variantGroups as $variantGroup)
                    @php
                      $appliedCategories = $variantGroup->categories->pluck('name');
                      $valuesCollection = $variantGroup->values;
                      $rowNumber = $loop->iteration + ($variantGroups->currentPage() - 1) * $variantGroups->perPage();
                    @endphp
                    <tr>
                      <td style="text-align: center;">
                        <input type="checkbox" class="form-check-input row-checkbox" value="{{ $variantGroup->id }}" aria-label="Select variant group">
                      </td>
                      <td style="font-weight: 700; color: var(--tbl-muted); font-size: 13px;">
                        {{ $rowNumber }}
                      </td>
                      <td>
                        <div style="font-weight: 700; color: var(--tbl-ink); font-size: 14px;">
                          {{ $variantGroup->name }}
                        </div>
                        @if ($variantGroup->label)
                          <div style="font-size: 12px; color: var(--tbl-muted); margin-top: 1px;">
                            {{ $variantGroup->label }}
                          </div>
                        @endif
                        @if ($appliedCategories->isNotEmpty())
                          <div class="variant-cat-tag">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                            {{ $appliedCategories->implode(', ') }}
                          </div>
                        @endif
                      </td>
                      <td>
                        <div style="display: flex; flex-wrap: wrap; gap: 4px; max-width: 260px;">
                          @forelse ($valuesCollection->take(5) as $val)
                            <span class="variant-pill-tag">{{ $val->value }}</span>
                          @empty
                            <span style="font-size: 12px; color: var(--tbl-muted);">—</span>
                          @endforelse
                          @if ($valuesCollection->count() > 5)
                            <span class="variant-pill-tag more">+{{ $valuesCollection->count() - 5 }} more</span>
                          @endif
                        </div>
                      </td>
                      <td>
                        <div class="status-toggle-wrap">
                          <label class="form-check form-switch" title="Toggle status" style="margin: 0; cursor: pointer;">
                            <input type="checkbox" class="form-check-input status-toggle-input"
                              data-url="{{ route('admin.variants.toggle', $variantGroup->id) }}"
                              data-id="{{ $variantGroup->id }}"
                              {{ $variantGroup->is_active ? 'checked' : '' }}>
                          </label>
                          <span class="badge {{ $variantGroup->is_active ? 'bg-success' : 'bg-secondary' }} status-badge-{{ $variantGroup->id }}">
                            {{ $variantGroup->is_active ? 'Active' : 'Inactive' }}
                          </span>
                        </div>
                      </td>
                      <td style="text-align: right;">
                        <div style="display: inline-flex; align-items: center; justify-content: flex-end; gap: 6px;">
                          <!-- Square Edit Button matching screenshot -->
                          <button type="button" class="square-action-btn btn-action-edit"
                            title="Edit Variant Group"
                            onclick="editVariantGroup(@js($variantGroup->load(['values', 'categories'])))">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                              <path d="M12 20h9" />
                              <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z" />
                            </svg>
                          </button>

                          <!-- Square Delete Button matching screenshot -->
                          <button type="button" class="square-action-btn btn-action-delete ajax-delete"
                            title="Delete Variant Group"
                            data-url="/adminvariants/{{ $variantGroup->id }}"
                            data-id="{{ $variantGroup->id }}"
                            data-confirm="Delete the variant group &quot;{{ addslashes($variantGroup->name) }}&quot;? All its values will also be deleted."
                            data-table="#variantTableCard">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                              <polyline points="3 6 5 6 21 6"/>
                              <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                            </svg>
                          </button>
                        </div>
                      </td>
                    </tr>
                  @empty
                    @include('admin.partials.empty-state', [
                      'colspan' => 6,
                      'title' => 'No variant groups found',
                      'message' => 'No variant groups match your search or filter criteria.',
                      'resetUrl' => url('/adminvariants'),
                      'hasFilters' => (bool) (request('search') || request('q') || request('category_id') || request('status') || request('sort_by')),
                    ])
                  @endforelse
                </tbody>
              </table>
            </div>

            @include('admin.partials.table-pagination', [
              'paginator' => $variantGroups,
              'recordName' => 'variant groups'
            ])
          </div>
        </div>
      </div>
    </main>
  </div>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="{{ asset('assets/js/admin-tables.js') }}"></script>
  <script src="{{ asset('assets/js/ajax.js') }}"></script>
  <script>
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const adminSidebar = document.getElementById('adminSidebar');
    if (mobileMenuBtn && adminSidebar) {
      mobileMenuBtn.addEventListener('click', function () {
        adminSidebar.classList.toggle('open');
      });
    }

    let valueRowCounter = 1000;

    function addValueRow(value = '', sku = '', id = null) {
      const index = valueRowCounter++;
      const tbody = document.getElementById('valueTableBody');
      const tr = document.createElement('tr');

      let idInput = '';
      if (id) {
        idInput = `<input type="hidden" name="values[${index}][id]" value="${id}">`;
      }

      tr.innerHTML = `
        ${idInput}
        <td>
          <input type="text" name="values[${index}][value]" value="${escapeHtml(value)}" placeholder="e.g. Red, XL" required>
        </td>
        <td>
          <input type="text" name="values[${index}][sku_suffix]" value="${escapeHtml(sku)}" placeholder="e.g. RED, XL">
        </td>
        <td style="text-align: center;">
          <button type="button" class="btn-remove-val" onclick="removeValueRow(this)" aria-label="Remove value">&times;</button>
        </td>
      `;

      tbody.appendChild(tr);
    }

    function removeValueRow(button) {
      const tbody = document.getElementById('valueTableBody');
      if (tbody.querySelectorAll('tr').length <= 1) {
        alert('At least one variant value is required.');
        return;
      }
      button.closest('tr').remove();
    }

    function editVariantGroup(group) {
      if (!group) return;

      // Update Card Title & Mode Badge
      document.getElementById('formCardTitle').textContent = 'Edit Variant Group';
      let badge = document.getElementById('formModeBadge');
      if (!badge) {
        badge = document.createElement('span');
        badge.id = 'formModeBadge';
        badge.className = 'category-badge-pill';
        document.querySelector('.variant-card-header').appendChild(badge);
      }
      badge.textContent = `Editing #${group.id}`;
      badge.style.display = 'inline-block';

      // Update Form Action
      const form = document.getElementById('variantForm');
      form.action = `/admineditvariant/${group.id}`;
      document.getElementById('editingGroupId').value = group.id;

      // Fill Inputs
      document.getElementById('variantName').value = group.name || '';
      document.getElementById('variantLabel').value = group.label || '';

      const catSelect = document.getElementById('variantCategory');
      if (catSelect) {
        const catId = (group.categories && group.categories.length > 0) ? group.categories[0].id : '';
        catSelect.value = catId;
      }

      const activeCheckbox = document.getElementById('variantIsActive');
      if (activeCheckbox) {
        activeCheckbox.checked = !!group.is_active;
      }

      // Populate Values Builder
      const tbody = document.getElementById('valueTableBody');
      tbody.innerHTML = '';

      if (group.values && group.values.length > 0) {
        group.values.forEach(val => {
          addValueRow(val.value || '', val.sku_suffix || '', val.id || null);
        });
      } else {
        addValueRow('', '');
      }

      // Update Submit Button & Show Cancel Button
      document.getElementById('submitBtn').innerHTML = `
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
          <polyline points="17 21 17 13 7 13 7 21"></polyline>
          <polyline points="7 3 7 8 15 8"></polyline>
        </svg>
        <span>Update Variant Group</span>
      `;
      document.getElementById('cancelEditBtn').style.display = 'inline-flex';

      // Highlight left card and scroll
      const card = document.getElementById('variantFormCard');
      card.classList.add('is-editing-highlight');
      setTimeout(() => card.classList.remove('is-editing-highlight'), 1000);
      card.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function cancelEditMode() {
      // Reset Card Title & Mode Badge
      document.getElementById('formCardTitle').textContent = 'Create Variant Group';
      const badge = document.getElementById('formModeBadge');
      if (badge) badge.style.display = 'none';

      // Reset Form Action & Inputs
      const form = document.getElementById('variantForm');
      form.action = '/adminaddvariant';
      document.getElementById('editingGroupId').value = '';
      document.getElementById('variantName').value = '';
      document.getElementById('variantLabel').value = '';

      const catSelect = document.getElementById('variantCategory');
      if (catSelect) catSelect.value = '';

      const activeCheckbox = document.getElementById('variantIsActive');
      if (activeCheckbox) activeCheckbox.checked = true;

      // Reset Values Builder
      const tbody = document.getElementById('valueTableBody');
      tbody.innerHTML = '';
      addValueRow('', '');

      // Reset Buttons
      document.getElementById('submitBtn').innerHTML = `
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <path d="M5 12l5 5L20 7"/>
        </svg>
        <span>Submit</span>
      `;
      document.getElementById('cancelEditBtn').style.display = 'none';

      const card = document.getElementById('variantFormCard');
      card.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function deleteVariantGroup(id, name) {
      if (!confirm(`Are you sure you want to delete the variant group "${name}"? All its values will also be deleted.`)) {
        return;
      }

      $.ajax({
        url: `/adminvariants/${id}`,
        type: 'POST',
        data: {
          _method: 'DELETE',
          _token: $('meta[name="csrf-token"]').attr('content')
        },
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        },
        success: function (res) {
          alert(res.message || 'Deleted successfully.');
          let editingId = $('#editingGroupId').val();
          if (editingId && String(editingId) === String(id)) {
            cancelEditMode();
          }
          if (typeof refreshTable === 'function') {
            refreshTable('#variantTableCard');
          }
        },
        error: function () {
          alert('Delete failed');
        }
      });
    }

    function escapeHtml(str) {
      if (str === null || str === undefined) return '';
      return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    }
  </script>
</body>

</html>
