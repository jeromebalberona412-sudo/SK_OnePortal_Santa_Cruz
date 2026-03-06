document.addEventListener('DOMContentLoaded', () => {
    initializeAnnouncementsUI();
});

function showAnnouncementToast(message, type) {
    const existing = document.querySelector('.app-toast');
    if (existing) existing.remove();
    const toast = document.createElement('div');
    toast.className = 'app-toast app-toast-show app-toast-' + (type || 'success');
    toast.innerHTML = '<span class="app-toast-icon">✓</span> ' + message;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.classList.remove('app-toast-show');
        toast.classList.add('app-toast-hide');
        setTimeout(() => toast.remove(), 300);
    }, 2500);
}

function showAnnouncementConfirm(options) {
    return new Promise((resolve) => {
        const { title, message, confirmText = 'OK', cancelText = 'Cancel', theme = 'default' } = options;
        const isAlert = cancelText === '';
        let overlay = document.getElementById('announcement-confirm-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'announcement-confirm-overlay';
            overlay.className = 'announcement-confirm-overlay';
            overlay.innerHTML = `
                <div class="announcement-confirm-modal">
                    <div class="announcement-confirm-header"></div>
                    <div class="announcement-confirm-body"></div>
                    <div class="announcement-confirm-actions">
                        <button type="button" class="announcement-confirm-cancel"></button>
                        <button type="button" class="announcement-confirm-ok"></button>
                    </div>
                </div>
            `;
            document.body.appendChild(overlay);
        }
        const headerEl = overlay.querySelector('.announcement-confirm-header');
        const bodyEl = overlay.querySelector('.announcement-confirm-body');
        const cancelBtn = overlay.querySelector('.announcement-confirm-cancel');
        const okBtn = overlay.querySelector('.announcement-confirm-ok');

        headerEl.textContent = title;
        bodyEl.textContent = message;
        cancelBtn.textContent = cancelText;
        cancelBtn.style.display = isAlert ? 'none' : '';
        okBtn.textContent = confirmText;
        overlay.className = 'announcement-confirm-overlay theme-' + (options.theme || 'default');

        const cleanup = () => {
            overlay.classList.remove('show');
            cancelBtn.onclick = null;
            okBtn.onclick = null;
            overlay.onclick = null;
        };

        cancelBtn.onclick = () => { cleanup(); resolve(false); };
        okBtn.onclick = () => { cleanup(); resolve(true); };
        overlay.onclick = (e) => {
            if (e.target === overlay) {
                cleanup();
                resolve(isAlert ? true : false);
            }
        };

        overlay.classList.add('show');
    });
}

