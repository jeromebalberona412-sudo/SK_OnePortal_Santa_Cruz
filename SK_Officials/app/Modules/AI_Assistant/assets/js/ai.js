// ══════════════════════════════════════════════════════════════════════════
// AI ASSISTANT — Full-screen page
// ══════════════════════════════════════════════════════════════════════════

document.addEventListener('DOMContentLoaded', function () {
    const STORAGE_KEY = 'skAiChats';
    const config = window.AI_ASSISTANT_CONFIG || {};

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

    const inputWelcome = document.getElementById('aiInputField');
    const inputChat = document.getElementById('aiInputFieldChat');
    const sendWelcome = document.getElementById('aiSendBtn');
    const sendChat = document.getElementById('aiSendBtnChat');

    let chats = loadChats();
    let activeChatId = null;
    let isTyping = false;

    function loadChats() {
        try {
            const saved = localStorage.getItem(STORAGE_KEY);
            return saved ? JSON.parse(saved) : [];
        } catch {
            return [];
        }
    }

    function saveChats() {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(chats));
    }

    function generateId() {
        return 'chat_' + Date.now() + '_' + Math.random().toString(36).slice(2, 9);
    }

    function getActiveChat() {
        return chats.find(c => c.id === activeChatId) || null;
    }

    function truncateLabel(text, max) {
        const t = (text || '').trim();
        if (t.length <= max) return t;
        return t.slice(0, max).trim() + '…';
    }

    /** Show welcome OR chat — never both (fixes duplicate Ask anything) */
    function syncViewState() {
        const chat = getActiveChat();
        const hasMessages = !!(chat && chat.messages.length > 0);

        if (aiWelcomeView) aiWelcomeView.hidden = hasMessages;
        if (aiChatView) aiChatView.hidden = !hasMessages;

        if (!hasMessages) {
            if (inputWelcome) inputWelcome.focus();
        } else if (inputChat) {
            inputChat.focus();
        }
    }

    function buildMessageEl(text, sender) {
        const row = document.createElement('div');
        row.className = `ai-msg ai-msg--${sender}`;

        if (sender === 'user') {
            const bubble = document.createElement('div');
            bubble.className = 'ai-msg-bubble';
            bubble.textContent = text;
            row.appendChild(bubble);
        } else {
            const textEl = document.createElement('div');
            textEl.className = 'ai-msg-text';
            textEl.textContent = text;
            row.appendChild(textEl);
        }
        return row;
    }

    function renderMessages() {
        if (!aiChatArea) return;

        const chat = getActiveChat();
        aiChatArea.innerHTML = '';
        syncViewState();

        if (!chat || !chat.messages.length) return;

        chat.messages.forEach(msg => {
            aiChatArea.appendChild(buildMessageEl(msg.text, msg.sender));
        });
        scrollToBottom();
    }

    function scrollToBottom() {
        if (aiChatArea) aiChatArea.scrollTop = aiChatArea.scrollHeight;
    }

    function setActiveChat(id) {
        activeChatId = id || null;
        renderChatList();
        renderMessages();
        closeMobileSidebar();
    }

    function startNewChat() {
        activeChatId = null;
        if (aiChatArea) aiChatArea.innerHTML = '';
        syncViewState();
        clearInputs();
        renderChatList();
        if (inputWelcome) inputWelcome.focus();
    }

    function renderChatList(filter = '') {
        if (!aiChatList) return;

        const query = filter.trim().toLowerCase();
        const filtered = query
            ? chats.filter(c => c.title.toLowerCase().includes(query))
            : chats;

        aiChatList.innerHTML = '';

        filtered.forEach(chat => {
            const li = document.createElement('li');
            li.className = 'ai-chat-list-item';

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'ai-chat-list-btn' + (chat.id === activeChatId ? ' active' : '');
            btn.textContent = truncateLabel(chat.title, 22);
            btn.title = chat.title;
            btn.addEventListener('click', () => setActiveChat(chat.id));

            li.appendChild(btn);
            aiChatList.appendChild(li);
        });

        if (aiChatListEmpty) {
            aiChatListEmpty.classList.toggle('hidden', filtered.length > 0);
        }
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
        [inputWelcome, inputChat, sendWelcome, sendChat].forEach(el => {
            if (el) el.disabled = disabled;
        });
    }

    function generateAIResponse(userMessage) {
        const lower = userMessage.toLowerCase();
        if (lower.includes('resolution')) {
            return "I can help you create an SK Resolution. Include title, whereas clauses, and resolved clauses. Would you like a template?";
        }
        if (lower.includes('proposal') || lower.includes('project') || lower.includes('write')) {
            return "For a project proposal: background, objectives, beneficiaries, timeline, budget, and outcomes. Which section should we draft first?";
        }
        if (lower.includes('event') || lower.includes('planning')) {
            return "Event planning: objectives, date, venue, budget, committee, program flow, and logistics. What event are you planning?";
        }
        if (lower.includes('budget')) {
            return "SK budget tips: review allocations, prioritize programs, add contingency, and get council approval.";
        }
        if (lower.includes('guideline') || lower.includes('look')) {
            return "Key SK areas: KK Profiling, ABYIP, resolutions, budgets, and youth programs. Which topic do you need?";
        }
        return "I'm here for SK tasks — resolutions, proposals, events, and budgets. How can I help?";
    }

    function sendMessage(rawMessage) {
        const message = (rawMessage || '').trim();
        if (!message || isTyping) return;

        let chat = getActiveChat();
        if (!chat) {
            activeChatId = generateId();
            chat = {
                id: activeChatId,
                title: truncateLabel(message, 28),
                messages: [],
                updatedAt: Date.now(),
            };
            chats.unshift(chat);
        }

        chat.messages.push({ text: message, sender: 'user' });
        chat.updatedAt = Date.now();
        chat.title = truncateLabel(message, 28);
        chats = [chat, ...chats.filter(c => c.id !== chat.id)].slice(0, 30);
        saveChats();

        renderMessages();
        clearInputs();
        setSendDisabled(true);
        showTypingIndicator();

        setTimeout(() => {
            hideTypingIndicator();
            chat.messages.push({ text: generateAIResponse(message), sender: 'ai' });
            chat.updatedAt = Date.now();
            saveChats();
            renderMessages();
            renderChatList(aiSearchChats ? aiSearchChats.value : '');
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
    }

    function bindComposer(input, sendBtn) {
        if (!input) return;
        if (sendBtn) {
            sendBtn.addEventListener('click', () => sendMessage(input.value));
        }
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage(input.value);
            }
        });
        input.addEventListener('input', function () {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 200) + 'px';
        });
    }

    function closeMobileSidebar() {
        if (aiSidebar) aiSidebar.classList.remove('open');
        if (aiSidebarOverlay) aiSidebarOverlay.classList.remove('show');
    }

    if (aiNewChatBtn) {
        aiNewChatBtn.addEventListener('click', () => {
            startNewChat();
            closeMobileSidebar();
        });
    }

    if (aiSearchChats) {
        aiSearchChats.addEventListener('input', function () {
            renderChatList(this.value);
        });
    }

    document.querySelectorAll('.ai-quick-chip').forEach(chip => {
        chip.addEventListener('click', function () {
            const prompt = this.getAttribute('data-prompt');
            if (prompt) sendMessage(prompt);
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

    bindComposer(inputWelcome, sendWelcome);
    bindComposer(inputChat, sendChat);

    renderChatList();

    const urlChatId = new URLSearchParams(window.location.search).get('chat');
    if (urlChatId && chats.some(c => c.id === urlChatId)) {
        setActiveChat(urlChatId);
    } else {
        const latestWithMessages = chats.find(c => c.messages && c.messages.length > 0);
        if (latestWithMessages) {
            setActiveChat(latestWithMessages.id);
        } else {
            startNewChat();
        }
    }
});
