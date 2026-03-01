/**
 * Admin Two-Factor Authentication - Main Authentication Code Functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('.code-input');
    const verifyBtn = document.getElementById('verifyBtn');
    const fullCodeInput = document.getElementById('fullCode');

    if (!inputs.length || !verifyBtn || !fullCodeInput) return;

    inputs.forEach((input, index) => {
        // Auto-focus next input
        input.addEventListener('input', (e) => {
            const value = e.target.value;
            
            if (value.length === 1) {
                input.classList.add('filled');
                if (index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            } else {
                input.classList.remove('filled');
            }

            // Check if all inputs are filled
            updateVerifyButton();
        });

        // Handle backspace
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !input.value && index > 0) {
                inputs[index - 1].focus();
                inputs[index - 1].value = '';
                inputs[index - 1].classList.remove('filled');
                updateVerifyButton();
            }
        });

        // Only allow numbers
        input.addEventListener('keypress', (e) => {
            if (!/[0-9]/.test(e.key)) {
                e.preventDefault();
            }
        });

        // Handle paste
        input.addEventListener('paste', (e) => {
            e.preventDefault();
            const pastedData = e.clipboardData.getData('text').slice(0, 6);
            const digits = pastedData.split('');
            
            digits.forEach((digit, i) => {
                if (inputs[i] && /[0-9]/.test(digit)) {
                    inputs[i].value = digit;
                    inputs[i].classList.add('filled');
                }
            });

            if (digits.length > 0) {
                const lastIndex = Math.min(digits.length - 1, inputs.length - 1);
                inputs[lastIndex].focus();
            }

            updateVerifyButton();
        });

        // Add Enter key functionality for verification
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                
                const code = Array.from(inputs).map(inp => inp.value).join('');
                
                // Update the full code input
                if (fullCodeInput) {
                    fullCodeInput.value = code;
                }
                
                if (code.length === 6) {
                    // Enable button and trigger click
                    if (verifyBtn) {
                        verifyBtn.disabled = false;
                        verifyBtn.click();
                    }
                } else {
                    // Focus next empty input
                    const nextEmpty = Array.from(inputs).find(inp => !inp.value);
                    if (nextEmpty) {
                        nextEmpty.focus();
                    }
                }
            }
        });
    });

    function updateVerifyButton() {
        const code = Array.from(inputs).map(input => input.value).join('');
        fullCodeInput.value = code;
        
        if (code.length === 6) {
            verifyBtn.disabled = false;
        } else {
            verifyBtn.disabled = true;
        }
    }

    // Form submission
    const twoFactorForm = document.getElementById('twoFactorForm');
    if (twoFactorForm) {
        twoFactorForm.addEventListener('submit', (e) => {
            const code = fullCodeInput.value;
            
            if (code.length !== 6) {
                e.preventDefault();
                return;
            }
            
            // Show loading state
            verifyBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Verifying...</span>';
            verifyBtn.disabled = true;
        });
    }

    // Auto-focus first input
    inputs[0].focus();
});
