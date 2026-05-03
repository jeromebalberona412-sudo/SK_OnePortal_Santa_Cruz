/* ── SK Community Feed JS ── */
'use strict';

let currentFilter = 'all';
let currentPage   = 1;
let editingPostId = null;
let pendingImageDataUrl = null;
let pendingLinkUrl = null;
let isUploading = false;
const commentSections = new Set();

const FED_AVATAR = 'https://ui-avatars.com/api/?name=SK+Federation&background=213F99&color=fff&size=80';

/* ── API STATE ── */
let posts      = [];
let lastPage   = 1;
let isLoading  = false;

function csrfToken() {
    // Try meta tag first, then XSRF-TOKEN cookie (always fresh)
    var meta = document.querySelector('meta[name="csrf-token"]')?.content;
    if (meta) return meta;
    var match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
    return match ? decodeURIComponent(match[1]) : '';
}

function apiFetch(url, options) {
    return fetch(url, Object.assign({
        headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json', 'Content-Type': 'application/json' },
        credentials: 'same-origin',
    }, options));
}

/* ── LOAD POSTS FROM API ── */
function loadPosts(reset) {
    if (isLoading) return;
    isLoading = true;

    var container = document.getElementById('feed-posts');
    if (reset) { posts = []; currentPage = 1; container.innerHTML = '<div class="post-card" style="text-align:center;color:#999;padding:32px;">Loading...</div>'; }

    var url = '/api/community-feed?page=' + currentPage + (currentFilter !== 'all' ? '&filter=' + currentFilter : '');

    apiFetch(url, { method: 'GET' })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (reset) container.innerHTML = '';
            lastPage = data.last_page || 1;

            var newPosts = data.data || [];
            posts = reset ? newPosts : posts.concat(newPosts);

            if (!posts.length) {
                container.innerHTML = '<div class="post-card" style="text-align:center;color:#999;padding:32px;">No posts found.</div>';
            } else {
                newPosts.forEach(function(p) {
                    var el = document.createElement('div');
                    el.className = 'post-card';
                    el.dataset.postId = p.id;
                    el.innerHTML = buildPost(p);
                    container.appendChild(el);
                });
            }

            var btn = document.getElementById('load-more-btn');
            if (btn) btn.style.display = currentPage >= lastPage ? 'none' : 'inline-flex';
        })
        .catch(function() {
            if (reset) container.innerHTML = '<div class="post-card" style="text-align:center;color:#999;padding:32px;">Failed to load posts.</div>';
        })
        .finally(function() { isLoading = false; });
}

/* ── RENDER (alias kept for filter/search callers) ── */
function renderPosts(reset) {
    loadPosts(reset);
}

