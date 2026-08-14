'use strict';

const POSTS_PER_PAGE = 10;
const MAX_BODY_CHARS = 2000;
const MAX_IMAGES = 20;
const REACTION_EMOJI = {
    like: '👍',
    love: '❤️',
    haha: '😂',
    wow: '😮',
    sad: '😢',
    angry: '😡',
};
const REACTION_LABEL = {
    like: 'Like',
    love: 'Love',
    haha: 'Haha',
    wow: 'Wow',
    sad: 'Sad',
    angry: 'Angry',
};
const THUMBS_SVG = '<svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z"/></svg>';

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
const postCache = new Map();

const SK_AVATAR = () => window.CommunityFeedConfig?.userAvatar || window.CommunityFeedConfig?.barangayLogo || DEFAULT_LOGO();
const DEFAULT_LOGO = () => window.CommunityFeedConfig?.defaultLogo || '/images/logo.png';

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

const FEED_REACTION_SOUND_URL = '/sounds/reactions_ux.mp3';
let feedReactionAudio = null;

function ensureFeedReactionAudio() {
    if (!feedReactionAudio) {
        feedReactionAudio = new Audio(FEED_REACTION_SOUND_URL);
        feedReactionAudio.preload = 'auto';
        feedReactionAudio.volume = 0.75;
        try { feedReactionAudio.load(); } catch (e) {}
    }
    return feedReactionAudio;
}

function playFeedReactionSound() {
    const audio = ensureFeedReactionAudio();
    audio.muted = false;
    audio.volume = 0.75;
    try {
        if (audio.readyState >= 2) {
            try { audio.currentTime = 0; } catch (e) {}
        }
        const playPromise = audio.play();
        if (playPromise && playPromise.catch) {
            playPromise.catch(() => {
                const oneShot = new Audio(FEED_REACTION_SOUND_URL);
                oneShot.volume = 0.75;
                oneShot.play().catch(() => {});
            });
        }
    } catch (e) {
        try {
            const fallback = new Audio(FEED_REACTION_SOUND_URL);
            fallback.volume = 0.75;
            fallback.play().catch(() => {});
        } catch (err) {}
    }
}

window.playFeedReactionSound = playFeedReactionSound;
ensureFeedReactionAudio();

function resolveNextReaction(currentType, requestedType) {
    const requested = requestedType || 'like';
    const current = currentType || '';
    if (current === requested) return { liked: false, type: '' };
    return { liked: true, type: requested };
}

function bumpReactionCounts(counts, fromType, toType) {
    const next = { ...(counts || {}) };
    if (fromType && fromType !== toType) {
        next[fromType] = Math.max(0, Number(next[fromType] || 0) - 1);
    }
    if (toType && fromType !== toType) {
        next[toType] = Number(next[toType] || 0) + 1;
    }
    return next;
}

const reactionRequestSeq = new Map();
const reactionAbort = new Map();

function beginReactionRequest(key) {
    const seq = (reactionRequestSeq.get(key) || 0) + 1;
    reactionRequestSeq.set(key, seq);
    reactionAbort.get(key)?.abort();
    const controller = new AbortController();
    reactionAbort.set(key, controller);
    return { seq, signal: controller.signal };
}

function isLatestReactionRequest(key, seq) {
    return reactionRequestSeq.get(key) === seq;
}

function notifyFeed(message, type = 'success') {
    if (typeof window.showFeedToast === 'function') {
        window.showFeedToast(message, type);
        return;
    }
    const el = document.getElementById('feedToast');
    if (!el) return;
    el.textContent = message;
    el.className = 'feed-toast feed-toast--' + type + ' is-visible';
}

function isReplyComment(commentId) {
    return Boolean(
        document.querySelector(`.comment-item.is-reply[data-comment-id="${commentId}"]`)
        || document.querySelector(`.cp-comment.is-reply[data-comment-id="${commentId}"]`)
    );
}

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
        const data = await apiFetch(`/api/community-feed?${params}`);

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
    if (id) postCache.set(id, p);
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
    bindReactionControls(el);

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
        const data = await apiFetch(`/api/community-feed?${params}`);
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
    const interval = window.CommunityFeedConfig?.feedPollMs || 30000;
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

