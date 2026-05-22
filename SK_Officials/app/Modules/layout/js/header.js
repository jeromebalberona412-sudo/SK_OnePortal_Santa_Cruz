// Header JavaScript Functionality
// Single DOMContentLoaded — no duplicate listeners

document.addEventListener('DOMContentLoaded', function () {
    initializeHeader();
});

function initializeHeader() {
    const sidebarToggle    = document.getElementById('sidebarToggle');
    const userMenuToggle   = document.getElementById('userMenuToggle');
    const userDropdown     = document.getElementById('userDropdown');
    const searchInput      = document.querySelector('.search-input');
    const searchBtn        = document.querySelector('.search-btn');
    const notificationBtn  = document.querySelector('.notification-btn');

    // ── Sidebar toggle ──────────────────────────────────────────────────────
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', responsiveToggleSidebar);
    }
    syncToggleState();

    // ── Profile dropdown — click-only, no hover ─────────────────────────────
    if (userMenuToggle && userDropdown) {
        // Toggle open/close on button click
        userMenuToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            const isOpen = userDropdown.classList.contains('open');
            closeProfileDropdown(); // close first (handles any stale state)
            closeNotifDropdown();   // close notification dropdown if open
            if (!isOpen) {
                userDropdown.classList.add('open');
                userMenuToggle.setAttribute('aria-expanded', 'true');
            }
        });
    }

    // ── Change Password trigger ─────────────────────────────────────────────
    const changePasswordTrigger = document.getElementById('changePasswordTrigger');
    if (changePasswordTrigger) {
        changePasswordTrigger.addEventListener('click', function (e) {
            closeProfileDropdown();
            // Navigate to change-password page (let the href handle it)
        });
    }

    // ── Logout ──────────────────────────────────────────────────────────────
    initializeLogout();

    // ── Search ──────────────────────────────────────────────────────────────
    if (searchInput && searchBtn) {
        initializeSearch();
    }

    // ── Notifications ───────────────────────────────────────────────────────
    if (notificationBtn) {
        initializeNotifications();
    }

    // ── AI Assistant ────────────────────────────────────────────────────────
    initializeAIAssistant();

    // ── Global outside-click — single listener on document ──────────────────
    document.addEventListener('click', function (e) {
        // Close profile dropdown when clicking outside
        if (userDropdown && userMenuToggle) {
            if (!userMenuToggle.contains(e.target) && !userDropdown.contains(e.target)) {
                closeProfileDropdown();
            }
        }
    });

    // ── Escape key closes dropdown ──────────────────────────────────────────
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeProfileDropdown();
            closeNotifDropdown();
            closeAIAssistant();
            const logoutModal = document.getElementById('logoutModal');
            if (logoutModal && logoutModal.style.display === 'flex') {
                logoutModal.style.display = 'none';
                document.body.style.overflow = '';
                document.documentElement.style.overflow = '';
                const mainContent = document.getElementById('mainContent') || document.querySelector('.main-content');
                if (mainContent) mainContent.style.overflow = '';
            }
        }
        // Ctrl/Cmd + K → focus search
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            const si = document.querySelector('.search-input');
            if (si) si.focus();
        }
    });
}

// ── Profile dropdown helpers ────────────────────────────────────────────────
function closeProfileDropdown() {
    const userDropdown   = document.getElementById('userDropdown');
    const userMenuToggle = document.getElementById('userMenuToggle');
    if (userDropdown)   userDropdown.classList.remove('open');
    if (userMenuToggle) userMenuToggle.setAttribute('aria-expanded', 'false');
}

