@extends('layouts.app')

@section('title', 'Profile')

@section('head')
@endsection

@section('content')
<!-- Include Header -->
@include('layout::layouts.header')

<!-- Include Sidebar -->
@include('layout::layouts.sidebar')

<style>
/* Enterprise Admin Profile Styles */
:root {
    --theme-deep-blue: #1e5fae;
    --theme-deep-blue-hover: #1a5499;
    --theme-blue-teal: #1f7a8c;
    --theme-green: #2e8b57;
}

.admin-profile-container {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.profile-header {
    background: linear-gradient(135deg, var(--theme-deep-blue) 0%, var(--theme-blue-teal) 100%);
    color: white;
    padding: 2.5rem;
    border-radius: 16px 16px 0 0;
    position: relative;
    overflow: hidden;
}

.profile-header::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 200px;
    height: 200px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    transform: translate(50px, -50px);
}

.profile-header::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 260px;
    height: 260px;
    background: rgba(255, 255, 255, 0.08);
    border-radius: 50%;
    transform: translate(-90px, 90px);
}

.profile-header-content {
    display: flex;
    align-items: center;
    gap: 2rem;
    position: relative;
    z-index: 1;
}

.profile-avatar {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 4px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
    position: relative;
}

.profile-avatar img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
}

.avatar-upload {
    position: absolute;
    bottom: 0;
    right: 0;
    background: var(--theme-deep-blue);
    color: white;
    border: 3px solid white;
    border-radius: 50%;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
}

.avatar-upload:hover {
    background: var(--theme-deep-blue-hover);
    transform: scale(1.1);
}

.profile-info h1 {
    font-size: 2rem;
    font-weight: 700;
    margin: 0 0 0.5rem 0;
}

.profile-info .username {
    font-size: 1.125rem;
    opacity: 0.9;
    margin: 0 0 0.25rem 0;
}

.profile-info .role {
    display: inline-block;
    background: rgba(255, 255, 255, 0.2);
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
}

.profile-content {
    background: white;
    border-radius: 0 0 16px 16px;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    border: 2px solid #e5e7eb;
    border-top: none;
}

.profile-tabs {
    display: flex;
    border-bottom: 1px solid #e5e7eb;
    background: #f9fafb;
}

.tab-button {
    padding: 1rem 1.5rem;
    background: none;
    border: none;
    color: #6b7280;
    font-weight: 500;
    cursor: pointer;
    position: relative;
    transition: all 0.2s ease;
}

.tab-button:hover {
    color: #374151;
    background: rgba(59, 130, 246, 0.05);
}

.tab-button.active {
    color: #3b82f6;
}

.tab-button.active::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: #3b82f6;
}

.tab-content {
    padding: 2rem;
}

.tab-pane {
    display: none;
}

.tab-pane.active {
    display: block;
}

/* Section Cards */
.section-card {
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    transition: all 0.2s ease;
}

.section-card:hover {
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    border-color: #d1d5db;
}

