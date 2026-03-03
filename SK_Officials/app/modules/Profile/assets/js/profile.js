// Profile JavaScript Functionality
document.addEventListener('DOMContentLoaded', function() {
    initializeProfile();
});

function initializeProfile() {
    // Initialize profile-specific functionality only
    // Header functionality is handled by header.js
    // Sidebar functionality is handled by sidebar.js
    initializeProfileFeatures();
}

function initializeProfileFeatures() {
    // Change avatar functionality
    const changeAvatarBtn = document.querySelector('.change-avatar-btn');
    if (changeAvatarBtn) {
        changeAvatarBtn.addEventListener('click', handleAvatarChange);
    }

    // Edit buttons functionality
    const editButtons = document.querySelectorAll('.edit-btn');
    editButtons.forEach(button => {
        button.addEventListener('click', handleEditSection);
    });

    // Initialize tooltips and other interactive elements
    initializeTooltips();
}

function handleAvatarChange() {
    // Create file input
    const fileInput = document.createElement('input');
    fileInput.type = 'file';
    fileInput.accept = 'image/*';
    
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Handle avatar upload
            uploadAvatar(file);
        }
    });
    
    fileInput.click();
}

function uploadAvatar(file) {
    // Create FormData for file upload
    const formData = new FormData();
    formData.append('avatar', file);
    
    // Show loading state
    showNotification('Uploading avatar...', 'info');
    
    // Simulate upload (replace with actual API call)
    setTimeout(() => {
        // Update avatar display
        const avatarImg = document.querySelector('.admin-avatar img, .profile-avatar img');
        if (avatarImg) {
            const reader = new FileReader();
            reader.onload = function(e) {
                avatarImg.src = e.target.result;
                showNotification('Avatar updated successfully!', 'success');
            };
            reader.readAsDataURL(file);
        }
    }, 1500);
}

function handleEditSection(event) {
    const button = event.target.closest('.edit-btn');
    const section = button.closest('.barangay-info-section, .sk-chairman-section, .sk-kagawad-section, .sk-secretary-section, .sk-treasurer-section');
    
    if (section) {
        // Toggle edit mode
        const isEditing = section.classList.contains('editing');
        
        if (isEditing) {
            // Save changes
            saveSectionChanges(section);
            section.classList.remove('editing');
            button.innerHTML = '<i class="fa-solid fa-edit"></i> Edit';
            button.classList.remove('save-mode');
        } else {
            // Enter edit mode
            section.classList.add('editing');
            button.innerHTML = '<i class="fa-solid fa-save"></i> Save';
            button.classList.add('save-mode');
            makeSectionEditable(section);
        }
    }
}

function makeSectionEditable(section) {
    const infoItems = section.querySelectorAll('.info-item p, .detail-item p');
    
    infoItems.forEach(item => {
        const currentText = item.textContent;
        const input = document.createElement('input');
        input.type = 'text';
        input.value = currentText;
        input.className = 'edit-input';
        
        // Replace p with input
        item.parentNode.replaceChild(input, item);
        
        // Focus on first input
        if (infoItems[0] === item) {
            input.focus();
        }
    });
}

function saveSectionChanges(section) {
    const inputs = section.querySelectorAll('.edit-input');
    
    inputs.forEach(input => {
        const p = document.createElement('p');
        p.textContent = input.value;
        input.parentNode.replaceChild(p, input);
    });
    
    showNotification('Changes saved successfully!', 'success');
}

function initializeTooltips() {
    // Add tooltip functionality to interactive elements
    const tooltipElements = document.querySelectorAll('[title], [data-tooltip]');
    
    tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', showTooltip);
        element.addEventListener('mouseleave', hideTooltip);
    });
}

function showTooltip(event) {
    const element = event.target;
    const text = element.getAttribute('title') || element.getAttribute('data-tooltip');
    
    if (text) {
        const tooltip = document.createElement('div');
        tooltip.className = 'tooltip';
        tooltip.textContent = text;
        tooltip.style.cssText = `
            position: absolute;
            background: #333;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            z-index: 10000;
            pointer-events: none;
            white-space: nowrap;
        `;
        
        document.body.appendChild(tooltip);
        
        const rect = element.getBoundingClientRect();
        tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
        tooltip.style.top = rect.top - tooltip.offsetHeight - 5 + 'px';
        
        element.tooltip = tooltip;
    }
}

function hideTooltip(event) {
    const element = event.target;
    if (element.tooltip) {
        element.tooltip.remove();
        element.tooltip = null;
    }
}

function showNotification(message, type = 'info') {
    // Use notification system from header.js if available
    if (typeof window.HeaderFunctions !== 'undefined' && window.HeaderFunctions.showNotification) {
        window.HeaderFunctions.showNotification(message, type);
        return;
    }
    
    // Fallback notification system
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 8px;
        color: white;
        font-weight: 500;
        z-index: 10000;
        animation: slideInRight 0.3s ease-out;
        max-width: 300px;
    `;
    
    // Set background color based on type
    switch(type) {
        case 'success':
            notification.style.background = '#28a745';
            break;
        case 'error':
            notification.style.background = '#dc3545';
            break;
        case 'warning':
            notification.style.background = '#ffc107';
            notification.style.color = '#333';
            break;
        default:
            notification.style.background = '#17a2b8';
    }
    
    notification.textContent = message;
    document.body.appendChild(notification);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.style.animation = 'slideOutRight 0.3s ease-out';
            setTimeout(() => {
                if (notification.parentNode) {
                    document.body.removeChild(notification);
                }
            }, 300);
        }
    }, 3000);
}

// Export functions for external use
window.ProfileFunctions = {
    handleAvatarChange: handleAvatarChange,
    showNotification: showNotification
};
