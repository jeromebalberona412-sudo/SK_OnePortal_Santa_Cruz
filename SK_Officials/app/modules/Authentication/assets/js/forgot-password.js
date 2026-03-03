// DOM elements
const forgotPasswordForm = document.getElementById('forgotPasswordForm');
const emailInput = document.getElementById('email');
const submitBtn = document.getElementById('submitBtn');
const errorMessage = document.getElementById('errorMessage');
const successMessage = document.getElementById('successMessage');

// Check if user is already logged in
document.addEventListener('DOMContentLoaded', function() {
    // Check for existing session
    const isLoggedIn = sessionStorage.getItem('isLoggedIn');
    const userEmail = sessionStorage.getItem('userEmail');
    
    if (isLoggedIn === 'true' && userEmail) {
        // Redirect to dashboard if already logged in
        window.location.href = '/dashboard';
        return;
    }
    
    // Pre-fill email if it exists in session
    if (sessionStorage.getItem('rememberedEmail')) {
        emailInput.value = sessionStorage.getItem('rememberedEmail');
    }
    
    // Focus on email input
    emailInput.focus();
});

// Form submission handler
forgotPasswordForm.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // Get form values
    const email = emailInput.value.trim();
    
    // Basic validation
    if (!email) {
        showError('Please enter your email address.');
        return;
    }
    
    // Email validation
    if (!isValidEmail(email)) {
        showError('Please enter a valid email address.');
        return;
    }
    
    // Show loading state using centralized loader
    window.loader.show('Sending reset instructions...', '.forgot-password-container');
    hideError();
    hideSuccess();
    
    // Simulate API call delay
    await simulateAsyncOperation(2000);
    
    // Simulate successful email sending
    window.loader.updateText('Instructions sent!');
    
    setTimeout(() => {
        window.loader.hide('.forgot-password-container');
        showSuccess();
        
        // Clear form
        emailInput.value = '';
        
        // Redirect to login after 3 seconds
        setTimeout(() => {
            window.location.href = '/login';
        }, 3000);
    }, 1000);
});

// Utility functions
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function showError(message) {
    errorMessage.textContent = message;
    errorMessage.style.display = 'block';
    errorMessage.classList.add('show');
    
    // Remove animation class after animation completes
    setTimeout(() => {
        errorMessage.classList.remove('show');
    }, 500);
}

function hideError() {
    errorMessage.style.display = 'none';
    errorMessage.classList.remove('show');
}

function showSuccess() {
    successMessage.style.display = 'block';
    successMessage.classList.add('show');
}

function hideSuccess() {
    successMessage.style.display = 'none';
    successMessage.classList.remove('show');
}

function simulateAsyncOperation(delay) {
    return new Promise(resolve => {
        setTimeout(resolve, delay);
    });
}

// Input field enhancements
emailInput.addEventListener('input', function() {
    if (this.value.trim()) {
        this.style.borderColor = '#28a745';
    } else {
        this.style.borderColor = '#e1e5e9';
    }
    hideError();
    hideSuccess();
});

// Enter key handling for better UX
emailInput.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        forgotPasswordForm.dispatchEvent(new Event('submit'));
    }
});

// Prevent form resubmission on page refresh
if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}

// Add keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Escape key to go back to login
    if (e.key === 'Escape') {
        window.location.href = '/login';
    }
});
