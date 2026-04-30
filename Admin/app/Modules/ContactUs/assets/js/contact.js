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

        contacts.forEach(function (c, i) {
            var tr = document.createElement('tr');
            tr.innerHTML =
                '<td class="td-num">' + (i + 1) + '</td>' +
                '<td><span class="type-badge ' + badgeFor(c.type) + '">' + iconFor(c.type) + ' ' + esc(c.type) + '</span></td>' +
                '<td class="td-label">' + esc(c.label) + '</td>' +
                '<td class="td-value">' + esc(c.value) + '</td>' +
                '<td class="td-actions">' +
                    '<button class="row-btn row-btn-edit" data-id="' + c.id + '">' +
                        '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>' +
                        ' Edit' +
                    '</button>' +
                    '<button class="row-btn row-btn-delete" data-id="' + c.id + '">' +
                        '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>' +
                        ' Delete' +
                    '</button>' +
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

    /* ── Toast ─────────────────────────────────────────── */
    function toast(msg) {
        var el  = document.getElementById('successToast');
        var txt = document.getElementById('toastMessage');
        if (!el || !txt) return;
        txt.textContent = msg;
        el.style.display = 'flex';
        clearTimeout(el._t);
        el._t = setTimeout(function () { el.style.display = 'none'; }, 3000);
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
        }

        if (btnCloseAdd)  btnCloseAdd.addEventListener('click',  closeAdd);
        if (btnCancelAdd) btnCancelAdd.addEventListener('click', closeAdd);

        if (btnSubmitAdd) btnSubmitAdd.addEventListener('click', function () {
            var type  = val('addType');
            var label = val('addLabel');
            var value = val('addValue');
            if (!type || !label || !value) { shake('addContactModal'); return; }
            contacts.push({ id: uid(), type: type, label: label, value: value });
            persist();
            render();
            closeAdd();
            toast('Contact added successfully');
        });

        /* ── EDIT ── */
        var btnCloseEdit  = document.getElementById('btnCloseEdit');
        var btnCancelEdit = document.getElementById('btnCancelEdit');
        var btnSubmitEdit = document.getElementById('btnSubmitEdit');

        function closeEdit() { hideModal('editContactModal'); }

        if (btnCloseEdit)  btnCloseEdit.addEventListener('click',  closeEdit);
        if (btnCancelEdit) btnCancelEdit.addEventListener('click', closeEdit);

        if (btnSubmitEdit) btnSubmitEdit.addEventListener('click', function () {
            var id    = val('editContactId');
            var type  = val('editType');
            var label = val('editLabel');
            var value = val('editValue');
            if (!type || !label || !value) { shake('editContactModal'); return; }
            for (var i = 0; i < contacts.length; i++) {
                if (contacts[i].id === id) {
                    contacts[i] = { id: id, type: type, label: label, value: value };
                    break;
                }
            }
            persist();
            render();
            closeEdit();
            toast('Contact updated successfully');
        });

        /* ── DELETE ── */
        var btnCloseDelete  = document.getElementById('btnCloseDelete');
        var btnCancelDelete = document.getElementById('btnCancelDelete');
        var btnSubmitDelete = document.getElementById('btnSubmitDelete');

        function closeDelete() {
            deleteTargetId = null;
            hideModal('deleteContactModal');
        }

        if (btnCloseDelete)  btnCloseDelete.addEventListener('click',  closeDelete);
        if (btnCancelDelete) btnCancelDelete.addEventListener('click', closeDelete);

        if (btnSubmitDelete) btnSubmitDelete.addEventListener('click', function () {
            if (!deleteTargetId) return;
            contacts = contacts.filter(function (c) { return c.id !== deleteTargetId; });
            persist();
            render();
            closeDelete();
            toast('Contact deleted successfully');
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