function buildPost(p) {
    var liked = p.liked || false;
    var showComments = commentSections.has(p.id);
    var avatar = p.is_federation_wide
        ? FED_AVATAR
        : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(p.author_name || 'SK') + '&background=213F99&color=fff&size=80';

    var mediaHtml = '';
    if (p.image_url) {
        mediaHtml += '<div class="post-image"><img src="' + p.image_url + '" alt="Post image" loading="lazy"></div>';
    }
    if (p.link_url) {
        mediaHtml += '<a href="' + p.link_url + '" target="_blank" rel="noopener" class="post-link-preview">'
            + '<svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd"/></svg>'
            + p.link_url + '</a>';
    }

    var detailsHtml = '';
    if (p.details) {
        detailsHtml = '<div class="post-details">'
            + '<div class="detail-item"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/></svg><span>' + p.details.date + '</span></div>'
            + '<div class="detail-item"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg><span>' + p.details.location + '</span></div>'
            + '</div>';
    }

    var programHtml = '';
    if (p.programInfo) {
        programHtml = '<div class="program-info">'
            + '<div class="info-badge"><svg viewBox="0 0 20 20" fill="currentColor"><path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"/></svg>' + p.programInfo.category + '</div>'
            + '<div class="info-badge"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/></svg>Deadline: ' + p.programInfo.deadline + '</div>'
            + '</div>'
            + '<button class="view-details-btn" onclick="openProgramModal(\'education\')">View Program Details &amp; Apply '
            + '<svg viewBox="0 0 20 20" fill="currentColor" style="width:16px;height:16px;"><path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>'
            + '</button>';
    }

    var optionsHtml = '';
    if (p.owned) {
        optionsHtml = '<div style="position:relative;">'
            + '<button class="post-options-btn" onclick="togglePostOptions(' + p.id + ', event)" aria-label="Post options">'
            + '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/></svg>'
            + '</button>'
            + '<div class="post-options-menu" id="options-menu-' + p.id + '">'
            + '<button onclick="editPost(' + p.id + ')"><svg viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>Edit</button>'
            + '<button class="danger" onclick="deletePost(' + p.id + ')"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>Delete</button>'
            + '</div></div>';
    }

    var commentsHtml = showComments ? buildComments(p) : '';

    return '<div class="post-header">'
        + '<img src="' + avatar + '" alt="' + p.author_name + '" class="post-avatar">'
        + '<div class="post-info">'
        + '<h3 class="post-author">' + p.author_name + (p.barangay_name && !p.is_federation_wide ? ' <small style="font-weight:400;color:#888;">· ' + p.barangay_name + '</small>' : '') + '</h3>'
        + '<p class="post-meta"><span class="post-type ' + p.type + '">' + p.type + '</span><span class="post-time">' + p.time + '</span></p>'
        + '</div>' + optionsHtml + '</div>'
        + '<div class="post-content">'
        + (p.title ? '<h2 class="post-title">' + p.title + '</h2>' : '')
        + '<p class="post-text">' + p.body + '</p>'
        + mediaHtml
        + '</div>'
        + '<div class="post-actions">'
        + '<button class="action-btn' + (liked ? ' liked' : '') + '" onclick="toggleLike(' + p.id + ', this)">'
        + '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z"/></svg>'
        + '<span id="like-count-' + p.id + '">Like (' + p.likes + ')</span></button>'
        + '<button class="action-btn comment-btn" onclick="toggleComments(' + p.id + ')">'
        + '<svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"/></svg>'
        + '<span id="comment-count-' + p.id + '">Comment (' + (p.comments ? p.comments.length : 0) + ')</span></button>'
        + '</div>'
        + '<div class="comments-section" id="comments-' + p.id + '" style="' + (showComments ? '' : 'display:none;') + '">'
        + commentsHtml + '</div>';
}

function buildComments(p) {
    var userAvatar = window.currentAvatar || FED_AVATAR;
    var items = (p.comments || []).map(function(c) {
        return '<div class="comment-item">'
            + '<img src="https://ui-avatars.com/api/?name=' + encodeURIComponent(c.author_name) + '&background=667eea&color=fff" alt="' + c.author_name + '">'
            + '<div class="comment-content">'
            + '<p class="comment-author">' + c.author_name + '</p>'
            + '<p class="comment-text">' + c.body + '</p>'
            + '<span class="comment-time">' + c.time + '</span>'
            + '</div></div>';
    }).join('');

    return items
        + '<div class="comment-input-wrapper">'
        + '<img src="' + userAvatar + '" alt="You">'
        + '<input type="text" class="comment-input" placeholder="Write a comment..." onkeydown="submitComment(event,' + p.id + ',this)">'
        + '<button class="send-comment-btn" onclick="submitCommentBtn(' + p.id + ',this)">'
        + '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/></svg>'
        + '</button></div>';
}

/* ── INTERACTIONS ── */
function toggleLike(id, btn) {
    apiFetch('/api/community-feed/' + id + '/react', { method: 'POST' })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var el = document.getElementById('like-count-' + id);
            if (el) el.textContent = 'Like (' + data.count + ')';
            if (data.liked) btn.classList.add('liked'); else btn.classList.remove('liked');
            var p = posts.find(function(x) { return x.id === id; });
            if (p) { p.likes = data.count; p.liked = data.liked; }
        });
}

function toggleComments(id) {
    var section = document.getElementById('comments-' + id);
    if (!section) return;
    var p = posts.find(function(x) { return x.id === id; });
    if (!p) return;

    if (commentSections.has(id)) {
        commentSections.delete(id);
        section.style.display = 'none';
    } else {
        commentSections.add(id);
        section.innerHTML = buildComments(p);
        section.style.display = 'block';
    }
}

