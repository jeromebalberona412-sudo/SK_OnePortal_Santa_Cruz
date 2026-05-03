'use strict';

const POSTS_PER_PAGE = 10;
let currentFilter  = 'all';
let currentPage    = 1;
let lastPage       = 1;
let editingPostId  = null;
let pendingImageUrl = null;
let pendingLinkUrl  = null;
let currentUserId  = null;

const SK_AVATAR = 'https://ui-avatars.com/api/?name=SK+Officials&background=2c2c3e&color=f5c518&size=80';

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

// ── API helpers ──────────────────────────────────────────────────────────────

async function apiFetch(url, options = {}) {
    const { headers: extraHeaders, ...rest } = options;
    const res = await fetch(url, {
        ...rest,
        headers: {
            'X-CSRF-TOKEN': csrfToken(),
            'Accept': 'application/json',
            ...extraHeaders,
        },
    });
    if (!res.ok) throw new Error(await res.text());
    return res.json();
}

// ── Feed ─────────────────────────────────────────────────────────────────────

async function loadFeed(reset = true) {
    if (reset) { currentPage = 1; document.getElementById('feed-posts').innerHTML = ''; }

    const params = new URLSearchParams({ page: currentPage, filter: currentFilter });
    const data   = await apiFetch(`/api/announcements?${params}`);

    currentUserId = data.user_id;
    lastPage      = data.last_page;

    data.data.forEach(p => appendPost(p));

    const btn = document.getElementById('load-more-btn');
    if (btn) btn.style.display = currentPage >= lastPage ? 'none' : 'inline-flex';
}

function loadMorePosts() { currentPage++; loadFeed(false); }

function setFeedFilter(btn, filter) {
    currentFilter = filter;
    document.querySelectorAll('.feed-tab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');
    loadFeed(true);
}

// ── Render ───────────────────────────────────────────────────────────────────

function appendPost(p) {
    const container = document.getElementById('feed-posts');
    const el = document.createElement('article');
    el.className   = 'post-card';
    el.dataset.postId = p.id;
    el.innerHTML   = buildPost(p);
    container.appendChild(el);
}

function buildPost(p) {
    const avatar    = `https://ui-avatars.com/api/?name=${encodeURIComponent(p.barangay_name ?? 'SK')}&background=2c2c3e&color=f5c518&size=80`;
    const mediaHtml = p.image_url
        ? `<div class="post-image"><img src="${p.image_url}" alt="Post image" loading="lazy"></div>` : '';
    const linkHtml  = p.link_url
        ? `<a href="${p.link_url}" target="_blank" rel="noopener" class="post-link-preview">${p.link_url}</a>` : '';
    const optionsHtml = p.owned
        ? `<div style="position:relative;">
            <button class="post-options-btn" onclick="togglePostOptions(${p.id},event)">
              <svg viewBox="0 0 20 20" fill="currentColor"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/></svg>
            </button>
            <div class="post-options-menu" id="options-menu-${p.id}">
              <button onclick="editPost(${p.id})"><svg viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>Edit</button>
              <button class="danger" onclick="deletePost(${p.id})"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>Delete</button>
            </div>
           </div>` : '';

    const commentsHtml = (p.comments ?? []).map(c =>
        `<div class="comment-item">
           <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(c.author_name)}&background=667eea&color=fff" alt="${c.author_name}">
           <div class="comment-content">
             <p class="comment-author">${c.author_name}</p>
             <p class="comment-text">${c.body}</p>
             <span class="comment-time">${c.time}</span>
           </div>
         </div>`
    ).join('');

    return `
      <div class="post-header">
        <img src="${avatar}" alt="${p.barangay_name}" class="post-avatar">
        <div class="post-info">
          <h3 class="post-author">${p.author_name ?? ('SK Brgy. ' + (p.barangay_name ?? ''))}</h3>
          <p class="post-meta">
            <span class="post-type ${p.type}">${p.type}</span>
            <span class="post-time">${p.time ?? ''}</span>
          </p>
        </div>
        ${optionsHtml}
      </div>
      <div class="post-content">
        ${p.title ? `<h2 class="post-title">${p.title}</h2>` : ''}
        <p class="post-text">${p.body}</p>
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
        <div id="comments-list-${p.id}">${commentsHtml}</div>
        <div class="comment-input-wrapper">
          <img src="${SK_AVATAR}" alt="You">
          <input type="text" class="comment-input" placeholder="Write a comment..." onkeydown="if(event.key==='Enter')submitComment(${p.id},this)">
          <button class="send-comment-btn" onclick="submitComment(${p.id},this.previousElementSibling)">
            <svg viewBox="0 0 20 20" fill="currentColor"><path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/></svg>
          </button>
        </div>
      </div>`;
}

