// Sidebar Toggle
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    
    sidebar.classList.toggle('collapsed');
    sidebar.classList.toggle('show');
    mainContent.classList.toggle('expanded');
}

// Tab Switching
function switchTab(tabName, triggerButton = null) {
    // Hide all tab contents
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(content => {
        content.classList.remove('active');
    });
    
    // Remove active class from all tab buttons
    const tabButtons = document.querySelectorAll('.tab-btn');
    tabButtons.forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected tab content
    const selectedTab = document.getElementById(tabName);
    if (selectedTab) {
        selectedTab.classList.add('active');
    }

    // Add active class to clicked/target button
    const button = triggerButton
        ?? document.querySelector(`.tab-btn[onclick="switchTab('${tabName}')"]`)
        ?? (typeof event !== 'undefined' ? event.target : null);

    if (button) {
        button.classList.add('active');
    }
}

// Avatar Upload Preview
function handleAvatarUpload(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            // Update all avatar images
            document.querySelectorAll('.profile-avatar-large, .user-avatar').forEach(img => {
                img.src = e.target.result;
            });
            
            showSuccessMessage('Profile picture updated successfully!');
        };
        reader.readAsDataURL(file);
    }
}

// Trigger file input
function triggerAvatarUpload() {
    document.getElementById('avatarUpload').click();
}

// Password Strength Checker
function checkPasswordStrength(password) {
    const strengthBar = document.querySelector('.password-strength-bar');
    const hint = document.querySelector('.password-hint');
    
    if (!password) {
        strengthBar.className = 'password-strength-bar';
        hint.textContent = '';
        return;
    }
    
    let strength = 0;
    
    // Check length
    if (password.length >= 8) strength++;
    if (password.length >= 12) strength++;
    
    // Check for numbers
    if (/\d/.test(password)) strength++;
    
    // Check for lowercase and uppercase
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
    
    // Check for special characters
    if (/[^A-Za-z0-9]/.test(password)) strength++;
    
    // Update UI
    strengthBar.className = 'password-strength-bar';
    
    if (strength <= 2) {
        strengthBar.classList.add('weak');
        hint.textContent = 'Weak password';
        hint.style.color = '#d0242b';
    } else if (strength <= 4) {
        strengthBar.classList.add('medium');
        hint.textContent = 'Medium password';
        hint.style.color = '#f7d31e';
    } else {
        strengthBar.classList.add('strong');
        hint.textContent = 'Strong password';
        hint.style.color = '#10b981';
    }
}

// Show Success Message
function showSuccessMessage(message) {
    const successDiv = document.querySelector('.success-message');
    if (successDiv) {
        successDiv.textContent = message;
        successDiv.classList.add('show');
        
        setTimeout(() => {
            successDiv.classList.remove('show');
        }, 3000);
    }
}

// Save Profile Info
function saveProfileInfo(event) {
    event.preventDefault();
    
    // Get form data
    const formData = new FormData(event.target);
    const data = Object.fromEntries(formData);
    
    console.log('Saving profile:', data);
    
    // Show success message
    showSuccessMessage('Profile information updated successfully!');
    
    // Update navbar user name
    const userName = document.querySelector('.user-name');
    if (userName && data.name) {
        userName.textContent = data.name;
    }
}

// Save Settings
function saveSettings(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const data = Object.fromEntries(formData);
    
    console.log('Saving settings:', data);
    showSuccessMessage('Settings updated successfully!');
}

// Change Password
function changePassword(event) {
    event.preventDefault();
    
    const currentPassword = document.getElementById('currentPassword').value;
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    
    // Validation
    if (!currentPassword || !newPassword || !confirmPassword) {
        alert('Please fill in all password fields');
        return;
    }
    
    if (newPassword !== confirmPassword) {
        alert('New passwords do not match');
        return;
    }
    
    if (newPassword.length < 8) {
        alert('Password must be at least 8 characters long');
        return;
    }
    
    console.log('Changing password...');
    showSuccessMessage('Password changed successfully!');
    
    // Clear form
    event.target.reset();
    document.querySelector('.password-strength-bar').className = 'password-strength-bar';
    document.querySelector('.password-hint').textContent = '';
}

// Logout Modal Functions
function showLogoutModal() {
    const modal = document.getElementById('logoutModal');
    if (modal) {
        modal.style.display = 'flex';
        setTimeout(() => modal.classList.add('show'), 10);
    }
}

function closeLogoutModal() {
    const modal = document.getElementById('logoutModal');
    if (modal) {
        modal.classList.remove('show');
        setTimeout(() => modal.style.display = 'none', 300);
    }
}

function confirmLogout() {
    // Show loading screen
    if (typeof LoadingScreen !== 'undefined') {
        LoadingScreen.show('Logging Out', 'Please wait...');
    }
    
    // Submit logout form
    const logoutForm = document.getElementById('logout-form');
    if (logoutForm) {
        logoutForm.submit();
    } else if (window.logoutRoute) {
        window.location.href = window.logoutRoute;
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Set first tab as active
    const firstTab = document.querySelector('.tab-btn');
    const firstContent = document.querySelector('.tab-content');
    
    if (firstTab) firstTab.classList.add('active');
    if (firstContent) firstContent.classList.add('active');
    
    // Add password strength checker
    const newPasswordInput = document.getElementById('newPassword');
    if (newPasswordInput) {
        newPasswordInput.addEventListener('input', function() {
            checkPasswordStrength(this.value);
        });
    }
});
