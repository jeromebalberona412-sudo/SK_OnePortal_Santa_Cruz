// ══════════════════════════════════════════════════════════════════════════
// AI ASSISTANT MODULE - JAVASCRIPT
// ══════════════════════════════════════════════════════════════════════════

document.addEventListener('DOMContentLoaded', function() {
    
    // ═══════════════════════════════════════════════════════════════════════
    // DOM ELEMENTS
    // ═══════════════════════════════════════════════════════════════════════
    
    const aiAssistantBtn = document.getElementById('aiAssistantBtn');
    const aiAssistantModal = document.getElementById('aiAssistantModal');
    const aiCloseBtn = document.getElementById('aiCloseBtn');
    const aiMaximizeBtn = document.getElementById('aiMaximizeBtn');
    const aiChatArea = document.getElementById('aiChatArea');
    const aiInputField = document.getElementById('aiInputField');
    const aiSendBtn = document.getElementById('aiSendBtn');
    const aiSuggestions = document.getElementById('aiSuggestions');
    const aiRecentPrompts = document.getElementById('aiRecentPrompts');
    const aiRecentList = document.getElementById('aiRecentList');
    
    // ═══════════════════════════════════════════════════════════════════════
    // STATE
    // ═══════════════════════════════════════════════════════════════════════
    
    let isModalOpen = false;
    let isFullscreen = false;
    let recentPrompts = JSON.parse(localStorage.getItem('aiRecentPrompts')) || [];
    
    // ═══════════════════════════════════════════════════════════════════════
    // MODAL TOGGLE
    // ═══════════════════════════════════════════════════════════════════════
    
    function toggleModal() {
        isModalOpen = !isModalOpen;
        
        if (isModalOpen) {
            aiAssistantModal.classList.add('active');
            aiAssistantBtn.setAttribute('aria-expanded', 'true');
            aiInputField.focus();
            loadRecentPrompts();
        } else {
            aiAssistantModal.classList.remove('active');
            aiAssistantBtn.setAttribute('aria-expanded', 'false');
            // Reset fullscreen when closing
            if (isFullscreen) {
                toggleFullscreen();
            }
        }
    }
    
    // ═══════════════════════════════════════════════════════════════════════
    // FULLSCREEN TOGGLE
    // ═══════════════════════════════════════════════════════════════════════
    
    function toggleFullscreen() {
        isFullscreen = !isFullscreen;
        
        if (isFullscreen) {
            aiAssistantModal.classList.add('fullscreen');
            aiMaximizeBtn.setAttribute('title', 'Restore');
        } else {
            aiAssistantModal.classList.remove('fullscreen');
            aiMaximizeBtn.setAttribute('title', 'Maximize');
        }
    }
    
    // ═══════════════════════════════════════════════════════════════════════
    // SEND MESSAGE
    // ═══════════════════════════════════════════════════════════════════════
    
    function sendMessage() {
        const message = aiInputField.value.trim();
        
        if (!message) return;
        
        // Hide welcome message and suggestions on first message
        const welcomeMessage = aiChatArea.querySelector('.ai-welcome-message');
        if (welcomeMessage) {
            welcomeMessage.style.display = 'none';
        }
        aiSuggestions.style.display = 'none';
        
        // Add user message
        addChatBubble(message, 'user');
        
        // Clear input
        aiInputField.value = '';
        aiInputField.style.height = 'auto';
        
        // Save to recent prompts
        saveRecentPrompt(message);
        
        // Show typing indicator
        showTypingIndicator();
        
        // Simulate AI response
        setTimeout(() => {
            hideTypingIndicator();
            const response = generateAIResponse(message);
            addChatBubble(response, 'ai');
        }, 1500);
    }
    
    // ═══════════════════════════════════════════════════════════════════════
    // CHAT BUBBLE
    // ═══════════════════════════════════════════════════════════════════════
    
    function addChatBubble(message, sender) {
        const bubble = document.createElement('div');
        bubble.className = `ai-chat-bubble ${sender}`;
        
        const avatar = document.createElement('div');
        avatar.className = 'ai-bubble-avatar';
        avatar.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                ${sender === 'ai' 
                    ? '<path d="M12 8V4H8"></path><rect width="16" height="12" x="4" y="8" rx="2"></rect><path d="M2 14h2"></path><path d="M20 14h2"></path><path d="M15 13v2"></path><path d="M9 13v2"></path>'
                    : '<circle cx="12" cy="7" r="4"></circle><path d="M5.5 21a6.5 6.5 0 0 1 13 0"></path>'
                }
            </svg>
        `;
        
        const content = document.createElement('div');
        content.className = 'ai-bubble-content';
        content.textContent = message;
        
        bubble.appendChild(avatar);
        bubble.appendChild(content);
        
        aiChatArea.appendChild(bubble);
        aiChatArea.scrollTop = aiChatArea.scrollHeight;
    }
    
    // ═══════════════════════════════════════════════════════════════════════
    // TYPING INDICATOR
    // ═══════════════════════════════════════════════════════════════════════
    
    function showTypingIndicator() {
        const typingBubble = document.createElement('div');
        typingBubble.className = 'ai-chat-bubble ai';
        typingBubble.id = 'aiTypingBubble';
        
        const avatar = document.createElement('div');
        avatar.className = 'ai-bubble-avatar';
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
        
        const typingIndicator = document.createElement('div');
        typingIndicator.className = 'ai-typing-indicator';
        typingIndicator.innerHTML = `
            <span class="ai-typing-dot"></span>
            <span class="ai-typing-dot"></span>
            <span class="ai-typing-dot"></span>
        `;
        
        typingBubble.appendChild(avatar);
        typingBubble.appendChild(typingIndicator);
        
        aiChatArea.appendChild(typingBubble);
        aiChatArea.scrollTop = aiChatArea.scrollHeight;
    }
    
    function hideTypingIndicator() {
        const typingBubble = document.getElementById('aiTypingBubble');
        if (typingBubble) {
            typingBubble.remove();
        }
    }
    
    // ═══════════════════════════════════════════════════════════════════════
    // AI RESPONSE GENERATOR (DUMMY)
    // ═══════════════════════════════════════════════════════════════════════
    
    function generateAIResponse(message) {
        const lowerMessage = message.toLowerCase();
        
        // Simple keyword-based responses
        if (lowerMessage.includes('resolution')) {
            return "I can help you create an SK Resolution. A typical resolution includes: Title, Whereas clauses (background/justification), and Resolved clauses (actions to be taken). Would you like me to provide a template?";
        } else if (lowerMessage.includes('proposal') || lowerMessage.includes('project')) {
            return "For a project proposal, you'll need: Executive Summary, Project Background, Objectives, Target Beneficiaries, Implementation Plan, Budget, and Expected Outcomes. I can help you draft each section.";
        } else if (lowerMessage.includes('event') || lowerMessage.includes('program')) {
            return "Planning an event? Let's organize it step by step: Event Title, Date & Venue, Target Participants, Program Flow, Budget Allocation, and Logistics. What type of event are you planning?";
        } else if (lowerMessage.includes('budget')) {
            return "Budget planning is crucial for SK activities. I can help you create a budget breakdown with categories like: Venue, Food & Refreshments, Materials & Supplies, Transportation, Honorarium, and Contingency. What's your estimated total budget?";
        } else if (lowerMessage.includes('sports')) {
            return "A sports festival program typically includes: Opening Ceremony, Sports Events Schedule, Awarding Ceremony, and Closing Program. Would you like suggestions for specific sports activities suitable for youth?";
        } else if (lowerMessage.includes('scholarship')) {
            return "For a scholarship program, consider these components: Eligibility Criteria, Application Requirements, Selection Process, Scholarship Benefits, and Monitoring & Evaluation. What type of scholarship are you planning?";
        } else {
            return "I'm here to assist with SK-related tasks like creating resolutions, proposals, event planning, budget preparation, and more. How can I help you today?";
        }
    }
    
    // ═══════════════════════════════════════════════════════════════════════
    // RECENT PROMPTS
    // ═══════════════════════════════════════════════════════════════════════
    
    function saveRecentPrompt(prompt) {
        // Add to beginning of array
        recentPrompts.unshift(prompt);
        
        // Keep only last 5 prompts
        if (recentPrompts.length > 5) {
            recentPrompts = recentPrompts.slice(0, 5);
        }
        
        // Save to localStorage
        localStorage.setItem('aiRecentPrompts', JSON.stringify(recentPrompts));
        
        // Update UI
        loadRecentPrompts();
    }
    
    function loadRecentPrompts() {
        if (recentPrompts.length === 0) {
            aiRecentPrompts.style.display = 'none';
            return;
        }
        
        aiRecentPrompts.style.display = 'block';
        aiRecentList.innerHTML = '';
        
        recentPrompts.forEach(prompt => {
            const item = document.createElement('div');
            item.className = 'ai-recent-item';
            item.textContent = prompt;
            item.addEventListener('click', () => {
                aiInputField.value = prompt;
                aiInputField.focus();
            });
            aiRecentList.appendChild(item);
        });
    }
    
    // ═══════════════════════════════════════════════════════════════════════
    // AUTO-RESIZE TEXTAREA
    // ═══════════════════════════════════════════════════════════════════════
    
    function autoResizeTextarea() {
        aiInputField.style.height = 'auto';
        aiInputField.style.height = aiInputField.scrollHeight + 'px';
    }
    
    // ═══════════════════════════════════════════════════════════════════════
    // EVENT LISTENERS
    // ═══════════════════════════════════════════════════════════════════════
    
    // Toggle modal
    aiAssistantBtn.addEventListener('click', toggleModal);
    aiCloseBtn.addEventListener('click', toggleModal);
    
    // Toggle fullscreen
    aiMaximizeBtn.addEventListener('click', toggleFullscreen);
    
    // Send message
    aiSendBtn.addEventListener('click', sendMessage);
    
    // Enter key to send
    aiInputField.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });
    
    // Auto-resize textarea
    aiInputField.addEventListener('input', autoResizeTextarea);
    
    // Suggestion cards
    const suggestionCards = document.querySelectorAll('.ai-suggestion-card');
    suggestionCards.forEach(card => {
        card.addEventListener('click', function() {
            const prompt = this.getAttribute('data-prompt');
            aiInputField.value = prompt;
            sendMessage();
        });
    });
    
    // Close modal when clicking outside
    document.addEventListener('click', function(e) {
        if (isModalOpen && 
            !aiAssistantModal.contains(e.target) && 
            !aiAssistantBtn.contains(e.target)) {
            toggleModal();
        }
    });
    
    // Escape key to close
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && isModalOpen) {
            toggleModal();
        }
    });
    
    // ═══════════════════════════════════════════════════════════════════════
    // INITIALIZATION
    // ═══════════════════════════════════════════════════════════════════════
    
    // Load recent prompts on init
    loadRecentPrompts();
    
});
