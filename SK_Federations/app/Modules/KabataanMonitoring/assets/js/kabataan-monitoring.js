(function () {
    const config = window.kmConfig || {};
    const records = [];

    const state = { search: '', status: 'all', barangay: 'all' };

    function getFiltered() {
        var q = state.search.trim().toLowerCase();
        return records.filter(function(r) {
            var matchStatus = state.status === 'all' || r.status === state.status;
            var matchBrgy   = state.barangay === 'all' || r.barangay === state.barangay;
            var hay = (r.name + ' ' + r.barangay + ' ' + r.focus + ' ' + r.youthClassification).toLowerCase();
            var matchSearch = !q || hay.includes(q);
            return matchStatus && matchBrgy && matchSearch;
        });
    }

    function updateSummary(items) {
        var total    = records.length;
        var active   = records.filter(function(r){ return r.status === 'active'; }).length;
        var inactive = records.filter(function(r){ return r.status === 'inactive'; }).length;
        var rate     = total > 0 ? Math.round((active / total) * 100) : 0;

        var t = document.getElementById('km-kpi-total');
        var a = document.getElementById('km-kpi-active');
        var i = document.getElementById('km-kpi-inactive');
        var p = document.getElementById('km-kpi-rate');
        if (t) t.textContent = total;
        if (a) a.textContent = active;
        if (i) i.textContent = inactive;
        if (p) p.textContent = rate + '%';
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
                '<div class="km-bsc-stats">' +
                    '<div class="km-bsc-stat"><i class="fas fa-users"></i> <strong>' + total + '</strong> Total Kabataan</div>' +
                    '<div class="km-bsc-stat"><i class="fas fa-chart-pie"></i> <strong>' + rate + '%</strong> Participation Rate</div>' +
                    '<div class="km-bsc-stat"><i class="fas fa-user-check"></i> <strong>' + active + '</strong> Active</div>' +
                    '<div class="km-bsc-stat"><i class="fas fa-user-times"></i> <strong>' + inactive + '</strong> Inactive</div>' +
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
                renderBarangayDetail();
            });
        }

        // Period filter dropdown
        var periodFilter = document.getElementById('km-period-filter');
        if (periodFilter) {
            periodFilter.addEventListener('change', function(e) {
                state.period = e.target.value || 'all';
                renderBarangayDetail();
            });
        }
    }

    window.performBarangaySearch = function() {
        var searchInput = document.getElementById('km-brgy-search');
        if (searchInput) {
            state.search = searchInput.value || '';
            renderBarangayDetail();
        }
    };

    // ── Barangay Detail Page ──
    function renderBarangayDetail() {
        var brgy = window.kmBarangay || '';
        var members = records.filter(function(r){ return r.barangay === brgy; });
        var tbody = document.getElementById('km-table-tbody');
        var empty = document.getElementById('km-empty');
        var countEl = document.getElementById('km-result-count');
        if (!tbody) return;

        // Update summary cards
        var total = members.length;
        var active = members.filter(function(r){ return r.status === 'active'; }).length;
        var inactive = members.filter(function(r){ return r.status === 'inactive'; }).length;
        var rate = total > 0 ? Math.round(((active + members.filter(function(r){ return r.status === 'moderate'; }).length) / total) * 100) : 0;

        var totalEl = document.getElementById('km-brgy-total');
        var rateEl = document.getElementById('km-brgy-rate');
        var activeEl = document.getElementById('km-brgy-active');
        var inactiveEl = document.getElementById('km-brgy-inactive');
        if (totalEl) totalEl.textContent = total;
        if (rateEl) rateEl.textContent = rate + '%';
        if (activeEl) activeEl.textContent = active;
        if (inactiveEl) inactiveEl.textContent = inactive;

        // Render table
        var filtered = getFiltered().filter(function(r){ return r.barangay === brgy; });
        if (countEl) countEl.textContent = filtered.length + ' record' + (filtered.length !== 1 ? 's' : '');

        if (!filtered.length) {
            tbody.innerHTML = '';
            if (empty) empty.hidden = false;
            updatePagination([], 0);
            return;
        }
        if (empty) empty.hidden = true;

        // Pagination setup
        window.kmPaginationState = window.kmPaginationState || {};
        window.kmPaginationState.itemsPerPage = 10;
        window.kmPaginationState.currentPage = 1;
        window.kmPaginationState.allItems = filtered;
        window.kmPaginationState.totalPages = Math.ceil(filtered.length / window.kmPaginationState.itemsPerPage);

        renderPaginatedTable();
    }

    function renderPaginatedTable() {
        var state = window.kmPaginationState || {};
        var tbody = document.getElementById('km-table-tbody');
        var detailBase = (document.querySelector('main.km-main') || {}).dataset?.detailBase || '/kabataan-monitoring';
        if (!tbody || !state.allItems) return;

        var start = (state.currentPage - 1) * state.itemsPerPage;
        var end = start + state.itemsPerPage;
        var pageItems = state.allItems.slice(start, end);

        var rows = pageItems.map(function(r, idx) {
            var rowNum = start + idx + 1;
            return '<tr>' +
                '<td>' + rowNum + '</td>' +
                '<td class="km-td-name">' + r.name + '</td>' +
                '<td>' + r.age + '</td>' +
                '<td>' + r.sex + '</td>' +
                '<td>' + r.civilStatus + '</td>' +
                '<td>' + r.education + '</td>' +
                '<td>' + r.workStatus + '</td>' +
                '<td>' + r.youthClassification + '</td>' +
                '<td><span class="km-badge ' + r.status + '">' + (statusLabel[r.status] || r.status) + '</span></td>' +
                '<td><button class="km-btn" onclick="openKKPModal(\'' + r.slug.replace(/'/g, "\\'") + '\')">View <i class="fas fa-arrow-right"></i></button></td>' +
                '</tr>';
        }).join('');
        tbody.innerHTML = rows;

        updatePagination(state.allItems, state.currentPage);
    }

    function updatePagination(items, currentPage) {
        var state = window.kmPaginationState || {};
        var paginationText = document.getElementById('km-pagination-text');
        var prevBtn = document.getElementById('km-prev-btn');
        var nextBtn = document.getElementById('km-next-btn');
        var pageNumbers = document.getElementById('km-pagination-numbers');

        if (!items.length) {
            if (paginationText) paginationText.textContent = 'Showing 0 of 0 records';
            if (prevBtn) prevBtn.disabled = true;
            if (nextBtn) nextBtn.disabled = true;
            if (pageNumbers) pageNumbers.innerHTML = '';
            return;
        }

        var start = (currentPage - 1) * state.itemsPerPage + 1;
        var end = Math.min(currentPage * state.itemsPerPage, items.length);
        if (paginationText) paginationText.textContent = 'Showing ' + start + ' to ' + end + ' of ' + items.length + ' records';

        if (prevBtn) prevBtn.disabled = currentPage === 1;
        if (nextBtn) nextBtn.disabled = currentPage === state.totalPages;

        // Generate page numbers
        var pageHtml = '';
        var maxPages = 5;
        var startPage = Math.max(1, currentPage - Math.floor(maxPages / 2));
        var endPage = Math.min(state.totalPages, startPage + maxPages - 1);
        if (endPage - startPage < maxPages - 1) {
            startPage = Math.max(1, endPage - maxPages + 1);
        }

        if (startPage > 1) {
            pageHtml += '<button class="km-page-num" onclick="goToPage(1)">1</button>';
            if (startPage > 2) pageHtml += '<span class="km-page-ellipsis">...</span>';
        }

        for (var i = startPage; i <= endPage; i++) {
            var activeClass = i === currentPage ? 'active' : '';
            pageHtml += '<button class="km-page-num ' + activeClass + '" onclick="goToPage(' + i + ')">' + i + '</button>';
        }

        if (endPage < state.totalPages) {
            if (endPage < state.totalPages - 1) pageHtml += '<span class="km-page-ellipsis">...</span>';
            pageHtml += '<button class="km-page-num" onclick="goToPage(' + state.totalPages + ')">' + state.totalPages + '</button>';
        }

        if (pageNumbers) pageNumbers.innerHTML = pageHtml;
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
        var state = window.kmPaginationState || {};
        if (pageNum >= 1 && pageNum <= state.totalPages) {
            state.currentPage = pageNum;
            renderPaginatedTable();
        }
    };

    window.exportBarangayCSV = function() {
        var brgy = window.kmBarangay || '';
        var members = records.filter(function(r){ return r.barangay === brgy; });
        var headers = ['Name','Age','Sex','Barangay','Civil Status','Education','Work Status','Youth Classification','Engagement Score','Status'];
        var rows = members.map(function(r) {
            return [r.name,r.age,r.sex,r.barangay,r.civilStatus,r.education,r.workStatus,r.youthClassification,r.score,statusLabel[r.status]||r.status]
                .map(function(v){ return '"'+String(v).replace(/"/g,'""')+'"'; }).join(',');
        });
        var csv = [headers.join(',')].concat(rows).join('\n');
        var blob = new Blob([csv],{type:'text/csv'});
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href=url; a.download='kkk-'+brgy.replace(/\s+/g,'-').toLowerCase()+'.csv';
        document.body.appendChild(a); a.click();
        document.body.removeChild(a); URL.revokeObjectURL(url);
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
