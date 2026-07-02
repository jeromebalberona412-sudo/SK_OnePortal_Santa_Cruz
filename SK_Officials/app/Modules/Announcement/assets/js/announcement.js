'use strict';

const POSTS_PER_PAGE = 10;
const MAX_BODY_CHARS = 2000;
const MAX_IMAGES = 20;

let currentFilter = 'all';
let currentPage = 1;
let lastPage = 1;
let editingPostId = null;
let pendingFiles = [];
let existingImages = [];
let removedImageIds = [];
let pendingLinkUrl = null;
let currentUserId = null;
let lightboxImages = [];
let lightboxIndex = 0;
let lightboxZoom = 1;
const LIGHTBOX_ZOOM_MIN = 0.5;
const LIGHTBOX_ZOOM_MAX = 4;
const LIGHTBOX_ZOOM_STEP = 0.25;
let feedPollTimer = null;
let knownPostIds = new Set();
let feedLoading = false;
let feedSearch = '';

const SK_AVATAR = () => window.AnnConfig?.userAvatar || window.AnnConfig?.barangayLogo || DEFAULT_LOGO();
const DEFAULT_LOGO = () => window.AnnConfig?.defaultLogo || '/images/logo.png';

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

function escapeHtml(text) {
    return String(text ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function linkifyText(text) {
    const escaped = escapeHtml(text);
    return escaped.replace(
        /(https?:\/\/[^\s<]+)/g,
        '<a href="$1" target="_blank" rel="noopener noreferrer" class="post-inline-link">$1</a>'
    );
}

async function apiFetch(url, options = {}) {
    const { headers: extraHeaders, ...rest } = options;
    const res = await fetch(url, {
        ...rest,
        headers: {
            'X-CSRF-TOKEN': csrfToken(),
            Accept: 'application/json',
            ...extraHeaders,
        },
    });
    if (!res.ok) {
        let message = 'Request failed.';
        try {
            const err = await res.json();
            message = err.message || message;
        } catch (_) { /* ignore */ }
        throw new Error(message);
    }
    return res.json();
}

async function loadFeed(reset = true) {
    if (feedLoading) return;
    feedLoading = true;

    const container = document.getElementById('feed-posts');
    if (!container) {
        feedLoading = false;
        return;
    }

    try {
        if (reset) {
            currentPage = 1;
            container.innerHTML = '';
            knownPostIds.clear();
        }

        const params = new URLSearchParams({ page: currentPage, filter: currentFilter });
        if (feedSearch) params.set('search', feedSearch);
        const data = await apiFetch(`/api/announcements?${params}`);

        currentUserId = data.user_id;
        lastPage = data.last_page;

        (data.data || []).forEach((p) => upsertPost(p, reset ? 'append' : 'append'));

        const btn = document.getElementById('load-more-btn');
        if (btn) btn.style.display = currentPage >= lastPage ? 'none' : 'inline-flex';
    } finally {
        feedLoading = false;
        setFilterTabsDisabled(false);
    }
}

function postExistsInDom(postId) {
    return document.querySelector(`.post-card[data-post-id="${postId}"]`) !== null;
}

function upsertPost(p, mode = 'append') {
    const id = Number(p.id);
    if (!id || knownPostIds.has(id) || postExistsInDom(id)) {
        return;
    }

    knownPostIds.add(id);
    const container = document.getElementById('feed-posts');
    const el = document.createElement('article');
    el.className = 'post-card';
    el.dataset.postId = String(id);
    el.innerHTML = buildPost(p);
    bindPostImageClicks(el, p);

    if (mode === 'prepend') {
        container.prepend(el);
    } else {
        container.appendChild(el);
    }
}

async function pollFeedUpdates() {
    if (document.hidden || feedLoading || document.getElementById('composeModal')?.classList.contains('active')) {
        return;
    }

    try {
        const params = new URLSearchParams({ page: 1, filter: currentFilter });
        const data = await apiFetch(`/api/announcements?${params}`);
        const fresh = (data.data || []).filter((p) => {
            const id = Number(p.id);
            return id && !knownPostIds.has(id) && !postExistsInDom(id);
        });

        if (!fresh.length) return;

        fresh.reverse().forEach((p) => {
            upsertPost(p, 'prepend');
            const el = document.querySelector(`.post-card[data-post-id="${p.id}"]`);
            if (el) el.classList.add('post-card-new');
        });

        setTimeout(() => {
            document.querySelectorAll('.post-card-new').forEach((el) => el.classList.remove('post-card-new'));
        }, 1200);
    } catch (_) { /* silent poll failure */ }
}

function startFeedPolling() {
    const interval = window.AnnConfig?.feedPollMs || 30000;
    if (feedPollTimer) clearInterval(feedPollTimer);
    feedPollTimer = setInterval(pollFeedUpdates, interval);
}

function postAvatarUrl(p) {
    if (p.barangay_logo_url) return p.barangay_logo_url;
    if (p.is_federation_wide) return DEFAULT_LOGO();
    const name = p.barangay_name || 'SK';
    return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=2c2c3e&color=f5c518&size=80`;
}

function commentAvatarUrl(c) {
    if (c.author_avatar_url) return c.author_avatar_url;
    if (c.user_type === 'sk_official' && c.barangay_logo_url) return c.barangay_logo_url;
    if (c.user_type === 'sk_fed' && c.barangay_logo_url) return c.barangay_logo_url;
    const name = c.author_name || 'SK';
    return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=2c2c3e&color=f5c518&size=80`;
}

function buildImageGrid(images, postId) {
    if (!images?.length) return '';

    const unique = [...new Set(images.filter(Boolean))];
    const count = unique.length;
    let gridClass = 'post-media-grid';
    let slots = [];

    if (count === 1) {
        gridClass += ' grid-1';
        slots = [{ index: 0 }];
    } else if (count === 2) {
        gridClass += ' grid-2 fit-contain';
        slots = [{ index: 0 }, { index: 1 }];
    } else if (count === 3) {
        gridClass += ' grid-3 fit-contain';
        slots = [{ index: 0 }, { index: 1 }, { index: 2 }];
    } else if (count === 4) {
        gridClass += ' grid-4 fit-contain';
        slots = [{ index: 0 }, { index: 1 }, { index: 2 }, { index: 3 }];
    } else {
        gridClass += ' grid-4-plus fit-contain';
        slots = [
            { index: 0 },
            { index: 1 },
            { index: 2 },
            { index: 3, overlay: `+${count - 3}`, moreOnly: true },
        ];
    }

    const tiles = slots.map(({ index, overlay, moreOnly }) => {
        const overlayHtml = overlay
            ? `<span class="post-media-more">${escapeHtml(overlay)}</span>`
            : '';
        const tileClass = moreOnly ? 'post-media-tile post-media-more-tile' : 'post-media-tile';
        const imgHtml = moreOnly
            ? `<img src="${escapeHtml(unique[index])}" alt="" loading="lazy" aria-hidden="true">`
            : `<img src="${escapeHtml(unique[index])}" alt="Post image ${index + 1}" loading="lazy">`;

        return `<button type="button" class="${tileClass}" data-post-id="${postId}" data-index="${index}" aria-label="View photo ${index + 1}">
            ${imgHtml}${overlayHtml}
        </button>`;
    }).join('');

    return `<div class="${gridClass}" data-all-images='${JSON.stringify(unique).replace(/'/g, '&#39;')}'>${tiles}</div>`;
}

function loadMorePosts() {
    currentPage++;
    loadFeed(false);
}

function setFeedFilter(btn, filter) {
    if (feedLoading || (filter === currentFilter && btn.classList.contains('active'))) {
        return;
    }
    currentFilter = filter;
    document.querySelectorAll('.feed-tab').forEach((t) => t.classList.remove('active'));
    btn.classList.add('active');
    setFilterTabsDisabled(true);
    loadFeed(true);
}

function setFilterTabsDisabled(disabled) {
    document.querySelectorAll('.feed-tab').forEach((tab) => {
        tab.disabled = disabled;
        tab.classList.toggle('is-loading', disabled);
    });
}

function bindFeedFilterTabs() {
    document.querySelectorAll('.feed-tab').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            setFeedFilter(btn, btn.dataset.filter || 'all');
        });
    });
}

