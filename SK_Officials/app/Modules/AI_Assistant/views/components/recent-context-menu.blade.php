{{-- Floating context menu for recent chat rows (ChatGPT-style) --}}
<div class="ai-chat-context-menu" id="aiChatContextMenu" role="menu" hidden>
    <button type="button" class="ai-ctx-item" data-action="rename" role="menuitem">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
        Rename
    </button>
    <div class="ai-ctx-divider" role="separator"></div>
    <button type="button" class="ai-ctx-item" data-action="pin" role="menuitem">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 17v5"></path><path d="M9 10.76a2 2 0 0 1-1.11 1.79l-1.65.83a1 1 0 0 0-.55.9v1.84a1 1 0 0 0 1 1h8.72a1 1 0 0 0 1-1v-1.84a1 1 0 0 0-.55-.9l-1.65-.83A2 2 0 0 1 12 4.24z"></path></svg>
        <span data-pin-label>Pin chat</span>
    </button>
    <button type="button" class="ai-ctx-item ai-ctx-item--danger" data-action="delete" role="menuitem">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
        Delete
    </button>
</div>
