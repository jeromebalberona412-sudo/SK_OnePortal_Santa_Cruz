(function () {
'use strict';

const REACTION_EMOJI = { like: '👍', love: '❤️', haha: '😂', wow: '😮', sad: '😢', angry: '😡' };
const REACTION_LABEL = { like: 'Like', love: 'Love', haha: 'Haha', wow: 'Wow', sad: 'Sad', angry: 'Angry' };
const THUMBS_SVG = '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z"/></svg>';
const COMMENT_SVG = '<svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 10 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7z" clip-rule="evenodd"/></svg>';
const CHEVRON_SVG = '<svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>';
const SEND_SVG = '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/></svg>';

let post = window.CommentPreviewConfig?.post || null;
let sortMode = 'relevant';
let sending = false;
let viewerState = { data: null, filter: 'all' };
let pageBound = false;
let syncingUrl = false;

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';
const cfg = () => window.CommentPreviewConfig || {};

function escapeHtml(text) {
    return String(text ?? '')
        .replace(/&/g, '&amp;').replace(/</g, '&lt;')
        .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

async function apiFetch(url, options = {}) {
    const res = await fetch(url, {
        ...options,
        headers: {
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
            ...(options.headers || {}),
        },
    });
    if (!res.ok) {
        let message = 'Request failed.';
        try { message = (await res.json()).message || message; } catch (_) { /* ignore */ }
        throw new Error(message);
    }
    return res.json();
}

function avatarUrl(item) {
    return item?.author_avatar_url || item?.barangay_logo_url || cfg().userAvatar || cfg().defaultLogo;
}

function postAvatar() {
    return post?.barangay_logo_url || cfg().defaultLogo;
}

function formatCount(n) {
    const num = Number(n || 0);
    if (num >= 1000) return `${(num / 1000).toFixed(num % 1000 === 0 ? 0 : 1).replace(/\.0$/, '')}K`;
    return String(num);
}

function topTypes(counts) {
    return Object.entries(counts || {})
        .filter(([, n]) => Number(n) > 0)
        .sort((a, b) => Number(b[1]) - Number(a[1]))
        .slice(0, 3)
        .map(([type]) => type);
}

function facesHtml(counts) {
    return topTypes(counts).map((type) => {
        const inner = type === 'like'
            ? '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z"/></svg>'
            : type === 'love'
                ? '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"/></svg>'
                : (REACTION_EMOJI[type] || '');
        return `<span class="cp-face ${type}">${inner}</span>`;
    }).join('');
}

function countComments(comments) {
    return (comments || []).reduce((sum, c) => sum + 1 + countComments(c.replies || []), 0);
}

function sortComments(comments) {
    const list = [...(comments || [])];
    if (sortMode === 'newest') return list.sort((a, b) => Number(b.id) - Number(a.id));
    if (sortMode === 'oldest') return list.sort((a, b) => Number(a.id) - Number(b.id));
    return list.sort((a, b) => {
        const likes = Number(b.likes || 0) - Number(a.likes || 0);
        return likes !== 0 ? likes : Number(b.id) - Number(a.id);
    });
}

function pickerHtml(active) {
    return `<div class="cp-picker"><div class="cp-picker-inner">${
        Object.entries(REACTION_EMOJI).map(([type, emoji]) =>
            `<button type="button" class="cp-picker-opt${active === type ? ' is-active' : ''}" data-type="${type}" title="${type}">${emoji}</button>`
        ).join('')
    }</div></div>`;
}

function likeInner(type) {
    const label = type ? (REACTION_LABEL[type] || 'Like') : 'Like';
    const icon = type && type !== 'like' ? `<span>${REACTION_EMOJI[type]}</span>` : THUMBS_SVG;
    return `${icon}<span>${escapeHtml(label)}</span>`;
}

function commentLikeInner(type) {
    const label = type ? (REACTION_LABEL[type] || 'Like') : 'Like';
    if (type && type !== 'like') {
        return `<span class="comment-react-emoji">${REACTION_EMOJI[type] || ''}</span><span>${escapeHtml(label)}</span>`;
    }
    return `<span>${escapeHtml(label)}</span>`;
}

function renderPost() {
    if (!post) return;
    document.getElementById('cpTitle').textContent = `${post.author_name || 'SK'}'s Post`;
    const images = post.images?.length ? post.images : (post.image_url ? [post.image_url] : []);
    const mediaClass = images.length > 1 ? 'two' : 'one';
    const media = images.length
        ? `<div class="cp-media ${mediaClass}">${images.slice(0, 2).map((src) => `<img src="${escapeHtml(src)}" alt="">`).join('')}</div>`
        : '';

    document.getElementById('cpPost').innerHTML = `
        <div class="cp-post-head">
            <img src="${escapeHtml(postAvatar())}" alt="" class="cp-avatar">
            <div>
                <div class="cp-author">${escapeHtml(post.author_name || '')}</div>
                <div class="cp-meta">${escapeHtml(post.type || '')} · ${escapeHtml(post.time || '')}</div>
            </div>
        </div>
        ${post.title ? `<h2 class="cp-title-text">${escapeHtml(post.title)}</h2>` : ''}
        <p class="cp-body">${escapeHtml(post.body || '')}</p>
        ${media}
    `;

    const likes = Number(post.likes || 0);
    const comments = countComments(post.comments);
    const type = post.reaction_type || (post.liked ? 'like' : '');
    document.getElementById('cpEngage').innerHTML = `
        <div class="cp-stats">
            <button type="button" class="cp-stats-left" id="cpViewPostReactions">
                <span class="cp-faces">${facesHtml(post.reaction_counts)}</span>
                <span>${likes > 0 ? formatCount(likes) : ''}</span>
            </button>
            <span class="cp-stats-comments">${comments > 0 ? `${formatCount(comments)} comments` : ''}</span>
        </div>
        <div class="cp-actions">
            <div class="cp-reaction-wrap" data-target="post">
                <button type="button" class="cp-action${post.liked ? ' liked' : ''}" id="cpLikeBtn" data-type="${escapeHtml(type)}">${likeInner(type)}</button>
                ${pickerHtml(type)}
            </div>
            <button type="button" class="cp-action" id="cpFocusComment">${COMMENT_SVG}<span>Comment</span></button>
        </div>
    `;

    bindReactionWrap(document.querySelector('.cp-reaction-wrap'));
    document.getElementById('cpViewPostReactions')?.addEventListener('click', () => openViewer('post'));
    document.getElementById('cpFocusComment')?.addEventListener('click', () => {
        document.getElementById('cpCommentInput')?.focus();
        document.getElementById('cpComposer')?.scrollIntoView({ block: 'end' });
    });
}

function renderComments() {
    const root = document.getElementById('cpComments');
    const comments = sortComments(post?.comments || []);
    if (!comments.length) {
        root.innerHTML = '<p class="cp-empty">No comments yet. Be the first to comment.</p>';
        return;
    }
    root.innerHTML = comments.map((c) => commentHtml(c, false)).join('');
    root.querySelectorAll('.cp-reaction-wrap').forEach(bindReactionWrap);
}

function commentHtml(comment, isReply) {
    const replies = comment.replies || [];
    const likes = Number(comment.likes || 0);
    const type = comment.reaction_type || (comment.liked ? 'like' : '');
    const badge = likes > 0
        ? `<button type="button" class="cp-bubble-react" data-comment-id="${comment.id}">${facesHtml(comment.reaction_counts)} ${formatCount(likes)}</button>`
        : `<button type="button" class="cp-bubble-react" data-comment-id="${comment.id}" hidden></button>`;
    const options = comment.owned
        ? `<div class="cp-options">
            <button type="button" class="cp-options-btn" data-opt="${comment.id}">⋯</button>
            <div class="cp-options-menu" id="cp-opt-${comment.id}">
              <button type="button" data-edit="${comment.id}">Edit</button>
              <button type="button" class="danger" data-del="${comment.id}">Delete</button>
            </div>
           </div>`
        : '';
    const viewReplies = replies.length && !isReply
        ? `<button type="button" class="cp-view-replies" data-toggle-replies="${comment.id}" data-count="${replies.length}">${CHEVRON_SVG} View ${replies.length} ${replies.length === 1 ? 'reply' : 'replies'}</button>`
        : '';

    return `<div class="cp-comment${isReply ? ' is-reply' : ''}" data-comment-id="${comment.id}">
        <img src="${escapeHtml(avatarUrl(comment))}" alt="" class="cp-comment-avatar">
        <div class="cp-comment-body">
            <div class="cp-bubble">
                <span class="cp-bubble-name">${escapeHtml(comment.author_name)}</span>
                <span class="cp-bubble-text" id="cp-text-${comment.id}">${escapeHtml(comment.body)}</span>
                ${badge}
            </div>
            <div class="cp-comment-meta">
                <div class="cp-reaction-wrap" data-target="comment" data-comment-id="${comment.id}">
                    <button type="button" class="cp-like-btn${comment.liked ? ' liked' : ''}" data-type="${escapeHtml(type)}">${commentLikeInner(type)}</button>
                    ${pickerHtml(type)}
                </div>
                <button type="button" class="cp-meta-btn" data-reply="${comment.id}">Reply</button>
                <span class="cp-time">${escapeHtml(comment.time || '')}</span>
                ${options}
            </div>
            ${viewReplies}
            <div class="cp-reply-box" id="cp-reply-${comment.id}">
                <input type="text" maxlength="500" placeholder="Write a reply..." data-reply-input="${comment.id}">
                <button type="button" class="cp-send-btn" data-reply-send="${comment.id}">${SEND_SVG}</button>
            </div>
            ${replies.length ? `<div class="cp-replies" id="cp-replies-${comment.id}" hidden>${replies.map((r) => commentHtml(r, true)).join('')}</div>` : ''}
        </div>
    </div>`;
}

function isTouch() {
    return window.matchMedia('(hover: none), (pointer: coarse)').matches;
}

function closePickers(except = null) {
    document.querySelectorAll('.cp-reaction-wrap.is-open').forEach((el) => {
        if (el !== except) el.classList.remove('is-open');
    });
}

function bindReactionWrap(wrap) {
    if (!wrap || wrap.dataset.bound === '1') return;
    wrap.dataset.bound = '1';
    const btn = wrap.querySelector('.cp-action, .cp-like-btn');
    const picker = wrap.querySelector('.cp-picker');
    let hideTimer = null;
    let showTimer = null;
    let pressTimer = null;
    let didLongPress = false;
    const target = wrap.dataset.target;
    const commentId = Number(wrap.dataset.commentId || 0);

    const apply = (type) => {
        if (target === 'comment') setCommentReaction(commentId, type);
        else setPostReaction(type);
    };
    const open = () => {
        closePickers(wrap);
        wrap.classList.add('is-open');
    };

    wrap.addEventListener('mouseenter', () => {
        if (isTouch()) return;
        clearTimeout(hideTimer);
        showTimer = setTimeout(open, 280);
    });
    wrap.addEventListener('mouseleave', () => {
        if (isTouch()) return;
        clearTimeout(showTimer);
        hideTimer = setTimeout(() => wrap.classList.remove('is-open'), 320);
    });
    picker?.addEventListener('mouseenter', () => {
        if (isTouch()) return;
        clearTimeout(hideTimer);
        clearTimeout(showTimer);
        wrap.classList.add('is-open');
    });
    picker?.addEventListener('mouseleave', () => {
        if (isTouch()) return;
        hideTimer = setTimeout(() => wrap.classList.remove('is-open'), 320);
    });
    btn?.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        if (didLongPress) { didLongPress = false; return; }
        wrap.classList.remove('is-open');
        apply(btn.dataset.type || 'like');
    });
    btn?.addEventListener('touchstart', () => {
        didLongPress = false;
        pressTimer = setTimeout(() => { didLongPress = true; open(); }, 420);
    }, { passive: true });
    ['touchend', 'touchcancel', 'touchmove'].forEach((ev) => btn?.addEventListener(ev, () => clearTimeout(pressTimer)));
    picker?.querySelectorAll('.cp-picker-opt').forEach((opt) => {
        opt.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            wrap.classList.remove('is-open');
            apply(opt.dataset.type);
        });
    });
}