.section-card h3 {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0 0 1.5rem 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.section-card h3::before {
    content: '';
    width: 4px;
    height: 20px;
    background: #3b82f6;
    border-radius: 2px;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.help-icon {
    width: 16px;
    height: 16px;
    background: #9ca3af;
    color: white;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    cursor: help;
    position: relative;
}

.help-icon:hover::after {
    content: attr(data-tooltip);
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    background: #1f2937;
    color: white;
    padding: 0.5rem;
    border-radius: 6px;
    font-size: 0.75rem;
    white-space: nowrap;
    z-index: 10;
    margin-bottom: 0.25rem;
}

.form-group input,
.form-group select {
    padding: 0.75rem 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 1rem;
    color: #374151;
    background: white;
    transition: all 0.2s ease;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.form-group input:hover,
.form-group select:hover {
    border-color: #d1d5db;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1), 0 2px 4px rgba(0, 0, 0, 0.1);
}

.form-group.read-only input {
    background: #f9fafb;
    color: #6b7280;
    cursor: not-allowed;
}

.form-group.read-only input:focus {
    border-color: #e5e7eb;
    box-shadow: none;
}

.toggle-switch {
    position: relative;
    width: 60px;
    height: 32px;
    margin-top: 0.5rem;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #d1d5db;
    transition: 0.3s;
    border-radius: 34px;
}

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 24px;
    width: 24px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: 0.3s;
    border-radius: 50%;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.toggle-switch input:checked + .toggle-slider {
    background-color: #10b981;
}

.toggle-switch input:checked + .toggle-slider:before {
    transform: translateX(28px);
}

/* Activity & Logs tab removed */

/* Discard / Two-factor modals (UI only) */
.confirm-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.55);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 10050;
    padding: 16px;
    backdrop-filter: blur(4px);
}

.confirm-modal {
    width: 100%;
    max-width: 520px;
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 20px 45px rgba(0, 0, 0, 0.25);
    overflow: hidden;
}

.confirm-modal-header {
    background: linear-gradient(135deg, var(--theme-deep-blue) 0%, var(--theme-blue-teal) 100%);
    color: #ffffff;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.confirm-modal-title {
    margin: 0;
    font-size: 16px;
    font-weight: 700;
}

.confirm-modal-close {
    background: rgba(255, 255, 255, 0.18);
    border: 1px solid rgba(255, 255, 255, 0.25);
    color: #ffffff;
    border-radius: 10px;
    width: 38px;
    height: 38px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.confirm-modal-body {
    padding: 18px 20px;
    color: #1f2937;
}

.confirm-modal-body p {
    margin: 0;
    color: #4b5563;
    line-height: 1.5;
}

.confirm-modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 20px 20px;
    background: #f9fafb;
    border-top: 1px solid #e5e7eb;
}

.confirm-btn {
    border: none;
    border-radius: 10px;
    padding: 10px 14px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
}

.confirm-btn-secondary {
    background: #ffffff;
    border: 1px solid #d1d5db;
    color: #1f2937;
}

.confirm-btn-primary {
    background: var(--theme-deep-blue);
    color: #ffffff;
}

.confirm-btn-primary:hover {
    background: var(--theme-deep-blue-hover);
}

.twofactor-modal-body {
    padding: 18px 20px;
}

.twofactor-hint {
    margin: 0 0 14px;
    color: #4b5563;
    font-size: 14px;
}

.twofactor-code-row {
    display: flex;
    gap: 10px;
    justify-content: center;
    margin: 12px 0 18px;
}

.twofactor-code-input {
    width: 44px;
    height: 54px;
    border-radius: 12px;
    border: 1px solid #d1d5db;
    background: #ffffff;
    font-size: 20px;
    font-weight: 800;
    text-align: center;
    color: #1f2937;
}

.twofactor-code-input:focus {
    outline: none;
    border-color: var(--theme-deep-blue);
    box-shadow: 0 0 0 4px rgba(30, 95, 174, 0.12);
}

@media (max-width: 480px) {
    .twofactor-code-row { gap: 8px; }
    .twofactor-code-input {
        width: 40px;
        height: 52px;
        border-radius: 10px;
        font-size: 18px;
    }
}

/* Status Badges */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
}

.status-badge.active {
    background: #dcfce7;
    color: #166534;
}

.status-badge.inactive {
    background: #fee2e2;
    color: #991b1b;
}

.status-badge.suspended {
    background: #fef3c7;
    color: #92400e;
}

.status-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: currentColor;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #e5e7eb;
}