// ── Sidebar toggle ──────────────────────────────────────────────────────────
function responsiveToggleSidebar() {
    const sidebar      = document.getElementById('mainSidebar');
    const toggle       = document.getElementById('sidebarToggle');
    let   overlay      = document.querySelector('.sidebar-overlay');

    if (!sidebar || !toggle) return;

    if (window.innerWidth <= 768) {
        // Mobile: slide in/out
        const isOpen = sidebar.classList.contains('open');
        if (isOpen) {
            sidebar.classList.remove('open');
            toggle.classList.remove('active');
            if (overlay) overlay.classList.remove('show');
        } else {
            sidebar.classList.add('open');
            toggle.classList.add('active');
            if (!overlay) overlay = createOverlay();
            overlay.classList.add('show');
        }
    } else {
        // Desktop: collapse / expand permanently
        const isCollapsed = sidebar.classList.contains('collapsed');
        const mainContent = document.querySelector('.main-content');

        if (isCollapsed) {
            sidebar.classList.remove('collapsed');
            if (mainContent) {
                mainContent.classList.remove('sidebar-collapsed');
            }
            toggle.classList.add('active');
        } else {
            sidebar.classList.add('collapsed');
            if (mainContent) {
                mainContent.classList.add('sidebar-collapsed');
            }
            toggle.classList.remove('active');
        }
        if (overlay) overlay.classList.remove('show');
    }
}

function syncToggleState() {
    const sidebar = document.getElementById('mainSidebar');
    const toggle  = document.getElementById('sidebarToggle');
    if (!sidebar || !toggle || window.innerWidth <= 768) return;

    if (sidebar.classList.contains('collapsed')) {
        toggle.classList.remove('active');
    } else {
        toggle.classList.add('active');
    }
}

function createOverlay() {
    const overlay = document.createElement('div');
    overlay.className = 'sidebar-overlay';
    overlay.addEventListener('click', function () {
        const sidebar = document.getElementById('mainSidebar');
        const toggle  = document.getElementById('sidebarToggle');
        if (sidebar) sidebar.classList.remove('open');
        if (toggle)  toggle.classList.remove('active');
        overlay.classList.remove('show');
    });
    document.body.appendChild(overlay);
    return overlay;
}

// ── Search ──────────────────────────────────────────────────────────────────
function initializeSearch() {
    const searchInput = document.querySelector('.search-input');
    const searchBtn   = document.querySelector('.search-btn');

    if (searchInput) {
        searchInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') performSearch();
        });
        let searchTimeout;
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (this.value.trim()) performSearch();
            }, 300);
        });
    }
    if (searchBtn) {
        searchBtn.addEventListener('click', performSearch);
    }
}

function performSearch() {
    const searchInput = document.querySelector('.search-input');
    const query = searchInput ? searchInput.value.trim() : '';
    if (query) console.log('Searching for:', query);
}

// ── Notifications ────────────────────────────────────────────────────────────
function initializeNotifications() {
    const notifBtn      = document.getElementById('notificationBtn');
    const notifDropdown = document.getElementById('notifDropdown');
    const notifMenu     = document.getElementById('notifMenu');
    const markAllBtn    = document.getElementById('notifMarkAllBtn');
    const notifList     = document.getElementById('notifList');
    const notifBadge    = document.getElementById('notifBadge');
    const notifCountPill = document.getElementById('notifCountPill');
    const notifEmpty    = document.getElementById('notifEmpty');

    if (!notifBtn || !notifDropdown) return;

    /* ── Toggle open/close ── */
    notifBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        const isOpen = notifDropdown.classList.contains('open');

        // Close profile dropdown if open
        closeProfileDropdown();

        if (isOpen) {
            closeNotifDropdown();
        } else {
            notifDropdown.classList.add('open');
            notifBtn.setAttribute('aria-expanded', 'true');
        }
    });

    /* ── Close on outside click ── */
    document.addEventListener('click', function (e) {
        if (notifMenu && !notifMenu.contains(e.target)) {
            closeNotifDropdown();
        }
    });

    /* ── Escape closes it ── */
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeNotifDropdown();
    });

    /* ── Mark individual item as read on click ── */
    if (notifList) {
        notifList.addEventListener('click', function (e) {
            const item = e.target.closest('.notif-item');
            if (!item) return;
            if (item.classList.contains('notif-unread')) {
                item.classList.remove('notif-unread');
                const dot = item.querySelector('.notif-unread-dot');
                if (dot) dot.remove();
                updateUnreadCount();
            }
        });
    }

    /* ── Mark all as read ── */
    if (markAllBtn) {
        markAllBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            const unreadItems = notifList ? notifList.querySelectorAll('.notif-unread') : [];
            unreadItems.forEach(function (item) {
                item.classList.remove('notif-unread');
                const dot = item.querySelector('.notif-unread-dot');
                if (dot) dot.remove();
            });
            updateUnreadCount();
        });
    }

    /* ── Count helpers ── */
    function updateUnreadCount() {
        const unread = notifList ? notifList.querySelectorAll('.notif-unread').length : 0;

        if (notifBadge) {
            notifBadge.textContent = unread;
            notifBadge.style.display = unread > 0 ? 'flex' : 'none';
        }
        if (notifCountPill) {
            notifCountPill.textContent = unread;
            notifCountPill.style.display = unread > 0 ? 'inline' : 'none';
        }
        if (notifEmpty && notifList) {
            const hasItems = notifList.querySelectorAll('.notif-item').length > 0;
            notifEmpty.style.display = hasItems ? 'none' : 'flex';
        }
    }
}