function buildPost(p) {
    const avatar = postAvatarUrl(p);
    const images = [...new Set((p.images?.length ? p.images : (p.image_url ? [p.image_url] : [])).filter(Boolean))];
    const mediaHtml = buildImageGrid(images, p.id);
    const linkHtml = p.link_url
        ? `<a href="${escapeHtml(p.link_url)}" target="_blank" rel="noopener" class="post-link-preview">${escapeHtml(p.link_url)}</a>`
        : '';
    const optionsHtml = p.owned
        ? `<div style="position:relative;">
            <button class="post-options-btn" onclick="togglePostOptions(${p.id},event)">
              <svg viewBox="0 0 20 20" fill="currentColor"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/></svg>
            </button>
            <div class="post-options-menu" id="options-menu-${p.id}">
              <button onclick="editPost(${p.id})"><svg viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>Edit</button>
              <button class="danger" onclick="openArchiveModal(${p.id})"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>Archive</button>
            </div>
           </div>` : '';

    const commentsHtml = (p.comments ?? []).map((c) =>
        `<div class="comment-item">
           <img src="${escapeHtml(commentAvatarUrl(c))}" alt="${escapeHtml(c.author_name)}" class="comment-avatar">
           <div class="comment-content">
             <p class="comment-author">${escapeHtml(c.author_name)}</p>
             <p class="comment-text">${escapeHtml(c.body)}</p>
             <span class="comment-time">${escapeHtml(c.time)}</span>
           </div>
         </div>`
    ).join('');

    return `
      <div class="post-header">
        <img src="${avatar}" alt="${escapeHtml(p.barangay_name)}" class="post-avatar">
        <div class="post-info">
          <h3 class="post-author">${escapeHtml(p.author_name ?? ('SK Brgy. ' + (p.barangay_name ?? '')))}</h3>
          <p class="post-meta">
            <span class="post-type ${p.type}">${escapeHtml(p.type)}</span>
            <span class="post-time">${escapeHtml(p.time ?? '')}</span>
          </p>
        </div>
        ${optionsHtml}
      </div>
      <div class="post-content">
        ${p.title ? `<h2 class="post-title">${escapeHtml(p.title)}</h2>` : ''}
        <p class="post-text">${linkifyText(p.body)}</p>
        ${mediaHtml}${linkHtml}
      </div>
      <div class="post-actions">
        <button class="action-btn${p.liked ? ' liked' : ''}" onclick="toggleLike(${p.id}, this)">
          <svg viewBox="0 0 20 20" fill="currentColor"><path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z"/></svg>
          <span id="like-count-${p.id}">Like (${p.likes})</span>
        </button>
        <button class="action-btn comment-btn" onclick="toggleComments(${p.id})">
          <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"/></svg>
          <span id="comment-count-${p.id}">Comment (${(p.comments ?? []).length})</span>
        </button>
      </div>
      <div class="comments-section" id="comments-${p.id}" style="display:none;">
        <div class="comments-list" id="comments-list-${p.id}">${commentsHtml}</div>
        <div class="comment-input-wrapper">
          <img src="${escapeHtml(SK_AVATAR())}" alt="You" class="comment-avatar">
          <input type="text" class="comment-input" placeholder="Write a comment..." maxlength="500" onkeydown="if(event.key==='Enter')submitComment(${p.id},this)">
          <button class="send-comment-btn" onclick="submitComment(${p.id},this.previousElementSibling)">
            <svg viewBox="0 0 20 20" fill="currentColor"><path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/></svg>
          </button>
        </div>
      </div>`;
}

