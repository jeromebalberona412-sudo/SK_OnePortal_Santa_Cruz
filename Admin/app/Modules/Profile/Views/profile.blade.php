@extends('layouts.app')

@section('title', 'Profile')

@section('head')
@vite(['app/Modules/Profile/assets/css/profile.css'])
@endsection

@section('content')
<!-- Include Header -->
@include('layout::layouts.header')

<!-- Include Sidebar -->
@include('layout::layouts.sidebar')

<div id="mainContent" class="profile-page">
    <input type="hidden" id="defaultFullName" value="{{ $user->name ?? 'Admin' }}">
    <input type="hidden" id="defaultEmail" value="{{ $user->email ?? '' }}">

    <div class="profile-shell">
        <header class="profile-hero">
            <div class="profile-hero__avatar-wrap">
                <div class="profile-avatar-frame" aria-hidden="true">
                    <img src="{{ asset('Images/logo.png') }}" alt="Admin Avatar" id="avatarImage" width="96" height="96">
                    <span class="profile-avatar-fallback" id="avatarFallback">{{ strtoupper(substr(($user->name ?? 'Admin'), 0, 1)) }}</span>
                </div>
                <button type="button" class="profile-avatar-btn" id="avatarChangeButton" onclick="document.getElementById('avatarInput').click()">Change Avatar</button>
                <input type="file" id="avatarInput" accept="image/*" style="display: none;" onchange="handleAvatarChange(event)">
            </div>
            <div class="profile-hero__identity">
                <p class="profile-eyebrow">System User</p>
                <h1 class="profile-title">{{ $user->name ?? 'Admin' }}</h1>
                <p class="profile-subtitle">{{ $user->email ?? '' }}</p>
                <p class="profile-role-badge">Administrator</p>
            </div>
        </header>

        <section class="profile-panel">
            <div class="profile-tabs" role="tablist" aria-label="Profile Sections">
                <button type="button" class="tab-button active" data-tab="personal" onclick="switchTab('personal', event)">
                    Personal Information
                </button>
                <button type="button" class="tab-button" data-tab="security" onclick="switchTab('security', event)">
                    Security and Access
                </button>
                <button type="button" class="tab-button" data-tab="settings" onclick="switchTab('settings', event)">
                    Settings
                </button>
            </div>

            <div class="profile-panel__body">
                <div id="personal-tab" class="tab-pane active profile-pane">
                    <h3>Basic Information</h3>
                    <form id="personalForm" class="form-grid">
                        <div class="field-group">
                            <label for="fullName">Full Name *</label>
                            <input type="text" id="fullName" name="fullName" value="{{ $user->name ?? 'Admin' }}" required>
                        </div>
                        <div class="field-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" value="{{ $user->email ?? '' }}" required>
                        </div>
                        <div class="field-group field-group--full">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" placeholder="Enter your phone number">
                        </div>
                    </form>
                </div>

                <div id="security-tab" class="tab-pane profile-pane" hidden>
                    <h3>Account Security</h3>
                    <div class="form-grid">
                        <div class="field-group">
                            <label for="lastLogin">Last Login</label>
                            <input type="text" id="lastLogin" name="lastLogin" value="March 03, 2026 4:29 AM" readonly>
                        </div>
                        <div class="field-group">
                            <label for="lastLoginIP">Last Login IP</label>
                            <input type="text" id="lastLoginIP" name="lastLoginIP" value="127.0.0.1" readonly>
                        </div>
                        <div class="field-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" placeholder="Enter new password">
                        </div>
                        <div class="field-group">
                            <label for="confirmPassword">Confirm Password</label>
                            <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm new password">
                        </div>
                    </div>
                </div>

                <div id="settings-tab" class="tab-pane profile-pane" hidden>
                    <h3>Preferences</h3>
                    <div class="form-grid">
                        <div class="field-group">
                            <label for="language">Preferred Language</label>
                            <select id="language" name="language">
                                <option value="en" selected>English</option>
                            </select>
                        </div>
                        <div class="field-group field-group--toggle">
                            <label for="darkModeToggle">Theme Mode</label>
                            <label class="profile-switch" for="darkModeToggle">
                                <input type="checkbox" id="darkModeToggle" onchange="toggleDarkMode()">
                                <span class="profile-switch-slider"></span>
                                <span class="profile-switch-label">Enable dark mode</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="profile-actions">
            <p class="profile-actions__status" id="profileEditStatus">View mode: click Edit Profile to make changes.</p>
            <div class="profile-actions__buttons">
                <button type="button" class="btn btn-primary" id="saveButton" style="display: none;" onclick="saveProfile()">
                    <span class="button-text">Save Changes</span>
                </button>
                <button type="button" class="btn btn-secondary" id="cancelButton" style="display: none;" onclick="cancelEdit()">
                    Cancel
                </button>
                <button type="button" class="btn btn-primary" id="editButton" onclick="enableEdit()">
                    Edit Profile
                </button>
            </div>
        </section>
    </div>
