@php
    $user = auth()->user();
    $modalUserName = $user->name ?? 'SK Official';
    $modalUserFirstName = explode(' ', trim($modalUserName))[0] ?: 'there';
@endphp

<div class="ai-assistant-modal" id="aiAssistantModal" role="dialog" aria-label="SKai"
     data-user-first-name="{{ $modalUserFirstName }}">

    <div class="ai-modal-layout" id="aiModalLayout">

        {{-- Collapsible recent chats (left) --}}
        <aside class="ai-recents-sidebar" id="aiRecentsSidebar" aria-hidden="true">
            <div class="ai-recents-sidebar-inner">
                <p class="ai-recents-sidebar-label">Recent</p>
                <ul class="ai-recent-list" id="aiRecentList"></ul>
                <p class="ai-recent-empty" id="aiRecentEmpty">No chats yet</p>
            </div>
        </aside>

        <div class="ai-modal-main">

            <div class="ai-modal-header">
                <button type="button" class="ai-recents-header-btn" id="aiRecentsToggle"
                        title="Chat history" aria-label="Chat history" aria-expanded="false"
                        aria-controls="aiRecentsSidebar">
                    <svg class="ai-recents-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                    <span class="ai-recents-btn-text">History</span>
                </button>

                <div class="ai-modal-title">
                    <div class="ai-avatar">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 8V4H8"></path>
                            <rect width="16" height="12" x="4" y="8" rx="2"></rect>
                            <path d="M2 14h2"></path>
                            <path d="M20 14h2"></path>
                            <path d="M15 13v2"></path>
                            <path d="M9 13v2"></path>
                        </svg>
                    </div>
                    <div>
                        <div class="ai-title-text">SKai</div>
                        <div class="ai-subtitle-text">SK Officials Portal</div>
                    </div>
                </div>

                <div class="ai-header-actions">
                    <button type="button" class="ai-new-chat-header-btn" id="aiModalNewChatBtn" title="New chat" aria-label="New chat">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                    </button>
                    <a href="{{ route('ai-assistant') }}" class="ai-fullscreen-btn" title="Open full screen" aria-label="Open full screen">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path>
                        </svg>
                    </a>
                    <button type="button" class="ai-close-btn" id="aiCloseBtn" title="Close" aria-label="Close">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="ai-modal-body">

                {{-- Welcome (no messages yet) --}}
                <div class="ai-modal-welcome" id="aiModalWelcome">
                    <h2 class="ai-modal-greeting">How can I help, {{ $modalUserFirstName }}?</h2>
                    <div class="ai-welcome-composer">
                        <div class="ai-composer-box">
                            <button type="button" class="ai-composer-attach" id="aiModalAttachWelcome" title="Attach files or photos" aria-label="Attach files or photos">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                            </button>
                            <div class="ai-composer-input-wrap">
                                <textarea class="ai-composer-input ai-input-field" id="aiInputFieldWelcome" placeholder="Ask anything" rows="1" maxlength="500"></textarea>
                                <span class="ai-char-count" id="aiCharCountWelcome">0/500</span>
                            </div>
                            <button type="button" class="ai-send-btn" id="aiSendBtnWelcome" aria-label="Send" disabled>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="22" y1="2" x2="11" y2="13"></line>
                                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                                </svg>
                            </button>
                        </div>
                        <div class="ai-attach-preview" id="aiAttachPreviewWelcome" hidden></div>
                    </div>
                    <div class="ai-quick-chips" id="aiQuickChips">
                        <button type="button" class="ai-quick-chip" data-prompt="Help me create an SK Resolution">Create Resolution</button>
                        <button type="button" class="ai-quick-chip" data-prompt="Generate a project proposal for youth programs">Write or edit</button>
                        <button type="button" class="ai-quick-chip" data-prompt="What SK guidelines should I know?">Look something up</button>
                    </div>
                </div>

                {{-- Active conversation --}}
                <div class="ai-modal-chat" id="aiModalChat" hidden>
                    <div class="ai-chat-area" id="aiChatArea"></div>
                    <div class="ai-input-area">
                        <div class="ai-composer-box ai-composer-box--chat">
                            <button type="button" class="ai-composer-attach" id="aiModalAttachChat" title="Attach files or photos" aria-label="Attach files or photos">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                            </button>
                            <div class="ai-composer-input-wrap">
                                <textarea class="ai-composer-input ai-input-field" id="aiInputField" placeholder="Ask anything..." rows="1" maxlength="500"></textarea>
                                <span class="ai-char-count" id="aiCharCountChat">0/500</span>
                            </div>
                            <button type="button" class="ai-send-btn" id="aiSendBtn" aria-label="Send" disabled>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="22" y1="2" x2="11" y2="13"></line>
                                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                                </svg>
                            </button>
                        </div>
                        <div class="ai-attach-preview" id="aiAttachPreviewChat" hidden></div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
