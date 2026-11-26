$(document).ready(function () {

    // ==========================================
    // 1. ADD / EDIT (AJAX SUBMIT)
    // ==========================================
    $(document).on('submit', '.ajax-form, #variantForm', function (e) {
        e.preventDefault();

        let form = $(this);
        let btn = form.find('[type="submit"]');

        btn.prop('disabled', true);

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (res) {
                let msg = res.message || 'Saved successfully.';

                // Show toast or alert
                if (window.AdminAjax && typeof window.AdminAjax.toast === 'function') {
                    window.AdminAjax.toast(msg, 'success');
                } else if (typeof window.showAdminToast === 'function') {
                    window.showAdminToast(msg, 'success');
                } else if (typeof window.showToast === 'function') {
                    window.showToast(msg, false);
                } else {
                    alert(msg);
                }

                // Reset form to Add mode
                if (typeof window.cancelEditMode === 'function' && (form.attr('id') === 'variantForm' || form.hasClass('variant-form'))) {
                    window.cancelEditMode();
                } else if (form.data('reset') !== false && !form.hasClass('no-reset')) {
                    form[0].reset();
                    form.find('input[name="_method"]').remove();
                }

                // Refresh table without reloading whole page
                let tableSelector = form.data('table');
                if (tableSelector && $(tableSelector).length) {
                    refreshTable(tableSelector);
                }
            },
            error: function (xhr) {
                let errorMsg = xhr.responseJSON?.message || 'Something went wrong';
                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    let errorList = Object.values(xhr.responseJSON.errors).flat();
                    if (errorList.length > 0) {
                        errorMsg = errorList.join('\n');
                    }
                }

                if (window.AdminAjax && typeof window.AdminAjax.toast === 'function') {
                    window.AdminAjax.toast(errorMsg, 'error');
                } else if (typeof window.showAdminToast === 'function') {
                    window.showAdminToast(errorMsg, 'error');
                } else if (typeof window.showToast === 'function') {
                    window.showToast(errorMsg, true);
                } else {
                    alert(errorMsg);
                }
            },
            complete: function () {
                btn.prop('disabled', false);
            }
        });
    });

    // ==========================================
    // 2. DELETE (AJAX DELETE)
    // ==========================================
    $(document).on('click', '.ajax-delete', function (e) {
        e.preventDefault();

        let btn = $(this);
        let confirmMsg = btn.data('confirm') || 'Are you sure you want to delete?';

        if (!confirm(confirmMsg)) return;

        let deleteUrl = btn.data('url') || btn.attr('href');
        if (!deleteUrl) return;

        $.ajax({
            url: deleteUrl,
            type: 'POST',
            data: {
                _method: 'DELETE',
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (res) {
                let msg = res.message || 'Deleted successfully.';
                if (window.AdminAjax && typeof window.AdminAjax.toast === 'function') {
                    window.AdminAjax.toast(msg, 'success');
                } else if (typeof window.showAdminToast === 'function') {
                    window.showAdminToast(msg, 'success');
                } else if (typeof window.showToast === 'function') {
                    window.showToast(msg, false);
                } else {
                    alert(msg);
                }

                // If currently editing this deleted record, reset form to Add mode
                let editingId = $('#editingGroupId').val();
                let deletedId = btn.data('id');
                if (editingId && deletedId && String(editingId) === String(deletedId)) {
                    if (typeof window.cancelEditMode === 'function') {
                        window.cancelEditMode();
                    }
                }

                // Refresh table without reloading whole page
                let tableSelector = btn.data('table') || '#variantTableCard';
                if (tableSelector && $(tableSelector).length) {
                    refreshTable(tableSelector);
                }
            },
            error: function (xhr) {
                let msg = xhr.responseJSON?.message || 'Delete failed';
                if (window.AdminAjax && typeof window.AdminAjax.toast === 'function') {
                    window.AdminAjax.toast(msg, 'error');
                } else if (typeof window.showAdminToast === 'function') {
                    window.showAdminToast(msg, 'error');
                } else if (typeof window.showToast === 'function') {
                    window.showToast(msg, true);
                } else {
                    alert(msg);
                }
            }
        });
    });

    // ==========================================
    // 3. REFRESH TABLE (NO WHOLE PAGE RELOAD)
    // ==========================================
    window.refreshTable = function (selector) {
        if (!selector) {
            selector = '#variantTableCard';
        }

        $.ajax({
            url: window.location.href,
            type: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function (html) {
                let freshContent = $(html).find(selector).html();
                if (freshContent) {
                    $(selector).html(freshContent);
                } else {
                    let tbody = $(html).find('#variantTableBody').html();
                    if (tbody && $('#variantTableBody').length) {
                        $('#variantTableBody').html(tbody);
                    }
                }

                let newCount = $(html).find('#variantCountPill').text();
                if (newCount) {
                    $('#variantCountPill').text(newCount);
                }

                // Rebind status toggles or checkboxes if function exists
                if (typeof window.initAdminTableCheckboxes === 'function') {
                    window.initAdminTableCheckboxes();
                }
            }
        });
    };

});
