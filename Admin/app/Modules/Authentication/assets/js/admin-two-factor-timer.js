/**
 * Admin Two-Factor Authentication - Session Timer Functionality
 */

/**
 * Session Timer Countdown
 */
(function() {
    const timerElement = document.getElementById('sessionTimer');
    if (!timerElement) return;
    
    let timeLeft = 10 * 60; // 10 minutes in seconds

    function updateTimer() {
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        
        const display = `${minutes}:${seconds.toString().padStart(2, '0')}`;
        timerElement.innerHTML = `Session expires in: <strong style="color: ${timeLeft < 60 ? '#ef4444' : '#10b981'}">${display}</strong>`;
        
        if (timeLeft <= 0) {
            // Session expired
            timerElement.innerHTML = '<strong style="color: #ef4444">Session expired!</strong>';
            
            // Disable forms
            const twoFactorForm = document.getElementById('twoFactorForm');
            const recoveryForm = document.getElementById('recoveryForm');
            
            if (twoFactorForm) {
                twoFactorForm.querySelectorAll('input, button').forEach(el => el.disabled = true);
            }
            if (recoveryForm) {
                recoveryForm.querySelectorAll('input, button').forEach(el => el.disabled = true);
            }
            
            // Show alert and redirect
            setTimeout(() => {
                alert('Your session has expired. Please login again.');
                window.location.href = '/login';
            }, 1000);
            
            return;
        }
        
        timeLeft--;
        setTimeout(updateTimer, 1000);
    }
    
    updateTimer();
})();
