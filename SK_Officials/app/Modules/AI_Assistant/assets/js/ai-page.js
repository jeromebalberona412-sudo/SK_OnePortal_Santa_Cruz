/**
 * SK AI Assistant — full-screen page
 */
document.addEventListener('DOMContentLoaded', function () {
    const store = window.SkAiStorage;
    const recentMenu = window.SkAiRecentMenu;
    const toast = window.SkAiToast;
    const attachments = window.SkAiAttachments;

    const aiWelcomeView = document.getElementById('aiWelcomeView');
    const aiChatView = document.getElementById('aiChatView');
    const aiChatArea = document.getElementById('aiChatArea');
    const aiChatList = document.getElementById('aiChatList');
    const aiChatListEmpty = document.getElementById('aiChatListEmpty');
    const aiNewChatBtn = document.getElementById('aiNewChatBtn');
    const aiSearchChats = document.getElementById('aiSearchChats');
    const aiSidebar = document.getElementById('aiSidebar');
    const aiSidebarToggle = document.getElementById('aiSidebarToggle');
    const aiSidebarOverlay = document.getElementById('aiSidebarOverlay');
    const aiBackPortal = document.getElementById('aiBackPortal');

    const inputWelcome = document.getElementById('aiInputField');
    const inputChat = document.getElementById('aiInputFieldChat');
    const sendWelcome = document.getElementById('aiSendBtn');
    const sendChat = document.getElementById('aiSendBtnChat');
    const charWelcome = document.getElementById('aiCharCountPageWelcome');
    const charChat = document.getElementById('aiCharCountPageChat');

    if (!store || !aiChatArea) return;

    const MAX_INPUT_CHARS = 500;
    let activeChatId = null;
    let isTyping = false;
    let limitToastShown = false;

    const attachWelcome = attachments?.bind({
        key: 'pageWelcome',
        attachBtn: document.getElementById('aiPageAttachWelcome'),
        previewEl: document.getElementById('aiAttachPreviewPageWelcome'),
        toast,
        onChange: syncComposerState,
    });

    const attachChat = attachments?.bind({
        key: 'pageChat',
        attachBtn: document.getElementById('aiPageAttachChat'),
        previewEl: document.getElementById('aiAttachPreviewPageChat'),
        toast,
        onChange: syncComposerState,
    });

    function getActiveChat() {
        return store.find(store.load(), activeChatId);
    }

    function getActiveAttachPool() {
        if (aiChatView && !aiChatView.hidden) return attachChat;
        return attachWelcome;
    }

    function syncViewState() {
        const chat = getActiveChat();
        const hasMessages = !!(chat && chat.messages.length > 0);
        if (aiWelcomeView) aiWelcomeView.hidden = hasMessages;
        if (aiChatView) aiChatView.hidden = !hasMessages;
        if (!hasMessages && inputWelcome) inputWelcome.focus();
        else if (hasMessages && inputChat) inputChat.focus();
        syncComposerState();
    }

    function buildMessageEl(msg) {
        const row = document.createElement('div');
        const sender = msg.sender || 'ai';
        row.className = `ai-msg ai-msg--${sender}`;

        if (sender === 'user') {
            const bubble = document.createElement('div');
            bubble.className = 'ai-msg-bubble';
            if (msg.text) {
                const t = document.createElement('div');
                t.textContent = msg.text;
                bubble.appendChild(t);
            }
            if (msg.attachments?.length) {
                const wrap = document.createElement('div');
                wrap.className = 'ai-msg-attachments';
                msg.attachments.forEach(att => {
                    if (att.isImage) {
                        const img = document.createElement('img');
                        img.className = 'ai-msg-attach-thumb';
                        img.src = att.dataUrl;
                        img.alt = att.name;
                        wrap.appendChild(img);
                    } else {
                        const span = document.createElement('span');
                        span.className = 'ai-msg-attach-file';
                        span.textContent = att.name;
                        wrap.appendChild(span);
                    }
                });
                bubble.appendChild(wrap);
            }
            row.appendChild(bubble);
        } else {
            const textEl = document.createElement('div');
            textEl.className = 'ai-msg-text';
            textEl.textContent = msg.text || '';
            row.appendChild(textEl);
        }
        return row;
    }

    function renderMessages() {
        const chat = getActiveChat();
        aiChatArea.innerHTML = '';
        syncViewState();
        if (!chat || !chat.messages.length) return;
        chat.messages.forEach(msg => aiChatArea.appendChild(buildMessageEl(msg)));
        scrollToBottom();
    }

    function scrollToBottom() {
        aiChatArea.scrollTop = aiChatArea.scrollHeight;
    }

    function updateCharCount(input, counterEl) {
        if (!input || !counterEl) return;
        const len = input.value.length;
        counterEl.textContent = len + '/' + MAX_INPUT_CHARS;
        counterEl.classList.toggle('is-limit', len >= MAX_INPUT_CHARS);
        if (len >= MAX_INPUT_CHARS && !limitToastShown) {
            limitToastShown = true;
            toast?.show('Maximum 500 characters reached', 'success');
        }
        if (len < MAX_INPUT_CHARS) limitToastShown = false;
    }

    function canSend(input) {
        const hasText = (input?.value || '').trim().length > 0;
        const pool = getActiveAttachPool();
        return hasText || (pool?.hasFiles?.() || false);
    }

    function syncComposerState() {
        const inChat = aiChatView && !aiChatView.hidden;
        updateSendState(inChat ? inputChat : inputWelcome, inChat ? sendChat : sendWelcome);
        updateCharCount(inChat ? inputChat : inputWelcome, inChat ? charChat : charWelcome);
    }

    function updateSendState(input, sendBtn) {
        if (!sendBtn) return;
        sendBtn.disabled = isTyping || !canSend(input);
    }

    function updateChatList() {
        if (!aiChatList || !recentMenu) return;
        const filter = aiSearchChats ? aiSearchChats.value.trim() : '';
        const visible = store.getVisible(store.load(), false);
        const shown = filter
            ? visible.filter(c => (c.title || '').toLowerCase().includes(filter.toLowerCase()))
            : visible;

        if (aiChatListEmpty) {
            aiChatListEmpty.classList.toggle('hidden', shown.length > 0);
        }

        recentMenu.renderList(aiChatList, {
            variant: 'page',
            limit: 30,
            activeChatId,
            filter,
            onLoadChat: setActiveChat,
            onChange: () => updateChatList(),
        });
    }

    function setActiveChat(id) {
        if (!id) {
            startNewChat();
            return;
        }
        const chat = store.find(store.load(), id);
        if (!chat || chat.archived) return;
        activeChatId = id;
        updateChatList();
        renderMessages();
        closeMobileSidebar();
    }

    function startNewChat() {
        activeChatId = null;
        aiChatArea.innerHTML = '';
        attachWelcome?.clear?.();
        attachChat?.clear?.();
        syncViewState();
        clearInputs();
        updateChatList();
        if (recentMenu) recentMenu.closeMenu();
        if (inputWelcome) inputWelcome.focus();
    }

    function showTypingIndicator() {
        const row = document.createElement('div');
        row.className = 'ai-msg ai-msg--ai';
        row.id = 'aiTypingBubble';
        row.innerHTML = '<div class="ai-msg-typing"><span class="ai-typing-dot"></span><span class="ai-typing-dot"></span><span class="ai-typing-dot"></span></div>';
        aiChatArea.appendChild(row);
        scrollToBottom();
    }

    function hideTypingIndicator() {
        const el = document.getElementById('aiTypingBubble');
        if (el) el.remove();
    }

    function setSendDisabled(disabled) {
        isTyping = disabled;
        [inputWelcome, inputChat].forEach(el => {
            if (el) el.disabled = disabled;
        });
        if (disabled) {
            [sendWelcome, sendChat].forEach(el => {
                if (el) el.disabled = true;
            });
        } else {
            syncComposerState();
        }
    }

    function generateAIResponse(userMessage, hasAttachments) {
        const lower = (userMessage || '').toLowerCase();
        if (hasAttachments) {
            return "I've received your file(s). Tell me how you'd like help with these SK documents.";
        }
        if (lower.includes('resolution')) return 'I can help you create an SK Resolution. Would you like a template?';
        if (lower.includes('proposal') || lower.includes('project') || lower.includes('write')) {
            return 'For a project proposal: background, objectives, timeline, budget, and outcomes.';
        }
        if (lower.includes('event') || lower.includes('planning')) {
            return 'Event planning: objectives, venue, budget, and program flow.';
        }
        if (lower.includes('budget')) return 'SK budget tips: review allocations and include contingency.';
        if (lower.includes('guideline') || lower.includes('look')) {
            return 'Key SK areas: KK Profiling, ABYIP, resolutions, and youth programs.';
        }
        return "I'm here for SK tasks — resolutions, proposals, events, and budgets.";
    }

    function ensureChat(firstMessage) {
        let chats = store.load();
        let chat = chats.find(c => c.id === activeChatId);
        if (!chat) {
            chat = store.normalize({
                id: store.generateId(),
                title: store.truncate(firstMessage, 28),
                messages: [],
                updatedAt: Date.now(),
            });
            activeChatId = chat.id;
            chats.unshift(chat);
        }
        return { chats, chat };
    }

    function sendMessage(rawMessage, pool) {
        const attachPool = pool || getActiveAttachPool();
        const pendingFiles = attachPool?.getPending?.() || [];
        const message = (rawMessage || '').trim().slice(0, MAX_INPUT_CHARS);
        if ((!message && !pendingFiles.length) || isTyping) return;

        const displayText = message || (pendingFiles.length === 1
            ? '📎 ' + pendingFiles[0].name
            : '📎 ' + pendingFiles.length + ' files');

        const { chats, chat } = ensureChat(displayText);
        activeChatId = chat.id;
        chat.messages.push({
            text: message,
            sender: 'user',
            attachments: pendingFiles.length ? pendingFiles : undefined,
        });
        chat.updatedAt = Date.now();
        chat.title = store.truncate(message || pendingFiles[0]?.name || 'Attachment', 28);
        store.save([chat, ...chats.filter(c => c.id !== chat.id)]);

        attachPool?.clear?.();
        renderMessages();
        clearInputs();
        setSendDisabled(true);
        showTypingIndicator();
        updateChatList();

        setTimeout(() => {
            hideTypingIndicator();
            let list = store.load();
            const c = store.find(list, activeChatId);
            if (!c) return;
            c.messages.push({
                text: generateAIResponse(message, pendingFiles.length > 0),
                sender: 'ai',
            });
            c.updatedAt = Date.now();
            store.upsert(list, c);
            renderMessages();
            updateChatList();
            setSendDisabled(false);
        }, 1100 + Math.random() * 600);
    }

    function clearInputs() {
        [inputWelcome, inputChat].forEach(el => {
            if (el) {
                el.value = '';
                el.style.height = 'auto';
            }
        });
        updateCharCount(inputWelcome, charWelcome);
        updateCharCount(inputChat, charChat);
        syncComposerState();
    }

    function enforceMaxLength(input) {
        if (!input || input.value.length <= MAX_INPUT_CHARS) return;
        input.value = input.value.slice(0, MAX_INPUT_CHARS);
        toast?.show('Maximum 500 characters reached', 'success');
    }

    function bindComposer(input, sendBtn, counterEl, attachPool) {
        if (!input) return;
        input.setAttribute('maxlength', String(MAX_INPUT_CHARS));
        if (sendBtn) {
            sendBtn.addEventListener('click', () => sendMessage(input.value, attachPool));
        }
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage(input.value, attachPool);
            }
        });
        input.addEventListener('input', function () {
            enforceMaxLength(this);
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 200) + 'px';
            updateCharCount(input, counterEl);
            updateSendState(input, sendBtn);
        });
        updateCharCount(input, counterEl);
        updateSendState(input, sendBtn);
    }

    function closeMobileSidebar() {
        if (aiSidebar) aiSidebar.classList.remove('open');
        if (aiSidebarOverlay) aiSidebarOverlay.classList.remove('show');
    }

    if (aiBackPortal) {
        aiBackPortal.addEventListener('click', function () {
            try {
                sessionStorage.setItem('skaiModalClosed', '1');
            } catch (_) { /* ignore */ }
        });
    }

    if (aiNewChatBtn) {
        aiNewChatBtn.addEventListener('click', () => {
            startNewChat();
            closeMobileSidebar();
        });
    }

    if (aiSearchChats) {
        aiSearchChats.addEventListener('input', () => updateChatList());
    }

    document.querySelectorAll('.ai-quick-chip').forEach(chip => {
        chip.addEventListener('click', function () {
            const prompt = this.getAttribute('data-prompt');
            if (prompt) sendMessage(prompt, attachWelcome);
        });
    });

    if (aiSidebarToggle) {
        aiSidebarToggle.addEventListener('click', () => {
            if (aiSidebar && aiSidebar.classList.contains('open')) {
                closeMobileSidebar();
            } else if (aiSidebar) {
                aiSidebar.classList.add('open');
                if (aiSidebarOverlay) aiSidebarOverlay.classList.add('show');
            }
        });
    }

    if (aiSidebarOverlay) {
        aiSidebarOverlay.addEventListener('click', closeMobileSidebar);
    }

    bindComposer(inputWelcome, sendWelcome, charWelcome, attachWelcome);
    bindComposer(inputChat, sendChat, charChat, attachChat);

    updateChatList();

    const urlChatId = new URLSearchParams(window.location.search).get('chat');
    if (urlChatId) {
        const chat = store.find(store.load(), urlChatId);
        if (chat && !chat.archived) setActiveChat(urlChatId);
        else startNewChat();
    } else {
        const visible = store.getVisible(store.load(), false);
        const latest = visible.find(c => c.messages && c.messages.length > 0);
        if (latest) setActiveChat(latest.id);
        else startNewChat();
    }
});
