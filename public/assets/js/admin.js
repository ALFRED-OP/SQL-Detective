/**
 * SQL Detective — Admin Panel
 * Handles confirmations, AJAX actions, bulk operations
 */
document.addEventListener('DOMContentLoaded', function() {
    initAdminConfirmations();
    initBulkActions();
    initInlineEditing();
    initAdminSearch();
    initStats();
});

/* ── Delete Confirmations ────────────────────────────────── */
function initAdminConfirmations() {
    document.querySelectorAll('.confirm-delete, .confirm-action').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const message = this.dataset.confirm || 'Are you sure you want to delete this item?';
            if (!confirm(message)) return;

            const url = this.href || this.dataset.url;
            const method = this.dataset.method || 'POST';

            if (url) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = url;
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = getCsrfToken();
                form.appendChild(csrfInput);
                if (method !== 'POST') {
                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = method;
                    form.appendChild(methodInput);
                }
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
}

/* ── Bulk Actions ────────────────────────────────────────── */
function initBulkActions() {
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.bulk-checkbox');
    const bulkActions = document.getElementById('bulk-actions');

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(function(cb) {
                cb.checked = selectAll.checked;
            });
            updateBulkActions();
        });
    }

    checkboxes.forEach(function(cb) {
        cb.addEventListener('change', updateBulkActions);
    });

    const bulkForm = document.getElementById('bulk-form');
    if (bulkForm) {
        bulkForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const action = document.getElementById('bulk-action-select')?.value;
            if (!action) return;

            const selectedIds = [];
            checkboxes.forEach(function(cb) {
                if (cb.checked) selectedIds.push(cb.value);
            });

            if (selectedIds.length === 0) {
                alert('No items selected.');
                return;
            }

            if (!confirm(`Are you sure you want to ${action} ${selectedIds.length} item(s)?`)) return;

            const formData = new FormData(bulkForm);
            formData.append('action', action);
            formData.append('ids', JSON.stringify(selectedIds));

            fetch(bulkForm.action, {
                method: 'POST',
                headers: { 'X-CSRF-Token': getCsrfToken() },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.error || 'Bulk action failed.');
                }
            })
            .catch(function() {
                alert('Network error. Please try again.');
            });
        });
    }
}

function updateBulkActions() {
    const checked = document.querySelectorAll('.bulk-checkbox:checked').length;
    const bulkActions = document.getElementById('bulk-actions');
    const selectedCount = document.getElementById('selected-count');

    if (bulkActions) {
        bulkActions.style.display = checked > 0 ? 'flex' : 'none';
    }
    if (selectedCount) {
        selectedCount.textContent = checked;
    }
}

/* ── Inline Editing ──────────────────────────────────────── */
function initInlineEditing() {
    document.querySelectorAll('.editable').forEach(function(el) {
        el.addEventListener('dblclick', function() {
            if (this.classList.contains('editing')) return;

            const currentValue = this.textContent.trim();
            const field = this.dataset.field;
            const id = this.dataset.id;
            const type = this.dataset.type || 'text';

            this.classList.add('editing');
            this.dataset.originalValue = currentValue;

            let input;
            if (type === 'select') {
                input = document.createElement('select');
                const options = JSON.parse(this.dataset.options || '[]');
                options.forEach(function(opt) {
                    const option = document.createElement('option');
                    option.value = opt.value;
                    option.textContent = opt.label;
                    option.selected = opt.value === currentValue;
                    input.appendChild(option);
                });
            } else if (type === 'textarea') {
                input = document.createElement('textarea');
                input.value = currentValue;
                input.rows = 3;
            } else {
                input = document.createElement('input');
                input.type = type;
                input.value = currentValue;
            }

            input.className = 'inline-edit-input';
            this.textContent = '';
            this.appendChild(input);
            input.focus();
            input.select();

            const saveBtn = document.createElement('button');
            saveBtn.className = 'btn btn-sm btn-success';
            saveBtn.textContent = '✓';
            saveBtn.style.marginLeft = '4px';

            const cancelBtn = document.createElement('button');
            cancelBtn.className = 'btn btn-sm btn-secondary';
            cancelBtn.textContent = '✗';
            cancelBtn.style.marginLeft = '4px';

            this.appendChild(saveBtn);
            this.appendChild(cancelBtn);

            const self = this;

            function finishEdit() {
                self.classList.remove('editing');
                self.innerHTML = self.dataset.originalValue;
            }

            cancelBtn.addEventListener('click', finishEdit);

            saveBtn.addEventListener('click', function() {
                const newValue = input.value;
                if (newValue === self.dataset.originalValue) {
                    finishEdit();
                    return;
                }

                saveBtn.disabled = true;
                cancelBtn.disabled = true;

                fetch('/admin/api/update', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': getCsrfToken() },
                    body: JSON.stringify({ id: id, field: field, value: newValue, type: self.dataset.entity })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        self.textContent = newValue;
                        self.classList.remove('editing');
                        self.classList.add('updated');
                        setTimeout(function() { self.classList.remove('updated'); }, 2000);
                    } else {
                        alert(data.error || 'Update failed.');
                        finishEdit();
                    }
                })
                .catch(function() {
                    alert('Network error.');
                    finishEdit();
                });
            });

            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    saveBtn.click();
                }
                if (e.key === 'Escape') {
                    finishEdit();
                }
            });
        });
    });
}

/* ── Search ──────────────────────────────────────────────── */
function initAdminSearch() {
    const searchInput = document.getElementById('admin-search');
    if (!searchInput) return;

    let timeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(timeout);
        const query = this.value.trim();
        timeout = setTimeout(function() {
            const url = new URL(window.location);
            if (query) {
                url.searchParams.set('search', query);
            } else {
                url.searchParams.delete('search');
            }
            url.searchParams.delete('page');
            window.location.href = url.toString();
        }, 500);
    });
}

/* ── Stats Animation ─────────────────────────────────────── */
function initStats() {
    document.querySelectorAll('.stat-counter').forEach(function(el) {
        const target = parseInt(el.dataset.target);
        if (isNaN(target)) return;
        let current = 0;
        const step = Math.ceil(target / 40);
        const interval = setInterval(function() {
            current += step;
            if (current >= target) {
                current = target;
                clearInterval(interval);
            }
            el.textContent = current.toLocaleString();
        }, 30);
    });
}

/* ── Helpers ─────────────────────────────────────────────── */
function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.content : '';
}