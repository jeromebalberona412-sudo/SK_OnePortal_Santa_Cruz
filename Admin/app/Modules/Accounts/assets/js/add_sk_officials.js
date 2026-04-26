// ── Add SK Officials — modal controls & form logic ───────────

// ── Window resize toggle ─────────────────────────────────────
// On first open: button shows MAXIMIZE icon (four-corner arrows)
// After click:   button shows RESTORE-DOWN icon (overlapping squares)
let addOfficialsIsMaximized = false;

const ICON_MAXIMIZE = `
    <path d="M8 3H5a2 2 0 0 0-2 2v3"/>
    <path d="M21 8V5a2 2 0 0 0-2-2h-3"/>
    <path d="M3 16v3a2 2 0 0 0 2 2h3"/>
    <path d="M16 21h3a2 2 0 0 0 2-2v-3"/>
`;

const ICON_RESTORE = `
    <rect x="3" y="7" width="11" height="11" rx="1.5"/>
    <path d="M10 7V5a2 2 0 0 1 2-2h7a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2h-2"/>
`;

window.toggleAddOfficialsSize = function () {
    const overlay = document.getElementById('addSkOfficialsModal');
    const content = document.getElementById('addSkOfficialsModalContent');
    const icon    = document.getElementById('addOfficialsResizeIcon');
    const btn     = document.getElementById('addOfficialsResizeBtn');

    if (!overlay || !content || !icon) return;

    addOfficialsIsMaximized = !addOfficialsIsMaximized;

    if (addOfficialsIsMaximized) {
        content.style.width        = '100vw';
        content.style.maxWidth     = '100vw';
        content.style.height       = '100vh';
        content.style.maxHeight    = '100vh';
        content.style.borderRadius = '0';
        overlay.style.padding      = '0';
        btn.title      = 'Restore Down';
        icon.innerHTML = ICON_RESTORE;
    } else {
        content.style.cssText = '';
        overlay.style.padding = '';
        btn.title      = 'Maximize';
        icon.innerHTML = ICON_MAXIMIZE;
    }
};

// ── Close modal ──────────────────────────────────────────────
window.closeAddSkOfficialsModal = function () {
    const modal = document.getElementById('addSkOfficialsModal');
    if (!modal) return;

    modal.style.display = 'none';

    // Reset size state
    addOfficialsIsMaximized = false;
    const content = document.getElementById('addSkOfficialsModalContent');
    if (content) {
        content.style.cssText = '';
    }

    // Reset form
    const form = document.getElementById('addSkOfficialsForm');
    if (form) {
        form.reset();
        form.querySelectorAll('.is-invalid, .is-valid').forEach(el => el.classList.remove('is-invalid', 'is-valid'));
        form.querySelectorAll('.validation-error').forEach(el => el.remove());
    }

    // Reset tabs back to manual
    switchAddOfficialTab('manual');
};

// ── Success modal ────────────────────────────────────────────
window.closeSkOfficialsSuccessModal = function () {
    const modal = document.getElementById('skOfficialsSuccessModal');
    if (modal) modal.style.display = 'none';
    closeAddSkOfficialsModal();
    // No reload — row was added to the table UI directly
};

// ── Tab switcher ─────────────────────────────────────────────
window.switchAddOfficialTab = function (tab) {
    const manualPane = document.getElementById('addOfficialManualPane');
    const batchPane  = document.getElementById('addOfficialBatchPane');
    const tabManual  = document.getElementById('tabManual');
    const tabBatch   = document.getElementById('tabBatch');

    if (tab === 'manual') {
        if (manualPane) manualPane.style.display = '';
        if (batchPane)  batchPane.style.display  = 'none';
        if (tabManual)  tabManual.classList.add('active');
        if (tabBatch)   tabBatch.classList.remove('active');
    } else {
        if (manualPane) manualPane.style.display = 'none';
        if (batchPane)  batchPane.style.display  = '';
        if (tabManual)  tabManual.classList.remove('active');
        if (tabBatch)   tabBatch.classList.add('active');
    }
};