function closeNotifDropdown() {
    const notifDropdown = document.getElementById('notifDropdown');
    const notifBtn      = document.getElementById('notificationBtn');
    if (notifDropdown) notifDropdown.classList.remove('open');
    if (notifBtn)      notifBtn.setAttribute('aria-expanded', 'false');
}

// ── Logout ───────────────────────────────────────────────────────────────────
function initializeLogout() {
    const logoutTrigger  = document.getElementById('logoutTrigger');
    const logoutModal    = document.getElementById('logoutModal');
    const cancelLogout   = document.getElementById('cancelLogout');
    const confirmLogout  = document.getElementById('confirmLogout');

    if (logoutTrigger) {
        logoutTrigger.addEventListener('click', function (e) {
            e.preventDefault();
            closeProfileDropdown();
            if (logoutModal) {
                logoutModal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
                document.documentElement.style.overflow = 'hidden';
                const mainContent = document.getElementById('mainContent') || document.querySelector('.main-content');
                if (mainContent) mainContent.style.overflow = 'hidden';
            }
        });
    }

    function closeLogoutModal() {
        if (logoutModal) logoutModal.style.display = 'none';
        document.body.style.overflow = '';
        document.documentElement.style.overflow = '';
        const mainContent = document.getElementById('mainContent') || document.querySelector('.main-content');
        if (mainContent) mainContent.style.overflow = '';
    }

    if (cancelLogout) {
        cancelLogout.addEventListener('click', closeLogoutModal);
    }

    if (confirmLogout) {
        confirmLogout.addEventListener('click', function () {
            closeLogoutModal();
            handleLogout();
        });
    }

    if (logoutModal) {
        logoutModal.addEventListener('click', function (e) {
            if (e.target === logoutModal) closeLogoutModal();
        });
    }
}

function handleLogout() {
    const logoutForm = document.getElementById('logoutForm');
    sessionStorage.removeItem('isLoggedIn');
    sessionStorage.removeItem('userEmail');
    if (logoutForm) logoutForm.submit();
}

// ── Resize ───────────────────────────────────────────────────────────────────
window.addEventListener('resize', function () {
    const sidebar  = document.getElementById('mainSidebar');
    const overlay  = document.querySelector('.sidebar-overlay');
    if (window.innerWidth > 768) {
        if (overlay)  overlay.classList.remove('show');
        if (sidebar)  sidebar.classList.remove('open');
        syncToggleState();
    }
});

