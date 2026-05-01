/**
 * KK Profiling Form JavaScript
 * Navigation, age auto-fill, alert dismiss, and e-signature pad
 */

(function () {
    'use strict';

    // ── Navigation Drawer ──
    const navHamburger = document.getElementById('navHamburger');
    const navDrawer    = document.getElementById('navDrawer');
    if (navHamburger && navDrawer) {
        navHamburger.addEventListener('click', function (e) {
            e.stopPropagation();
            navDrawer.classList.toggle('open');
        });
        document.addEventListener('click', function (e) {
            if (!navHamburger.contains(e.target) && !navDrawer.contains(e.target)) {
                navDrawer.classList.remove('open');
            }
        });
        navDrawer.addEventListener('click', function (e) { e.stopPropagation(); });
    }

    // ── Login buttons ──
    const navLoginBtn       = document.getElementById('navLoginBtn');
    const navDrawerLoginBtn = document.getElementById('navDrawerLoginBtn');
    if (navLoginBtn)       navLoginBtn.addEventListener('click', () => window.location.href = '/youth/login');
    if (navDrawerLoginBtn) navDrawerLoginBtn.addEventListener('click', () => window.location.href = '/youth/login');

    // ── Age auto-fill from birthday ──
    const form          = document.getElementById('kkProfilingForm');
    const birthdayInput = form && form.querySelector('input[name="birthday"]');
    const ageInput      = form && form.querySelector('input[name="age"]');
    if (birthdayInput && ageInput) {
        birthdayInput.addEventListener('change', function () {
            const bday  = new Date(this.value);
            const today = new Date();
            let age = today.getFullYear() - bday.getFullYear();
            const m = today.getMonth() - bday.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < bday.getDate())) age--;
            if (age >= 15 && age <= 30) {
                ageInput.value = age;
            } else {
                alert('Age must be between 15 and 30 years old for KK profiling.');
                this.value = '';
                ageInput.value = '';
            }
        });
    }

    // ── Auto-fill signature name from name fields ──
    // When any name field changes, update the signature name input automatically
    function updateSignatureName() {
        const last   = (document.getElementById('kkpLastName')   || {}).value   || '';
        const first  = (document.getElementById('kkpFirstName')  || {}).value   || '';
        const middle = (document.getElementById('kkpMiddleName') || {}).value   || '';
        const suffix = (document.getElementById('kkpSuffix')     || {}).value   || '';
        const sigNameInput = document.getElementById('kkpSignatureName');
        const triggerBtn   = document.getElementById('kkpSignatureTrigger');
        const sigInput     = document.getElementById('kkpSignatureData');

        if (!sigNameInput) return;

        const parts = [first, middle, last, suffix].filter(Boolean);
        const fullName = parts.join(' ');
        sigNameInput.value = fullName;

        // Show Sign button if name is filled and no signature yet
        if (triggerBtn) {
            const hasSig = sigInput && sigInput.value;
            triggerBtn.style.display = (fullName.trim().length > 0 && !hasSig) ? 'inline-flex' : 'none';
        }
    }

    ['kkpLastName', 'kkpFirstName', 'kkpMiddleName', 'kkpSuffix'].forEach(function (id) {
        const el = document.getElementById(id);
        if (el) el.addEventListener('input', updateSignatureName);
        if (el && el.tagName === 'SELECT') el.addEventListener('change', updateSignatureName);
    });

    // ── Single-check helper (like SK Officials kkfSingleCheck) ──
    // Allows only one checkbox checked per group
    window.kkpSingleCheck = function (checkbox, hiddenId) {
        const group = document.querySelectorAll('input[name="' + checkbox.name + '"]');
        group.forEach(function (cb) {
            if (cb !== checkbox) cb.checked = false;
        });
        const hidden = document.getElementById(hiddenId);
        if (hidden) hidden.value = checkbox.checked ? checkbox.value : '';
    };

    // ── KK Assembly conditional show/hide ──
    window.kkpHandleAssembly = function (checkbox) {
        const yesCell = document.getElementById('kkpAssemblyYesCell');
        const noCell  = document.getElementById('kkpAssemblyNoCell');
        if (!yesCell || !noCell) return;

        if (!checkbox.checked) {
            // unchecked — hide both
            yesCell.style.display = 'none';
            noCell.style.display  = 'none';
            return;
        }

        if (checkbox.value === 'Yes') {
            yesCell.style.display = '';
            noCell.style.display  = 'none';
            // clear No reason
            document.querySelectorAll('input[name="kk_reasonChk"]').forEach(cb => cb.checked = false);
            const r = document.getElementById('kkpKkReason');
            if (r) r.value = '';
        } else {
            noCell.style.display  = '';
            yesCell.style.display = 'none';
            // clear Yes times
            document.querySelectorAll('input[name="kk_timesChk"]').forEach(cb => cb.checked = false);
            const t = document.getElementById('kkpKkTimes');
            if (t) t.value = '';
        }
    };

    // ── Auto-dismiss success alert ──
    const successAlert = document.querySelector('.kkp-alert-success');
    if (successAlert) {
        setTimeout(function () {
            successAlert.style.transition = 'opacity 0.5s, transform 0.5s';
            successAlert.style.opacity   = '0';
            successAlert.style.transform = 'translateY(-10px)';
            setTimeout(() => successAlert.remove(), 500);
        }, 5000);
    }

    console.log('KK Profiling form initialized');
})();