// ── DOMContentLoaded: form validation + batch upload ─────────
document.addEventListener('DOMContentLoaded', function () {

    // ── Form validation ──────────────────────────────────────
    const form = document.getElementById('addSkOfficialsForm');

    function showError(input, msg) {
        clearError(input);
        const span = document.createElement('span');
        span.className = 'validation-error';
        span.textContent = msg;
        input.parentNode.appendChild(span);
        input.classList.add('is-invalid');
    }

    function clearError(input) {
        input.classList.remove('is-invalid', 'is-valid');
        const existing = input.parentNode.querySelector('.validation-error');
        if (existing) existing.remove();
    }

    function validateField(input) {
        const val = input.value.trim();
        if (input.hasAttribute('required') && !val) {
            showError(input, 'This field is required');
            return false;
        }
        if (input.type === 'email' && val && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
            showError(input, 'Enter a valid email address');
            return false;
        }
        if (input.id === 'official_contact_number' && val && val.length < 10) {
            showError(input, 'Contact number must be at least 10 digits');
            return false;
        }
        clearError(input);
        if (val) input.classList.add('is-valid');
        return true;
    }

    if (form) {
        // Text-only name fields
        ['official_first_name', 'official_last_name', 'official_middle_name'].forEach(id => {
            const el = document.getElementById(id);
            if (!el) return;
            el.addEventListener('input', () => {
                el.value = el.value.replace(/[^a-zA-Z\s\-']/g, '');
                if (el.classList.contains('is-invalid')) validateField(el);
            });
        });

        // Numbers-only contact
        const contactEl = document.getElementById('official_contact_number');
        if (contactEl) {
            contactEl.addEventListener('input', () => {
                contactEl.value = contactEl.value.replace(/\D/g, '');
                if (contactEl.classList.contains('is-invalid')) validateField(contactEl);
            });
        }

        // Blur validation on all required fields
        form.querySelectorAll('[required]').forEach(el => {
            el.addEventListener('blur', () => validateField(el));
        });

        // Submit — validate only; actual row injection handled by manage_account.js
        form.addEventListener('submit', function (e) {
            let valid = true;
            form.querySelectorAll('[required]').forEach(el => {
                if (!validateField(el)) valid = false;
            });
            if (!valid) {
                e.preventDefault();
                e.stopImmediatePropagation();
                const first = form.querySelector('.is-invalid');
                if (first) first.focus();
            }
        });
    }

    // ── Batch upload drag-drop + auto preview ────────────────
    const BATCH_COLUMNS = [
        'Full Name','First Name','Middle Name','Last Name','Suffix','Sex',
        'Birthdate','Contact Number','Position','Status','Region','Province',
        'Municipality','Barangay','Term Start Date','Term End Date','Committee',
        'Email Address'
    ];

    const fileInput = document.getElementById('officialBatchFile');
    const dropzone  = document.getElementById('officialDropzone');
    const fileLabel = document.getElementById('officialFileName');
    const preview   = document.getElementById('officialBatchPreview');

    function renderPreviewTable(rows) {
        if (!preview) return;
        const thead = `<thead><tr>${BATCH_COLUMNS.map(c => `<th>${c}</th>`).join('')}</tr></thead>`;
        const tbody = `<tbody>${rows.map(row =>
            `<tr>${BATCH_COLUMNS.map((_, i) => `<td>${row[i] ?? ''}</td>`).join('')}</tr>`
        ).join('')}</tbody>`;
        preview.innerHTML = `<div class="batch-preview-wrap"><table class="batch-preview-table">${thead}${tbody}</table></div>`;
        preview.style.display = '';
    }

    function parseCSV(text) {
        const lines = text.trim().split(/\r?\n/);
        const start = lines[0] && /[a-zA-Z]/.test(lines[0]) ? 1 : 0;
        return lines.slice(start).map(line =>
            line.split(/\t|,/).map(cell => cell.replace(/^"|"$/g, '').trim())
        ).filter(row => row.some(c => c));
    }

    if (fileInput && dropzone) {
        fileInput.addEventListener('change', () => {
            const file = fileInput.files[0];
            if (fileLabel) fileLabel.textContent = file?.name || 'No file selected';
            if (file) {
                const reader = new FileReader();
                reader.onload = e => renderPreviewTable(parseCSV(e.target.result));
                reader.readAsText(file);
            }
        });

        dropzone.addEventListener('click', e => {
            if (!e.target.classList.contains('dropzone-browse')) fileInput.click();
        });

        dropzone.addEventListener('dragover', e => {
            e.preventDefault();
            dropzone.classList.add('drag-over');
        });

        dropzone.addEventListener('dragleave', () => dropzone.classList.remove('drag-over'));

        dropzone.addEventListener('drop', e => {
            e.preventDefault();
            dropzone.classList.remove('drag-over');
            const file = e.dataTransfer.files[0];
            if (file) {
                const dt = new DataTransfer();
                dt.items.add(file);
                fileInput.files = dt.files;
                if (fileLabel) fileLabel.textContent = file.name;
                const reader = new FileReader();
                reader.onload = ev => renderPreviewTable(parseCSV(ev.target.result));
                reader.readAsText(file);
            }
        });
    }
});