function scrollFeedHome() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function setFeedFilter(btn, filter) {
    if (feedLoading) return;

    const goHome = filter === 'all';
    currentFilter = filter;
    document.querySelectorAll('.feed-tab').forEach((t) => t.classList.remove('active'));
    btn.classList.add('active');

    if (goHome) {
        feedSearch = '';
        const search = document.getElementById('feedSearchInput');
        if (search) search.value = '';
    }

    setFilterTabsDisabled(true);
    loadFeed(true).then(() => {
        if (goHome) scrollFeedHome();
    });
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
        ? `<div class="post-options-wrap">
            <button type="button" class="post-options-btn" onclick="togglePostOptions(${p.id},event)" aria-label="Post options" title="More">
              <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </button>
            <div class="post-options-menu" id="options-menu-${p.id}">
              <button type="button" onclick="editPost(${p.id})"><svg viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>Edit</button>
              <button type="button" class="danger" onclick="openArchiveModal(${p.id})"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>Delete</button>
            </div>
           </div>` : '';

    const comments = p.comments ?? [];
    const commentsHtml = comments.map((c) => buildCommentItem(c, p.id)).join('');
    const totalComments = Number(p.comment_count ?? countComments(comments));
    const featured = pickFeaturedComment(comments);
    const reactionType = p.reaction_type || '';
    const reactionIcon = reactionType
        ? `<span class="reaction-current">${escapeHtml(REACTION_EMOJI[reactionType] || '👍')}</span>`
        : THUMBS_SVG;
    const reactionLabel = reactionType ? (REACTION_LABEL[reactionType] || 'Like') : 'Like';

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
      <div class="post-engage">
        <div class="reaction-summary" id="reaction-summary-${p.id}" data-comments="${totalComments}" data-reactions="${Number(p.likes || 0)}" data-counts="${escapeHtml(JSON.stringify(p.reaction_counts || {}))}">${buildReactionSummaryHtml(p.reaction_counts, p.likes, totalComments, p.id)}</div>
        <div class="post-actions">
        <div class="reaction-wrap" data-target="post" data-post-id="${p.id}">
          <button type="button" class="action-btn reaction-btn${p.liked ? ' liked' : ''}" id="reaction-btn-${p.id}" data-type="${escapeHtml(reactionType)}" aria-label="${escapeHtml(reactionLabel)}">
            <span class="reaction-icon">${reactionIcon}</span>
            <span class="reaction-label">${escapeHtml(reactionLabel)}</span>
          </button>
          <div class="reaction-picker" id="reaction-picker-${p.id}">
            <div class="reaction-picker-inner">
              ${Object.entries(REACTION_EMOJI).map(([type, emoji]) =>
                  `<button type="button" class="reaction-option${reactionType === type ? ' is-active' : ''}" data-type="${type}" title="${type}">${emoji}</button>`
              ).join('')}
            </div>
          </div>
        </div>
        <button type="button" class="action-btn comment-btn" onclick="toggleComments(${p.id})">
          <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"/></svg>
          <span id="comment-count-${p.id}" data-count="${totalComments}">Comment</span>
        </button>
        </div>
      </div>
      ${buildCommentPreviewHtml(featured, p.id, totalComments)}
      <div class="comments-section" id="comments-${p.id}" style="display:none;">
        <div class="comments-list" id="comments-list-${p.id}">${commentsHtml}</div>
        <div class="comment-input-wrapper">
          <img src="${escapeHtml(SK_AVATAR())}" alt="You" class="comment-avatar">
          <input type="text" class="comment-input" placeholder="Write a comment..." maxlength="500" onkeydown="handleCommentKey(event, ${p.id}, this)">
          <button type="button" class="send-comment-btn" onclick="submitComment(${p.id},this.previousElementSibling)">
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

function countComments(comments) {
    return (comments || []).reduce((sum, comment) => sum + 1 + countComments(comment.replies || []), 0);
}

function flattenComments(comments) {
    return (comments || []).reduce((list, comment) => {
        list.push(comment);
        return list.concat(flattenComments(comment.replies || []));
    }, []);
}

function pickFeaturedComment(comments) {
    const all = flattenComments(comments);
    if (!all.length) return null;
    return all.reduce((best, current) => {
        const bestLikes = Number(best.likes || 0);
        const currentLikes = Number(current.likes || 0);
        if (currentLikes > bestLikes) return current;
        if (currentLikes === bestLikes && Number(current.id) > Number(best.id)) return current;
        return best;
    });
}

function commentLikeInner(type) {
    const label = type ? (REACTION_LABEL[type] || 'Like') : 'Like';
    if (type && type !== 'like') {
        return `<span class="comment-react-emoji">${REACTION_EMOJI[type] || ''}</span><span>${escapeHtml(label)}</span>`;
    }
    return `<span>${escapeHtml(label)}</span>`;
}

function buildCommentPreviewHtml(comment, postId, total) {
    const count = Number(total || 0);
    if (!comment || count <= 0) {
        return `<div class="comment-preview" id="comment-preview-${postId}" hidden role="button" tabindex="0" onclick="openComments(${postId})"></div>`;
    }
    const likes = Number(comment.likes || 0);
    const replyCount = Number(comment.reply_count || (comment.replies || []).length || 0);
    const badge = likes > 0
        ? `<span class="comment-react-badge comment-react-inline">${buildCommentBadgeHtml(comment.reaction_counts, likes)}</span>`
        : '';
    const more = count > 1
        ? `<span class="comment-preview-more">View all ${count} comments</span>`
        : '';
    const replies = replyCount > 0
        ? `<span class="fb-view-replies"><svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>View ${replyCount} ${replyCount === 1 ? 'reply' : 'replies'}</span>`
        : '';
    return `<div class="comment-preview" id="comment-preview-${postId}" role="button" tabindex="0" onclick="openComments(${postId})">
        ${more}
        <div class="fb-comment-row">
            <img src="${escapeHtml(commentAvatarUrl(comment))}" alt="" class="comment-avatar">
            <div class="fb-comment-main">
                <div class="fb-comment-head">
                    <span class="comment-author">${escapeHtml(comment.author_name)}</span>
                    <span class="fb-comment-dot">·</span>
                    <span class="comment-time">${escapeHtml(comment.time || '')}</span>
                </div>
                <p class="comment-text">${escapeHtml(comment.body)}</p>
                ${badge}
                <div class="comment-meta">
                    <span class="comment-like-btn">${commentLikeInner(comment.reaction_type)}</span>
                    <span class="comment-action-btn">Reply</span>
                    ${replies}
                </div>
            </div>
        </div>
    </div>`;
}

function reactionPickerHtml(activeType, pickerId) {
    return `<div class="reaction-picker" id="${pickerId}">
        <div class="reaction-picker-inner">
            ${Object.entries(REACTION_EMOJI).map(([type, emoji]) =>
                `<button type="button" class="reaction-option${activeType === type ? ' is-active' : ''}" data-type="${type}" title="${type}">${emoji}</button>`
            ).join('')}
        </div>
    </div>`;
}

function reactionFacesHtml(counts) {
    return topReactionTypes(counts)
        .map((type) => `<span class="reaction-face reaction-face--${type}" title="${escapeHtml(REACTION_LABEL[type] || type)}">${REACTION_EMOJI[type] || ''}</span>`)
        .join('');
}

function buildCommentBadgeHtml(counts, total) {
    const n = Number(total || 0);
    if (n <= 0) return '';
    return `<span class="reaction-faces">${reactionFacesHtml(counts)}</span><span class="reaction-total">${formatCount(n)}</span>`;
}

function buildCommentItem(comment, postId, isReply = false) {
    const nested = comment.replies || [];
    const repliesHtml = nested.map((reply) => buildCommentItem(reply, postId, true)).join('');
    const replyCount = nested.length;
    const reactionType = comment.reaction_type || '';
    const likes = Number(comment.likes || 0);
    const badgeHtml = buildCommentBadgeHtml(comment.reaction_counts, likes);
    const optionsHtml = comment.owned
        ? `<div class="comment-options-wrap">
            <button type="button" class="comment-options-btn" onclick="toggleCommentOptions(${comment.id}, event)" aria-label="Comment options">
              <svg viewBox="0 0 20 20" fill="currentColor"><path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </button>
            <div class="comment-options-menu" id="comment-options-${comment.id}">
              <button type="button" onclick="editComment(${postId}, ${comment.id})">Edit</button>
              <button type="button" class="danger" onclick="deleteComment(${postId}, ${comment.id})">Delete</button>
            </div>
           </div>`
        : '';
    const viewReplies = replyCount > 0 && !isReply
        ? `<button type="button" class="fb-view-replies" id="view-replies-${comment.id}" data-count="${replyCount}" onclick="toggleReplies(${comment.id}, event)">
            <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
            View ${replyCount} ${replyCount === 1 ? 'reply' : 'replies'}
           </button>`
        : '';

    return `<div class="comment-item${isReply ? ' is-reply' : ''}" data-comment-id="${comment.id}">
        <img src="${escapeHtml(commentAvatarUrl(comment))}" alt="${escapeHtml(comment.author_name)}" class="comment-avatar">
        <div class="comment-body">
            <div class="fb-comment-head">
                <span class="comment-author">${escapeHtml(comment.author_name)}</span>
                <span class="fb-comment-dot">·</span>
                <span class="comment-time">${escapeHtml(comment.time || '')}</span>
                ${optionsHtml}
            </div>
            <p class="comment-text" id="comment-text-${comment.id}">${escapeHtml(comment.body)}</p>
            <button type="button" class="comment-react-badge comment-react-inline" id="comment-react-badge-${comment.id}" ${likes > 0 ? '' : 'hidden'} onclick="openReactionViewer('comment', ${postId}, ${comment.id})">${badgeHtml}</button>
            <div class="comment-meta">
                <div class="reaction-wrap comment-like-wrap" data-target="comment" data-post-id="${postId}" data-comment-id="${comment.id}">
                    <button type="button" class="comment-like-btn reaction-btn${comment.liked ? ' liked' : ''}" data-type="${escapeHtml(reactionType)}">${commentLikeInner(reactionType)}</button>
                    ${reactionPickerHtml(reactionType, `reaction-picker-c${comment.id}`)}
                </div>
                <button type="button" class="comment-action-btn" onclick="showReplyInput(${postId}, ${comment.id})">Reply</button>
            </div>
            ${viewReplies}
            <div class="comment-reply-box" id="reply-box-${comment.id}" style="display:none;">
                <input type="text" class="comment-input" placeholder="Write a reply..." maxlength="500" onkeydown="handleCommentKey(event, ${postId}, this, ${comment.id})">
                <button type="button" class="send-comment-btn" onclick="submitComment(${postId},this.previousElementSibling,${comment.id})">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/></svg>
                </button>
            </div>
            ${repliesHtml ? `<div class="comment-replies" id="comment-replies-${comment.id}" hidden>${repliesHtml}</div>` : ''}
        </div>
    </div>`;
}

function topReactionTypes(counts) {
    return Object.keys(REACTION_EMOJI)
        .filter((type) => Number((counts || {})[type] || 0) > 0)
        .sort((a, b) => Number(counts[b] || 0) - Number(counts[a] || 0))
        .slice(0, 3);
}

function formatCount(n) {
    const num = Number(n || 0);
    if (num >= 1000000) {
        const value = num / 1000000;
        return `${value % 1 === 0 ? value.toFixed(0) : value.toFixed(1).replace(/\.0$/, '')}M`;
    }
    if (num >= 1000) {
        const value = num / 1000;
        return `${value % 1 === 0 ? value.toFixed(0) : value.toFixed(1).replace(/\.0$/, '')}K`;
    }
    return String(num);
}

function buildReactionSummaryHtml(counts, total, comments, postId) {
    const n = Number(total || 0);
    const c = Number(comments || 0);
    if (n <= 0 && c <= 0) return '';
    const left = n > 0
        ? `<button type="button" class="reaction-summary-left" onclick="openReactionViewer('post', ${Number(postId)})"><span class="reaction-faces">${reactionFacesHtml(counts)}</span><span class="reaction-total">${formatCount(n)}</span></button>`
        : '<div class="reaction-summary-left"></div>';
    const right = c > 0
        ? `<button type="button" class="reaction-summary-comments" onclick="toggleComments(${Number(postId)})">${formatCount(c)} ${c === 1 ? 'comment' : 'comments'}</button>`
        : '';
    return `${left}${right}`;
}

function refreshEngageSummary(id, patch = {}) {
    const summary = document.getElementById(`reaction-summary-${id}`);
    if (!summary) return;
    if (patch.counts) summary.dataset.counts = JSON.stringify(patch.counts);
    if (patch.total != null) summary.dataset.reactions = String(patch.total);
    if (patch.comments != null) summary.dataset.comments = String(patch.comments);
    let counts = {};
    try {
        counts = JSON.parse(summary.dataset.counts || '{}');
    } catch (_) {
        counts = {};
    }
    summary.innerHTML = buildReactionSummaryHtml(
        counts,
        Number(summary.dataset.reactions || 0),
        Number(summary.dataset.comments || 0),
        id,
    );
}

function applyReactionState(id, data) {
    const btn = document.getElementById(`reaction-btn-${id}`);
    const type = data.reaction_type || null;
    const label = type ? (REACTION_LABEL[type] || 'Like') : 'Like';
    if (btn) {
        btn.classList.toggle('liked', Boolean(type));
        btn.dataset.type = type || '';
        btn.setAttribute('aria-label', label);
        const icon = btn.querySelector('.reaction-icon');
        if (icon) {
            icon.innerHTML = type
                ? `<span class="reaction-current">${REACTION_EMOJI[type] || '👍'}</span>`
                : THUMBS_SVG;
        }
        const labelEl = btn.querySelector('.reaction-label');
        if (labelEl) labelEl.textContent = label;
    }
    refreshEngageSummary(id, {
        counts: data.reaction_counts || {},
        total: data.count,
    });
    document.querySelectorAll(`#reaction-picker-${id} .reaction-option`).forEach((option) => {
        option.classList.toggle('is-active', option.dataset.type === type);
    });
}

function isTouchDevice() {
    return window.matchMedia('(hover: none), (pointer: coarse)').matches;
}

function closeAllReactionPickers(exceptWrap = null) {
    document.querySelectorAll('.reaction-wrap.is-open').forEach((wrap) => {
        if (wrap !== exceptWrap) wrap.classList.remove('is-open');
    });
}

function bindReactionControls(root) {
    root.querySelectorAll('.reaction-wrap').forEach(bindReactionWrap);
}

function bindReactionWrap(wrap) {
    if (!wrap || wrap.dataset.bound === '1') return;
    wrap.dataset.bound = '1';

    const postId = Number(wrap.dataset.postId);
    const commentId = Number(wrap.dataset.commentId || 0);
    const isComment = wrap.dataset.target === 'comment';
    const btn = wrap.querySelector('.reaction-btn, .comment-like-btn');
    const picker = wrap.querySelector('.reaction-picker');
    let hideTimer = null;
    let pressTimer = null;
    let didLongPress = false;

    const applyType = (type) => {
        if (isComment) setCommentReaction(postId, commentId, type);
        else setReaction(postId, type);
    };

    const openPicker = () => {
        closeAllReactionPickers(wrap);
        document.querySelectorAll('.post-options-menu.open, .comment-options-menu.open').forEach((m) => m.classList.remove('open'));
        clearTimeout(hideTimer);
        wrap.classList.add('is-open');
    };

    const scheduleHide = () => {
        clearTimeout(hideTimer);
        hideTimer = setTimeout(() => wrap.classList.remove('is-open'), 80);
    };

    wrap.addEventListener('mouseenter', () => {
        if (isTouchDevice()) return;
        clearTimeout(hideTimer);
        openPicker();
    });

    wrap.addEventListener('mouseleave', () => {
        if (isTouchDevice()) return;
        scheduleHide();
    });

    picker?.addEventListener('mouseenter', () => {
        if (isTouchDevice()) return;
        clearTimeout(hideTimer);
        wrap.classList.add('is-open');
    });

    picker?.addEventListener('mouseleave', () => {
        if (isTouchDevice()) return;
        scheduleHide();
    });

    btn?.addEventListener('click', (event) => {
        event.preventDefault();
        event.stopPropagation();
        if (didLongPress) {
            didLongPress = false;
            return;
        }
        if (isTouchDevice() && wrap.classList.contains('is-open')) {
            wrap.classList.remove('is-open');
            return;
        }
        wrap.classList.remove('is-open');
        applyType(btn.dataset.type || 'like');
    });

    btn?.addEventListener('touchstart', () => {
        didLongPress = false;
        clearTimeout(pressTimer);
        pressTimer = setTimeout(() => {
            didLongPress = true;
            openPicker();
        }, 180);
    }, { passive: true });

    const cancelPress = () => clearTimeout(pressTimer);
    btn?.addEventListener('touchend', cancelPress);
    btn?.addEventListener('touchcancel', cancelPress);
    btn?.addEventListener('touchmove', cancelPress);
    btn?.addEventListener('contextmenu', (event) => event.preventDefault());

    picker?.querySelectorAll('.reaction-option').forEach((option) => {
        option.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            wrap.classList.remove('is-open');
            applyType(option.dataset.type);
        });
    });
}