/* ═══════════════════════════════════════════════════════════════
   FORM SUBMISSION HANDLER - Validate then Show Email Verification
═══════════════════════════════════════════════════════════════ */
window.handleFormSubmit = function(event) {
    event.preventDefault();

    // ── Clear previous errors ──
    document.querySelectorAll('.kkp-field-error').forEach(el => el.remove());
    document.querySelectorAll('.kkp-input-err').forEach(el => el.classList.remove('kkp-input-err'));

    const errors = [];

    // Helper: show inline error below an element
    function fieldError(el, msg) {
        if (!el) return;
        el.classList.add('kkp-input-err');
        const err = document.createElement('span');
        err.className = 'kkp-field-error';
        err.textContent = msg;
        el.parentNode.insertBefore(err, el.nextSibling);
    }

    // Helper: get hidden input value (single-check groups)
    function hiddenVal(id) {
        const el = document.getElementById(id);
        return el ? el.value.trim() : '';
    }

    // ── 1. Last Name ──
    const lastName = document.querySelector('input[name="last_name"]');
    if (!lastName || !lastName.value.trim()) {
        errors.push('Last Name is required.');
        fieldError(lastName, 'Last Name is required.');
    }

    // ── 2. First Name ──
    const firstName = document.querySelector('input[name="first_name"]');
    if (!firstName || !firstName.value.trim()) {
        errors.push('First Name is required.');
        fieldError(firstName, 'First Name is required.');
    }

    // ── 3. Purok/Zone ──
    const purok = document.querySelector('input[name="purok_zone"]');
    if (!purok || !purok.value.trim()) {
        errors.push('Purok/Zone is required.');
        fieldError(purok, 'Purok/Zone is required.');
    }

    // ── 4. Sex ──
    if (!hiddenVal('kkpSex')) {
        errors.push('Sex Assigned by Birth is required.');
        const sexBlock = document.querySelector('.kkp-sex-block');
        if (sexBlock) {
            const err = document.createElement('span');
            err.className = 'kkp-field-error';
            err.textContent = 'Please select Sex Assigned by Birth.';
            sexBlock.parentNode.insertBefore(err, sexBlock.nextSibling);
        }
    }

    // ── 5. Age ──
    const age = document.querySelector('input[name="age"]');
    if (!age || !age.value.trim()) {
        errors.push('Age is required.');
        fieldError(age, 'Age is required.');
    }

    // ── 6. Birthday ──
    const birthday = document.querySelector('input[name="birthday"]');
    if (!birthday || !birthday.value.trim()) {
        errors.push('Birthday is required.');
        fieldError(birthday, 'Birthday is required.');
    }

    // ── 7. Email ──
    const email = document.querySelector('input[name="email"]');
    if (!email || !email.value.trim()) {
        errors.push('E-mail address is required.');
        fieldError(email, 'E-mail address is required.');
    }

    // ── 8. Contact # ──
    const contact = document.querySelector('input[name="contact_number"]');
    if (!contact || !contact.value.trim()) {
        errors.push('Contact # is required.');
        fieldError(contact, 'Contact # is required.');
    }

    // ── 9. Civil Status ──
    if (!hiddenVal('kkpCivilStatus')) {
        errors.push('Civil Status is required.');
        const el = document.getElementById('kkpCivilStatus');
        if (el) {
            const err = document.createElement('span');
            err.className = 'kkp-field-error';
            err.textContent = 'Please select Civil Status.';
            el.parentNode.insertBefore(err, el.nextSibling);
        }
    }

    // ── 10. Youth Age Group ──
    if (!hiddenVal('kkpYouthAgeGroup')) {
        errors.push('Youth Age Group is required.');
        const el = document.getElementById('kkpYouthAgeGroup');
        if (el) {
            const err = document.createElement('span');
            err.className = 'kkp-field-error';
            err.textContent = 'Please select Youth Age Group.';
            el.parentNode.insertBefore(err, el.nextSibling);
        }
    }

    // ── 11. Educational Background ──
    if (!hiddenVal('kkpEducation')) {
        errors.push('Educational Background is required.');
        const el = document.getElementById('kkpEducation');
        if (el) {
            const err = document.createElement('span');
            err.className = 'kkp-field-error';
            err.textContent = 'Please select Educational Background.';
            el.parentNode.insertBefore(err, el.nextSibling);
        }
    }

    // ── 12. Youth Classification ──
    if (!hiddenVal('kkpYouthClass')) {
        errors.push('Youth Classification is required.');
        const el = document.getElementById('kkpYouthClass');
        if (el) {
            const err = document.createElement('span');
            err.className = 'kkp-field-error';
            err.textContent = 'Please select Youth Classification.';
            el.parentNode.insertBefore(err, el.nextSibling);
        }
    }

    // ── 13. Work Status ──
    if (!hiddenVal('kkpWorkStatus')) {
        errors.push('Work Status is required.');
        const el = document.getElementById('kkpWorkStatus');
        if (el) {
            const err = document.createElement('span');
            err.className = 'kkp-field-error';
            err.textContent = 'Please select Work Status.';
            el.parentNode.insertBefore(err, el.nextSibling);
        }
    }

    // ── 14. Registered SK Voter ──
    if (!hiddenVal('kkpSkVoter')) {
        errors.push('Registered SK Voter is required.');
        const el = document.getElementById('kkpSkVoter');
        if (el) {
            const err = document.createElement('span');
            err.className = 'kkp-field-error';
            err.textContent = 'Please select Yes or No.';
            el.parentNode.insertBefore(err, el.nextSibling);
        }
    }

    // ── 15. Did you vote last SK ──
    if (!hiddenVal('kkpSkVoted')) {
        errors.push('Did you vote last SK is required.');
        const el = document.getElementById('kkpSkVoted');
        if (el) {
            const err = document.createElement('span');
            err.className = 'kkp-field-error';
            err.textContent = 'Please select Yes or No.';
            el.parentNode.insertBefore(err, el.nextSibling);
        }
    }

    // ── 16. Registered National Voter ──
    if (!hiddenVal('kkpNationalVoter')) {
        errors.push('Registered National Voter is required.');
        const el = document.getElementById('kkpNationalVoter');
        if (el) {
            const err = document.createElement('span');
            err.className = 'kkp-field-error';
            err.textContent = 'Please select Yes or No.';
            el.parentNode.insertBefore(err, el.nextSibling);
        }
    }

    // ── 17. KK Assembly ──
    if (!hiddenVal('kkpKkAssembly')) {
        errors.push('Have you attended a KK Assembly is required.');
        const el = document.getElementById('kkpKkAssembly');
        if (el) {
            const err = document.createElement('span');
            err.className = 'kkp-field-error';
            err.textContent = 'Please select Yes or No.';
            el.parentNode.insertBefore(err, el.nextSibling);
        }
    }

    // ── 18. FB Account ──
    const facebook = document.querySelector('input[name="facebook"]');
    if (!facebook || !facebook.value.trim()) {
        errors.push('FB Account is required.');
        fieldError(facebook, 'FB Account is required.');
    }

    // ── 19. Willing to join group chat ──
    if (!hiddenVal('kkpGroupChat')) {
        errors.push('Willing to join the group chat is required.');
        const el = document.getElementById('kkpGroupChat');
        if (el) {
            const err = document.createElement('span');
            err.className = 'kkp-field-error';
            err.textContent = 'Please select Yes or No.';
            el.parentNode.insertBefore(err, el.nextSibling);
        }
    }

    // ── 20. Signature ──
    const sigData = document.getElementById('kkpSignatureData');
    if (!sigData || !sigData.value.trim()) {
        errors.push('Signature is required.');
        const sigSection = document.querySelector('.kkp-sig-section');
        if (sigSection) {
            let err = sigSection.querySelector('.kkp-field-error');
            if (!err) {
                err = document.createElement('span');
                err.className = 'kkp-field-error';
                err.textContent = 'Please provide your signature.';
                sigSection.appendChild(err);
            }
        }
    }

    // ── If errors, scroll to first error and stop ──
    if (errors.length > 0) {
        const firstErr = document.querySelector('.kkp-field-error');
        if (firstErr) {
            firstErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        return false;
    }

    // ── All valid — show email verification card ──
    const emailVal = email.value.trim();
    const formCard        = document.getElementById('kkpFormCard');
    const emailVerifyCard = document.getElementById('emailVerifyCard');
    const displayEmail    = document.getElementById('displayEmail');

    if (formCard)        formCard.style.display        = 'none';
    if (emailVerifyCard) {
        emailVerifyCard.style.display = 'block';
        if (displayEmail) displayEmail.textContent = emailVal;
    }

    // Start resend timer
    if (window.startResendTimer) window.startResendTimer();

    window.scrollTo({ top: 0, behavior: 'smooth' });
    return false;
};

/* ═══════════════════════════════════════════════════════════════
   EMAIL VERIFICATION HANDLERS
═══════════════════════════════════════════════════════════════ */
(function() {
    // Back to form button
    const backToFormBtn  = document.getElementById('backToFormBtn');
    const backToFormBtn2 = document.getElementById('backToFormBtn2');

    function showForm() {
        const formCard        = document.getElementById('kkpFormCard');
        const emailVerifyCard = document.getElementById('emailVerifyCard');
        const setPasswordCard = document.getElementById('setPasswordCard');
        const regSuccessCard  = document.getElementById('regSuccessCard');

        if (formCard)        formCard.style.display        = 'block';
        if (emailVerifyCard) emailVerifyCard.style.display = 'none';
        if (setPasswordCard) setPasswordCard.style.display = 'none';
        if (regSuccessCard)  regSuccessCard.style.display  = 'none';

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    if (backToFormBtn)  backToFormBtn.addEventListener('click', showForm);
    if (backToFormBtn2) backToFormBtn2.addEventListener('click', showForm);

    // ── Resend email with 1-minute countdown ──
    let resendInterval = null;

    window.startResendTimer = function () {
        const btn   = document.getElementById('resendEmailBtn');
        const timer = document.getElementById('resendTimer');
        if (!btn || !timer) return;

        let seconds = 60;
        btn.disabled = true;
        timer.style.display = 'inline';
        timer.textContent = '(1:00)';

        clearInterval(resendInterval);
        resendInterval = setInterval(function () {
            seconds--;
            const m = Math.floor(seconds / 60);
            const s = seconds % 60;
            timer.textContent = '(' + m + ':' + (s < 10 ? '0' : '') + s + ')';

            if (seconds <= 0) {
                clearInterval(resendInterval);
                btn.disabled = false;
                timer.style.display = 'none';
            }
        }, 1000);
    };

    const resendEmailBtn = document.getElementById('resendEmailBtn');
    if (resendEmailBtn) {
        resendEmailBtn.addEventListener('click', function () {
            if (this.disabled) return;
            const btn = this;
            const originalText = btn.textContent;
            btn.textContent = 'Email sent!';
            setTimeout(() => { btn.textContent = 'Resend verification email'; }, 2500);
            window.startResendTimer();
        });
    }
})();

/* ═══════════════════════════════════════════════════════════════
   SET PASSWORD HANDLERS
═══════════════════════════════════════════════════════════════ */
(function() {
    // Password toggle
    function setupPasswordToggle(toggleBtnId, inputId) {
        const toggleBtn = document.getElementById(toggleBtnId);
        const input     = document.getElementById(inputId);
        if (!toggleBtn || !input) return;

        const eyeIcon    = toggleBtn.querySelector('.eye-icon');
        const eyeOffIcon = toggleBtn.querySelector('.eye-off-icon');

        toggleBtn.addEventListener('click', function () {
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            if (eyeIcon && eyeOffIcon) {
                eyeIcon.style.display    = isPassword ? 'none'  : 'block';
                eyeOffIcon.style.display = isPassword ? 'block' : 'none';
            }
        });
    }

    setupPasswordToggle('togglePassword',        'password');
    setupPasswordToggle('togglePasswordConfirm', 'password_confirmation');

    // Form submission with loading animation
    const setPasswordForm = document.getElementById('setPasswordForm');
    if (setPasswordForm) {
        setPasswordForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const password        = document.getElementById('password');
            const passwordConfirm = document.getElementById('password_confirmation');
            if (!password || !passwordConfirm) return;

            if (password.value !== passwordConfirm.value) {
                showSetPwError('Passwords do not match. Please try again.');
                passwordConfirm.focus();
                return;
            }
            if (password.value.length < 8) {
                showSetPwError('Password must be at least 8 characters long.');
                password.focus();
                return;
            }

            // Show loading state
            const submitBtn  = document.getElementById('setpwSubmitBtn');
            const btnIcon    = submitBtn && submitBtn.querySelector('.setpw-btn-icon');
            const btnSpinner = submitBtn && submitBtn.querySelector('.setpw-btn-spinner');
            const btnText    = submitBtn && submitBtn.querySelector('.setpw-btn-text');

            if (submitBtn)  submitBtn.disabled = true;
            if (btnIcon)    btnIcon.style.display    = 'none';
            if (btnSpinner) btnSpinner.style.display = 'block';
            if (btnText)    btnText.textContent      = 'Registering...';

            // Simulate async registration (replace with real AJAX in production)
            setTimeout(function () {
                // Hide set password card, show success card
                const setPasswordCard = document.getElementById('setPasswordCard');
                const regSuccessCard  = document.getElementById('regSuccessCard');

                if (setPasswordCard) setPasswordCard.style.display = 'none';
                if (regSuccessCard)  regSuccessCard.style.display  = 'block';

                // Reset button state
                if (submitBtn)  submitBtn.disabled = false;
                if (btnIcon)    btnIcon.style.display    = 'block';
                if (btnSpinner) btnSpinner.style.display = 'none';
                if (btnText)    btnText.textContent      = 'Complete Registration';

                window.scrollTo({ top: 0, behavior: 'smooth' });
            }, 2000);
        });
    }

    function showSetPwError(msg) {
        let errEl = document.getElementById('setpwErrorMsg');
        if (!errEl) {
            errEl = document.createElement('p');
            errEl.id = 'setpwErrorMsg';
            errEl.className = 'setpw-error-msg';
            const form = document.getElementById('setPasswordForm');
            if (form) form.prepend(errEl);
        }
        errEl.textContent = msg;
        errEl.style.display = 'block';
        setTimeout(() => { errEl.style.display = 'none'; }, 4000);
    }
})();

