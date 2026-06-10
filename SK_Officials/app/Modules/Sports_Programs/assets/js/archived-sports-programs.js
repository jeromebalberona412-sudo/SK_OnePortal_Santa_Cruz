'use strict';

let aspRecords = [];
let aspPendingRestoreId = null;
let aspPendingDeleteId = null;

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function showToast(message, type = 'success') {
    const toast = document.getElementById('aspToast');
    if (!toast) return;
    toast.textContent = message;
    toast.style.display = 'flex';
    toast.style.background = type === 'error' ? '#ef4444' : '#22c55e';
    clearTimeout(showToast._timer);
    showToast._timer = setTimeout(() => { toast.style.display = 'none'; }, 2800);
}

async function apiFetch(url, options = {}) {
    const response = await fetch(url, {
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            ...(options.headers || {}),
        },
        ...options,
    });
    const data = await response.json().catch(() => ({}));
    if (!response.ok) {
        throw new Error(data.message || 'Request failed.');
    }
    return data;
}

function renderStats(stats = {}) {
    const totalEl = document.getElementById('aspStatTotal');
    const expiringEl = document.getElementById('aspStatExpiring');
    if (totalEl) totalEl.textContent = String(stats.total ?? 0);
    if (expiringEl) expiringEl.textContent = String(stats.expiring_soon ?? 0);
}

function renderTable(records) {
    const tbody = document.getElementById('aspTableBody');
    if (!tbody) return;

    if (!records.length) {
        tbody.innerHTML = '<tr><td colspan="7" class="saf-table-empty">No archived sports programs found.</td></tr>';
        return;
    }

    tbody.innerHTML = records.map((record) => {
        const days = record.days_remaining ?? 0;
        const daysClass = days <= 7 ? 'asp-days-badge is-urgent' : 'asp-days-badge';
        const canDelete = record.can_permanently_delete;

        return `
            <tr>
                <td>
                    <div style="font-weight:600;color:#111827;">${escapeHtml(record.program_name || record.program_type)}</div>
                    <span class="asp-badge-archived">Archived</span>
                </td>
                <td>${escapeHtml(record.program_type || '—')}</td>
                <td>${escapeHtml(record.start_date || '—')}</td>
                <td>${escapeHtml(record.end_date || '—')}</td>
                <td>${escapeHtml(record.archived_date || '—')}</td>
                <td><span class="${daysClass}">${days} day${days === 1 ? '' : 's'}</span></td>
                <td class="col-actions">
                    <div class="prog-tbl-actions">
                        <button type="button" class="asp-btn-restore" data-restore="${record.id}">Restore</button>
                        <button type="button" class="asp-btn-delete" data-delete="${record.id}" ${canDelete ? '' : 'disabled title="Contains historical records"'}>Delete Permanently</button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');

    tbody.querySelectorAll('[data-restore]').forEach((button) => {
        button.addEventListener('click', () => openRestoreModal(Number(button.dataset.restore)));
    });
    tbody.querySelectorAll('[data-delete]:not(:disabled)').forEach((button) => {
        button.addEventListener('click', () => openDeleteModal(Number(button.dataset.delete)));
    });
}

async function loadArchivedPrograms(search = '') {
    const params = new URLSearchParams();
    if (search) params.set('search', search);
    const data = await apiFetch(`/sports-programs/archived/data?${params.toString()}`);
    aspRecords = data.data || [];
    renderStats(data.stats || {});
    renderTable(aspRecords);
}

function findRecord(id) {
    return aspRecords.find((record) => Number(record.id) === Number(id));
}

function openRestoreModal(id) {
    const record = findRecord(id);
    if (!record) return;
    aspPendingRestoreId = id;
    const nameEl = document.getElementById('aspRestoreName');
    if (nameEl) nameEl.textContent = record.program_name || record.program_type || 'Sports Program';
    document.getElementById('aspRestoreModal').style.display = 'flex';
}

function closeRestoreModal() {
    aspPendingRestoreId = null;
    document.getElementById('aspRestoreModal').style.display = 'none';
}

function openDeleteModal(id) {
    const record = findRecord(id);
    if (!record || !record.can_permanently_delete) return;
    aspPendingDeleteId = id;
    const nameEl = document.getElementById('aspDeleteName');
    if (nameEl) nameEl.textContent = record.program_name || record.program_type || 'Sports Program';
    document.getElementById('aspDeleteModal').style.display = 'flex';
}

function closeDeleteModal() {
    aspPendingDeleteId = null;
    document.getElementById('aspDeleteModal').style.display = 'none';
}

document.addEventListener('DOMContentLoaded', async () => {
    const searchInput = document.getElementById('aspSearch');
    let searchTimer = null;

    if (searchInput) {
        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => {
                loadArchivedPrograms(searchInput.value.trim()).catch((error) => {
                    showToast(error.message || 'Failed to load archived programs.', 'error');
                });
            }, 250);
        });
    }

    document.getElementById('aspRestoreClose')?.addEventListener('click', closeRestoreModal);
    document.getElementById('aspRestoreCancel')?.addEventListener('click', closeRestoreModal);
    document.getElementById('aspDeleteClose')?.addEventListener('click', closeDeleteModal);
    document.getElementById('aspDeleteCancel')?.addEventListener('click', closeDeleteModal);

    document.getElementById('aspRestoreConfirm')?.addEventListener('click', async () => {
        if (!aspPendingRestoreId) return;
        const button = document.getElementById('aspRestoreConfirm');
        if (button) button.disabled = true;
        try {
            await apiFetch(`/sports-programs/restore/${aspPendingRestoreId}`, { method: 'POST' });
            closeRestoreModal();
            showToast('Sports program restored successfully.');
            await loadArchivedPrograms(searchInput?.value?.trim() || '');
        } catch (error) {
            showToast(error.message || 'Failed to restore program.', 'error');
        } finally {
            if (button) button.disabled = false;
        }
    });

    document.getElementById('aspDeleteConfirm')?.addEventListener('click', async () => {
        if (!aspPendingDeleteId) return;
        const button = document.getElementById('aspDeleteConfirm');
        if (button) button.disabled = true;
        try {
            await apiFetch(`/sports-programs/delete/${aspPendingDeleteId}`, { method: 'DELETE' });
            closeDeleteModal();
            showToast('Sports program permanently deleted.');
            await loadArchivedPrograms(searchInput?.value?.trim() || '');
        } catch (error) {
            showToast(error.message || 'Failed to delete program.', 'error');
        } finally {
            if (button) button.disabled = false;
        }
    });

    try {
        if (typeof window.showLoading === 'function') window.showLoading();
        await loadArchivedPrograms();
    } catch (error) {
        showToast(error.message || 'Failed to load archived programs.', 'error');
    } finally {
        if (typeof window.hideLoading === 'function') window.hideLoading();
    }
});
