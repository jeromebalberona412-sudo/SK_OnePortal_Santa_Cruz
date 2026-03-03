// Profile JavaScript Functionality
document.addEventListener('DOMContentLoaded', function() {
    initializeProfile();
});

function initializeProfile() {
    // Initialize sidebar functionality
    initializeSidebar();
    
    // Initialize header functionality
    initializeHeader();
    
    // Initialize profile-specific functionality
    initializeProfileFeatures();
}

function initializeSidebar() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('mainSidebar');
    const sidebarClose = document.getElementById('sidebarClose');
    const overlay = document.querySelector('.sidebar-overlay');

    // Toggle sidebar
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', toggleSidebar);
    }

    // Close sidebar
    if (sidebarClose) {
        sidebarClose.addEventListener('click', closeSidebar);
    }

    // Close on overlay click
    if (overlay) {
        overlay.addEventListener('click', closeSidebar);
    }

    // Handle window resize
    window.addEventListener('resize', handleResize);
}

function toggleSidebar() {
    const sidebar = document.getElementById('mainSidebar');
    const mainContent = document.querySelector('.main-content');
    const overlay = document.querySelector('.sidebar-overlay');

    if (sidebar) {
        sidebar.classList.toggle('open');
        mainContent.classList.toggle('sidebar-collapsed');
        
        // Handle overlay for mobile
        if (window.innerWidth <= 768) {
            if (!overlay) {
                createOverlay();
            } else {
                overlay.classList.toggle('show');
            }
        }
    }
}

function closeSidebar() {
    const sidebar = document.getElementById('mainSidebar');
    const mainContent = document.querySelector('.main-content');
    const overlay = document.querySelector('.sidebar-overlay');

    if (sidebar) {
        sidebar.classList.remove('open');
        mainContent.classList.remove('sidebar-collapsed');
        
        if (overlay) {
            overlay.classList.remove('show');
        }
    }
}

function createOverlay() {
    const overlay = document.createElement('div');
    overlay.className = 'sidebar-overlay';
    overlay.addEventListener('click', closeSidebar);
    document.body.appendChild(overlay);
}

function handleResize() {
    const sidebar = document.getElementById('mainSidebar');
    const mainContent = document.querySelector('.main-content');
    const overlay = document.querySelector('.sidebar-overlay');

    if (window.innerWidth > 768) {
        // Desktop view - remove mobile overlay
        if (overlay) {
            overlay.classList.remove('show');
        }
        
        // Reset sidebar state for desktop
        if (sidebar) {
            sidebar.classList.remove('open');
            mainContent.classList.remove('sidebar-collapsed');
        }
    }
}

function initializeHeader() {
    const userMenuToggle = document.getElementById('userMenuToggle');
    const userDropdown = document.getElementById('userDropdown');

    // User menu dropdown functionality
    if (userMenuToggle && userDropdown) {
        userMenuToggle.addEventListener('click', toggleUserDropdown);
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', handleOutsideClick);
}

function toggleUserDropdown(event) {
    event.stopPropagation();
    const userDropdown = document.getElementById('userDropdown');
    
    if (userDropdown) {
        userDropdown.classList.toggle('show');
    }
}

function handleOutsideClick(event) {
    const userMenuToggle = document.getElementById('userMenuToggle');
    const userDropdown = document.getElementById('userDropdown');
    
    // Close user dropdown if clicking outside
    if (userDropdown && userMenuToggle) {
        if (!userMenuToggle.contains(event.target) && !userDropdown.contains(event.target)) {
            userDropdown.classList.remove('show');
        }
    }
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
    fileInput.style.display = 'none';
    
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validate file type and size
            if (file.type.startsWith('image/')) {
                if (file.size <= 5 * 1024 * 1024) { // 5MB limit
                    uploadAvatar(file);
                } else {
                    showNotification('File size must be less than 5MB', 'error');
                }
            } else {
                showNotification('Please select an image file', 'error');
            }
        }
    });
    
    document.body.appendChild(fileInput);
    fileInput.click();
    document.body.removeChild(fileInput);
}