async function setPostReaction(type) {
    try {
        const data = await apiFetch(`/api/community-feed/${post.id}/react`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ reaction_type: type }),
        });
        post.reaction_type = data.reaction_type;
        post.liked = data.liked;
        post.likes = data.count;
        post.reaction_counts = data.reaction_counts;
        renderPost();
    } catch (err) {
        alert(err?.message || 'Unable to react.');
    }
}

async function setCommentReaction(commentId, type) {
    try {
        const data = await apiFetch(`/api/community-feed/${post.id}/comments/${commentId}/reactions`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ reaction_type: type }),
        });
        updateCommentReaction(post.comments, commentId, data);
        renderComments();
    } catch (err) {
        alert(err?.message || 'Unable to react.');
    }
}

function updateCommentReaction(comments, id, data) {
    (comments || []).forEach((c) => {
        if (Number(c.id) === Number(id)) {
            c.reaction_type = data.reaction_type;
            c.liked = data.liked;
            c.likes = data.count;
            c.reaction_counts = data.reaction_counts;
        }
        updateCommentReaction(c.replies || [], id, data);
    });
}

async function submitComment(body, parentId = null) {
    if (sending || !body) return;
    sending = true;
    try {
        const payload = { body };
        if (parentId) payload.parent_id = parentId;
        const comment = await apiFetch(`/api/community-feed/${post.id}/comment`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        if (findComment(post.comments, comment.id)) {
            return;
        }
        const actualParentId = comment.parent_id || parentId;
        if (actualParentId) {
            const parent = findComment(post.comments, actualParentId);
            if (parent) {
                parent.replies = parent.replies || [];
                parent.replies.push(comment);
                parent.reply_count = parent.replies.length;
            }
        } else {
            post.comments = post.comments || [];
            post.comments.push(comment);
        }
        renderPost();
        renderComments();
        if (actualParentId) {
            const box = document.getElementById(`cp-replies-${actualParentId}`);
            if (box) box.hidden = false;
        }
        document.getElementById('cpScroll')?.scrollTo({ top: document.getElementById('cpScroll').scrollHeight, behavior: 'smooth' });
    } catch (err) {
        alert(err?.message || 'Unable to post comment.');
    } finally {
        sending = false;
    }
}

function findComment(comments, id) {
    for (const c of comments || []) {
        if (Number(c.id) === Number(id)) return c;
        const nested = findComment(c.replies || [], id);
        if (nested) return nested;
    }
    return null;
}

async function openViewer(target, commentId = null) {
    const modal = document.getElementById('cpReactionViewer');
    const list = document.getElementById('cpViewerList');
    modal.hidden = false;
    list.innerHTML = '<p class="cp-viewer-empty">Loading...</p>';
    const url = target === 'comment'
        ? `/api/community-feed/${post.id}/comments/${commentId}/reactions`
        : `/api/community-feed/${post.id}/likes`;
    try {
        viewerState = { data: await apiFetch(url), filter: 'all' };
        renderViewer('all');
    } catch (err) {
        list.innerHTML = `<p class="cp-viewer-empty">${escapeHtml(err?.message || 'Unable to load reactions.')}</p>`;
    }
}

function renderViewer(filter) {
    viewerState.filter = filter;
    const data = viewerState.data || { reactors: [], reaction_counts: {}, count: 0 };
    const counts = data.reaction_counts || {};
    const tabs = document.getElementById('cpViewerTabs');
    const items = [['all', `All ${formatCount(data.count || 0)}`]].concat(
        Object.entries(REACTION_EMOJI).filter(([t]) => Number(counts[t] || 0) > 0)
            .map(([t, e]) => [t, `${e} ${formatCount(counts[t])}`])
    );
    tabs.innerHTML = items.map(([t, label]) =>
        `<button type="button" class="cp-viewer-tab${filter === t ? ' is-active' : ''}" data-type="${t}">${label}</button>`
    ).join('');
    tabs.querySelectorAll('.cp-viewer-tab').forEach((tab) => {
        tab.addEventListener('click', () => renderViewer(tab.dataset.type));
    });
    const rows = (data.reactors || []).filter((r) => filter === 'all' || r.reaction_type === filter);
    const list = document.getElementById('cpViewerList');
    list.innerHTML = rows.length
        ? rows.map((r) => `<div class="cp-viewer-row"><div class="cp-viewer-avatar-wrap"><img src="${escapeHtml(r.avatar_url)}" alt=""><span class="cp-viewer-emoji">${REACTION_EMOJI[r.reaction_type] || ''}</span></div><span class="cp-viewer-name">${escapeHtml(r.name)}</span></div>`).join('')
        : '<p class="cp-viewer-empty">No reactions yet.</p>';
}

function bindPage() {
    const input = document.getElementById('cpCommentInput');
    const send = document.getElementById('cpSendBtn');
    const updateSend = () => { send.disabled = !input.value.trim(); };
    input?.addEventListener('input', updateSend);
    input?.addEventListener('keydown', (e) => {
        if (e.key !== 'Enter' || e.repeat || e.isComposing) return;
        e.preventDefault();
        const text = input.value.trim();
        if (!text) return;
        input.value = '';
        updateSend();
        submitComment(text);
    });
    send?.addEventListener('click', () => {
        const text = input.value.trim();
        if (!text) return;
        input.value = '';
        updateSend();
        submitComment(text);
    });

    document.getElementById('cpSortBtn')?.addEventListener('click', (e) => {
        e.stopPropagation();
        document.getElementById('cpSortMenu')?.classList.toggle('open');
    });
    document.querySelectorAll('#cpSortMenu button').forEach((btn) => {
        btn.addEventListener('click', () => {
            sortMode = btn.dataset.sort;
            const labels = { relevant: 'Most relevant', newest: 'Newest', oldest: 'Oldest' };
            document.getElementById('cpSortBtn').textContent = labels[sortMode] || 'Most relevant';
            document.getElementById('cpSortMenu').classList.remove('open');
            renderComments();
        });
    });

    document.getElementById('cpComments')?.addEventListener('click', (e) => {
        const reply = e.target.closest('[data-reply]');
        if (reply) {
            const box = document.getElementById(`cp-reply-${reply.dataset.reply}`);
            if (box) {
                box.classList.toggle('open');
                box.querySelector('input')?.focus();
            }
            return;
        }
        const toggle = e.target.closest('[data-toggle-replies]');
        if (toggle) {
            const id = toggle.dataset.toggleReplies;
            const el = document.getElementById(`cp-replies-${id}`);
            if (!el) return;
            const open = el.hidden;
            el.hidden = !open;
            toggle.classList.toggle('is-open', open);
            const n = Number(toggle.dataset.count || 0);
            toggle.innerHTML = `${CHEVRON_SVG} ${open ? 'Hide replies' : `View ${n} ${n === 1 ? 'reply' : 'replies'}`}`;
            return;
        }
        const react = e.target.closest('.cp-bubble-react');
        if (react) {
            openViewer('comment', react.dataset.commentId);
            return;
        }
        const opt = e.target.closest('[data-opt]');
        if (opt) {
            e.stopPropagation();
            document.querySelectorAll('.cp-options-menu.open').forEach((m) => m.classList.remove('open'));
            document.getElementById(`cp-opt-${opt.dataset.opt}`)?.classList.toggle('open');
            return;
        }
        const edit = e.target.closest('[data-edit]');
        if (edit) {
            if (typeof window.editComment === 'function') {
                window.editComment(post.id, edit.dataset.edit);
            }
            return;
        }
        const del = e.target.closest('[data-del]');
        if (del) {
            if (typeof window.deleteComment === 'function') {
                window.deleteComment(post.id, del.dataset.del);
            }
            return;
        }
        const replySend = e.target.closest('[data-reply-send]');
        if (replySend) {
            const id = replySend.dataset.replySend;
            const field = document.querySelector(`[data-reply-input="${id}"]`);
            const text = field?.value.trim();
            if (!text) return;
            field.value = '';
            submitComment(text, Number(id));
        }
    });

    document.getElementById('cpComments')?.addEventListener('keydown', (e) => {
        const field = e.target.closest('[data-reply-input]');
        if (!field || e.key !== 'Enter' || e.repeat) return;
        e.preventDefault();
        const text = field.value.trim();
        if (!text) return;
        field.value = '';
        submitComment(text, Number(field.dataset.replyInput));
    });

    document.getElementById('cpViewerOverlay')?.addEventListener('click', () => {
        document.getElementById('cpReactionViewer').hidden = true;
    });
    document.getElementById('cpViewerClose')?.addEventListener('click', () => {
        document.getElementById('cpReactionViewer').hidden = true;
    });
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.cp-reaction-wrap')) closePickers();
        if (!e.target.closest('.cp-options')) {
            document.querySelectorAll('.cp-options-menu.open').forEach((m) => m.classList.remove('open'));
        }
        if (!e.target.closest('.cp-sort')) {
            document.getElementById('cpSortMenu')?.classList.remove('open');
        }
    });
    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return;
        if (document.getElementById('editCommentModal')?.classList.contains('active')) return;
        if (document.getElementById('deleteCommentModal')?.classList.contains('active')) return;
        const viewer = document.getElementById('cpReactionViewer');
        if (viewer && !viewer.hidden) {
            viewer.hidden = true;
            return;
        }
        closeCommentPreview();
    });

    document.getElementById('cpClose')?.addEventListener('click', (e) => {
        e.preventDefault();
        closeCommentPreview();
    });
    document.getElementById('commentPreviewShell')?.addEventListener('click', (e) => {
        if (e.target.id === 'commentPreviewShell') closeCommentPreview();
    });
}