// ── Exports ──────────────────────────────────────────────────────────────────
window.HeaderFunctions = {
    toggleSidebar:          responsiveToggleSidebar,
    closeProfileDropdown:   closeProfileDropdown,
    performSearch:          performSearch,
    updateNotificationBadge: updateNotificationBadge,
    initializeLogout:       initializeLogout,
    handleLogout:           handleLogout,
};

// ══════════════════════════════════════════════════════════════════════════
// AI ASSISTANT FUNCTIONALITY
// ══════════════════════════════════════════════════════════════════════════

function initializeAIAssistant() {
    const aiBtn = document.getElementById('aiAssistantBtn');
    const aiModal = document.getElementById('aiAssistantModal');
    const aiMenu = document.getElementById('aiAssistantMenu');
    const aiCloseBtn = document.getElementById('aiCloseBtn');
    const aiInputField = document.getElementById('aiInputField');
    const aiSendBtn = document.getElementById('aiSendBtn');
    const aiChatArea = document.getElementById('aiChatArea');
    const aiSuggestions = document.getElementById('aiSuggestions');
    const aiRecentPrompts = document.getElementById('aiRecentPrompts');
    const aiRecentList = document.getElementById('aiRecentList');

    if (!aiBtn || !aiModal) return;

    // State
    let isTyping = false;
    let chatHistory = [];
    let recentPrompts = loadRecentPrompts();

    // Toggle AI Assistant
    aiBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        const isOpen = aiModal.classList.contains('open');

        closeProfileDropdown();
        closeNotifDropdown();

        if (isOpen) {
            closeAIAssistant();
        } else {
            aiModal.classList.add('open');
            aiBtn.setAttribute('aria-expanded', 'true');
            aiInputField.focus();
            updateRecentPrompts();
        }
    });

    // Close button
    if (aiCloseBtn) {
        aiCloseBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            closeAIAssistant();
        });
    }

    // Close on outside click
    document.addEventListener('click', function (e) {
        if (aiMenu && !aiMenu.contains(e.target)) {
            closeAIAssistant();
        }
    });

    // Suggestion cards
    const suggestionCards = document.querySelectorAll('.ai-suggestion-card');
    suggestionCards.forEach(card => {
        card.addEventListener('click', function () {
            const prompt = this.getAttribute('data-prompt');
            if (prompt) {
                aiInputField.value = prompt;
                sendMessage();
            }
        });
    });

    // Send button
    if (aiSendBtn) {
        aiSendBtn.addEventListener('click', sendMessage);
    }

    // Enter key to send
    if (aiInputField) {
        aiInputField.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        // Auto-resize textarea
        aiInputField.addEventListener('input', function () {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 100) + 'px';
        });
    }

    // Send message function
    function sendMessage() {
        const message = aiInputField.value.trim();
        if (!message || isTyping) return;

        // Add user message
        addMessage(message, 'user');
        
        // Save to recent prompts
        saveRecentPrompt(message);
        
        // Clear input
        aiInputField.value = '';
        aiInputField.style.height = 'auto';

        // Hide suggestions after first message
        if (aiSuggestions) {
            aiSuggestions.style.display = 'none';
        }

        // Show typing indicator
        showTypingIndicator();

        // Simulate AI response
        setTimeout(() => {
            hideTypingIndicator();
            const response = generateAIResponse(message);
            addMessage(response, 'ai');
        }, 1500 + Math.random() * 1000);
    }

    // Add message to chat
    function addMessage(text, sender) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `ai-chat-message ${sender}`;

        const avatar = document.createElement('div');
        avatar.className = `ai-message-avatar ${sender}`;
        
        if (sender === 'ai') {
            avatar.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 8V4H8"></path>
                    <rect width="16" height="12" x="4" y="8" rx="2"></rect>
                    <path d="M2 14h2"></path>
                    <path d="M20 14h2"></path>
                    <path d="M15 13v2"></path>
                    <path d="M9 13v2"></path>
                </svg>
            `;
        } else {
            avatar.textContent = 'U';
        }

        const content = document.createElement('div');
        content.className = 'ai-message-content';

        const bubble = document.createElement('div');
        bubble.className = 'ai-message-bubble';
        bubble.textContent = text;

        const time = document.createElement('div');
        time.className = 'ai-message-time';
        time.textContent = getCurrentTime();

        content.appendChild(bubble);
        content.appendChild(time);

        messageDiv.appendChild(avatar);
        messageDiv.appendChild(content);

        // Remove welcome message if exists
        const welcomeMsg = aiChatArea.querySelector('.ai-welcome-message');
        if (welcomeMsg) {
            welcomeMsg.remove();
        }

        aiChatArea.appendChild(messageDiv);
        scrollToBottom();

        chatHistory.push({ text, sender, time: getCurrentTime() });
    }

    // Typing indicator
    function showTypingIndicator() {
        isTyping = true;
        aiSendBtn.disabled = true;
        aiInputField.disabled = true;

        const typingDiv = document.createElement('div');
        typingDiv.className = 'ai-typing-indicator';
        typingDiv.id = 'aiTypingIndicator';

        typingDiv.innerHTML = `
            <div class="ai-message-avatar ai">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 8V4H8"></path>
                    <rect width="16" height="12" x="4" y="8" rx="2"></rect>
                    <path d="M2 14h2"></path>
                    <path d="M20 14h2"></path>
                    <path d="M15 13v2"></path>
                    <path d="M9 13v2"></path>
                </svg>
            </div>
            <div class="ai-message-content">
                <div class="ai-message-bubble">
                    <div class="ai-typing-dot"></div>
                    <div class="ai-typing-dot"></div>
                    <div class="ai-typing-dot"></div>
                </div>
            </div>
        `;

        aiChatArea.appendChild(typingDiv);
        scrollToBottom();
    }

    function hideTypingIndicator() {
        isTyping = false;
        aiSendBtn.disabled = false;
        aiInputField.disabled = false;

        const typingIndicator = document.getElementById('aiTypingIndicator');
        if (typingIndicator) {
            typingIndicator.remove();
        }
    }

    // Generate AI response (dummy responses)
    function generateAIResponse(userMessage) {
        const msg = userMessage.toLowerCase();

        // Resolution keywords
        if (msg.includes('resolution')) {
            return "I can help you create an SK Resolution! A typical SK Resolution includes: (1) Title and Resolution Number, (2) Whereas clauses stating the background, (3) Resolved clauses with specific actions, (4) Signatures of SK officials. Would you like me to provide a template?";
        }

        // Proposal keywords
        if (msg.includes('proposal') || msg.includes('project')) {
            return "For a project proposal, you'll need: (1) Project Title, (2) Background/Rationale, (3) Objectives, (4) Target Beneficiaries, (5) Activities and Timeline, (6) Budget Requirements, (7) Expected Outcomes. I can help you draft each section!";
        }

        // Event keywords
        if (msg.includes('event') || msg.includes('planning')) {
            return "Event planning checklist: (1) Define event objectives, (2) Set date and venue, (3) Create budget, (4) Form organizing committee, (5) Prepare program flow, (6) Coordinate logistics, (7) Promote the event, (8) Prepare documentation. What type of event are you planning?";
        }

        // Budget keywords
        if (msg.includes('budget')) {
            return "For SK budget planning: (1) Review your allocated funds, (2) Prioritize programs based on community needs, (3) Allocate funds per project, (4) Include contingency (10-15%), (5) Get approval from SK Council, (6) Submit to Sanggunian. Need help with a specific budget item?";
        }

        // Sports keywords
        if (msg.includes('sports') || msg.includes('fest')) {
            return "Youth Sports Festival ideas: (1) Basketball/Volleyball tournaments, (2) Fun runs, (3) Zumba sessions, (4) Chess competitions, (5) E-sports tournaments, (6) Traditional Filipino games (Patintero, Tumbang Preso). Which sports would you like to include?";
        }

        // Scholarship keywords
        if (msg.includes('scholarship')) {
            return "SK Scholarship Program components: (1) Eligibility criteria (age, residency, academic performance), (2) Application requirements, (3) Selection process, (4) Scholarship amount/coverage, (5) Renewal conditions, (6) Monitoring system. Would you like help designing the application form?";
        }

        // Youth program keywords
        if (msg.includes('youth') || msg.includes('program')) {
            return "Popular SK Youth Programs: (1) Skills training workshops, (2) Mental health awareness campaigns, (3) Environmental projects, (4) Livelihood programs, (5) Arts and culture activities, (6) Leadership seminars. What area interests you most?";
        }

        // Tree planting keywords
        if (msg.includes('tree') || msg.includes('environment')) {
            return "Tree Planting Project guide: (1) Coordinate with DENR/LGU, (2) Select appropriate tree species, (3) Identify planting site, (4) Recruit volunteers, (5) Prepare tools and materials, (6) Conduct orientation, (7) Document with photos, (8) Plan monitoring schedule. Need help with the proposal?";
        }

        // Mental health keywords
        if (msg.includes('mental') || msg.includes('health')) {
            return "Mental Health Seminar planning: (1) Partner with health professionals, (2) Topics: stress management, self-care, seeking help, (3) Interactive activities, (4) Provide resource materials, (5) Create safe space for sharing, (6) Follow-up support system. Would you like speaker suggestions?";
        }

        // Default response
        const responses = [
            "That's an interesting question! As an SK official, you have many resources available. Could you provide more details so I can assist you better?",
            "I'm here to help with SK-related tasks! Could you clarify what specific assistance you need?",
            "Great question! For SK officials, I can help with resolutions, proposals, event planning, budgeting, and program ideas. What would you like to focus on?",
            "I'd be happy to help! Could you tell me more about what you're trying to accomplish?",
            "As your AI assistant for SK tasks, I can provide guidance on various topics. What specific area do you need help with?"
        ];

        return responses[Math.floor(Math.random() * responses.length)];
    }

    // Recent prompts management
    function saveRecentPrompt(prompt) {
        recentPrompts = recentPrompts.filter(p => p !== prompt);
        recentPrompts.unshift(prompt);
        recentPrompts = recentPrompts.slice(0, 5);
        localStorage.setItem('aiRecentPrompts', JSON.stringify(recentPrompts));
        updateRecentPrompts();
    }

    function loadRecentPrompts() {
        try {
            const saved = localStorage.getItem('aiRecentPrompts');
            return saved ? JSON.parse(saved) : [];
        } catch {
            return [];
        }
    }

    function updateRecentPrompts() {
        if (!aiRecentList || !aiRecentPrompts) return;

        if (recentPrompts.length === 0) {
            aiRecentPrompts.style.display = 'none';
            return;
        }

        aiRecentPrompts.style.display = 'block';
        aiRecentList.innerHTML = '';

        recentPrompts.forEach(prompt => {
            const item = document.createElement('div');
            item.className = 'ai-recent-item';
            item.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
                <span>${prompt.length > 50 ? prompt.substring(0, 50) + '...' : prompt}</span>
            `;
            item.addEventListener('click', function () {
                aiInputField.value = prompt;
                aiInputField.focus();
            });
            aiRecentList.appendChild(item);
        });
    }

    // Utility functions
    function scrollToBottom() {
        aiChatArea.scrollTop = aiChatArea.scrollHeight;
    }

    function getCurrentTime() {
        const now = new Date();
        return now.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
    }
}

function closeAIAssistant() {
    const aiModal = document.getElementById('aiAssistantModal');
    const aiBtn = document.getElementById('aiAssistantBtn');
    if (aiModal) aiModal.classList.remove('open');
    if (aiBtn) aiBtn.setAttribute('aria-expanded', 'false');
}
