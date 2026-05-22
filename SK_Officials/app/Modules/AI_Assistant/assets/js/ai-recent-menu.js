/**
 * SK AI Assistant — recent chat rows + context menu (hover / mobile tap)
 */
(function (global) {
    const store = global.SkAiStorage;
    const toast = global.SkAiToast;

    let menuEl = null;
    let openChatId = null;
    let callbacks = {};

    function getMenu() {
        if (!menuEl) {
            menuEl = document.getElementById('aiChatContextMenu');
        }
        return menuEl;
    }

    function closeMenu() {
        const menu = getMenu();
        if (!menu) return;
        menu.hidden = true;
        openChatId = null;
    }

    function positionMenu(anchor) {
        const menu = getMenu();
        if (!menu || !anchor) return;

        menu.hidden = false;
        const rect = anchor.getBoundingClientRect();
        const menuRect = menu.getBoundingClientRect();
        let top = rect.bottom + 6;
        let left = rect.right - menuRect.width;

        if (left < 8) left = 8;
        if (top + menuRect.height > window.innerHeight - 8) {
            top = rect.top - menuRect.height - 6;
        }
        if (left + menuRect.width > window.innerWidth - 8) {
            left = window.innerWidth - menuRect.width - 8;
        }

        menu.style.top = top + 'px';
        menu.style.left = left + 'px';
    }

    function updateMenuLabels(chat) {
        const menu = getMenu();
        if (!menu || !chat) return;
        const pinLabel = menu.querySelector('[data-pin-label]');
        const archiveLabel = menu.querySelector('[data-archive-label]');
        if (pinLabel) pinLabel.textContent = chat.pinned ? 'Unpin chat' : 'Pin chat';
        if (archiveLabel) archiveLabel.textContent = chat.archived ? 'Unarchive' : 'Archive';
    }

    function openMenu(chatId, anchor) {
        const chats = store.load();
        const chat = store.find(chats, chatId);
        if (!chat) return;

        openChatId = chatId;
        updateMenuLabels(chat);
        positionMenu(anchor);
    }

    async function copyShareLink(chatId) {
        const url = store.getShareUrl(chatId);
        try {
            await navigator.clipboard.writeText(url);
            toast.show('Link copied to clipboard', 'success');
        } catch {
            toast.show(url, 'success');
        }
    }

    function handleAction(action) {
        if (!openChatId) return;
        let chats = store.load();
        const chat = store.find(chats, openChatId);
        if (!chat) {
            closeMenu();
            return;
        }

        switch (action) {
            case 'share':
                copyShareLink(openChatId);
                break;
            case 'group':
                chat.groupChat = !chat.groupChat;
                if (chat.groupChat && !chat.title.startsWith('👥 ')) {
                    chat.title = '👥 ' + chat.title.replace(/^👥\s*/, '');
                }
                chats = store.upsert(chats, chat);
                toast.show(chat.groupChat ? 'Group chat started' : 'Switched to solo chat', 'success');
                if (callbacks.onChange) callbacks.onChange();
                break;
            case 'rename': {
                const next = window.prompt('Rename chat', chat.title);
                if (next && next.trim()) {
                    chat.title = next.trim();
                    chat.updatedAt = Date.now();
                    chats = store.upsert(chats, chat);
                    toast.show('Chat renamed', 'success');
                    if (callbacks.onChange) callbacks.onChange();
                }
                break;
            }
            case 'pin':
                chat.pinned = !chat.pinned;
                chat.updatedAt = Date.now();
                chats = store.upsert(chats, chat);
                toast.show(chat.pinned ? 'Chat pinned' : 'Chat unpinned', 'success');
                if (callbacks.onChange) callbacks.onChange();
                break;
            case 'archive':
                chat.archived = !chat.archived;
                chat.updatedAt = Date.now();
                chats = store.upsert(chats, chat);
                toast.show(chat.archived ? 'Chat archived' : 'Chat restored', 'success');
                if (callbacks.onChange) callbacks.onChange();
                if (callbacks.activeChatId === openChatId && chat.archived && callbacks.onLoadChat) {
                    callbacks.onLoadChat(null);
                }
                break;
            case 'delete':
                if (window.confirm('Delete this chat permanently?')) {
                    chats = store.remove(chats, openChatId);
                    toast.show('Chat deleted', 'success');
                    if (callbacks.activeChatId === openChatId && callbacks.onLoadChat) {
                        callbacks.onLoadChat(null);
                    }
                    if (callbacks.onChange) callbacks.onChange();
                }
                break;
        }
        closeMenu();
    }

    function bindMenuActions() {
        const menu = getMenu();
        if (!menu || menu.dataset.bound) return;
        menu.dataset.bound = '1';

        menu.querySelectorAll('[data-action]').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                handleAction(this.getAttribute('data-action'));
            });
        });

        document.addEventListener('click', function (e) {
            if (!menu.hidden && !menu.contains(e.target) && !e.target.closest('.ai-recent-more-btn')) {
                closeMenu();
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeMenu();
        });
    }

    function createRow(chat, activeChatId, variant) {
        const li = document.createElement('li');
        li.className = 'ai-recent-row' + (chat.id === activeChatId ? ' is-active' : '') + (chat.pinned ? ' is-pinned' : '');

        const rowInner = document.createElement('div');
        rowInner.className = 'ai-recent-row-inner';

        const titleBtn = document.createElement('button');
        titleBtn.type = 'button';
        titleBtn.className = 'ai-recent-row-title';
        titleBtn.title = chat.title;
        const label = store.truncate(chat.title, variant === 'page' ? 22 : 14);
        if (chat.groupChat) {
            titleBtn.textContent = label.startsWith('👥') ? label : '👥 ' + label;
        } else {
            titleBtn.textContent = label;
        }
        titleBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            closeMenu();
            if (callbacks.onLoadChat) callbacks.onLoadChat(chat.id);
        });

        const moreBtn = document.createElement('button');
        moreBtn.type = 'button';
        moreBtn.className = 'ai-recent-more-btn';
        moreBtn.setAttribute('aria-label', 'Chat options');
        moreBtn.setAttribute('aria-haspopup', 'menu');
        moreBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="1.5"></circle><circle cx="12" cy="12" r="1.5"></circle><circle cx="12" cy="19" r="1.5"></circle></svg>';
        moreBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            if (!menuEl || menuEl.hidden || openChatId !== chat.id) {
                openMenu(chat.id, moreBtn);
            } else {
                closeMenu();
            }
        });

        rowInner.appendChild(titleBtn);
        rowInner.appendChild(moreBtn);
        li.appendChild(rowInner);
        return li;
    }

    function renderList(container, options) {
        if (!container || !store) return;

        callbacks = {
            activeChatId: options.activeChatId,
            onLoadChat: options.onLoadChat,
            onChange: options.onChange,
        };

        const variant = options.variant || 'modal';
        let chats = store.getVisible(store.load(), false);
        const filter = (options.filter || '').trim().toLowerCase();
        if (filter) {
            chats = chats.filter(c => (c.title || '').toLowerCase().includes(filter));
        }
        container.innerHTML = '';

        chats.slice(0, options.limit || 15).forEach(chat => {
            container.appendChild(createRow(chat, options.activeChatId, variant));
        });

        bindMenuActions();
    }

    global.SkAiRecentMenu = {
        renderList,
        openMenu,
        closeMenu,
    };
})(window);
