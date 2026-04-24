document.addEventListener('DOMContentLoaded', () => {
    initializeCommitteesUI();
});

function initializeCommitteesUI() {
    const grid = document.getElementById('committeeGrid');
    const searchInput = document.getElementById('committeeSearch');
    const headFilter = document.getElementById('committeeHeadFilter');
    const addBtn = document.getElementById('addCommitteeBtn');
    const modal = document.getElementById('committeeModal');
    const nameInput = document.getElementById('committeeNameInput');
    const otherCommitteeField = document.getElementById('otherCommitteeField');
    const otherCommitteeInput = document.getElementById('otherCommitteeInput');
    const headInput = document.getElementById('committeeHeadInput');
    const dateInput = document.getElementById('committeeDateInput');
    const descInput = document.getElementById('committeeDescriptionInput');
    const saveBtn = document.getElementById('committeeSaveBtn');
    const successModal = document.getElementById('committeeSuccessModal');
    const successMessage = document.getElementById('committeeSuccessMessage');
    const viewModal = document.getElementById('committeeViewModal');
    const viewCommitteeHead = document.getElementById('viewCommitteeHead');

    // Modal maximize/minimize (restore) controls
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

    // Start with empty list; entries appear only after "Add Committee"
    const committees = [];
    let editingIndex = -1;

    let currentQuery = '';
    let currentHeadFilter = '';
    const officialMembers = [
        'Paula A Talais',
        'Jerome Balberona',
        'Gabriel Garcia',
        'Frankien Belangoy',
        'Juan Dela Cruz',
        'Jane Doe',
        'Mark Anthony Reyes',
        'Maria Clara Santos',
        'Robert James Tan',
    ];

    function populateDropdowns() {
        const assignedHeads = committees.map(c => c.head);
        const assignedCommittees = committees.map(c => c.name);

        if (headFilter) {
            headFilter.innerHTML = '<option value="">All heads</option>';
            officialMembers.forEach((name) => {
                const option = document.createElement('option');
                option.value = name;
                option.textContent = name;
                headFilter.appendChild(option);
            });
        }

        if (headInput) {
            headInput.innerHTML = '<option value="">Select Committee Head</option>';
            officialMembers.forEach((name) => {
                // Skip if this person is already assigned to a committee (except when editing)
                const isAssigned = assignedHeads.includes(name) &&
                    !(editingIndex >= 0 && committees[editingIndex]?.head === name);

                if (!isAssigned) {
                    const option = document.createElement('option');
                    option.value = name;
                    option.textContent = name;
                    headInput.appendChild(option);
                }
            });
        }

        // Populate committee name dropdown with available committees
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
                // Skip if this committee is already assigned (except when editing the same committee)
                const isAssigned = assignedCommittees.includes(committee) &&
                    !(editingIndex >= 0 && committees[editingIndex]?.name === committee);

                if (!isAssigned) {
                    const option = document.createElement('option');
                    option.value = committee;
                    option.textContent = committee;
                    nameInput.appendChild(option);
                }
            });

            // Always add "Other" option
            const otherOption = document.createElement('option');
            otherOption.value = 'Other';
            otherOption.textContent = 'Other';
            nameInput.appendChild(otherOption);

            // Restore previous selection if it still exists
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
                c.head.toLowerCase().includes(currentQuery);

            const matchesHead =
                !currentHeadFilter ||
                c.head.toLowerCase() === currentHeadFilter.toLowerCase();

            return matchesSearch && matchesHead;
        });

        if (filtered.length === 0) {
            const empty = document.createElement('tr');
            empty.innerHTML = '<td colspan="5" class="empty-state">No committees assigned yet. Click "Assign Committee".</td>';
            grid.appendChild(empty);
            return;
        }

        filtered.forEach((c) => {
            const sourceIndex = committees.indexOf(c);
            const row = document.createElement('tr');

            // Format description HTML (simplified for table display)
            const descriptionHtml = `
                <div class="committee-description">
                    <div class="committee-description-item">
                        <span class="committee-description-value">${c.description || c.purpose || 'N/A'}</span>
                    </div>
                </div>
            `;

            row.innerHTML = `
                <td>${c.name}</td>
                <td>${c.head}</td>
                <td>${c.dateCreated || '—'}</td>
                <td>${descriptionHtml}</td>
                <td>
                    <div class="committee-actions">
                        <button type="button" class="btn-action-view" data-action="view" data-index="${sourceIndex}">View</button>
                        <button type="button" class="btn-action-edit" data-action="edit" data-index="${sourceIndex}">Edit</button>
                    </div>
                </td>
            `;
            grid.appendChild(row);
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', () => {
            currentQuery = searchInput.value.trim().toLowerCase();
            render();
        });
    }

    if (headFilter) {
        headFilter.addEventListener('change', () => {
            currentHeadFilter = headFilter.value;
            render();
        });
    }

    // Committee dropdown change event
    if (nameInput) {
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

    // Open / close modal
    function openModal() {
        if (!modal) return;
        populateDropdowns(); // Refresh dropdown to show available committee heads and committees
        modal.style.display = 'flex';
        resetModalMaximize(modal);
        editingIndex = -1;
        if (saveBtn) saveBtn.textContent = 'Save';
        if (nameInput) nameInput.focus();
    }

    function closeModal() {
        if (!modal) return;
        modal.style.display = 'none';
        resetModalMaximize(modal);
        if (nameInput) nameInput.value = '';
        if (otherCommitteeInput) otherCommitteeInput.value = '';
        if (otherCommitteeField) otherCommitteeField.style.display = 'none';
        if (headInput) headInput.value = '';
        if (descInput) descInput.value = '';
    }

    function openSuccessModal(message) {
        if (!successModal) return;
        if (successMessage) {
            successMessage.textContent = message || 'Add successful.';
        }
        successModal.style.display = 'flex';
    }

    function closeSuccessModal() {
        if (!successModal) return;
        successModal.style.display = 'none';
    }

    if (addBtn) {
        addBtn.addEventListener('click', openModal);
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
            const index = Number(target.getAttribute('data-index'));
            if (Number.isNaN(index) || !committees[index]) return;
            const committee = committees[index];

            if (action === 'view') {
                const viewName = document.getElementById('viewCommitteeName');
                const viewHead = document.getElementById('viewCommitteeHead');
                const viewStatus = document.getElementById('viewCommitteeStatus');
                const viewStatusText = document.getElementById('viewCommitteeStatusText');
                const viewDate = document.getElementById('viewCommitteeDateAssigned');
                const viewDateCreated = document.getElementById('viewCommitteeDateCreated');
                const viewDesc = document.getElementById('viewCommitteeDescription');
                const viewResp = document.getElementById('viewCommitteeResponsibilities');

                if (viewName) viewName.textContent = committee.name || '—';
                if (viewHead) viewHead.textContent = committee.head || '—';
                if (viewStatus) viewStatus.textContent = committee.status || 'Active';
                if (viewStatusText) viewStatusText.textContent = committee.status || 'Active';
                if (viewDate) viewDate.textContent = committee.dateCreated || '—';
                if (viewDateCreated) viewDateCreated.textContent = committee.dateCreated ? 'Assigned ' + committee.dateCreated : '';
                if (viewDesc) viewDesc.textContent = committee.description || committee.purpose || '—';
                if (viewResp) viewResp.textContent = committee.responsibilities || '—';

                if (viewModal) {
                    resetModalMaximize(viewModal);
                    viewModal.style.display = 'flex';
                }
                return;
            }

            editingIndex = index;
            if (nameInput) nameInput.value = committee.name;
            if (headInput) headInput.value = committee.head;
            if (saveBtn) saveBtn.textContent = 'Update';
            populateDropdowns(); // Refresh dropdown when editing
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
        saveBtn.addEventListener('click', () => {
            let name = (nameInput?.value || '').trim();
            const otherCommittee = (otherCommitteeInput?.value || '').trim();
            const head = (headInput?.value || '').trim();
            const desc = (descInput?.value || '').trim();

            // Handle Other committee option
            if (name === 'Other' && otherCommittee) {
                name = otherCommittee;
            } else if (name === 'Other') {
                alert('Please specify committee name.');
                return;
            }

            if (!name || !head) {
                alert('Please select a committee and assign it to someone.');
                return;
            }

            // Check if the committee head is already assigned to another committee
            const isDuplicateHead = committees.some((c, index) =>
                c.head === head && index !== editingIndex
            );

            if (isDuplicateHead) {
                alert(`${head} is already assigned to a committee. Please select someone else.`);
                return;
            }

            // Check if the committee name is already assigned to another committee
            const isDuplicateCommittee = committees.some((c, index) =>
                c.name === name && index !== editingIndex
            );

            if (isDuplicateCommittee) {
                alert(`${name} is already assigned. Please select a different committee.`);
                return;
            }

            saveBtn.disabled = true;
            saveBtn.textContent = 'Saving...';

            // Simulated AJAX
            setTimeout(() => {
                const payload = {
                    name,
                    head,
                    description: desc || 'To serve the youth and community through dedicated programs and initiatives.',
                    // Additional fields for completeness
                    purpose: desc || 'To serve the youth and community through dedicated programs and initiatives.',
                    responsibilities: 'Organize activities, coordinate with members, and report progress to SK council.',
                    dateCreated: new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }),
                    status: 'Active' // Default status
                };
                if (editingIndex >= 0 && committees[editingIndex]) {
                    // Preserve existing description fields when editing
                    committees[editingIndex] = {
                        ...committees[editingIndex],
                        ...payload
                    };
                } else {
                    committees.push(payload);
                }

                closeModal();
                render();
                populateDropdowns(); // Refresh dropdowns after saving
                saveBtn.disabled = false;
                saveBtn.textContent = 'Save';
                openSuccessModal(editingIndex >= 0 ? 'Update successful.' : 'Assignment successful.');
                editingIndex = -1;
            }, 500);
        });

    }

    if (successModal) {
        successModal.addEventListener('click', (e) => {
            if (e.target === successModal || e.target.hasAttribute('data-success-close')) {
                closeSuccessModal();
            }
        });
    }

    if (successModal) {
        successModal.addEventListener('click', (e) => {
            if (e.target === successModal || e.target.hasAttribute('data-success-close')) {
                closeSuccessModal();
            }
        });
    }

    populateDropdowns();
    render();
    wireModalToggle(modal);
    wireModalToggle(viewModal);
}