</div>

<!-- Discard Changes Modal -->
<div id="discardChangesModal" class="confirm-modal-overlay" aria-hidden="true" style="display: none;">
    <div class="confirm-modal" role="dialog" aria-modal="true" aria-labelledby="discardTitle">
        <div class="confirm-modal-header">
            <h3 class="confirm-modal-title" id="discardTitle">Discard changes?</h3>
            <button type="button" class="confirm-modal-close" onclick="closeDiscardModal()" aria-label="Close">x</button>
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
<div id="twoFactorModal" class="confirm-modal-overlay" aria-hidden="true" style="display: none;">
    <div class="confirm-modal" role="dialog" aria-modal="true" aria-labelledby="twoFactorTitle">
        <div class="confirm-modal-header">
            <h3 class="confirm-modal-title" id="twoFactorTitle">Two-Factor Authentication</h3>
            <button type="button" class="confirm-modal-close" onclick="closeTwoFactorModal()" aria-label="Close">x</button>
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
                <div class="confirm-modal-actions">
                    <button type="button" class="confirm-btn confirm-btn-secondary" onclick="closeTwoFactorModal()">Cancel</button>
                    <button type="submit" class="confirm-btn confirm-btn-primary" id="twoFactorVerifyBtn">Verify and Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const profileDefaults = {
    name: document.getElementById('defaultFullName') ? document.getElementById('defaultFullName').value : 'Admin',
    email: document.getElementById('defaultEmail') ? document.getElementById('defaultEmail').value : '',
    phone: '',
    language: 'en',
    darkMode: localStorage.getItem('darkMode') === 'enabled'
};

const editableFieldIds = ['fullName', 'email', 'phone', 'password', 'confirmPassword', 'language', 'darkModeToggle'];

function setEditingState(isEditing) {
    const shell = document.querySelector('.profile-shell');
    if (shell) {
        shell.classList.toggle('is-editing', isEditing);
    }

    editableFieldIds.forEach(function(fieldId) {
        const field = document.getElementById(fieldId);
        if (field) {
            field.disabled = !isEditing;
        }
    });

    const status = document.getElementById('profileEditStatus');
    if (status) {
        status.textContent = isEditing
            ? 'Editing mode: update your details and save when ready.'
            : 'View mode: click Edit Profile to make changes.';
    }
}

function initializeAvatarFallback() {
    const avatarImage = document.getElementById('avatarImage');
    const avatarFallback = document.getElementById('avatarFallback');
    if (!avatarImage || !avatarFallback) return;

    function showFallback() {
        avatarImage.classList.add('avatar-hidden');
        avatarFallback.style.display = 'grid';
    }

    function hideFallback() {
        avatarImage.classList.remove('avatar-hidden');
        avatarFallback.style.display = 'none';
    }

    avatarImage.addEventListener('error', showFallback);
    avatarImage.addEventListener('load', hideFallback);

    if (avatarImage.complete && avatarImage.naturalWidth === 0) {
        showFallback();
    } else {
        hideFallback();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const darkMode = localStorage.getItem('darkMode');
    const darkModeToggle = document.getElementById('darkModeToggle');
    if (darkMode === 'enabled' && darkModeToggle) {
        document.body.classList.add('dark-mode');
        darkModeToggle.checked = true;
    }

    const discardOverlay = document.getElementById('discardChangesModal');
    if (discardOverlay) {
        discardOverlay.addEventListener('click', function(e) {
            if (e.target === discardOverlay) closeDiscardModal();
        });
    }

    const twoFactorOverlay = document.getElementById('twoFactorModal');
    if (twoFactorOverlay) {
        twoFactorOverlay.addEventListener('click', function(e) {
            if (e.target === twoFactorOverlay) closeTwoFactorModal();
        });
    }

    initializeAvatarFallback();
    setEditingState(false);
});

document.addEventListener('keydown', function(e) {
    if (e.key !== 'Escape') return;

    const discardOverlay = document.getElementById('discardChangesModal');
    if (discardOverlay && discardOverlay.style.display !== 'none') {
        closeDiscardModal();
        return;
    }

    const twoFactorOverlay = document.getElementById('twoFactorModal');
    if (twoFactorOverlay && twoFactorOverlay.style.display !== 'none') {
        closeTwoFactorModal();
    }
});

