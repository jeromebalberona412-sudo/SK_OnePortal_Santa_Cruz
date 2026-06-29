(function () {
    const config = window.kmConfig || {};
    const records = [];

    const state = { search: '', status: 'all', barangay: 'all', year: 'all' };

    function getFiltered() {
        var q = state.search.trim().toLowerCase();
        return records.filter(function(r) {
            var matchStatus = state.status === 'all' || r.status === state.status;
            var matchBrgy   = state.barangay === 'all' || r.barangay === state.barangay;
            var matchYear = state.year === 'all' || String(r.submittedYear || '') === String(state.year);
            var hay = (r.name + ' ' + r.barangay + ' ' + r.focus + ' ' + r.youthClassification + ' ' + (r.respondentNumber || '') + ' ' + (r.purokZone || '')).toLowerCase();
            var matchSearch = !q || hay.includes(q);
            return matchStatus && matchBrgy && matchSearch && matchYear;
        });
    }

    function updateSummary() {
        // Stats cards removed from index page.
    }

    function populateBrgyFilter() {
        // Barangays are now hardcoded in the HTML, so this function is no longer needed
        // but kept for compatibility
    }

    var statusLabel = { active: 'Active', moderate: 'Moderate', inactive: 'Inactive' };

    function renderBrgyCards() {
        var container = document.getElementById('km-brgy-cards');
        var empty = document.getElementById('km-empty');
        var countEl = document.getElementById('km-result-count');
        if (!container) return;

        var filtered = getFiltered();
        if (countEl) countEl.textContent = filtered.length + ' record' + (filtered.length !== 1 ? 's' : '');

        // Define all 26 barangays
        var allBrgys = [
            'Alipit', 'Bagumbayan', 'Bubukal', 'Calios', 'Duhat', 'Gatid', 'Jasaan', 
            'Labuin', 'Malinao', 'Oogong', 'Pagsawitan', 'Palasan', 'Patimbao',
            'Poblacion I', 'Poblacion II', 'Poblacion III', 'Poblacion IV', 'Poblacion V',
            'San Jose', 'San Juan', 'San Pablo Norte', 'San Pablo Sur',
            'Santisima Cruz', 'Santo Angel Central', 'Santo Angel Norte', 'Santo Angel Sur'
        ];

        // Filter barangays based on selected barangay filter
        var brgysToDisplay = state.barangay === 'all' ? allBrgys : [state.barangay];

        if (empty) empty.hidden = true;

        var today = new Date();
        var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

        container.innerHTML = '<div class="km-brgy-grid">' + brgysToDisplay.map(function(brgy) {
            var members = records.filter(function(r){ return r.barangay === brgy; });
            var total    = members.length;
            var active   = members.filter(function(r){ return r.status === 'active'; }).length;
            var moderate = members.filter(function(r){ return r.status === 'moderate'; }).length;
            var inactive = members.filter(function(r){ return r.status === 'inactive'; }).length;
            var rate     = total > 0 ? Math.round(((active + moderate) / total) * 100) : 0;
            var lastUpdate = months[today.getMonth()] + ' ' + today.getDate() + ', ' + today.getFullYear();

            var statusClass = total === 0 ? 'inactive' : (rate >= 70 ? 'active' : (rate >= 40 ? 'moderate' : 'inactive'));
            var statusText  = total === 0 ? 'No Data' : (rate >= 70 ? 'Active' : (rate >= 40 ? 'Moderate' : 'Inactive'));

            return '<div class="km-brgy-summary-card">' +
                '<div class="km-bsc-header">' +
                    '<h3 class="km-bsc-name"><i class="fas fa-map-marker-alt"></i> ' + brgy + '</h3>' +
                    '<span class="km-badge ' + statusClass + '">' + statusText + '</span>' +
                '</div>' +
                '<div class="km-bsc-footer">' +
                    '<span class="km-bsc-update"><i class="fas fa-clock"></i> Last update: ' + lastUpdate + '</span>' +
                    '<button class="km-bsc-view-btn" onclick="openBrgyModal(\'' + brgy.replace(/'/g, "\\'") + '\')">' +
                        'View full details <i class="fas fa-arrow-right"></i>' +
                    '</button>' +
                '</div>' +
            '</div>';
        }).join('') + '</div>';
    }

    // ── Barangay detail modal ──
    window.openBrgyModal = function(brgy) {
        var detailBase = (document.querySelector('main.km-main') || {}).dataset?.detailBase || '/kabataan-monitoring';
        var encodedBrgy = encodeURIComponent(brgy);
        window.location.href = detailBase + '/barangay/' + encodedBrgy;
    };

    function initIndex() {
        loadRecords().then(function () {
            updateSummary();
            populateBrgyFilter();
            renderBrgyCards();
        });

        var searchInput = document.getElementById('km-search');
        if (searchInput) {
            searchInput.addEventListener('input', function(e) {
                state.search = e.target.value || '';
                renderBrgyCards();
            });
        }

        var brgyFilter = document.getElementById('km-brgy-filter');
        if (brgyFilter) {
            brgyFilter.addEventListener('change', function(e) {
                state.barangay = e.target.value || 'all';
                renderBrgyCards();
            });
        }
    }

    // ── Detail page ──
    var statusLabelDetail = { active: 'Active', moderate: 'Moderate', inactive: 'Inactive', high: 'High', low: 'Low' };

    function metricCard(label, value) {
        return '<article class="km-metric-card"><small>' + label + '</small><strong>' + value + '</strong></article>';
    }

    function renderDetail() {
        var root = document.querySelector('main.km-main');
        if (!root) return;
        var detailBase = root.dataset.detailBase || '/kabataan-monitoring';
        var slug = root.dataset.kabataanSlug || '';
        var profile = records.find(function(r){ return r.slug === slug; });
        var hero = document.getElementById('km-profile-hero');
        var detailGrid = document.getElementById('km-detail-grid');
        var notFound = document.getElementById('km-not-found');
        if (!hero || !detailGrid || !notFound) return;

        if (!profile) { detailGrid.hidden = true; notFound.hidden = false; return; }
        notFound.hidden = true; detailGrid.hidden = false;

        hero.innerHTML = '<div class="km-profile-head">' +
            '<a class="km-back-link" href="' + detailBase + '"><i class="fas fa-arrow-left"></i> Back to list</a>' +
            '<h1>' + profile.name + '</h1>' +
            '<p>' + profile.barangay + ' | ' + profile.age + ' years old | Focus: ' + profile.focus + '</p>' +
            '<div class="km-profile-strip">' +
                '<span class="km-profile-pill">Status: ' + (statusLabelDetail[profile.status] || profile.status) + '</span>' +
                '<span class="km-profile-pill">Attendance: ' + profile.attendance + '</span>' +
                '<span class="km-profile-pill">Last check-in: ' + profile.lastCheckIn + '</span>' +
            '</div></div>';

        var metricGrid = document.getElementById('km-metric-grid');
        var programList = document.getElementById('km-program-list');
        var recoList = document.getElementById('km-reco-list');
        var timeline = document.getElementById('km-timeline');
        if (!metricGrid || !programList || !recoList || !timeline) return;

        metricGrid.innerHTML = [
            metricCard('Engagement Score', profile.score),
            metricCard('Attendance', profile.attendance),
            metricCard('Status', statusLabelDetail[profile.status] || profile.status),
            metricCard('Focus Area', profile.focus)
        ].join('');

        programList.innerHTML = profile.programs.map(function(p){
            return '<article class="km-list-item"><h4>' + p.title + '</h4><p>' + p.summary + '</p></article>';
        }).join('');

        recoList.innerHTML = profile.recommendations.map(function(r){ return '<li>' + r + '</li>'; }).join('');

        timeline.innerHTML = profile.timeline.map(function(t){
            return '<article class="km-time-item"><h4>' + t.title + '</h4><p>' + t.note + '</p></article>';
        }).join('');
    }

    async function loadRecords() {
        if (!config.dataUrl) {
            return;
        }

        try {
            const response = await fetch(config.dataUrl, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error('Failed to load kabataan records');
            }

            const payload = await response.json();
            records.length = 0;
            (payload.data || []).forEach(function (item) {
                records.push(item);
            });
        } catch (error) {
            console.error(error);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (window.kmPageMode === 'show') {
            loadRecords().then(renderDetail);
            return;
        }
        if (window.kmPageMode === 'barangay-detail') {
            loadRecords().then(function () {
                renderBarangayDetail();
                setupBarangayDetailFilters();
            });
            return;
        }
        initIndex();
    });

    function setupBarangayDetailFilters() {
        var searchInput = document.getElementById('km-brgy-search');
        if (searchInput) {
            searchInput.addEventListener('input', function(e) {
                state.search = e.target.value || '';
                resetBarangayPagination();
                renderBarangayDetail();
            });
            // Also trigger on Enter key
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    performBarangaySearch();
                }
            });
        }

        var yearFilter = document.getElementById('km-brgy-year-filter');
        if (yearFilter) {
            yearFilter.addEventListener('change', function(e) {
                state.year = e.target.value || 'all';
                resetBarangayPagination();
                renderBarangayDetail();
            });
        }

        var rowsSelect = document.getElementById('km-rows-per-page');
        if (rowsSelect) {
            rowsSelect.addEventListener('change', function(e) {
                var perPage = parseInt(e.target.value, 10) || 10;
                window.kmPaginationState = window.kmPaginationState || {};
                window.kmPaginationState.itemsPerPage = perPage;
                resetBarangayPagination();
                renderBarangayDetail();
            });
        }

        var pageInput = document.getElementById('km-page-input');
        if (pageInput) {
            pageInput.addEventListener('change', function(e) {
                var statePag = window.kmPaginationState || {};
                var page = parseInt(e.target.value, 10) || 1;
                var totalPages = statePag.totalPages || 1;
                page = Math.max(1, Math.min(totalPages, page));
                statePag.currentPage = page;
                renderPaginatedTable();
            });
        }

        var prevBtn = document.getElementById('km-prev-btn');
        if (prevBtn) {
            prevBtn.addEventListener('click', function() {
                window.previousPage();
            });
        }

        var nextBtn = document.getElementById('km-next-btn');
        if (nextBtn) {
            nextBtn.addEventListener('click', function() {
                window.nextPage();
            });
        }
    }

    function resetBarangayPagination() {
        window.kmPaginationState = window.kmPaginationState || {};
        window.kmPaginationState.currentPage = 1;
    }

    window.performBarangaySearch = function() {
        var searchInput = document.getElementById('km-brgy-search');
        if (searchInput) {
            state.search = searchInput.value || '';
            resetBarangayPagination();
            renderBarangayDetail();
        }
    };

    // ── Barangay Detail Page ──
    function renderBarangayDetail() {
        var brgy = window.kmBarangay || '';
        var tbody = document.getElementById('km-table-tbody');
        var empty = document.getElementById('km-empty');
        if (!tbody) return;

        var filtered = getFiltered().filter(function(r){ return r.barangay === brgy; });

        window.kmPaginationState = window.kmPaginationState || {};
        if (!window.kmPaginationState.itemsPerPage) {
            var rowsSelect = document.getElementById('km-rows-per-page');
            window.kmPaginationState.itemsPerPage = parseInt(rowsSelect?.value || '10', 10) || 10;
        }
        if (!window.kmPaginationState.currentPage) {
            window.kmPaginationState.currentPage = 1;
        }

        window.kmPaginationState.allItems = filtered;
        window.kmPaginationState.totalPages = Math.max(1, Math.ceil(filtered.length / window.kmPaginationState.itemsPerPage) || 1);

        if (window.kmPaginationState.currentPage > window.kmPaginationState.totalPages) {
            window.kmPaginationState.currentPage = window.kmPaginationState.totalPages;
        }

        if (!filtered.length) {
            tbody.innerHTML = '<tr class="km-empty-row"><td colspan="7">No profiles match your current filters.</td></tr>';
            if (empty) empty.hidden = true;
            updatePagination([], 0);
            return;
        }
        if (empty) empty.hidden = true;

        renderPaginatedTable();
    }

    function formatFullName(r) {
        var suffixRaw = r.suffix || '';
        var suffix = (suffixRaw && String(suffixRaw).toLowerCase() !== 'none') ? ',' + suffixRaw : '';
        var parts = [r.firstName, r.middleName].filter(Boolean);
        var firstMiddle = parts.length ? parts.join(',') : '';
        var last = r.lastName || '';

        if (last && firstMiddle) {
            return last + ',' + firstMiddle + suffix;
        }
        if (last) {
            return last + suffix;
        }
        if (firstMiddle) {
            return firstMiddle + suffix;
        }

        return r.name || '—';
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function renderPaginatedTable() {
        var state = window.kmPaginationState || {};
        var tbody = document.getElementById('km-table-tbody');
        var detailBase = (document.querySelector('main.km-main') || {}).dataset?.detailBase || '/kabataan-monitoring';
        if (!tbody || !state.allItems) return;

        var start = (state.currentPage - 1) * state.itemsPerPage;
        var end = start + state.itemsPerPage;
        var pageItems = state.allItems.slice(start, end);

        var rows = pageItems.map(function(r) {
            var fullName = formatFullName(r);
            return '<tr>' +
                '<td class="km-respondent-cell">' + escapeHtml(r.respondentNumber || '—') + '</td>' +
                '<td class="km-fullname-cell"><span class="km-fullname">' + escapeHtml(fullName) + '</span></td>' +
                '<td>' + escapeHtml(String(r.age ?? '—')) + '</td>' +
                '<td>' + escapeHtml(r.barangay || '—') + '</td>' +
                '<td>' + escapeHtml(r.purokZone || '—') + '</td>' +
                '<td>' + escapeHtml(r.registeredVoter || '—') + '</td>' +
                '<td><div class="km-actions"><button type="button" class="km-btn-view" onclick="openKKPModal(\'' + r.slug.replace(/'/g, "\\'") + '\')">View</button></div></td>' +
                '</tr>';
        }).join('');
        tbody.innerHTML = rows;

        updatePagination(state.allItems, state.currentPage);
    }

    function updatePagination(items, currentPage) {
        var statePag = window.kmPaginationState || {};
        var paginationText = document.getElementById('km-pagination-text');
        var prevBtn = document.getElementById('km-prev-btn');
        var nextBtn = document.getElementById('km-next-btn');
        var pageInput = document.getElementById('km-page-input');
        var totalPagesEl = document.getElementById('km-total-pages');

        if (!items.length) {
            if (paginationText) paginationText.textContent = '0 records';
            if (prevBtn) prevBtn.disabled = true;
            if (nextBtn) nextBtn.disabled = true;
            if (pageInput) pageInput.value = '1';
            if (totalPagesEl) totalPagesEl.textContent = '1';
            return;
        }

        if (paginationText) {
            paginationText.textContent = items.length + ' record' + (items.length === 1 ? '' : 's');
        }

        if (pageInput) {
            pageInput.value = String(currentPage);
            pageInput.max = String(statePag.totalPages || 1);
        }
        if (totalPagesEl) {
            totalPagesEl.textContent = String(statePag.totalPages || 1);
        }

        if (prevBtn) prevBtn.disabled = currentPage <= 1;
        if (nextBtn) nextBtn.disabled = currentPage >= (statePag.totalPages || 1);
    }

    window.previousPage = function() {
        var state = window.kmPaginationState || {};
        if (state.currentPage > 1) {
            state.currentPage--;
            renderPaginatedTable();
        }
    };

    window.nextPage = function() {
        var state = window.kmPaginationState || {};
        if (state.currentPage < state.totalPages) {
            state.currentPage++;
            renderPaginatedTable();
        }
    };

    window.goToPage = function(pageNum) {
        var statePag = window.kmPaginationState || {};
        if (pageNum >= 1 && pageNum <= statePag.totalPages) {
            statePag.currentPage = pageNum;
            renderPaginatedTable();
        }
    };

    // ── KK Profiling Form Modal ──
    window.openKKPModal = function(kabataanSlug) {
        var profile = records.find(function(r){ return r.slug === kabataanSlug; });
        if (!profile) return;

        var modal = document.getElementById('kmKKPModal');
        var container = document.getElementById('kmKKPFormContainer');
        if (!modal || !container) return;

        var urlTemplate = (window.kmConfig && window.kmConfig.questionnaireUrl) || '';
        var recordId = profile.id || profile.slug;
        var fetchUrl = urlTemplate.replace('__ID__', encodeURIComponent(recordId));

        container.innerHTML = '<p class="km-kkp-loading">Loading questionnaire...</p>';
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';

        fetch(fetchUrl, {
            method: 'GET',
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        })
            .then(function(res) {
                if (!res.ok) {
                    throw new Error('Failed to load questionnaire');
                }
                return res.json();
            })
            .then(function(data) {
                var html = data.html || '<p class="km-kkp-loading">No questionnaire data found.</p>';
                container.innerHTML = '<div class="kk-qs-scroll-wrapper">' + html + '</div>';
            })
            .catch(function() {
                container.innerHTML = '<p class="km-kkp-loading">Unable to load questionnaire. Please try again.</p>';
            });
    };

    window.closeKKPModal = function() {
        var modal = document.getElementById('kmKKPModal');
        if (modal) {
            modal.classList.remove('show');
            document.body.style.overflow = '';
        }
    };

    window.printKKPForm = function() {
        var container = document.getElementById('kmKKPFormContainer');
        if (!container || !container.innerHTML.trim()) return;

        var printWindow = window.open('', '', 'height=800,width=900');
        var formHTML = container.innerHTML;
        var viewCssHref = window.location.origin + '/modules/kabataan-monitoring/css/kk-questionnaire-view.css';

        printWindow.document.write('<html><head><title>KK Survey Questionnaire</title>');
        printWindow.document.write('<link rel="stylesheet" href="' + viewCssHref + '">');
        printWindow.document.write('</head><body>');
        printWindow.document.write(formHTML);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.onload = function() {
            printWindow.print();
        };
    };
})();
