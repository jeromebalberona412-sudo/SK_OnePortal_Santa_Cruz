/**
 * SK AI Assistant — header dropdown modal
 */
document.addEventListener('DOMContentLoaded', function () {
    const store = window.SkAiStorage;
    const recentMenu = window.SkAiRecentMenu;
    const toast = window.SkAiToast;
    const attachments = window.SkAiAttachments;

    const aiBtn = document.getElementById('aiAssistantBtn');
    const aiModal = document.getElementById('aiAssistantModal');
    const aiMenu = document.getElementById('aiAssistantMenu');
    const aiCloseBtn = document.getElementById('aiCloseBtn');
    const aiChatArea = document.getElementById('aiChatArea');
    const aiModalWelcome = document.getElementById('aiModalWelcome');
    const aiModalChat = document.getElementById('aiModalChat');
    const aiInputField = document.getElementById('aiInputField');
    const aiInputWelcome = document.getElementById('aiInputFieldWelcome');
    const aiSendBtn = document.getElementById('aiSendBtn');
    const aiSendWelcome = document.getElementById('aiSendBtnWelcome');
    const aiCharCountChat = document.getElementById('aiCharCountChat');
    const aiCharCountWelcome = document.getElementById('aiCharCountWelcome');
    const aiRecentsToggle = document.getElementById('aiRecentsToggle');
    const aiRecentsSidebar = document.getElementById('aiRecentsSidebar');
    const aiModalLayout = document.getElementById('aiModalLayout');
    const aiRecentList = document.getElementById('aiRecentList');
    const aiRecentEmpty = document.getElementById('aiRecentEmpty');
    const aiRecentSearch = document.getElementById('aiRecentSearch');
    const aiNewChatBtn = document.getElementById('aiModalNewChatBtn');

    if (!aiBtn || !aiModal || !aiChatArea || !store) return;

    const MAX_INPUT_CHARS = 500;
    let isTyping = false;
    let activeChatId = null;
    let limitToastShown = false;

    const attachWelcome = attachments?.bind({
        key: 'modalWelcome',
        attachBtn: document.getElementById('aiModalAttachWelcome'),
        previewEl: document.getElementById('aiAttachPreviewWelcome'),
        toast,
        onChange: () => syncComposerState(),
    });

    const attachChat = attachments?.bind({
        key: 'modalChat',
        attachBtn: document.getElementById('aiModalAttachChat'),
        previewEl: document.getElementById('aiAttachPreviewChat'),
        toast,
        onChange: () => syncComposerState(),
    });

    function getActiveChat() {
        return store.find(store.load(), activeChatId);
    }

    function setViewMode(hasMessages) {
        if (aiModalWelcome) aiModalWelcome.hidden = hasMessages;
        if (aiModalChat) aiModalChat.hidden = !hasMessages;
    }

    function getActiveAttachPool() {
        if (aiModalChat && !aiModalChat.hidden) return attachChat;
        return attachWelcome;
    }

    function buildMessageEl(msg) {
        const row = document.createElement('div');
        const sender = msg.sender || 'ai';
        row.className = `ai-msg ai-msg--${sender}`;

        if (sender === 'user') {
            const bubble = document.createElement('div');
            bubble.className = 'ai-msg-bubble';
            if (msg.text) {
                const textNode = document.createElement('div');
                textNode.textContent = msg.text;
                bubble.appendChild(textNode);
            }
            if (msg.attachments && msg.attachments.length) {
                const attWrap = document.createElement('div');
                attWrap.className = 'ai-msg-attachments';
                msg.attachments.forEach(att => {
                    if (att.isImage) {
                        const img = document.createElement('img');
                        img.className = 'ai-msg-attach-thumb';
                        img.src = att.dataUrl;
                        img.alt = att.name;
                        attWrap.appendChild(img);
                    } else {
                        const span = document.createElement('span');
                        span.className = 'ai-msg-attach-file';
                        span.textContent = att.name;
                        attWrap.appendChild(span);
                    }
                });
                bubble.appendChild(attWrap);
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

    function renderChatMessages() {
        const chat = getActiveChat();
        aiChatArea.innerHTML = '';
        if (!chat || !chat.messages.length) {
            setViewMode(false);
            return;
        }
        setViewMode(true);
        chat.messages.forEach(msg => aiChatArea.appendChild(buildMessageEl(msg)));
        scrollToBottom();
    }

    function scrollToBottom() {
        aiChatArea.scrollTop = aiChatArea.scrollHeight;
    }

    function newChatTemplate(firstMessage) {
        return store.normalize({
            id: store.generateId(),
            title: store.truncate(firstMessage, 24),
            messages: [],
            updatedAt: Date.now(),
            pinned: false,
            archived: false,
            groupChat: false,
        });
    }

    function ensureChat(firstMessage) {
        let chats = store.load();
        let chat = chats.find(c => c.id === activeChatId);
        if (!chat) {
            chat = newChatTemplate(firstMessage);
            activeChatId = chat.id;
            chats.unshift(chat);
        }
        return { chats, chat };
    }

    function updateRecentList() {
        if (!aiRecentList || !recentMenu) return;
        const chats = store.getVisible(store.load(), false);
        if (aiRecentEmpty) aiRecentEmpty.style.display = chats.length ? 'none' : 'block';

        recentMenu.renderList(aiRecentList, {
            variant: 'modal',
            limit: 12,
            activeChatId,
            filter: aiRecentSearch?.value || '',
            onLoadChat: loadChatInModal,
            onChange: updateRecentList,
        });
    }

    function showTypingIndicator() {
        const row = document.createElement('div');
        row.className = 'ai-msg ai-msg--ai';
        row.id = 'aiTypingIndicator';
        row.innerHTML = '<div class="ai-msg-typing"><span class="ai-typing-dot"></span><span class="ai-typing-dot"></span><span class="ai-typing-dot"></span></div>';
        aiChatArea.appendChild(row);
        scrollToBottom();
    }

    function hideTypingIndicator() {
        const el = document.getElementById('aiTypingIndicator');
        if (el) el.remove();
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

    function updateSendState(input, sendBtn) {
        if (!sendBtn) return;
        sendBtn.disabled = isTyping || !canSend(input);
    }

    function syncComposerState() {
        const inChat = aiModalChat && !aiModalChat.hidden;
        updateSendState(inChat ? aiInputField : aiInputWelcome, inChat ? aiSendBtn : aiSendWelcome);
        if (inChat) {
            updateCharCount(aiInputField, aiCharCountChat);
        } else {
            updateCharCount(aiInputWelcome, aiCharCountWelcome);
        }
    }

    function setInputsDisabled(disabled) {
        isTyping = disabled;
        [aiInputField, aiInputWelcome].forEach(el => {
            if (el) el.disabled = disabled;
        });
        if (disabled) {
            [aiSendBtn, aiSendWelcome].forEach(el => {
                if (el) el.disabled = true;
            });
        } else {
            syncComposerState();
        }
    }

    function generateAIResponse(userMessage, hasAttachments) {
        const msg = userMessage.toLowerCase();
        if (hasAttachments) {
            return "I've received your file(s). Tell me how you'd like help with these SK documents.";
        }
        if (msg.includes('resolution')) return 'I can help you create an SK Resolution. Would you like a template?';
        if (msg.includes('proposal') || msg.includes('write')) return 'For a project proposal: background, objectives, timeline, and budget.';
        if (msg.includes('event')) return 'Event planning: objectives, venue, budget, and program flow.';
        if (msg.includes('budget')) return 'SK budget tips: review allocations and include contingency.';
        if (msg.includes('guideline') || msg.includes('look')) return 'Key SK areas: KK Profiling, ABYIP, resolutions, and youth programs.';
        return "I'm here for SK tasks — resolutions, proposals, events, and budgets.";
    }

    function sendMessage(rawText, fromInput, fromAttachPool) {
        const pool = fromAttachPool || getActiveAttachPool();
        const pendingFiles = pool?.getPending?.() || [];
        const message = (rawText || '').trim().slice(0, MAX_INPUT_CHARS);
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
        chat.title = store.truncate(message || pendingFiles[0]?.name || 'Attachment', 24);
        store.save([chat, ...chats.filter(c => c.id !== chat.id)]);

        pool?.clear?.();
        renderChatMessages();
        clearInputs();
        setInputsDisabled(true);
        showTypingIndicator();
        updateRecentList();

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
            renderChatMessages();
            setInputsDisabled(false);
            updateRecentList();
            if (aiInputField && !aiModalChat.hidden) aiInputField.focus();
        }, 1100 + Math.random() * 500);
    }

    function clearInputs() {
        [aiInputField, aiInputWelcome].forEach(el => {
            if (el) {
                el.value = '';
                el.style.height = 'auto';
            }
        });
        updateCharCount(aiInputField, aiCharCountChat);
        updateCharCount(aiInputWelcome, aiCharCountWelcome);
        syncComposerState();
    }

    function startNewChat() {
        activeChatId = null;
        aiChatArea.innerHTML = '';
        setViewMode(false);
        attachWelcome?.clear?.();
        attachChat?.clear?.();
        clearInputs();
        updateRecentList();
        if (recentMenu) recentMenu.closeMenu();
        if (aiInputWelcome) aiInputWelcome.focus();
    }

    function loadChatInModal(chatId) {
        if (!chatId) {
            startNewChat();
            return;
        }
        const chat = store.find(store.load(), chatId);
        if (!chat || chat.archived) return;
        activeChatId = chatId;
        renderChatMessages();
        updateRecentList();
        if (aiInputField && chat.messages.length) aiInputField.focus();
        else if (aiInputWelcome) aiInputWelcome.focus();
        syncComposerState();
    }

    function toggleRecentsSidebar(forceOpen) {
        if (!aiRecentsSidebar || !aiRecentsToggle) return;
        const open = forceOpen === true ? true : forceOpen === false ? false : !aiRecentsSidebar.classList.contains('is-open');
        aiRecentsSidebar.classList.toggle('is-open', open);
        aiRecentsSidebar.setAttribute('aria-hidden', open ? 'false' : 'true');
        aiRecentsToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (aiModalLayout) aiModalLayout.classList.toggle('recents-open', open);
        if (open) updateRecentList();
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
            sendBtn.addEventListener('click', () => sendMessage(input.value, input, attachPool));
        }
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage(input.value, input, attachPool);
            }
        });
        input.addEventListener('input', function () {
            enforceMaxLength(this);
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 100) + 'px';
            updateCharCount(input, counterEl);
            updateSendState(input, sendBtn);
        });
        updateCharCount(input, counterEl);
        updateSendState(input, sendBtn);
    }

    window.closeAIAssistant = function () {
        if (aiModal) {
            aiModal.classList.remove('open');
            aiModal.setAttribute('hidden', '');
            aiModal.setAttribute('aria-hidden', 'true');
        }
        if (aiBtn) aiBtn.setAttribute('aria-expanded', 'false');
        if (recentMenu) recentMenu.closeMenu();
        toggleRecentsSidebar(false);
        try {
            sessionStorage.setItem('skaiModalClosed', '1');
        } catch (_) { /* ignore */ }
    };

    function openAIAssistant() {
        if (!aiModal) return;
        try { sessionStorage.removeItem('skaiModalClosed'); } catch (_) { /* ignore */ }
        aiModal.removeAttribute('hidden');
        aiModal.setAttribute('aria-hidden', 'false');
        aiModal.classList.add('open');
        if (aiBtn) aiBtn.setAttribute('aria-expanded', 'true');
        updateRecentList();
        const chat = getActiveChat();
        if (chat && chat.messages.length) renderChatMessages();
        else startNewChat();
    }

    aiBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        if (typeof closeProfileDropdown === 'function') closeProfileDropdown();
        if (typeof closeNotifDropdown === 'function') closeNotifDropdown();
        if (typeof window.closeLogoutModal === 'function') window.closeLogoutModal();
        if (aiModal.classList.contains('open')) {
            window.closeAIAssistant();
        } else {
            openAIAssistant();
        }
    });

    if (aiCloseBtn) {
        aiCloseBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            window.closeAIAssistant();
        });
    }

    function isClickInsideAi(target) {
        if (!target) return false;
        const ctxMenu = document.getElementById('aiChatContextMenu');
        return (aiMenu && aiMenu.contains(target)) || (ctxMenu && ctxMenu.contains(target));
    }

    document.addEventListener('click', function (e) {
        if (!aiModal.classList.contains('open')) return;
        if (!isClickInsideAi(e.target)) {
            window.closeAIAssistant();
        }
    }, true);

    document.addEventListener('click', function (e) {
        const link = e.target.closest('a[href]');
        if (!link || !aiModal.classList.contains('open')) return;
        const href = link.getAttribute('href');
        if (!href || href === '#' || href.startsWith('javascript:')) return;
        if (!isClickInsideAi(link)) {
            window.closeAIAssistant();
        }
    }, true);

    aiModal.querySelectorAll('a[href]').forEach(function (anchor) {
        anchor.addEventListener('click', function () {
            window.closeAIAssistant();
        });
    });

    if (aiRecentsToggle) {
        aiRecentsToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            toggleRecentsSidebar();
        });
    }

    if (aiNewChatBtn) {
        aiNewChatBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            startNewChat();
        });
    }

    document.querySelectorAll('#aiModalWelcome .ai-quick-chip').forEach(chip => {
        chip.addEventListener('click', function () {
            const prompt = this.getAttribute('data-prompt');
            if (prompt) sendMessage(prompt, aiInputWelcome, attachWelcome);
        });
    });

    bindComposer(aiInputField, aiSendBtn, aiCharCountChat, attachChat);
    bindComposer(aiInputWelcome, aiSendWelcome, aiCharCountWelcome, attachWelcome);

    if (window.SkAiModalForm?.initAttachTypePickers) {
        window.SkAiModalForm.initAttachTypePickers();
    }

    if (aiRecentSearch) {
        aiRecentSearch.addEventListener('input', updateRecentList);
    }

    recentMenu.renderList(aiRecentList, {
        variant: 'modal',
        activeChatId,
        filter: aiRecentSearch?.value || '',
        onLoadChat: loadChatInModal,
        onChange: updateRecentList,
    });

    window.closeAIAssistant();

    if (window.SkAiClose?.markAiReady) {
        window.SkAiClose.markAiReady();
    } else {
        document.documentElement.classList.add('sk-ai-ready');
        aiModal.removeAttribute('hidden');
    }
});
