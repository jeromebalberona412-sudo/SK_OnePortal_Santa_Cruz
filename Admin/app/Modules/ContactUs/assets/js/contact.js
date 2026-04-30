/* ============================================================
   SK OnePortal — Manage Contact (pure event-listener CRUD)
   No global functions — all wired via addEventListener
   ============================================================ */

(function () {
    'use strict';

    /* ── State ─────────────────────────────────────────── */
    var contacts       = [];
    var deleteTargetId = null;

    /* ── Helpers ───────────────────────────────────────── */
    function uid() {
        return Date.now().toString(36) + Math.random().toString(36).slice(2, 8);
    }

    function esc(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function load() {
        try {
            var raw = localStorage.getItem('sk_contacts');
            contacts = raw ? JSON.parse(raw) : defaults();
        } catch (e) {
            contacts = defaults();
        }
    }

    function persist() {
        localStorage.setItem('sk_contacts', JSON.stringify(contacts));
    }

    function defaults() {
        return [
            { id: uid(), type: 'Address',      label: 'Main Office',    value: 'Municipal Hall, Santa Cruz, Laguna, Philippines' },
            { id: uid(), type: 'Phone',        label: 'Contact Number', value: '09081137315' },
            { id: uid(), type: 'Email',        label: 'Official Email', value: 'skoneportal@gmail.com' },
            { id: uid(), type: 'Office Hours', label: 'Working Hours',  value: 'Mon\u2013Fri: 8:00 AM \u2013 5:00 PM' },
        ];
    }

    function findById(id) {
        for (var i = 0; i < contacts.length; i++) {
            if (contacts[i].id === id) return contacts[i];
        }
        return null;
    }

    /* ── Type icon SVG ─────────────────────────────────── */
    var TYPE_ICONS = {
        'Phone':        '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>',
        'Email':        '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>',
        'Address':      '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>',
        'Facebook':     '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>',
        'Website':      '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>',
        'Office Hours': '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>',
        'Other':        '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>',
    };

    var TYPE_BADGE = {
        'Phone':        'badge-phone',
        'Email':        'badge-email',
        'Address':      'badge-address',
        'Facebook':     'badge-facebook',
        'Website':      'badge-website',
        'Office Hours': 'badge-hours',
        'Other':        'badge-other',
    };

    function iconFor(type)  { return TYPE_ICONS[type]  || TYPE_ICONS['Other'];  }
    function badgeFor(type) { return TYPE_BADGE[type]  || 'badge-other'; }

    /* ── Render table ──────────────────────────────────── */
    function render() {
        var tbody = document.getElementById('contactTableBody');
        var empty = document.getElementById('emptyState');
        if (!tbody) return;

        tbody.innerHTML = '';

        if (contacts.length === 0) {
            if (empty) empty.style.display = 'flex';
            return;
        }
        if (empty) empty.style.display = 'none';

        contacts.forEach(function (c) {
            var tr = document.createElement('tr');
            tr.innerHTML =
                '<td><span class="type-text">' + esc(c.type) + '</span></td>' +
                '<td class="td-label">' + esc(c.label) + '</td>' +
                '<td class="td-value">' + esc(c.value) + '</td>' +
                '<td class="td-actions">' +
                    '<button class="row-btn row-btn-edit" data-id="' + c.id + '">Edit</button>' +
                    '<button class="row-btn row-btn-delete" data-id="' + c.id + '">Delete</button>' +
                '</td>';
            tbody.appendChild(tr);
        });
    }

    /* ── Modal helpers ─────────────────────────────────── */
    function showModal(id) {
        var el = document.getElementById(id);
        if (el) { el.style.display = 'flex'; document.body.style.overflow = 'hidden'; }
    }

    function hideModal(id) {
        var el = document.getElementById(id);
        if (el) { el.style.display = 'none'; document.body.style.overflow = ''; }
    }

    function shake(modalId) {
        var c = document.querySelector('#' + modalId + ' .modal-container');
        if (!c) return;
        c.classList.add('modal-shake');
        setTimeout(function () { c.classList.remove('modal-shake'); }, 500);
    }

    /* ── Toast — matches account module ───────────────────── */
    var _toastTimer = null;
    function toast(msg, type) {
        var idMap  = { add: 'contactToastAdd', edit: 'contactToastEdit', delete: 'contactToastDelete' };
        var msgMap = { add: 'contactToastAddMsg', edit: 'contactToastEditMsg', delete: 'contactToastDeleteMsg' };
        var toastId = idMap[type]  || 'contactToastAdd';
        var msgId   = msgMap[type] || 'contactToastAddMsg';

        ['contactToastAdd', 'contactToastEdit', 'contactToastDelete'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.classList.remove('show');
        });

        var toastEl = document.getElementById(toastId);
        var msgEl   = document.getElementById(msgId);
        if (!toastEl) return;
        if (msgEl) msgEl.textContent = msg;
        toastEl.classList.add('show');
        if (_toastTimer) clearTimeout(_toastTimer);
        _toastTimer = setTimeout(function () { toastEl.classList.remove('show'); }, 3500);
    }

    /* ── Field validation helpers ──────────────────────────── */
    function setError(inputId, errorId, show) {
        var input = document.getElementById(inputId);
        var err   = document.getElementById(errorId);
        if (input) {
            if (show) input.classList.add('input-error');
            else      input.classList.remove('input-error');
        }
        if (err) {
            if (show) err.classList.add('visible');
            else      err.classList.remove('visible');
        }
    }

    function clearErrors(ids) {
        ids.forEach(function (pair) { setError(pair[0], pair[1], false); });
    }

    /* ── Validation helper ─────────────────────────────── */
    function val(id) {
        var el = document.getElementById(id);
        return el ? el.value.trim() : '';
    }

    /* ── Wire everything up after DOM ready ────────────── */
    document.addEventListener('DOMContentLoaded', function () {
        load();
        render();

        /* ── Dynamic value field on type select ── */
        function wireTypeSelect(selectId, dynamicGroupId, valueInputId, labelInputId) {
            var sel   = document.getElementById(selectId);
            var group = document.getElementById(dynamicGroupId);
            var input = document.getElementById(valueInputId);
            var lbl   = document.getElementById(labelInputId);
            if (!sel || !group || !input) return;

            // Placeholder map per type
            var placeholders = {
                'Phone':        'e.g. 09081137315',
                'Email':        'e.g. skoneportal@gmail.com',
                'Address':      'e.g. Municipal Hall, Santa Cruz, Laguna',
                'Facebook':     'e.g. https://facebook.com/skoneportal',
                'Website':      'e.g. https://skoneportal.gov.ph',
                'Office Hours': 'e.g. Mon\u2013Fri: 8:00 AM \u2013 5:00 PM',
                'Other':        'Enter contact value',
            };

            var labelDefaults = {
                'Phone':        'Contact Number',
                'Email':        'Official Email',
                'Address':      'Main Office',
                'Facebook':     'Facebook Page',
                'Website':      'Official Website',
                'Office Hours': 'Working Hours',
                'Other':        'Other',
            };

            sel.addEventListener('change', function () {
                var t = sel.value;
                if (t) {
                    input.placeholder = placeholders[t] || 'Enter value';
                    // Auto-fill label only if empty
                    if (lbl && !lbl.value.trim()) {
                        lbl.value = labelDefaults[t] || '';
                    }
                    group.classList.add('visible');
                    setTimeout(function () { input.focus(); }, 80);
                } else {
                    group.classList.remove('visible');
                }
            });
        }

        wireTypeSelect('addType', 'addValueGroup', 'addValue', 'addLabel');
        wireTypeSelect('editType', 'editValueGroup', 'editValue', 'editLabel');

        /* ── Maximize / Restore — exact same pattern as Archived/Deleted modules ── */
        function wireMaximize(toggleBtnId, overlayId, boxId) {
            var toggleBtn = document.getElementById(toggleBtnId);
            var overlay   = document.getElementById(overlayId);
            var box       = document.getElementById(boxId);
            if (!toggleBtn || !overlay || !box) return;

            toggleBtn.textContent = '□';

            toggleBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                var isMax = !box.classList.contains('contact-maximized');
                overlay.classList.toggle('contact-maximized', isMax);
                box.classList.toggle('contact-maximized', isMax);
                toggleBtn.textContent = isMax ? '⧉' : '□';
            });
        }

        wireMaximize('btnMaxAdd',  'addContactModal',  'addModalContainer');
        wireMaximize('btnMaxEdit', 'editContactModal', 'editModalContainer');

        /* ── ADD ── */
        var btnAdd = document.getElementById('btnAddContact');
        if (btnAdd) btnAdd.addEventListener('click', function () {
            document.getElementById('addContactForm').reset();
            showModal('addContactModal');
            setTimeout(function () {
                var t = document.getElementById('addType');
                if (t) t.focus();
            }, 120);
        });

        var btnCloseAdd   = document.getElementById('btnCloseAdd');
        var btnCancelAdd  = document.getElementById('btnCancelAdd');
        var btnSubmitAdd  = document.getElementById('btnSubmitAdd');

        function closeAdd() {
            hideModal('addContactModal');
            document.getElementById('addContactForm').reset();
            var avg = document.getElementById('addValueGroup');
            if (avg) avg.classList.remove('visible');
            clearErrors([['addType','addTypeError'],['addLabel','addLabelError'],['addValue','addValueError']]);
            // Reset maximize state
            var overlay = document.getElementById('addContactModal');
            var box = document.getElementById('addModalContainer');
            var tb = document.getElementById('btnMaxAdd');
            if (overlay) overlay.classList.remove('contact-maximized');
            if (box) box.classList.remove('contact-maximized');
            if (tb) tb.textContent = '□';
        }

        if (btnCloseAdd)  btnCloseAdd.addEventListener('click',  closeAdd);
        if (btnCancelAdd) btnCancelAdd.addEventListener('click', closeAdd);

        if (btnSubmitAdd) btnSubmitAdd.addEventListener('click', function () {
            var type  = val('addType');
            var label = val('addLabel');
            var value = val('addValue');

            // Clear previous errors
            clearErrors([['addType','addTypeError'],['addLabel','addLabelError'],['addValue','addValueError']]);

            var hasError = false;
            if (!type)  { setError('addType',  'addTypeError',  true); hasError = true; }
            if (!label) { setError('addLabel', 'addLabelError', true); hasError = true; }
            if (!value) { setError('addValue', 'addValueError', true); hasError = true; }
            if (hasError) { shake('addContactModal'); return; }

            contacts.push({ id: uid(), type: type, label: label, value: value });
            persist();
            render();
            closeAdd();
            toast('Contact added successfully!', 'add');
        });

        /* ── EDIT ── */
        var btnCloseEdit  = document.getElementById('btnCloseEdit');
        var btnCancelEdit = document.getElementById('btnCancelEdit');
        var btnSubmitEdit = document.getElementById('btnSubmitEdit');

        function closeEdit() {
            hideModal('editContactModal');
            // Reset maximize state
            var overlay = document.getElementById('editContactModal');
            var box = document.getElementById('editModalContainer');
            var tb = document.getElementById('btnMaxEdit');
            if (overlay) overlay.classList.remove('contact-maximized');
            if (box) box.classList.remove('contact-maximized');
            if (tb) tb.textContent = '□';
        }

        if (btnCloseEdit)  btnCloseEdit.addEventListener('click',  closeEdit);
        if (btnCancelEdit) btnCancelEdit.addEventListener('click', closeEdit);

        if (btnSubmitEdit) btnSubmitEdit.addEventListener('click', function () {
            var id    = val('editContactId');
            var type  = val('editType');
            var label = val('editLabel');
            var value = val('editValue');

            clearErrors([['editLabel','editLabelError'],['editValue','editValueError']]);

            var hasError = false;
            if (!label) { setError('editLabel', 'editLabelError', true); hasError = true; }
            if (!value) { setError('editValue', 'editValueError', true); hasError = true; }
            if (hasError) { shake('editContactModal'); return; }

            for (var i = 0; i < contacts.length; i++) {
                if (contacts[i].id === id) {
                    contacts[i] = { id: id, type: type, label: label, value: value };
                    break;
                }
            }
            persist();
            render();
            closeEdit();
            toast('Contact updated successfully!', 'edit');
        });

        /* ── DELETE ── */
        var btnCancelDelete = document.getElementById('btnCancelDelete');
        var btnSubmitDelete = document.getElementById('btnSubmitDelete');

        function closeDelete() {
            deleteTargetId = null;
            hideModal('deleteContactModal');
        }

        if (btnCancelDelete) btnCancelDelete.addEventListener('click', closeDelete);

        if (btnSubmitDelete) btnSubmitDelete.addEventListener('click', function () {
            if (!deleteTargetId) return;
            contacts = contacts.filter(function (c) { return c.id !== deleteTargetId; });
            persist();
            render();
            closeDelete();
            toast('Contact deleted successfully!', 'delete');
        });

        /* ── Row buttons (event delegation on tbody) ── */
        var tbody = document.getElementById('contactTableBody');
        if (tbody) {
            tbody.addEventListener('click', function (e) {
                var btn = e.target.closest('button');
                if (!btn) return;
                var id = btn.getAttribute('data-id');
                if (!id) return;

                if (btn.classList.contains('row-btn-edit')) {
                    var c = findById(id);
                    if (!c) return;
                    document.getElementById('editContactId').value = c.id;
                    document.getElementById('editType').value      = c.type;
                    document.getElementById('editLabel').value     = c.label;
                    document.getElementById('editValue').value     = c.value;
                    // Show the dynamic value group since type is already set
                    var evg = document.getElementById('editValueGroup');
                    if (evg) evg.classList.add('visible');
                    showModal('editContactModal');
                    setTimeout(function () {
                        var lbl = document.getElementById('editLabel');
                        if (lbl) lbl.focus();
                    }, 120);
                }

                if (btn.classList.contains('row-btn-delete')) {
                    var c2 = findById(id);
                    if (!c2) return;
                    deleteTargetId = id;
                    var lbl2 = document.getElementById('deleteContactLabel');
                    if (lbl2) lbl2.textContent = '"' + c2.label + '"';
                    showModal('deleteContactModal');
                }
            });
        }

        /* ── Backdrop click closes modals ── */
        document.addEventListener('click', function (e) {
            ['addContactModal', 'editContactModal', 'deleteContactModal'].forEach(function (id) {
                var el = document.getElementById(id);
                if (el && e.target === el) {
                    hideModal(id);
                    if (id === 'deleteContactModal') deleteTargetId = null;
                }
            });
        });

        /* ── Escape key closes modals ── */
        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Escape') return;
            ['editContactModal', 'addContactModal', 'deleteContactModal'].forEach(function (id) {
                var el = document.getElementById(id);
                if (el && el.style.display === 'flex') {
                    hideModal(id);
                    if (id === 'deleteContactModal') deleteTargetId = null;
                }
            });
        });
    });

    /* ── Public API for homepage ───────────────────────── */
    window.getContactData = function () {
        try {
            var raw = localStorage.getItem('sk_contacts');
            return raw ? JSON.parse(raw) : defaults();
        } catch (e) { return defaults(); }
    };

}());
