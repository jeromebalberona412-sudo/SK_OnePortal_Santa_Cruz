/**
 * SK Officials — Resend activation email with Cloudflare Turnstile
 */
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('resendActivationForm');
    const btn = document.getElementById('resendActivationBtn');
    const label = document.getElementById('resendActivationLabel');

    if (!form || !btn || !label) {
        return;
    }

    let remaining = parseInt(btn.getAttribute('data-remaining'), 10) || 0;

    function formatTime(seconds) {
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return mins + ':' + String(secs).padStart(2, '0');
    }

    function tick() {
        if (remaining <= 0) {
            btn.disabled = false;
            label.textContent = 'Resend activation email';
            return;
        }

        btn.disabled = true;
        label.textContent = 'Resend in ' + formatTime(remaining);
        remaining -= 1;
        window.setTimeout(tick, 1000);
    }

    tick();

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        if (btn.disabled) {
            return;
        }

        const gate = window.SkOfficialsTurnstileGate;
        if (!gate || !gate.isEnabled || !gate.isEnabled()) {
            btn.disabled = true;
            label.textContent = 'Sending...';
            form.submit();
            return;
        }

        gate.challenge().then(function (token) {
            gate.injectToken(form, token);
            btn.disabled = true;
            label.textContent = 'Sending...';
            HTMLFormElement.prototype.submit.call(form);
        }).catch(function () {
            if (remaining <= 0) {
                btn.disabled = false;
            }
            label.textContent = remaining > 0 ? ('Resend in ' + formatTime(remaining)) : 'Resend activation email';
        });
    });
});