function removeComment(comments, id) {
    return (comments || []).filter((c) => Number(c.id) !== id).map((c) => ({
        ...c,
        replies: removeComment(c.replies || [], id),
    }));
}

function commentsPath(id) {
    const template = window.CommunityFeedConfig?.commentsPageUrl;
    if (template && String(template).includes('__ID__')) {
        return String(template).replace('__ID__', String(id));
    }
    return `/community-feed/${id}/comments`;
}

function feedPath() {
    return cfg().feedUrl || '/community-feed';
}

function pathOf(url) {
    try {
        return new URL(url, window.location.origin).pathname;
    } catch (_) {
        return url;
    }
}

function syncCommentsUrl(id) {
    const next = id ? commentsPath(id) : feedPath();
    if (pathOf(window.location.href) === pathOf(next)) return;
    syncingUrl = true;
    history.pushState({ commentPreview: id || null }, '', next);
    syncingUrl = false;
}

function openCommentPreview(nextPost, { skipUrl } = {}) {
    if (!nextPost) return;
    post = nextPost;
    const shell = document.getElementById('commentPreviewShell');
    if (!shell) return;
    shell.hidden = false;
    shell.classList.add('is-open');
    document.body.style.overflow = 'hidden';
    if (!pageBound) {
        bindPage();
        pageBound = true;
    }
    renderPost();
    renderComments();
    document.getElementById('cpScroll')?.scrollTo({ top: 0 });
    if (!skipUrl) syncCommentsUrl(nextPost.id);
}