// ── Interactions ─────────────────────────────────────────────────────────────

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
               <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(comment.author_name)}&background=2c2c3e&color=f5c518" alt="${comment.author_name}">
               <div class="comment-content">
                 <p class="comment-author">${comment.author_name}</p>
                 <p class="comment-text">${comment.body}</p>
                 <span class="comment-time">${comment.time}</span>
               </div>
             </div>`
        );
    }
    const countEl = document.getElementById(`comment-count-${id}`);
    if (countEl) {
        const cur = parseInt(countEl.textContent.match(/\d+/)?.[0] ?? '0');
        countEl.textContent = `Comment (${cur + 1})`;
    }
}

// ── Compose / Edit ───────────────────────────────────────────────────────────

function openComposeModal(type) {
    editingPostId = null; pendingImageUrl = null; pendingLinkUrl = null;
    document.getElementById('compose-modal-title').textContent = 'Create Post';
    document.getElementById('edit-post-id').value = '';
    document.getElementById('compose-content').value = '';
    document.getElementById('compose-image-preview').style.display = 'none';
    document.getElementById('compose-link-input-wrap').style.display = 'none';
    document.getElementById('compose-link-input').value = '';
    if (type) {
        const map = { announcement: 'announcement', event: 'event', photo: 'activity' };
        document.getElementById('compose-type').value = map[type] ?? 'update';
    }
    document.getElementById('composeModal').classList.add('active');
}

function closeComposeModal() {
    document.getElementById('composeModal').classList.remove('active');
    editingPostId = null; pendingImageUrl = null; pendingLinkUrl = null;
}

async function editPost(id) {
    // Fetch fresh data from the feed container
    const card = document.querySelector(`[data-post-id="${id}"]`);
    if (!card) return;
    editingPostId = id;
    document.getElementById('compose-modal-title').textContent = 'Edit Post';
    document.getElementById('edit-post-id').value = id;
    document.getElementById('compose-content').value = card.querySelector('.post-text')?.textContent ?? '';
    document.getElementById('composeModal').classList.add('active');
    document.querySelectorAll('.post-options-menu.open').forEach(m => m.classList.remove('open'));
}

async function deletePost(id) {
    if (!confirm('Delete this post?')) return;
    await apiFetch(`/api/announcements/${id}`, { method: 'DELETE' });
    document.querySelector(`[data-post-id="${id}"]`)?.remove();
    document.querySelectorAll('.post-options-menu.open').forEach(m => m.classList.remove('open'));
}

async function submitPost() {
    const body  = document.getElementById('compose-content').value.trim();
    if (!body) { alert('Please write something.'); return; }
    const type  = document.getElementById('compose-type').value;
    const title = '';
    const link  = document.getElementById('compose-link-input').value.trim() || null;

    const payload = { type, title, body, image_url: pendingImageUrl, link_url: link };

    try {
        if (editingPostId) {
            const updated = await apiFetch(`/api/announcements/${editingPostId}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });
            const card = document.querySelector(`[data-post-id="${editingPostId}"]`);
            if (card) card.innerHTML = buildPost(updated);
        } else {
            const created = await apiFetch('/api/announcements', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });
            const container = document.getElementById('feed-posts');
            const el = document.createElement('article');
            el.className = 'post-card';
            el.dataset.postId = created.id;
            el.innerHTML = buildPost(created);
            container.prepend(el);
        }
        closeComposeModal();
    } catch (e) {
        alert('Failed to save post. Please try again.\n' + e.message);
    }
}

// ── Image upload ─────────────────────────────────────────────────────────────

