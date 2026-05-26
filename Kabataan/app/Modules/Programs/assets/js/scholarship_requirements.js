/**
 * Scholarship Requirements — view attachment (download before open), upload, resubmit (frontend only)
 */
(function () {
    'use strict';

    const MAX_BYTES = 5 * 1024 * 1024;
    const PDF_MIME = 'application/pdf';

    const modal = document.getElementById('schAttachmentModal');
    const docNameEl = document.getElementById('schAttDocName');
    const statusBadgeEl = document.getElementById('schAttStatusBadge');
    const remarksEl = document.getElementById('schAttRemarks');
    const downloadBtn = document.getElementById('schAttDownloadBtn');
    const openBtn = document.getElementById('schAttOpenBtn');

    let currentBlobUrl = null;
    let hasDownloaded = false;
    let currentFileName = '';

    function statusLabel(status) {
        if (status === 'verified') return { text: '✔ Verified', class: 'sch-req-badge-verified' };
        if (status === 'pending') return { text: '⏳ Pending', class: 'sch-req-badge-pending' };
        return { text: '✖ Rejected', class: 'sch-req-badge-rejected' };
    }

    function revokeBlob() {
        if (currentBlobUrl) {
            URL.revokeObjectURL(currentBlobUrl);
            currentBlobUrl = null;
        }
    }

    function createDemoPdfBlob(fileName) {
        const content = '%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj 2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj 3 0 obj<</Type/Page/MediaBox[0 0 612 792]/Parent 2 0 R>>endobj\nxref\n0 4\ntrailer<</Size 4/Root 1 0 R>>\nstartxref\n0\n%%EOF';
        return new Blob([content], { type: 'application/pdf' });
    }

    function getRowData(reqId) {
        const row = document.querySelector('.sch-req-row[data-req-id="' + reqId + '"]');
        if (!row) return null;
        return {
            id: reqId,
            name: row.querySelector('.sch-req-name')?.textContent?.trim() || 'Document',
            status: row.dataset.status || 'pending',
            file: row.dataset.file || 'document.pdf',
            remarks: row.dataset.remarks || '',
        };
    }

    function openAttachmentModal(reqId) {
        const data = getRowData(reqId);
        if (!data || !data.file) return;

        revokeBlob();
        hasDownloaded = false;
        currentFileName = data.file;

        const blob = createDemoPdfBlob(data.file);
        currentBlobUrl = URL.createObjectURL(blob);

        if (docNameEl) docNameEl.textContent = data.name;
        if (statusBadgeEl) {
            const s = statusLabel(data.status);
            statusBadgeEl.textContent = s.text;
            statusBadgeEl.className = 'sch-req-badge ' + s.class;
        }
        if (remarksEl) {
            if (data.remarks) {
                remarksEl.textContent = 'Remarks: ' + data.remarks;
                remarksEl.hidden = false;
            } else {
                remarksEl.hidden = true;
            }
        }
        if (openBtn) {
            openBtn.disabled = true;
        }

        if (modal) modal.hidden = false;
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        if (modal) modal.hidden = true;
        document.body.style.overflow = '';
        revokeBlob();
        hasDownloaded = false;
    }

    document.querySelectorAll('[data-view-attachment]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            openAttachmentModal(btn.dataset.reqId);
        });
    });

    document.querySelectorAll('[data-show-remarks]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const row = btn.closest('.sch-req-row');
            if (row) openAttachmentModal(row.dataset.reqId);
        });
    });

    if (downloadBtn) {
        downloadBtn.addEventListener('click', function () {
            if (!currentBlobUrl) return;
            const a = document.createElement('a');
            a.href = currentBlobUrl;
            a.download = currentFileName || 'attachment.pdf';
            document.body.appendChild(a);
            a.click();
            a.remove();
            hasDownloaded = true;
            if (openBtn) openBtn.disabled = false;
        });
    }

    if (openBtn) {
        openBtn.addEventListener('click', function () {
            if (!hasDownloaded || !currentBlobUrl) {
                alert('Please download the attachment first before opening.');
                return;
            }
            window.open(currentBlobUrl, '_blank', 'noopener,noreferrer');
        });
    }

    modal?.querySelectorAll('[data-close-modal]').forEach(function (el) {
        el.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal && !modal.hidden) closeModal();
    });

    function updateRowAfterUpload(row, fileName) {
        row.dataset.file = fileName;
        row.dataset.status = 'pending';

        const badge = row.querySelector('[data-status-badge]');
        if (badge) {
            badge.textContent = '⏳ Pending';
            badge.className = 'sch-req-badge sch-req-badge-pending';
        }

        const attachCell = row.querySelector('.sch-req-attach-cell');
        if (attachCell) {
            attachCell.innerHTML = '<button type="button" class="sch-req-btn sch-req-btn-view" data-view-attachment data-req-id="' + row.dataset.reqId + '">View Attachment</button>';
            attachCell.querySelector('[data-view-attachment]').addEventListener('click', function () {
                openAttachmentModal(row.dataset.reqId);
            });
        }

        const actionCell = row.querySelector('.sch-req-action-cell');
        if (actionCell) {
            const reqId = row.dataset.reqId;
            actionCell.innerHTML = '<button type="button" class="sch-req-btn sch-req-btn-upload" data-upload-trigger data-req-id="' + reqId + '"><svg viewBox="0 0 20 20" fill="currentColor" width="14" height="14"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/></svg> Upload</button><input type="file" class="sch-req-file-input" id="file-' + reqId + '" accept="application/pdf,.pdf" hidden>';
            bindUpload(actionCell.querySelector('[data-upload-trigger]'));
        }

        const remarksLink = row.querySelector('.sch-req-remarks-link');
        if (remarksLink) remarksLink.remove();
        row.dataset.remarks = '';
    }

    function bindUpload(trigger) {
        if (!trigger) return;
        const reqId = trigger.dataset.reqId;
        const input = document.getElementById('file-' + reqId);
        const row = document.querySelector('.sch-req-row[data-req-id="' + reqId + '"]');

        trigger.addEventListener('click', function () {
            input?.click();
        });

    }

    document.querySelectorAll('[data-upload-trigger]').forEach(bindUpload);

    document.querySelectorAll('[data-resubmit]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('file-' + btn.dataset.reqId)?.click();
        });
    });

    document.querySelectorAll('.sch-req-file-input').forEach(function (input) {
        const reqId = input.dataset.reqInput || input.id.replace('file-', '');
        const row = document.querySelector('.sch-req-row[data-req-id="' + reqId + '"]');

        input.addEventListener('change', function () {
            const file = this.files[0];
            if (!file || !row) return;

            if (file.type !== PDF_MIME && !file.name.toLowerCase().endsWith('.pdf')) {
                alert('Only PDF files are allowed.');
                this.value = '';
                return;
            }
            if (file.size > MAX_BYTES) {
                alert('File must not exceed 5MB.');
                this.value = '';
                return;
            }

            const wasRejected = row.dataset.status === 'rejected';
            updateRowAfterUpload(row, file.name);
            alert((wasRejected ? 'Document resubmitted.' : 'Document uploaded.') + ' Status is now Pending for review. (Demo — file not saved to server.)');
            this.value = '';
        });
    });
})();
