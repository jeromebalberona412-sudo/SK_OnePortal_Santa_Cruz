/**
 * Manage Location Module — JavaScript
 * Handles client-side filtering, pagination, view/edit modals, and AJAX updates.
 */

(function () {
    'use strict';

    /* ── State ─────────────────────────────────────────────────── */
    let allRows      = [];
    let filteredRows = [];
    let currentPage  = 1;
    const PAGE_SIZE  = 15;
    let editMode     = 'add'; // 'add' or 'edit'

    /* ── DOM refs ──────────────────────────────────────────────── */
    const tableBody       = document.getElementById('mlTableBody');
    const searchInput     = document.getElementById('mlSearch');
    const paginationInfo  = document.getElementById('mlPaginationInfo');
    const prevBtn         = document.getElementById('mlPrevBtn');
    const nextBtn         = document.getElementById('mlNextBtn');
    const pageNumbers     = document.getElementById('mlPageNumbers');
    const statsRow        = document.getElementById('mlStatsRow');
    const addBtn          = document.getElementById('mlAddBtn');

    /* ── Bootstrap ─────────────────────────────────────────────── */
    function init() {
        collectRows();
        buildStats();
        applyFilters();
        bindEvents();
        bindModalEvents();
    }

    /* ── Collect rows from rendered HTML ───────────────────────── */
    function collectRows() {
        allRows = Array.from(tableBody.querySelectorAll('tr[data-id]'));
    }

    /* ── Stats ─────────────────────────────────────────────────── */
    function buildStats() {
        if (!statsRow) return;

        const totalBarangay = allRows.length;
        const totalPurok  = allRows.reduce((sum, r) => sum + parseInt(r.dataset.totalPurok || 0, 10), 0);
        const totalSitio  = allRows.reduce((sum, r) => sum + parseInt(r.dataset.totalSitio || 0, 10), 0);

        const cards = [
            { label: 'Total Barangay', value: totalBarangay, colorClass: 'ml-stat-card-blue',   iconClass: 'ml-stat-icon-blue',   icon: locationIcon() },
            { label: 'Total Purok',    value: totalPurok, colorClass: 'ml-stat-card-green',  iconClass: 'ml-stat-icon-green',  icon: gridIcon() },
            { label: 'Total Sitio',    value: totalSitio, colorClass: 'ml-stat-card-yellow', iconClass: 'ml-stat-icon-yellow', icon: homeIcon() },
        ];

        statsRow.innerHTML = cards.map(c => `
            <div class="ml-stat-card ${c.colorClass}">
                <div class="ml-stat-top">
                    <span class="ml-stat-value">${c.value}</span>
                    <span class="ml-stat-icon ${c.iconClass}">${c.icon}</span>
                </div>
                <div class="ml-stat-label">${c.label}</div>
            </div>
        `).join('');
    }

    /* ── Filter logic ──────────────────────────────────────────── */
    function applyFilters() {
        const search  = (searchInput?.value || '').toLowerCase().trim();

        filteredRows = allRows.filter(row => {
            const d = row.dataset;

            if (search) {
                const haystack = [d.name].join(' ').toLowerCase();
                if (!haystack.includes(search)) return false;
            }

            return true;
        });

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
                ? 'No records'
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
                if (p === '...') return '<span class="ml-page-ellipsis">…</span>';
                return `<button type="button" class="ml-page-num ${p === currentPage ? 'active' : ''}" data-page="${p}">${p}</button>`;
            }).join('');
        }
    }

    /* ── Event bindings ────────────────────────────────────────── */
    function bindEvents() {
        searchInput?.addEventListener('input', applyFilters);

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
            if (e.target.classList.contains('ml-page-num')) {
                currentPage = parseInt(e.target.dataset.page, 10);
                renderPage();
            }
        });

        // Add button
        addBtn?.addEventListener('click', () => {
            editMode = 'add';
            openEditModal();
        });

        // Table action buttons (delegated)
        tableBody?.addEventListener('click', e => {
            const viewBtn    = e.target.closest('.ml-btn-view');
            const editBtn    = e.target.closest('.ml-btn-edit');
            const deleteBtn  = e.target.closest('.ml-btn-delete');

            if (viewBtn) {
                const id = viewBtn.dataset.id;
                openViewModal(id);
            } else if (editBtn) {
                const id = editBtn.dataset.id;
                editMode = 'edit';
                openEditModal(id);
            } else if (deleteBtn) {
                const id = deleteBtn.dataset.id;
                openDeleteModal(id);
            }
        });
    }

    /* ── View Modal ────────────────────────────────────────────── */
    function openViewModal(id) {
        // Get data from stored map or row dataset
        let barangayData = window.mlBarangayData?.[id];
        
        if (!barangayData) {
            // Fallback: get from row dataset
            const row = allRows.find(r => r.dataset.id === String(id));
            if (!row) {
                showToast('Barangay not found.');
                return;
            }
            barangayData = {
                id: id,
                name: row.dataset.name,
                municipality: row.dataset.municipality,
                province: row.dataset.province,
                region: row.dataset.region,
                status: row.dataset.status,
                puroks: [],
                sitios: [],
            };
        }

        renderViewModal(barangayData);
    }

    function renderViewModal(b) {
        const modal = document.getElementById('mlViewModal');
        const body  = document.getElementById('mlViewBody');
        if (!modal || !body) return;

        body.innerHTML = `
            <div class="ml-view-section">
                <div class="ml-view-section-header">
                    ${locationIcon()}
                    <span>Barangay Information</span>
                </div>
                <div class="ml-view-grid">
                    <div class="ml-view-item">
                        <span class="ml-view-label">Barangay Name</span>
                        <span class="ml-view-value">${b.name || '—'}</span>
                    </div>
                    <div class="ml-view-item">
                        <span class="ml-view-label">Municipality</span>
                        <span class="ml-view-value">${b.municipality || '—'}</span>
                    </div>
                    <div class="ml-view-item">
                        <span class="ml-view-label">Province</span>
                        <span class="ml-view-value">${b.province || '—'}</span>
                    </div>
                    <div class="ml-view-item">
                        <span class="ml-view-label">Region</span>
                        <span class="ml-view-value">${b.region || '—'}</span>
                    </div>
                    <div class="ml-view-item">
                        <span class="ml-view-label">Status</span>
                        <span class="ml-view-value">${b.status || 'Active'}</span>
                    </div>
                </div>
            </div>

            <div class="ml-view-section">
                <div class="ml-view-section-header">
                    ${gridIcon()}
                    <span>Purok List (${b.puroks?.length || 0})</span>
                </div>
                <div class="ml-view-list">
                    ${b.puroks && b.puroks.length > 0
                        ? b.puroks.map(p => `
                            <div class="ml-view-list-item">
                                ${checkIcon()}
                                <span>${p.name}</span>
                            </div>
                        `).join('')
                        : '<p style="color:#6b7280;font-size:14px;">No Purok added.</p>'
                    }
                </div>
            </div>

            <div class="ml-view-section">
                <div class="ml-view-section-header">
                    ${homeIcon()}
                    <span>Sitio List (${b.sitios?.length || 0})</span>
                </div>
                <div class="ml-view-list">
                    ${b.sitios && b.sitios.length > 0
                        ? b.sitios.map(s => `
                            <div class="ml-view-list-item">
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

    /* ── Edit Modal ────────────────────────────────────────────── */
    function openEditModal(id = null) {
        const modal = document.getElementById('mlEditModal');
        const title = document.getElementById('mlEditModalTitle');
        const form  = document.getElementById('mlEditForm');
        if (!modal || !form) return;

        // Reset form
        form.reset();
        clearErrors();
        document.getElementById('mlEditId').value = '';
        document.getElementById('mlPurokList').innerHTML = '';
        document.getElementById('mlSitioList').innerHTML = '';

        if (id) {
            // Edit mode
            title.textContent = 'Edit Barangay';
            
            // Get data from stored map
            const barangayData = window.mlBarangayData?.[id];
            if (barangayData) {
                populateEditForm(barangayData);
            } else {
                // Fallback: get from row dataset
                const row = allRows.find(r => r.dataset.id === String(id));
                if (row) {
                    const data = {
                        id: id,
                        name: row.dataset.name,
                        municipality: row.dataset.municipality,
                        province: row.dataset.province,
                        region: row.dataset.region,
                        status: row.dataset.status,
                        puroks: [],
                        sitios: [],
                    };
                    populateEditForm(data);
                }
            }
        } else {
            // Add mode
            title.textContent = 'Add Barangay';
        }

        modal.style.display = 'flex';
    }

    function populateEditForm(b) {
        document.getElementById('mlEditId').value = b.id;
        document.getElementById('mlEditName').value = b.name || '';
        document.getElementById('mlEditMunicipality').value = b.municipality || '';
        document.getElementById('mlEditProvince').value = b.province || '';
        document.getElementById('mlEditRegion').value = b.region || '';

        // Populate Purok list
        const purokList = document.getElementById('mlPurokList');
        if (b.puroks && b.puroks.length > 0) {
            b.puroks.forEach(p => {
                addDynamicItem('purok', p.id, p.name);
            });
        }

        // Populate Sitio list
        const sitioList = document.getElementById('mlSitioList');
        if (b.sitios && b.sitios.length > 0) {
            b.sitios.forEach(s => {
                addDynamicItem('sitio', s.id, s.name);
            });
        }
    }

    /* ── Dynamic List (Purok/Sitio) ────────────────────────────── */
    function addDynamicItem(type, id = '', name = '') {
        const list = type === 'purok' ? document.getElementById('mlPurokList') : document.getElementById('mlSitioList');
        if (!list) return;

        const div = document.createElement('div');
        div.className = 'ml-dynamic-item';
        div.innerHTML = `
            <input type="hidden" name="${type}s[${Date.now()}][id]" value="${id}">
            <input type="text" name="${type}s[${Date.now()}][name]" value="${name}" placeholder="${type === 'purok' ? 'Purok' : 'Sitio'} Name" required>
            <button type="button" class="ml-btn-remove-item">Remove</button>
        `;

        div.querySelector('.ml-btn-remove-item').addEventListener('click', () => {
            div.remove();
        });

        list.appendChild(div);
    }

    /* ── Form submission ───────────────────────────────────────── */
    function handleFormSubmit(e) {
        e.preventDefault();
        clearErrors();

        const form = e.target;
        const id   = document.getElementById('mlEditId').value;

        // Collect form data
        const formData = new FormData(form);
        const data = {};
        
        // Basic fields
        data.name = formData.get('name');
        data.municipality = formData.get('municipality');
        data.province = formData.get('province');
        data.region = formData.get('region');
        data.status = 'Active'; // Default to Active

        // Validate required fields
        if (!data.name || !data.name.trim()) {
            displayErrors({ name: ['Barangay name is required.'] });
            return;
        }
        if (!data.municipality || !data.municipality.trim()) {
            displayErrors({ municipality: ['Municipality is required.'] });
            return;
        }
        if (!data.province || !data.province.trim()) {
            displayErrors({ province: ['Province is required.'] });
            return;
        }
        if (!data.region || !data.region.trim()) {
            displayErrors({ region: ['Region is required.'] });
            return;
        }

        // Collect Puroks
        data.puroks = [];
        const purokInputs = form.querySelectorAll('[name^="puroks"]');
        const purokMap = {};
        purokInputs.forEach(input => {
            const match = input.name.match(/puroks\[(\d+)\]\[(\w+)\]/);
            if (match) {
                const [, idx, field] = match;
                if (!purokMap[idx]) purokMap[idx] = {};
                purokMap[idx][field] = input.value;
            }
        });
        data.puroks = Object.values(purokMap).filter(p => p.name && p.name.trim());

        // Collect Sitios
        data.sitios = [];
        const sitioInputs = form.querySelectorAll('[name^="sitios"]');
        const sitioMap = {};
        sitioInputs.forEach(input => {
            const match = input.name.match(/sitios\[(\d+)\]\[(\w+)\]/);
            if (match) {
                const [, idx, field] = match;
                if (!sitioMap[idx]) sitioMap[idx] = {};
                sitioMap[idx][field] = input.value;
            }
        });
        data.sitios = Object.values(sitioMap).filter(s => s.name && s.name.trim());

        // Show loading
        const saveBtn = document.getElementById('mlEditSaveBtn');
        const saveBtnText = document.getElementById('mlEditSaveBtnText');
        const saveBtnSpinner = document.getElementById('mlEditSaveBtnSpinner');
        
        if (saveBtn && saveBtnText && saveBtnSpinner) {
            saveBtn.disabled = true;
            saveBtnText.textContent = 'Saving...';
            saveBtnSpinner.style.display = 'inline-block';
        }

        // Simulate save delay
        setTimeout(() => {
            if (saveBtn && saveBtnText && saveBtnSpinner) {
                saveBtn.disabled = false;
                saveBtnText.textContent = 'Save Changes';
                saveBtnSpinner.style.display = 'none';
            }

            if (id) {
                // Edit mode - update existing row
                updateTableRow(id, data);
                showToast('Barangay updated successfully.');
            } else {
                // Add mode - create new row
                addTableRow(data);
                showToast('Barangay added successfully.');
            }

            closeEditModal();
            buildStats();
            applyFilters();
        }, 800);
    }

    /* ── Add new row to table ──────────────────────────────────── */
    function addTableRow(data) {
        const tbody = document.getElementById('mlTableBody');
        if (!tbody) return;

        // Generate unique ID
        const newId = Date.now();

        // Create new row
        const tr = document.createElement('tr');
        tr.setAttribute('data-id', newId);
        tr.setAttribute('data-name', data.name);
        tr.setAttribute('data-municipality', data.municipality || '');
        tr.setAttribute('data-province', data.province || '');
        tr.setAttribute('data-region', data.region || '');
        tr.setAttribute('data-status', data.status);
        tr.setAttribute('data-total-purok', data.puroks.length);
        tr.setAttribute('data-total-sitio', data.sitios.length);

        tr.innerHTML = `
            <td class="ml-name-cell">${data.name}</td>
            <td class="ml-count-cell">${data.puroks.length}</td>
            <td class="ml-count-cell">${data.sitios.length}</td>
            <td>
                <div class="ml-action-btns">
                    <button type="button" class="ml-btn-view" data-id="${newId}" aria-label="View ${data.name}">View</button>
                    <button type="button" class="ml-btn-edit" data-id="${newId}" aria-label="Edit ${data.name}">Edit</button>
                    <button type="button" class="ml-btn-delete" data-id="${newId}" aria-label="Delete ${data.name}">Delete</button>
                </div>
            </td>
        `;

        // Store full data in a global map for later retrieval
        if (!window.mlBarangayData) window.mlBarangayData = {};
        window.mlBarangayData[newId] = {
            id: newId,
            name: data.name,
            municipality: data.municipality || '',
            province: data.province || '',
            region: data.region || '',
            status: data.status,
            puroks: data.puroks.map((p, idx) => ({ id: `p${newId}_${idx}`, name: p.name })),
            sitios: data.sitios.map((s, idx) => ({ id: `s${newId}_${idx}`, name: s.name })),
        };

        // Remove empty state if exists
        const emptyRow = tbody.querySelector('tr:not([data-id])');
        if (emptyRow) emptyRow.remove();

        // Add to table
        tbody.insertBefore(tr, tbody.firstChild);

        // Update allRows
        collectRows();
    }

    /* ── Update existing row ───────────────────────────────────── */
    function updateTableRow(id, data) {
        const row = allRows.find(r => r.dataset.id === String(id));
        if (!row) return;

        // Update dataset
        row.setAttribute('data-name', data.name);
        row.setAttribute('data-municipality', data.municipality || '');
        row.setAttribute('data-province', data.province || '');
        row.setAttribute('data-region', data.region || '');
        row.setAttribute('data-status', data.status);
        row.setAttribute('data-total-purok', data.puroks.length);
        row.setAttribute('data-total-sitio', data.sitios.length);

        // Update cells
        const cells = row.querySelectorAll('td');
        cells[0].textContent = data.name;
        cells[1].textContent = data.puroks.length;
        cells[2].textContent = data.sitios.length;

        // Update stored data
        if (!window.mlBarangayData) window.mlBarangayData = {};
        window.mlBarangayData[id] = {
            id: id,
            name: data.name,
            municipality: data.municipality || '',
            province: data.province || '',
            region: data.region || '',
            status: data.status,
            puroks: data.puroks.map((p, idx) => ({ 
                id: p.id || `p${id}_${idx}`, 
                name: p.name 
            })),
            sitios: data.sitios.map((s, idx) => ({ 
                id: s.id || `s${id}_${idx}`, 
                name: s.name 
            })),
        };
    }

    /* ── Archive Barangay ──────────────────────────────────────── */
    let deleteTargetId = null;

    function openDeleteModal(id) {
        deleteTargetId = id;
        const row = allRows.find(r => r.dataset.id === String(id));
        const barangayName = row ? row.dataset.name : 'this barangay';
        
        const modal = document.getElementById('mlDeleteModal');
        const message = document.getElementById('mlDeleteModalMessage');
        
        if (message) {
            message.textContent = `Are you sure you want to delete "${barangayName}"? This action cannot be undone and will permanently remove all associated Purok and Sitio data.`;
        }
        
        if (modal) {
            modal.style.display = 'flex';
        }
    }

    function deleteBarangay() {
        if (!deleteTargetId) return;

        // Find and remove the row
        const row = allRows.find(r => r.dataset.id === String(deleteTargetId));
        if (row) {
            row.remove();
            
            // Remove from stored data
            if (window.mlBarangayData?.[deleteTargetId]) {
                delete window.mlBarangayData[deleteTargetId];
            }

            // Update state
            collectRows();
            buildStats();
            applyFilters();
            
            showToast('Barangay deleted successfully.');
        } else {
            showToast('Barangay not found.');
        }

        // Close modal and reset
        closeDeleteModal();
    }

    function closeDeleteModal() {
        const modal = document.getElementById('mlDeleteModal');
        if (modal) {
            modal.style.display = 'none';
        }
        deleteTargetId = null;
    }

    /* ── Modal events ──────────────────────────────────────────── */
    function bindModalEvents() {
        // View modal
        const viewModal = document.getElementById('mlViewModal');
        const viewCloseBtn = document.getElementById('mlViewCloseBtn');
        const viewToggleBtn = document.getElementById('mlViewToggleBtn');
        const viewModalBox = document.getElementById('mlViewModalBox');

        const closeViewModal = () => {
            if (viewModal) { 
                viewModal.style.display = 'none'; 
                viewModal.classList.remove('ml-maximized'); 
            }
            if (viewModalBox) viewModalBox.classList.remove('ml-maximized');
            if (viewToggleBtn) viewToggleBtn.textContent = '□';
        };

        viewCloseBtn?.addEventListener('click', closeViewModal);

        viewToggleBtn?.addEventListener('click', function (e) {
            e.stopPropagation();
            const isMax = !viewModalBox.classList.contains('ml-maximized');
            viewModal.classList.toggle('ml-maximized', isMax);
            viewModalBox.classList.toggle('ml-maximized', isMax);
            viewToggleBtn.textContent = isMax ? '⧉' : '□';
        });

        viewModal?.addEventListener('click', e => {
            if (e.target === viewModal) closeViewModal();
        });

        // Edit modal
        const editModal = document.getElementById('mlEditModal');
        const editCloseBtn = document.getElementById('mlEditCloseBtn');
        const editCancelBtn = document.getElementById('mlEditCancelBtn');
        const editToggleBtn = document.getElementById('mlEditToggleBtn');
        const editModalBox = document.getElementById('mlEditModalBox');
        const editForm = document.getElementById('mlEditForm');

        const closeEditModalFn = () => {
            if (editModal) { 
                editModal.style.display = 'none'; 
                editModal.classList.remove('ml-maximized'); 
            }
            if (editModalBox) editModalBox.classList.remove('ml-maximized');
            if (editToggleBtn) editToggleBtn.textContent = '□';
            closeEditModal(); // Call the existing close function
        };

        editCloseBtn?.addEventListener('click', closeEditModalFn);
        editCancelBtn?.addEventListener('click', closeEditModalFn);

        editToggleBtn?.addEventListener('click', function (e) {
            e.stopPropagation();
            const isMax = !editModalBox.classList.contains('ml-maximized');
            editModal.classList.toggle('ml-maximized', isMax);
            editModalBox.classList.toggle('ml-maximized', isMax);
            editToggleBtn.textContent = isMax ? '⧉' : '□';
        });

        editModal?.addEventListener('click', e => {
            if (e.target === editModal) closeEditModalFn();
        });

        editForm?.addEventListener('submit', handleFormSubmit);

        // Add Purok/Sitio buttons
        document.getElementById('mlAddPurokBtn')?.addEventListener('click', () => {
            addDynamicItem('purok');
        });

        document.getElementById('mlAddSitioBtn')?.addEventListener('click', () => {
            addDynamicItem('sitio');
        });

        // ESC key
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') {
                if (viewModal?.style.display === 'flex') closeViewModal();
                if (editModal?.style.display === 'flex') closeEditModalFn();
                const deleteModal = document.getElementById('mlDeleteModal');
                if (deleteModal?.style.display === 'flex') closeDeleteModal();
            }
        });

        // Delete modal
        const deleteModal = document.getElementById('mlDeleteModal');
        const deleteCancelBtn = document.getElementById('mlDeleteCancelBtn');
        const deleteConfirmBtn = document.getElementById('mlDeleteConfirmBtn');

        deleteCancelBtn?.addEventListener('click', closeDeleteModal);
        deleteConfirmBtn?.addEventListener('click', deleteBarangay);

        deleteModal?.addEventListener('click', e => {
            if (e.target === deleteModal) closeDeleteModal();
        });
    }

    function closeEditModal() {
        const modal = document.getElementById('mlEditModal');
        if (modal) modal.style.display = 'none';
    }

    /* ── Error handling ────────────────────────────────────────── */
    function clearErrors() {
        document.querySelectorAll('.ml-field-error').forEach(el => {
            el.classList.remove('show');
            el.textContent = '';
        });
        document.querySelectorAll('.ml-form-input.error').forEach(el => {
            el.classList.remove('error');
        });
    }

    function displayErrors(errors) {
        for (const [field, messages] of Object.entries(errors)) {
            const errorEl = document.getElementById(`err${field.charAt(0).toUpperCase() + field.slice(1)}`);
            const inputEl = document.getElementById(`mlEdit${field.charAt(0).toUpperCase() + field.slice(1)}`);
            if (errorEl) {
                errorEl.textContent = messages[0];
                errorEl.classList.add('show');
            }
            if (inputEl) {
                inputEl.classList.add('error');
            }
        }
    }

    /* ── Loading ───────────────────────────────────────────────── */
    function showLoading() {
        const loading = document.getElementById('mlTableLoading');
        if (loading) loading.style.display = 'flex';
    }

    function hideLoading() {
        const loading = document.getElementById('mlTableLoading');
        if (loading) loading.style.display = 'none';
    }

    /* ── Toast ─────────────────────────────────────────────────── */
    function showToast(msg) {
        const toast = document.getElementById('mlToast');
        const toastMsg = document.getElementById('mlToastMsg');
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

    /* ── Initialize ────────────────────────────────────────────── */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
