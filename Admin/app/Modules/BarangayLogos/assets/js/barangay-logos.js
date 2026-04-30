/**
 * barangay-logos.js
 * Frontend-only. Uses addEventListener (not onclick attrs) so that
 * fileInput.click() is always called synchronously inside a user gesture.
 */

(function () {
    'use strict';

    /* ── Shared state ──────────────────────────────────────── */
    var uploadedCount   = 0;
    var logosVisible    = true;

    // Change-logo flow
    var selectedFileInput  = null;   // the <input type="file"> to trigger
    var selectedCardIndex  = null;   // which card index
    var isChangingLogo     = false;  // true when coming from Change Logo confirm

    // Remove-logo flow
    var removeCardIndex    = null;

    /* ══════════════════════════════════════════════════════════
       INIT — wire everything up after DOM is ready
    ══════════════════════════════════════════════════════════ */
    document.addEventListener('DOMContentLoaded', function () {

        updateCounter();

        // Sidebar active state
        var sidebarLink = document.querySelector('[data-nav-key="barangay-logos"]');
        if (sidebarLink) sidebarLink.classList.add('active');

        /* ── Change-logo confirm button ── */
        var changeConfirmBtn = document.getElementById('blChangeConfirmBtn');
        if (changeConfirmBtn) {
            changeConfirmBtn.addEventListener('click', function () {
                // Hide modal instantly — must stay synchronous for file picker
                hideModal('blChangeModal');

                if (selectedFileInput) {
                    isChangingLogo = true;       // flag: next file pick = change, not fresh upload
                    selectedFileInput.click();   // 🔥 triggers file picker
                }
            });
        }

        /* ── Change-logo cancel button ── */
        var changeCancelBtn = document.getElementById('blChangeCancelBtn');
        if (changeCancelBtn) {
            changeCancelBtn.addEventListener('click', function () {
                selectedFileInput = null;
                selectedCardIndex = null;
                isChangingLogo    = false;
                hideModal('blChangeModal');
            });
        }

        /* ── Remove-logo confirm button ── */
        var removeConfirmBtn = document.getElementById('blRemoveConfirmBtn');
        if (removeConfirmBtn) {
            removeConfirmBtn.addEventListener('click', function () {
                hideModal('blRemoveModal');
                if (removeCardIndex !== null) {
                    doRemoveLogo(removeCardIndex);
                    blToast('Logo successfully removed', 'success');
                    removeCardIndex = null;
                }
            });
        }

        /* ── Remove-logo cancel button ── */
        var removeCancelBtn = document.getElementById('blRemoveCancelBtn');
        if (removeCancelBtn) {
            removeCancelBtn.addEventListener('click', function () {
                removeCardIndex = null;
                hideModal('blRemoveModal');
            });
        }

        /* ── Upload confirm button (fresh upload preview modal) ── */
        var uploadConfirmBtn = document.getElementById('blUploadConfirmBtn');
        if (uploadConfirmBtn) {
            uploadConfirmBtn.addEventListener('click', function () {
                blConfirmUpload();
            });
        }

        /* ── Upload cancel button ── */
        var uploadCancelBtn = document.getElementById('blUploadCancelBtn');
        if (uploadCancelBtn) {
            uploadCancelBtn.addEventListener('click', function () {
                blCancelUpload();
            });
        }

        var uploadCancelBtn2 = document.getElementById('blUploadCancelBtn2');
        if (uploadCancelBtn2) {
            uploadCancelBtn2.addEventListener('click', function () {
                blCancelUpload();
            });
        }

        /* ── Backdrop clicks ── */
        ['blChangeModal', 'blRemoveModal', 'blConfirmModal'].forEach(function (id) {
            var el = document.getElementById(id);
            if (!el) return;
            el.addEventListener('click', function (e) {
                if (e.target === el) {
                    if (id === 'blChangeModal')  { selectedFileInput = null; selectedCardIndex = null; }
                    if (id === 'blRemoveModal')  { removeCardIndex = null; }
                    if (id === 'blConfirmModal') { blCancelUpload(); return; }
                    hideModal(id);
                }
            });
        });

        /* ── Escape key ── */
        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Escape') return;
            ['blChangeModal', 'blRemoveModal', 'blConfirmModal'].forEach(function (id) {
                var el = document.getElementById(id);
                if (el && el.style.display !== 'none') {
                    if (id === 'blConfirmModal') blCancelUpload();
                    else hideModal(id);
                }
            });
        });

        /* ── File inputs: wire change event on each card ── */
        document.querySelectorAll('.bl-file-input').forEach(function (input) {
            input.addEventListener('change', function () {
                var idx  = parseInt(this.getAttribute('data-index'), 10);
                var name = this.getAttribute('data-name') || '';
                var file = this.files[0];
                this.value = ''; // reset so same file can be re-selected

                if (!file) return;

                if (!file.type.startsWith('image/')) {
                    blToast('Please select a valid image file.', 'error');
                    return;
                }

                var reader = new FileReader();
                reader.onload = function (e) {
                    // Always apply directly — no second confirmation
                    var msg = isChangingLogo ? 'Logo successfully changed' : 'Logo successfully uploaded';
                    isChangingLogo = false;
                    applyLogoToCard(idx, e.target.result);
                    blToast(msg, 'success');
                };
                reader.onerror = function () {
                    blToast('Failed to read the image. Please try again.', 'error');
                };
                reader.readAsDataURL(file);
            });
        });

        /* ── Upload buttons: intercept when card already has logo ── */
        document.querySelectorAll('.bl-upload-label').forEach(function (label) {
            label.addEventListener('click', function (e) {
                var idx  = parseInt(this.getAttribute('data-index'), 10);
                var name = this.getAttribute('data-name') || '';
                var card = document.getElementById('card-' + idx);

                if (card && card.classList.contains('has-logo')) {
                    e.preventDefault();
                    // Show change-logo confirmation
                    selectedFileInput = document.getElementById('fileInput-' + idx);
                    selectedCardIndex = idx;
                    showChangeModal(name);
                }
                // else: label's for= opens file picker normally
            });
        });

        /* ── Remove buttons ── */
        document.querySelectorAll('.bl-remove-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var idx  = parseInt(this.getAttribute('data-index'), 10);
                var name = this.getAttribute('data-name') || '';
                removeCardIndex = idx;
                showRemoveModal(name);
            });
        });

        /* ── Hide/Show toggle ── */
        var toggleBtn = document.getElementById('toggleLogosBtn');
        if (toggleBtn) {
            // Logos are visible on load → start green
            toggleBtn.classList.add('logos-visible');
            toggleBtn.addEventListener('click', function () {
                blToggleLogos();
            });
        }

        /* ── Search ── */
        var searchInput = document.getElementById('blSearchInput');
        var searchClear = document.getElementById('blSearchClear');

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                var q = this.value.trim();
                searchClear.style.display = q ? 'flex' : 'none';
                blFilterCards(q);
            });
        }

        if (searchClear) {
            searchClear.addEventListener('click', function () {
                searchInput.value = '';
                searchClear.style.display = 'none';
                blFilterCards('');
                searchInput.focus();
            });
        }

    }); // end DOMContentLoaded

    /* ══════════════════════════════════════════════════════════
       SHOW MODALS
    ══════════════════════════════════════════════════════════ */
    function showChangeModal(barangayName) {
        var el = document.getElementById('blChangeModal');
        var nameEl = document.getElementById('blChangeBarangayName');
        if (nameEl) nameEl.textContent = barangayName;
        showModal(el);
    }

    function showRemoveModal(barangayName) {
        var el = document.getElementById('blRemoveModal');
        var nameEl = document.getElementById('blRemoveBarangayName');
        if (nameEl) nameEl.textContent = barangayName;
        showModal(el);
    }

    function showModal(el) {
        if (!el) return;
        el.style.display = 'flex';
        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                el.classList.add('bl-modal-visible');
            });
        });
    }

    function hideModal(id) {
        var el = document.getElementById(id);
        if (!el) return;
        el.classList.remove('bl-modal-visible');
        // Use a short timeout instead of transitionend to avoid async issues
        setTimeout(function () {
            el.style.display = 'none';
        }, 230);
    }

    /* ══════════════════════════════════════════════════════════
       UPLOAD PREVIEW MODAL (fresh upload — no logo yet)
    ══════════════════════════════════════════════════════════ */
    var pendingIndex   = null;
    var pendingDataUrl = null;

    window.blConfirmUpload = function () {
        if (pendingIndex === null || !pendingDataUrl) return;
        applyLogoToCard(pendingIndex, pendingDataUrl);
        hideModal('blConfirmModal');
        blToast('Logo successfully uploaded', 'success');
        pendingIndex   = null;
        pendingDataUrl = null;
    };

    window.blCancelUpload = function () {
        pendingIndex   = null;
        pendingDataUrl = null;
        hideModal('blConfirmModal');
    };

    /* ══════════════════════════════════════════════════════════
       APPLY LOGO TO CARD
    ══════════════════════════════════════════════════════════ */
    function applyLogoToCard(index, dataUrl) {
        var img         = document.getElementById('img-'           + index);
        var placeholder = document.getElementById('placeholder-'   + index);
        var removeBtn   = document.getElementById('removeBtn-'     + index);
        var card        = document.getElementById('card-'          + index);
        var btnText     = document.getElementById('uploadBtnText-' + index);
        var overlay     = document.getElementById('overlay-'       + index);

        if (!img || !placeholder || !removeBtn || !card) {
            console.error('[BL] Missing elements for index', index);
            return;
        }

        img.src             = dataUrl;
        img.style.display   = logosVisible ? 'block' : 'none';
        img.style.zIndex    = '5';

        placeholder.style.display = 'none';

        if (overlay) overlay.style.display = logosVisible ? 'none' : 'flex';

        removeBtn.style.display = 'flex';
        removeBtn.style.zIndex  = '20';

        var wasUploaded = card.classList.contains('has-logo');
        card.classList.add('has-logo');

        if (!wasUploaded) {
            uploadedCount++;
            updateCounter();
        }

        if (btnText) btnText.textContent = 'Change Logo';

        // Pulse animation
        card.classList.remove('just-uploaded');
        void card.offsetWidth;
        card.classList.add('just-uploaded');
        card.addEventListener('animationend', function handler() {
            card.classList.remove('just-uploaded');
            card.removeEventListener('animationend', handler);
        });
    }

    /* ══════════════════════════════════════════════════════════
       REMOVE LOGO
    ══════════════════════════════════════════════════════════ */
    function doRemoveLogo(index) {
        var img         = document.getElementById('img-'           + index);
        var placeholder = document.getElementById('placeholder-'   + index);
        var removeBtn   = document.getElementById('removeBtn-'     + index);
        var card        = document.getElementById('card-'          + index);
        var btnText     = document.getElementById('uploadBtnText-' + index);
        var overlay     = document.getElementById('overlay-'       + index);

        if (!img || !placeholder || !removeBtn || !card) return;

        img.src                   = '';
        img.style.display         = 'none';
        placeholder.style.display = '';
        removeBtn.style.display   = 'none';
        if (overlay) overlay.style.display = 'none';

        if (card.classList.contains('has-logo')) {
            card.classList.remove('has-logo');
            uploadedCount = Math.max(0, uploadedCount - 1);
            updateCounter();
        }

        if (btnText) btnText.textContent = 'Upload Logo';
    }

    /* ══════════════════════════════════════════════════════════
       HIDE / SHOW LOGOS TOGGLE
    ══════════════════════════════════════════════════════════ */
    function blToggleLogos() {
        logosVisible = !logosVisible;

        var btn     = document.getElementById('toggleLogosBtn');
        var btnText = document.getElementById('toggleBtnText');
        var icon    = document.getElementById('toggleIcon');

        document.querySelectorAll('.bl-card.has-logo').forEach(function (card) {
            var idx     = card.id.replace('card-', '');
            var img     = document.getElementById('img-'     + idx);
            var overlay = document.getElementById('overlay-' + idx);

            if (logosVisible) {
                if (img)     img.style.display     = 'block';
                if (overlay) overlay.style.display = 'none';
            } else {
                if (img)     img.style.display     = 'none';
                if (overlay) overlay.style.display = 'flex';
            }
        });

        if (logosVisible) {
            // Logos now visible → green button
            btn.classList.remove('logos-hidden');
            btn.classList.add('logos-visible');
            btnText.textContent = 'Hide Logos';
            icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2"/>'
                + '<circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>';
        } else {
            // Logos now hidden → gray button
            btn.classList.remove('logos-visible');
            btn.classList.add('logos-hidden');
            btnText.textContent = 'Show Logos';
            icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>'
                + '<path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>'
                + '<line x1="1" y1="1" x2="23" y2="23" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>';
        }
    }

    /* ══════════════════════════════════════════════════════════
       SEARCH / FILTER CARDS BY BARANGAY NAME
    ══════════════════════════════════════════════════════════ */
    function blFilterCards(query) {
        var q = query.toLowerCase().trim();
        var cards = document.querySelectorAll('.bl-card');
        var visibleCount = 0;

        cards.forEach(function (card) {
            var nameEl = card.querySelector('.bl-barangay-name');
            var name   = nameEl ? nameEl.textContent.toLowerCase() : '';
            var match  = !q || name.includes(q);

            card.style.display = match ? '' : 'none';
            if (match) visibleCount++;
        });

        // Show/hide no-results message
        var noResults = document.getElementById('blNoResults');
        if (noResults) {
            noResults.style.display = visibleCount === 0 ? 'block' : 'none';
        }
    }

    /* ── Helpers ─────────────────────────────────────────────── */

    function updateCounter() {
        var el = document.getElementById('uploadedCount');
        if (el) el.textContent = uploadedCount;
    }

    function blToast(message, type) {
        // Remove any existing toast immediately
        var existing = document.getElementById('bl-toast');
        if (existing) {
            clearTimeout(existing._timer);
            existing.remove();
        }

        var toast = document.createElement('div');
        toast.id = 'bl-toast';
        toast.setAttribute('role', 'status');
        toast.setAttribute('aria-live', 'polite');

        Object.assign(toast.style, {
            position:        'fixed',
            top:             '72px',          // just below the header
            left:            '50%',
            transform:       'translateX(-50%) translateY(-18px)',
            zIndex:          '99999',
            padding:         '11px 28px',
            borderRadius:    '8px',
            fontSize:        '0.875rem',
            fontWeight:      '600',
            fontFamily:      'inherit',
            color:           '#fff',
            boxShadow:       '0 4px 18px rgba(0,0,0,.18)',
            opacity:         '0',
            transition:      'opacity 0.22s ease, transform 0.22s ease',
            whiteSpace:      'nowrap',
            pointerEvents:   'none',
            background:      type === 'error' ? '#dc2626' : '#16a34a',
        });

        toast.textContent = message;
        document.body.appendChild(toast);

        // Slide down + fade in
        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                toast.style.opacity   = '1';
                toast.style.transform = 'translateX(-50%) translateY(0)';
            });
        });

        // Auto-dismiss after 2.5 s
        toast._timer = setTimeout(function () {
            toast.style.opacity   = '0';
            toast.style.transform = 'translateX(-50%) translateY(-10px)';
            setTimeout(function () {
                if (toast.parentNode) toast.remove();
            }, 250);
        }, 2500);
    }

})();