async function toggleLike(id) {
    await setReaction(id, 'like');
}

async function setReaction(id, reactionType) {
    const btn = document.getElementById(`reaction-btn-${id}`);
    const current = btn?.dataset.type || '';
    const next = resolveNextReaction(current, reactionType);
    const summary = document.getElementById(`reaction-summary-${id}`);
    let counts = {};
    try { counts = JSON.parse(summary?.dataset.counts || '{}'); } catch (_) { counts = {}; }
    let total = Number(summary?.dataset.reactions || 0);
    if (current && !next.liked) total = Math.max(0, total - 1);
    else if (!current && next.liked) total += 1;
    counts = bumpReactionCounts(counts, current, next.type);
    if (next.liked) playFeedReactionSound();
    applyReactionState(id, {
        reaction_type: next.type || null,
        liked: next.liked,
        count: total,
        reaction_counts: counts,
    });
    closeAllReactionPickers();

    const key = `post:${id}`;
    const { seq, signal } = beginReactionRequest(key);
    try {
        const data = await apiFetch(`/api/community-feed/${id}/reactions`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ reaction_type: reactionType, client_seq: seq }),
            signal,
        });
        if (!isLatestReactionRequest(key, seq)) return;
        if (typeof data?.liked !== 'boolean') return;
        applyReactionState(id, data);
    } catch (err) {
        if (err?.name === 'AbortError') return;
        if (!isLatestReactionRequest(key, seq)) return;
        notifyFeed('Unable to update your reaction. Please try again.', 'error');
    }
}