function bindPostImageClicks(postEl, post) {
    const grid = postEl.querySelector('.post-media-grid');
    if (!grid) return;

    let images = [];
    try {
        images = JSON.parse(grid.dataset.allImages || '[]');
    } catch (_) {
        images = [];
    }
    if (!images.length) {
        images = post.images?.length ? post.images : (post.image_url ? [post.image_url] : []);
        images = [...new Set(images.filter(Boolean))];
    }

    grid.querySelectorAll('.post-media-tile').forEach((tile) => {
        tile.addEventListener('click', () => {
            openLightbox(images, parseInt(tile.dataset.index, 10) || 0);
        });
    });
}

function openLightbox(images, startIndex = 0) {
    lightboxImages = images;
    lightboxIndex = startIndex;
    lightboxZoom = 1;
    const lb = document.getElementById('imageLightbox');
    if (!lb) return;
    lb.classList.add('active');
    document.body.style.overflow = 'hidden';
    renderLightboxImage();
    applyLightboxZoom();
}

function closeLightbox() {
    document.getElementById('imageLightbox')?.classList.remove('active');
    document.body.style.overflow = '';
    lightboxZoom = 1;
    applyLightboxZoom();
}

function applyLightboxZoom() {
    const img = document.getElementById('lightboxImage');
    const label = document.getElementById('lightboxZoomLevel');
    if (img) {
        img.style.transform = `scale(${lightboxZoom})`;
    }
    if (label) {
        label.textContent = `${Math.round(lightboxZoom * 100)}%`;
    }
}

