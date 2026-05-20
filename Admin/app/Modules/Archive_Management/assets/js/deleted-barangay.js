/**
 * Deleted Barangay Module — JavaScript
 * Handles client-side filtering, pagination, view/restore/delete modals, and AJAX updates.
 */

(function () {
    'use strict';

    /* ── Sample Data ───────────────────────────────────────────── */
    const SAMPLE_DATA = [
        {
            id: 1,
            name: 'Barangay San Isidro',
            municipality: 'Santa Cruz',
            province: 'Laguna',
            region: 'Region IV-A (CALABARZON)',
            deletedAt: new Date(Date.now() - 3 * 24 * 60 * 60 * 1000), // 3 days ago
            puroks: [
                { id: 1, name: 'Purok 1 - Maligaya' },
                { id: 2, name: 'Purok 2 - Masaya' },
                { id: 3, name: 'Purok 3 - Maunlad' }
            ],
            sitios: [
                { id: 1, name: 'Sitio Riverside' },
                { id: 2, name: 'Sitio Hillside' }
            ]
        }
    ];

    /* ── State ─────────────────────────────────────────────────── */
    let allRows      = [];
    let filteredRows = [];
    let currentPage  = 1;
    const PAGE_SIZE  = 15;

    /* ── DOM refs ──────────────────────────────────────────────── */
    const tableBody       = document.getElementById('dbTableBody');
    const searchInput     = document.getElementById('dbSearch');
    const paginationInfo  = document.getElementById('dbPaginationInfo');
    const prevBtn         = document.getElementById('dbPrevBtn');
    const nextBtn         = document.getElementById('dbNextBtn');
    const pageNumbers     = document.getElementById('dbPageNumbers');
    const statsRow        = document.getElementById('dbStatsRow');
    const filterTabs      = document.getElementById('dbFilterTabs');
    const sectionLabel    = document.getElementById('dbSectionLabel');

    /* ── Bootstrap ─────────────────────────────────────────────── */
    function init() {
        injectSampleData();
        collectRows();
        buildStats();
        applyFilters();
        bindEvents();
        bindModalEvents();
    }

    /* ── Inject sample data into table ─────────────────────────── */
    function injectSampleData() {
        if (!tableBody) return;

        const rows = SAMPLE_DATA.map(b => {
            const deletedDate = formatDate(b.deletedAt);
            const deletedTime = formatTime(b.deletedAt);
            
            return `
                <tr
                    data-id="${b.id}"
                    data-name="${b.name}"
                    data-municipality="${b.municipality}"
                    data-province="${b.province}"
                    data-region="${b.region}"
                    data-deleted-at="${b.deletedAt.toISOString()}"
                    data-deleted-date="${deletedDate}"
                    data-deleted-time="${deletedTime}"
                    data-puroks='${JSON.stringify(b.puroks)}'
                    data-sitios='${JSON.stringify(b.sitios)}'
                >
                    <td class="db-name-cell">${b.name}</td>
                    <td>${b.municipality}</td>
                    <td>${b.province}</td>
                    <td>${b.region}</td>
                    <td class="db-count-cell">${b.puroks.length}</td>
                    <td class="db-count-cell">${b.sitios.length}</td>
                    <td class="db-date-cell">${deletedDate}</td>
                    <td class="db-date-cell">${deletedTime}</td>
                    <td>
                                <div class="db-action-btns">
                                    <button type="button" class="db-btn-view" data-id="${b.id}" aria-label="View ${b.name}">View</button>
                                    <button type="button" class="db-btn-restore" data-id="${b.id}" aria-label="Restore ${b.name}">Restore</button>
                                </div>
                    </td>
                </tr>
            `;
        }).join('');

        tableBody.innerHTML = rows;
    }

    /* ── Date/Time formatters ──────────────────────────────────── */
    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    function formatTime(date) {
        let hours = date.getHours();
        const minutes = String(date.getMinutes()).padStart(2, '0');
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12 || 12;
        return `${String(hours).padStart(2, '0')}:${minutes} ${ampm}`;
    }

    /* ── Collect rows from rendered HTML ───────────────────────── */
    function collectRows() {
        allRows = Array.from(tableBody.querySelectorAll('tr[data-id]'));
    }

    /* ── Stats ─────────────────────────────────────────────────── */
    function buildStats() {
        if (!statsRow) return;

        const now = new Date();
        const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
        const weekAgo = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
        const monthAgo = new Date(today.getTime() - 30 * 24 * 60 * 60 * 1000);

        const totalDeleted = allRows.length;
        const deletedToday = allRows.filter(r => {
            const deletedAt = new Date(r.dataset.deletedAt);
            return deletedAt >= today;
        }).length;
        const deletedThisWeek = allRows.filter(r => {
            const deletedAt = new Date(r.dataset.deletedAt);
            return deletedAt >= weekAgo;
        }).length;
        const deletedThisMonth = allRows.filter(r => {
            const deletedAt = new Date(r.dataset.deletedAt);
            return deletedAt >= monthAgo;
        }).length;

        const cards = [
            { label: 'All Deleted', value: totalDeleted, colorClass: 'db-stat-card-red', iconClass: 'db-stat-icon-red', icon: trashIcon() },
            { label: 'Deleted Today', value: deletedToday, colorClass: 'db-stat-card-red', iconClass: 'db-stat-icon-red', icon: trashIcon() },
            { label: 'This Week', value: deletedThisWeek, colorClass: 'db-stat-card-red', iconClass: 'db-stat-icon-red', icon: trashIcon() },
            { label: 'This Month', value: deletedThisMonth, colorClass: 'db-stat-card-red', iconClass: 'db-stat-icon-red', icon: trashIcon() },
        ];

        statsRow.innerHTML = cards.map(c => `
            <div class="db-stat-card ${c.colorClass}">
                <div class="db-stat-top">
                    <span class="db-stat-value">${c.value}</span>
                    <span class="db-stat-icon ${c.iconClass}">${c.icon}</span>
                </div>
                <div class="db-stat-label">${c.label}</div>
            </div>
        `).join('');
    }

    /* ── Filter logic ──────────────────────────────────────────── */
    function applyFilters() {
        const search  = (searchInput?.value || '').toLowerCase().trim();
        const activeTab = document.querySelector('.db-tab.active');
        const filter = activeTab ? activeTab.dataset.filter : 'all';

        filteredRows = allRows.filter(row => {
            const d = row.dataset;

            // Search filter
            if (search) {
                const haystack = [d.name].join(' ').toLowerCase();
                if (!haystack.includes(search)) return false;
            }

            // Time filter
            if (filter !== 'all') {
                const deletedAt = new Date(d.deletedAt);
                const now = new Date();
                const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                const weekAgo = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
                const monthAgo = new Date(today.getTime() - 30 * 24 * 60 * 60 * 1000);

                if (filter === 'today' && deletedAt < today) return false;
                if (filter === 'week' && deletedAt < weekAgo) return false;
                if (filter === 'month' && deletedAt < monthAgo) return false;
            }

            return true;
        });

        // Update section label
        if (sectionLabel) {
            const labels = {
                'all': 'All Deleted Records',
                'today': 'Deleted Today',
                'week': 'Deleted This Week',
                'month': 'Deleted This Month'
            };
            sectionLabel.textContent = labels[filter] || 'All Deleted Records';
        }

        currentPage = 1;
        renderPage();
    }

    /* ── Render current page ───────────────────────────────────── */
    function renderPage() {
        const total      = filteredRows.length;
        const totalPages = Math.max(1, Math.ceil(total / PAGE_SIZE));
        currentPage      = Math.min(currentPage, totalPages);

        const start = (currentPage - 1) * PAGE_SIZE;
        const end   = Math.min(start + PAGE_SIZE, total);

        // Hide all rows, show only current page slice
        allRows.forEach(r => { r.style.display = 'none'; });
        filteredRows.forEach((r, i) => {
            r.style.display = (i >= start && i < end) ? '' : 'none';
        });

        // Pagination info
        if (paginationInfo) {
            paginationInfo.textContent = total === 0
                ? 'No records found'
                : `Showing ${start + 1}–${end} of ${total}`;
        }

        // Pagination buttons
        if (prevBtn) prevBtn.disabled = currentPage === 1;
        if (nextBtn) nextBtn.disabled = currentPage === totalPages;

        // Page numbers
        if (pageNumbers) {
            const pages = [];
            for (let i = 1; i <= totalPages; i++) {
                if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                    pages.push(i);
                } else if (pages[pages.length - 1] !== '...') {
                    pages.push('...');
                }
            }
            pageNumbers.innerHTML = pages.map(p => {
                if (p === '...') return '<span class="db-page-ellipsis">…</span>';
                return `<button type="button" class="db-page-num ${p === currentPage ? 'active' : ''}" data-page="${p}">${p}</button>`;
            }).join('');
        }
    }

    /* ── Event bindings ────────────────────────────────────────── */
    function bindEvents() {
        searchInput?.addEventListener('input', applyFilters);

        // Filter tabs
        filterTabs?.addEventListener('click', e => {
            if (e.target.classList.contains('db-tab')) {
                document.querySelectorAll('.db-tab').forEach(t => t.classList.remove('active'));
                e.target.classList.add('active');
                applyFilters();
            }
        });

        prevBtn?.addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                renderPage();
            }
        });

        nextBtn?.addEventListener('click', () => {
            const totalPages = Math.ceil(filteredRows.length / PAGE_SIZE);
            if (currentPage < totalPages) {
                currentPage++;
                renderPage();
            }
        });

        pageNumbers?.addEventListener('click', e => {
            if (e.target.classList.contains('db-page-num')) {
                currentPage = parseInt(e.target.dataset.page, 10);
                renderPage();
            }
        });

        // Table action buttons (delegated)
        tableBody?.addEventListener('click', e => {
            const viewBtn    = e.target.closest('.db-btn-view');
            const restoreBtn = e.target.closest('.db-btn-restore');

            if (viewBtn) {
                const id = viewBtn.dataset.id;
                openViewModal(id);
            } else if (restoreBtn) {
                const id = restoreBtn.dataset.id;
                openRestoreModal(id);
            }
        });
    }

    /* ── View Modal ────────────────────────────────────────────── */
    function openViewModal(id) {
        const row = allRows.find(r => r.dataset.id === String(id));
        if (!row) {
            showToast('Barangay not found.');
            return;
        }

        const barangayData = {
            id: id,
            name: row.dataset.name,
            municipality: row.dataset.municipality,
            province: row.dataset.province,
            region: row.dataset.region,
            deletedDate: row.dataset.deletedDate,
            deletedTime: row.dataset.deletedTime,
            puroks: JSON.parse(row.dataset.puroks || '[]'),
            sitios: JSON.parse(row.dataset.sitios || '[]'),
        };

        renderViewModal(barangayData);
    }

    function renderViewModal(b) {
        const modal = document.getElementById('dbViewModal');
        const body  = document.getElementById('dbViewBody');
        if (!modal || !body) return;

        body.innerHTML = `
            <div class="db-view-section">
                <div class="db-view-section-header">
                    ${locationIcon()}
                    <span>Barangay Information</span>
                </div>
                <div class="db-view-grid">
                    <div class="db-view-item">
                        <span class="db-view-label">Barangay Name</span>
                        <span class="db-view-value">${b.name || '—'}</span>
                    </div>
                    <div class="db-view-item">
                        <span class="db-view-label">Municipality</span>
                        <span class="db-view-value">${b.municipality || '—'}</span>
                    </div>
                    <div class="db-view-item">
                        <span class="db-view-label">Province</span>
                        <span class="db-view-value">${b.province || '—'}</span>
                    </div>
                    <div class="db-view-item">
                        <span class="db-view-label">Region</span>
                        <span class="db-view-value">${b.region || '—'}</span>
                    </div>
                    <div class="db-view-item">
                        <span class="db-view-label">Deleted Date</span>
                        <span class="db-view-value">${b.deletedDate || '—'}</span>
                    </div>
                    <div class="db-view-item">
                        <span class="db-view-label">Deleted Time</span>
                        <span class="db-view-value">${b.deletedTime || '—'}</span>
                    </div>
                </div>
            </div>

            <div class="db-view-section">
                <div class="db-view-section-header">
                    ${gridIcon()}
                    <span>Purok List (${b.puroks?.length || 0})</span>
                </div>
                <div class="db-view-list">
                    ${b.puroks && b.puroks.length > 0
                        ? b.puroks.map(p => `
                            <div class="db-view-list-item">
                                ${checkIcon()}
                                <span>${p.name}</span>
                            </div>
                        `).join('')
                        : '<p style="color:#6b7280;font-size:14px;">No Purok added.</p>'
                    }
                </div>
            </div>

            <div class="db-view-section">
                <div class="db-view-section-header">
                    ${homeIcon()}
                    <span>Sitio List (${b.sitios?.length || 0})</span>
                </div>
                <div class="db-view-list">
                    ${b.sitios && b.sitios.length > 0
                        ? b.sitios.map(s => `
                            <div class="db-view-list-item">
                                ${checkIcon()}
                                <span>${s.name}</span>
                            </div>
                        `).join('')
                        : '<p style="color:#6b7280;font-size:14px;">No Sitio added.</p>'
                    }
                </div>
            </div>
        `;

        modal.style.display = 'flex';
    }

    /* ── Restore Barangay ──────────────────────────────────────── */
    let restoreTargetId = null;

    function openRestoreModal(id) {
        restoreTargetId = id;
        const row = allRows.find(r => r.dataset.id === String(id));
        const barangayName = row ? row.dataset.name : 'this barangay';
        
        const modal = document.getElementById('dbRestoreModal');
        const message = document.getElementById('dbRestoreModalMessage');
        
        if (message) {
            message.textContent = `Are you sure you want to restore "${barangayName}"? It will be moved back to the active list.`;
        }
        
        if (modal) {
            modal.style.display = 'flex';
        }
    }

    function restoreBarangay() {
        if (!restoreTargetId) return;

        // SAMPLE MODE: Just remove the row from the table
        const row = allRows.find(r => r.dataset.id === String(restoreTargetId));
        if (row) {
            row.remove();
            collectRows();
            buildStats();
            applyFilters();
        }
        showToast('Barangay restored successfully. (Sample mode - no backend)');
        closeRestoreModal();
    }

    function closeRestoreModal() {
        const modal = document.getElementById('dbRestoreModal');
        if (modal) {
            modal.style.display = 'none';
        }
        restoreTargetId = null;
    }

    /* ── Permanently Delete Barangay ───────────────────────────── */
    let deleteTargetId = null;

    function openDeleteModal(id) {
        deleteTargetId = id;
        const row = allRows.find(r => r.dataset.id === String(id));
        const barangayName = row ? row.dataset.name : 'this barangay';
        
        const modal = document.getElementById('dbDeleteModal');
        const message = document.getElementById('dbDeleteModalMessage');
        
        if (message) {
            message.textContent = `This action cannot be undone. The barangay "${barangayName}" and all associated data will be permanently removed.`;
        }
        
        if (modal) {
            modal.style.display = 'flex';
        }
    }

    function deleteBarangay() {
        if (!deleteTargetId) return;

        // SAMPLE MODE: Just remove the row from the table
        const row = allRows.find(r => r.dataset.id === String(deleteTargetId));
        if (row) {
            row.remove();
            collectRows();
            buildStats();
            applyFilters();
        }
        showToast('Barangay permanently deleted. (Sample mode - no backend)');
        closeDeleteModal();
    }

    function closeDeleteModal() {
        const modal = document.getElementById('dbDeleteModal');
        if (modal) {
            modal.style.display = 'none';
        }
        deleteTargetId = null;
    }

    /* ── Modal events ──────────────────────────────────────────── */
    function bindModalEvents() {
        // View modal
        const viewModal = document.getElementById('dbViewModal');
        const viewCloseBtn = document.getElementById('dbViewCloseBtn');
        const viewToggleBtn = document.getElementById('dbViewToggleBtn');
        const viewModalBox = document.getElementById('dbViewModalBox');

        const closeViewModal = () => {
            if (viewModal) { 
                viewModal.style.display = 'none'; 
                viewModal.classList.remove('db-maximized'); 
            }
            if (viewModalBox) viewModalBox.classList.remove('db-maximized');
            if (viewToggleBtn) viewToggleBtn.textContent = '□';
        };

        viewCloseBtn?.addEventListener('click', closeViewModal);

        viewToggleBtn?.addEventListener('click', function (e) {
            e.stopPropagation();
            const isMax = !viewModalBox.classList.contains('db-maximized');
            viewModal.classList.toggle('db-maximized', isMax);
            viewModalBox.classList.toggle('db-maximized', isMax);
            viewToggleBtn.textContent = isMax ? '⧉' : '□';
        });

        viewModal?.addEventListener('click', e => {
            if (e.target === viewModal) closeViewModal();
        });

        // Restore modal
        const restoreModal = document.getElementById('dbRestoreModal');
        const restoreCancelBtn = document.getElementById('dbRestoreCancelBtn');
        const restoreConfirmBtn = document.getElementById('dbRestoreConfirmBtn');

        restoreCancelBtn?.addEventListener('click', closeRestoreModal);
        restoreConfirmBtn?.addEventListener('click', restoreBarangay);

        restoreModal?.addEventListener('click', e => {
            if (e.target === restoreModal) closeRestoreModal();
        });

        // ESC key
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') {
                if (viewModal?.style.display === 'flex') closeViewModal();
                if (restoreModal?.style.display === 'flex') closeRestoreModal();
            }
        });
    }

    /* ── Toast ─────────────────────────────────────────────────── */
    function showToast(msg) {
        const toast = document.getElementById('dbToast');
        const toastMsg = document.getElementById('dbToastMsg');
        if (!toast || !toastMsg) return;

        toastMsg.textContent = msg;
        toast.classList.add('show');

        setTimeout(() => {
            toast.classList.remove('show');
        }, 3000);
    }

    /* ── Icons ─────────────────────────────────────────────────── */
    function locationIcon() {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>';
    }
    function gridIcon() {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>';
    }
    function homeIcon() {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>';
    }
    function checkIcon() {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>';
    }
    function trashIcon() {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>';
    }

    /* ── Initialize ────────────────────────────────────────────── */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
