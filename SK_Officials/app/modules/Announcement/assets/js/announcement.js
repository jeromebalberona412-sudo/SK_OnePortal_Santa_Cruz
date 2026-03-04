document.addEventListener('DOMContentLoaded', () => {
    initializeAnnouncementsUI();
});

function initializeAnnouncementsUI() {
    const form = document.getElementById('announcementForm');
    const list = document.getElementById('announcementList');

    if (!form || !list) return;

    const announcements = [];
    let isFormActive = false;
    let editingIndex = null;

    const primaryBtn = document.getElementById('announcementPrimaryBtn');
    const cancelBtn = document.getElementById('announcementCancelBtn');
    const titleInput = form.querySelector('#announcementTitle');
    const contentInput = form.querySelector('#announcementContent');
    const titleError = form.querySelector('.error-message[data-for="title"]');
    const contentError = form.querySelector('.error-message[data-for="content"]');

    function setFormActive(active) {
        isFormActive = active;

        titleInput.disabled = !active;
        contentInput.disabled = !active;

        if (active) {
            primaryBtn.textContent = editingIndex !== null ? 'Post Announcement' : 'Post Announcement';
            cancelBtn.classList.remove('is-hidden');
        } else {
            primaryBtn.textContent = 'Add Announcement';
            cancelBtn.classList.add('is-hidden');
            titleError.textContent = '';
            titleError.style.display = 'none';
            contentError.textContent = '';
            contentError.style.display = 'none';
        }
    }

    function renderList() {
        list.innerHTML = '';

        if (announcements.length === 0) {
            const empty = document.createElement('p');
            empty.className = 'announcement-empty';
            empty.textContent = 'No announcements yet. Use the form above to create one.';
            list.appendChild(empty);
            return;
        }

        announcements.forEach((item, index) => {
            const card = document.createElement('article');
            card.className = 'announcement-card';

            const header = document.createElement('div');
            header.className = 'announcement-card-header';

            const title = document.createElement('h3');
            title.className = 'announcement-card-title';
            title.textContent = item.title;

            const meta = document.createElement('span');
            meta.className = 'announcement-card-meta';
            meta.textContent = item.createdAt;

            header.appendChild(title);
            header.appendChild(meta);

            const body = document.createElement('div');
            body.className = 'announcement-card-body';
            body.textContent = item.content;

            card.appendChild(header);
            card.appendChild(body);

            const actions = document.createElement('div');
            actions.className = 'announcement-actions';

            const viewBtn = document.createElement('button');
            viewBtn.type = 'button';
            viewBtn.textContent = 'View';
            viewBtn.dataset.action = 'view';
            viewBtn.dataset.index = index;

            const editBtn = document.createElement('button');
            editBtn.type = 'button';
            editBtn.textContent = 'Edit';
            editBtn.dataset.action = 'edit';
            editBtn.dataset.index = index;

            const deleteBtn = document.createElement('button');
            deleteBtn.type = 'button';
            deleteBtn.textContent = 'Delete';
            deleteBtn.dataset.action = 'delete';
            deleteBtn.dataset.index = index;

            actions.appendChild(viewBtn);
            actions.appendChild(editBtn);
            actions.appendChild(deleteBtn);

            card.appendChild(actions);

            list.appendChild(card);
        });
    }

    function validate() {
        let valid = true;

        if (!titleInput.value.trim()) {
            titleError.textContent = 'Title is required.';
            titleError.style.display = 'block';
            valid = false;
        } else {
            titleError.textContent = '';
            titleError.style.display = 'none';
        }

        if (!contentInput.value.trim()) {
            contentError.textContent = 'Content is required.';
            contentError.style.display = 'block';
            valid = false;
        } else {
            contentError.textContent = '';
            contentError.style.display = 'none';
        }

        return valid;
    }

    if (primaryBtn) {
        primaryBtn.addEventListener('click', e => {
            if (!isFormActive) {
                e.preventDefault();
                setFormActive(true);
                titleInput.focus();
            }
        });
    }

    if (cancelBtn) {
        cancelBtn.addEventListener('click', () => {
            form.reset();
            editingIndex = null;
            setFormActive(false);
        });
    }

    form.addEventListener('submit', e => {
        e.preventDefault();

        if (!isFormActive) {
            return;
        }

        if (!validate()) {
            return;
        }

        const title = form.title.value.trim();
        const content = form.content.value.trim();

        const now = new Date();
        const createdAt = now.toLocaleString(undefined, {
            year: 'numeric',
            month: 'short',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
        });

        const record = { title, content, createdAt };

        if (editingIndex !== null) {
            announcements[editingIndex] = record;
        } else {
            announcements.unshift(record);
        }

        form.reset();
        editingIndex = null;
        setFormActive(false);
        renderList();
    });

    form.addEventListener('input', () => {
        if (isFormActive) {
            validate();
        }
    });

    list.addEventListener('click', e => {
        const btn = e.target.closest('button[data-action]');
        if (!btn) return;

        const index = parseInt(btn.dataset.index, 10);
        if (Number.isNaN(index) || !announcements[index]) return;

        const action = btn.dataset.action;

        if (action === 'view') {
            openViewModal(announcements[index]);
        } else if (action === 'edit') {
            editingIndex = index;
            setFormActive(true);
            form.title.value = announcements[index].title;
            form.content.value = announcements[index].content;
            titleInput.focus();
            window.scrollTo({ top: form.getBoundingClientRect().top + window.scrollY - 100, behavior: 'smooth' });
        } else if (action === 'delete') {
            if (window.confirm('Delete this announcement?')) {
                announcements.splice(index, 1);
                renderList();
            }
        }
    });

    function openViewModal(item) {
        let backdrop = document.querySelector('.announcement-modal-backdrop');
        if (!backdrop) {
            backdrop = document.createElement('div');
            backdrop.className = 'announcement-modal-backdrop';
            backdrop.innerHTML = `
                <div class="announcement-modal">
                    <div class="announcement-modal-header">
                        <h2 class="announcement-modal-title"></h2>
                        <button type="button" class="announcement-modal-close">&times;</button>
                    </div>
                    <div class="announcement-modal-body"></div>
                </div>
            `;
            document.body.appendChild(backdrop);
        }

        const titleEl = backdrop.querySelector('.announcement-modal-title');
        const bodyEl = backdrop.querySelector('.announcement-modal-body');
        const closeBtn = backdrop.querySelector('.announcement-modal-close');

        titleEl.textContent = item.title;
        bodyEl.textContent = item.content;

        function hide() {
            backdrop.classList.remove('show');
            closeBtn.removeEventListener('click', onClose);
            backdrop.removeEventListener('click', onBackdrop);
        }

        function onClose() {
            hide();
        }

        function onBackdrop(e) {
            if (e.target === backdrop) {
                hide();
            }
        }

        closeBtn.addEventListener('click', onClose);
        backdrop.addEventListener('click', onBackdrop);

        backdrop.classList.add('show');
    }

    renderList();
}