function lightboxZoomIn() {
    lightboxZoom = Math.min(LIGHTBOX_ZOOM_MAX, +(lightboxZoom + LIGHTBOX_ZOOM_STEP).toFixed(2));
    applyLightboxZoom();
}

function lightboxZoomOut() {
    lightboxZoom = Math.max(LIGHTBOX_ZOOM_MIN, +(lightboxZoom - LIGHTBOX_ZOOM_STEP).toFixed(2));
    applyLightboxZoom();
}

function lightboxZoomReset() {
    lightboxZoom = 1;
    applyLightboxZoom();
    const viewport = document.getElementById('lightboxViewport');
    if (viewport) {
        viewport.scrollLeft = 0;
        viewport.scrollTop = 0;
    }
}

function renderLightboxImage() {
    const img = document.getElementById('lightboxImage');
    const counter = document.getElementById('lightboxCounter');
    if (!img || !lightboxImages.length) return;
    img.src = lightboxImages[lightboxIndex];
    lightboxZoom = 1;
    applyLightboxZoom();
    if (counter) counter.textContent = `${lightboxIndex + 1} / ${lightboxImages.length}`;
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

async function toggleLike(id, btn) {
    const data = await apiFetch(`/api/announcements/${id}/react`, { method: 'POST' });
    btn.classList.toggle('liked', data.liked);
    const el = document.getElementById(`like-count-${id}`);
    if (el) el.textContent = `Like (${data.count})`;
}

function toggleComments(id) {
    const section = document.getElementById(`comments-${id}`);
    if (section) section.style.display = section.style.display === 'none' ? 'block' : 'none';
}

async function submitComment(id, input) {
    const text = input.value.trim();
    if (!text) return;
    if (text.length > 500) {
        alert('Comments are limited to 500 characters.');
        return;
    }
    try {
        const comment = await apiFetch(`/api/announcements/${id}/comment`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ body: text }),
        });
        input.value = '';
        const list = document.getElementById(`comments-list-${id}`);
        if (list) {
            list.insertAdjacentHTML('beforeend',
                `<div class="comment-item">
                   <img src="${escapeHtml(commentAvatarUrl(comment))}" alt="${escapeHtml(comment.author_name)}" class="comment-avatar">
                   <div class="comment-content">
                     <p class="comment-author">${escapeHtml(comment.author_name)}</p>
                     <p class="comment-text">${escapeHtml(comment.body)}</p>
                     <span class="comment-time">${escapeHtml(comment.time)}</span>
                   </div>
                 </div>`
            );
            list.scrollTop = list.scrollHeight;
        }
        const countEl = document.getElementById(`comment-count-${id}`);
        if (countEl) {
            const cur = parseInt(countEl.textContent.match(/\d+/)?.[0] ?? '0', 10);
            countEl.textContent = `Comment (${cur + 1})`;
        }
    } catch (err) {
        alert(err?.message || 'Unable to post comment.');
    }
}

function resetComposeForm() {
    editingPostId = null;
    pendingFiles = [];
    existingImages = [];
    removedImageIds = [];
    pendingLinkUrl = null;
    document.getElementById('edit-post-id').value = '';
    document.getElementById('compose-content').value = '';
    document.getElementById('compose-char-count').textContent = `0 / ${MAX_BODY_CHARS}`;
    document.getElementById('compose-images-preview').innerHTML = '';
    document.getElementById('compose-link-input-wrap').style.display = 'none';
    document.getElementById('compose-link-input').value = '';
    document.getElementById('compose-image-input').value = '';
    renderPreviewMeta();
    setPostButtonLoading(false);
}

function totalSelectedImages() {
    return existingImages.length + pendingFiles.length;
}

function openComposeModal(type) {
    resetComposeForm();
    document.getElementById('compose-modal-title').textContent = 'Create Post';
    if (type) {
        const map = { announcement: 'announcement', event: 'event', photo: 'activity' };
        document.getElementById('compose-type').value = map[type] ?? 'update';
    }
    const modal = document.getElementById('composeModal');
    modal.classList.add('active');
    modal.classList.remove('compose-maximized');
    const btn = document.getElementById('composeFullscreenBtn');
    if (btn) {
        btn.title = 'Full screen';
        btn.setAttribute('aria-label', 'Full screen');
        btn.innerHTML = '<svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path d="M3 3h5v2H5v3H3V3zm9 0h5v5h-2V5h-3V3zM3 12h2v3h3v2H3v-5zm12 0h2v5h-5v-2h3v-3z"/></svg>';
    }
}