.btn {
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-primary {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.btn-secondary {
    background: white;
    color: #374151;
    border: 1px solid #d1d5db;
}

.btn-secondary:hover {
    background: #f9fafb;
}

.btn-danger {
    background: #ef4444;
    color: white;
}

.btn-danger:hover {
    background: #dc2626;
}

.btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
}

/* Loading Spinner */
.spinner {
    width: 16px;
    height: 16px;
    border: 2px solid #ffffff;
    border-top: 2px solid transparent;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    display: none;
}

.btn.loading .spinner {
    display: inline-block;
}

.btn.loading .button-text {
    display: none;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Responsive */
@media (max-width: 768px) {
    .admin-profile-container {
        margin: 1rem auto;
    }
    
    .profile-header {
        padding: 1.5rem;
    }
    
    .profile-header-content {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }
    
    .profile-tabs {
        overflow-x: auto;
    }
    
    .tab-content {
        padding: 1rem;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .action-buttons {
        flex-direction: column;
    }
}
</style>

<div class="main-content-modern" id="mainContent">
    <div class="admin-profile-container">
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-header-content">
                <div class="profile-avatar">
                    <img src="{{ asset('Images/logo.png') }}" alt="Admin Avatar" id="avatarImage">
                    <div class="avatar-upload" onclick="document.getElementById('avatarInput').click()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 4v16m8-8H4"/>
                        </svg>
                    </div>
                    <input type="file" id="avatarInput" accept="image/*" style="display: none;" onchange="handleAvatarChange(event)">
                </div>
                <div class="profile-info">
                    <h1 style="color: white;">Admin Three</h1>
                    <div class="username" style="color: rgba(255, 255, 255, 0.9);">@admin_three</div>
                    <div class="role" style="background: rgba(255, 255, 255, 0.2); color: white;">Admin</div>
                </div>
            </div>
        </div>

        <!-- Profile Content -->
        <div class="profile-content">
            <!-- Tabs -->
            <div class="profile-tabs">
                <button class="tab-button active" onclick="switchTab('personal')">
                    Personal Information
                </button>
                <button class="tab-button" onclick="switchTab('security')">
                    Security & Access
                </button>
                <button class="tab-button" onclick="switchTab('settings')">
                    Settings
                </button>
            </div>

            <!-- Tab Content -->
            <div class="tab-content">
                <!-- Personal Information Tab -->
                <div id="personal-tab" class="tab-pane active">
                    <div class="section-card">
                        <h3>Basic Information</h3>
                        <form id="personalForm">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="fullName">Full Name *</label>
                                    <input type="text" id="fullName" name="fullName" value="Admin Three" required>
                                </div>
                                <div class="form-group">
                                    <label for="email">Email Address *</label>
                                    <input type="email" id="email" name="email" value="admin3@oneportal.local" required>
                                </div>
                                <div class="form-group">
                                    <label for="phone">Phone Number</label>
                                    <input type="tel" id="phone" name="phone" placeholder="Enter Your Phone Number">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Security & Access Tab -->
                <div id="security-tab" class="tab-pane">
                    <div class="section-card">
                        <h3>Account Security</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="lastLogin">Last Login</label>
                                <input type="text" id="lastLogin" name="lastLogin" value="March 03, 2026 4:29 AM" readonly class="read-only">
                            </div>
                            <div class="form-group">
                                <label for="lastLoginIP">Last Login IP</label>
                                <input type="text" id="lastLoginIP" name="lastLoginIP" value="127.0.0.1" readonly class="read-only">
                            </div>
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" id="password" name="password" placeholder="Enter new password">
                            </div>
                            <div class="form-group">
                                <label for="confirmPassword">Confirm Password</label>
                                <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm new password">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Settings Tab -->
                <div id="settings-tab" class="tab-pane">
                    <div class="section-card">
                        <h3>Preferences</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="language">Preferred Language</label>
                                <select id="language" name="language">
                                    <option value="en" selected>English</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Theme Mode</label>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="darkModeToggle" onchange="toggleDarkMode()">
                                    <span class="toggle-slider"></span>
                                </label>
                                <small style="color: #6b7280; margin-top: 0.25rem; display: block;">Toggle between light and dark mode</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <button type="button" class="btn btn-primary" id="saveButton" style="display: none;" onclick="saveProfile()">
                    <span class="button-text">Save Changes</span>
                    <div class="spinner"></div>
                </button>
                <button type="button" class="btn btn-secondary" id="cancelButton" style="display: none;" onclick="cancelEdit()">
                    Cancel
                </button>
                <button type="button" class="btn btn-primary" id="editButton" onclick="enableEdit()">
                    Edit Profile
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Discard Changes Modal -->
<div id="discardChangesModal" class="confirm-modal-overlay" aria-hidden="true">
    <div class="confirm-modal" role="dialog" aria-modal="true" aria-labelledby="discardTitle">
        <div class="confirm-modal-header">
            <h3 class="confirm-modal-title" id="discardTitle">Discard changes?</h3>
            <button type="button" class="confirm-modal-close" onclick="closeDiscardModal()" aria-label="Close">
                ✕
            </button>
        </div>
        <div class="confirm-modal-body">
            <p>Are you sure you want to discard your changes? Any edits you made will be lost.</p>
        </div>
        <div class="confirm-modal-actions">
            <button type="button" class="confirm-btn confirm-btn-secondary" onclick="closeDiscardModal()">Keep editing</button>
            <button type="button" class="confirm-btn confirm-btn-primary" onclick="confirmDiscardChanges()">Discard</button>
        </div>
    </div>
</div>

<!-- Two-Factor Modal (UI only, before saving) -->
<div id="twoFactorModal" class="confirm-modal-overlay" aria-hidden="true">
    <div class="confirm-modal" role="dialog" aria-modal="true" aria-labelledby="twoFactorTitle">
        <div class="confirm-modal-header">
            <h3 class="confirm-modal-title" id="twoFactorTitle">Two-Factor Authentication</h3>
            <button type="button" class="confirm-modal-close" onclick="closeTwoFactorModal()" aria-label="Close">
                ✕
            </button>
        </div>
        <div class="twofactor-modal-body">
            <p class="twofactor-hint">Enter your 6-digit authentication code to continue.</p>
            <form id="twoFactorInlineForm" onsubmit="return verifyTwoFactorAndSave(event)">
                <div class="twofactor-code-row" aria-label="6 digit code">
                    <input class="twofactor-code-input" inputmode="numeric" pattern="[0-9]" maxlength="1" required>
                    <input class="twofactor-code-input" inputmode="numeric" pattern="[0-9]" maxlength="1" required>
                    <input class="twofactor-code-input" inputmode="numeric" pattern="[0-9]" maxlength="1" required>
                    <input class="twofactor-code-input" inputmode="numeric" pattern="[0-9]" maxlength="1" required>
                    <input class="twofactor-code-input" inputmode="numeric" pattern="[0-9]" maxlength="1" required>
                    <input class="twofactor-code-input" inputmode="numeric" pattern="[0-9]" maxlength="1" required>
                </div>
                <div class="confirm-modal-actions" style="padding: 0; background: transparent; border-top: none;">
                    <button type="button" class="confirm-btn confirm-btn-secondary" onclick="closeTwoFactorModal()">Cancel</button>
                    <button type="submit" class="confirm-btn confirm-btn-primary" id="twoFactorVerifyBtn">Verify & Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Check for saved dark mode preference
document.addEventListener('DOMContentLoaded', function() {
    const darkMode = localStorage.getItem('darkMode');
    if (darkMode === 'enabled') {
        document.body.classList.add('dark-mode');
        document.getElementById('darkModeToggle').checked = true;
    }
    
    // All fields are editable by default - no read-only setup needed

    // Close modals on overlay click
    const discardOverlay = document.getElementById('discardChangesModal');
    if (discardOverlay) {
        discardOverlay.addEventListener('click', (e) => {
            if (e.target === discardOverlay) closeDiscardModal();
        });
    }

    const twoFactorOverlay = document.getElementById('twoFactorModal');
    if (twoFactorOverlay) {
        twoFactorOverlay.addEventListener('click', (e) => {
            if (e.target === twoFactorOverlay) closeTwoFactorModal();
        });
    }
});

// Escape key closes any open modal
document.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape') return;
    const discardOverlay = document.getElementById('discardChangesModal');
    if (discardOverlay && discardOverlay.style.display === 'flex') {
        closeDiscardModal();
        return;
    }
    const twoFactorOverlay = document.getElementById('twoFactorModal');
    if (twoFactorOverlay && twoFactorOverlay.style.display === 'flex') {
        closeTwoFactorModal();
    }
});

// Tab switching
function switchTab(tabName, e) {
    // Remove active class from all tabs and panes
    document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));
    
    // Add active class to selected tab and pane
    const evt = e || window.event;
    if (evt && evt.target) {
        evt.target.classList.add('active');
    }
    document.getElementById(tabName + '-tab').classList.add('active');
}

