const cfg = window.ArchiveConfig || {};
let currentPage = 1;
let lastPage = 1;
let searchTimer = null;
let pendingActionId = null;

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

function renderStats(stats = {}) {
    const row = document.getElementById('archiveStatsRow');
    if (!row) return;

    row.innerHTML = `
        <div class="archive-stat-card">
            <div class="archive-stat-label">Total Archived</div>
            <div class="archive-stat-value">${stats.total ?? 0}</div>
        </div>
        <div class="archive-stat-card">
            <div class="archive-stat-label">Expiring Within 7 Days</div>
            <div class="archive-stat-value">${stats.expiring_soon ?? 0}</div>
        </div>
    `;
}

function renderCard(post) {
    const thumb = post.thumbnail_url
        ? `<img src="${escapeHtml(post.thumbnail_url)}" alt="" class="archive-card-thumb">`
        : '';

    return `
        <article class="archive-card" data-id="${post.id}">
            <div class="archive-card-main">
                <div class="archive-card-badges">
                    <span class="archive-badge archived">Archived</span>
                    <span class="archive-badge ${tierClass(post.days_tier)}">${escapeHtml(post.auto_delete_label)}</span>
                    <span class="archive-badge days-${post.days_tier}">${post.days_remaining} days remaining</span>
                    <span class="archive-badge type">${escapeHtml(post.type_label)}</span>
                </div>
                <h3 class="archive-card-title">${escapeHtml(post.title)}</h3>
                <p class="archive-card-preview">${escapeHtml(post.body_preview)}</p>
                <div class="archive-card-meta">
                    <span>By ${escapeHtml(post.author_name)}</span>
                    <span>Archived ${escapeHtml(post.archived_ago)}</span>
                    ${post.image_count ? `<span>${post.image_count} image${post.image_count === 1 ? '' : 's'}</span>` : ''}
                </div>
            </div>
            <div class="archive-card-actions">
                ${thumb}
                <button type="button" class="archive-btn archive-btn-primary" data-restore="${post.id}">Restore</button>
            </div>
        </article>
    `;
}

function renderList(posts) {
    const list = document.getElementById('archiveList');
    if (!list) return;

    if (!posts.length) {
        list.innerHTML = '<div class="archive-empty">No archived posts found.</div>';
        return;
    }

    list.innerHTML = posts.map(renderCard).join('');
}

function updatePagination() {
    const wrap = document.getElementById('archivePagination');
    const info = document.getElementById('archivePageInfo');
    const prev = document.getElementById('archivePrevBtn');
    const next = document.getElementById('archiveNextBtn');

    if (!wrap) return;

    if (lastPage <= 1) {
        wrap.style.display = 'none';
        return;
    }

    wrap.style.display = 'flex';
    info.textContent = `Page ${currentPage} of ${lastPage}`;
    prev.disabled = currentPage <= 1;
    next.disabled = currentPage >= lastPage;
}

async function loadArchive(page = 1) {
    currentPage = page;
    const search = document.getElementById('archiveSearch')?.value.trim() || '';
    const list = document.getElementById('archiveList');

    if (list) {
        list.innerHTML = '<div class="archive-loading">Loading archived posts…</div>';
    }

    const params = new URLSearchParams({ page: String(page) });
    if (search) params.set('search', search);

    try {
        const data = await apiFetch(`${cfg.dataUrl}?${params.toString()}`);
        renderStats(data.stats);
        renderList(data.data || []);
        lastPage = data.last_page || 1;
        updatePagination();
    } catch (err) {
        if (list) {
            list.innerHTML = `<div class="archive-empty">${escapeHtml(err.message)}</div>`;
        }
    }
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

    document.getElementById('archivePrevBtn')?.addEventListener('click', () => {
        if (currentPage > 1) loadArchive(currentPage - 1);
    });

    document.getElementById('archiveNextBtn')?.addEventListener('click', () => {
        if (currentPage < lastPage) loadArchive(currentPage + 1);
    });

    document.getElementById('archiveList')?.addEventListener('click', (e) => {
        const restoreBtn = e.target.closest('[data-restore]');

        if (restoreBtn) {
            pendingActionId = restoreBtn.dataset.restore;
            openModal('restoreModal');
        }
    });

    document.querySelectorAll('[data-close-modal]').forEach((el) => {
        el.addEventListener('click', closeModals);
    });

    document.getElementById('confirmRestoreBtn')?.addEventListener('click', confirmRestore);
}

document.addEventListener('DOMContentLoaded', () => {
    bindEvents();
    loadArchive(1);
});