async function setCommentReaction(postId, commentId, reactionType) {
    const wrap = document.querySelector(`.reaction-wrap[data-comment-id="${commentId}"]`);
    const btn = wrap?.querySelector('.comment-like-btn');
    const current = btn?.dataset.type || '';
    const next = resolveNextReaction(current, reactionType);
    if (next.liked) playFeedReactionSound();
    applyCommentReactionState(commentId, {
        reaction_type: next.type || '',
        liked: next.liked,
        count: next.liked ? 1 : 0,
        reaction_counts: {},
    });
    closeAllReactionPickers();

    const key = `comment:${commentId}`;
    const { seq, signal } = beginReactionRequest(key);
    try {
        const data = await apiFetch(`/api/community-feed/${postId}/comments/${commentId}/reactions`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ reaction_type: reactionType, client_seq: seq }),
            signal,
        });
        if (!isLatestReactionRequest(key, seq)) return;
        if (typeof data?.liked !== 'boolean') return;
        applyCommentReactionState(commentId, data);
    } catch (err) {
        if (err?.name === 'AbortError') return;
        if (!isLatestReactionRequest(key, seq)) return;
        notifyFeed('Unable to update your reaction. Please try again.', 'error');
    }
}

function applyCommentReactionState(commentId, data) {
    const wrap = document.querySelector(`.reaction-wrap[data-comment-id="${commentId}"]`);
    const type = data.reaction_type || '';
    const btn = wrap?.querySelector('.comment-like-btn');
    if (btn) {
        btn.classList.toggle('liked', Boolean(type));
        btn.dataset.type = type;
        btn.innerHTML = commentLikeInner(type);
    }
    wrap?.querySelectorAll('.reaction-option').forEach((option) => {
        option.classList.toggle('is-active', option.dataset.type === type);
    });
    const badge = document.getElementById(`comment-react-badge-${commentId}`);
    if (badge) {
        const count = Number(data.count || 0);
        badge.innerHTML = buildCommentBadgeHtml(data.reaction_counts, count);
        badge.hidden = count <= 0;
    }
}

