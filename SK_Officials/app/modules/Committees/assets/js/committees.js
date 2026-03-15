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
    const headInput = document.getElementById('committeeHeadInput');
    const membersInput = document.getElementById('committeeMembersInput');
    const descInput = document.getElementById('committeeDescriptionInput');
    const saveBtn = document.getElementById('committeeSaveBtn');

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
        if (headInput) headInput.value = '';
        if (membersInput) membersInput.value = '';
        if (descInput) descInput.value = '';
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
            const name = (nameInput?.value || '').trim();
            const head = (headInput?.value || '').trim();
            const membersRaw = (membersInput?.value || '').trim();
            const description = (descInput?.value || '').trim();

            if (!name || !head) {
                alert('Please provide at least a Committee Name and Head (UI only).');
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
                    description: description || 'No description yet.',
                    headRole: 'SK Official',
                    head,
                    members,
                    scope: 'Custom',
                });

                closeModal();
                render();
                saveBtn.disabled = false;
                saveBtn.textContent = 'Save';

                alert('Committee successfully added (UI only, no backend yet).');
            }, 500);
        });
    }

    render();
}