// Enable edit mode (now just shows save/cancel buttons)
function enableEdit() {
    document.getElementById('editButton').style.display = 'none';
    document.getElementById('saveButton').style.display = 'inline-flex';
    document.getElementById('cancelButton').style.display = 'inline-flex';
}

// Cancel edit
function cancelEdit() {
    openDiscardModal();
}

// Save profile
function saveProfile() {
    openTwoFactorModal();
}

function openDiscardModal() {
    const modal = document.getElementById('discardChangesModal');
    modal.style.display = 'flex';
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
}

function closeDiscardModal() {
    const modal = document.getElementById('discardChangesModal');
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
}

function confirmDiscardChanges() {
    // Reset form to original values (UI-only placeholders)
    document.getElementById('fullName').value = 'Admin Three';
    document.getElementById('email').value = 'admin3@oneportal.local';
    document.getElementById('phone').value = '';
    document.getElementById('password').value = '';
    document.getElementById('confirmPassword').value = '';
    document.getElementById('language').value = 'en';

    document.getElementById('editButton').style.display = 'inline-flex';
    document.getElementById('saveButton').style.display = 'none';
    document.getElementById('cancelButton').style.display = 'none';
    closeDiscardModal();
    showToast('Changes discarded');
}

function openTwoFactorModal() {
    const modal = document.getElementById('twoFactorModal');
    modal.style.display = 'flex';
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';

    // Clear previous code
    const inputs = modal.querySelectorAll('.twofactor-code-input');
    inputs.forEach(i => (i.value = ''));
    if (inputs[0]) inputs[0].focus();

    // Auto-advance UX
    inputs.forEach((input, idx) => {
        input.oninput = () => {
            input.value = (input.value || '').replace(/[^0-9]/g, '').slice(0, 1);
            if (input.value && inputs[idx + 1]) inputs[idx + 1].focus();
        };
        input.onkeydown = (ev) => {
            if (ev.key === 'Backspace' && !input.value && inputs[idx - 1]) inputs[idx - 1].focus();
        };
    });
}

