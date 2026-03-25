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
    const membersChecklist = document.getElementById('committeeMembersChecklist');
    const descInput = document.getElementById('committeeDescriptionInput');
    const statusInput = document.getElementById('committeeStatusInput');
    const saveBtn = document.getElementById('committeeSaveBtn');
    const successModal = document.getElementById('committeeSuccessModal');
    const successMessage = document.getElementById('committeeSuccessMessage');
    const viewModal = document.getElementById('committeeViewModal');
    const viewCommitteeName = document.getElementById('viewCommitteeName');
    const viewCommitteeHead = document.getElementById('viewCommitteeHead');
    const viewCommitteeMembers = document.getElementById('viewCommitteeMembers');
    const viewCommitteeDescription = document.getElementById('viewCommitteeDescription');

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

    function populateHeadDropdowns() {
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
                const option = document.createElement('option');
                option.value = name;
                option.textContent = name;
                headInput.appendChild(option);
            });
        }
    }

    function populateMembersDropdown(selectedHead) {
        if (!membersChecklist) return;
        membersChecklist.innerHTML = '';
        officialMembers
            .filter((member) => !selectedHead || member !== selectedHead)
            .forEach((member) => {
                const label = document.createElement('label');
                label.className = 'member-checkbox-item';

                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.value = member;

                const text = document.createElement('span');
                text.textContent = member;

                label.appendChild(checkbox);
                label.appendChild(text);
                membersChecklist.appendChild(label);
            });
    }

    function render() {
        grid.innerHTML = '';

        const filtered = committees.filter((c) => {
            const matchesSearch =
                !currentQuery ||
                c.name.toLowerCase().includes(currentQuery) ||
                c.description.toLowerCase().includes(currentQuery) ||
                c.members.some((m) => m.toLowerCase().includes(currentQuery));

            const matchesHead =
                !currentHeadFilter ||
                c.head.toLowerCase() === currentHeadFilter.toLowerCase();

            return matchesSearch && matchesHead;
        });

        if (filtered.length === 0) {
            const empty = document.createElement('tr');
            empty.innerHTML = '<td colspan="5" class="empty-state">No committees added yet. Click "+ Add Committee".</td>';
            grid.appendChild(empty);
            return;
        }

        filtered.forEach((c) => {
            const sourceIndex = committees.indexOf(c);
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${c.name}</td>
                <td>${c.head}</td>
                <td>${c.members.join(', ')}</td>
                <td>${c.description}</td>
                <td>
                    <div class="committee-actions">
                        <button type="button" class="btn-outline" data-action="view" data-index="${sourceIndex}">View</button>
                        <button type="button" class="btn-outline edit-btn" data-action="edit" data-index="${sourceIndex}">Edit</button>
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
        if (statusInput) statusInput.value = 'active';
        populateMembersDropdown('');
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

    if (headInput) {
        headInput.addEventListener('change', () => {
            populateMembersDropdown(headInput.value);
        });
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
                if (viewCommitteeName) viewCommitteeName.value = committee.name;
                if (viewCommitteeHead) viewCommitteeHead.value = committee.head;
                if (viewCommitteeMembers) viewCommitteeMembers.value = committee.members.join('\n');
                if (viewCommitteeDescription) viewCommitteeDescription.value = committee.description;
                if (viewModal) {
                    resetModalMaximize(viewModal);
                    viewModal.style.display = 'flex';
                }
                return;
            }

            editingIndex = index;
            if (nameInput) nameInput.value = committee.name;
            if (headInput) headInput.value = committee.head;
            populateMembersDropdown(committee.head);
            if (membersChecklist) {
                Array.from(membersChecklist.querySelectorAll('input[type="checkbox"]')).forEach((cb) => {
                    cb.checked = committee.members.includes(cb.value);
                });
            }
            if (descInput) descInput.value = committee.description;
            if (saveBtn) saveBtn.textContent = 'Update';
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
            const members = membersChecklist
                ? Array.from(membersChecklist.querySelectorAll('input[type="checkbox"]:checked')).map((cb) => cb.value.trim()).filter(Boolean)
                : [];
            const desc = (descInput?.value || '').trim();
            const status = (statusInput?.value || 'active').trim();

            // Handle Other committee option
            if (name === 'Other' && otherCommittee) {
                name = otherCommittee;
            } else if (name === 'Other') {
                alert('Please specify committee name.');
                return;
            }

            if (!name || !head) {
                alert('Please fill in committee name and head. UI only.');
                return;
            }

            saveBtn.disabled = true;
            saveBtn.textContent = 'Saving...';

            // Simulated AJAX
            setTimeout(() => {
                const payload = {
                    name,
                    description: desc || 'No description yet.',
                    headRole: 'SK Official',
                    head,
                    members,
                    scope: 'Custom',
                };
                if (editingIndex >= 0 && committees[editingIndex]) {
                    committees[editingIndex] = payload;
                } else {
                    committees.push(payload);
                }

                closeModal();
                render();
                saveBtn.disabled = false;
                saveBtn.textContent = 'Save';
                openSuccessModal(editingIndex >= 0 ? 'Update successful.' : 'Add successful.');
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

    populateHeadDropdowns();
    populateMembersDropdown('');
    render();

    wireModalToggle(modal);
    wireModalToggle(viewModal);
}