function previewImage(input) {
    const file = input.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('compose-preview-img').src = e.target.result;
        document.getElementById('compose-image-preview').style.display = 'block';
    };
    reader.readAsDataURL(file);

    // Disable Post button until upload completes
    const postBtn = document.querySelector('.modal-footer-btns .btn-primary');
    if (postBtn) { postBtn.disabled = true; postBtn.textContent = 'Uploading…'; }

    const fd = new FormData();
    fd.append('image', file);
    fd.append('_token', csrfToken());
    fetch('/api/announcements/upload-image', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => {
            if (d.url) {
                pendingImageUrl = d.url;
                document.getElementById('compose-preview-img').src = d.url;
            }
        })
        .catch(() => {})
        .finally(() => {
            if (postBtn) { postBtn.disabled = false; postBtn.innerHTML = '<svg viewBox="0 0 20 20" fill="currentColor" style="width:16px;height:16px;"><path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/></svg> Post'; }
        });
}

function removeImagePreview() {
    pendingImageUrl = null;
    document.getElementById('compose-image-preview').style.display = 'none';
    document.getElementById('compose-image-input').value = '';
}

function toggleLinkInput() {
    const wrap = document.getElementById('compose-link-input-wrap');
    wrap.style.display = wrap.style.display === 'none' ? 'block' : 'none';
}

function togglePostOptions(id, e) {
    e.stopPropagation();
    const menu   = document.getElementById(`options-menu-${id}`);
    const isOpen = menu?.classList.contains('open');
    document.querySelectorAll('.post-options-menu.open').forEach(m => m.classList.remove('open'));
    if (!isOpen) menu?.classList.add('open');
}

// ── Sidebar / UI helpers (unchanged) ─────────────────────────────────────────

function toggleNotifPopover(e) { e.stopPropagation(); const pop = document.getElementById('notifPopover'); const dd = document.getElementById('profileDropdown'); dd?.classList.remove('show'); document.querySelector('.profile-btn')?.classList.remove('open'); pop?.classList.toggle('show'); }
function toggleProfileDropdown(e) { e.stopPropagation(); const dd = document.getElementById('profileDropdown'); const pop = document.getElementById('notifPopover'); const btn = document.querySelector('.profile-btn'); pop?.classList.remove('show'); dd?.classList.toggle('show'); btn?.classList.toggle('open'); }
function toggleSidebar() { const isMobile = window.innerWidth <= 1024; if (isMobile) { document.body.classList.toggle('sidebar-open'); } else { document.body.classList.toggle('sidebar-collapsed'); localStorage.setItem('sidebarCollapsed', document.body.classList.contains('sidebar-collapsed')); } }

document.addEventListener('DOMContentLoaded', () => {
    loadFeed(true);
    if (window.innerWidth > 1024 && localStorage.getItem('sidebarCollapsed') === 'true') {
        document.body.classList.add('sidebar-collapsed');
    }
    document.addEventListener('click', () => {
        document.querySelectorAll('.post-options-menu.open').forEach(m => m.classList.remove('open'));
        document.getElementById('notifPopover')?.classList.remove('show');
        document.getElementById('profileDropdown')?.classList.remove('show');
        document.querySelector('.profile-btn')?.classList.remove('open');
    });
    const fab      = document.getElementById('programsFab');
    const sidebar  = document.getElementById('programsSidebar');
    const backdrop = document.getElementById('programsDrawerBackdrop');
    const openDrawer  = () => { sidebar?.classList.add('drawer-open'); backdrop?.classList.add('active'); document.body.style.overflow = 'hidden'; };
    const closeDrawer = () => { sidebar?.classList.remove('drawer-open'); backdrop?.classList.remove('active'); document.body.style.overflow = ''; };
    fab?.addEventListener('click', e => { e.stopPropagation(); openDrawer(); });
    backdrop?.addEventListener('click', closeDrawer);
    window.addEventListener('resize', () => { if (window.innerWidth > 1100) closeDrawer(); });
});

// Expose functions needed by inline onclick attributes
window.openComposeModal    = openComposeModal;
window.closeComposeModal   = closeComposeModal;
window.submitPost          = submitPost;
window.previewImage        = previewImage;
window.removeImagePreview  = removeImagePreview;
window.toggleLinkInput     = toggleLinkInput;
window.toggleLike          = toggleLike;
window.toggleComments      = toggleComments;
window.submitComment       = submitComment;
window.togglePostOptions   = togglePostOptions;
window.editPost            = editPost;
window.deletePost          = deletePost;
window.loadMorePosts       = loadMorePosts;
window.setFeedFilter       = setFeedFilter;
window.toggleNotifPopover  = toggleNotifPopover;
window.toggleProfileDropdown = toggleProfileDropdown;
window.toggleSidebar       = toggleSidebar;