function closeTwoFactorModal() {
    const modal = document.getElementById('twoFactorModal');
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
}

function verifyTwoFactorAndSave(e) {
    e.preventDefault();

    const saveBtn = document.getElementById('saveButton');
    saveBtn.classList.add('loading');
    saveBtn.disabled = true;

    // UI-only: simulate verification + save
    setTimeout(() => {
        saveBtn.classList.remove('loading');
        saveBtn.disabled = false;
        closeTwoFactorModal();

        document.getElementById('editButton').style.display = 'inline-flex';
        document.getElementById('saveButton').style.display = 'none';
        document.getElementById('cancelButton').style.display = 'none';
        showModal('Profile updated successfully!', 'success');
    }, 1200);

    return false;
}

// Toggle dark mode
function toggleDarkMode() {
    const isChecked = document.getElementById('darkModeToggle').checked;
    if (isChecked) {
        document.body.classList.add('dark-mode');
        localStorage.setItem('darkMode', 'enabled');
        showToast('Dark mode enabled');
    } else {
        document.body.classList.remove('dark-mode');
        localStorage.setItem('darkMode', 'disabled');
        showToast('Light mode enabled');
    }
}

// Handle avatar change
function handleAvatarChange(event) {
    const file = event.target.files[0];
    if (file) {
        // Check if file is an image
        if (file.type.startsWith('image/')) {
            // Read the file and update preview (UI only)
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('avatarImage').src = e.target.result;
                showModal('Profile image updated successfully!', 'success');
            };
            reader.readAsDataURL(file);
        } else {
            showModal('Please select a valid image file.', 'error');
        }
    }
}

