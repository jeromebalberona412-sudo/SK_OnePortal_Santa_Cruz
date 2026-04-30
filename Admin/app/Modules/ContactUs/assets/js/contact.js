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
            { id: uid(), label: 'Main Office',    value: 'Municipal Hall, Santa Cruz, Laguna, Philippines' },
            { id: uid(), label: 'Contact Number', value: '09081137315' },
            { id: uid(), label: 'Official Email', value: 'skoneportal@gmail.com' },
            { id: uid(), label: 'Working Hours',  value: 'Mon\u2013Fri: 8:00 AM \u2013 5:00 PM' },
        ];
    }

    function findById(id) {
        for (var i = 0; i < contacts.length; i++) {
            if (contacts[i].id === id) return contacts[i];
        }
        return null;
    }

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

    /* ── Toast ─────────────────────────────────────────── */
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

    /* ── Field validation helpers ──────────────────────── */
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

    function val(id) {
        var el = document.getElementById(id);
        return el ? el.value.trim() : '';
    }

    /* ── Wire everything up after DOM ready ────────────── */
    document.addEventListener('DOMContentLoaded', function () {
        load();
        render();

        /* ── Maximize / Restore ── */
        function wireMaximize(toggleBtnId, overlayId, boxId) {
            var toggleBtn = document.getElementById(toggleBtnId);
            var overlay   = document.getElementById(overlayId);
            var box       = document.getElementById(boxId);
            if (!toggleBtn || !overlay || !box) return;

            toggleBtn.textContent = '\u25A1';

            toggleBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                var isMax = !box.classList.contains('contact-maximized');
                if (isMax) {
                    overlay.classList.add('contact-maximized');
                    overlay.style.padding = '0';
                    box.classList.add('contact-maximized');
                    toggleBtn.textContent = '\u29C9';
                } else {
                    overlay.classList.remove('contact-maximized');
                    overlay.style.padding = '';
                    box.classList.remove('contact-maximized');
                    toggleBtn.textContent = '\u25A1';
                }
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
                var lbl = document.getElementById('addLabel');
                if (lbl) lbl.focus();
            }, 120);
        });

        var btnCloseAdd  = document.getElementById('btnCloseAdd');
        var btnCancelAdd = document.getElementById('btnCancelAdd');
        var btnSubmitAdd = document.getElementById('btnSubmitAdd');

        function closeAdd() {
            hideModal('addContactModal');
            document.getElementById('addContactForm').reset();
            clearErrors([['addLabel','addLabelError'],['addValue','addValueError']]);
            var overlay = document.getElementById('addContactModal');
            var box = document.getElementById('addModalContainer');
            var tb  = document.getElementById('btnMaxAdd');
            if (overlay) { overlay.classList.remove('contact-maximized'); overlay.style.padding = ''; }
            if (box) box.classList.remove('contact-maximized');
            if (tb)  tb.textContent = '\u25A1';
        }

        if (btnCloseAdd)  btnCloseAdd.addEventListener('click',  closeAdd);
        if (btnCancelAdd) btnCancelAdd.addEventListener('click', closeAdd);

        if (btnSubmitAdd) btnSubmitAdd.addEventListener('click', function () {
            var label = val('addLabel');
            var value = val('addValue');

            clearErrors([['addLabel','addLabelError'],['addValue','addValueError']]);

            var hasError = false;
            if (!label) { setError('addLabel', 'addLabelError', true); hasError = true; }
            if (!value) { setError('addValue', 'addValueError', true); hasError = true; }
            if (hasError) { shake('addContactModal'); return; }

            contacts.push({ id: uid(), label: label, value: value });
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
            var overlay = document.getElementById('editContactModal');
            var box = document.getElementById('editModalContainer');
            var tb  = document.getElementById('btnMaxEdit');
            if (overlay) { overlay.classList.remove('contact-maximized'); overlay.style.padding = ''; }
            if (box) box.classList.remove('contact-maximized');
            if (tb)  tb.textContent = '\u25A1';
        }

        if (btnCloseEdit)  btnCloseEdit.addEventListener('click',  closeEdit);
        if (btnCancelEdit) btnCancelEdit.addEventListener('click', closeEdit);

        if (btnSubmitEdit) btnSubmitEdit.addEventListener('click', function () {
            var id    = val('editContactId');
            var label = val('editLabel');
            var value = val('editValue');

            clearErrors([['editLabel','editLabelError'],['editValue','editValueError']]);

            var hasError = false;
            if (!label) { setError('editLabel', 'editLabelError', true); hasError = true; }
            if (!value) { setError('editValue', 'editValueError', true); hasError = true; }
            if (hasError) { shake('editContactModal'); return; }

            for (var i = 0; i < contacts.length; i++) {
                if (contacts[i].id === id) {
                    contacts[i] = { id: id, label: label, value: value };
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
