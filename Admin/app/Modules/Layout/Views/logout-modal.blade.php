<div id="logoutModal" class="logout-modal" data-logout-url="{{ route('logout') }}" data-login-url="{{ route('login') }}">
    <div class="logout-overlay"></div>
    <div class="logout-box" role="dialog" aria-modal="true" aria-labelledby="logoutModalTitle">
        <h2 id="logoutModalTitle" class="logout-box__title">Confirm Logout</h2>
        <p class="logout-box__text">Are you sure you want to logout?</p>
        <div class="logout-box__actions">
            <button type="button" class="logout-btn logout-btn--no btn-cancel-modern" onclick="closeLogoutModal()">No</button>
            <button type="button" class="logout-btn logout-btn--yes btn-logout-modern" onclick="confirmLogout()">Yes</button>
        </div>
    </div>
</div>
