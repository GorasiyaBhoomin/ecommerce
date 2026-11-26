/**
 * Admin Tables Helper
 * Provides instant search with debounce, filter auto-submit, and clean resets.
 */
document.addEventListener('DOMContentLoaded', function () {
  // Setup debounced search on all table search inputs
  document.querySelectorAll('.table-search-box .search-input').forEach(function (input) {
    const form = input.closest('form');
    if (!form) return;

    let debounceTimer = null;

    input.addEventListener('input', function () {
      clearTimeout(debounceTimer);

      const clearBtn = input.parentElement.querySelector('.search-clear-btn');
      if (clearBtn) {
        clearBtn.style.display = input.value.trim().length > 0 ? 'flex' : 'none';
      }

      debounceTimer = setTimeout(function () {
        // Reset page to 1 on new search
        const pageInput = form.querySelector('input[name="page"]');
        if (pageInput) {
          pageInput.value = '1';
        }
        form.submit();
      }, 400);
    });

    input.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        clearTimeout(debounceTimer);
        const pageInput = form.querySelector('input[name="page"]');
        if (pageInput) {
          pageInput.value = '1';
        }
        form.submit();
      }
    });
  });

  // Setup auto-submit for filter selects & per-page dropdowns
  document.querySelectorAll('.table-filter-select, .table-per-page-select, .table-filter-date').forEach(function (element) {
    element.addEventListener('change', function () {
      const form = element.closest('form');
      if (!form) return;

      const pageInput = form.querySelector('input[name="page"]');
      if (pageInput) {
        pageInput.value = '1';
      }
      form.submit();
    });
  });

  // Setup Checkboxes and Bulk Actions
  setupTableCheckboxes();

  // Setup AJAX Status Toggles
  setupStatusToggles();
});

/**
 * Toast Notification Helper
 */
function showAdminToast(message, type = 'success') {
  let container = document.getElementById('adminToastContainer');
  if (!container) {
    container = document.createElement('div');
    container.id = 'adminToastContainer';
    container.className = 'admin-toast-container';
    document.body.appendChild(container);
  }

  const toast = document.createElement('div');
  toast.className = `admin-toast toast-${type}`;

  const iconSvg = type === 'success'
    ? `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>`
    : `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>`;

  toast.innerHTML = `
    <span class="admin-toast-icon">${iconSvg}</span>
    <span class="admin-toast-body">${message}</span>
    <button type="button" class="admin-toast-close" aria-label="Close" onclick="this.parentElement.remove()">✕</button>
  `;

  container.appendChild(toast);
  requestAnimationFrame(() => toast.classList.add('show'));

  setTimeout(() => {
    toast.classList.remove('show');
    setTimeout(() => toast.remove(), 250);
  }, 3500);
}

window.showAdminToast = showAdminToast;

/**
 * Setup Select-All and Row Checkboxes for all tables
 */
function setupTableCheckboxes() {
  document.querySelectorAll('table').forEach(table => {
    const selectAll = table.querySelector('.select-all-checkbox');
    const rowCheckboxes = table.querySelectorAll('.row-checkbox');
    const bulkBtn = document.querySelector('.btn-bulk-delete');

    if (!selectAll && rowCheckboxes.length === 0) return;

    function updateBulkButton() {
      const checkedBoxes = table.querySelectorAll('.row-checkbox:checked');
      const count = checkedBoxes.length;

      document.querySelectorAll('.btn-bulk-delete').forEach(btn => {
        const countSpan = btn.querySelector('.selected-count');
        if (countSpan) {
          countSpan.textContent = count;
        }
        btn.style.display = count > 0 ? 'inline-flex' : 'none';
      });

      if (selectAll) {
        if (count === 0) {
          selectAll.checked = false;
          selectAll.indeterminate = false;
        } else if (count === rowCheckboxes.length) {
          selectAll.checked = true;
          selectAll.indeterminate = false;
        } else {
          selectAll.checked = false;
          selectAll.indeterminate = true;
        }
      }
    }

    if (selectAll) {
      selectAll.addEventListener('change', function () {
        rowCheckboxes.forEach(cb => {
          cb.checked = selectAll.checked;
        });
        updateBulkButton();
      });
    }

    rowCheckboxes.forEach(cb => {
      cb.addEventListener('change', updateBulkButton);
    });

    // Initial check
    updateBulkButton();
  });
}

