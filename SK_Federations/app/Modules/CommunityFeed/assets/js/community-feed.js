/* ── SK Community Feed JS ── */
'use strict';

let currentFilter = 'all';
let feedSearch = '';
let editingPostId = null;
let pendingFiles = [];
const MAX_IMAGES = 20;
let lightboxImages = [];
let lightboxIndex = 0;
let lightboxZoom = 1;
const LIGHTBOX_ZOOM_MIN = 0.5;
const LIGHTBOX_ZOOM_MAX = 4;
const LIGHTBOX_ZOOM_STEP = 0.25;
const commentSections = new Set();
const expandedComments = new Set();

const FED_AVATAR = window.currentAvatar || 'https://ui-avatars.com/api/?name=SK+Federation&background=213F99&color=fff&size=80';

/* ── API STATE ── */
let posts      = [];
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
    if (reset) { posts = []; container.innerHTML = '<div class="post-card" style="text-align:center;color:#999;padding:32px;">Loading...</div>'; }

    var url = '/api/community-feed?per_page=100&page=1'
        + (currentFilter !== 'all' ? '&filter=' + encodeURIComponent(currentFilter) : '')
        + (feedSearch ? '&search=' + encodeURIComponent(feedSearch) : '');

    apiFetch(url, { method: 'GET' })
        .then(function(r) {
            if (!r.ok) throw new Error('Failed to load posts');
            return r.json();
        })
        .then(function(data) {
            if (reset) container.innerHTML = '';

            posts = (data.data || []).filter(function(p) { return p && p.id; });

            if (!posts.length) {
                container.innerHTML = '<div class="post-card" style="text-align:center;color:#999;padding:32px;">No posts found.</div>';
            } else {
                posts.forEach(function(p) {
                    var el = document.createElement('div');
                    el.className = 'post-card';
                    el.dataset.postId = p.id;
                    el.innerHTML = buildPost(p);
                    bindPostImageClicks(el, p);
                    container.appendChild(el);
                });
            }
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

function escapeHtml(v) {
    return String(v ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

const LIKE_THUMB_SVG = '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z"/></svg>';

function postAvatarUrl(p) {
    if (p.author_avatar_url) return p.author_avatar_url;
    if (p.is_federation_wide) return FED_AVATAR;
    return 'https://ui-avatars.com/api/?name=' + encodeURIComponent(p.author_name || 'SK') + '&background=213F99&color=fff&size=80';
}

function commentAvatarUrl(c) {
    if (c.avatar_url) return c.avatar_url;
    if (c.author_avatar_url) return c.author_avatar_url;
    return 'https://ui-avatars.com/api/?name=' + encodeURIComponent(c.author_name || 'Member') + '&background=213F99&color=fff&size=80';
}

function buildImageGrid(images, postId) {
    if (!images || !images.length) return '';

    var unique = [];
    images.forEach(function (url) {
        if (url && unique.indexOf(url) === -1) unique.push(url);
    });
    var count = unique.length;
    var gridClass = 'post-media-grid';
    var slots = [];

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
            { index: 3, overlay: '+' + (count - 3), moreOnly: true },
        ];
    }

    var tiles = slots.map(function (slot) {
        var overlayHtml = slot.overlay
            ? '<span class="post-media-more">' + escapeHtml(slot.overlay) + '</span>'
            : '';
        var tileClass = slot.moreOnly ? 'post-media-tile post-media-more-tile' : 'post-media-tile';
        var imgHtml = slot.moreOnly
            ? '<img src="' + escapeHtml(unique[slot.index]) + '" alt="" loading="lazy" aria-hidden="true">'
            : '<img src="' + escapeHtml(unique[slot.index]) + '" alt="Post image ' + (slot.index + 1) + '" loading="lazy">';
        return '<button type="button" class="' + tileClass + '" data-post-id="' + postId + '" data-index="' + slot.index + '" aria-label="View photo ' + (slot.index + 1) + '">'
            + imgHtml + overlayHtml + '</button>';
    }).join('');

    return '<div class="' + gridClass + '" data-all-images=\'' + JSON.stringify(unique).replace(/'/g, '&#39;') + '\'>' + tiles + '</div>';
}

function bindPostImageClicks(postEl, post) {
    var grid = postEl.querySelector('.post-media-grid');
    if (!grid) return;

    var images = [];
    try {
        images = JSON.parse(grid.getAttribute('data-all-images') || '[]');
    } catch (_) {
        images = [];
    }
    if (!images.length) {
        images = (post.images && post.images.length) ? post.images : (post.image_url ? [post.image_url] : []);
    }

    grid.querySelectorAll('.post-media-tile').forEach(function (tile) {
        tile.addEventListener('click', function () {
            openLightbox(images, parseInt(tile.getAttribute('data-index'), 10) || 0);
        });
    });
}

function openLightbox(images, startIndex) {
    lightboxImages = images || [];
    lightboxIndex = startIndex || 0;
    lightboxZoom = 1;
    var lb = document.getElementById('imageLightbox');
    if (!lb) return;
    lb.classList.add('active');
    document.body.style.overflow = 'hidden';
    renderLightboxImage();
    applyLightboxZoom();
}

function closeLightbox() {
    var lb = document.getElementById('imageLightbox');
    if (lb) lb.classList.remove('active');
    document.body.style.overflow = '';
    lightboxZoom = 1;
    applyLightboxZoom();
}

function applyLightboxZoom() {
    var img = document.getElementById('lightboxImage');
    var label = document.getElementById('lightboxZoomLevel');
    if (img) img.style.transform = 'scale(' + lightboxZoom + ')';
    if (label) label.textContent = Math.round(lightboxZoom * 100) + '%';
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
}

function renderLightboxImage() {
    var img = document.getElementById('lightboxImage');
    var counter = document.getElementById('lightboxCounter');
    if (!img || !lightboxImages.length) return;
    img.src = lightboxImages[lightboxIndex] || '';
    if (counter) counter.textContent = (lightboxIndex + 1) + ' / ' + lightboxImages.length;
}

function lightboxPrev() {
    if (!lightboxImages.length) return;
    lightboxIndex = (lightboxIndex - 1 + lightboxImages.length) % lightboxImages.length;
    lightboxZoom = 1;
    renderLightboxImage();
    applyLightboxZoom();
}

function lightboxNext() {
    if (!lightboxImages.length) return;
    lightboxIndex = (lightboxIndex + 1) % lightboxImages.length;
    lightboxZoom = 1;
    renderLightboxImage();
    applyLightboxZoom();
}

window.openLightbox = openLightbox;
window.closeLightbox = closeLightbox;
window.lightboxPrev = lightboxPrev;
window.lightboxNext = lightboxNext;
window.lightboxZoomIn = lightboxZoomIn;
window.lightboxZoomOut = lightboxZoomOut;
window.lightboxZoomReset = lightboxZoomReset;

function buildReactionAvatarsHtml(summary) {
    if (!summary || !summary.reactors || !summary.reactors.length) return '';
    return summary.reactors.slice(0, 3).map(function (r) {
        return '<img src="' + escapeHtml(r.avatar_url) + '" alt="" class="post-like-avatar-mini">';
    }).join('');
}

function buildStatsBar(p) {
    var likeCount = p.likes || 0;
    var commentCount = (p.comments || []).length;
    if (likeCount <= 0 && commentCount <= 0) return '';

    var likesHtml = '';
    if (likeCount > 0) {
        likesHtml = '<button type="button" class="post-stats-likes" onclick="event.stopPropagation();openLikesModal(' + p.id + ')">'
            + '<span class="post-like-avatars">' + buildReactionAvatarsHtml(p.reactions_summary) + '</span>'
            + '<span>' + likeCount + '</span>'
            + '</button>';
    }

    var commentsHtml = '';
    if (commentCount > 0) {
        commentsHtml = '<button type="button" class="post-stats-comments" onclick="event.stopPropagation();toggleComments(' + p.id + ')">'
            + commentCount + ' comment' + (commentCount === 1 ? '' : 's')
            + '</button>';
    }

    return '<div class="post-stats-bar">' + likesHtml + commentsHtml + '</div>';
}

function buildCommentsList(p) {
    var comments = p.comments || [];
    var expanded = expandedComments.has(p.id);
    var html = '';

    if (comments.length > 2 && !expanded) {
        html += '<button type="button" class="view-more-comments" onclick="expandAllComments(' + p.id + ')">'
            + 'View all ' + comments.length + ' comments</button>';
    }

    var visible = expanded ? comments : comments.slice(-2);
    html += visible.map(function (c) {
        return '<div class="comment-item">'
            + '<img src="' + escapeHtml(commentAvatarUrl(c)) + '" alt="' + escapeHtml(c.author_name) + '" class="comment-avatar">'
            + '<div class="comment-bubble">'
            + '<p class="comment-author">' + escapeHtml(c.author_name) + '</p>'
            + '<p class="comment-text">' + escapeHtml(c.body) + '</p>'
            + '</div>'
            + '<span class="comment-time">' + escapeHtml(c.time) + '</span>'
            + '</div>';
    }).join('');

    return html;
}

function buildCommentInput(p) {
    var userAvatar = window.currentAvatar || FED_AVATAR;
    return '<div class="comment-input-wrapper">'
        + '<img src="' + escapeHtml(userAvatar) + '" alt="You" class="comment-avatar">'
        + '<input type="text" class="comment-input" placeholder="Write a comment..." maxlength="500" onkeydown="submitComment(event,' + p.id + ',this)">'
        + '<button type="button" class="send-comment-btn" onclick="submitCommentBtn(' + p.id + ',this)">'
        + '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/></svg>'
        + '</button></div>';
}

function refreshCommentsSection(p) {
    var section = document.getElementById('comments-' + p.id);
    if (!section) return;
    var list = section.querySelector('.comments-list');
    if (list) list.innerHTML = buildCommentsList(p);
}

function expandAllComments(id) {
    expandedComments.add(id);
    var p = posts.find(function (x) { return x.id === id; });
    if (p) refreshCommentsSection(p);
}

window.expandAllComments = expandAllComments;

function formatLikeCountLabel(postId, count) {
    var total = count || 0;
    if (total > 0) {
        return 'Like (<span class="post-like-count-link" onclick="event.stopPropagation();openLikesModal(' + postId + ')">' + total + '</span>)';
    }
    return 'Like (0)';
}

function updateLikeCountLabel(postId, count) {
    var el = document.getElementById('like-count-' + postId);
    if (el) el.innerHTML = formatLikeCountLabel(postId, count);
}

function openLikesModal(postId) {
    var modal = document.getElementById('likesModal');
    var list = document.getElementById('likesModalList');
    var countEl = document.getElementById('likesModalCount');
    if (!modal || !list) return;

    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
    list.innerHTML = '<div class="cf-likes-loading">Loading...</div>';
    if (countEl) countEl.textContent = '0';

    apiFetch('/api/community-feed/' + postId + '/likes', { method: 'GET' })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (countEl) countEl.textContent = String(data.count || 0);
            renderLikesList(data.reactors || []);
        })
        .catch(function () {
            list.innerHTML = '<div class="cf-likes-empty">Could not load likes.</div>';
        });
}

function renderLikesList(reactors) {
    var list = document.getElementById('likesModalList');
    if (!list) return;

    if (!reactors.length) {
        list.innerHTML = '<div class="cf-likes-empty">No likes yet.</div>';
        return;
    }

    list.innerHTML = reactors.map(function (r) {
        return '<div class="cf-likes-item">'
            + '<div class="cf-likes-avatar-wrap">'
            + '<img src="' + escapeHtml(r.avatar_url) + '" alt="' + escapeHtml(r.name) + '" class="cf-likes-avatar">'
            + '<span class="cf-likes-reaction-badge">' + LIKE_THUMB_SVG + '</span>'
            + '</div>'
            + '<div class="cf-likes-user">'
            + '<p class="cf-likes-name">' + escapeHtml(r.name) + '</p>'
            + '<p class="cf-likes-role">' + escapeHtml(r.role_label || 'Member') + '</p>'
            + '</div>'
            + '</div>';
    }).join('');
}

function closeLikesModal() {
    var modal = document.getElementById('likesModal');
    if (modal) modal.classList.remove('active');
    document.body.style.overflow = '';
}

window.openLikesModal = openLikesModal;
window.closeLikesModal = closeLikesModal;

function buildPost(p) {
    var liked = p.liked || false;
    var avatar = postAvatarUrl(p);

    var images = [];
    if (p.images && p.images.length) {
        images = p.images.slice();
    } else if (p.image_url) {
        images = [p.image_url];
    }
    var mediaHtml = buildImageGrid(images, p.id);
    if (p.link_url) {
        mediaHtml += '<a href="' + escapeHtml(p.link_url) + '" target="_blank" rel="noopener" class="post-link-preview">'
            + '<svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd"/></svg>'
            + escapeHtml(p.link_url) + '</a>';
    }

    var detailsHtml = '';
    if (p.details) {
        detailsHtml = '<div class="post-details">'
            + '<div class="detail-item"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/></svg><span>' + p.details.date + '</span></div>'
            + '<div class="detail-item"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg><span>' + p.details.location + '</span></div>'
            + '</div>';
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

    var commentCount = (p.comments || []).length;
    var statsHtml = buildStatsBar(p);

    return '<div class="post-header">'
        + '<img src="' + escapeHtml(avatar) + '" alt="' + escapeHtml(p.author_name) + '" class="post-avatar">'
        + '<div class="post-info">'
        + '<h3 class="post-author">' + escapeHtml(p.author_name || 'SK Federation') + (p.barangay_name && !p.is_federation_wide ? ' <small style="font-weight:400;color:#888;">· ' + escapeHtml(p.barangay_name) + '</small>' : '') + '</h3>'
        + '<p class="post-meta"><span class="post-type ' + escapeHtml(p.type || 'update') + '">' + escapeHtml(p.type || 'update') + '</span><span class="post-time">' + escapeHtml(p.time || '') + '</span></p>'
        + '</div>' + optionsHtml + '</div>'
        + '<div class="post-content">'
        + (p.title ? '<h2 class="post-title">' + escapeHtml(p.title) + '</h2>' : '')
        + '<p class="post-text">' + escapeHtml(p.body || '') + '</p>'
        + mediaHtml
        + '</div>'
        + statsHtml
        + '<div class="post-actions">'
        + '<button class="action-btn' + (liked ? ' liked' : '') + '" onclick="toggleLike(' + p.id + ', this)">'
        + LIKE_THUMB_SVG
        + '<span id="like-count-' + p.id + '">' + formatLikeCountLabel(p.id, p.likes || 0) + '</span></button>'
        + '<button class="action-btn comment-btn" onclick="toggleComments(' + p.id + ')">'
        + '<svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"/></svg>'
        + '<span id="comment-count-' + p.id + '">Comment (' + commentCount + ')</span></button>'
        + '</div>'
        + '<div class="comments-section" id="comments-' + p.id + '" style="display:none;">'
        + '<div class="comments-list" id="comments-list-' + p.id + '">' + buildCommentsList(p) + '</div>'
        + buildCommentInput(p)
        + '</div>';
}

/* ── INTERACTIONS ── */
function toggleLike(id, btn) {
    apiFetch('/api/community-feed/' + id + '/react', { method: 'POST' })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.liked) btn.classList.add('liked');
            else btn.classList.remove('liked');

            var p = posts.find(function (x) { return x.id === id; });
            if (p) {
                p.likes = data.count;
                p.liked = data.liked;
                if (data.reactions_summary) p.reactions_summary = data.reactions_summary;
                updateLikeCountLabel(id, data.count);
                var card = document.querySelector('[data-post-id="' + id + '"]');
                if (card) {
                    var statsBar = card.querySelector('.post-stats-bar');
                    var newStats = buildStatsBar(p);
                    if (statsBar) {
                        if (newStats) statsBar.outerHTML = newStats;
                        else statsBar.remove();
                    } else if (newStats) {
                        var actions = card.querySelector('.post-actions');
                        if (actions) actions.insertAdjacentHTML('beforebegin', newStats);
                    }
                }
            }
        });
}