/* ═══════════════════════════════════════════════════════════════
   SIGNATURE PAD — mirrors SK Officials Kabataan module pattern
   Signature image overlaid on top of printed name
═══════════════════════════════════════════════════════════════ */
(function initKKPSignaturePad() {
    const overlay     = document.getElementById('kkpSignaturePadOverlay');
    const triggerBtn  = document.getElementById('kkpSignatureTrigger');
    const closeBtn    = document.getElementById('kkpSignaturePadClose');
    const clearBtn    = document.getElementById('kkpSignaturePadClear');
    const saveBtn     = document.getElementById('kkpSignaturePadSave');
    const canvas      = document.getElementById('kkpSignaturePadCanvas');
    const placeholder = document.getElementById('kkpSignatureCanvasPlaceholder');
    const sigInput    = document.getElementById('kkpSignatureData');
    const sigPreview  = document.getElementById('kkpSignaturePreview');
    const sigOverlay  = document.getElementById('kkpSignatureOverlay');

    if (!canvas || !overlay) return;

    const ctx = canvas.getContext('2d');
    let isDrawing    = false;
    let hasSignature = false;

    // Show Sign button only after name is auto-filled and no signature yet
    // (name is auto-filled from name fields — handled in main IIFE above)
    // triggerBtn visibility is managed by updateSignatureName()

    function setupCanvas() {
        const rect    = canvas.getBoundingClientRect();
        canvas.width  = rect.width  || 500;
        canvas.height = rect.height || 260;
        ctx.strokeStyle = '#000';
        ctx.lineWidth   = 2;
        ctx.lineCap     = 'round';
        ctx.lineJoin    = 'round';
    }

    function openPad() {
        overlay.style.display = 'flex';
        setupCanvas();
        // Restore existing signature if any
        if (sigInput && sigInput.value) {
            const img = new Image();
            img.onload = function () {
                ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                hasSignature = true;
                hidePlaceholder();
            };
            img.src = sigInput.value;
        }
    }

    function closePad() {
        overlay.style.display = 'none';
        clearCanvas();
    }

    function clearCanvas() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        hasSignature = false;
        showPlaceholder();
    }

    function hidePlaceholder() { if (placeholder) placeholder.style.display = 'none'; }
    function showPlaceholder() { if (placeholder) placeholder.style.display = 'block'; }

    function getPos(e) {
        const rect = canvas.getBoundingClientRect();
        const cx   = e.touches ? e.touches[0].clientX : e.clientX;
        const cy   = e.touches ? e.touches[0].clientY : e.clientY;
        return { x: cx - rect.left, y: cy - rect.top };
    }

    function startDraw(e) {
        isDrawing    = true;
        hasSignature = true;
        const p = getPos(e);
        ctx.beginPath();
        ctx.moveTo(p.x, p.y);
        hidePlaceholder();
    }

    function draw(e) {
        if (!isDrawing) return;
        e.preventDefault();
        const p = getPos(e);
        ctx.lineTo(p.x, p.y);
        ctx.stroke();
    }

    function stopDraw() { isDrawing = false; }

    function saveSig() {
        if (!hasSignature) {
            alert('Please provide a signature before saving.');
            return;
        }
        // Show confirmation modal instead of saving immediately
        const confirmOverlay = document.getElementById('kkpSigConfirmOverlay');
        if (confirmOverlay) confirmOverlay.style.display = 'flex';
    }

    function doSaveSig() {
        const data = canvas.toDataURL('image/png');

        // Store in hidden input
        if (sigInput) sigInput.value = data;

        // Show signature image overlaid on top of printed name
        if (sigPreview && sigOverlay) {
            sigPreview.src = data;
            sigOverlay.style.display = 'flex';
        }

        // Hide the Sign button (signature is done)
        if (triggerBtn) triggerBtn.style.display = 'none';

        // Close confirmation modal
        const confirmOverlay = document.getElementById('kkpSigConfirmOverlay');
        if (confirmOverlay) confirmOverlay.style.display = 'none';

        closePad();
    }

    // Button events
    if (triggerBtn) triggerBtn.addEventListener('click', openPad);
    if (closeBtn)   closeBtn.addEventListener('click', closePad);
    if (clearBtn)   clearBtn.addEventListener('click', clearCanvas);
    if (saveBtn)    saveBtn.addEventListener('click', saveSig);

    // Confirmation modal buttons
    const confirmOverlay  = document.getElementById('kkpSigConfirmOverlay');
    const confirmCancelBtn = document.getElementById('kkpSigConfirmCancel');
    const confirmSaveBtn   = document.getElementById('kkpSigConfirmSave');

    if (confirmCancelBtn) {
        confirmCancelBtn.addEventListener('click', function () {
            if (confirmOverlay) confirmOverlay.style.display = 'none';
        });
    }
    if (confirmSaveBtn) {
        confirmSaveBtn.addEventListener('click', doSaveSig);
    }
    // Close confirmation on backdrop click
    if (confirmOverlay) {
        confirmOverlay.addEventListener('click', function (e) {
            if (e.target === confirmOverlay) confirmOverlay.style.display = 'none';
        });
    }

    // Close on backdrop click
    if (overlay) {
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) closePad();
        });
    }

    // Mouse events
    canvas.addEventListener('mousedown',  startDraw);
    canvas.addEventListener('mousemove',  draw);
    canvas.addEventListener('mouseup',    stopDraw);
    canvas.addEventListener('mouseleave', stopDraw);

    // Touch events
    canvas.addEventListener('touchstart', function (e) { e.preventDefault(); startDraw(e); }, { passive: false });
    canvas.addEventListener('touchmove',  function (e) { e.preventDefault(); draw(e); },      { passive: false });
    canvas.addEventListener('touchend',   function (e) { e.preventDefault(); stopDraw(); },   { passive: false });

    // Resize
    window.addEventListener('resize', function () {
        if (overlay.style.display !== 'none') setupCanvas();
    });
})();