let reactionViewerState = { target: 'post', postId: null, commentId: null, data: null, filter: 'all' };

async function openReactionViewer(target, postId, commentId = null) {
    const modal = document.getElementById('reactionViewerModal');
    if (!modal) return;
    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
    const list = document.getElementById('reactionViewerList');
    const tabs = document.getElementById('reactionViewerTabs');
    if (tabs) tabs.innerHTML = '';
    if (list) list.innerHTML = '<p class="reaction-viewer-empty">Loading...</p>';

    const url = target === 'comment'
        ? `/api/community-feed/${postId}/comments/${commentId}/reactions`
        : `/api/community-feed/${postId}/reactions`;

    try {
        const data = await apiFetch(url);
        reactionViewerState = { target, postId, commentId, data, filter: 'all' };
        renderReactionViewer('all');
    } catch (err) {
        if (list) list.innerHTML = `<p class="reaction-viewer-empty">${escapeHtml(err?.message || 'Unable to load reactions.')}</p>`;
    }
}

function closeReactionViewer() {
    const modal = document.getElementById('reactionViewerModal');
    if (!modal?.classList.contains('open')) return;
    modal.classList.remove('open');
    if (!document.getElementById('imageLightbox')?.classList.contains('active')) {
        document.body.style.overflow = '';
    }
}

function renderReactionViewer(filter = 'all') {
    reactionViewerState.filter = filter;
    const data = reactionViewerState.data || { reactors: [], reaction_counts: {}, count: 0 };
    const counts = data.reaction_counts || {};
    const tabs = document.getElementById('reactionViewerTabs');
    if (tabs) {
        const items = [['all', `All ${formatCount(data.count || 0)}`]].concat(
            Object.entries(REACTION_EMOJI)
                .filter(([type]) => Number(counts[type] || 0) > 0)
                .map(([type, emoji]) => [type, `${emoji} ${formatCount(counts[type])}`])
        );
        tabs.innerHTML = items.map(([type, label]) =>
            `<button type="button" class="reaction-viewer-tab${filter === type ? ' is-active' : ''}" data-type="${type}">${label}</button>`
        ).join('');
        tabs.querySelectorAll('.reaction-viewer-tab').forEach((tab) => {
            tab.addEventListener('click', () => renderReactionViewer(tab.dataset.type));
        });
    }

    const reactors = (data.reactors || []).filter((row) => filter === 'all' || row.reaction_type === filter);
    const list = document.getElementById('reactionViewerList');
    if (!list) return;
    if (!reactors.length) {
        list.innerHTML = '<p class="reaction-viewer-empty">No reactions yet.</p>';
        return;
    }
    list.innerHTML = reactors.map((row) => `
        <div class="reaction-viewer-row">
            <div class="reaction-viewer-avatar-wrap">
                <img src="${escapeHtml(row.avatar_url)}" alt="">
                <span class="reaction-viewer-emoji">${REACTION_EMOJI[row.reaction_type] || ''}</span>
            </div>
            <span class="reaction-viewer-name">${escapeHtml(row.name)}</span>
        </div>
    `).join('');
}

function isCommentsOpen(id) {
    const section = document.getElementById(`comments-${id}`);
    return Boolean(section && section.style.display !== 'none');
}