function toggleComments(id) {
    var section = document.getElementById('comments-' + id);
    if (!section) return;

    if (commentSections.has(id)) {
        commentSections.delete(id);
        section.style.display = 'none';
    } else {
        commentSections.add(id);
        section.style.display = 'block';
        var input = section.querySelector('.comment-input');
        if (input) input.focus();
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
    if (text.length > 500) {
        alert('Comments are limited to 500 characters.');
        return;
    }
    input.value = '';

    apiFetch('/api/community-feed/' + id + '/comment', { method: 'POST', body: JSON.stringify({ body: text }) })
        .then(function(r) {
            if (!r.ok) {
                return r.json().then(function(data) {
                    throw new Error(data.message || 'Unable to post comment.');
                });
            }
            return r.json();
        })
        .then(function(c) {
            var p = posts.find(function(x) { return x.id === id; });
            if (p) {
                if (!p.comments) p.comments = [];
                p.comments.push(c);
                commentSections.add(id);
                expandedComments.add(id);
                var section = document.getElementById('comments-' + id);
                if (section) {
                    section.style.display = 'block';
                    refreshCommentsSection(p);
                }
                var countEl = document.getElementById('comment-count-' + id);
                if (countEl) countEl.textContent = 'Comment (' + p.comments.length + ')';
                var card = document.querySelector('[data-post-id="' + id + '"]');
                if (card) {
                    var statsBar = card.querySelector('.post-stats-bar');
                    var newStats = buildStatsBar(p);
                    if (statsBar) statsBar.outerHTML = newStats;
                    else if (newStats) {
                        var actions = card.querySelector('.post-actions');
                        if (actions) actions.insertAdjacentHTML('beforebegin', newStats);
                    }
                }
            }
        })
        .catch(function(err) {
            alert(err.message || 'Unable to post comment.');
        });
}

function setFeedFilter(btn, filter) {
    currentFilter = filter;
    document.querySelectorAll('.feed-tab').forEach(function(t) { t.classList.remove('active'); });
    btn.classList.add('active');
    renderPosts(true);
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
    pendingFiles = [];
    document.getElementById('compose-modal-title').textContent = 'Edit Post';
    document.getElementById('edit-post-id').value = id;
    document.getElementById('compose-content').value = p.body;
    document.getElementById('compose-type').value = p.type;
    refreshPreviewGrid();
    if (p.link_url) {
        document.getElementById('compose-link-input').value = p.link_url;
        document.getElementById('compose-link-input-wrap').style.display = 'block';
    }
    document.querySelectorAll('.post-options-menu.open').forEach(function(m) { m.classList.remove('open'); });
    document.getElementById('composeModal').classList.add('active');
}

function submitPost() {
    var content = document.getElementById('compose-content').value.trim();
    if (!content) { alert('Please write something.'); return; }
    if (content.length > 2000) { alert('Posts are limited to 2000 characters.'); return; }

    var type = document.getElementById('compose-type').value;
    var linkVal = document.getElementById('compose-link-input').value.trim();
    var titleVal = document.getElementById('compose-title') ? document.getElementById('compose-title').value.trim() : '';

    var fd = new FormData();
    fd.append('type', type);
    fd.append('body', content);
    if (titleVal) fd.append('title', titleVal);
    if (linkVal) fd.append('link_url', linkVal);
    pendingFiles.forEach(function (file) { fd.append('images[]', file); });

    savePostForm(fd);
}

function savePostForm(formData) {
    var isEdit = editingPostId !== null;
    var url = isEdit ? '/api/community-feed/' + editingPostId : '/api/community-feed';
    var method = isEdit ? 'POST' : 'POST';
    if (isEdit) formData.append('_method', 'PUT');

    fetch(url, {
        method: method,
        headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
        credentials: 'same-origin',
        body: formData,
    })
        .then(function(r) {
            return r.json().then(function(body) {
                if (!r.ok) {
                    var msg = body.message || (body.errors ? Object.values(body.errors).flat().join(' ') : 'Unable to save post.');
                    throw new Error(msg);
                }
                return body;
            });
        })
        .then(function(saved) {
            if (!saved || !saved.id) throw new Error('Invalid response from server.');
            closeComposeModal();
            if (typeof showFeedToast === 'function') showFeedToast('Post published successfully.', 'success');
            renderPosts(true);
        })
        .catch(function(err) {
            alert(err.message || 'Unable to save post.');
        });
}

function openComposeModal(type) {
    editingPostId = null;
    pendingFiles = [];
    document.getElementById('compose-modal-title').textContent = 'Create Post';
    document.getElementById('edit-post-id').value = '';
    document.getElementById('compose-content').value = '';
    var previewWrap = document.getElementById('compose-images-preview');
    if (previewWrap) previewWrap.innerHTML = '';
    var meta = document.getElementById('compose-images-meta');
    if (meta) meta.textContent = '';
    document.getElementById('compose-link-input-wrap').style.display = 'none';
    document.getElementById('compose-link-input').value = '';
    var fileInput = document.getElementById('compose-image-input');
    if (fileInput) fileInput.value = '';
    if (type && document.getElementById('compose-type')) {
        var sel = document.getElementById('compose-type');
        var map = { announcement:'announcement', event:'event', photo:'activity' };
        sel.value = map[type] || 'update';
    }
    var modal = document.getElementById('composeModal');
    modal.classList.add('active');
    modal.classList.remove('compose-maximized');
    var btn = document.getElementById('composeFullscreenBtn');
    if (btn) {
        btn.title = 'Full screen';
        btn.setAttribute('aria-label', 'Full screen');
        btn.innerHTML = '<svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path d="M3 3h5v2H5v3H3V3zm9 0h5v5h-2V5h-3V3zM3 12h2v3h3v2H3v-5zm12 0h2v5h-5v-2h3v-3z"/></svg>';
    }
    updateCharCount();
}

function closeComposeModal() {
    var modal = document.getElementById('composeModal');
    if (modal) modal.classList.remove('active', 'compose-maximized');
    editingPostId = null;
    pendingFiles = [];
}

function toggleComposeFullscreen() {
    var modal = document.getElementById('composeModal');
    var btn = document.getElementById('composeFullscreenBtn');
    if (!modal) return;
    modal.classList.toggle('compose-maximized');
    var isMax = modal.classList.contains('compose-maximized');
    if (btn) {
        btn.title = isMax ? 'Restore down' : 'Full screen';
        btn.setAttribute('aria-label', btn.title);
        btn.innerHTML = isMax
            ? '<svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path d="M7 7H3v4h2V9h2V7zm6 0v2h2v2h2V7h-4zM7 13H5v-2H3v4h4v-2zm6 2v-2h2v-2h2v4h-4v-2z"/></svg>'
            : '<svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path d="M3 3h5v2H5v3H3V3zm9 0h5v5h-2V5h-3V3zM3 12h2v3h3v2H3v-5zm12 0h2v5h-5v-2h3v-3z"/></svg>';
    }
}

function updateCharCount() {
    var el = document.getElementById('compose-content');
    var counter = document.getElementById('compose-char-count');
    if (!el || !counter) return;
    var len = el.value.length;
    counter.textContent = len + ' / 2000';
    counter.classList.toggle('over-limit', len > 2000);
}

function previewImages(input) {
    var files = Array.from(input.files || []);
    if (!files.length) return;

    var remaining = MAX_IMAGES - pendingFiles.length;
    if (remaining <= 0) {
        alert('You can upload up to ' + MAX_IMAGES + ' images per post.');
        input.value = '';
        return;
    }

    var toAdd = files.slice(0, remaining);
    if (files.length > remaining) {
        alert('Only ' + remaining + ' more image(s) can be added (max ' + MAX_IMAGES + ').');
    }

    toAdd.forEach(function (file) {
        pendingFiles.push(file);
        var reader = new FileReader();
        reader.onload = function (e) {
            appendPreviewThumb(e.target.result, pendingFiles.length - 1);
        };
        reader.readAsDataURL(file);
    });

    input.value = '';
    updateImagesMeta();
}

function appendPreviewThumb(src, index) {
    var wrap = document.getElementById('compose-images-preview');
    if (!wrap) return;
    var item = document.createElement('div');
    item.className = 'compose-preview-item';
    item.innerHTML = '<img src="' + src + '" alt="Preview"><button type="button" class="compose-preview-remove" data-index="' + index + '" aria-label="Remove image">&times;</button>';
    item.querySelector('.compose-preview-remove').addEventListener('click', function () {
        pendingFiles.splice(parseInt(this.getAttribute('data-index'), 10), 1);
        refreshPreviewGrid();
    });
    wrap.appendChild(item);
}

function refreshPreviewGrid() {
    var wrap = document.getElementById('compose-images-preview');
    if (!wrap) return;
    wrap.innerHTML = '';
    pendingFiles.forEach(function (file, idx) {
        var reader = new FileReader();
        reader.onload = function (e) { appendPreviewThumb(e.target.result, idx); };
        reader.readAsDataURL(file);
    });
    updateImagesMeta();
}

function updateImagesMeta() {
    var meta = document.getElementById('compose-images-meta');
    if (!meta) return;
    meta.textContent = pendingFiles.length
        ? pendingFiles.length + ' image' + (pendingFiles.length === 1 ? '' : 's') + ' selected (max ' + MAX_IMAGES + ')'
        : '';
}

function toggleLinkInput() {
    var wrap = document.getElementById('compose-link-input-wrap');
    wrap.style.display = wrap.style.display === 'none' ? 'block' : 'none';
}

/* ── PROGRAM MODALS ── (defined in blade inline script for success modal support) ── */

/* ── Layout shell (sidebar, notifications, profile) provided by layout.js ── */

/* ── INIT ── */
document.addEventListener('DOMContentLoaded', function() {
    renderPosts(true);
    document.getElementById('compose-content')?.addEventListener('input', updateCharCount);
    updateCharCount();
    var searchInput = document.getElementById('feed-search-input') || document.getElementById('feedSearchInput');
    if (searchInput) {
        var searchTimer = null;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimer);
            var input = this;
            searchTimer = setTimeout(function() {
                feedSearch = input.value.trim();
                renderPosts(true);
            }, 300);
        });
    }

    // Close post option menus on outside click
    document.addEventListener('click', function(e) {
        document.querySelectorAll('.post-options-menu.open').forEach(function(m) { m.classList.remove('open'); });
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeLikesModal();
            closeLightbox();
        }
    });

    document.getElementById('lightboxClose')?.addEventListener('click', closeLightbox);
    document.getElementById('lightboxPrev')?.addEventListener('click', lightboxPrev);
    document.getElementById('lightboxNext')?.addEventListener('click', lightboxNext);
    document.getElementById('lightboxZoomIn')?.addEventListener('click', lightboxZoomIn);
    document.getElementById('lightboxZoomOut')?.addEventListener('click', lightboxZoomOut);
    document.getElementById('lightboxZoomReset')?.addEventListener('click', lightboxZoomReset);
    document.getElementById('imageLightbox')?.addEventListener('click', function (e) {
        if (e.target && e.target.id === 'imageLightbox') closeLightbox();
    });
});
