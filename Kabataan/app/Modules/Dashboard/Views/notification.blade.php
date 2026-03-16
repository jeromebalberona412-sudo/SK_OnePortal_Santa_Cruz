{{-- Notification Popover — included in Dashboard and Profile navbars --}}
<div class="notif-nav-wrapper">
    <button class="nav-icon-btn notif-nav-btn" id="notifNavBtn" title="Notifications" onclick="toggleNotifPopover()">
        <svg viewBox="0 0 20 20" fill="currentColor">
            <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/>
        </svg>
    </button>

    <div class="notif-popover" id="notifPopover" role="dialog" aria-label="Notifications">
        <div class="np-inner">
            <div class="np-header">
                <span class="np-title">Notifications</span>
                <button class="np-close-btn" onclick="closeNotifPopover()" aria-label="Close notifications">
                    <svg viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
            <div class="np-body">
                <div class="np-empty">
                    <svg width="72" height="72" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6z" stroke="#cbd5e1" stroke-width="1.5" fill="none"/>
                        <path d="M10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z" stroke="#cbd5e1" stroke-width="1.5" fill="none"/>
                    </svg>
                    <p class="np-empty-title">No Notifications</p>
                    <p class="np-empty-sub">You're all caught up! Check back later for updates from SK.</p>
                </div>
            </div>
        </div>
    </div>
</div>
