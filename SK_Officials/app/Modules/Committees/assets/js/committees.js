document.addEventListener('DOMContentLoaded', () => {
    initializeCommitteesUI();
});

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

async function apiFetch(url, options = {}) {
    const { headers: extraHeaders, body, ...rest } = options;
    const headers = {
        'X-CSRF-TOKEN': csrfToken(),
        'Accept': 'application/json',
        ...extraHeaders,
    };

    if (body && !(body instanceof FormData)) {
        headers['Content-Type'] = 'application/json';
    }

    const res = await fetch(url, {
        ...rest,
        headers,
        body: body && !(body instanceof FormData) ? JSON.stringify(body) : body,
    });

    const data = await res.json().catch(() => ({}));

    if (!res.ok) {
        const message = data.message || Object.values(data.errors || {}).flat()[0] || 'Request failed.';
        throw new Error(message);
    }

    return data;
}

function initializeCommitteesUI() {
    const grid = document.getElementById('committeeGrid');
    const searchInput = document.getElementById('committeeSearch');
    const searchInputMobile = document.getElementById('committeeSearchMobile');
    const nameFilter = document.getElementById('committeeNameFilter');
    const headFilter = document.getElementById('committeeHeadFilter');
    const addBtn = document.getElementById('addCommitteeBtn');
    const addBtnMobile = document.getElementById('addCommitteeBtnMobile');
    const modal = document.getElementById('committeeModal');
    const nameInput = document.getElementById('committeeNameInput');
    const otherCommitteeField = document.getElementById('otherCommitteeField');
    const otherCommitteeInput = document.getElementById('otherCommitteeInput');
    const headInput = document.getElementById('committeeHeadInput');
    const descInput = document.getElementById('committeeDescriptionInput');
    const saveBtn = document.getElementById('committeeSaveBtn');
    const viewModal = document.getElementById('committeeViewModal');

    function showToast(message, type = 'success') {
        const existingToast = document.querySelector('.committee-toast');
        if (existingToast) {
            existingToast.remove();
        }

        const toast = document.createElement('div');
        toast.className = `committee-toast committee-toast-${type}`;
        toast.innerHTML = `
            <div class="committee-toast-icon">
                ${type === 'success' ? '✓' : '✕'}
            </div>
            <div class="committee-toast-message">${message}</div>
        `;

        document.body.appendChild(toast);

        setTimeout(() => {
            toast.classList.add('committee-toast-show');
        }, 10);

        setTimeout(() => {
            toast.classList.remove('committee-toast-show');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 3000);
    }

    function resetModalMaximize(backdropEl) {
        if (!backdropEl) return;
        backdropEl.classList.remove('modal-maximized');
        const box = backdropEl.querySelector('.modal-box');
        if (box) box.classList.remove('modal-maximized');
        const toggleBtn = backdropEl.querySelector('[data-modal-toggle]');
        if (toggleBtn) toggleBtn.textContent = '□';
    }

    function wireModalToggle(backdropEl) {
        if (!backdropEl) return;
        const toggleBtn = backdropEl.querySelector('[data-modal-toggle]');
        const box = backdropEl.querySelector('.modal-box');
        if (!toggleBtn || !box) return;

        toggleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const willMaximize = !box.classList.contains('modal-maximized');
            backdropEl.classList.toggle('modal-maximized', willMaximize);
            box.classList.toggle('modal-maximized', willMaximize);
            toggleBtn.textContent = willMaximize ? '⧉' : '□';
        });
    }

    if (!grid) return;

    let committees = [];
    let skOfficials = [];
    let editingId = null;

    let currentQuery = '';
    let currentNameFilter = '';
    let currentHeadFilter = '';

    function populateDropdowns() {
        const assignedHeadIds = committees.map((c) => String(c.head_id));
        const assignedCommittees = committees.map((c) => c.name);

        if (nameFilter) {
            const uniqueNames = [...new Set(committees.map((c) => c.name))].sort();
            nameFilter.innerHTML = '<option value="">All Committees</option>';
            uniqueNames.forEach((name) => {
                const option = document.createElement('option');
                option.value = name;
                option.textContent = name;
                nameFilter.appendChild(option);
            });
        }

        if (headFilter) {
            const uniqueHeads = [...new Set(committees.map((c) => c.head))].sort();
            headFilter.innerHTML = '<option value="">All Committee Heads</option>';
            uniqueHeads.forEach((head) => {
                const option = document.createElement('option');
                option.value = head;
                option.textContent = head;
                headFilter.appendChild(option);
            });
        }

        if (headInput) {
            const currentValue = headInput.value;
            headInput.innerHTML = '<option value="">Select Committee Head</option>';
            skOfficials.forEach((official) => {
                const isAssigned = assignedHeadIds.includes(String(official.id)) &&
                    !(editingId !== null && committees.find((c) => c.id === editingId)?.head_id === official.id);

                if (!isAssigned) {
                    const option = document.createElement('option');
                    option.value = String(official.id);
                    option.textContent = official.full_name;
                    headInput.appendChild(option);
                }
            });

            if (currentValue) {
                headInput.value = currentValue;
            }
        }

        if (nameInput) {
            const currentValue = nameInput.value;
            nameInput.innerHTML = '<option value="">Select Committee</option>';

            const standardCommittees = [
                'Committee on Peace and Order',
                'Committee on Health',
                'Committee on Education',
                'Committee on Environment',
                'Committee on Social Services',
                'Committee on Livelihood / Employment',
                'Committee on Infrastructure',
                'Committee on Budget and Finance',
                'Committee on Women and Family',
                'Committee on Youth and Sports Development'
            ];

            standardCommittees.forEach((committee) => {
                const isAssigned = assignedCommittees.includes(committee) &&
                    !(editingId !== null && committees.find((c) => c.id === editingId)?.name === committee);

                if (!isAssigned) {
                    const option = document.createElement('option');
                    option.value = committee;
                    option.textContent = committee;
                    nameInput.appendChild(option);
                }
            });

            const otherOption = document.createElement('option');
            otherOption.value = 'Other';
            otherOption.textContent = 'Other';
            nameInput.appendChild(otherOption);

            if (currentValue) {
                nameInput.value = currentValue;
            }
        }
    }

    function render() {
        grid.innerHTML = '';

        const filtered = committees.filter((c) => {
            const matchesSearch =
                !currentQuery ||
                c.name.toLowerCase().includes(currentQuery) ||
                c.head.toLowerCase().includes(currentQuery) ||
                (c.description && c.description.toLowerCase().includes(currentQuery));

            const matchesName = !currentNameFilter || c.name === currentNameFilter;
            const matchesHead = !currentHeadFilter || c.head === currentHeadFilter;

            return matchesSearch && matchesName && matchesHead;
        });

        if (filtered.length === 0) {
            const empty = document.createElement('tr');
            empty.innerHTML = '<td colspan="6" class="empty-state">No committees assigned yet. Click "Assign Committee".</td>';
            grid.appendChild(empty);
            return;
        }

        filtered.forEach((c) => {
            const row = document.createElement('tr');
            const descriptionHtml = `
                <div class="committee-description">
                    <div class="committee-description-item">
                        <span class="committee-description-value">${c.description || 'N/A'}</span>
                    </div>
                </div>
            `;

            row.innerHTML = `
                <td>${c.name}</td>
                <td>${c.head}</td>
                <td>${c.assigned_date || '—'}</td>
                <td>${c.assigned_time || '—'}</td>
                <td>${descriptionHtml}</td>
                <td>
                    <div class="committee-actions">
                        <button type="button" class="btn-action-view" data-action="view" data-id="${c.id}">View</button>
                        <button type="button" class="btn-action-edit" data-action="edit" data-id="${c.id}">Edit</button>
                    </div>
                </td>
            `;
            grid.appendChild(row);
        });
    }

    async function loadCommittees() {
        const response = await apiFetch('/api/committees');
        committees = response.data || [];
        populateDropdowns();
        render();
    }

    async function loadSkOfficials() {
        const response = await apiFetch('/api/committees/sk-officials');
        skOfficials = response.data || [];
        populateDropdowns();
    }

    async function initializeData() {
        try {
            await Promise.all([loadSkOfficials(), loadCommittees()]);
        } catch (error) {
            showToast(error.message || 'Failed to load committees.', 'error');
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', () => {
            currentQuery = searchInput.value.trim().toLowerCase();
            if (searchInputMobile) {
                searchInputMobile.value = searchInput.value;
            }
            render();
        });
    }

    if (searchInputMobile) {
        searchInputMobile.addEventListener('input', () => {
            currentQuery = searchInputMobile.value.trim().toLowerCase();
            if (searchInput) {
                searchInput.value = searchInputMobile.value;
            }
            render();
        });
    }

    if (nameFilter) {
        nameFilter.addEventListener('change', () => {
            currentNameFilter = nameFilter.value;
            render();
        });
    }

    if (headFilter) {
        headFilter.addEventListener('change', () => {
            currentHeadFilter = headFilter.value;
            render();
        });
    }

    if (nameInput) {
        nameInput.addEventListener('focus', () => {
            const instructionOption = nameInput.querySelector('option[value=""]');
            if (instructionOption) {
                instructionOption.disabled = true;
            }
        });

        nameInput.addEventListener('change', () => {
            if (otherCommitteeField && otherCommitteeInput) {
                if (nameInput.value === 'Other') {
                    otherCommitteeField.style.display = 'block';
                } else {
                    otherCommitteeField.style.display = 'none';
                    otherCommitteeInput.value = '';
                }
            }
        });
    }

    if (headInput) {
        headInput.addEventListener('focus', () => {
            const instructionOption = headInput.querySelector('option[value=""]');
            if (instructionOption) {
                instructionOption.disabled = true;
            }
        });
    }

    if (descInput) {
        const charCount = document.getElementById('descCharCount');
        descInput.addEventListener('input', () => {
            if (charCount) {
                charCount.textContent = descInput.value.length;
            }
        });
    }

    function openModal() {
        if (!modal) return;
        populateDropdowns();
        modal.style.display = 'flex';
        resetModalMaximize(modal);
        editingId = null;
        if (saveBtn) saveBtn.textContent = 'Save';
        if (nameInput) {
            nameInput.focus();
            nameInput.disabled = false;
        }
        if (headInput) {
            headInput.disabled = false;
        }

        const charCount = document.getElementById('descCharCount');
        if (charCount) charCount.textContent = '0';
    }

    function closeModal() {
        if (!modal) return;
        modal.style.display = 'none';
        resetModalMaximize(modal);
        if (nameInput) {
            nameInput.value = '';
            nameInput.disabled = false;
        }
        if (otherCommitteeInput) otherCommitteeInput.value = '';
        if (otherCommitteeField) otherCommitteeField.style.display = 'none';
        if (headInput) {
            headInput.value = '';
            headInput.disabled = false;
        }
        if (descInput) descInput.value = '';

        const charCount = document.getElementById('descCharCount');
        if (charCount) charCount.textContent = '0';
        editingId = null;
    }

    if (addBtn) {
        addBtn.addEventListener('click', openModal);
    }

    if (addBtnMobile) {
        addBtnMobile.addEventListener('click', openModal);
    }

    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal || e.target.hasAttribute('data-modal-close') || e.target.hasAttribute('data-modal-cancel')) {
                closeModal();
            }
        });
    }

    if (grid) {
        grid.addEventListener('click', (e) => {
            const target = e.target;
            if (!(target instanceof HTMLElement)) return;
            const action = target.getAttribute('data-action');
            if (action !== 'view' && action !== 'edit') return;

            const committeeId = Number(target.getAttribute('data-id'));
            const committee = committees.find((c) => c.id === committeeId);
            if (!committee) return;

            if (action === 'view') {
                const viewName = document.getElementById('viewCommitteeName');
                const viewNameInfo = document.getElementById('viewCommitteeNameInfo');
                const viewHead = document.getElementById('viewCommitteeHead');
                const viewStatus = document.getElementById('viewCommitteeStatus');
                const viewDate = document.getElementById('viewCommitteeDateAssigned');
                const viewDateCreated = document.getElementById('viewCommitteeDateCreated');
                const viewDesc = document.getElementById('viewCommitteeDescription');
                const viewResp = document.getElementById('viewCommitteeResponsibilities');

                if (viewName) viewName.textContent = committee.name || '—';
                if (viewNameInfo) viewNameInfo.textContent = committee.name || '—';
                if (viewHead) viewHead.textContent = committee.head || '—';
                if (viewStatus) viewStatus.textContent = committee.status || 'Active';
                if (viewDate) viewDate.textContent = committee.assigned_date || '—';
                if (viewDateCreated) {
                    viewDateCreated.textContent = committee.assigned_date ? `Assigned ${committee.assigned_date}` : '';
                }
                if (viewDesc) viewDesc.textContent = committee.description || '—';
                if (viewResp) viewResp.textContent = committee.description || '—';

                if (viewModal) {
                    resetModalMaximize(viewModal);
                    viewModal.style.display = 'flex';
                }
                return;
            }

            editingId = committee.id;
            if (nameInput) {
                const standardValues = Array.from(nameInput.options).map((o) => o.value);
                if (standardValues.includes(committee.name)) {
                    nameInput.value = committee.name;
                } else {
                    nameInput.value = 'Other';
                    if (otherCommitteeField) otherCommitteeField.style.display = 'block';
                    if (otherCommitteeInput) otherCommitteeInput.value = committee.name;
                }
                nameInput.disabled = false;
            }
            if (headInput) {
                headInput.value = String(committee.head_id);
                headInput.disabled = false;
            }
            if (descInput) {
                descInput.value = committee.description || '';
                const charCount = document.getElementById('descCharCount');
                if (charCount) {
                    charCount.textContent = descInput.value.length;
                }
            }
            if (saveBtn) saveBtn.textContent = 'Update';
            populateDropdowns();
            if (modal) {
                resetModalMaximize(modal);
                modal.style.display = 'flex';
            }
        });
    }

    if (viewModal) {
        viewModal.addEventListener('click', (e) => {
            if (e.target === viewModal || e.target.hasAttribute('data-view-close')) {
                resetModalMaximize(viewModal);
                viewModal.style.display = 'none';
            }
        });
    }

    if (saveBtn) {
        saveBtn.addEventListener('click', async () => {
            let name = (nameInput?.value || '').trim();
            const otherCommittee = (otherCommitteeInput?.value || '').trim();
            const headId = Number(headInput?.value || 0);
            const desc = (descInput?.value || '').trim();

            if (name === 'Other' && otherCommittee) {
                name = otherCommittee;
            } else if (name === 'Other') {
                alert('Please specify committee name.');
                return;
            }

            if (!name || !headId) {
                alert('Please select a committee and assign a committee head.');
                return;
            }

            saveBtn.disabled = true;
            saveBtn.textContent = 'Saving...';

            const payload = {
                committee_name: name,
                committee_head_id: headId,
                description: desc,
            };

            try {
                if (editingId !== null) {
                    await apiFetch(`/api/committees/${editingId}`, {
                        method: 'PUT',
                        body: payload,
                    });
                    showToast('Update successful.');
                } else {
                    await apiFetch('/api/committees', {
                        method: 'POST',
                        body: payload,
                    });
                    showToast('Assignment successful.');
                }

                closeModal();
                await loadCommittees();
            } catch (error) {
                alert(error.message || 'Failed to save committee.');
            } finally {
                saveBtn.disabled = false;
                saveBtn.textContent = editingId !== null ? 'Update' : 'Save';
            }
        });
    }

    initializeData();
    wireModalToggle(modal);
    wireModalToggle(viewModal);
}
