/**
 * forgot-password.js
 * Handles the 3-step forgot password flow:
 *   Step 1 — Enter email → send reset link
 *   Step 2 — Enter 6-digit OTP (demo code: 1 2 3 4 5 6)
 *   Step 3 — Set new password + confirm → success screen
 */

(function () {
    'use strict';

    /* ─────────────────────────────────────────
       DEMO: the correct OTP code is 1 2 3 4 5 6
    ───────────────────────────────────────── */
    var DEMO_OTP     = '123456';
    var OTP_COOLDOWN = 29;   // seconds before resend is allowed

    /* ── DOM refs ── */
    var card        = document.getElementById('fpCard');
    var steps       = {
        email    : document.getElementById('fpStepEmail'),
        otp      : document.getElementById('fpStepOtp'),
        password : document.getElementById('fpStepPassword'),
        success  : document.getElementById('fpStepSuccess'),
    };

    /* Step 1 */
    var fpForm      = document.getElementById('fpForm');
    var fpSubmitBtn = document.getElementById('fpSubmitBtn');
    var fpEmailIn   = document.getElementById('fpEmailInput');

    /* Step 2 */
    var otpEmailEl  = document.getElementById('fpOtpEmail');
    var otpBoxes    = document.querySelectorAll('.fp-otp-box');
    var verifyBtn   = document.getElementById('fpOtpVerifyBtn');
    var cancelBtn   = document.getElementById('fpOtpCancelBtn');
    var resendLbl   = document.getElementById('fpResendLabel');
    var otpErrorEl  = document.getElementById('fpOtpError');

    /* Step 3 */
    var pwForm      = document.getElementById('fpPwForm');
    var pwInput     = document.getElementById('fpNewPassword');
    var pwConfirm   = document.getElementById('fpConfirmPassword');
    var pwSaveBtn   = document.getElementById('fpSavePasswordBtn');
    var pwToggle1   = document.getElementById('fpPwToggle1');
    var pwToggle2   = document.getElementById('fpPwToggle2');
    var pwStrength  = document.getElementById('fpPwStrength');
    var pwMatchMsg  = document.getElementById('fpPwMatch');

    /* Shared */
    var overlay     = document.getElementById('signin-overlay');
    var timerEl     = null;
    var countdownId = null;
    var secondsLeft = OTP_COOLDOWN;
    var currentEmail = '';

    /* ══════════════════════════════════════════
       STEP NAVIGATION
    ══════════════════════════════════════════ */
    var CARD_MODES = {};   /* no background switching — all steps use the same white card */

    function goTo(stepName) {
        Object.keys(steps).forEach(function (key) {
            var el = steps[key];
            if (!el) return;
            el.classList.remove('is-active');
        });

        var target = steps[stepName];
        if (target) target.classList.add('is-active');
    }

    /* ══════════════════════════════════════════
       STEP 1 — EMAIL FORM
    ══════════════════════════════════════════ */
    if (fpEmailIn && fpSubmitBtn) {
        function toggleSend() {
            fpSubmitBtn.disabled = !fpEmailIn.value.trim();
        }
        toggleSend();
        fpEmailIn.addEventListener('input', toggleSend);
    }

    if (fpForm) {
        fpForm.addEventListener('submit', function (e) {
            e.preventDefault();
            currentEmail = fpEmailIn ? fpEmailIn.value.trim() : '';
            if (!currentEmail) return;

            showOverlay('Sending Reset Code', 'Please wait...');
            setTimeout(function () {
                hideOverlay();
                goTo('otp');
                if (otpEmailEl) otpEmailEl.textContent = currentEmail;
                resetOtp();
                startCountdown();
                if (otpBoxes.length) otpBoxes[0].focus();
            }, 1000);
        });
    }

    /* ══════════════════════════════════════════
       STEP 2 — OTP
    ══════════════════════════════════════════ */
    otpBoxes.forEach(function (box, idx) {
        box.addEventListener('keydown', function (e) {
            if (e.key === 'Backspace') {
                if (!box.value && idx > 0) {
                    otpBoxes[idx - 1].value = '';
                    otpBoxes[idx - 1].classList.remove('is-filled');
                    otpBoxes[idx - 1].focus();
                }
                checkOtpComplete();
                return;
            }
            if (e.key === 'ArrowLeft'  && idx > 0)                    { otpBoxes[idx - 1].focus(); return; }
            if (e.key === 'ArrowRight' && idx < otpBoxes.length - 1)  { otpBoxes[idx + 1].focus(); return; }
            if (!/^\d$/.test(e.key) && !['Tab', 'Delete'].includes(e.key)) {
                e.preventDefault();
            } else {
                box.value = '';
            }
        });

        box.addEventListener('input', function () {
            var v = box.value.replace(/\D/g, '');
            box.value = v ? v[0] : '';
            box.classList.toggle('is-filled', !!box.value);
            if (box.value && idx < otpBoxes.length - 1) otpBoxes[idx + 1].focus();
            checkOtpComplete();
            if (otpErrorEl) otpErrorEl.style.display = 'none';
        });

        box.addEventListener('paste', function (e) {
            e.preventDefault();
            var digits = (e.clipboardData || window.clipboardData)
                .getData('text').replace(/\D/g, '').slice(0, otpBoxes.length);
            digits.split('').forEach(function (d, i) {
                if (otpBoxes[i]) { otpBoxes[i].value = d; otpBoxes[i].classList.add('is-filled'); }
            });
            var last = Math.min(digits.length, otpBoxes.length) - 1;
            if (last >= 0) otpBoxes[last].focus();
            checkOtpComplete();
        });
    });

    function checkOtpComplete() {
        if (!verifyBtn) return;
        verifyBtn.disabled = !Array.from(otpBoxes).every(function (b) { return b.value.length === 1; });
    }

    function resetOtp() {
        otpBoxes.forEach(function (b) { b.value = ''; b.classList.remove('is-filled', 'is-error'); });
        if (otpErrorEl) otpErrorEl.style.display = 'none';
        checkOtpComplete();
    }

    function shakeOtpError(msg) {
        otpBoxes.forEach(function (b) { b.classList.add('is-error'); });
        if (otpErrorEl) { otpErrorEl.textContent = msg || 'Invalid code. Please try again.'; otpErrorEl.style.display = 'block'; }
        setTimeout(function () {
            otpBoxes.forEach(function (b) { b.classList.remove('is-error'); });
            resetOtp();
            if (otpBoxes.length) otpBoxes[0].focus();
        }, 600);
    }

    if (verifyBtn) {
        verifyBtn.addEventListener('click', function () {
            var code = Array.from(otpBoxes).map(function (b) { return b.value; }).join('');

            /* Demo: accept 123456 */
            if (code === DEMO_OTP) {
                showOverlay('Verifying Code', 'Please wait...');
                setTimeout(function () {
                    hideOverlay();
                    goTo('password');
                    if (pwInput) pwInput.focus();
                }, 800);
            } else {
                shakeOtpError('Incorrect code. Please try again.');
            }
        });
    }

    if (cancelBtn) {
        cancelBtn.addEventListener('click', function () {
            stopCountdown();
            resetOtp();
            goTo('email');
        });
    }

    /* ── Countdown ── */
    function startCountdown() {
        secondsLeft = OTP_COOLDOWN;
        renderResend();
        stopCountdown();
        countdownId = setInterval(function () {
            secondsLeft--;
            if (timerEl) timerEl.textContent = secondsLeft + 's';
            if (secondsLeft <= 0) {
                stopCountdown();
                if (resendLbl) {
                    resendLbl.innerHTML = '<span class="fp-otp-resend-link" id="fpResendLink">Resend code</span>';
                    var link = document.getElementById('fpResendLink');
                    if (link) link.addEventListener('click', function () { startCountdown(); });
                }
            }
        }, 1000);
    }

    function stopCountdown() {
        if (countdownId) { clearInterval(countdownId); countdownId = null; }
    }

    function renderResend() {
        if (!resendLbl) return;
        resendLbl.innerHTML = 'Resend code in <span class="fp-otp-timer" id="fpOtpTimer">' + OTP_COOLDOWN + 's</span>';
        timerEl = document.getElementById('fpOtpTimer');
    }

    /* ══════════════════════════════════════════
       STEP 3 — SET NEW PASSWORD
    ══════════════════════════════════════════ */
    /* Password strength meter */
    function getStrength(pw) {
        var score = 0;
        if (pw.length >= 8)  score++;
        if (pw.length >= 12) score++;
        if (/[A-Z]/.test(pw)) score++;
        if (/[0-9]/.test(pw)) score++;
        if (/[^A-Za-z0-9]/.test(pw)) score++;
        return score; /* 0-5 */
    }

    function renderStrength(score) {
        if (!pwStrength) return;
        var labels = ['', 'Very Weak', 'Weak', 'Fair', 'Strong', 'Very Strong'];
        var colors = ['', '#ef4444', '#f97316', '#eab308', '#22c55e', '#16a34a'];
        var pct    = (score / 5) * 100;
        pwStrength.innerHTML =
            '<div style="height:4px;border-radius:99px;background:#e2e8f0;overflow:hidden;margin-bottom:0.3rem;">' +
            '<div style="height:100%;width:' + pct + '%;background:' + (colors[score] || '#083080') + ';border-radius:99px;transition:width 0.3s,background 0.3s;"></div></div>' +
            (score > 0 ? '<span style="font-size:0.75rem;color:' + (colors[score]) + ';font-weight:600;">' + labels[score] + '</span>' : '');
    }

    function checkPwMatch() {
        if (!pwInput || !pwConfirm || !pwMatchMsg) return false;
        var pw  = pwInput.value;
        var cpw = pwConfirm.value;
        if (!cpw) { pwMatchMsg.textContent = ''; pwMatchMsg.style.color = ''; return false; }
        if (pw === cpw) {
            pwMatchMsg.textContent = '✓ Passwords match';
            pwMatchMsg.style.color = '#16a34a';
            return true;
        } else {
            pwMatchMsg.textContent = '✗ Passwords do not match';
            pwMatchMsg.style.color = '#dc2626';
            return false;
        }
    }

    function checkPwFormReady() {
        if (!pwSaveBtn || !pwInput || !pwConfirm) return;
        var valid = pwInput.value.length >= 8 && pwInput.value === pwConfirm.value;
        pwSaveBtn.disabled = !valid;
    }

    if (pwInput) {
        pwInput.addEventListener('input', function () {
            renderStrength(getStrength(pwInput.value));
            checkPwMatch();
            checkPwFormReady();
        });
    }
    if (pwConfirm) {
        pwConfirm.addEventListener('input', function () {
            checkPwMatch();
            checkPwFormReady();
        });
    }

    /* Password visibility toggles */
    function bindToggle(btn, inputEl) {
        if (!btn || !inputEl) return;
        btn.addEventListener('click', function () {
            var isHidden = inputEl.type === 'password';
            inputEl.type = isHidden ? 'text' : 'password';
            btn.querySelector('.pw-eye-show').style.display = isHidden ? 'none'  : '';
            btn.querySelector('.pw-eye-hide').style.display = isHidden ? ''      : 'none';
        });
    }
    bindToggle(pwToggle1, pwInput);
    bindToggle(pwToggle2, pwConfirm);

    if (pwForm) {
        pwForm.addEventListener('submit', function (e) {
            e.preventDefault();
            if (!checkPwMatch()) return;
            showOverlay('Saving Password', 'Please wait...');
            setTimeout(function () {
                hideOverlay();
                goTo('success');
            }, 1000);
        });
    }

    /* ══════════════════════════════════════════
       OVERLAY HELPERS
    ══════════════════════════════════════════ */
    function showOverlay(title, sub) {
        if (!overlay) return;
        var t = overlay.querySelector('.signin-overlay-title');
        var s = overlay.querySelector('.signin-overlay-sub');
        if (t) t.textContent = title || 'Please wait';
        if (s) s.textContent = sub   || '';
        overlay.removeAttribute('hidden');
        overlay.classList.add('is-visible');
    }

    function hideOverlay() {
        if (!overlay) return;
        overlay.classList.remove('is-visible');
        overlay.setAttribute('hidden', '');
    }

    /* ══════════════════════════════════════════
       INIT — check if server already sent status
    ══════════════════════════════════════════ */
    var serverStatus = document.getElementById('fpServerStatus');
    if (serverStatus && serverStatus.dataset.email) {
        currentEmail = serverStatus.dataset.email;
        if (otpEmailEl) otpEmailEl.textContent = currentEmail;
        goTo('otp');
        startCountdown();
    }

})();