function submitComment(e, id, input) {
    if (e.key === 'Enter') addComment(id, input);
}

function submitCommentBtn(id, btn) {
    var input = btn.previousElementSibling;
    if (input) addComment(id, input);
}

function addComment(id, input) {
    var text = input.value.trim();
    if (!text) return;
    input.value = '';

    apiFetch('/api/community-feed/' + id + '/comment', { method: 'POST', body: JSON.stringify({ body: text }) })
        .then(function(r) { return r.json(); })
        .then(function(c) {
            var p = posts.find(function(x) { return x.id === id; });
            if (p) {
                p.comments.push({ author_name: c.author_name, body: c.body, time: c.time });
                var section = document.getElementById('comments-' + id);
                if (section) section.innerHTML = buildComments(p);
                var countEl = document.getElementById('comment-count-' + id);
                if (countEl) countEl.textContent = 'Comment (' + p.comments.length + ')';
            }
        });
}

function setFeedFilter(btn, filter) {
    currentFilter = filter;
    document.querySelectorAll('.feed-tab').forEach(function(t) { t.classList.remove('active'); });
    btn.classList.add('active');
    renderPosts(true);
}

function loadMorePosts() {
    currentPage++;
    renderPosts(false);
}

function togglePostOptions(id, e) {
    e.stopPropagation();
    var menu = document.getElementById('options-menu-' + id);
    if (!menu) return;
    var isOpen = menu.classList.contains('open');
    document.querySelectorAll('.post-options-menu.open').forEach(function(m) { m.classList.remove('open'); });
    if (!isOpen) menu.classList.add('open');
}


function deletePost(id) {
    if (!confirm('Delete this post?')) return;
    apiFetch('/api/community-feed/' + id, { method: 'DELETE' })
        .then(function(r) { return r.json(); })
        .then(function() {
            posts = posts.filter(function(x) { return x.id !== id; });
            var el = document.querySelector('[data-post-id="' + id + '"]');
            if (el) el.remove();
            document.querySelectorAll('.post-options-menu.open').forEach(function(m) { m.classList.remove('open'); });
        });
}

function editPost(id) {
    var p = posts.find(function(x) { return x.id === id; });
    if (!p) return;
    editingPostId = id;
    document.getElementById('compose-modal-title').textContent = 'Edit Post';
    document.getElementById('edit-post-id').value = id;
    document.getElementById('compose-content').value = p.body;
    document.getElementById('compose-type').value = p.type;
    pendingImageDataUrl = p.image_url || null;
    pendingLinkUrl = p.link_url || null;

    if (p.image_url) {
        document.getElementById('compose-preview-img').src = p.image_url;
        document.getElementById('compose-image-preview').style.display = 'block';
    }
    if (p.link_url) {
        document.getElementById('compose-link-input').value = p.link_url;
        document.getElementById('compose-link-input-wrap').style.display = 'block';
    }

    document.querySelectorAll('.post-options-menu.open').forEach(function(m) { m.classList.remove('open'); });
    document.getElementById('composeModal').classList.add('active');
}

function submitPost() {
    if (isUploading) { alert('Please wait for the image to finish uploading.'); return; }
    var content = document.getElementById('compose-content').value.trim();
    if (!content) { alert('Please write something.'); return; }

    var type     = document.getElementById('compose-type').value;
    var linkVal  = document.getElementById('compose-link-input').value.trim();
    var titleVal = document.getElementById('compose-title') ? document.getElementById('compose-title').value.trim() : '';
    pendingLinkUrl = linkVal || null;

    var payload = { type: type, body: content, link_url: pendingLinkUrl, title: titleVal || null };

    if (pendingImageDataUrl) payload.image_url = pendingImageDataUrl;
    savePost(payload);
}

function dataURLtoFile(dataUrl, filename) {
    var arr = dataUrl.split(','), mime = arr[0].match(/:(.*?);/)[1];
    var bstr = atob(arr[1]), n = bstr.length, u8arr = new Uint8Array(n);
    while (n--) u8arr[n] = bstr.charCodeAt(n);
    return new File([u8arr], filename, { type: mime });
}