function commentsPageUrl(id) {
    const template = window.CommunityFeedConfig?.commentsPageUrl;
    if (template && String(template).includes('__ID__')) {
        return String(template).replace('__ID__', String(id));
    }
    return `/community-feed/${id}/comments`;
}

async function openComments(id) {
    const postId = Number(id);
    if (typeof window.openCommentPreview === 'function') {
        const cached = postCache.get(postId);
        if (cached) {
            window.openCommentPreview(cached);
            return;
        }
        try {
            const data = await apiFetch(`/api/community-feed/${postId}`);
            postCache.set(postId, data);
            window.openCommentPreview(data);
            return;
        } catch (_) { /* fall through to page URL */ }
    }
    window.location.assign(commentsPageUrl(postId));
}

function toggleComments(id) {
    openComments(id);
}

function handleCommentKey(event, postId, input, parentId = null) {
    if (event.key !== 'Enter' || event.shiftKey || event.isComposing || event.repeat) return;
    event.preventDefault();
    submitComment(postId, input, parentId);
}

function showReplyInput(postId, commentId) {
    const box = document.getElementById(`reply-box-${commentId}`);
    if (!box) return;
    box.style.display = box.style.display === 'none' ? 'flex' : 'none';
    box.querySelector('input')?.focus();
}

function bumpCommentCount(postId, delta) {
    const summary = document.getElementById(`reaction-summary-${postId}`);
    const countEl = document.getElementById(`comment-count-${postId}`);
    const cur = Number(summary?.dataset.comments ?? countEl?.dataset.count ?? 0);
    const next = Math.max(0, cur + delta);
    if (countEl) countEl.dataset.count = String(next);
    refreshEngageSummary(postId, { comments: next });
}

