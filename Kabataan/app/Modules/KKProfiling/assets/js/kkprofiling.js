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
    const nameInput   = document.getElementById('kkpSignatureName');

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

        closePad();
    }

    // Button events
    if (triggerBtn) triggerBtn.addEventListener('click', openPad);
    if (closeBtn)   closeBtn.addEventListener('click', closePad);
    if (clearBtn)   clearBtn.addEventListener('click', clearCanvas);
    if (saveBtn)    saveBtn.addEventListener('click', saveSig);

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
