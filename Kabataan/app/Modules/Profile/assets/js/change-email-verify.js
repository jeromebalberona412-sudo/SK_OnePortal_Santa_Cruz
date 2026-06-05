document.addEventListener('DOMContentLoaded', function() {
    // Timer configuration
    const TIMER_DURATION = 60; // 60 seconds
    const TIMER_STORAGE_KEY = 'email_change_timer';
    const TIMER_START_KEY = 'email_change_timer_start';
    
    // DOM elements
    const timerElement = document.getElementById('ceTimer');
    const timerCountElement = document.getElementById('ceTimerCount');
    const resendBtn = document.getElementById('ceResendBtn');
    const cancelBtn = document.getElementById('ceCancelBtn');
    
    let timerInterval = null;
    
    // Initialize timer from localStorage or start fresh
    function initTimer() {
        const storedStartTime = localStorage.getItem(TIMER_START_KEY);
        
        if (storedStartTime) {
            const startTime = parseInt(storedStartTime);
            const currentTime = Date.now();
            const elapsedSeconds = Math.floor((currentTime - startTime) / 1000);
            const remainingTime = TIMER_DURATION - elapsedSeconds;
            
            if (remainingTime > 0) {
                // Resume timer with remaining time
                startTimer(remainingTime);
            } else {
                // Timer already expired
                timerExpired();
            }
        } else {
            // Start fresh timer
            startTimer(TIMER_DURATION);
        }
    }
    
    // Start the countdown timer
    function startTimer(seconds) {
        let remaining = seconds;
        
        // Store start time if starting fresh
        if (seconds === TIMER_DURATION) {
            localStorage.setItem(TIMER_START_KEY, Date.now().toString());
        }
        
        // Update display immediately
        updateTimerDisplay(remaining);
        
        // Clear any existing interval
        if (timerInterval) {
            clearInterval(timerInterval);
        }
        
        // Start countdown
        timerInterval = setInterval(function() {
            remaining--;
            
            if (remaining <= 0) {
                clearInterval(timerInterval);
                timerExpired();
            } else {
                updateTimerDisplay(remaining);
            }
        }, 1000);
    }
    
    // Update timer display
    function updateTimerDisplay(seconds) {
        if (timerCountElement) {
            timerCountElement.textContent = seconds;
        }
    }
    
    // Handle timer expiration
    function timerExpired() {
        if (timerElement) {
            timerElement.style.display = 'none';
        }
        if (resendBtn) {
            resendBtn.disabled = false;
        }
        
        // Clear localStorage
        localStorage.removeItem(TIMER_START_KEY);
        localStorage.removeItem(TIMER_STORAGE_KEY);
    }
    
    // Resend verification email
    if (resendBtn) {
        resendBtn.addEventListener('click', function() {
            if (!this.disabled) {
                // Reset and restart timer
                localStorage.removeItem(TIMER_START_KEY);
                localStorage.removeItem(TIMER_STORAGE_KEY);
                
                // Disable button temporarily
                this.disabled = true;
                
                // Show timer again
                if (timerElement) {
                    timerElement.style.display = 'block';
                }
                
                // Start fresh timer
                startTimer(TIMER_DURATION);
                
                // Here you would make an API call to resend the verification email
                // For now, just restart the timer
                console.log('Resending verification email...');
                
                // Simulate API call
                setTimeout(() => {
                    console.log('Verification email resent!');
                }, 500);
            }
        });
    }
    
    // Cancel email change request
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            // Clear timer storage
            localStorage.removeItem(TIMER_START_KEY);
            localStorage.removeItem(TIMER_STORAGE_KEY);
            
            // Clear any running timer
            if (timerInterval) {
                clearInterval(timerInterval);
            }
            
            // Redirect to profile
            window.location.href = '/profile';
        });
    }
    
    // Prevent back button from clearing timer
    window.addEventListener('beforeunload', function() {
        // Timer state is already in localStorage, so it will persist
    });
    
    // Initialize timer on page load
    initTimer();
});
