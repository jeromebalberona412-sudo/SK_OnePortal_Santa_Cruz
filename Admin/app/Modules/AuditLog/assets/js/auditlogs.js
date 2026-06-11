document.addEventListener('DOMContentLoaded', function () {
    const shell = document.getElementById('auditLogApp');
    if (!shell) {
        return;
    }

    let routes = {};
    try {
        routes = JSON.parse(shell.dataset.auditRoutes || '{}');
    } catch (error) {
        routes = {};
    }

    const state = {
        page: 1,
        perPage: 15,
        rows: [],
        meta: { total: 0, from: 0, to: 0, last_page: 1 },
        debounceTimer: null,
    };

    const els = {
        search: document.getElementById('auditSearch'),
        dateFrom: document.getElementById('auditDateFrom'),
        dateTo: document.getElementById('auditDateTo'),
        role: document.getElementById('auditRole'),
        barangay: document.getElementById('auditBarangay'),
        eventType: document.getElementById('auditEventType'),
        perPage: document.getElementById('auditPerPage'),
        tableBody: document.getElementById('auditLogsTableBody'),
        tableSubtitle: document.getElementById('auditTableSubtitle'),
        prevBtn: document.getElementById('auditPrevBtn'),
        nextBtn: document.getElementById('auditNextBtn'),
        pageNumbers: document.getElementById('auditPageNumbers'),
        pageInfo: document.getElementById('auditPageInfo'),
        modal: document.getElementById('auditDetailsModal'),
        modalSubtitle: document.getElementById('auditModalSubtitle'),
        detailGrid: document.getElementById('auditDetailGrid'),
    };

    const navAuditBtn = document.querySelector('.nav-link.auditlogs-btn');
    if (navAuditBtn) {
        navAuditBtn.classList.add('active');
    }

    function getFilters() {
        return {
            search: els.search?.value.trim() || '',
            date_from: els.dateFrom?.value || '',
            date_to: els.dateTo?.value || '',
            role: els.role?.value || '',
            barangay_id: els.barangay?.value || '',
            event_type: els.eventType?.value || '',
            page: String(state.page),
            per_page: String(state.perPage),
        };
    }

    function buildQuery(filters) {
        const params = new URLSearchParams();
        Object.entries(filters).forEach(function ([key, value]) {
            if (value !== '' && value !== null && value !== undefined) {
                params.set(key, value);
            }
        });
        return params.toString();
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function isAdminRole(row) {
        const roleKey = String(row.role_key || '').toLowerCase();
        return roleKey === 'admin' || roleKey === 'super_admin' || row.role === 'Admin';
    }

    function renderRows(rows) {
        if (!els.tableBody) {
            return;
        }

        if (!rows.length) {
            els.tableBody.innerHTML = '<tr><td colspan="7" class="audit-empty-row">No matching audit logs found.</td></tr>';
            return;
        }

        els.tableBody.innerHTML = rows.map(function (row, index) {
            return `
                <tr>
                    <td class="cell-primary">${escapeHtml(row.created_date)}</td>
                    <td>${escapeHtml(row.created_time)}</td>
                    <td class="audit-cell-email">${escapeHtml(row.user_email || '-')}</td>
                    <td>${escapeHtml(row.role)}</td>
                    <td>${escapeHtml(row.event_type)}</td>
                    <td>${escapeHtml(row.ip_address)}</td>
                    <td>
                        <button type="button" class="audit-details-btn" data-row-index="${index}">View</button>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function renderPagination(meta) {
        if (els.tableSubtitle) {
            els.tableSubtitle.textContent = meta.total
                ? `Showing ${meta.from}-${meta.to} of ${meta.total} audit logs`
                : 'No matching audit logs';
        }

        if (els.pageInfo) {
            els.pageInfo.textContent = `Page ${meta.current_page} of ${meta.last_page}`;
        }

        if (els.prevBtn) {
            els.prevBtn.disabled = meta.current_page <= 1;
        }

        if (els.nextBtn) {
            els.nextBtn.disabled = meta.current_page >= meta.last_page;
        }

        if (!els.pageNumbers) {
            return;
        }

        const current = meta.current_page;
        const last = meta.last_page;
        const start = Math.max(1, current - 2);
        const end = Math.min(last, current + 2);
        let html = '';

        for (let page = start; page <= end; page += 1) {
            html += `<button type="button" class="audit-page-number ${page === current ? 'is-active' : ''}" data-page="${page}">${page}</button>`;
        }

        els.pageNumbers.innerHTML = html;
    }

    function updateStatCards(stats) {
        Object.entries(stats || {}).forEach(function ([key, value]) {
            const targets = document.querySelectorAll('.stat-card');
            targets.forEach(function (targetCard) {
                const label = targetCard.querySelector('.stat-card-label');
                if (!label) {
                    return;
                }

                const labelMap = {
                    'Total Audit Logs': 'total_logs',
                    "Today's Activities": 'today_activities',
                    'Security Events': 'security_events',
                    'Active Users Logged Today': 'active_users_today',
                };

                if (labelMap[label.textContent.trim()] === key) {
                    const valueEl = targetCard.querySelector('.stat-card-value');
                    if (valueEl) {
                        valueEl.textContent = Number(value).toLocaleString();
                    }
                }
            });
        });
    }

    async function fetchLogs() {
        if (!routes.data) {
            return;
        }

        try {
            const response = await fetch(`${routes.data}?${buildQuery(getFilters())}`, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                throw new Error('Failed to load audit logs');
            }

            const payload = await response.json();
            state.rows = payload.data || [];
            state.meta = payload.meta || state.meta;
            renderRows(state.rows);
            renderPagination(state.meta);
        } catch (error) {
            if (els.tableSubtitle) {
                els.tableSubtitle.textContent = 'Unable to load audit logs. Please try again.';
            }
            if (els.tableBody) {
                els.tableBody.innerHTML = '';
            }
        }
    }

    async function refreshStats() {
        if (!routes.stats) {
            return;
        }

        try {
            const response = await fetch(routes.stats, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (!response.ok) {
                return;
            }

            const statsPayload = await response.json();
            if (statsPayload?.data) {
                updateStatCards(statsPayload.data);
            }
        } catch (error) {
            // Keep server-rendered stats when refresh fails.
        }
    }

    function openDetailsModal(row) {
        if (!els.modal || !row) {
            return;
        }

        const detailFields = [
            ['Date', row.created_date],
            ['Time', row.created_time],
            ['Email', row.user_email || '-'],
            ['Role', row.role],
            ['Event Type', row.event_type],
            ['IP Address', row.ip_address],
        ];

        if (!isAdminRole(row) && row.barangay && row.barangay !== '-') {
            detailFields.splice(4, 0, ['Barangay', row.barangay]);
        }

        if (els.modalSubtitle) {
            els.modalSubtitle.textContent = row.summary || 'Activity details';
        }

        if (els.detailGrid) {
            els.detailGrid.innerHTML = detailFields.map(function ([label, value]) {
                return `
                    <div class="audit-detail-item">
                        <span>${escapeHtml(label)}</span>
                        <strong>${escapeHtml(value || '-')}</strong>
                    </div>
                `;
            }).join('');
        }

        els.modal.hidden = false;
        document.body.style.overflow = 'hidden';
    }

    function closeDetailsModal() {
        if (!els.modal) {
            return;
        }

        els.modal.hidden = true;
        document.body.style.overflow = '';
    }

    els.perPage?.addEventListener('change', function () {
        state.perPage = Number(els.perPage.value || 15);
        state.page = 1;
        fetchLogs();
    });

    els.search?.addEventListener('input', function () {
        window.clearTimeout(state.debounceTimer);
        state.debounceTimer = window.setTimeout(function () {
            state.page = 1;
            fetchLogs();
        }, 350);
    });

    [
        els.dateFrom,
        els.dateTo,
        els.role,
        els.barangay,
        els.eventType,
    ].forEach(function (input) {
        input?.addEventListener('change', function () {
            state.page = 1;
            fetchLogs();
        });
    });

    els.prevBtn?.addEventListener('click', function () {
        if (state.page > 1) {
            state.page -= 1;
            fetchLogs();
        }
    });

    els.nextBtn?.addEventListener('click', function () {
        if (state.page < state.meta.last_page) {
            state.page += 1;
            fetchLogs();
        }
    });

    els.pageNumbers?.addEventListener('click', function (event) {
        const button = event.target.closest('[data-page]');
        if (!button) {
            return;
        }

        state.page = Number(button.dataset.page || 1);
        fetchLogs();
    });

    els.tableBody?.addEventListener('click', function (event) {
        const button = event.target.closest('[data-row-index]');
        if (!button) {
            return;
        }

        const index = Number(button.dataset.rowIndex);
        openDetailsModal(state.rows[index]);
    });

    document.querySelectorAll('[data-close-modal]').forEach(function (element) {
        element.addEventListener('click', closeDetailsModal);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeDetailsModal();
        }
    });

    fetchLogs();
    refreshStats();

    window.setInterval(function () {
        if (!document.hidden) {
            fetchLogs();
            refreshStats();
        }
    }, 20000);
});