function savePost(payload) {
    var isEdit  = editingPostId !== null;
    var url     = isEdit ? '/api/community-feed/' + editingPostId : '/api/community-feed';
    var method  = isEdit ? 'PUT' : 'POST';

    apiFetch(url, { method: method, body: JSON.stringify(payload) })
        .then(function(r) { return r.json(); })
        .then(function(saved) {
            if (isEdit) {
                var idx = posts.findIndex(function(x) { return x.id === saved.id; });
                if (idx !== -1) posts[idx] = saved;
                var el = document.querySelector('[data-post-id="' + saved.id + '"]');
                if (el) el.innerHTML = buildPost(saved);
            } else {
                posts.unshift(saved);
                var container = document.getElementById('feed-posts');
                var el = document.createElement('div');
                el.className = 'post-card';
                el.dataset.postId = saved.id;
                el.innerHTML = buildPost(saved);
                container.insertBefore(el, container.firstChild);
            }
            closeComposeModal();
        });
}

function openComposeModal(type) {
    editingPostId = null;
    pendingImageDataUrl = null;
    pendingLinkUrl = null;
    document.getElementById('compose-modal-title').textContent = 'Create Post';
    document.getElementById('edit-post-id').value = '';
    document.getElementById('compose-content').value = '';
    document.getElementById('compose-image-preview').style.display = 'none';
    document.getElementById('compose-link-input-wrap').style.display = 'none';
    document.getElementById('compose-link-input').value = '';
    if (type && document.getElementById('compose-type')) {
        var sel = document.getElementById('compose-type');
        var map = { announcement:'announcement', event:'event', photo:'activity' };
        sel.value = map[type] || 'update';
    }
    document.getElementById('composeModal').classList.add('active');
}

function closeComposeModal() {
    document.getElementById('composeModal').classList.remove('active');
    editingPostId = null;
    pendingImageDataUrl = null;
    pendingLinkUrl = null;
    isUploading = false;
}

function previewImage(input) {
    var file = input.files[0];
    if (!file) return;

    // Show local preview immediately
    var reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('compose-preview-img').src = e.target.result;
        document.getElementById('compose-image-preview').style.display = 'block';
    };
    reader.readAsDataURL(file);

    // Upload to server and store the returned URL
    isUploading = true;
    var postBtn = document.querySelector('.modal-footer-btns .btn-primary');
    if (postBtn) { postBtn.disabled = true; postBtn.textContent = 'Uploading…'; }

    var fd = new FormData();
    fd.append('image', file);
    fetch('/api/community-feed/upload-image', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
        credentials: 'same-origin',
        body: fd,
    })
    .then(function(r) {
        if (!r.ok) {
            return r.text().then(function(text) {
                try {
                    var json = JSON.parse(text);
                    throw new Error(json.message || 'HTTP ' + r.status);
                } catch (e) {
                    throw new Error('HTTP ' + r.status + ': ' + text.substring(0, 200));
                }
            });
        }
        return r.json();
    })
    .then(function(data) {
        if (data.url) {
            pendingImageDataUrl = data.url;
            document.getElementById('compose-preview-img').src = data.url;
        } else {
            pendingImageDataUrl = null;
            alert('Image upload failed: ' + (data.message || 'No URL returned'));
        }
    })
    .catch(function(err) {
        pendingImageDataUrl = null;
        alert('Image upload failed: ' + err.message);
    })
    .finally(function() {
        isUploading = false;
        if (postBtn) { postBtn.disabled = false; postBtn.textContent = 'Post'; }
    });
}

function removeImagePreview() {
    pendingImageDataUrl = null;
    document.getElementById('compose-image-preview').style.display = 'none';
    document.getElementById('compose-image-input').value = '';
}

function toggleLinkInput() {
    var wrap = document.getElementById('compose-link-input-wrap');
    wrap.style.display = wrap.style.display === 'none' ? 'block' : 'none';
}

/* ── PROGRAM MODALS ── (defined in blade inline script for success modal support) ── */

