/**
 * SK AI Assistant — chat storage (localStorage, frontend only)
 */
(function (global) {
    const STORAGE_KEY = 'skAiChats';
    const MAX_CHATS = 30;

    function load() {
        try {
            const raw = localStorage.getItem(STORAGE_KEY);
            const list = raw ? JSON.parse(raw) : [];
            return list.map(normalize);
        } catch {
            return [];
        }
    }

    function save(chats) {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(chats.slice(0, MAX_CHATS)));
    }

    function normalize(chat) {
        return {
            id: chat.id,
            title: chat.title || 'New chat',
            messages: Array.isArray(chat.messages) ? chat.messages : [],
            updatedAt: chat.updatedAt || Date.now(),
            pinned: !!chat.pinned,
            archived: !!chat.archived,
            groupChat: !!chat.groupChat,
        };
    }

    function generateId() {
        return 'chat_' + Date.now() + '_' + Math.random().toString(36).slice(2, 9);
    }

    function truncate(text, max) {
        const t = (text || '').trim();
        if (t.length <= max) return t;
        return t.slice(0, max).trim() + '…';
    }

    function sortChats(chats) {
        return [...chats].sort((a, b) => {
            if (a.pinned !== b.pinned) return a.pinned ? -1 : 1;
            return (b.updatedAt || 0) - (a.updatedAt || 0);
        });
    }

    function getVisible(chats, includeArchived) {
        const list = includeArchived ? chats : chats.filter(c => !c.archived);
        return sortChats(list);
    }

    function find(chats, id) {
        return chats.find(c => c.id === id) || null;
    }

    function upsert(chats, chat) {
        const next = [normalize(chat), ...chats.filter(c => c.id !== chat.id)];
        save(next);
        return next;
    }

    function remove(chats, id) {
        const next = chats.filter(c => c.id !== id);
        save(next);
        return next;
    }

    function getShareUrl(chatId) {
        const origin = window.location.origin;
        const path = '/ai-assistant';
        return `${origin}${path}?chat=${encodeURIComponent(chatId)}`;
    }

    global.SkAiStorage = {
        STORAGE_KEY,
        load,
        save,
        normalize,
        generateId,
        truncate,
        sortChats,
        getVisible,
        find,
        upsert,
        remove,
        getShareUrl,
    };
})(window);
