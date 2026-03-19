// DOM elements
const loginForm = document.getElementById('loginForm');
const emailInput = document.getElementById('email');
const passwordInput = document.getElementById('password');
const loginBtn = document.getElementById('loginBtn');
const errorMessage = document.getElementById('errorMessage');
const forgotBtn = document.getElementById('forgotBtn');

// Form submission handler
loginForm.addEventListener('submit', async function (e) {
    e.preventDefault();

    const email = emailInput.value.trim();
    const password = passwordInput.value;

    if (!email || !password) {
        showError('Please fill in all fields.');
        return;
    }

    if (!isValidEmail(email)) {
        showError('Please enter a valid email address.');
        return;
    }

    window.loader.show('Logging in...', '.login-container');
    hideError();

    const formData = new FormData();
    formData.append('email', email);
    formData.append('password', password);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');

    try {
        const response = await fetch('/login', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(data.message || 'The credentials are incorrect.');
        }

        window.loader.updateText('Login Successful!');

        setTimeout(() => {
            window.location.href = data.redirect || '/dashboard';
        }, 800);
    } catch (error) {
        window.loader.hide('.login-container');
        showError(error.message || 'The credentials are incorrect.');
        passwordInput.value = '';
        passwordInput.focus();
    }
});

// Forgot password handler
forgotBtn.addEventListener('click', function () {
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

    setTimeout(() => {
        errorMessage.classList.remove('show');
    }, 500);
}

function hideError() {
    errorMessage.style.display = 'none';
    errorMessage.classList.remove('show');
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
