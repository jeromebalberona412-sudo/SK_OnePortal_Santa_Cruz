<div
    id="logoutModal"
    class="modal logout-modal"
    data-logout-url="{{ route('logout') }}"
    data-login-url="{{ route('login') }}"
    hidden
>
    <div class="modal-content logout-modal-content" role="dialog" aria-modal="true" aria-labelledby="logoutModalTitle">
        <button type="button" class="modal-close-icon" onclick="closeLogoutModal()" aria-label="Close logout dialog">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>

        <div class="modal-body logout-modal-body">
            <div class="logout-modal-inner">
                <div class="logout-icon-wrapper" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M9 6V4h7a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H9v-2"></path>
                        <polyline points="12,8 8,12 12,16"></polyline>
                        <line x1="8" y1="12" x2="20" y2="12"></line>
                    </svg>
                </div>

                <h2 id="logoutModalTitle" class="logout-modal-title">Logout Confirmation</h2>
                <p class="logout-modal-text">Are you sure you want to logout?</p>

                <div class="logout-modal-actions">
                    <button type="button" class="btn-cancel-modern" onclick="closeLogoutModal()">Cancel</button>
                    <button type="button" class="btn-logout-modern" onclick="confirmLogout()">
                        <span>Yes, Logout</span>
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="13,6 19,12 13,18"></polyline>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