function closeComposeModal() {
    document.getElementById('composeModal').classList.remove('active', 'compose-maximized');
    resetComposeForm();
}

function toggleComposeFullscreen() {
    const modal = document.getElementById('composeModal');
    const btn = document.getElementById('composeFullscreenBtn');
    if (!modal) return;
    modal.classList.toggle('compose-maximized');
    const isMax = modal.classList.contains('compose-maximized');
    if (btn) {
        btn.title = isMax ? 'Restore down' : 'Full screen';
        btn.setAttribute('aria-label', btn.title);
        btn.innerHTML = isMax
            ? '<svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path d="M7 7H3v4h2V9h2V7zm6 0v2h2v2h2V7h-4zM7 13H5v-2H3v4h4v-2zm6 2v-2h2v-2h2v4h-4v-2z"/></svg>'
            : '<svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path d="M3 3h5v2H5v3H3V3zm9 0h5v5h-2V5h-3V3zM3 12h2v3h3v2H3v-5zm12 0h2v5h-5v-2h3v-3z"/></svg>';
    }
}

async function editPost(id) {
    document.querySelectorAll('.post-options-menu.open').forEach((m) => m.classList.remove('open'));

    try {
        const post = await apiFetch(`/api/announcements/${id}`);
        editingPostId = id;
        pendingFiles = [];
        removedImageIds = [];
        existingImages = [];

        if (post.image_items?.length) {
            existingImages = post.image_items.map((item) => ({ id: item.id, url: item.url }));
        } else if (post.images?.length) {
            existingImages = post.images.map((url, idx) => ({ id: null, url, legacyIndex: idx }));
        }

        document.getElementById('compose-modal-title').textContent = 'Edit Post';
        document.getElementById('edit-post-id').value = id;
        document.getElementById('compose-content').value = post.body || '';
        document.getElementById('compose-type').value = post.type || 'update';
        document.getElementById('compose-link-input-wrap').style.display = post.link_url ? 'block' : 'none';
        document.getElementById('compose-link-input').value = post.link_url || '';
        refreshPreviewGrid();
        updateCharCount();
        document.getElementById('composeModal').classList.add('active');
    } catch (err) {
        alert(err.message || 'Unable to load post for editing.');
    }
}

let pendingArchivePostId = null;

function openArchiveModal(id) {
    pendingArchivePostId = id;
    document.getElementById('archivePostModal')?.classList.add('active');
    document.querySelectorAll('.post-options-menu.open').forEach((m) => m.classList.remove('open'));
}

function closeArchiveModal() {
    pendingArchivePostId = null;
    document.getElementById('archivePostModal')?.classList.remove('active');
}

async function confirmArchivePost() {
    if (!pendingArchivePostId) return;
    const id = pendingArchivePostId;
    const btn = document.getElementById('confirmArchiveBtn');
    if (btn) {
        btn.disabled = true;
        btn.textContent = 'Archiving…';
    }

    try {
        await apiFetch(`/api/announcements/${id}`, { method: 'DELETE' });
        knownPostIds.delete(Number(id));
        document.querySelector(`.post-card[data-post-id="${id}"]`)?.remove();
        closeArchiveModal();
    } catch (err) {
        alert(err.message || 'Failed to archive post.');
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.textContent = 'Archive Post';
        }
    }
}

async function deletePost(id) {
    openArchiveModal(id);
}

function setPostButtonLoading(loading) {
    const btn = document.getElementById('compose-post-btn');
    if (!btn) return;
    btn.disabled = loading;
    btn.classList.toggle('is-loading', loading);
    btn.innerHTML = loading
        ? '<span class="btn-spinner"></span> Posting…'
        : '<svg viewBox="0 0 20 20" fill="currentColor" style="width:16px;height:16px;"><path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/></svg> Post';
}