function switchTab(tabName, e) {
    document.querySelectorAll('.tab-button').forEach(function(btn) {
        btn.classList.remove('active');
    });

    document.querySelectorAll('.tab-pane').forEach(function(pane) {
        pane.classList.remove('active');
        pane.hidden = true;
    });

    const targetButton = (e && e.target)
        ? e.target
        : document.querySelector('.tab-button[data-tab="' + tabName + '"]');

    if (targetButton) {
        targetButton.classList.add('active');
    }

    const targetPane = document.getElementById(tabName + '-tab');
    if (targetPane) {
        targetPane.classList.add('active');
        targetPane.hidden = false;
    }
}

function enableEdit() {
    document.getElementById('editButton').style.display = 'none';
    document.getElementById('saveButton').style.display = 'inline-flex';
    document.getElementById('cancelButton').style.display = 'inline-flex';
    setEditingState(true);
}

function cancelEdit() {
    openDiscardModal();
}

function saveProfile() {
    openTwoFactorModal();
}

function openDiscardModal() {
    const modal = document.getElementById('discardChangesModal');
    modal.style.display = 'block';
    modal.setAttribute('aria-hidden', 'false');
}

function closeDiscardModal() {
    const modal = document.getElementById('discardChangesModal');
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
}

function confirmDiscardChanges() {
    document.getElementById('fullName').value = profileDefaults.name;
    document.getElementById('email').value = profileDefaults.email;
    document.getElementById('phone').value = profileDefaults.phone;
    document.getElementById('password').value = '';
    document.getElementById('confirmPassword').value = '';
    document.getElementById('language').value = profileDefaults.language;

    const darkModeToggle = document.getElementById('darkModeToggle');
    if (darkModeToggle) {
        darkModeToggle.checked = profileDefaults.darkMode;
    }

    if (profileDefaults.darkMode) {
        document.body.classList.add('dark-mode');
        localStorage.setItem('darkMode', 'enabled');
    } else {
        document.body.classList.remove('dark-mode');
        localStorage.setItem('darkMode', 'disabled');
    }

    document.getElementById('editButton').style.display = 'inline-block';
    document.getElementById('saveButton').style.display = 'none';
    document.getElementById('cancelButton').style.display = 'none';
    setEditingState(false);

    closeDiscardModal();
    showToast('Changes discarded');
}

function openTwoFactorModal() {
    const modal = document.getElementById('twoFactorModal');
    modal.style.display = 'block';
    modal.setAttribute('aria-hidden', 'false');

    const inputs = modal.querySelectorAll('.twofactor-code-input');
    inputs.forEach(function(input) {
        input.value = '';
    });

    if (inputs[0]) {
        inputs[0].focus();
    }

    inputs.forEach(function(input, idx) {
        input.oninput = function() {
            input.value = (input.value || '').replace(/[^0-9]/g, '').slice(0, 1);
            if (input.value && inputs[idx + 1]) {
                inputs[idx + 1].focus();
            }
        };

        input.onkeydown = function(ev) {
            if (ev.key === 'Backspace' && !input.value && inputs[idx - 1]) {
                inputs[idx - 1].focus();
            }
        };
    });
}

function closeTwoFactorModal() {
    const modal = document.getElementById('twoFactorModal');
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
}

function verifyTwoFactorAndSave(e) {
    e.preventDefault();

    const saveBtn = document.getElementById('saveButton');
    const saveLabel = saveBtn.querySelector('.button-text');
    const previousLabel = saveLabel ? saveLabel.textContent : 'Save Changes';

    saveBtn.disabled = true;
    if (saveLabel) {
        saveLabel.textContent = 'Saving...';
    }

    setTimeout(function() {
        saveBtn.disabled = false;
        if (saveLabel) {
            saveLabel.textContent = previousLabel;
        }

        closeTwoFactorModal();
        document.getElementById('editButton').style.display = 'inline-block';
        document.getElementById('saveButton').style.display = 'none';
        document.getElementById('cancelButton').style.display = 'none';
        setEditingState(false);

        showModal('Profile updated successfully!', 'success');
    }, 1200);

    return false;
}

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

function handleAvatarChange(event) {
    const file = event.target.files[0];
    if (!file) return;

    if (!file.type.startsWith('image/')) {
        showModal('Please select a valid image file.', 'error');
        return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
        const avatarImage = document.getElementById('avatarImage');
        const avatarFallback = document.getElementById('avatarFallback');

        if (avatarImage) {
            avatarImage.src = e.target.result;
            avatarImage.classList.remove('avatar-hidden');
        }

        if (avatarFallback) {
            avatarFallback.style.display = 'none';
        }

        showModal('Profile image updated successfully!', 'success');
    };
    reader.readAsDataURL(file);
}

function showModal(message) {
    window.alert(message);
}

function closeModal() {
    return;
}

function showToast(message) {
    console.log(message);
}
</script>
@endsection