// Show modal with proper styling and positioning
function showModal(message, type = 'success') {
    // Remove any existing modal
    const existingModal = document.getElementById('customModal');
    if (existingModal) {
        existingModal.remove();
    }

    // Create modal overlay
    const modalOverlay = document.createElement('div');
    modalOverlay.id = 'customModal';
    modalOverlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10000;
        animation: fadeIn 0.3s ease;
    `;

    // Create modal content
    const modalContent = document.createElement('div');
    const bgColor = type === 'success' ? '#10b981' : '#ef4444';
    const icon = type === 'success' ? '✓' : '✕';
    
    modalContent.style.cssText = `
        background: white;
        border-radius: 12px;
        padding: 2rem;
        max-width: 400px;
        width: 90%;
        text-align: center;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        animation: slideUp 0.3s ease;
    `;

    modalContent.innerHTML = `
        <div style="
            width: 48px;
            height: 48px;
            background: ${bgColor};
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 24px;
            font-weight: bold;
        ">${icon}</div>
        <h3 style="
            margin: 0 0 0.5rem 0;
            color: #1f2937;
            font-size: 1.25rem;
            font-weight: 600;
        ">${type === 'success' ? 'Success!' : 'Error!'}</h3>
        <p style="
            margin: 0 0 1.5rem 0;
            color: #6b7280;
            line-height: 1.5;
        ">${message}</p>
        <button onclick="closeModal()" style="
            background: ${bgColor};
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        ">OK</button>
    `;

    modalOverlay.appendChild(modalContent);
    document.body.appendChild(modalOverlay);

    // Close modal when clicking overlay
    modalOverlay.addEventListener('click', function(e) {
        if (e.target === modalOverlay) {
            closeModal();
        }
    });
}

// Close modal
function closeModal() {
    const modal = document.getElementById('customModal');
    if (modal) {
        modal.style.animation = 'fadeOut 0.3s ease';
        setTimeout(() => modal.remove(), 300);
    }
}

// Show toast notification (for minor messages)
function showToast(message) {
    // Create toast element
    const toast = document.createElement('div');
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #1f2937;
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 9999;
        animation: slideIn 0.3s ease;
    `;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    // Remove after 3 seconds
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Add animations and dark mode styles
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    @keyframes fadeOut {
        from { opacity: 1; }
        to { opacity: 0; }
    }
    @keyframes slideUp {
        from { transform: translateY(20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    
    /* Dark Mode Styles */
    body.dark-mode {
        background-color: #1f2937;
        color: #f9fafb;
    }
    
    body.dark-mode .profile-content {
        background: #374151;
        color: #f9fafb;
    }
    
    body.dark-mode .section-card {
        background: #4b5563;
        border-color: #6b7280;
    }
    
    body.dark-mode .section-card h3 {
        color: #f9fafb;
    }
    
    body.dark-mode .form-group label {
        color: #d1d5db;
    }
    
    body.dark-mode .form-group input,
    body.dark-mode .form-group select {
        background: #374151;
        border-color: #6b7280;
        color: #f9fafb;
    }
    
    body.dark-mode .form-group input:focus,
    body.dark-mode .form-group select:focus {
        border-color: #60a5fa;
        box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.1);
    }
    
    body.dark-mode .profile-tabs {
        background: #374151;
        border-color: #4b5563;
    }
    
    body.dark-mode .tab-button {
        color: #d1d5db;
    }
    
    body.dark-mode .tab-button:hover {
        color: #f9fafb;
        background: rgba(96, 165, 250, 0.1);
    }
    
    body.dark-mode .tab-button.active {
        color: #60a5fa;
    }
    
    /* removed: activity & logs dark mode styles */
    
    body.dark-mode .toggle-slider {
        background-color: #6b7280;
    }
    
    body.dark-mode .toggle-slider:before {
        background-color: #f9fafb;
    }
    
    body.dark-mode .help-icon {
        background: #6b7280;
    }
`;
document.head.appendChild(style);
</script>
@endsection
