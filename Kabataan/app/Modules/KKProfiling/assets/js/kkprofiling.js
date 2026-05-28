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

        // Enable Sign button only when name is filled and no signature yet
        if (triggerBtn) {
            const hasSig = !!(sigInput && sigInput.value);
            const canSign = fullName.trim().length > 0 && !hasSig;
            triggerBtn.disabled = !canSign;
            triggerBtn.setAttribute('aria-disabled', canSign ? 'false' : 'true');
        }
    }

    ['kkpLastName', 'kkpFirstName', 'kkpMiddleName', 'kkpSuffix'].forEach(function (id) {
        const el = document.getElementById(id);
        if (el) el.addEventListener('input', updateSignatureName);
        if (el && el.tagName === 'SELECT') el.addEventListener('change', updateSignatureName);
    });
    updateSignatureName();

    // ── Name input restrictions (letters only, no leading spaces) ──
    ['kkpLastName', 'kkpFirstName', 'kkpMiddleName'].forEach(function (id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.addEventListener('input', function () {
            this.value = this.value
                .replace(/^\s+/, '')
                .replace(/[^A-Za-z.\-\s]/g, '')
                .replace(/\s{2,}/g, ' ');
        });
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

        if (!checkbox.checked) return;

        if (checkbox.value === 'Yes') {
            yesCell.style.display = '';
            noCell.style.display  = '';
        } else {
            noCell.style.display  = '';
            yesCell.style.display = '';
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

    function hasAnySpace(v) {
        return /\s/.test(v || '');
    }

    // ── 1. Last Name ──
    const lastName = document.querySelector('input[name="last_name"]');
    if (!lastName || !lastName.value.trim()) {
        errors.push('Last Name is required.');
        fieldError(lastName, 'Last Name is required.');
    } else if (!/^[A-Za-z.\-\s]+$/.test(lastName.value) || /^\s/.test(lastName.value)) {
        errors.push('Last Name must contain letters only and no leading spaces.');
        fieldError(lastName, 'Letters only, no leading spaces.');
    }

    // ── 2. First Name ──
    const firstName = document.querySelector('input[name="first_name"]');
    if (!firstName || !firstName.value.trim()) {
        errors.push('First Name is required.');
        fieldError(firstName, 'First Name is required.');
    } else if (!/^[A-Za-z.\-\s]+$/.test(firstName.value) || /^\s/.test(firstName.value)) {
        errors.push('First Name must contain letters only and no leading spaces.');
        fieldError(firstName, 'Letters only, no leading spaces.');
    }

    // ── 3. Purok/Zone ──
    const purok = document.querySelector('input[name="purok_zone"]');
    if (!purok || !purok.value.trim()) {
        errors.push('Purok/Zone is required.');
        fieldError(purok, 'Purok/Zone is required.');
    } else if (/^\s+$/.test(purok.value)) {
        errors.push('Purok/Zone cannot be spaces only.');
        fieldError(purok, 'Cannot be spaces only.');
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
    } else if (+age.value < 15 || +age.value > 30) {
        errors.push('Age must be 15 to 30 only.');
        fieldError(age, 'Age must be 15 to 30 only.');
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
    } else if (hasAnySpace(email.value) || !/^[A-Za-z0-9._%+-]+@gmail\.com$/i.test(email.value)) {
        errors.push('Email must be a valid @gmail.com address and must not contain spaces.');
        fieldError(email, 'Use valid @gmail.com only, no spaces.');
    }

    // ── 8. Contact # ──
    const contact = document.querySelector('input[name="contact_number"]');
    if (!contact || !contact.value.trim()) {
        errors.push('Contact # is required.');
        fieldError(contact, 'Contact # is required.');
    } else if (hasAnySpace(contact.value)) {
        errors.push('Contact # must not contain spaces.');
        fieldError(contact, 'No spaces allowed.');
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

    // 15b. Vote frequency required
    if (!hiddenVal('kkpVoteFreq')) {
        errors.push('Vote frequency is required.');
        const el = document.getElementById('kkpVoteFreq');
        if (el) {
            const err = document.createElement('span');
            err.className = 'kkp-field-error';
            err.textContent = 'Please select vote frequency.';
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

    if (!hiddenVal('kkpKkTimes')) {
        errors.push('KK Assembly attendance count is required.');
        const el = document.getElementById('kkpKkTimes');
        if (el) {
            const err = document.createElement('span');
            err.className = 'kkp-field-error';
            err.textContent = 'Please select number of times attended.';
            el.parentNode.insertBefore(err, el.nextSibling);
        }
    }

    // ── 18. FB Account ──
    const facebook = document.querySelector('input[name="facebook"]');
    if (!facebook || !facebook.value.trim()) {
        errors.push('FB Account is required.');
        fieldError(facebook, 'FB Account is required.');
    } else if (hasAnySpace(facebook.value) || /[0-9]/.test(facebook.value)) {
        errors.push('FB Account must not contain spaces or numbers.');
        fieldError(facebook, 'No spaces and no numbers.');
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

    // ── All valid — show submit loading then submit ──
    const submitBtn = document.getElementById('kkpSubmitBtn');
    const submitText = document.getElementById('kkpSubmitText');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.classList.add('is-submitting');
    }
    if (submitText) submitText.textContent = 'Submitting KK Profiling...';

    return true; // Allow form submission
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
            if (btnText)    btnText.textContent      = 'Signing up...';

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
    const clearSavedBtn = document.getElementById('kkpSignatureClearSaved');

    if (!canvas || !overlay) return;

    const ctx = canvas.getContext('2d');
    let isDrawing    = false;
    let hasSignature = false;

    // Show Sign button only after name is auto-filled and no signature yet
    // (name is auto-filled from name fields — handled in main IIFE above)
    // triggerBtn visibility is managed by updateSignatureName()

    function setupCanvas(preserveDrawing) {
        const rect = canvas.getBoundingClientRect();
        const cssW = rect.width  || 500;
        const cssH = rect.height || 260;
        const dpr  = window.devicePixelRatio || 1;

        const snapshot = preserveDrawing ? canvas.toDataURL('image/png') : null;

        canvas.width  = Math.floor(cssW * dpr);
        canvas.height = Math.floor(cssH * dpr);
        canvas.style.width  = cssW + 'px';
        canvas.style.height = cssH + 'px';

        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        ctx.strokeStyle = '#000';
        ctx.lineWidth   = 2;
        ctx.lineCap     = 'round';
        ctx.lineJoin    = 'round';

        if (snapshot && snapshot !== 'data:,') {
            const img = new Image();
            img.onload = function () {
                ctx.drawImage(img, 0, 0, cssW, cssH);
            };
            img.src = snapshot;
        }
    }

    function openPad() {
        overlay.style.display = 'flex';
        setupCanvas(false);
        // Restore existing signature if any
        if (sigInput && sigInput.value) {
            const img = new Image();
            img.onload = function () {
                const rect = canvas.getBoundingClientRect();
                ctx.drawImage(img, 0, 0, rect.width || 500, rect.height || 260);
                hasSignature = true;
                hidePlaceholder();
            };
            img.src = sigInput.value;
        } else {
            clearCanvas();
        }
    }

    function closePad() {
        overlay.style.display = 'none';
    }

    function clearCanvas() {
        const rect = canvas.getBoundingClientRect();
        ctx.clearRect(0, 0, rect.width || 500, rect.height || 260);
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
        hasSignature = true;
    }

    function stopDraw() { isDrawing = false; }

    function canvasHasInk() {
        const w = canvas.width;
        const h = canvas.height;
        if (!w || !h) return false;
        const data = ctx.getImageData(0, 0, w, h).data;
        for (let i = 3; i < data.length; i += 4) {
            if (data[i] !== 0) return true;
        }
        return false;
    }

    function saveSig() {
        if (!hasSignature || !canvasHasInk()) {
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

        // Lock Sign button; show clear button for re-signing
        if (triggerBtn) {
            triggerBtn.disabled = true;
            triggerBtn.setAttribute('aria-disabled', 'true');
        }
        if (clearSavedBtn) clearSavedBtn.style.display = 'inline-flex';

        // Close confirmation modal
        const confirmOverlay = document.getElementById('kkpSigConfirmOverlay');
        if (confirmOverlay) confirmOverlay.style.display = 'none';

        closePad();
    }

    function clearSavedSignature() {
        if (sigInput) sigInput.value = '';
        if (sigOverlay) sigOverlay.style.display = 'none';
        if (sigPreview) sigPreview.removeAttribute('src');
        if (clearSavedBtn) clearSavedBtn.style.display = 'none';
        if (triggerBtn) {
            triggerBtn.disabled = false;
            triggerBtn.setAttribute('aria-disabled', 'false');
        }
        hasSignature = false;
        showPlaceholder();
        clearCanvas();
    }

    // Button events
    if (triggerBtn) {
        triggerBtn.addEventListener('click', function () {
            if (triggerBtn.disabled) return;
            openPad();
        });
    }
    if (closeBtn)   closeBtn.addEventListener('click', closePad);
    if (clearBtn)   clearBtn.addEventListener('click', clearCanvas);
    if (saveBtn)    saveBtn.addEventListener('click', saveSig);
    if (clearSavedBtn) clearSavedBtn.addEventListener('click', clearSavedSignature);

    // Initial state (in case of server-side repopulation)
    if (sigInput && sigInput.value && sigPreview && sigOverlay) {
        sigPreview.src = sigInput.value;
        sigOverlay.style.display = 'flex';
        if (triggerBtn) {
            triggerBtn.disabled = true;
            triggerBtn.setAttribute('aria-disabled', 'true');
        }
        if (clearSavedBtn) clearSavedBtn.style.display = 'inline-flex';
    }

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
        if (overlay.style.display !== 'none') setupCanvas(true);
    });
})();