async function submitPost() {
    const bodyEl = document.getElementById('compose-content');
    const body = bodyEl.value.trim();
    if (!body) { alert('Please write something.'); return; }
    if (body.length > MAX_BODY_CHARS) {
        alert(`Post text must be ${MAX_BODY_CHARS} characters or less.`);
        return;
    }

    const type = document.getElementById('compose-type').value;
    const link = document.getElementById('compose-link-input').value.trim() || null;

    setPostButtonLoading(true);

    try {
        if (editingPostId) {
            const fd = new FormData();
            fd.append('type', type);
            fd.append('body', body);
            if (link) fd.append('link_url', link);
            fd.append('_method', 'PUT');
            pendingFiles.forEach((file) => fd.append('images[]', file));
            removedImageIds.forEach((imageId) => fd.append('removed_image_ids[]', imageId));

            const res = await fetch(`/api/announcements/${editingPostId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken(),
                    Accept: 'application/json',
                },
                body: fd,
            });
            const updated = await res.json();
            if (!res.ok) throw new Error(updated.message || 'Failed to update post.');

            const card = document.querySelector(`.post-card[data-post-id="${editingPostId}"]`);
            if (card) {
                card.innerHTML = buildPost(updated);
                bindPostImageClicks(card, updated);
            }
        } else {
            const fd = new FormData();
            fd.append('type', type);
            fd.append('body', body);
            if (link) fd.append('link_url', link);
            pendingFiles.forEach((file) => fd.append('images[]', file));

            const res = await fetch('/api/announcements', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken(),
                    Accept: 'application/json',
                },
                body: fd,
            });
            const created = await res.json();
            if (!res.ok) throw new Error(created.message || 'Failed to create post.');

            knownPostIds.add(Number(created.id));
            upsertPost(created, 'prepend');
            if (typeof showFeedToast === 'function') {
                showFeedToast('Post published successfully.', 'success');
            }
        }
        closeComposeModal();
    } catch (e) {
        alert('Failed to save post. Please try again.\n' + (e.message || ''));
    } finally {
        setPostButtonLoading(false);
    }
}

function updateCharCount() {
    const el = document.getElementById('compose-content');
    const counter = document.getElementById('compose-char-count');
    if (!el || !counter) return;
    const len = el.value.length;
    counter.textContent = `${len} / ${MAX_BODY_CHARS}`;
    counter.classList.toggle('over-limit', len > MAX_BODY_CHARS);
}

function previewImages(input) {
    const files = Array.from(input.files || []);
    if (!files.length) return;

    const remaining = MAX_IMAGES - totalSelectedImages();
    if (remaining <= 0) {
        alert(`You can upload up to ${MAX_IMAGES} images per post.`);
        input.value = '';
        return;
    }

    const toAdd = files.slice(0, remaining);
    if (files.length > remaining) {
        alert(`Only ${remaining} more image(s) can be added (max ${MAX_IMAGES}).`);
    }

    toAdd.forEach((file) => pendingFiles.push(file));
    input.value = '';
    refreshPreviewGrid();
}

function appendExistingThumb(image) {
    const wrap = document.getElementById('compose-images-preview');
    if (!wrap || !image?.url) return;
    const item = document.createElement('div');
    item.className = 'compose-preview-item compose-preview-item--existing';
    item.innerHTML = `
        <img src="${escapeHtml(image.url)}" alt="Existing photo">
        <button type="button" class="compose-preview-remove" aria-label="Remove photo">&times;</button>`;
    item.querySelector('.compose-preview-remove').addEventListener('click', () => {
        if (image.id) removedImageIds.push(image.id);
        existingImages = existingImages.filter((img) => {
            if (image.id) return img.id !== image.id;
            return img.url !== image.url;
        });
        refreshPreviewGrid();
    });
    wrap.appendChild(item);
}

function appendPreviewThumb(src, index) {
    const wrap = document.getElementById('compose-images-preview');
    const item = document.createElement('div');
    item.className = 'compose-preview-item compose-preview-item--new';
    item.innerHTML = `
        <img src="${src}" alt="Preview">
        <button type="button" class="compose-preview-remove" aria-label="Remove photo">&times;</button>`;
    item.querySelector('.compose-preview-remove').addEventListener('click', () => {
        pendingFiles.splice(index, 1);
        refreshPreviewGrid();
    });
    wrap.appendChild(item);
}

function refreshPreviewGrid() {
    const wrap = document.getElementById('compose-images-preview');
    if (!wrap) return;
    wrap.innerHTML = '';
    existingImages.forEach((image) => appendExistingThumb(image));
    pendingFiles.forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = (e) => appendPreviewThumb(e.target.result, index);
        reader.readAsDataURL(file);
    });
    renderPreviewMeta();
}

function rerenderPreviewThumbs() {
    refreshPreviewGrid();
}

function renderPreviewMeta() {
    const meta = document.getElementById('compose-images-meta');
    if (!meta) return;
    const total = totalSelectedImages();
    meta.textContent = total ? `${total} / ${MAX_IMAGES} photos selected` : '';
}

function toggleLinkInput() {
    const wrap = document.getElementById('compose-link-input-wrap');
    wrap.style.display = wrap.style.display === 'none' ? 'block' : 'none';
}

function togglePostOptions(id, e) {
    e.stopPropagation();
    const menu = document.getElementById(`options-menu-${id}`);
    const isOpen = menu?.classList.contains('open');
    document.querySelectorAll('.post-options-menu.open').forEach((m) => m.classList.remove('open'));
    if (!isOpen) menu?.classList.add('open');
}

function toggleNotifPopover(e) { e.stopPropagation(); document.getElementById('notifPopover')?.classList.toggle('show'); document.getElementById('profileDropdown')?.classList.remove('show'); }
function toggleProfileDropdown(e) { e.stopPropagation(); document.getElementById('profileDropdown')?.classList.toggle('show'); document.getElementById('notifPopover')?.classList.remove('show'); }
function toggleSidebar() {
    const isMobile = window.innerWidth <= 1024;
    if (isMobile) document.body.classList.toggle('sidebar-open');
    else {
        document.body.classList.toggle('sidebar-collapsed');
        localStorage.setItem('sidebarCollapsed', document.body.classList.contains('sidebar-collapsed'));
    }
}

document.addEventListener('DOMContentLoaded', () => {
    bindFeedFilterTabs();
    loadFeed(true).then(() => startFeedPolling());

    let feedSearchTimer = null;
    document.getElementById('feedSearchInput')?.addEventListener('input', function () {
        clearTimeout(feedSearchTimer);
        feedSearchTimer = setTimeout(() => {
            feedSearch = this.value.trim();
            loadFeed(true);
        }, 300);
    });

    document.getElementById('compose-content')?.addEventListener('input', updateCharCount);
    updateCharCount();

    if (window.innerWidth > 1024 && localStorage.getItem('sidebarCollapsed') === 'true') {
        document.body.classList.add('sidebar-collapsed');
    }

    document.addEventListener('click', () => {
        document.querySelectorAll('.post-options-menu.open').forEach((m) => m.classList.remove('open'));
        document.getElementById('notifPopover')?.classList.remove('show');
        document.getElementById('profileDropdown')?.classList.remove('show');
    });

    document.getElementById('lightboxClose')?.addEventListener('click', closeLightbox);
    document.getElementById('lightboxPrev')?.addEventListener('click', lightboxPrev);
    document.getElementById('lightboxNext')?.addEventListener('click', lightboxNext);
    document.getElementById('lightboxZoomIn')?.addEventListener('click', lightboxZoomIn);
    document.getElementById('lightboxZoomOut')?.addEventListener('click', lightboxZoomOut);
    document.getElementById('lightboxZoomReset')?.addEventListener('click', lightboxZoomReset);
    document.getElementById('imageLightbox')?.addEventListener('click', (e) => {
        if (e.target?.id === 'imageLightbox') closeLightbox();
    });
    document.getElementById('lightboxViewport')?.addEventListener('wheel', (e) => {
        const lb = document.getElementById('imageLightbox');
        if (!lb?.classList.contains('active')) return;
        e.preventDefault();
        if (e.deltaY < 0) lightboxZoomIn();
        else lightboxZoomOut();
    }, { passive: false });

    document.addEventListener('keydown', (e) => {
        const lb = document.getElementById('imageLightbox');
        if (!lb?.classList.contains('active')) return;
        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowLeft') lightboxPrev();
        if (e.key === 'ArrowRight') lightboxNext();
        if (e.key === '+' || e.key === '=') lightboxZoomIn();
        if (e.key === '-') lightboxZoomOut();
        if (e.key === '0') lightboxZoomReset();
    });

    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) pollFeedUpdates();
    });

    const fab = document.getElementById('programsFab');
    const sidebar = document.getElementById('programsSidebar');
    const backdrop = document.getElementById('programsDrawerBackdrop');
    const closeDrawer = () => { sidebar?.classList.remove('drawer-open'); backdrop?.classList.remove('active'); document.body.style.overflow = ''; };
    fab?.addEventListener('click', (e) => { e.stopPropagation(); sidebar?.classList.add('drawer-open'); backdrop?.classList.add('active'); document.body.style.overflow = 'hidden'; });
    backdrop?.addEventListener('click', closeDrawer);
    window.addEventListener('resize', () => { if (window.innerWidth > 1100) closeDrawer(); });
});

window.openComposeModal = openComposeModal;
window.closeComposeModal = closeComposeModal;
window.toggleComposeFullscreen = toggleComposeFullscreen;
window.submitPost = submitPost;
window.previewImages = previewImages;
window.toggleLinkInput = toggleLinkInput;
window.toggleLike = toggleLike;
window.toggleComments = toggleComments;
window.submitComment = submitComment;
window.togglePostOptions = togglePostOptions;
window.editPost = editPost;
window.deletePost = deletePost;
window.openArchiveModal = openArchiveModal;
window.closeArchiveModal = closeArchiveModal;
window.confirmArchivePost = confirmArchivePost;
window.loadMorePosts = loadMorePosts;
window.setFeedFilter = setFeedFilter;
window.toggleNotifPopover = toggleNotifPopover;
window.toggleProfileDropdown = toggleProfileDropdown;
window.toggleSidebar = toggleSidebar;

function renderProfilePreviewModal() {
    const profile = window.AnnConfig?.profilePreview;
    if (!profile) return;

    const title = document.getElementById('profilePreviewTitle');
    const location = document.getElementById('profilePreviewLocation');
    if (title) title.textContent = `SK Barangay ${profile.name}`;
    if (location) location.textContent = profile.location || '';

    const postCount = document.getElementById('profilePreviewPostCount');
    const term = document.getElementById('profilePreviewTerm');
    const officialCount = document.getElementById('profilePreviewOfficialCount');
    if (postCount) postCount.textContent = String(profile.post_count ?? 0);
    if (term) term.textContent = profile.term_label || '—';
    if (officialCount) officialCount.textContent = String((profile.officials || []).length);

    const officialsEl = document.getElementById('profilePreviewOfficials');
    if (officialsEl) {
        const officials = profile.officials || [];
        officialsEl.innerHTML = officials.length
            ? officials.map((o) => `
                <div class="profile-preview-list-item">
                    <strong>${escapeHtml(o.name)}</strong>
                    <span>${escapeHtml(o.role)}</span>
                </div>`).join('')
            : '<p class="profile-preview-empty">No officials listed.</p>';
    }

    const postsEl = document.getElementById('profilePreviewPosts');
    if (postsEl) {
        const posts = profile.posts || [];
        postsEl.innerHTML = posts.length
            ? posts.map((p) => `
                <div class="profile-preview-post-item">
                    <div class="profile-preview-post-head">
                        <span class="profile-preview-post-type">${escapeHtml(p.type || 'update')}</span>
                        <span class="profile-preview-post-time">${escapeHtml(p.posted_time || p.time || '')}</span>
                    </div>
                    ${p.title ? `<p class="profile-preview-post-title">${escapeHtml(p.title)}</p>` : ''}
                    <p class="profile-preview-post-body">${escapeHtml(p.body || '')}</p>
                    ${p.image_url ? `<img src="${escapeHtml(p.image_url)}" alt="" class="profile-preview-post-image">` : ''}
                </div>`).join('')
            : '<p class="profile-preview-empty">No posts yet.</p>';
    }
}

function openProfilePreviewModal() {
    const modal = document.getElementById('profilePreviewModal');
    if (!modal) return;
    renderProfilePreviewModal();
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeProfilePreviewModal() {
    const modal = document.getElementById('profilePreviewModal');
    const box = document.getElementById('profilePreviewModalBox');
    if (modal) modal.style.display = 'none';
    if (box) box.classList.remove('view-modal-maximized');
    document.body.style.overflow = '';
}

function toggleProfilePreviewFullscreen() {
    const backdrop = document.getElementById('profilePreviewModal');
    const box = document.getElementById('profilePreviewModalBox');
    const btn = document.getElementById('profilePreviewToggle');
    if (!backdrop || !box) return;
    const isMax = box.classList.toggle('view-modal-maximized');
    backdrop.classList.toggle('view-modal-maximized', isMax);
    if (btn) {
        btn.textContent = isMax ? '❐' : '□';
        btn.setAttribute('aria-label', isMax ? 'Restore down' : 'Full screen');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('profilePreviewClose')?.addEventListener('click', closeProfilePreviewModal);
    document.getElementById('profilePreviewToggle')?.addEventListener('click', toggleProfilePreviewFullscreen);
    document.getElementById('profilePreviewModal')?.addEventListener('click', (e) => {
        if (e.target.id === 'profilePreviewModal') closeProfilePreviewModal();
    });
});

window.openProfilePreviewModal = openProfilePreviewModal;
window.closeProfilePreviewModal = closeProfilePreviewModal;
