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
    const membersInput = document.getElementById('committeeMembersInput');
    const descInput = document.getElementById('committeeDescriptionInput');
    const statusInput = document.getElementById('committeeStatusInput');
    const saveBtn = document.getElementById('committeeSaveBtn');
    const successModal = document.getElementById('committeeSuccessModal');
    const successMessage = document.getElementById('committeeSuccessMessage');

    if (!grid) return;

    // Start with empty list; entries appear only after "Add Committee"
    const committees = [];

    let currentQuery = '';
    let currentHeadFilter = '';

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
                (currentHeadFilter === 'chairman' && c.headRole.toLowerCase().includes('chairman')) ||
                (currentHeadFilter === 'kagawad' && c.headRole.toLowerCase().includes('kagawad'));

            return matchesSearch && matchesHead;
        });

        if (filtered.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'empty-state';
            empty.innerHTML =
                'No committees added yet. <br><strong>Tip:</strong> click "+ Add Committee" to register your first committee.';
            grid.appendChild(empty);
            return;
        }

        filtered.forEach((c) => {
            const card = document.createElement('article');
            card.className = 'committee-card';

            card.innerHTML = `
                <header class="committee-header">
                    <div>
                        <h3 class="committee-name">${c.name}</h3>
                        <p class="committee-description">${c.description}</p>
                    </div>
                    <span class="committee-tag">${c.scope} Committee</span>
                </header>
                <div class="committee-meta">
                    <span class="committee-meta-item">
                        <span class="committee-meta-label">Head:</span> ${c.head} (${c.headRole})
                    </span>
                    <span class="committee-meta-item">
                        <span class="committee-meta-label">Members:</span> ${c.members.length}
                    </span>
                </div>
                <div class="committee-members">
                    ${c.members
                    .map((m) => `<span class="member-pill">${m}</span>`)
                    .join('')}
                </div>
                <footer class="committee-footer">
                    <span class="committee-hierarchy">
                        Committee → Program → Event
                    </span>
                    <div class="committee-actions">
                        <button type="button" class="btn-outline" data-action="view">
                            View
                        </button>
                        <span class="badge-pill">
                            <span class="badge-dot"></span>
                            Active
                        </span>
                    </div>
                </footer>
            `;

            grid.appendChild(card);
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
        if (nameInput) nameInput.focus();
    }

    function closeModal() {
        if (!modal) return;
        modal.style.display = 'none';
        if (nameInput) nameInput.value = '';
        if (otherCommitteeInput) otherCommitteeInput.value = '';
        if (otherCommitteeField) otherCommitteeField.style.display = 'none';
        if (headInput) headInput.value = '';
        if (membersInput) membersInput.value = '';
        if (descInput) descInput.value = '';
        if (statusInput) statusInput.value = 'active';
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

    if (saveBtn) {
        saveBtn.addEventListener('click', () => {
            let name = (nameInput?.value || '').trim();
            const otherCommittee = (otherCommitteeInput?.value || '').trim();
            const head = (headInput?.value || '').trim();
            const membersRaw = (membersInput?.value || '').trim();
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
                const members = membersRaw
                    ? membersRaw.split(',').map((m) => m.trim()).filter(Boolean)
                    : [];
                committees.push({
                    name,
                    description: desc || 'No description yet.',
                    headRole: 'SK Official',
                    head,
                    members,
                    scope: 'Custom',
                });

                closeModal();
                render();
                saveBtn.disabled = false;
                saveBtn.textContent = 'Save';
                openSuccessModal('Add successful.');
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

    render();
}

