/**
 * Admin Two-Factor Authentication - Recovery Code Functionality
 */

/**
 * Toggle Recovery Code Form
 */
function toggleRecoveryCode(event) {
    event.preventDefault();
    const twoFactorForm = document.getElementById('twoFactorForm');
    const recoveryForm = document.getElementById('recoveryForm');
    
    if (twoFactorForm.style.display === 'none') {
        twoFactorForm.style.display = 'block';
        recoveryForm.style.display = 'none';
    } else {
        twoFactorForm.style.display = 'none';
        recoveryForm.style.display = 'block';
        document.getElementById('recovery_code').focus();
    }
}

/**
 * Initialize Recovery Code Form
 */
document.addEventListener('DOMContentLoaded', function() {
    // Add click handlers for recovery code links
    const recoveryLinks = document.querySelectorAll('a[onclick*="toggleRecoveryCode"]');
    recoveryLinks.forEach(link => {
        link.addEventListener('click', toggleRecoveryCode);
    });
});