/* ── NOTIFICATIONS ── */
function toggleNotifPopover(e) {
    e.stopPropagation();
    const pop = document.getElementById('notifPopover');
    const dd  = document.getElementById('profileDropdown');
    dd?.classList.remove('show');
    document.querySelector('.profile-btn')?.classList.remove('open');
    pop?.classList.toggle('show');
}

/* ── SIDEBAR TOGGLE — toggles body.sidebar-collapsed to match dashboard.css ── */
function toggleSidebar() {
    const isMobile = window.innerWidth <= 1024;
    if (isMobile) {
        document.body.classList.toggle('sidebar-open');
    } else {
        document.body.classList.toggle('sidebar-collapsed');
        localStorage.setItem('sidebarCollapsed', document.body.classList.contains('sidebar-collapsed'));
    }
}

function toggleProfileDropdown(e) {
    e.stopPropagation();
    const dd  = document.getElementById('profileDropdown');
    const pop = document.getElementById('notifPopover');
    const btn = document.querySelector('.profile-btn');
    pop?.classList.remove('show');
    dd?.classList.toggle('show');
    btn?.classList.toggle('open');
}

/* ── INIT ── */
document.addEventListener('DOMContentLoaded', function() {
    renderPosts(true);

    // Restore sidebar collapsed state
    if (window.innerWidth > 1024 && localStorage.getItem('sidebarCollapsed') === 'true') {
        document.body.classList.add('sidebar-collapsed');
    }

    // Search
    var searchInput = document.getElementById('feed-search-input');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            var q = this.value.toLowerCase().trim();
            var container = document.getElementById('feed-posts');
            if (!q) { renderPosts(true); return; }
            container.innerHTML = '';
            var filtered = posts.filter(function(p) {
                return (p.title && p.title.toLowerCase().includes(q))
                    || (p.body && p.body.toLowerCase().includes(q))
                    || (p.author_name && p.author_name.toLowerCase().includes(q))
                    || p.type.toLowerCase().includes(q);
            });
            if (!filtered.length) {
                container.innerHTML = '<div class="post-card" style="text-align:center;color:#999;padding:32px;">No posts found.</div>';
                return;
            }
            filtered.forEach(function(p) {
                var el = document.createElement('div');
                el.className = 'post-card';
                el.dataset.postId = p.id;
                el.innerHTML = buildPost(p);
                container.appendChild(el);
            });
            var btn = document.getElementById('load-more-btn');
            if (btn) btn.style.display = 'none';
        });
    }

    // Close dropdowns on outside click
    document.addEventListener('click', function() {
        document.querySelectorAll('.post-options-menu.open').forEach(function(m) { m.classList.remove('open'); });
        document.getElementById('notifPopover')?.classList.remove('show');
        document.getElementById('profileDropdown')?.classList.remove('show');
        document.querySelector('.profile-btn')?.classList.remove('open');
    });

    // Programs drawer (mobile FAB)
    var fab      = document.getElementById('programsFab');
    var sidebar  = document.getElementById('programsSidebar');
    var backdrop = document.getElementById('programsDrawerBackdrop');

    function openProgramsDrawer() {
        sidebar?.classList.add('drawer-open');
        backdrop?.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    function closeProgramsDrawer() {
        sidebar?.classList.remove('drawer-open');
        backdrop?.classList.remove('active');
        document.body.style.overflow = '';
    }

    fab?.addEventListener('click', function(e) {
        e.stopPropagation();
        openProgramsDrawer();
    });
    backdrop?.addEventListener('click', closeProgramsDrawer);

    // Swipe-to-close on the drawer
    if (sidebar) {
        var touchStartX = 0;
        sidebar.addEventListener('touchstart', function(e) {
            touchStartX = e.touches[0].clientX;
        }, { passive: true });
        sidebar.addEventListener('touchend', function(e) {
            var dx = e.changedTouches[0].clientX - touchStartX;
            if (dx > 60) closeProgramsDrawer(); // swipe right to close
        }, { passive: true });
    }

    // Close drawer when viewport grows past tablet breakpoint
    window.addEventListener('resize', function() {
        if (window.innerWidth > 1100) closeProgramsDrawer();
    });
});