/**
 * Execute Bulk Delete via AJAX/fetch
 */
function executeBulkDelete(resourceName, deleteUrl) {
  const checkedBoxes = document.querySelectorAll('.row-checkbox:checked');
  const ids = Array.from(checkedBoxes).map(cb => cb.value);

  if (ids.length === 0) {
    showAdminToast('Please select at least one record to delete.', 'error');
    return;
  }

  const confirmMsg = `Are you sure you want to delete the ${ids.length} selected ${resourceName}? This action cannot be undone.`;
  if (!window.confirm(confirmMsg)) {
    return;
  }

  const csrfMeta = document.querySelector('meta[name="csrf-token"]');
  const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

  const bulkBtns = document.querySelectorAll('.btn-bulk-delete');
  bulkBtns.forEach(btn => {
    btn.disabled = true;
    btn.dataset.originalHtml = btn.innerHTML;
    btn.innerHTML = 'Deleting...';
  });

  fetch(deleteUrl, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrfToken,
      'Accept': 'application/json'
    },
    body: JSON.stringify({ ids: ids })
  })
  .then(async response => {
    const data = await response.json();
    if (!response.ok || data.success === false) {
      throw new Error(data.message || 'Bulk delete failed.');
    }
    return data;
  })
  .then(data => {
    showAdminToast(data.message || `${ids.length} records deleted successfully.`, 'success');
    setTimeout(() => {
      window.location.reload();
    }, 700);
  })
  .catch(err => {
    bulkBtns.forEach(btn => {
      btn.disabled = false;
      if (btn.dataset.originalHtml) {
        btn.innerHTML = btn.dataset.originalHtml;
      }
    });
    showAdminToast(err.message || 'An error occurred during deletion.', 'error');
  });
}

window.executeBulkDelete = executeBulkDelete;

/**
 * Setup AJAX Status Toggles for all status switches
 */
function setupStatusToggles() {
  document.querySelectorAll('.status-toggle-input').forEach(input => {
    input.addEventListener('change', function () {
      const toggleUrl = input.dataset.url;
      const recordId = input.dataset.id;
      const isChecked = input.checked;

      if (!toggleUrl) return;

      const csrfMeta = document.querySelector('meta[name="csrf-token"]');
      const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

      input.disabled = true;

      fetch(toggleUrl, {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json'
        },
        body: JSON.stringify({ is_active: isChecked })
      })
      .then(async response => {
        const data = await response.json();
        if (!response.ok || data.success === false) {
          throw new Error(data.message || 'Status update failed.');
        }
        return data;
      })
      .then(data => {
        input.disabled = false;
        
        // Update badge
        const badge = document.querySelector(`.status-badge-${recordId}`);
        const activeState = data.is_active !== undefined ? data.is_active : isChecked;

        if (badge) {
          if (activeState) {
            badge.classList.remove('bg-secondary', 'badge-low_stock', 'badge-out_of_stock', 'bg-danger');
            badge.classList.add('bg-success');
            badge.textContent = 'Active';
          } else {
            badge.classList.remove('bg-success', 'badge-in_stock');
            badge.classList.add('bg-secondary');
            badge.textContent = 'Inactive';
          }
        }

        showAdminToast(data.message || 'Status updated successfully.', 'success');
      })
      .catch(err => {
        // Revert switch to previous state
        input.checked = !isChecked;
        input.disabled = false;
        showAdminToast(err.message || 'Failed to update status.', 'error');
      });
    });
  });
}

window.setupStatusToggles = setupStatusToggles;

/**
 * Confirmation dialog helper for row deletion
 */
function confirmRecordDelete(name, itemType = 'record') {
  return window.confirm(`Are you sure you want to delete "${name}"? This action cannot be undone.`);
}

window.confirmRecordDelete = confirmRecordDelete;

/**
 * Clear the search field in a table toolbar and submit the form.
 */
function clearTableSearch(btn) {
  const box = btn.closest('.table-search-box');
  if (!box) return;
  const input = box.querySelector('.search-input');
  const form = box.closest('form');
  if (input && form) {
    input.value = '';
    btn.style.display = 'none';
    const pageInput = form.querySelector('input[name="page"]');
    if (pageInput) {
      pageInput.value = '1';
    }
    form.submit();
  }
}
