<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Variant Group | Shopkart Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
    rel="stylesheet">
  <link href="{{ asset('assets/css/adminaddproduct.css') }}" rel="stylesheet">
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
              <a href="/adminvariants">Variants</a>
              <span>/</span>
              <span>Edit Variant Group</span>
            </div>
            <h1 class="page-heading">Edit Variant Group</h1>
          </div>
        </div>

        <div class="header-actions">
          <a href="/adminvariants" class="btn-secondary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="m12 19-7-7 7-7" />
              <path d="M19 12H5" />
            </svg>
            Back to Variants
          </a>
          @include('admin.partials.notification-bar')
        </div>
      </header>

      <div class="content-inner single-col">
        <div class="form-card" style="margin-bottom: 0;">
          <div class="card-body" style="flex-direction: row; flex-wrap: wrap; gap: 12px; align-items: center;">
            <span class="category-badge-pill">Group ID: VAR-{{ str_pad($group->id, 3, '0', STR_PAD_LEFT) }}</span>
            <span style="font-size: 13px; color: var(--muted);">Last updated
              {{ $group->updated_at->format('d M Y, h:i A') }}</span>
            <span style="font-size: 13px; color: var(--muted);">Applied to {{ $group->categories->count() }}
              {{ Str::plural('category', $group->categories->count()) }}</span>
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

        <form id="variantForm" method="POST" action="/admineditvariant/{{ $group->id }}"
          style="margin-top: 24px;">
          @csrf
          <section class="form-card">
            <div class="card-header">
              <h2 class="card-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="4" y1="21" x2="4" y2="14" />
                  <line x1="4" y1="10" x2="4" y2="3" />
                  <line x1="12" y1="21" x2="12" y2="12" />
                  <line x1="12" y1="8" x2="12" y2="3" />
                  <line x1="20" y1="21" x2="20" y2="16" />
                  <line x1="20" y1="12" x2="20" y2="3" />
                </svg>
                Variant Group Details
              </h2>
            </div>
            <div class="card-body">
              <div class="form-row-2">
                <div class="form-group">
                  <label for="variantName">Group Name</label>
                  <input type="text" id="variantName" name="name" class="input-text"
                    value="{{ old('name', $group->name) }}" maxlength="255" required>
                </div>
                <div class="form-group">
                  <label for="variantLabel">Internal Label</label>
                  <input type="text" id="variantLabel" name="label" class="input-text"
                    value="{{ old('label', $group->label) }}" maxlength="255">
                </div>
              </div>

              <div class="form-row-2">

                <div class="form-group">
                  <label for="variantCategory">Applied To Category</label>
                  @php
                    $selectedCategoryId = old('category_id', $group->categories->first()?->id);
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
                  <div style="font-size: 12px; color: var(--muted); margin-top: 4px;">Select the primary category for this
                    variant group.</div>
                </div>
              </div>
            </div>
          </section>

          <section class="form-card">
            <div class="card-header">
              <h2 class="card-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect width="18" height="18" x="3" y="3" rx="2" />
                  <path d="M3 9h18" />
                  <path d="M9 21V9" />
                </svg>
                Variant Values
              </h2>
              <button type="button" class="btn-draft" onclick="addValueRow('', '')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="12" x2="12" y1="5" y2="19" />
                  <line x1="5" x2="19" y1="12" y2="12" />
                </svg>
                Add Value
              </button>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="variants-table">
                  <thead>
                    <tr>
                      <th>Value</th>
                      <th>SKU Suffix</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody id="valueTableBody">
                    @php
                      $valueRows = old(
                          'values',
                          $group->values
                              ->map(
                                  fn ($value) => [
                                      'id' => $value->id,
                                      'value' => $value->value,
                                      'sku_suffix' => $value->sku_suffix,
                                  ],
                              )
                              ->all(),
                      );
                    @endphp
                    @foreach ($valueRows as $index => $valueRow)
                      <tr>
                        <td>
                          <input type="hidden" name="values[{{ $index }}][id]"
                            value="{{ $valueRow['id'] ?? '' }}">
                          <input type="text" class="table-input" name="values[{{ $index }}][value]"
                            value="{{ $valueRow['value'] ?? '' }}" placeholder="Value" required>
                        </td>
                        <td><input type="text" class="table-input" name="values[{{ $index }}][sku_suffix]"
                            value="{{ $valueRow['sku_suffix'] ?? '' }}" placeholder="SKU"></td>
                        <td>
                          <button type="button" class="chip-remove-btn" onclick="removeValueRow(this)"
                            aria-label="Remove value">&#215;</button>
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
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
            <button type="submit" form="deleteVariantForm" class="btn-draft"
              onclick="return confirm('Delete the &quot;{{ $group->name }}&quot; variant group? Its values will be removed too.');">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 6h18" />
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" />
                <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
              </svg>
              Delete Variant Group
            </button>
            <a href="/adminvariants" class="btn-cancel">Cancel</a>
          </div>
        </form>

        <form id="deleteVariantForm" method="POST" action="/adminvariants/{{ $group->id }}" style="display: none;">
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
    <span id="toastMessage">Variant group updated.</span>
  </div>

  <script>
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const adminSidebar = document.getElementById('adminSidebar');
    if (mobileMenuBtn && adminSidebar) {
      mobileMenuBtn.addEventListener('click', function () {
        adminSidebar.classList.toggle('open');
      });
    }

    let valueRowIndex = {{ count($valueRows) }};

    function addValueRow(value, sku) {
      const index = valueRowIndex++;
      const row = document.createElement('tr');
      row.innerHTML = `
        <td>
          <input type="hidden" name="values[${index}][id]" value="">
          <input type="text" class="table-input" name="values[${index}][value]" value="${value}" placeholder="Value" required>
        </td>
        <td><input type="text" class="table-input" name="values[${index}][sku_suffix]" value="${sku}" placeholder="SKU"></td>
        <td>
          <button type="button" class="chip-remove-btn" onclick="removeValueRow(this)" aria-label="Remove value">&#215;</button>
        </td>`;
      document.getElementById('valueTableBody').appendChild(row);
    }

    function removeValueRow(button) {
      const body = document.getElementById('valueTableBody');
      if (body.querySelectorAll('tr').length <= 1) {
        showToast('At least one variant value is required.', true);
        return;
      }
      button.closest('tr').remove();
    }

    function showToast(message, isError) {
      const toast = document.getElementById('toast');
      document.getElementById('toastMessage').textContent = message;
      toast.classList.toggle('is-error', !!isError);
      toast.style.display = 'flex';
      setTimeout(() => { toast.style.display = 'none'; }, 2600);
    }
  </script>
</body>

</html>