function uploadAvatar(file) {
    // Create FormData for file upload
    const formData = new FormData();
    formData.append('avatar', file);
    
    // Show loading state
    const changeAvatarBtn = document.querySelector('.change-avatar-btn');
    const originalText = changeAvatarBtn.innerHTML;
    changeAvatarBtn.innerHTML = '<span class="loading-icon">&#128259;</span> Uploading...';
    changeAvatarBtn.disabled = true;
    
    // Simulate upload (replace with actual AJAX call)
    setTimeout(() => {
        // Update avatar preview
        const reader = new FileReader();
        reader.onload = function(e) {
            const profileAvatar = document.querySelector('.profile-avatar');
            if (profileAvatar) {
                profileAvatar.src = e.target.result;
            }
        };
        reader.readAsDataURL(file);
        
        // Reset button
        changeAvatarBtn.innerHTML = originalText;
        changeAvatarBtn.disabled = false;
        
        showNotification('Profile photo updated successfully!', 'success');
    }, 1500);
}

function handleEditSection(event) {
    const editBtn = event.currentTarget;
    const section = editBtn.closest('.profile-section');
    const sectionTitle = section.querySelector('.section-title').textContent.trim();
    
    // Show edit modal or inline editing
    showEditModal(sectionTitle, section);
}

function showEditModal(sectionTitle, section) {
    // Create modal overlay
    const modalOverlay = document.createElement('div');
    modalOverlay.className = 'edit-modal-overlay';
    modalOverlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(5px);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
    `;
    
    // Create modal content
    const modalContent = document.createElement('div');
    modalContent.className = 'edit-modal-content';
    modalContent.style.cssText = `
        background: white;
        border-radius: 15px;
        padding: 30px;
        max-width: 500px;
        width: 90%;
        max-height: 80vh;
        overflow-y: auto;
        animation: modalSlideIn 0.3s ease-out;
    `;
    
    modalContent.innerHTML = `
        <h3 style="margin-bottom: 20px; color: #333;">Edit ${sectionTitle}</h3>
        <p style="color: #666; margin-bottom: 25px;">Update your ${sectionTitle.toLowerCase()} information below.</p>
        
        <div class="edit-form">
            <!-- Form fields will be populated based on section -->
        </div>
        
        <div class="modal-actions" style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 25px;">
            <button class="btn-cancel" style="padding: 10px 20px; border: 1px solid #dee2e6; background: #f8f9fa; color: #6c757d; border-radius: 8px; cursor: pointer;">Cancel</button>
            <button class="btn-save" style="padding: 10px 20px; border: none; background: #667eea; color: white; border-radius: 8px; cursor: pointer;">Save Changes</button>
        </div>
    `;
    
    // Add modal to page
    modalOverlay.appendChild(modalContent);
    document.body.appendChild(modalOverlay);
    
    // Add event listeners
    const cancelBtn = modalContent.querySelector('.btn-cancel');
    const saveBtn = modalContent.querySelector('.btn-save');
    
    cancelBtn.addEventListener('click', () => {
        document.body.removeChild(modalOverlay);
    });
    
    saveBtn.addEventListener('click', () => {
        // Save changes (implement actual save logic)
        showNotification(`${sectionTitle} updated successfully!`, 'success');
        document.body.removeChild(modalOverlay);
    });
    
    // Close on overlay click
    modalOverlay.addEventListener('click', (e) => {
        if (e.target === modalOverlay) {
            document.body.removeChild(modalOverlay);
        }
    });
}

function initializeTooltips() {
    // Add tooltips for interactive elements
    const tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(element => {
        element.addEventListener('mouseenter', showTooltip);
        element.addEventListener('mouseleave', hideTooltip);
    });
}

function showTooltip(event) {
    const element = event.target;
    const tooltipText = element.getAttribute('data-tooltip');
    
    if (tooltipText) {
        const tooltip = document.createElement('div');
        tooltip.className = 'tooltip';
        tooltip.textContent = tooltipText;
        tooltip.style.cssText = `
            position: absolute;
            background: #333;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
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
        document.body.removeChild(element.tooltip);
        element.tooltip = null;
    }
}

function showNotification(message, type = 'info') {
    // Create notification element
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

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: scale(0.9) translateY(-20px);
        }
        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }
    
    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(100%);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    @keyframes slideOutRight {
        from {
            opacity: 1;
            transform: translateX(0);
        }
        to {
            opacity: 0;
            transform: translateX(100%);
        }
    }
`;
document.head.appendChild(style);

// Export functions for external use
window.ProfileFunctions = {
    toggleSidebar: toggleSidebar,
    closeSidebar: closeSidebar,
    handleAvatarChange: handleAvatarChange,
    showNotification: showNotification
};
