(function () {
    const records = [
        { slug:'alyssa-ramos',      name:'Alyssa Ramos',      age:21, sex:'Female', barangay:'Brgy. I (Poblacion)',   civilStatus:'Single',   education:'College Level',    workStatus:'Student',    youthClassification:'In-School Youth',  focus:'Education',          score:93, status:'active',   attendance:'11/12', lastCheckIn:'Mar 18, 2026', programs:[{title:'Peer Tutor Circle',summary:'Facilitator for youth study sessions.'},{title:'Digital Literacy Camp',summary:'Volunteer mentor for computer skills.'}], recommendations:['Assign as youth mentor for incoming volunteers.','Include in inter-barangay leadership exchange.','Track attendance for scholarship endorsement.'], timeline:[{title:'Initial registration',note:'Completed profile validation Jan 2026.'},{title:'Engagement uplift',note:'Attendance improved after joining tutor circle.'},{title:'Current monitoring',note:'Maintaining high score with peer support hours.'}] },
        { slug:'joshua-villanueva', name:'Joshua Villanueva', age:19, sex:'Male',   barangay:'Pagsawitan',            civilStatus:'Single',   education:'High School Grad', workStatus:'Unemployed', youthClassification:'Out-of-School Youth',focus:'Sports Development',  score:77, status:'moderate', attendance:'8/12',  lastCheckIn:'Mar 14, 2026', programs:[{title:'Barangay League',summary:'Participates in weekend league.'},{title:'Youth Referee Training',summary:'Under evaluation for officiating track.'}], recommendations:['Set monthly mentorship check-in.','Encourage parent-guardian touchpoint.','Pair with high-engagement peer buddy.'], timeline:[{title:'Re-engagement',note:'Returned after two-month inactivity.'},{title:'Participation recovery',note:'Attendance stabilized in February.'},{title:'Current monitoring',note:'Needs one-on-one follow-up.'}] },
        { slug:'celine-mendoza',    name:'Celine Mendoza',    age:20, sex:'Female', barangay:'Labuin',                civilStatus:'Single',   education:'College Level',    workStatus:'Unemployed', youthClassification:'Out-of-School Youth',focus:'Health and Wellness', score:59, status:'inactive', attendance:'5/12',  lastCheckIn:'Mar 06, 2026', programs:[{title:'Wellness Caravan',summary:'Registered but inconsistent participation.'},{title:'Mental Health Circle',summary:'Scheduled for counseling referral.'}], recommendations:['Prioritize welfare follow-up.','Include in transportation support pilot.','Escalate to wellness team if no check-in in 14 days.'], timeline:[{title:'At-risk flag',note:'Marked low engagement after repeated absence.'},{title:'Support initiated',note:'Referred to wellness caravan.'},{title:'Current monitoring',note:'Pending follow-up with household liaison.'}] },
        { slug:'mark-bautista',     name:'Mark Bautista',     age:22, sex:'Male',   barangay:'San Jose',              civilStatus:'Single',   education:'Vocational Grad',  workStatus:'Employed',   youthClassification:'Working Youth',    focus:'Livelihood',          score:74, status:'moderate', attendance:'7/12',  lastCheckIn:'Mar 12, 2026', programs:[{title:'Youth Skills Lab',summary:'Active in welding and carpentry sessions.'},{title:'Micro-Enterprise Clinic',summary:'Joined business planning workshops.'}], recommendations:['Route to advanced skills track under TESDA.','Monitor transition to job placement.','Track attendance against monthly targets.'], timeline:[{title:'Career pathway mapping',note:'Completed skills assessment.'},{title:'Program participation',note:'Moderate attendance in livelihood sessions.'},{title:'Current monitoring',note:'Needs attendance boost.'}] },
        { slug:'jessa-garcia',      name:'Jessa Garcia',      age:18, sex:'Female', barangay:'Brgy. III (Poblacion)', civilStatus:'Single',   education:'High School Grad', workStatus:'Student',    youthClassification:'In-School Youth',  focus:'Civic Participation', score:90, status:'active',   attendance:'12/12', lastCheckIn:'Mar 20, 2026', programs:[{title:'Barangay Assembly Corps',summary:'Lead youth mobilizer for civic assemblies.'},{title:'Voter Education Drive',summary:'Facilitates awareness for first-time voters.'}], recommendations:['Nominate for municipal youth leadership summit.','Assign as co-facilitator for federation orientation.','Capture case study for best-practice documentation.'], timeline:[{title:'Leadership onboarding',note:'Selected as youth mobilizer representative.'},{title:'Community impact',note:'Highest attendance and outreach participation.'},{title:'Current monitoring',note:'Sustaining high engagement.'}] },
        { slug:'paolo-santos',      name:'Paolo Santos',      age:20, sex:'Male',   barangay:'Brgy. V (Poblacion)',   civilStatus:'Single',   education:'High School Level', workStatus:'Unemployed', youthClassification:'Out-of-School Youth',focus:'School Continuity',  score:54, status:'inactive', attendance:'4/12',  lastCheckIn:'Mar 04, 2026', programs:[{title:'Back-to-School Support',summary:'Identified for scholarship support.'},{title:'Weekend Mentoring Group',summary:'Pending reactivation.'}], recommendations:['Coordinate with school focal person.','Prioritize in educational assistance.','Conduct household visit.'], timeline:[{title:'Attendance decline',note:'Flagged due to repeated absences.'},{title:'Intervention prepared',note:'Support package drafted.'},{title:'Current monitoring',note:'Awaiting family consent.'}] },
        { slug:'maria-santos',      name:'Maria Santos',      age:17, sex:'Female', barangay:'Alipit',                civilStatus:'Single',   education:'High School Level', workStatus:'Student',    youthClassification:'In-School Youth',  focus:'Education',          score:85, status:'active',   attendance:'10/12', lastCheckIn:'Mar 19, 2026', programs:[{title:'Study Circle',summary:'Active participant in peer learning.'},{title:'Youth Leadership Seminar',summary:'Attended municipal leadership training.'}], recommendations:['Recommend for scholarship program.','Include in youth council activities.'], timeline:[{title:'Registration',note:'Enrolled in Jan 2026.'},{title:'Active participation',note:'Consistent attendance in study circle.'}] },
        { slug:'ryan-dela-cruz',    name:'Ryan Dela Cruz',    age:23, sex:'Male',   barangay:'Bagumbayan',            civilStatus:'Single',   education:'College Grad',     workStatus:'Employed',   youthClassification:'Working Youth',    focus:'Livelihood',          score:80, status:'active',   attendance:'9/12',  lastCheckIn:'Mar 17, 2026', programs:[{title:'Entrepreneurship Workshop',summary:'Completed business planning module.'},{title:'Skills Certification',summary:'Enrolled in TESDA NC II program.'}], recommendations:['Support transition to self-employment.','Connect with DOLE livelihood programs.'], timeline:[{title:'Skills assessment',note:'Completed in Feb 2026.'},{title:'Program enrollment',note:'Joined entrepreneurship workshop.'}] },
        { slug:'ana-reyes',         name:'Ana Reyes',         age:16, sex:'Female', barangay:'Bubukal',               civilStatus:'Single',   education:'High School Level', workStatus:'Student',    youthClassification:'In-School Youth',  focus:'Health and Wellness', score:70, status:'moderate', attendance:'7/12',  lastCheckIn:'Mar 10, 2026', programs:[{title:'Health Awareness Camp',summary:'Participated in wellness activities.'},{title:'Sports Clinic',summary:'Enrolled in badminton training.'}], recommendations:['Encourage consistent attendance.','Refer to school guidance counselor.'], timeline:[{title:'Initial engagement',note:'Joined health camp in Jan 2026.'},{title:'Moderate participation',note:'Attendance fluctuating.'}] },
        { slug:'carlo-mendez',      name:'Carlo Mendez',      age:21, sex:'Male',   barangay:'Calios',                civilStatus:'Single',   education:'College Level',    workStatus:'Student',    youthClassification:'In-School Youth',  focus:'Civic Participation', score:88, status:'active',   attendance:'11/12', lastCheckIn:'Mar 21, 2026', programs:[{title:'Barangay Youth Council',summary:'Active council member.'},{title:'Community Clean-up Drive',summary:'Lead organizer for environmental activities.'}], recommendations:['Nominate for federation youth award.','Assign as barangay youth representative.'], timeline:[{title:'Council membership',note:'Elected in Dec 2025.'},{title:'Active leadership',note:'Led multiple community activities.'}] },
        { slug:'liza-flores',       name:'Liza Flores',       age:19, sex:'Female', barangay:'Duhat',                 civilStatus:'Single',   education:'High School Grad', workStatus:'Unemployed', youthClassification:'Out-of-School Youth',focus:'Livelihood',          score:62, status:'moderate', attendance:'6/12',  lastCheckIn:'Mar 08, 2026', programs:[{title:'Livelihood Training',summary:'Enrolled in dressmaking course.'},{title:'Micro-Finance Orientation',summary:'Attended financial literacy session.'}], recommendations:['Connect with DSWD livelihood program.','Provide transportation assistance.'], timeline:[{title:'Program enrollment',note:'Joined livelihood training Feb 2026.'},{title:'Moderate engagement',note:'Attendance needs improvement.'}] },
        { slug:'ben-torres',        name:'Ben Torres',        age:24, sex:'Male',   barangay:'Gatid',                 civilStatus:'Married',  education:'College Grad',     workStatus:'Employed',   youthClassification:'Working Youth',    focus:'Civic Participation', score:76, status:'moderate', attendance:'8/12',  lastCheckIn:'Mar 15, 2026', programs:[{title:'Youth Volunteer Corps',summary:'Active volunteer in community programs.'},{title:'Disaster Preparedness Training',summary:'Completed DRRM basic training.'}], recommendations:['Assign as community emergency responder.','Include in inter-barangay coordination.'], timeline:[{title:'Volunteer registration',note:'Joined corps in Nov 2025.'},{title:'Training completion',note:'Completed DRRM training.'}] },
        { slug:'grace-aquino',      name:'Grace Aquino',      age:18, sex:'Female', barangay:'Palasan',               civilStatus:'Single',   education:'High School Grad', workStatus:'Student',    youthClassification:'In-School Youth',  focus:'Education',          score:91, status:'active',   attendance:'12/12', lastCheckIn:'Mar 22, 2026', programs:[{title:'Academic Excellence Program',summary:'Top performer in academic support.'},{title:'Youth Leadership Camp',summary:'Completed leadership training.'}], recommendations:['Endorse for municipal scholarship.','Assign as peer tutor.'], timeline:[{title:'Top performer',note:'Recognized in Feb 2026.'},{title:'Leadership training',note:'Completed youth camp.'}] },
        { slug:'rico-navarro',      name:'Rico Navarro',      age:22, sex:'Male',   barangay:'Santisima Cruz',        civilStatus:'Single',   education:'Vocational Grad',  workStatus:'Self-Employed', youthClassification:'Working Youth', focus:'Livelihood',          score:83, status:'active',   attendance:'10/12', lastCheckIn:'Mar 16, 2026', programs:[{title:'Skills Development Program',summary:'Completed electrical installation course.'},{title:'Business Mentoring',summary:'Enrolled in micro-enterprise mentoring.'}], recommendations:['Connect with DTI for business registration.','Include in youth enterprise fair.'], timeline:[{title:'Skills completion',note:'Finished electrical course Jan 2026.'},{title:'Business setup',note:'Started small electrical services business.'}] },
        { slug:'diana-cruz',        name:'Diana Cruz',        age:20, sex:'Female', barangay:'Brgy. II (Poblacion)',  civilStatus:'Single',   education:'College Level',    workStatus:'Student',    youthClassification:'In-School Youth',  focus:'Health and Wellness', score:45, status:'inactive', attendance:'3/12',  lastCheckIn:'Feb 28, 2026', programs:[{title:'Mental Health Support',summary:'Referred for counseling services.'},{title:'Wellness Caravan',summary:'Registered but rarely attends.'}], recommendations:['Immediate welfare check required.','Coordinate with school guidance office.','Household visit within 7 days.'], timeline:[{title:'At-risk identification',note:'Flagged in Feb 2026.'},{title:'Referral initiated',note:'Referred to counseling services.'},{title:'Current monitoring',note:'Awaiting response from household.'}] }
    ];

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
        var sel = document.getElementById('km-brgy-filter');
        if (!sel) return;
        var brgys = [...new Set(records.map(function(r){ return r.barangay; }))].sort();
        brgys.forEach(function(b) {
            var opt = document.createElement('option');
            opt.value = b; opt.textContent = b;
            sel.appendChild(opt);
        });
    }

    var statusLabel = { active: 'Active', moderate: 'Moderate', inactive: 'Inactive' };

    function renderBrgyCards() {
        var container = document.getElementById('km-brgy-cards');
        var empty = document.getElementById('km-empty');
        var countEl = document.getElementById('km-result-count');
        if (!container) return;

        var filtered = getFiltered();
        if (countEl) countEl.textContent = filtered.length + ' record' + (filtered.length !== 1 ? 's' : '');

        if (!filtered.length) {
            container.innerHTML = '';
            if (empty) empty.hidden = false;
            return;
        }
        if (empty) empty.hidden = true;

        // Group by barangay
        var grouped = {};
        records.forEach(function(r) { // use all records for card stats
            if (!grouped[r.barangay]) grouped[r.barangay] = [];
            grouped[r.barangay].push(r);
        });

        var brgys = Object.keys(grouped).sort();
        var today = new Date();
        var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

        container.innerHTML = '<div class="km-brgy-grid">' + brgys.map(function(brgy) {
            var members = grouped[brgy];
            var total    = members.length;
            var active   = members.filter(function(r){ return r.status === 'active'; }).length;
            var moderate = members.filter(function(r){ return r.status === 'moderate'; }).length;
            var inactive = members.filter(function(r){ return r.status === 'inactive'; }).length;
            var rate     = total > 0 ? Math.round(((active + moderate) / total) * 100) : 0;
            var lastUpdate = months[today.getMonth()] + ' ' + today.getDate() + ', ' + today.getFullYear();

            var statusClass = rate >= 80 ? 'active' : rate >= 50 ? 'moderate' : 'inactive';
            var statusText  = rate >= 80 ? 'High Participation' : rate >= 50 ? 'Moderate' : 'Low Participation';

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
        updateSummary();
        renderBrgyCards();

        var searchInput = document.getElementById('km-search');
        if (searchInput) {
            searchInput.addEventListener('input', function(e) {
                state.search = e.target.value || '';
                renderBrgyCards();
            });
        }

        var chipWrap = document.getElementById('km-status-filter');
        if (chipWrap) {
            chipWrap.querySelectorAll('.km-chip').forEach(function(chip) {
                chip.addEventListener('click', function() {
                    chipWrap.querySelectorAll('.km-chip').forEach(function(c){ c.classList.remove('active'); });
                    chip.classList.add('active');
                    state.status = chip.dataset.status || 'all';
                    renderBrgyCards();
                });
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

    document.addEventListener('DOMContentLoaded', function() {
        if (window.kmPageMode === 'show') { renderDetail(); return; }
        if (window.kmPageMode === 'barangay-detail') { 
            renderBarangayDetail();
            setupBarangayDetailFilters();
            return; 
        }
        initIndex();
    });

    function setupBarangayDetailFilters() {
        var chipWrap = document.getElementById('km-status-filter');
        if (chipWrap) {
            chipWrap.querySelectorAll('.km-chip').forEach(function(chip) {
                chip.addEventListener('click', function() {
                    chipWrap.querySelectorAll('.km-chip').forEach(function(c){ c.classList.remove('active'); });
                    chip.classList.add('active');
                    state.status = chip.dataset.status || 'all';
                    renderBarangayDetail();
                });
            });
        }

        var searchInput = document.getElementById('km-search');
        if (searchInput) {
            searchInput.addEventListener('input', function(e) {
                state.search = e.target.value || '';
                renderBarangayDetail();
            });
        }
    }

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

        // Populate form with example data
        document.getElementById('kmKKPRespondent').value = 'KKP-' + Math.floor(Math.random() * 10000).toString().padStart(5, '0');
        document.getElementById('kmKKPDate').value = new Date().toLocaleDateString('en-US', { year: 'numeric', month: '2-digit', day: '2-digit' });
        
        document.getElementById('kmKKPLastName').value = profile.name.split(' ').pop();
        document.getElementById('kmKKPFirstName').value = profile.name.split(' ')[0];
        document.getElementById('kmKKPBarangay').value = profile.barangay;
        document.getElementById('kmKKPAge').value = profile.age;
        document.getElementById('kmKKPPurok').value = 'Purok ' + String.fromCharCode(65 + Math.floor(Math.random() * 5));
        
        // Set sex
        document.querySelector('input[name="kmKKPSex"][value="' + profile.sex + '"]').checked = true;
        
        // Set birthday (approximate from age)
        var today = new Date();
        var birthYear = today.getFullYear() - profile.age;
        var birthDate = new Date(birthYear, today.getMonth(), today.getDate());
        document.getElementById('kmKKPBirthday').value = birthDate.toISOString().split('T')[0];
        
        document.getElementById('kmKKPEmail').value = profile.name.toLowerCase().replace(/\s+/g, '.') + '@example.com';
        document.getElementById('kmKKPContact').value = '09' + Math.floor(Math.random() * 1000000000).toString().padStart(9, '0');
        
        // Set civil status
        document.querySelector('input[name="kmKKPCivilStatus"][value="Single"]').checked = true;
        
        // Set youth age group
        if (profile.age >= 15 && profile.age <= 17) {
            document.querySelector('input[name="kmKKPYouthAge"][value="Child Youth (15-17 yrs old)"]').checked = true;
        } else if (profile.age >= 18 && profile.age <= 24) {
            document.querySelector('input[name="kmKKPYouthAge"][value="Core Youth (18-24 yrs old)"]').checked = true;
        } else {
            document.querySelector('input[name="kmKKPYouthAge"][value="Young Adult (15-30 yrs old)"]').checked = true;
        }
        
        // Set education based on classification
        if (profile.youthClassification === 'In-School Youth') {
            document.querySelector('input[name="kmKKPEducation"][value="High School Level"]').checked = true;
        } else {
            document.querySelector('input[name="kmKKPEducation"][value="College Level"]').checked = true;
        }
        
        // Set youth classification
        var youthClassMap = {
            'In-School Youth': 'In School Youth',
            'Out-of-School Youth': 'Out of School Youth',
            'Working Youth': 'Working Youth'
        };
        var mappedClass = youthClassMap[profile.youthClassification] || profile.youthClassification;
        var youthClassCheckbox = document.querySelector('input[name="kmKKPYouthClass"][value="' + mappedClass + '"]');
        if (youthClassCheckbox) youthClassCheckbox.checked = true;
        
        // Set work status
        var workStatusMap = {
            'Student': 'Unemployed',
            'Employed': 'Employed',
            'Unemployed': 'Unemployed',
            'Self-Employed': 'Self-Employed'
        };
        var mappedStatus = workStatusMap[profile.workStatus] || 'Unemployed';
        var workStatusCheckbox = document.querySelector('input[name="kmKKPWorkStatus"][value="' + mappedStatus + '"]');
        if (workStatusCheckbox) workStatusCheckbox.checked = true;
        
        // Set SK voter status
        if (profile.status === 'active') {
            document.querySelector('input[name="kmKKPSKVoter"][value="Yes"]').checked = true;
            document.querySelector('input[name="kmKKPSKVoted"][value="Yes"]').checked = true;
        }
        
        document.getElementById('kmKKPFacebook').value = profile.name.toLowerCase().replace(/\s+/g, '') + '123';
        document.querySelector('input[name="kmKKPGroupChat"][value="Yes"]').checked = true;
        
        // Show modal
        var modal = document.getElementById('kmKKPModal');
        if (modal) {
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    };

    window.closeKKPModal = function() {
        var modal = document.getElementById('kmKKPModal');
        if (modal) {
            modal.classList.remove('show');
            document.body.style.overflow = '';
        }
    };

    window.printKKPForm = function() {
        var modal = document.getElementById('kmKKPModal');
        if (!modal) return;
        
        var printWindow = window.open('', '', 'height=600,width=800');
        var formHTML = modal.querySelector('.km-kkp-form').innerHTML;
        
        printWindow.document.write('<html><head><title>KK Survey Questionnaire</title>');
        printWindow.document.write('<style>');
        printWindow.document.write('body { font-family: Arial, sans-serif; margin: 20px; }');
        printWindow.document.write('.km-kkp-section-title { font-weight: bold; margin-top: 15px; margin-bottom: 10px; border-bottom: 2px solid #333; }');
        printWindow.document.write('.km-kkp-field-group { margin-bottom: 15px; }');
        printWindow.document.write('.km-kkp-field-label { font-weight: bold; display: block; margin-bottom: 5px; }');
        printWindow.document.write('input, select { padding: 5px; margin: 3px; }');
        printWindow.document.write('.km-kkp-checkbox-group { margin-left: 20px; }');
        printWindow.document.write('label { display: block; margin: 5px 0; }');
        printWindow.document.write('</style></head><body>');
        printWindow.document.write('<h1>KK Survey Questionnaire</h1>');
        printWindow.document.write(formHTML);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.print();
    };
})();
