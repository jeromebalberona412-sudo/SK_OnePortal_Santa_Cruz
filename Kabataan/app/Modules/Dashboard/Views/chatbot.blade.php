{{-- Chatbot Popover — included in Dashboard and Profile navbars --}}
<div class="chatbot-nav-wrapper">
    <button class="nav-icon-btn chatbot-nav-btn" id="chatbotNavBtn" title="Chatbot" onclick="toggleChatbotPopover()">
        <svg viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"/>
        </svg>
        <span class="cp-unread-badge"></span>
    </button>

    <div class="chatbot-popover" id="chatbotPopover" role="dialog" aria-label="SK Chatbot">
        <div class="cp-inner">
            <div class="cp-header">
                <div class="cp-bot-icon">
                    <svg viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="cp-header-info">
                    <div class="cp-header-name">SK Assistant</div>
                    <div class="cp-header-status">
                        <span class="cp-status-dot"></span>
                        Online
                    </div>
                </div>
                <button class="cp-close-btn" onclick="closeChatbotPopover()" aria-label="Close chatbot">
                    <svg viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>

            <div class="cp-topics">
                <button class="cp-topic-btn" onclick="cpSendTopic('Programs')">📋 Programs</button>
                <button class="cp-topic-btn" onclick="cpSendTopic('Events')">📅 Events</button>
                <button class="cp-topic-btn" onclick="cpSendTopic('Scholarship')">🎓 Scholarship</button>
                <button class="cp-topic-btn" onclick="cpSendTopic('How to apply')">📝 How to Apply</button>
                <button class="cp-topic-btn" onclick="cpSendTopic('Contact SK')">📞 Contact SK</button>
            </div>

            <div class="cp-messages" id="cpMessages">
                <div class="cp-msg bot">
                    <div class="cp-msg-avatar bot">
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="cp-msg-body">
                        <div class="cp-bubble">Kumusta! 👋 Ako si SK Assistant. Paano kita matutulungan ngayon?</div>
                        <span class="cp-msg-time" id="cpWelcomeTime"></span>
                    </div>
                </div>
                <div class="cp-typing" id="cpTyping" style="display:none;">
                    <div class="cp-msg-avatar bot">
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="cp-typing-dots">
                        <span></span><span></span><span></span>
                    </div>
                </div>
            </div>

            <div class="cp-input-area">
                <form class="cp-form" id="cpForm" onsubmit="cpHandleSubmit(event)">
                    <input type="text" class="cp-input" id="cpInput" placeholder="Mag-type ng mensahe..." autocomplete="off" maxlength="300">
                    <button type="submit" class="cp-send-btn" id="cpSendBtn" aria-label="Send">
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
