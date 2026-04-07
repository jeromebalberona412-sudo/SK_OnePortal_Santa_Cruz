// Predefined credentials
const VALID_CREDENTIALS = {
    email: 'jeromebalberona412@gmail.com',
    password: 'Jerome123!'
};

// DOM elements
const loginForm = document.getElementById('loginForm');
const emailInput = document.getElementById('email');
const passwordInput = document.getElementById('password');
const loginBtn = document.getElementById('loginBtn');
const errorMessage = document.getElementById('errorMessage');
const forgotBtn = document.getElementById('forgotBtn');

// Check if already logged in
if (sessionStorage.getItem('isLoggedIn') === 'true') {
    window.location.href = '/dashboard';
}

// Form submission handler
loginForm.addEventListener('submit', async function (e) {
    e.preventDefault();

    // Get form values
    const email = emailInput.value.trim();
    const password = passwordInput.value;

    // Basic validation
    if (!email || !password) {
        showError('Please fill in all fields.');
        return;
    }

    // Email validation
    if (!isValidEmail(email)) {
        showError('Please enter a valid email address.');
        return;
    }

    // Show loading state using centralized loader
    window.loader.show('Logging in...', '.login-container');
    hideError();

    // Create form data for Laravel authentication
    const formData = new FormData();
    formData.append('email', email);
    formData.append('password', password);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');

    try {
        // Send authentication request to Laravel
        const response = await fetch('/login', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });

        const data = await response.json();

        // Check if login is successful (either Laravel auth or predefined credentials)
        if ((response.ok && data.success) || (email === VALID_CREDENTIALS.email && password === VALID_CREDENTIALS.password)) {
            // Single successful login - store session and redirect
            window.loader.updateText('Login Successful!');

            // Store session info
            sessionStorage.setItem('isLoggedIn', 'true');
            sessionStorage.setItem('userEmail', email);

            // Single redirect to dashboard
            setTimeout(() => {
                window.location.href = '/dashboard';
            }, 1000);
        } else {
            throw new Error(data.message || 'Invalid credentials');
        }

    } catch (error) {
        // Failed login
        window.loader.hide('.login-container');
        showError('The credentials are incorrect.');
        passwordInput.value = ''; // Clear only password field
        passwordInput.focus(); // Focus back to password field
    }
});

// Forgot password handler
forgotBtn.addEventListener('click', function () {
    // Redirect to forgot password page
    window.location.href = '/forgot-password';
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

function simulateAsyncOperation(delay) {
    return new Promise(resolve => {
        setTimeout(resolve, delay);
    });
}

// Input field enhancements
emailInput.addEventListener('input', function () {
    if (this.value.trim()) {
        this.style.borderColor = '#28a745';
    } else {
        this.style.borderColor = '#e1e5e9';
    }
    hideError();
});

passwordInput.addEventListener('input', function () {
    if (this.value) {
        this.style.borderColor = '#28a745';
    } else {
        this.style.borderColor = '#e1e5e9';
    }
    hideError();
});

// Enter key handling for better UX
passwordInput.addEventListener('keypress', function (e) {
    if (e.key === 'Enter') {
        loginForm.dispatchEvent(new Event('submit'));
    }
});

emailInput.addEventListener('keypress', function (e) {
    if (e.key === 'Enter') {
        passwordInput.focus();
    }
});

// Focus management
emailInput.focus();

// Prevent form resubmission on page refresh
if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}