function initializeAnnouncementsUI() {
    const list = document.getElementById('announcementList');
    const addBtn = document.getElementById('announcementAddBtn');

    if (!list || !addBtn) return;

    const announcements = [];

    function renderList() {
        list.innerHTML = '';
        if (announcements.length === 0) {
            const empty = document.createElement('p');
            empty.className = 'announcement-empty';
            empty.textContent = 'No announcements yet. Click "Add Announcement" to create one.';
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
            meta.textContent = item.createdAt || '';
            header.appendChild(title);
            header.appendChild(meta);
            const body = document.createElement('div');
            body.className = 'announcement-card-body';
            body.textContent = item.content;
            const actions = document.createElement('div');
            actions.className = 'announcement-actions';
            const viewBtn = document.createElement('button');
            viewBtn.type = 'button';
            viewBtn.className = 'announcement-btn-view';
            viewBtn.textContent = 'View';
            viewBtn.dataset.action = 'view';
            viewBtn.dataset.index = index;
            const editBtn = document.createElement('button');
            editBtn.type = 'button';
            editBtn.className = 'announcement-btn-edit';
            editBtn.textContent = 'Edit';
            editBtn.dataset.action = 'edit';
            editBtn.dataset.index = index;
            const deleteBtn = document.createElement('button');
            deleteBtn.type = 'button';
            deleteBtn.className = 'announcement-btn-delete';
            deleteBtn.textContent = 'Delete';
            deleteBtn.dataset.action = 'delete';
            deleteBtn.dataset.index = index;
            actions.appendChild(viewBtn);
            actions.appendChild(editBtn);
            actions.appendChild(deleteBtn);
            card.appendChild(header);
            card.appendChild(body);
            card.appendChild(actions);
            list.appendChild(card);
        });
    }

    addBtn.addEventListener('click', () => openAddModal());

    list.addEventListener('click', async (e) => {
        const btn = e.target.closest('button[data-action]');
        if (!btn) return;
        const index = parseInt(btn.dataset.index, 10);
        if (Number.isNaN(index) || !announcements[index]) return;
        const action = btn.dataset.action;
        if (action === 'view') openViewModal(announcements[index]);
        else if (action === 'edit') openEditModal(index);
        else if (action === 'delete') {
            const ok = await showAnnouncementConfirm({
                title: 'Delete Announcement',
                message: 'Are you sure you want to delete this announcement?',
                confirmText: 'Yes',
                cancelText: 'No',
                theme: 'delete'
            });
            if (ok) {
                announcements.splice(index, 1);
                renderList();
                showAnnouncementToast('Delete successful');
            }
        }
    });

    function openAddModal() {
        let backdrop = document.querySelector('.announcement-modal-backdrop');
        if (!backdrop) {
            backdrop = document.createElement('div');
            backdrop.className = 'announcement-modal-backdrop';
            backdrop.innerHTML = `
                <div class="announcement-modal">
                    <div class="announcement-modal-header">
                        <h2 class="announcement-modal-title">Add Announcement</h2>
                        <div class="announcement-modal-window-controls">
                            <button type="button" class="announcement-modal-toggle" aria-label="Maximize">□</button>
                            <button type="button" class="announcement-modal-close" aria-label="Close">&times;</button>
                        </div>
                    </div>
                    <div class="announcement-modal-body">
                        <label>Title</label>
                        <input type="text" class="announcement-modal-title-input" placeholder="Enter title" />
                        <label>Content</label>
                        <textarea class="announcement-modal-content-input" placeholder="Write content..."></textarea>
                        <div class="announcement-modal-actions">
                            <button type="button" class="announcement-modal-post btn-primary">Post</button>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(backdrop);
        }

        const modal = backdrop.querySelector('.announcement-modal');
        const titleInput = modal.querySelector('.announcement-modal-title-input');
        const contentInput = modal.querySelector('.announcement-modal-content-input');
        const postBtn = modal.querySelector('.announcement-modal-post');
        const closeBtn = modal.querySelector('.announcement-modal-close');
        const toggleBtn = modal.querySelector('.announcement-modal-toggle');

        modal.querySelector('.announcement-modal-title').textContent = 'Add Announcement';
        titleInput.value = '';
        contentInput.value = '';

        function hide() {
            backdrop.classList.remove('show', 'modal-maximized');
            modal.classList.remove('modal-maximized');
            closeBtn.removeEventListener('click', onClose);
            postBtn.removeEventListener('click', onPost);
            toggleBtn.removeEventListener('click', onToggle);
            backdrop.removeEventListener('click', onBackdrop);
        }

        async function onPost() {
            const title = titleInput.value.trim();
            const content = contentInput.value.trim();
            if (!title || !content) return;
            const ok = await showAnnouncementConfirm({
                title: 'Post Announcement',
                message: 'Are you sure you want to post this announcement?',
                confirmText: 'Yes',
                cancelText: 'No',
                theme: 'post'
            });
            if (!ok) return;
            const now = new Date();
            announcements.unshift({
                title,
                content,
                createdAt: now.toLocaleString(undefined, { year: 'numeric', month: 'short', day: '2-digit', hour: '2-digit', minute: '2-digit' })
            });
            hide();
            renderList();
            showAnnouncementToast('Post successful');
        }

        function onClose() { hide(); }
        function onBackdrop(e) { if (e.target === backdrop) hide(); }
        function onToggle() {
            const isMax = backdrop.classList.toggle('modal-maximized');
            modal.classList.toggle('modal-maximized', isMax);
            toggleBtn.textContent = isMax ? '⧉' : '□';
        }

        closeBtn.addEventListener('click', onClose);
        postBtn.addEventListener('click', onPost);
        toggleBtn.addEventListener('click', onToggle);
        backdrop.addEventListener('click', onBackdrop);

        backdrop.classList.add('show');
        titleInput.focus();
    }

    function openViewModal(item) {
        let backdrop = document.querySelector('.announcement-view-backdrop');
        if (!backdrop) {
            backdrop = document.createElement('div');
            backdrop.className = 'announcement-modal-backdrop announcement-view-backdrop';
            backdrop.innerHTML = `
                <div class="announcement-modal">
                    <div class="announcement-modal-header">
                        <h2 class="announcement-modal-title">View Announcement</h2>
                        <div class="announcement-modal-window-controls">
                            <button type="button" class="announcement-modal-toggle" aria-label="Maximize">□</button>
                            <button type="button" class="announcement-modal-close" aria-label="Close">&times;</button>
                        </div>
                    </div>
                    <div class="announcement-modal-body">
                        <label>Title</label>
                        <div class="announcement-view-title announcement-view-field"></div>
                        <label>Content</label>
                        <div class="announcement-view-content announcement-view-field"></div>
                    </div>
                </div>
            `;
            document.body.appendChild(backdrop);
        }

        const modal = backdrop.querySelector('.announcement-modal');
        modal.querySelector('.announcement-view-title').textContent = item.title;
        modal.querySelector('.announcement-view-content').textContent = item.content;
        const closeBtn = modal.querySelector('.announcement-modal-close');
        const toggleBtn = modal.querySelector('.announcement-modal-toggle');

        function hide() {
            backdrop.classList.remove('show', 'modal-maximized');
            modal.classList.remove('modal-maximized');
            closeBtn.removeEventListener('click', onClose);
            toggleBtn.removeEventListener('click', onToggle);
            backdrop.removeEventListener('click', onBackdrop);
        }

        function onClose() { hide(); }
        function onBackdrop(e) { if (e.target === backdrop) hide(); }
        function onToggle() {
            const isMax = backdrop.classList.toggle('modal-maximized');
            modal.classList.toggle('modal-maximized', isMax);
            toggleBtn.textContent = isMax ? '⧉' : '□';
        }

        closeBtn.addEventListener('click', onClose);
        toggleBtn.addEventListener('click', onToggle);
        backdrop.addEventListener('click', onBackdrop);

        backdrop.classList.add('show');
    }

    function openEditModal(index) {
        const item = announcements[index];
        let backdrop = document.querySelector('.announcement-edit-backdrop');
        if (!backdrop) {
            backdrop = document.createElement('div');
            backdrop.className = 'announcement-modal-backdrop announcement-edit-backdrop';
            backdrop.innerHTML = `
                <div class="announcement-modal">
                    <div class="announcement-modal-header">
                        <h2 class="announcement-modal-title">Edit Announcement</h2>
                        <div class="announcement-modal-window-controls">
                            <button type="button" class="announcement-modal-toggle" aria-label="Maximize">□</button>
                            <button type="button" class="announcement-modal-close" aria-label="Close">&times;</button>
                        </div>
                    </div>
                    <div class="announcement-modal-body">
                        <label>Title</label>
                        <input type="text" class="announcement-edit-title" />
                        <label>Content</label>
                        <textarea class="announcement-edit-content"></textarea>
                        <div class="announcement-modal-actions">
                            <button type="button" class="announcement-edit-cancel btn-secondary">Cancel</button>
                            <button type="button" class="announcement-edit-save btn-primary">Save</button>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(backdrop);
        }

        const modal = backdrop.querySelector('.announcement-modal');
        const titleInput = modal.querySelector('.announcement-edit-title');
        const contentInput = modal.querySelector('.announcement-edit-content');
        const cancelBtn = modal.querySelector('.announcement-edit-cancel');
        const saveBtn = modal.querySelector('.announcement-edit-save');
        const closeBtn = modal.querySelector('.announcement-modal-close');
        const toggleBtn = modal.querySelector('.announcement-modal-toggle');

        titleInput.value = item.title;
        contentInput.value = item.content;

        function hide() {
            backdrop.classList.remove('show', 'modal-maximized');
            modal.classList.remove('modal-maximized');
            closeBtn.removeEventListener('click', onClose);
            cancelBtn.removeEventListener('click', onCancel);
            saveBtn.removeEventListener('click', onSave);
            toggleBtn.removeEventListener('click', onToggle);
            backdrop.removeEventListener('click', onBackdrop);
        }

        async function onSave() {
            const title = titleInput.value.trim();
            const content = contentInput.value.trim();
            const ok = await showAnnouncementConfirm({
                title: 'Save Changes',
                message: 'Are you sure you want to save changes?',
                confirmText: 'Yes',
                cancelText: 'No',
                theme: 'edit'
            });
            if (!ok) return;
            announcements[index] = {
                title,
                content,
                createdAt: item.createdAt
            };
            hide();
            renderList();
            showAnnouncementToast('Save changes successful');
        }

        function onCancel() { hide(); }
        function onClose() { hide(); }
        function onBackdrop(e) { if (e.target === backdrop) hide(); }
        function onToggle() {
            const isMax = backdrop.classList.toggle('modal-maximized');
            modal.classList.toggle('modal-maximized', isMax);
            toggleBtn.textContent = isMax ? '⧉' : '□';
        }

        closeBtn.addEventListener('click', onClose);
        cancelBtn.addEventListener('click', onCancel);
        saveBtn.addEventListener('click', onSave);
        toggleBtn.addEventListener('click', onToggle);
        backdrop.addEventListener('click', onBackdrop);

        backdrop.classList.add('show');
        titleInput.focus();
    }

    renderList();
}
