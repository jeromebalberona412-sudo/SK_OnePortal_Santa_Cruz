<!DOCTYPE html>
<html lang="en">
<head>
    @include('layout::favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SKai — SK Officials Portal</title>
    @vite([
        'app/Modules/AI_Assistant/assets/css/ai-page.css',
        'app/Modules/AI_Assistant/assets/css/ai-recent-menu.css',
        'app/Modules/AI_Assistant/assets/js/ai-assistant-modal-form.js',
        'app/Modules/AI_Assistant/assets/js/ai-storage.js',
        'app/Modules/AI_Assistant/assets/js/ai-toast.js',
        'app/Modules/AI_Assistant/assets/js/ai-recent-menu.js',
        'app/Modules/AI_Assistant/assets/js/ai-page.js',
    ])
</head>
<body class="ai-page-body">

<div class="ai-app" id="aiApp">

    <!-- ══ Sidebar ═══════════════════════════════════════════════════════ -->
    <aside class="ai-sidebar" id="aiSidebar">
        <div class="ai-sidebar-top">
            <a href="{{ route('dashboard') }}" class="ai-back-portal" id="aiBackPortal" title="Back to Portal">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <span>Back to Portal</span>
            </a>

            <button type="button" class="ai-new-chat-btn" id="aiNewChatBtn">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                New chat
            </button>

            <div class="ai-sidebar-search">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" id="aiSearchChats" placeholder="Search chats" autocomplete="off">
            </div>
        </div>

        <div class="ai-sidebar-recents">
            <div class="ai-recents-label">Recent Prompts</div>
            <ul class="ai-chat-list" id="aiChatList"></ul>
            <p class="ai-chat-list-empty" id="aiChatListEmpty">No conversations yet</p>
        </div>

        <div class="ai-sidebar-footer">
            <a href="{{ route('profile') }}" class="ai-user-card">
                <div class="ai-user-avatar">{{ $userInitials }}</div>
                <div class="ai-user-info">
                    <span class="ai-user-name">{{ $userName }}</span>
                    <span class="ai-user-role">SK Official</span>
                </div>
            </a>
        </div>
    </aside>

    <!-- ══ Main ════════════════════════════════════════════════════════════ -->
    <div class="ai-main">

        <header class="ai-topbar">
            <button type="button" class="ai-sidebar-toggle" id="aiSidebarToggle" aria-label="Show or hide recent prompts" title="Recent prompts">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>

            <div class="ai-topbar-title">
                <div class="ai-topbar-avatar">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 8V4H8"></path>
                        <rect width="16" height="12" x="4" y="8" rx="2"></rect>
                        <path d="M2 14h2"></path>
                        <path d="M20 14h2"></path>
                        <path d="M15 13v2"></path>
                        <path d="M9 13v2"></path>
                    </svg>
                </div>
                <div class="ai-topbar-brand">
                    <span class="ai-topbar-name">SKai</span>
                    <span class="ai-topbar-tagline">SK Officials Portal</span>
                </div>
            </div>

            <div class="ai-topbar-actions">
                <a href="{{ route('dashboard') }}" class="ai-topbar-link">Back to Dashboard</a>
            </div>
        </header>

        <div class="ai-content" id="aiContent">

            <!-- Welcome (empty chat) -->
            <div class="ai-welcome-view" id="aiWelcomeView">
                <h1 class="ai-greeting">How can I help, {{ $userFirstName }}?</h1>

                <div class="ai-composer">
                    <div class="ai-composer-box">
                        <div class="ai-composer-input-wrap">
                            <textarea
                                class="ai-composer-input"
                                id="aiInputField"
                                placeholder="Ask anything"
                                rows="1"
                                maxlength="500"
                            ></textarea>
                            <span class="ai-char-count" id="aiCharCountPageWelcome">0/500</span>
                        </div>
                        <button type="button" class="ai-composer-send" id="aiSendBtn" title="Send message" aria-label="Send" disabled>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 12h14"></path>
                                <path d="m12 5 7 7-7 7"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Active chat -->
            <div class="ai-chat-view" id="aiChatView" hidden>
                <div class="ai-messages" id="aiChatArea"></div>

                <div class="ai-composer ai-composer--bottom">
                    <div class="ai-composer-box">
                        <div class="ai-composer-input-wrap">
                            <textarea
                                class="ai-composer-input"
                                id="aiInputFieldChat"
                                placeholder="Ask anything"
                                rows="1"
                                maxlength="500"
                            ></textarea>
                            <span class="ai-char-count" id="aiCharCountPageChat">0/500</span>
                        </div>
                        <button type="button" class="ai-composer-send" id="aiSendBtnChat" title="Send message" aria-label="Send" disabled>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 12h14"></path>
                                <path d="m12 5 7 7-7 7"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="ai-sidebar-overlay" id="aiSidebarOverlay"></div>
</div>

@include('AI_Assistant::components.recent-context-menu')
@include('AI_Assistant::components.toast-container')

<script>
    window.AI_ASSISTANT_CONFIG = {
        userName: @json($userName),
        userFirstName: @json($userFirstName),
    };
</script>

</body>
</html>
