{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- AI ASSISTANT MODULE - BLADE VIEW --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}

<!-- AI Assistant Modal/Dropdown -->
<div class="ai-assistant-modal" id="aiAssistantModal">
    
    <!-- Header -->
    <div class="ai-modal-header">
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
                <div class="ai-title-text">AI Assistant</div>
                <div class="ai-subtitle-text">How can I assist SK Officials today?</div>
            </div>
        </div>
        <div class="ai-header-actions">
            <button class="ai-maximize-btn" id="aiMaximizeBtn" title="Maximize">
                <svg class="maximize-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path>
                </svg>
                <svg class="minimize-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M8 3v3a2 2 0 0 1-2 2H3m18 0h-3a2 2 0 0 1-2-2V3m0 18v-3a2 2 0 0 1 2-2h3M3 16h3a2 2 0 0 1 2 2v3"></path>
                </svg>
            </button>
            <button class="ai-close-btn" id="aiCloseBtn" title="Close">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
    </div>

    <!-- Chat Area -->
    <div class="ai-chat-area" id="aiChatArea">
        <!-- Welcome message -->
        <div class="ai-welcome-message">
            <div class="ai-welcome-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 8V4H8"></path>
                    <rect width="16" height="12" x="4" y="8" rx="2"></rect>
                    <path d="M2 14h2"></path>
                    <path d="M20 14h2"></path>
                    <path d="M15 13v2"></path>
                    <path d="M9 13v2"></path>
                </svg>
            </div>
            <h3>Welcome to AI Assistant!</h3>
            <p>I'm here to help you with SK-related tasks. Try one of the suggestions below or ask me anything.</p>
        </div>
    </div>

    <!-- Suggested Prompts -->
    <div class="ai-suggestions" id="aiSuggestions">
        <div class="ai-suggestions-title">Quick Actions</div>
        <div class="ai-suggestion-cards">
            <button class="ai-suggestion-card" data-prompt="Help me create an SK Resolution">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <polyline points="10 9 9 9 8 9"></polyline>
                </svg>
                <span>Create Resolution</span>
            </button>
            <button class="ai-suggestion-card" data-prompt="Generate a project proposal for youth programs">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                <span>Generate Proposal</span>
            </button>
            <button class="ai-suggestion-card" data-prompt="Help me plan an event announcement">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                <span>Event Planning</span>
            </button>
            <button class="ai-suggestion-card" data-prompt="Assist with budget planning">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="1" x2="12" y2="23"></line>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
                <span>Budget Assistance</span>
            </button>
            <button class="ai-suggestion-card" data-prompt="Ideas for youth sports festival program">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polygon points="10 8 16 12 10 16 10 8"></polygon>
                </svg>
                <span>Sports Fest Program</span>
            </button>
            <button class="ai-suggestion-card" data-prompt="Help with scholarship program ideas">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
                    <path d="M6 12v5c3 3 9 3 12 0v-5"></path>
                </svg>
                <span>Scholarship Ideas</span>
            </button>
        </div>
    </div>

    <!-- Recent Prompts -->
    <div class="ai-recent-prompts" id="aiRecentPrompts" style="display:none;">
        <div class="ai-recent-title">Recent Prompts</div>
        <div class="ai-recent-list" id="aiRecentList"></div>
    </div>

    <!-- Input Area -->
    <div class="ai-input-area">
        <textarea 
            class="ai-input-field" 
            id="aiInputField" 
            placeholder="Ask me anything about SK tasks..."
            rows="1"
        ></textarea>
        <button class="ai-send-btn" id="aiSendBtn" title="Send message">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="22" y1="2" x2="11" y2="13"></line>
                <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
            </svg>
        </button>
    </div>

</div><!-- /ai-assistant-modal -->

<!-- Load AI Assistant CSS -->
<link rel="stylesheet" href="{{ asset('modules/AI_Assistant/assets/css/ai.css') }}">

<!-- Load AI Assistant JavaScript -->
<script src="{{ asset('modules/AI_Assistant/assets/js/ai.js') }}"></script>