function closeCommentPreview({ skipUrl } = {}) {
    const shell = document.getElementById('commentPreviewShell');
    if (shell) {
        shell.hidden = true;
        shell.classList.remove('is-open');
    }
    document.getElementById('cpReactionViewer')?.setAttribute('hidden', '');
    document.body.style.overflow = '';
    if (!skipUrl) syncCommentsUrl(null);
}

window.openCommentPreview = openCommentPreview;
window.closeCommentPreview = closeCommentPreview;

window.addEventListener('popstate', () => {
    if (syncingUrl) return;
    const match = window.location.pathname.match(/\/community-feed\/(\d+)\/comments\/?$/);
    if (match) {
        const id = Number(match[1]);
        if (post && Number(post.id) === id) {
            openCommentPreview(post, { skipUrl: true });
            return;
        }
        fetch(`/api/community-feed/${id}`, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        }).then((res) => res.ok ? res.json() : null)
            .then((data) => { if (data) openCommentPreview(data, { skipUrl: true }); })
            .catch(() => {});
        return;
    }
    closeCommentPreview({ skipUrl: true });
});

document.addEventListener('community-feed:comment-updated', (event) => {
    const updated = event.detail;
    if (!post || !updated?.id) return;
    const found = findComment(post.comments, updated.id);
    if (found) found.body = updated.body;
});

document.addEventListener('community-feed:comment-deleted', (event) => {
    const commentId = Number(event.detail?.commentId);
    if (!post || !commentId) return;
    post.comments = removeComment(post.comments, commentId);
    renderPost();
    renderComments();
});

document.addEventListener('DOMContentLoaded', () => {
    if (post) openCommentPreview(post, { skipUrl: true });
});
})();
