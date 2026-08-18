const cfg = window.ArchiveConfig || {};
let currentPage = 1;
let lastPage = 1;
let totalRecords = 0;
let recordsPerPage = 10;
let tablePagination = null;
let searchTimer = null;
let pendingActionId = null;
let archivePosts = [];
let lightboxImages = [];
let lightboxIndex = 0;
let viewingPostId = null;

function apiFetch(url, options = {}) {
    const headers = {
        Accept: 'application/json',
        'X-CSRF-TOKEN': cfg.csrf,
        ...(options.headers || {}),
    };

    return fetch(url, { ...options, headers, credentials: 'same-origin' }).then(async (res) => {
        const data = await res.json().catch(() => ({}));
        if (!res.ok) {
            throw new Error(data.message || 'Request failed.');
        }
        return data;
    });
}

function escapeHtml(str) {
    return String(str ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function tierClass(tier) {
    if (tier === 'green') return 'days-green';
    if (tier === 'orange') return 'days-orange';
    return 'days-red';
}

function formatDate(iso) {
    if (!iso) return '—';
    const date = new Date(iso);
    if (Number.isNaN(date.getTime())) return '—';
    return date.toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' });
}

function formatTime(iso) {
    if (!iso) return '';
    const date = new Date(iso);
    if (Number.isNaN(date.getTime())) return '';
    return date.toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit' });
}

function formatDateTime(iso) {
    const date = formatDate(iso);
    const time = formatTime(iso);
    if (date === '—') return '—';
    return time ? `${date} · ${time}` : date;
}

function renderImageThumbs(images, postId) {
    const list = images || [];
    if (!list.length) {
        return '<span class="archive-no-image">No images</span>';
    }

    const visible = list.slice(0, 3);
    const extra = list.length > 3 ? `<span class="archive-thumb-more">+${list.length - 3}</span>` : '';

    return `
        <div class="archive-thumb-grid" data-post-id="${postId}">
            ${visible.map((url, index) => `
                <button type="button" class="archive-thumb-btn" data-image-index="${index}" title="View image">
                    <img src="${escapeHtml(url)}" alt="Post image ${index + 1}">
                </button>
            `).join('')}
            ${extra}
        </div>
    `;
}

function getPostImages(postId) {
    const post = findPost(postId);
    if (post?.images?.length) return post.images;
    if (post?.thumbnail_url) return [post.thumbnail_url];
    return [];
}

function renderActionMenuCell(postId) {
    const ellipsis = window.ROW_ACTIONS_ELLIPSIS || '⋯';
    return `
        <td class="col-actions">
            <div class="row-actions-menu">
                <button type="button" class="row-actions-trigger" aria-label="Actions" aria-haspopup="true" aria-expanded="false">${ellipsis}</button>
                <div class="row-actions-dropdown" role="menu">
                    <button type="button" class="row-actions-item row-actions-item-view" data-action="view" data-id="${postId}" role="menuitem">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <span>View</span>
                    </button>
                    <button type="button" class="row-actions-item row-actions-item-restore" data-action="restore" data-id="${postId}" role="menuitem">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                        <span>Restore</span>
                    </button>
                </div>
            </div>
        </td>
    `;
}

function renderRow(post) {
    const days = Number(post.days_remaining ?? 0);
    if (days <= 0) {
        return '';
    }

    const daysClass = `archive-days-badge ${tierClass(post.days_tier)}`;

    return `
        <tr data-id="${post.id}">
            <td><span class="archive-type-badge">${escapeHtml(post.type_label)}</span></td>
            <td>${escapeHtml(post.author_name)}</td>
            <td>${escapeHtml(formatDate(post.created_at))}</td>
            <td>${escapeHtml(formatTime(post.created_at)) || '—'}</td>
            <td>${escapeHtml(formatDate(post.archived_at))}</td>
            <td>${escapeHtml(formatTime(post.archived_at)) || '—'}</td>
            <td>
                <span class="${daysClass}" title="${escapeHtml(post.auto_delete_label)}">${days} day${days === 1 ? '' : 's'}</span>
            </td>
            ${renderActionMenuCell(post.id)}
        </tr>
    `;
}

function renderTable(posts) {
    const tbody = document.getElementById('archiveTableBody');
    if (!tbody) return;

    archivePosts = posts || [];

    if (!posts.length) {
        tbody.innerHTML = '<tr class="empty-state-row"><td colspan="8">No archived posts found.</td></tr>';
        if (window.bindRowActionsTable) bindRowActionsTable(tbody);
        return;
    }

    tbody.innerHTML = posts.map(renderRow).join('');
    if (window.bindRowActionsTable) {
        bindRowActionsTable(tbody);
    }
}

function updatePagination() {
    if (tablePagination) {
        tablePagination.updateFooter();
        return;
    }

    const info = document.getElementById('archivePaginationInfo');
    const prev = document.getElementById('archivePrevBtn');
    const next = document.getElementById('archiveNextBtn');
    const pageInput = document.getElementById('archivePageInput');
    const totalPages = document.getElementById('archiveTotalPages');

    if (info) {
        info.textContent = `${totalRecords} record${totalRecords === 1 ? '' : 's'}`;
    }
    if (pageInput) pageInput.value = String(currentPage);
    if (totalPages) totalPages.textContent = String(Math.max(1, lastPage));
    if (prev) prev.disabled = currentPage <= 1;
    if (next) next.disabled = currentPage >= lastPage || totalRecords === 0;
}

async function loadArchive(page = 1) {
    currentPage = page;
    const search = document.getElementById('archiveSearch')?.value.trim() || '';
    const tbody = document.getElementById('archiveTableBody');

    if (tbody) {
        tbody.innerHTML = '<tr class="archive-loading-row"><td colspan="8">Loading archived posts…</td></tr>';
    }

    const params = new URLSearchParams({
        page: String(page),
        per_page: String(recordsPerPage),
    });
    if (search) params.set('search', search);

    try {
        const data = await apiFetch(`${cfg.dataUrl}?${params.toString()}`);
        lastPage = data.last_page || 1;
        totalRecords = data.total ?? 0;
        currentPage = data.current_page || page;

        if (currentPage > lastPage) {
            await loadArchive(lastPage);
            return;
        }

        renderTable(data.data || []);
        updatePagination();
    } catch (err) {
        if (tbody) {
            tbody.innerHTML = `<tr class="empty-state-row"><td colspan="8">${escapeHtml(err.message)}</td></tr>`;
        }
        totalRecords = 0;
        lastPage = 1;
        updatePagination();
    }
}

function findPost(id) {
    return archivePosts.find((post) => String(post.id) === String(id));
}

function renderViewModalContent(post) {
    const images = post.images || [];
    const imagesHtml = images.length
        ? `<div class="archive-view-gallery" data-post-id="${post.id}">
            ${images.map((url, index) => `
                <button type="button" class="archive-view-gallery-item" data-image-index="${index}">
                    <img src="${escapeHtml(url)}" alt="Image ${index + 1}">
                </button>
            `).join('')}
           </div>`
        : '<p class="archive-view-empty">No images attached to this post.</p>';

    const linkHtml = post.link_url
        ? `<a href="${escapeHtml(post.link_url)}" class="archive-view-link" target="_blank" rel="noopener">${escapeHtml(post.link_url)}</a>`
        : '—';

    return `
        <div class="archive-view-meta-grid">
            <div class="archive-view-meta-item"><span class="label">Type</span><span class="value">${escapeHtml(post.type_label)}</span></div>
            <div class="archive-view-meta-item"><span class="label">Posted By</span><span class="value">${escapeHtml(post.author_name)}</span></div>
            <div class="archive-view-meta-item"><span class="label">Barangay</span><span class="value">${escapeHtml(post.barangay_name || '—')}</span></div>
            <div class="archive-view-meta-item"><span class="label">Posted</span><span class="value">${escapeHtml(formatDateTime(post.created_at))}</span></div>
            <div class="archive-view-meta-item"><span class="label">Archived</span><span class="value">${escapeHtml(formatDateTime(post.archived_at))}</span></div>
            <div class="archive-view-meta-item"><span class="label">Days Left</span><span class="value">${escapeHtml(post.auto_delete_label)}</span></div>
            <div class="archive-view-meta-item archive-view-meta-item--wide"><span class="label">Link</span><span class="value">${linkHtml}</span></div>
        </div>
        ${post.title ? `
        <div class="archive-view-section">
            <h3>Title</h3>
            <p class="archive-view-text">${escapeHtml(post.title)}</p>
        </div>` : ''}
        <div class="archive-view-section">
            <h3>Content</h3>
            <p class="archive-view-text archive-view-text--body">${escapeHtml(post.body || post.body_preview || '—')}</p>
        </div>
        <div class="archive-view-section">
            <h3>Images (${images.length})</h3>
            ${imagesHtml}
        </div>
    `;
}

async function openViewModal(id) {
    viewingPostId = id;
    let post = findPost(id);

    const modal = document.getElementById('viewPostModal');
    const body = document.getElementById('archiveViewBody');
    const title = document.getElementById('archiveViewTitle');
    const subtitle = document.getElementById('archiveViewSubtitle');

    if (!modal || !body) return;

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    body.innerHTML = '<div class="archive-view-loading">Loading post details…</div>';

    try {
        if (!post || !post.body) {
            const response = await apiFetch(cfg.showUrl(id));
            post = response.data;
            const existing = findPost(id);
            if (existing) {
                Object.assign(existing, post);
            } else {
                archivePosts.push(post);
            }
        }

        if (title) title.textContent = post.title || 'Archived Post';
        if (subtitle) subtitle.textContent = `Archived ${post.archived_ago || ''}`.trim();
        const cached = findPost(post.id);
        if (cached) Object.assign(cached, post);
        else archivePosts.push(post);
        body.innerHTML = renderViewModalContent(post);
    } catch (err) {
        body.innerHTML = `<div class="archive-view-empty">${escapeHtml(err.message)}</div>`;
    }
}

function closeViewModal() {
    const modal = document.getElementById('viewPostModal');
    if (modal) modal.style.display = 'none';
    document.body.style.overflow = '';
    viewingPostId = null;
}

function openLightbox(images, startIndex = 0) {
    lightboxImages = images || [];
    lightboxIndex = Math.max(0, Math.min(startIndex, lightboxImages.length - 1));

    const box = document.getElementById('archiveLightbox');
    if (!box || !lightboxImages.length) return;

    box.classList.add('is-open');
    box.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    renderLightboxImage();
}

function closeLightbox() {
    const box = document.getElementById('archiveLightbox');
    if (box) {
        box.classList.remove('is-open');
        box.setAttribute('aria-hidden', 'true');
    }
    if (!document.getElementById('viewPostModal') || document.getElementById('viewPostModal').style.display === 'none') {
        document.body.style.overflow = '';
    }
}

function renderLightboxImage() {
    const img = document.getElementById('archiveLightboxImage');
    const counter = document.getElementById('archiveLightboxCounter');
    const prev = document.getElementById('archiveLightboxPrev');
    const next = document.getElementById('archiveLightboxNext');

    if (!img || !lightboxImages.length) return;

    img.src = lightboxImages[lightboxIndex];
    if (counter) counter.textContent = `${lightboxIndex + 1} / ${lightboxImages.length}`;
    if (prev) prev.style.display = lightboxImages.length > 1 ? 'flex' : 'none';
    if (next) next.style.display = lightboxImages.length > 1 ? 'flex' : 'none';
}

function lightboxPrev() {
    if (!lightboxImages.length) return;
    lightboxIndex = (lightboxIndex - 1 + lightboxImages.length) % lightboxImages.length;
    renderLightboxImage();
}

function lightboxNext() {
    if (!lightboxImages.length) return;
    lightboxIndex = (lightboxIndex + 1) % lightboxImages.length;
    renderLightboxImage();
}

function showSuccess(message) {
    const banner = document.getElementById('archiveSuccessBanner');
    const text = document.getElementById('archiveSuccessText');
    if (!banner || !text) return;

    text.textContent = message;
    banner.style.display = 'flex';
    setTimeout(() => { banner.style.display = 'none'; }, 5000);
}

function openModal(id) {
    document.getElementById(id)?.classList.add('open');
}

function closeModals() {
    document.querySelectorAll('.archive-modal.open').forEach((m) => m.classList.remove('open'));
    pendingActionId = null;
}

async function confirmRestore() {
    if (!pendingActionId) return;
    const btn = document.getElementById('confirmRestoreBtn');
    btn.disabled = true;

    try {
        await apiFetch(cfg.restoreUrl(pendingActionId), { method: 'POST' });
        closeModals();
        closeViewModal();
        showSuccess('Post restored successfully.');
        await loadArchive(currentPage);
    } catch (err) {
        alert(err.message);
    } finally {
        btn.disabled = false;
    }
}

function bindEvents() {
    document.getElementById('archiveSearch')?.addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => loadArchive(1), 300);
    });

    document.getElementById('archiveTableBody')?.addEventListener('click', (e) => {
        const actionItem = e.target.closest('.row-actions-item');
        if (!actionItem) return;

        const id = actionItem.dataset.id;
        if (actionItem.dataset.action === 'view') {
            openViewModal(id);
            return;
        }
        if (actionItem.dataset.action === 'restore') {
            pendingActionId = id;
            openModal('restoreModal');
        }
    });

    document.getElementById('archiveViewBody')?.addEventListener('click', (e) => {
        const galleryItem = e.target.closest('.archive-view-gallery-item');
        if (!galleryItem) return;
        const postId = galleryItem.closest('.archive-view-gallery')?.dataset.postId || viewingPostId;
        const images = getPostImages(postId);
        openLightbox(images, Number(galleryItem.dataset.imageIndex || 0));
    });

    document.getElementById('archiveViewClose')?.addEventListener('click', closeViewModal);
    document.getElementById('archiveViewCloseBtn')?.addEventListener('click', closeViewModal);
    document.getElementById('viewPostModal')?.addEventListener('click', (e) => {
        if (e.target.id === 'viewPostModal') closeViewModal();
    });

    document.getElementById('archiveViewRestoreBtn')?.addEventListener('click', () => {
        if (!viewingPostId) return;
        pendingActionId = viewingPostId;
        openModal('restoreModal');
    });

    document.getElementById('archiveLightboxClose')?.addEventListener('click', closeLightbox);
    document.getElementById('archiveLightboxPrev')?.addEventListener('click', lightboxPrev);
    document.getElementById('archiveLightboxNext')?.addEventListener('click', lightboxNext);
    document.getElementById('archiveLightbox')?.addEventListener('click', (e) => {
        if (e.target.id === 'archiveLightbox') closeLightbox();
    });

    document.addEventListener('keydown', (e) => {
        const lightboxOpen = document.getElementById('archiveLightbox')?.classList.contains('is-open');
        if (e.key === 'Escape') {
            if (lightboxOpen) closeLightbox();
            else closeViewModal();
            closeModals();
        }
        if (!lightboxOpen) return;
        if (e.key === 'ArrowLeft') lightboxPrev();
        if (e.key === 'ArrowRight') lightboxNext();
    });

    document.querySelectorAll('[data-close-modal]').forEach((el) => {
        el.addEventListener('click', closeModals);
    });

    document.getElementById('confirmRestoreBtn')?.addEventListener('click', confirmRestore);
}

document.addEventListener('DOMContentLoaded', () => {
    bindEvents();

    if (typeof window.bindTablePageFooter === 'function') {
        tablePagination = window.bindTablePageFooter({
            prefix: 'archive',
            getTotalRecords: () => totalRecords,
            getCurrentPage: () => currentPage,
            setCurrentPage: (page) => { currentPage = page; },
            getRecordsPerPage: () => recordsPerPage,
            setRecordsPerPage: (value) => { recordsPerPage = value; },
            onPageChange: () => loadArchive(currentPage),
        });
    }

    loadArchive(1);
});