async function submitComment(id, input, parentId = null) {
    if (!input || input.dataset.sending === '1') return;
    const text = input.value.trim();
    if (!text) return;
    if (text.length > 500) {
        notifyFeed('Comments are limited to 500 characters.', 'error');
        return;
    }

    input.dataset.sending = '1';
    input.value = '';
    const sendBtn = input.parentElement?.querySelector('.send-comment-btn');
    if (sendBtn) sendBtn.disabled = true;

    const tempId = 'tmp-' + Date.now();
    const tempComment = {
        id: tempId,
        body: text,
        author_name: window.CommunityFeedConfig?.userDisplayName || window.CommentPreviewConfig?.userDisplayName || 'You',
        author_avatar_url: window.CommunityFeedConfig?.userAvatar || '',
        time: 'Just now',
        liked: false,
        likes: 0,
        owned: true,
        replies: [],
        parent_id: parentId,
    };
    const insertTemp = (comment, asReply) => {
        if (asReply) {
            const parentEl = document.querySelector(`[data-comment-id="${parentId}"] .comment-body`)
                || document.querySelector(`[data-comment-id="${parentId}"]`);
            let replies = parentEl?.querySelector(':scope > .comment-replies');
            if (parentEl && !replies) {
                replies = document.createElement('div');
                replies.className = 'comment-replies';
                parentEl.appendChild(replies);
            }
            replies?.insertAdjacentHTML('beforeend', buildCommentItem(comment, id, true));
            if (replies) replies.hidden = false;
        } else {
            const list = document.getElementById(`comments-list-${id}`);
            list?.insertAdjacentHTML('beforeend', buildCommentItem(comment, id));
            if (list) list.scrollTop = list.scrollHeight;
        }
    };
    insertTemp(tempComment, Boolean(parentId));
    bumpCommentCount(id, 1);
    refreshCommentPreview(id);

    try {
        const payload = { body: text };
        if (parentId) payload.parent_id = parentId;
        const comment = await apiFetch(`/api/community-feed/${id}/comments`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        document.querySelector(`[data-comment-id="${tempId}"]`)?.remove();
        if (!(document.getElementById(`comment-item-sent-${comment.id}`) || document.querySelector(`[data-comment-id="${comment.id}"]`))) {
            insertTemp(comment, Boolean(parentId || comment.parent_id));
            const postCard = document.querySelector(`.post-card[data-post-id="${id}"]`);
            if (postCard) bindReactionControls(postCard);
            refreshCommentPreview(id);
            if (isCommentsOpen(id)) {
                const preview = document.getElementById(`comment-preview-${id}`);
                if (preview) preview.hidden = true;
            }
        }
        notifyFeed(parentId ? 'Reply added successfully.' : 'Comment added successfully.');
    } catch (err) {
        document.querySelector(`[data-comment-id="${tempId}"]`)?.remove();
        bumpCommentCount(id, -1);
        refreshCommentPreview(id);
        input.value = text;
        notifyFeed(parentId ? 'Unable to add your reply. Please try again.' : 'Unable to add your comment. Please try again.', 'error');
    } finally {
        input.dataset.sending = '';
        if (sendBtn) sendBtn.disabled = false;
        input.focus();
    }
}

function refreshCommentPreview(postId) {
    const list = document.getElementById(`comments-list-${postId}`);
    const preview = document.getElementById(`comment-preview-${postId}`);
    const countEl = document.getElementById(`comment-count-${postId}`);
    if (!list || !preview) return;

    const items = [...list.querySelectorAll('.comment-item')];
    const total = Number(countEl?.dataset.count || items.length || 0);
    if (!items.length || total <= 0) {
        preview.innerHTML = '';
        preview.hidden = true;
        return;
    }

    let best = items[0];
    let bestScore = -1;
    items.forEach((item) => {
        const count = Number(item.querySelector('.comment-react-badge .reaction-total')?.textContent || 0);
        const id = Number(item.dataset.commentId || 0);
        const score = count * 1e9 + id;
        if (score >= bestScore) {
            bestScore = score;
            best = item;
        }
    });

    const avatar = best.querySelector(':scope > .comment-avatar')?.getAttribute('src') || '';
    const author = best.querySelector('.comment-author')?.textContent || '';
    const text = best.querySelector('.comment-text')?.textContent || '';
    const time = best.querySelector('.comment-time')?.textContent || '';
    const likeType = best.querySelector('.comment-like-btn')?.dataset.type || '';
    const replyCount = Number(best.querySelector('.fb-view-replies')?.dataset.count || 0);
    const badgeEl = best.querySelector(':scope > .comment-body > .comment-react-badge');
    const badgeHtml = badgeEl && !badgeEl.hidden
        ? `<span class="comment-react-badge comment-react-inline">${badgeEl.innerHTML}</span>`
        : '';
    const more = total > 1 ? `<span class="comment-preview-more">View all ${total} comments</span>` : '';
    const replies = replyCount > 0
        ? `<span class="fb-view-replies"><svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>View ${replyCount} ${replyCount === 1 ? 'reply' : 'replies'}</span>`
        : '';

    preview.innerHTML = `${more}
        <div class="fb-comment-row">
            <img src="${escapeHtml(avatar)}" alt="" class="comment-avatar">
            <div class="fb-comment-main">
                <div class="fb-comment-head">
                    <span class="comment-author">${escapeHtml(author)}</span>
                    <span class="fb-comment-dot">·</span>
                    <span class="comment-time">${escapeHtml(time)}</span>
                </div>
                <p class="comment-text">${escapeHtml(text)}</p>
                ${badgeHtml}
                <div class="comment-meta">
                    <span class="comment-like-btn">${commentLikeInner(likeType)}</span>
                    <span class="comment-action-btn">Reply</span>
                    ${replies}
                </div>
            </div>
        </div>`;
    preview.hidden = isCommentsOpen(postId);
}

function toggleReplies(commentId, event) {
    event?.preventDefault();
    event?.stopPropagation();
    const replies = document.getElementById(`comment-replies-${commentId}`);
    const btn = document.getElementById(`view-replies-${commentId}`);
    if (!replies) return;
    const opening = replies.hidden;
    replies.hidden = !opening;
    const count = Number(btn?.dataset.count || 0);
    if (btn) {
        btn.classList.toggle('is-open', opening);
        btn.innerHTML = `<svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>${opening ? 'Hide replies' : `View ${count} ${count === 1 ? 'reply' : 'replies'}`}`;
    }
}

function toggleCommentOptions(id, event) {
    event?.preventDefault();
    event?.stopPropagation();
    const menu = document.getElementById(`comment-options-${id}`);
    const isOpen = menu?.classList.contains('open');
    document.querySelectorAll('.comment-options-menu.open, .post-options-menu.open').forEach((m) => m.classList.remove('open'));
    closeAllReactionPickers();
    if (!isOpen) menu?.classList.add('open');
}

let pendingCommentAction = null;

function commentBodyText(commentId) {
    return document.getElementById(`comment-text-${commentId}`)?.textContent
        || document.getElementById(`cp-text-${commentId}`)?.textContent
        || '';
}

function editComment(postId, commentId) {
    pendingCommentAction = { postId: Number(postId), commentId: Number(commentId) };
    document.querySelectorAll('.comment-options-menu.open, .cp-options-menu.open, .post-options-menu.open').forEach((m) => m.classList.remove('open'));
    const modal = document.getElementById('editCommentModal');
    const field = document.getElementById('editCommentBody');
    if (!modal || !field) return;
    field.value = commentBodyText(commentId);
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
    setTimeout(() => field.focus(), 50);
}

function closeEditCommentModal() {
    document.getElementById('editCommentModal')?.classList.remove('active');
    pendingCommentAction = null;
    if (!document.getElementById('commentPreviewShell')?.classList.contains('is-open')) {
        document.body.style.overflow = '';
    }
}

async function confirmEditComment() {
    if (!pendingCommentAction) return;
    const { postId, commentId } = pendingCommentAction;
    const body = document.getElementById('editCommentBody')?.value.trim();
    if (!body) return;
    const btn = document.getElementById('confirmEditCommentBtn');
    if (btn) btn.disabled = true;
    try {
        const updated = await apiFetch(`/api/community-feed/${postId}/comments/${commentId}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ body }),
        });
        const feedEl = document.getElementById(`comment-text-${commentId}`);
        if (feedEl) feedEl.textContent = updated.body;
        const previewEl = document.getElementById(`cp-text-${commentId}`);
        if (previewEl) previewEl.textContent = updated.body;
        document.dispatchEvent(new CustomEvent('community-feed:comment-updated', { detail: updated }));
        closeEditCommentModal();
        notifyFeed(isReplyComment(commentId) ? 'Reply updated successfully.' : 'Comment updated successfully.');
    } catch (err) {
        notifyFeed(isReplyComment(commentId) ? 'Unable to update the reply. Please try again.' : 'Unable to update the comment. Please try again.', 'error');
    } finally {
        if (btn) btn.disabled = false;
    }
}

function deleteComment(postId, commentId) {
    pendingCommentAction = { postId: Number(postId), commentId: Number(commentId), isReply: isReplyComment(commentId) };
    document.querySelectorAll('.comment-options-menu.open, .cp-options-menu.open, .post-options-menu.open').forEach((m) => m.classList.remove('open'));
    const modal = document.getElementById('deleteCommentModal');
    if (!modal) return;
    const title = modal.querySelector('h2');
    const body = modal.querySelector('.modal-body p');
    const isReply = pendingCommentAction.isReply;
    if (title) title.textContent = isReply ? 'Delete Reply' : 'Delete Comment';
    if (body) body.textContent = isReply
        ? 'Are you sure you want to delete this reply?'
        : 'Are you sure you want to delete this comment?';
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeDeleteCommentModal() {
    document.getElementById('deleteCommentModal')?.classList.remove('active');
    pendingCommentAction = null;
    if (!document.getElementById('commentPreviewShell')?.classList.contains('is-open')) {
        document.body.style.overflow = '';
    }
}

async function confirmDeleteComment() {
    if (!pendingCommentAction) return;
    const { postId, commentId, isReply } = pendingCommentAction;
    const btn = document.getElementById('confirmDeleteCommentBtn');
    if (btn) btn.disabled = true;
    try {
        await apiFetch(`/api/community-feed/${postId}/comments/${commentId}`, { method: 'DELETE' });
        document.querySelectorAll(`[data-comment-id="${commentId}"]`).forEach((el) => el.remove());
        bumpCommentCount(postId, -1);
        document.dispatchEvent(new CustomEvent('community-feed:comment-deleted', { detail: { postId, commentId } }));
        closeDeleteCommentModal();
        notifyFeed(isReply ? 'Reply deleted successfully.' : 'Comment deleted successfully.');
    } catch (err) {
        notifyFeed(isReply ? 'Unable to delete the reply. Please try again.' : 'Unable to delete the comment. Please try again.', 'error');
    } finally {
        if (btn) btn.disabled = false;
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
    document.querySelectorAll('.post-options-menu.open, .comment-options-menu.open').forEach((m) => m.classList.remove('open'));

    try {
        const post = await apiFetch(`/api/community-feed/${id}`);
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
        notifyFeed('Unable to load the post for editing. Please try again.', 'error');
    }
}

let pendingArchivePostId = null;

function openArchiveModal(id) {
    pendingArchivePostId = id;
    document.getElementById('archivePostModal')?.classList.add('active');
    document.querySelectorAll('.post-options-menu.open, .comment-options-menu.open').forEach((m) => m.classList.remove('open'));
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
        await apiFetch(`/api/community-feed/${id}`, { method: 'DELETE' });
        knownPostIds.delete(Number(id));
        document.querySelector(`.post-card[data-post-id="${id}"]`)?.remove();
        closeArchiveModal();
        notifyFeed('Post deleted successfully.');
    } catch (err) {
        notifyFeed('Unable to delete the post. Please try again.', 'error');
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
    if (!body) { notifyFeed('Please write something.', 'error'); return; }
    if (body.length > MAX_BODY_CHARS) {
        notifyFeed(`Post text must be ${MAX_BODY_CHARS} characters or less.`, 'error');
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

            const res = await fetch(`/api/community-feed/${editingPostId}`, {
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
                bindReactionControls(card);
            }
            notifyFeed('Post updated successfully.');
        } else {
            const fd = new FormData();
            fd.append('type', type);
            fd.append('body', body);
            if (link) fd.append('link_url', link);
            pendingFiles.forEach((file) => fd.append('images[]', file));

            const res = await fetch('/api/community-feed', {
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
                showFeedToast('Post created successfully.', 'success');
            }
        }
        closeComposeModal();
    } catch (e) {
        notifyFeed(editingPostId ? 'Unable to update the post. Please try again.' : 'Unable to create the post. Please try again.', 'error');
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
    e.preventDefault();
    e.stopPropagation();
    const menu = document.getElementById(`options-menu-${id}`);
    const isOpen = menu?.classList.contains('open');
    document.querySelectorAll('.post-options-menu.open, .comment-options-menu.open').forEach((m) => m.classList.remove('open'));
    closeAllReactionPickers();
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

    document.addEventListener('click', (event) => {
        if (!event.target.closest('.reaction-wrap')) {
            closeAllReactionPickers();
        }
        document.querySelectorAll('.post-options-menu.open, .comment-options-menu.open').forEach((m) => m.classList.remove('open'));
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

    document.getElementById('editCommentBody')?.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeEditCommentModal();
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            if (document.getElementById('editCommentModal')?.classList.contains('active')) {
                closeEditCommentModal();
                return;
            }
            if (document.getElementById('deleteCommentModal')?.classList.contains('active')) {
                closeDeleteCommentModal();
                return;
            }
            closeReactionViewer();
        }
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
    const brgyList = document.getElementById('brgyLinkList');
    const profilesSidebar = document.getElementById('programsSidebar');
    profilesSidebar?.addEventListener('wheel', (event) => {
        event.preventDefault();
        event.stopPropagation();
        if (brgyList) brgyList.scrollTop += event.deltaY;
    }, { passive: false });
});

window.openComposeModal = openComposeModal;
window.closeComposeModal = closeComposeModal;
window.toggleComposeFullscreen = toggleComposeFullscreen;
window.submitPost = submitPost;
window.previewImages = previewImages;
window.toggleLinkInput = toggleLinkInput;
window.toggleLike = toggleLike;
window.setReaction = setReaction;
window.setCommentReaction = setCommentReaction;
window.openReactionViewer = openReactionViewer;
window.closeReactionViewer = closeReactionViewer;
window.toggleComments = toggleComments;
window.openComments = openComments;
window.toggleReplies = toggleReplies;
window.toggleCommentOptions = toggleCommentOptions;
window.handleCommentKey = handleCommentKey;
window.submitComment = submitComment;
window.showReplyInput = showReplyInput;
window.editComment = editComment;
window.deleteComment = deleteComment;
window.closeEditCommentModal = closeEditCommentModal;
window.confirmEditComment = confirmEditComment;
window.closeDeleteCommentModal = closeDeleteCommentModal;
window.confirmDeleteComment = confirmDeleteComment;
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
    const profile = window.CommunityFeedConfig?.profilePreview;
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
