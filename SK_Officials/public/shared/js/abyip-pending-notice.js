(function () {
    'use strict';

    const DEFAULT_MESSAGE = 'Pending — waiting for SK Federation President to approve your ABYIP.';

    function isPending(gate) {
        return gate && String(gate.status || '').toLowerCase() === 'pending';
    }

    function pendingMessage(gate) {
        return (gate && gate.pending_message) || DEFAULT_MESSAGE;
    }

    function renderNotice(container, gate) {
        if (!container || !isPending(gate)) {
            if (container) {
                container.innerHTML = '';
                container.hidden = true;
            }
            return;
        }

        container.hidden = false;
        container.innerHTML =
            '<div class="abyip-pending-notice" role="status">' +
                '<div class="abyip-pending-notice-icon" aria-hidden="true">' +
                    '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
                        '<circle cx="12" cy="12" r="10"></circle>' +
                        '<polyline points="12 6 12 12 16 14"></polyline>' +
                    '</svg>' +
                '</div>' +
                '<div class="abyip-pending-notice-content">' +
                    '<strong class="abyip-pending-notice-title">ABYIP Pending</strong>' +
                    '<p class="abyip-pending-notice-text">' + escapeHtml(pendingMessage(gate)) + '</p>' +
                '</div>' +
            '</div>';
    }

    function renderEmptyRow(colspan, gate, fallbackMessage) {
        if (isPending(gate)) {
            return (
                '<td colspan="' + colspan + '" class="abyip-pending-empty">' +
                    '<strong>ABYIP Pending</strong>' +
                    escapeHtml(pendingMessage(gate)) +
                '</td>'
            );
        }

        return (
            '<td colspan="' + colspan + '" style="text-align:center;font-size:13px;color:#6b7280;padding:24px 16px;">' +
                escapeHtml(fallbackMessage || 'No records found.') +
            '</td>'
        );
    }

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    window.SkAbyipNotice = {
        isPending: isPending,
        pendingMessage: pendingMessage,
        renderNotice: renderNotice,
        renderEmptyRow: renderEmptyRow,
    };
})();
