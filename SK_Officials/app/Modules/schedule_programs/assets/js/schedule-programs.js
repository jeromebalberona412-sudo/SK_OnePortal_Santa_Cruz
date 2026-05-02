// ── Education Activity Sample Data ────────────────────────────────────────
const SP_EDUCATION_SAMPLE = {
    'Support to ALS and RIC': [
        {
            last_name: 'Mendoza', first_name: 'Liza', middle_name: 'Cruz', suffix: '',
            school_name: 'ALS Learning Center – Santa Cruz', school_address: 'Brgy. Poblacion, Santa Cruz, Laguna',
            year_level: 'ALS Level 2', program_strand: 'Alternative Learning System (ALS)',
            purpose: 'Learning Materials', approved_at: 'Feb 5, 2025',
            purpose_list: ['Learning Materials'], purpose_others: '',
            cor_certified: true, photo_id: true,
        },
        {
            last_name: 'Ramos', first_name: 'Carlo', middle_name: 'Bautista', suffix: '',
            school_name: 'RIC – Regional Interactive Center', school_address: 'Brgy. Calios, Santa Cruz, Laguna',
            year_level: 'RIC Module 3', program_strand: 'Reading and Integrated Curriculum (RIC)',
            purpose: 'Books / Equipments', approved_at: 'Feb 12, 2025',
            purpose_list: ['Books / Equipments'], purpose_others: '',
            cor_certified: true, photo_id: false,
        },
        {
            last_name: 'Villanueva', first_name: 'Ana', middle_name: 'Santos', suffix: '',
            school_name: 'ALS Learning Center – Santa Cruz', school_address: 'Brgy. Poblacion, Santa Cruz, Laguna',
            year_level: 'ALS Level 1', program_strand: 'Alternative Learning System (ALS)',
            purpose: 'Tuition Fees, Learning Materials', approved_at: 'Mar 1, 2025',
            purpose_list: ['Tuition Fees', 'Learning Materials'], purpose_others: '',
            cor_certified: false, photo_id: true,
        },
    ],
    '150 Students for Educational Assistance': [
        {
            last_name: 'Reyes', first_name: 'Maria', middle_name: 'Santos', suffix: '',
            school_name: 'Laguna State Polytechnic University',
            school_address: 'Brgy. Siniloan, Siniloan, Laguna 4019',
            year_level: '2nd Year', program_strand: 'Bachelor of Secondary Education (BSED)',
            purpose: 'Tuition Fees, Books / Equipments', approved_at: 'Jan 15, 2025',
            purpose_list: ['Tuition Fees', 'Books / Equipments'], purpose_others: '',
            cor_certified: true, photo_id: true,
        },
        {
            last_name: 'Dela Cruz', first_name: 'Jose', middle_name: 'Ramos', suffix: 'Jr.',
            school_name: 'Laguna State Polytechnic University',
            school_address: 'Brgy. Siniloan, Siniloan, Laguna 4019',
            year_level: '3rd Year', program_strand: 'Bachelor of Science in Information Technology (BSIT)',
            purpose: 'Tuition Fees, Living Expenses', approved_at: 'Jan 25, 2025',
            purpose_list: ['Tuition Fees', 'Living Expenses'], purpose_others: '',
            cor_certified: true, photo_id: true,
        },
        {
            last_name: 'Bautista', first_name: 'Kristine', middle_name: 'Flores', suffix: '',
            school_name: 'De La Salle University – Dasmariñas',
            school_address: 'Brgy. Salitran, Dasmariñas, Cavite 4114',
            year_level: '2nd Year', program_strand: 'Bachelor of Science in Nursing (BSN)',
            purpose: 'Tuition Fees, Books / Equipments', approved_at: 'Feb 10, 2025',
            purpose_list: ['Tuition Fees', 'Books / Equipments'], purpose_others: '',
            cor_certified: true, photo_id: true,
        },
        {
            last_name: 'Santos', first_name: 'Mark', middle_name: 'Villanueva', suffix: '',
            school_name: 'University of the Philippines Los Baños',
            school_address: 'Brgy. College, Los Baños, Laguna 4031',
            year_level: '4th Year', program_strand: 'Bachelor of Science in Computer Science (BSCS)',
            purpose: 'Tuition Fees, Living Expenses', approved_at: 'Feb 20, 2025',
            purpose_list: ['Tuition Fees', 'Living Expenses'], purpose_others: '',
            cor_certified: true, photo_id: false,
        },
        {
            last_name: 'Lim', first_name: 'Angela', middle_name: 'Cruz', suffix: '',
            school_name: 'Santa Cruz National High School',
            school_address: 'Brgy. Poblacion, Santa Cruz, Laguna 4009',
            year_level: 'Grade 12', program_strand: 'Science, Technology, Engineering and Mathematics (STEM)',
            purpose: 'Books / Equipments', approved_at: 'Mar 5, 2025',
            purpose_list: ['Books / Equipments'], purpose_others: '',
            cor_certified: false, photo_id: true,
        },
    ],
    'Support to Elementary and Daycare': [
        {
            last_name: 'Garcia', first_name: 'Sofia', middle_name: 'Lopez', suffix: '',
            school_name: 'Calios Elementary School',
            school_address: 'Brgy. Calios, Santa Cruz, Laguna',
            year_level: 'Grade 3', program_strand: 'Elementary Education',
            purpose: 'School Supplies', approved_at: 'Jan 20, 2025',
            purpose_list: ['School Supplies'], purpose_others: '',
            cor_certified: true, photo_id: true,
        },
        {
            last_name: 'Torres', first_name: 'Miguel', middle_name: 'Reyes', suffix: '',
            school_name: 'Calios Daycare Center',
            school_address: 'Brgy. Calios, Santa Cruz, Laguna',
            year_level: 'Kinder 2', program_strand: 'Early Childhood Education',
            purpose: 'School Supplies, Books / Equipments', approved_at: 'Jan 28, 2025',
            purpose_list: ['School Supplies', 'Books / Equipments'], purpose_others: '',
            cor_certified: true, photo_id: false,
        },
        {
            last_name: 'Flores', first_name: 'Isabella', middle_name: 'Navarro', suffix: '',
            school_name: 'Calios Elementary School',
            school_address: 'Brgy. Calios, Santa Cruz, Laguna',
            year_level: 'Grade 5', program_strand: 'Elementary Education',
            purpose: 'Tuition Fees, School Supplies', approved_at: 'Feb 8, 2025',
            purpose_list: ['Tuition Fees', 'School Supplies'], purpose_others: '',
            cor_certified: false, photo_id: true,
        },
    ],
};

document.addEventListener('DOMContentLoaded', () => {
    initializeSchedulePrograms();
    initializeCommitteeCards();

    // ── "View Passed Scholars" standalone button ──────────────────────────
    const showPassedBtn = document.getElementById('spShowPassedBtn');
    if (showPassedBtn) {
        showPassedBtn.addEventListener('click', () => {
            // Hide sports section if visible
            const sportsSection = document.getElementById('spSportsSection');
            if (sportsSection) sportsSection.style.display = 'none';

            // Combine all education sample records
            const allRecords = [].concat(
                SP_EDUCATION_SAMPLE['Support to ALS and RIC'] || [],
                SP_EDUCATION_SAMPLE['150 Students for Educational Assistance'] || [],
                SP_EDUCATION_SAMPLE['Support to Elementary and Daycare'] || []
            );

            // Update section title
            const titleEl    = document.getElementById('spPassedSectionTitle');
            const subtitleEl = document.getElementById('spPassedSectionSubtitle');
            if (titleEl)    titleEl.textContent    = 'Passed Scholars';
            if (subtitleEl) subtitleEl.textContent = 'All approved and passed scholars across all education activities.';

            // Render table rows
            const tbody = document.getElementById('spPassedTableBody');
            if (tbody) {
                tbody.innerHTML = allRecords.map((r, i) => {
                    const fullName = `${r.last_name || ''}, ${r.first_name || ''}${r.middle_name ? ' ' + r.middle_name.charAt(0) + '.' : ''}${r.suffix ? ' ' + r.suffix : ''}`;
                    return `
                    <tr>
                        <td style="text-align:center;font-weight:600;">${fullName}</td>
                        <td style="text-align:center;font-size:12px;">${r.school_name || '—'}</td>
                        <td style="text-align:center;">${r.year_level || '—'}</td>
                        <td style="text-align:center;font-size:12px;">${r.program_strand || '—'}</td>
                        <td style="text-align:center;font-size:12px;">${r.purpose || '—'}</td>
                        <td style="text-align:center;">${r.approved_at || '—'}</td>
                        <td style="text-align:center;"><span class="sp-status-badge completed">Passed</span></td>
                        <td style="text-align:center;">
                            <div class="sp-actions">
                                <button class="sp-btn sp-btn-edit" data-passed-idx="${i}" style="background:#2c2c3e;">View</button>
                            </div>
                        </td>
                    </tr>`;
                }).join('');

                tbody.querySelectorAll('button[data-passed-idx]').forEach(btn => {
                    btn.addEventListener('click', function () {
                        const idx = parseInt(this.getAttribute('data-passed-idx'), 10);
                        if (allRecords[idx]) openPassedScholarModal(allRecords[idx]);
                    });
                });
            }

            // Show the section and scroll to it
            const passedSection = document.getElementById('spPassedSection');
            if (passedSection) {
                passedSection.style.display = '';
                setTimeout(() => passedSection.scrollIntoView({ behavior: 'smooth', block: 'start' }), 80);
            }
        });
    }
});

// ── Committee Cards Handler ────────────────────────────────────────────────
function initializeCommitteeCards() {
    const committeeCards     = document.querySelectorAll('.committee-card');
    const activityBtnsPanel  = document.getElementById('spActivityButtonsPanel');
    const activityPanelTitle = document.getElementById('spActivityPanelTitle');
    const activityBtnGroup   = document.getElementById('spActivityBtnGroup');
    const passedSection      = document.getElementById('spPassedSection');
    const sportsSection      = document.getElementById('spSportsSection');
    const scholarshipLink    = document.getElementById('spScholarshipLink');

    // Committee metadata
    const committeeData = {
        education: {
            title: 'Equitable Access to Quality Education',
            activities: [
                'Support to ALS and RIC',
                '150 Students for Educational Assistance',
                'Support to Elementary and Daycare',
            ],
            link: '/scholarship',
            linkLabel: 'Go to Scholarship Application List',
            type: 'education',
        },
        environment: {
            title: 'Environmental Protection',
            activities: ['Clean-Up Drive', 'Payroll for Laborer', 'Tree Planting'],
            type: 'other',
        },
        disaster: {
            title: 'Disaster Risk Reduction and Resiliency',
            activities: [
                'Training on Disaster Preparedness for Youth Volunteer Groups',
                'Distribution of Relief Goods for KK Members',
            ],
            type: 'other',
        },
        livelihood: {
            title: 'Youth Employment and Livelihood',
            activities: ['Livelihood Training', 'Food and Other Supplies'],
            type: 'other',
        },
        health: {
            title: 'Health',
            activities: [
                'Medicines / Medical Equipment',
                'Campaigning Materials for Anti-Drugs (Leaflets, Posters, Tarpaulins)',
            ],
            type: 'other',
        },
        'anti-drug': {
            title: 'Anti-Drug and Peace and Order',
            activities: ['Orientation for Anti-Drug and Physical Abuse', 'Foods and Accommodations'],
            type: 'other',
        },
        gender: {
            title: 'Gender Sensitivity',
            activities: ['Orientation on GAD and VAWC', 'Foods and Accommodations'],
            type: 'other',
        },
        feeding: {
            title: 'Feeding Program for KK Members',
            activities: [
                'Improve health and physique of children',
                'Youth and Children in the vicinity of Barangay',
            ],
            type: 'other',
        },
        sports: {
            title: 'Sports Development',
            activities: ['Supplies and Materials', 'Food and Accommodation', 'Officiating Fees'],
            link: '/sports-requests',
            linkLabel: 'Go to Sports Application Requests',
            type: 'sports',
        },
        other: {
            title: 'Other Programs',
            activities: [
                'Katipunan ng Kabataan (KK) General Assembly',
                'Barangay Day Celebration',
                'Youth Week',
            ],
            type: 'other',
        },
    };

    function hideAllTables() {
        if (passedSection)  passedSection.style.display  = 'none';
        if (sportsSection)  sportsSection.style.display  = 'none';
    }

    function buildActivityButtons(data) {
        if (!activityBtnGroup || !activityBtnsPanel || !activityPanelTitle) return;

        activityPanelTitle.textContent = data.title;
        activityBtnGroup.innerHTML = '';

        data.activities.forEach(activity => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'sp-activity-action-btn';
            btn.textContent = activity;
            btn.setAttribute('data-activity', activity);
            btn.setAttribute('data-committee-type', data.type);

            btn.addEventListener('click', function () {
                // Highlight active activity button
                activityBtnGroup.querySelectorAll('.sp-activity-action-btn')
                    .forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                const type = this.getAttribute('data-committee-type');
                const act  = this.getAttribute('data-activity');

                hideAllTables();

                if (type === 'sports') {
                    renderSportsPrograms(act);
                    const titleEl    = document.getElementById('spSportsSectionTitle');
                    const subtitleEl = document.getElementById('spSportsSectionSubtitle');
                    if (titleEl)    titleEl.textContent    = `Sports Development — ${act}`;
                    if (subtitleEl) subtitleEl.textContent = `Records for ${act} under Sports Development.`;
                    if (sportsSection) {
                        sportsSection.style.display = '';
                        setTimeout(() => sportsSection.scrollIntoView({ behavior: 'smooth', block: 'start' }), 80);
                    }
                } else if (type === 'education') {
                    renderEducationActivity(act);
                    const titleEl    = document.getElementById('spPassedSectionTitle');
                    const subtitleEl = document.getElementById('spPassedSectionSubtitle');
                    if (titleEl)    titleEl.textContent    = act;
                    if (subtitleEl) subtitleEl.textContent = `Records for ${act} under Equitable Access to Quality Education.`;
                    // Show/hide scholarship link for ALS/150 Students activities
                    if (scholarshipLink) {
                        if (act === '150 Students for Educational Assistance') {
                            scholarshipLink.href         = '/scholarship';
                            scholarshipLink.style.display = 'inline-flex';
                        } else {
                            scholarshipLink.style.display = 'none';
                        }
                    }
                    if (passedSection) {
                        passedSection.style.display = '';
                        setTimeout(() => passedSection.scrollIntoView({ behavior: 'smooth', block: 'start' }), 80);
                    }
                }
            });

            activityBtnGroup.appendChild(btn);
        });

        activityBtnsPanel.style.display = '';
        setTimeout(() => activityBtnsPanel.scrollIntoView({ behavior: 'smooth', block: 'start' }), 80);
    }

    committeeCards.forEach(card => {
        card.addEventListener('click', function () {
            const committee = this.getAttribute('data-committee');
            const data      = committeeData[committee];
            if (!data) return;

            // Highlight active card
            committeeCards.forEach(c => c.classList.remove('committee-active'));
            this.classList.add('committee-active');

            // Hide all tables and reset activity buttons
            hideAllTables();
            if (activityBtnsPanel) activityBtnsPanel.style.display = 'none';

            if (data.type === 'education') {
                // Build activity filter buttons
                buildActivityButtons(data);

                // Immediately show ALL passed scholars (all activities combined)
                const allRecords = [].concat(
                    SP_EDUCATION_SAMPLE['Support to ALS and RIC'] || [],
                    SP_EDUCATION_SAMPLE['150 Students for Educational Assistance'] || [],
                    SP_EDUCATION_SAMPLE['Support to Elementary and Daycare'] || []
                );

                const titleEl    = document.getElementById('spPassedSectionTitle');
                const subtitleEl = document.getElementById('spPassedSectionSubtitle');
                if (titleEl)    titleEl.textContent    = 'Equitable Access to Quality Education';
                if (subtitleEl) subtitleEl.textContent = 'All approved and passed scholars. Click an activity button above to filter.';

                // Reset thead to default
                const thead = document.getElementById('spPassedTableHead');
                if (thead) {
                    thead.innerHTML = `<tr>
                        <th>FULL NAME<div class="column-hint" style="font-size:9px;font-weight:400;color:rgba(255,255,255,0.75);text-transform:none;letter-spacing:0.02em;margin-top:2px;">LN, FN, MN, Suffix</div></th>
                        <th>School</th>
                        <th>Year / Level</th>
                        <th>Program / Strand</th>
                        <th>Purpose</th>
                        <th>Date Approved</th>
                        <th>Result</th>
                        <th class="col-actions">Actions</th>
                    </tr>`;
                }

                const tbody = document.getElementById('spPassedTableBody');
                if (tbody) {
                    tbody.innerHTML = allRecords.map((r, i) => {
                        const fullName = `${r.last_name || ''}, ${r.first_name || ''}${r.middle_name ? ' ' + r.middle_name.charAt(0) + '.' : ''}${r.suffix ? ' ' + r.suffix : ''}`;
                        return `
                        <tr>
                            <td style="text-align:center;font-weight:600;">${fullName}</td>
                            <td style="text-align:center;font-size:12px;">${r.school_name || '—'}</td>
                            <td style="text-align:center;">${r.year_level || '—'}</td>
                            <td style="text-align:center;font-size:12px;">${r.program_strand || '—'}</td>
                            <td style="text-align:center;font-size:12px;">${r.purpose || '—'}</td>
                            <td style="text-align:center;">${r.approved_at || '—'}</td>
                            <td style="text-align:center;"><span class="sp-status-badge completed">Passed</span></td>
                            <td style="text-align:center;">
                                <div class="sp-actions">
                                    <button class="sp-btn sp-btn-edit" data-passed-idx="${i}" style="background:#2c2c3e;">View</button>
                                </div>
                            </td>
                        </tr>`;
                    }).join('');

                    tbody.querySelectorAll('button[data-passed-idx]').forEach(btn => {
                        btn.addEventListener('click', function () {
                            const idx = parseInt(this.getAttribute('data-passed-idx'), 10);
                            if (allRecords[idx]) openPassedScholarModal(allRecords[idx]);
                        });
                    });
                }

                if (passedSection) {
                    passedSection.style.display = '';
                    setTimeout(() => passedSection.scrollIntoView({ behavior: 'smooth', block: 'start' }), 80);
                }

            } else if (data.type === 'sports') {
                // Build activity filter buttons
                buildActivityButtons(data);

                // Immediately show ALL sports records
                renderSportsPrograms(null);

                const titleEl    = document.getElementById('spSportsSectionTitle');
                const subtitleEl = document.getElementById('spSportsSectionSubtitle');
                if (titleEl)    titleEl.textContent    = 'Sports Development';
                if (subtitleEl) subtitleEl.textContent = 'All approved sports applications. Click an activity button above to filter.';

                if (sportsSection) {
                    sportsSection.style.display = '';
                    setTimeout(() => sportsSection.scrollIntoView({ behavior: 'smooth', block: 'start' }), 80);
                }
            }
            // Other committees: no table shown
        });
    });
}

// ── Passed Scholars Table ─────────────────────────────────────────────────
// Static sample data — always displayed regardless of localStorage state
const SP_PASSED_SAMPLE = [
    {
        last_name: 'Reyes', first_name: 'Maria', middle_name: 'Santos', suffix: '',
        date_of_birth: '2005-03-14', gender: 'Female', age: 20,
        contact_no: '09171234567',
        address: '123 Sampaguita St., Brgy. Calios, Santa Cruz, Laguna',
        email: 'maria.reyes@email.com',
        school_name: 'Laguna State Polytechnic University',
        school_address: 'Brgy. Siniloan, Siniloan, Laguna 4019',
        year_level: '2nd Year', program_strand: 'Bachelor of Secondary Education (BSED)',
        purpose: 'Tuition Fees, Books / Equipments',
        purpose_list: ['Tuition Fees', 'Books / Equipments'],
        purpose_others: '',
        cor_certified: true, photo_id: true,
        approved_at: 'Jan 15, 2025',
    },
    {
        last_name: 'Dela Cruz', first_name: 'Jose', middle_name: 'Ramos', suffix: 'Jr.',
        date_of_birth: '2004-11-20', gender: 'Male', age: 21,
        contact_no: '09721234567',
        address: '88 Magsaysay St., Brgy. Calios, Santa Cruz, Laguna',
        email: 'jose.delacruz@email.com',
        school_name: 'Laguna State Polytechnic University',
        school_address: 'Brgy. Siniloan, Siniloan, Laguna 4019',
        year_level: '3rd Year', program_strand: 'Bachelor of Science in Information Technology (BSIT)',
        purpose: 'Tuition Fees, Living Expenses',
        purpose_list: ['Tuition Fees', 'Living Expenses'],
        purpose_others: '',
        cor_certified: true, photo_id: true,
        approved_at: 'Jan 25, 2025',
    },
    {
        last_name: 'Bautista', first_name: 'Kristine', middle_name: 'Flores', suffix: '',
        date_of_birth: '2005-06-08', gender: 'Female', age: 20,
        contact_no: '09831234567',
        address: '14 Quezon Blvd., Brgy. Calios, Santa Cruz, Laguna',
        email: 'kristine.bautista@email.com',
        school_name: 'De La Salle University – Dasmariñas',
        school_address: 'Brgy. Salitran, Dasmariñas, Cavite 4114',
        year_level: '2nd Year', program_strand: 'Bachelor of Science in Nursing (BSN)',
        purpose: 'Tuition Fees, Books / Equipments',
        purpose_list: ['Tuition Fees', 'Books / Equipments'],
        purpose_others: '',
        cor_certified: true, photo_id: true,
        approved_at: 'Feb 10, 2025',
    },
    {
        last_name: 'Santos', first_name: 'Mark', middle_name: 'Villanueva', suffix: '',
        date_of_birth: '2003-09-15', gender: 'Male', age: 22,
        contact_no: '09941234567',
        address: '22 Rizal Ave., Brgy. Calios, Santa Cruz, Laguna',
        email: 'mark.santos@email.com',
        school_name: 'University of the Philippines Los Baños',
        school_address: 'Brgy. College, Los Baños, Laguna 4031',
        year_level: '4th Year', program_strand: 'Bachelor of Science in Computer Science (BSCS)',
        purpose: 'Tuition Fees, Living Expenses',
        purpose_list: ['Tuition Fees', 'Living Expenses'],
        purpose_others: '',
        cor_certified: true, photo_id: false,
        approved_at: 'Feb 20, 2025',
    },
    {
        last_name: 'Lim', first_name: 'Angela', middle_name: 'Cruz', suffix: '',
        date_of_birth: '2007-04-22', gender: 'Female', age: 18,
        contact_no: '09051234567',
        address: '5 Mabini St., Brgy. Calios, Santa Cruz, Laguna',
        email: 'angela.lim@email.com',
        school_name: 'Santa Cruz National High School',
        school_address: 'Brgy. Poblacion, Santa Cruz, Laguna 4009',
        year_level: 'Grade 12', program_strand: 'Science, Technology, Engineering and Mathematics (STEM)',
        purpose: 'Books / Equipments',
        purpose_list: ['Books / Equipments'],
        purpose_others: '',
        cor_certified: false, photo_id: true,
        approved_at: 'Mar 5, 2025',
    },
];

function renderPassedScholars() {
    const tbody = document.getElementById('spPassedTableBody');
    if (!tbody) return;

    // Merge static sample with any live Passed records from localStorage
    const stored = JSON.parse(localStorage.getItem('scholarship_requests') || '[]');
    const livePassed = stored.filter(r => r.status === 'Approved' && r.result === 'Passed');

    // Use live data if available, otherwise always show static sample
    const passed = livePassed.length > 0 ? livePassed : SP_PASSED_SAMPLE;

    tbody.innerHTML = passed.map((r, i) => {
        const fullName = `${r.last_name || ''}, ${r.first_name || ''}${r.middle_name ? ' ' + r.middle_name.charAt(0) + '.' : ''}${r.suffix ? ' ' + r.suffix : ''}`;
        return `
        <tr>
            <td style="text-align:center;font-weight:600;">${fullName}</td>
            <td style="text-align:center;font-size:12px;">${r.school_name || '—'}</td>
            <td style="text-align:center;">${r.year_level || '—'}</td>
            <td style="text-align:center;font-size:12px;">${r.program_strand || '—'}</td>
            <td style="text-align:center;font-size:12px;">${r.purpose || '—'}</td>
            <td style="text-align:center;">${r.approved_at || '—'}</td>
            <td style="text-align:center;"><span class="sp-status-badge completed">Passed</span></td>
            <td style="text-align:center;">
                <div class="sp-actions">
                    <button class="sp-btn sp-btn-edit" data-passed-idx="${i}" style="background:#2c2c3e;">View</button>
                </div>
            </td>
        </tr>`;
    }).join('');

    // View button — open passed scholar detail modal
    tbody.querySelectorAll('button[data-passed-idx]').forEach(btn => {
        btn.addEventListener('click', function () {
            const idx    = parseInt(this.getAttribute('data-passed-idx'), 10);
            const record = passed[idx];
            if (!record) return;
            openPassedScholarModal(record);
        });
    });

    // ── Export to CSV ────────────────────────────────────────────────────────
    const exportBtn = document.getElementById('spExportCsvBtn');
    if (exportBtn) {
        // Remove old listener by cloning
        const newBtn = exportBtn.cloneNode(true);
        exportBtn.parentNode.replaceChild(newBtn, exportBtn);
        newBtn.addEventListener('click', () => exportPassedToCsv(passed));
    }
}

// ── Passed Scholar View Modal — PDF exact layout ──────────────────────────
function openPassedScholarModal(r) {
    const modal = document.getElementById('spPassedViewModal');
    const body  = document.getElementById('spPassedViewBody');
    if (!modal || !body) return;

    const allPurposes = ['Tuition Fees', 'Books/Equipments', 'Living Expenses', 'Others'];
    const purposeList = r.purpose_list || [];

    const purposeHTML = allPurposes.map(p => {
        const checked = purposeList.some(v => v.toLowerCase().replace(/\s/g,'') === p.toLowerCase().replace(/\s/g,''));
        const extra   = (p === 'Others' && r.purpose_others) ? ` (${r.purpose_others})` : '';
        const box     = checked
            ? `<span style="width:13px;height:13px;border:1.5px solid #2c2c3e;background:#2c2c3e;border-radius:1px;display:inline-block;position:relative;flex-shrink:0;"><span style="position:absolute;left:2px;top:-1px;width:5px;height:9px;border:2px solid #fff;border-top:none;border-left:none;transform:rotate(45deg);display:block;"></span></span>`
            : `<span style="width:13px;height:13px;border:1.5px solid #374151;background:#fff;border-radius:1px;display:inline-block;flex-shrink:0;"></span>`;
        return `<div style="display:flex;align-items:center;gap:7px;font-size:11px;font-weight:600;color:#111827;margin-bottom:5px;">${box} ${p}${extra}</div>`;
    }).join('');

    const corBox   = r.cor_certified
        ? `<span style="width:13px;height:13px;border:1.5px solid #2c2c3e;background:#2c2c3e;border-radius:1px;display:inline-block;position:relative;flex-shrink:0;"><span style="position:absolute;left:2px;top:-1px;width:5px;height:9px;border:2px solid #fff;border-top:none;border-left:none;transform:rotate(45deg);display:block;"></span></span>`
        : `<span style="width:13px;height:13px;border:1.5px solid #374151;background:#fff;border-radius:1px;display:inline-block;flex-shrink:0;"></span>`;
    const photoBox = r.photo_id
        ? `<span style="width:13px;height:13px;border:1.5px solid #2c2c3e;background:#2c2c3e;border-radius:1px;display:inline-block;position:relative;flex-shrink:0;"><span style="position:absolute;left:2px;top:-1px;width:5px;height:9px;border:2px solid #fff;border-top:none;border-left:none;transform:rotate(45deg);display:block;"></span></span>`
        : `<span style="width:13px;height:13px;border:1.5px solid #374151;background:#fff;border-radius:1px;display:inline-block;flex-shrink:0;"></span>`;

    const line = (w) => `<span style="border-bottom:1.5px solid #374151;display:inline-block;min-width:${w}px;padding:0 4px 2px;font-size:11px;font-weight:700;color:#111827;"></span>`;
    const filled = (val, w) => `<span style="border-bottom:1.5px solid #374151;display:inline-block;min-width:${w||80}px;padding:0 4px 2px;font-size:11px;font-weight:700;color:#111827;">${val||'—'}</span>`;
    const label  = (txt) => `<span style="font-size:11px;font-weight:600;color:#111827;white-space:nowrap;flex-shrink:0;padding-bottom:2px;">${txt}</span>`;
    const row    = (content) => `<div style="display:flex;align-items:flex-end;gap:6px;margin-bottom:8px;flex-wrap:wrap;">${content}</div>`;
    const sectionTitle = (txt) => `<p style="font-size:11px;font-weight:800;color:#111827;text-decoration:underline;text-transform:uppercase;margin-bottom:8px;letter-spacing:0.02em;">${txt}</p>`;

    // Sample PDF file links (simulated)
    const samplePdfUrl = 'data:application/pdf;base64,JVBERi0xLjQKJcOkw7zDtsOfCjIgMCBvYmoKPDwvTGVuZ3RoIDMgMCBSL0ZpbHRlci9GbGF0ZURlY29kZT4+CnN0cmVhbQp4nCtUMlQyULIGAAMiAWUKZW5kc3RyZWFtCmVuZG9iagozIDAgb2JqCjE4CmVuZG9iagoxIDAgb2JqCjw8L1R5cGUvUGFnZS9NZWRpYUJveFswIDAgNjEyIDc5Ml0vUmVzb3VyY2VzPDwvRm9udDw8L0YxIDQgMCBSPj4+Pi9Db250ZW50cyAyIDAgUi9QYXJlbnQgNSAwIFI+PgplbmRvYmoKNCAwIG9iago8PC9UeXBlL0ZvbnQvU3VidHlwZS9UeXBlMS9CYXNlRm9udC9IZWx2ZXRpY2E+PgplbmRvYmoKNSAwIG9iago8PC9UeXBlL1BhZ2VzL0tpZHNbMSAwIFJdL0NvdW50IDE+PgplbmRvYmoKNiAwIG9iago8PC9UeXBlL0NhdGFsb2cvUGFnZXMgNSAwIFI+PgplbmRvYmoKeHJlZgowIDcKMDAwMDAwMDAwMCA2NTUzNSBmIAowMDAwMDAwMTI1IDAwMDAwIG4gCjAwMDAwMDAwMTUgMDAwMDAgbiAKMDAwMDAwMDEwNiAwMDAwMCBuIAowMDAwMDAwMjQ0IDAwMDAwIG4gCjAwMDAwMDAzMTMgMDAwMDAgbiAKMDAwMDAwMDM2NiAwMDAwMCBuIAp0cmFpbGVyCjw8L1NpemUgNy9Sb290IDYgMCBSPj4Kc3RhcnR4cmVmCjQxNQolJUVPRgo=';

    body.innerHTML = `
    <div style="background:#fff;border-radius:10px;padding:18px 22px;border:1px solid #e5e7eb;font-family:'Segoe UI',sans-serif;">

        <!-- PDF Header: Logo | Title | Picture Here (barangay logo as photo) -->
        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid #d1d5db;">
            <img src="/images/barangay_logo.png" alt="Barangay Calios" style="width:60px;height:60px;object-fit:contain;border-radius:50%;flex-shrink:0;" onerror="this.style.display='none'">
            <h2 style="font-size:15px;font-weight:900;color:#111827;text-align:center;flex:1;letter-spacing:0.02em;">SCHOLARSHIP APPLICATION FORM</h2>
            <!-- Picture Here — shows barangay logo as sample photo -->
            <div style="width:80px;height:90px;border:2px solid #374151;border-radius:2px;flex-shrink:0;overflow:hidden;display:flex;align-items:center;justify-content:center;background:#f9fafb;">
                <img src="/images/barangay_logo.png" alt="Picture Here"
                     style="width:100%;height:100%;object-fit:cover;"
                     onerror="this.outerHTML='<span style=\\'font-size:11px;font-weight:600;color:#374151;text-align:center;padding:4px;\\'>Picture<br>Here</span>'">
            </div>
        </div>

        <!-- Date row -->
        <div style="display:flex;justify-content:flex-end;margin-bottom:10px;">
            ${label('Date:')} ${filled(r.approved_at, 100)}
        </div>

        <!-- APPLICANT'S PERSONAL INFORMATION -->
        <div style="margin-bottom:12px;padding-bottom:12px;border-bottom:1px solid #e5e7eb;">
            ${sectionTitle('APPLICANT\'S PERSONAL INFORMATION:')}
            ${row(`${label('Last Name:')} ${filled(r.last_name,100)} ${label('First Name:')} ${filled(r.first_name,100)} ${label('Middle Name:')} ${filled(r.middle_name,90)}`)}
            ${row(`${label('Date of Birth:')} ${filled(r.date_of_birth,80)} ${label('Gender:')} ${filled(r.gender,60)} ${label('Age:')} ${filled(r.age,35)} ${label('Contact No:')} ${filled(r.contact_no,100)}`)}
            ${row(`${label('Complete Address:')} <span style="border-bottom:1.5px solid #374151;flex:1;min-width:100px;font-size:11px;font-weight:700;color:#111827;padding:0 4px 2px;">${r.address||'—'}</span>`)}
            ${row(`${label('Email Address:')} ${filled(r.email,200)}`)}
        </div>

        <!-- ACADEMIC INFORMATION -->
        <div style="margin-bottom:12px;padding-bottom:12px;border-bottom:1px solid #e5e7eb;">
            ${sectionTitle('ACADEMIC INFORMATION:')}
            ${row(`${label('Name of School:')} <span style="border-bottom:1.5px solid #374151;flex:1;min-width:100px;font-size:11px;font-weight:700;color:#111827;padding:0 4px 2px;">${r.school_name||'—'}</span>`)}
            ${row(`${label('School Address:')} <span style="border-bottom:1.5px solid #374151;flex:1;min-width:100px;font-size:11px;font-weight:700;color:#111827;padding:0 4px 2px;">${r.school_address||'—'}</span>`)}
            ${row(`${label('Year/Grade Level:')} ${filled(r.year_level,100)} <span style="margin-left:14px;"></span> ${label('Program/Strand:')} ${filled(r.program_strand,100)}`)}
        </div>

        <!-- SCHOLARSHIP INFO + SUBMITTED REQUIREMENTS side by side -->
        <div style="display:flex;gap:28px;align-items:flex-start;margin-bottom:12px;padding-bottom:12px;border-bottom:1px solid #e5e7eb;">
            <div style="flex:1;">
                ${sectionTitle('SCHOLARSHIP INFORMATION:')}
                <p style="font-size:11px;font-weight:600;color:#111827;margin-bottom:6px;">Purpose of Scholarship:</p>
                ${purposeHTML}
            </div>
            <div style="flex:1;">
                ${sectionTitle('SUBMITTED REQUIREMENTS:')}
                <p style="font-size:10px;color:#6b7280;margin-bottom:10px;">Note: To be filled out by SK officials</p>

                <!-- COR -->
                <div style="margin-bottom:10px;">
                    <div style="display:flex;align-items:center;gap:7px;font-size:11px;font-weight:600;color:#111827;margin-bottom:6px;">
                        ${corBox}
                        COR – CERTIFIED TRUE COPY
                    </div>
                    ${r.cor_certified ? `
                    <a href="${samplePdfUrl}" download="COR-sample.pdf"
                       style="display:inline-flex;align-items:center;gap:8px;background:#eff6ff;border:1.5px solid #bfdbfe;border-radius:8px;padding:7px 12px;text-decoration:none;cursor:pointer;transition:background 0.2s;margin-left:20px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
                        <div>
                            <div style="font-size:11px;font-weight:700;color:#1d4ed8;">COR-Certified-True-Copy.pdf</div>
                            <div style="font-size:10px;color:#6b7280;">Click to download</div>
                        </div>
                    </a>` : `<span style="margin-left:20px;font-size:10px;color:#9ca3af;font-style:italic;">No file submitted</span>`}
                </div>

                <!-- Photo ID -->
                <div>
                    <div style="display:flex;align-items:center;gap:7px;font-size:11px;font-weight:600;color:#111827;margin-bottom:6px;">
                        ${photoBox}
                        PHOTO COPY OF ID (FRONT AND BACK)
                    </div>
                    ${r.photo_id ? `
                    <a href="${samplePdfUrl}" download="PhotoID-sample.pdf"
                       style="display:inline-flex;align-items:center;gap:8px;background:#eff6ff;border:1.5px solid #bfdbfe;border-radius:8px;padding:7px 12px;text-decoration:none;cursor:pointer;transition:background 0.2s;margin-left:20px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                        <div>
                            <div style="font-size:11px;font-weight:700;color:#1d4ed8;">Photo-ID-Front-Back.pdf</div>
                            <div style="font-size:10px;color:#6b7280;">Click to download</div>
                        </div>
                    </a>` : `<span style="margin-left:20px;font-size:10px;color:#9ca3af;font-style:italic;">No file submitted</span>`}
                </div>
            </div>
        </div>

        <!-- Signature -->
        <div style="text-align:center;padding-top:20px;">
            <!-- Cursive signature and printed name stacked above the line -->
            <div style="width:280px;margin:0 auto;display:flex;flex-direction:column;align-items:center;justify-content:flex-end;min-height:70px;">
                <!-- Cursive signature -->
                <div style="margin-bottom:2px;">
                    <span style="font-family:'Brush Script MT','Segoe Script','Comic Sans MS',cursive;font-size:28px;color:#1e3a5f;line-height:1;letter-spacing:1px;white-space:nowrap;transform:rotate(-3deg);display:inline-block;">
                        ${r.first_name||''} ${r.middle_name ? r.middle_name.charAt(0) + '. ' : ''}${r.last_name||''}
                    </span>
                </div>
                <!-- Printed full name -->
                <p style="font-size:11px;font-weight:700;color:#111827;text-transform:uppercase;letter-spacing:0.5px;margin:0;padding-bottom:4px;">
                    ${r.first_name||''} ${r.middle_name ? r.middle_name.charAt(0) + '.' : ''} ${r.last_name||''}${r.suffix ? ' ' + r.suffix : ''}
                </p>
            </div>
            <div style="border-bottom:2px solid #374151;width:280px;margin:0 auto 4px;"></div>
            <p style="font-size:10px;color:#6b7280;margin-top:2px;">Signature over printed name</p>
        </div>

    </div>
    `;

    modal.style.display = 'flex';

    const closeModal = () => {
        modal.style.display = 'none';
        modal.classList.remove('sp-modal-maximized');
        box.classList.remove('sp-modal-maximized');
        const maxBtn = document.getElementById('spPassedViewMaximize');
        if (maxBtn) maxBtn.textContent = '□';
    };
    const box = document.getElementById('spPassedViewBox');
    const maxBtn = document.getElementById('spPassedViewMaximize');
    if (maxBtn && box) {
        maxBtn.onclick = function(e) {
            e.stopPropagation();
            const isMax = !box.classList.contains('sp-modal-maximized');
            modal.classList.toggle('sp-modal-maximized', isMax);
            box.classList.toggle('sp-modal-maximized', isMax);
            maxBtn.textContent = isMax ? '⧉' : '□';
        };
    }
    document.getElementById('spPassedViewClose').onclick = closeModal;
    modal.onclick = e => { if (e.target === modal) closeModal(); };
}

// ── Export Passed Scholars to CSV ─────────────────────────────────────────
function exportPassedToCsv(passed) {
    const headers = [
        'Last Name', 'First Name', 'Middle Name', 'Suffix',
        'Date of Birth', 'Gender', 'Age', 'Contact No',
        'Address', 'Email', 'School Name', 'School Address',
        'Year/Level', 'Program/Strand', 'Purpose',
        'COR Certified', 'Photo ID', 'Date Approved', 'Result'
    ];
    const rows = passed.map(r => [
        r.last_name      || '',
        r.first_name     || '',
        r.middle_name    || '',
        r.suffix         || '',
        r.date_of_birth  || '',
        r.gender         || '',
        r.age            || '',
        r.contact_no     || '',
        r.address        || '',
        r.email          || '',
        r.school_name    || '',
        r.school_address || '',
        r.year_level     || '',
        r.program_strand || '',
        r.purpose        || '',
        r.cor_certified  ? 'Yes' : 'No',
        r.photo_id       ? 'Yes' : 'No',
        r.approved_at    || '',
        'Passed',
    ]);

    const csvContent = [headers, ...rows]
        .map(row => row.map(cell => `"${String(cell).replace(/"/g, '""')}"`).join(','))
        .join('\n');

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href     = url;
    a.download = 'passed-scholars.csv';
    a.click();
    URL.revokeObjectURL(url);
}

// ── Education Activity Table Renderer ─────────────────────────────────────
function renderEducationActivity(activity) {
    const tbody = document.getElementById('spPassedTableBody');
    if (!tbody) return;

    // Always use per-activity sample data (live data integration can be added later)
    const records = SP_EDUCATION_SAMPLE[activity] || SP_PASSED_SAMPLE;

    // Update table header based on activity
    const thead = document.getElementById('spPassedTableHead');
    if (thead) {
        if (activity === 'Support to Elementary and Daycare') {
            thead.innerHTML = `<tr>
                <th>FULL NAME<div class="column-hint" style="font-size:9px;font-weight:400;color:rgba(255,255,255,0.75);text-transform:none;letter-spacing:0.02em;margin-top:2px;">LN, FN, MN, Suffix</div></th>
                <th>School</th>
                <th>Year / Level</th>
                <th>Program / Strand</th>
                <th>Purpose</th>
                <th>Date Approved</th>
                <th>Status</th>
                <th class="col-actions">Actions</th>
            </tr>`;
        } else {
            thead.innerHTML = `<tr>
                <th>FULL NAME<div class="column-hint" style="font-size:9px;font-weight:400;color:rgba(255,255,255,0.75);text-transform:none;letter-spacing:0.02em;margin-top:2px;">LN, FN, MN, Suffix</div></th>
                <th>School</th>
                <th>Year / Level</th>
                <th>Program / Strand</th>
                <th>Purpose</th>
                <th>Date Approved</th>
                <th>Result</th>
                <th class="col-actions">Actions</th>
            </tr>`;
        }
    }

    tbody.innerHTML = records.map((r, i) => {
        const fullName = `${r.last_name || ''}, ${r.first_name || ''}${r.middle_name ? ' ' + r.middle_name.charAt(0) + '.' : ''}${r.suffix ? ' ' + r.suffix : ''}`;
        const resultLabel = activity === 'Support to Elementary and Daycare' ? 'Approved' : 'Passed';
        return `
        <tr>
            <td style="text-align:center;font-weight:600;">${fullName}</td>
            <td style="text-align:center;font-size:12px;">${r.school_name || '—'}</td>
            <td style="text-align:center;">${r.year_level || '—'}</td>
            <td style="text-align:center;font-size:12px;">${r.program_strand || '—'}</td>
            <td style="text-align:center;font-size:12px;">${r.purpose || '—'}</td>
            <td style="text-align:center;">${r.approved_at || '—'}</td>
            <td style="text-align:center;"><span class="sp-status-badge completed">${resultLabel}</span></td>
            <td style="text-align:center;">
                <div class="sp-actions">
                    <button class="sp-btn sp-btn-edit" data-passed-idx="${i}" style="background:#2c2c3e;">View</button>
                </div>
            </td>
        </tr>`;
    }).join('');

    // View button
    tbody.querySelectorAll('button[data-passed-idx]').forEach(btn => {
        btn.addEventListener('click', function () {
            const idx    = parseInt(this.getAttribute('data-passed-idx'), 10);
            const record = records[idx];
            if (!record) return;
            openPassedScholarModal(record);
        });
    });

    // Export CSV button
    const exportBtn = document.getElementById('spExportCsvBtn');
    if (exportBtn) {
        const newBtn = exportBtn.cloneNode(true);
        newBtn.id = 'spExportCsvBtn';
        exportBtn.parentNode.replaceChild(newBtn, exportBtn);
        newBtn.addEventListener('click', () => exportPassedToCsv(records));
    }
}

// ── Approved Sports Programs ───────────────────────────────────────────────
// Static sample data — always displayed regardless of localStorage state
const SP_SPORTS_SAMPLE = [
    {
        id: 1,
        fullName: "Dela Cruz, Juan S.",
        programName: "Basketball Tournament 2026",
        sport: "Basketball",
        division: "Youth Division (18-21)",
        startDate: "2026-05-15",
        endDate: "2026-05-17",
        startTime: "08:00",
        endTime: "17:00",
        status: "Approved",
        payment: "Paid"
    },
    {
        id: 2,
        fullName: "Santos, Maria R.",
        programName: "Volleyball League 2026",
        sport: "Volleyball",
        division: "Young Adult (22-25)",
        startDate: "2026-05-20",
        endDate: "2026-05-22",
        startTime: "08:00",
        endTime: "17:00",
        status: "Approved",
        payment: "Paid"
    },
    {
        id: 3,
        fullName: "Reyes, Pedro M.",
        programName: "Youth Basketball Camp",
        sport: "Basketball",
        division: "Cadet Division (15-17)",
        startDate: "2026-05-25",
        endDate: "2026-05-27",
        startTime: "09:00",
        endTime: "16:00",
        status: "Approved",
        payment: "Paid"
    },
    {
        id: 4,
        fullName: "Garcia, Ana L.",
        programName: "Volleyball Training",
        sport: "Volleyball",
        division: "Youth Division (18-21)",
        startDate: "2026-05-29",
        endDate: "2026-05-31",
        startTime: "09:00",
        endTime: "16:00",
        status: "Approved",
        payment: "Paid"
    },
    {
        id: 5,
        fullName: "Torres, Carlos V.",
        programName: "Sports Festival 2026",
        sport: "Basketball",
        division: "Senior Division (26-30)",
        startDate: "2026-06-01",
        endDate: "2026-06-03",
        startTime: "08:00",
        endTime: "18:00",
        status: "Approved",
        payment: "Paid"
    }
];

function renderSportsPrograms(activity) {
    const tbody = document.getElementById('spSportsTableBody');
    if (!tbody) return;

    // Always use sample data — show all records (activity param reserved for future filtering)
    const sports = SP_SPORTS_SAMPLE;

    tbody.innerHTML = sports.map((s, i) => {
        const schedule = formatSchedule(s);
        return `
        <tr>
            <td style="text-align:center;font-weight:600;">${s.fullName || '—'}</td>
            <td style="text-align:center;font-size:12px;">${s.programName || '—'}</td>
            <td style="text-align:center;">${s.sport || '—'}</td>
            <td style="text-align:center;font-size:12px;">${s.division || '—'}</td>
            <td style="text-align:center;font-size:12px;">${schedule}</td>
            <td style="text-align:center;"><span class="sp-status-badge completed">${s.status || 'Approved'}</span></td>
            <td style="text-align:center;"><span class="sp-status-badge completed">${s.payment || 'Paid'}</span></td>
            <td style="text-align:center;">
                <div class="sp-actions">
                    <button class="sp-btn sp-btn-edit" data-sports-idx="${i}" style="background:#2c2c3e;">View</button>
                </div>
            </td>
        </tr>`;
    }).join('');

    // View button — open sports application detail modal
    tbody.querySelectorAll('button[data-sports-idx]').forEach(btn => {
        btn.addEventListener('click', function () {
            const idx    = parseInt(this.getAttribute('data-sports-idx'), 10);
            const record = sports[idx];
            if (!record) return;
            openSportsViewModal(record);
        });
    });
}

// ── Format Schedule Helper ────────────────────────────────────────────────
function formatSchedule(prog) {
    if (!prog.startDate || !prog.endDate) return '—';
    
    const start = new Date(prog.startDate + 'T00:00:00');
    const end = new Date(prog.endDate + 'T00:00:00');
    
    const dateRange = formatDateRange(start, end);
    const timeRange = formatTimeRange(prog.startTime, prog.endTime);
    
    return `${dateRange} | ${timeRange}`;
}

function formatDateRange(start, end) {
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    
    const startMonth = months[start.getMonth()];
    const startDay = start.getDate();
    const endMonth = months[end.getMonth()];
    const endDay = end.getDate();
    const year = start.getFullYear();
    
    if (start.getTime() === end.getTime()) {
        return `${startMonth} ${startDay}, ${year}`;
    } else if (start.getMonth() === end.getMonth()) {
        return `${startMonth} ${startDay}-${endDay}, ${year}`;
    } else {
        return `${startMonth} ${startDay} - ${endMonth} ${endDay}, ${year}`;
    }
}

function formatTimeRange(startTime, endTime) {
    if (!startTime) return '—';
    
    const formatTime12 = (time) => {
        const [h, m] = time.split(':');
        const hour = parseInt(h, 10);
        const ampm = hour >= 12 ? 'PM' : 'AM';
        const hour12 = hour % 12 || 12;
        return `${hour12}:${m} ${ampm}`;
    };
    
    const start = formatTime12(startTime);
    const end = endTime ? formatTime12(endTime) : '';
    
    return end ? `${start} - ${end}` : start;
}

// ── Sports Application View Modal ──────────────────────────────────────────
function openSportsViewModal(s) {
    const modal = document.getElementById('spSportsViewModal');
    const body  = document.getElementById('spSportsViewBody');
    if (!modal || !body) return;

    const schedule = formatSchedule(s);

    body.innerHTML = `
    <div style="background:#fff;border-radius:10px;padding:18px 22px;border:1px solid #e5e7eb;font-family:'Segoe UI',sans-serif;">
        
        <!-- Header -->
        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid #d1d5db;">
            <img src="/images/barangay_logo.png" alt="Barangay Calios" style="width:60px;height:60px;object-fit:contain;border-radius:50%;flex-shrink:0;" onerror="this.style.display='none'">
            <h2 style="font-size:15px;font-weight:900;color:#111827;text-align:center;flex:1;letter-spacing:0.02em;">SPORTS APPLICATION DETAILS</h2>
        </div>

        <!-- Application Information -->
        <div style="margin-bottom:12px;padding-bottom:12px;border-bottom:1px solid #e5e7eb;">
            <p style="font-size:11px;font-weight:800;color:#111827;text-decoration:underline;text-transform:uppercase;margin-bottom:8px;letter-spacing:0.02em;">APPLICATION INFORMATION:</p>
            
            <div style="display:flex;align-items:flex-end;gap:6px;margin-bottom:8px;flex-wrap:wrap;">
                <span style="font-size:11px;font-weight:600;color:#111827;white-space:nowrap;flex-shrink:0;padding-bottom:2px;">Full Name:</span>
                <span style="border-bottom:1.5px solid #374151;flex:1;min-width:200px;font-size:11px;font-weight:700;color:#111827;padding:0 4px 2px;">${s.fullName || '—'}</span>
            </div>
            
            <div style="display:flex;align-items:flex-end;gap:6px;margin-bottom:8px;flex-wrap:wrap;">
                <span style="font-size:11px;font-weight:600;color:#111827;white-space:nowrap;flex-shrink:0;padding-bottom:2px;">Program Name:</span>
                <span style="border-bottom:1.5px solid #374151;flex:1;min-width:200px;font-size:11px;font-weight:700;color:#111827;padding:0 4px 2px;">${s.programName || '—'}</span>
            </div>
            
            <div style="display:flex;gap:20px;margin-bottom:8px;flex-wrap:wrap;">
                <div style="display:flex;align-items:flex-end;gap:6px;">
                    <span style="font-size:11px;font-weight:600;color:#111827;white-space:nowrap;flex-shrink:0;padding-bottom:2px;">Sport:</span>
                    <span style="border-bottom:1.5px solid #374151;min-width:120px;font-size:11px;font-weight:700;color:#111827;padding:0 4px 2px;">${s.sport || '—'}</span>
                </div>
                <div style="display:flex;align-items:flex-end;gap:6px;">
                    <span style="font-size:11px;font-weight:600;color:#111827;white-space:nowrap;flex-shrink:0;padding-bottom:2px;">Division:</span>
                    <span style="border-bottom:1.5px solid #374151;min-width:150px;font-size:11px;font-weight:700;color:#111827;padding:0 4px 2px;">${s.division || '—'}</span>
                </div>
            </div>
        </div>

        <!-- Schedule Information -->
        <div style="margin-bottom:12px;padding-bottom:12px;border-bottom:1px solid #e5e7eb;">
            <p style="font-size:11px;font-weight:800;color:#111827;text-decoration:underline;text-transform:uppercase;margin-bottom:8px;letter-spacing:0.02em;">SCHEDULE INFORMATION:</p>
            
            <div style="display:flex;align-items:flex-end;gap:6px;margin-bottom:8px;flex-wrap:wrap;">
                <span style="font-size:11px;font-weight:600;color:#111827;white-space:nowrap;flex-shrink:0;padding-bottom:2px;">Schedule:</span>
                <span style="border-bottom:1.5px solid #374151;flex:1;min-width:200px;font-size:11px;font-weight:700;color:#111827;padding:0 4px 2px;">${schedule}</span>
            </div>
        </div>

        <!-- Status Information -->
        <div style="margin-bottom:12px;">
            <p style="font-size:11px;font-weight:800;color:#111827;text-decoration:underline;text-transform:uppercase;margin-bottom:8px;letter-spacing:0.02em;">STATUS INFORMATION:</p>
            
            <div style="display:flex;gap:20px;margin-bottom:8px;flex-wrap:wrap;">
                <div style="display:flex;align-items:flex-end;gap:6px;">
                    <span style="font-size:11px;font-weight:600;color:#111827;white-space:nowrap;flex-shrink:0;padding-bottom:2px;">Status:</span>
                    <span style="display:inline-block;padding:4px 12px;border-radius:12px;font-size:11px;font-weight:600;background:#d1fae5;color:#10b981;">${s.status || 'Approved'}</span>
                </div>
                <div style="display:flex;align-items:flex-end;gap:6px;">
                    <span style="font-size:11px;font-weight:600;color:#111827;white-space:nowrap;flex-shrink:0;padding-bottom:2px;">Payment:</span>
                    <span style="display:inline-block;padding:4px 12px;border-radius:12px;font-size:11px;font-weight:600;background:#d1fae5;color:#10b981;">${s.payment || 'Paid'}</span>
                </div>
            </div>
        </div>

    </div>
    `;

    modal.style.display = 'flex';

    const closeModal = () => {
        modal.style.display = 'none';
        modal.classList.remove('sp-modal-maximized');
        box.classList.remove('sp-modal-maximized');
        const maxBtn = document.getElementById('spSportsViewMaximize');
        if (maxBtn) maxBtn.textContent = '□';
    };
    
    const box = document.getElementById('spSportsViewBox');
    const maxBtn = document.getElementById('spSportsViewMaximize');
    if (maxBtn && box) {
        maxBtn.onclick = function(e) {
            e.stopPropagation();
            const isMax = !box.classList.contains('sp-modal-maximized');
            modal.classList.toggle('sp-modal-maximized', isMax);
            box.classList.toggle('sp-modal-maximized', isMax);
            maxBtn.textContent = isMax ? '⧉' : '□';
        };
    }
    
    document.getElementById('spSportsViewClose').onclick = closeModal;
    modal.onclick = e => { if (e.target === modal) closeModal(); };
}

// ── Toast ──────────────────────────────────────────────────────────────────
function showToast(message, type) {
    const existing = document.querySelector('.app-toast');
    if (existing) existing.remove();
    const toast = document.createElement('div');
    toast.className = 'app-toast app-toast-show' + (type === 'error' ? ' app-toast-error' : '');
    toast.innerHTML = '<span>' + (type === 'error' ? '✕' : '✓') + '</span> ' + message;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.classList.remove('app-toast-show');
        toast.classList.add('app-toast-hide');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// ── Helpers ────────────────────────────────────────────────────────────────
function formatTime(t) {
    if (!t) return '—';
    const [h, m] = t.split(':');
    const hour = parseInt(h, 10);
    return `${hour % 12 || 12}:${m} ${hour >= 12 ? 'PM' : 'AM'}`;
}

function formatDate(d) {
    if (!d) return '—';
    return new Date(d + 'T00:00:00').toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

// ── Main ───────────────────────────────────────────────────────────────────
function initializeSchedulePrograms() {
    let schedules = [];
    let nextId = 1;
    let editingId = null;
    let deleteTargetId = null;
    let currentPage = 1;
    const perPage = 10;
    let filterStatus = '';
    let filterSearch = '';

    // DOM refs — modal
    const formOverlay    = document.getElementById('spFormOverlay');
    const modalTitle     = document.getElementById('spModalTitle');
    const modalClose     = document.getElementById('spModalClose');
    const hiddenId       = document.getElementById('spHiddenId');
    const fProgram       = document.getElementById('spProgram');
    const fActivityType  = document.getElementById('spActivityType');
    const fDate          = document.getElementById('spDate');
    const fStartTime     = document.getElementById('spStartTime');
    const fEndTime       = document.getElementById('spEndTime');
    const fVenue         = document.getElementById('spVenue');
    const fOfficials     = document.getElementById('spOfficials');
    const fParticipants  = document.getElementById('spParticipants');
    const fStatus        = document.getElementById('spStatus');
    const btnSave        = document.getElementById('spBtnSave');
    const btnClear       = document.getElementById('spBtnClear');
    const addBtn         = document.getElementById('spAddBtn');

    // DOM refs — table / filters
    const tbody          = document.getElementById('spTableBody');
    const searchInput    = document.getElementById('spSearch');
    const statusFilter   = document.getElementById('spStatusFilter');

    // DOM refs — delete modal
    const deleteOverlay    = document.getElementById('spDeleteOverlay');
    const btnDeleteConfirm = document.getElementById('spDeleteConfirm');

    // ── Modal open/close ────────────────────────────────────────────────────
    function openFormModal() { formOverlay.style.display = 'flex'; }
    function closeFormModal() { formOverlay.style.display = 'none'; }

    if (addBtn) addBtn.addEventListener('click', () => {
        clearForm();
        modalTitle.textContent = 'Add Schedule Program';
        btnSave.textContent = 'Save';
        openFormModal();
    });

    if (modalClose) modalClose.addEventListener('click', closeFormModal);

    formOverlay.addEventListener('click', e => {
        if (e.target === formOverlay) closeFormModal();
    });

    // ── Render ──────────────────────────────────────────────────────────────
    function getFiltered() {
        return schedules.filter(s => {
            if (filterStatus && s.status !== filterStatus) return false;
            if (filterSearch) {
                const q = filterSearch.toLowerCase();
                const haystack = [s.programName, s.activityType, s.venue, s.status]
                    .map(v => (v || '').toLowerCase()).join(' ');
                if (!haystack.includes(q)) return false;
            }
            return true;
        });
    }

    function renderTable() {
        const filtered = getFiltered();
        const totalPages = Math.max(1, Math.ceil(filtered.length / perPage));
        if (currentPage > totalPages) currentPage = totalPages;

        const start = (currentPage - 1) * perPage;
        const end   = Math.min(start + perPage, filtered.length);
        const page  = filtered.slice(start, end);

        tbody.innerHTML = '';

        if (page.length === 0) {
            const tr = document.createElement('tr');
            tr.className = 'empty-state-row';
            tr.innerHTML = `<td colspan="7">No schedules found.</td>`;
            tbody.appendChild(tr);
        } else {
            page.forEach(s => {
                const statusCls = s.status.toLowerCase();
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${s.programName || '—'}</td>
                    <td>${s.activityType || '—'}</td>
                    <td>${formatDate(s.date)}</td>
                    <td>${formatTime(s.startTime)}${s.endTime ? ' – ' + formatTime(s.endTime) : ''}</td>
                    <td>${s.venue || '—'}</td>
                    <td><span class="sp-status-badge ${statusCls}">${s.status}</span></td>
                    <td>
                        <div class="sp-actions">
                            <button class="sp-btn sp-btn-edit"   data-action="edit"   data-id="${s.id}">Edit</button>
                            <button class="sp-btn sp-btn-delete" data-action="delete" data-id="${s.id}">Delete</button>
                        </div>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        updatePagination(filtered.length, start + 1, end, totalPages);
        updateStats();
    }

    function updateStats() {
        const counts = { Upcoming: 0, Ongoing: 0, Completed: 0, Cancelled: 0, Rescheduled: 0 };
        schedules.forEach(s => { if (counts[s.status] !== undefined) counts[s.status]++; });
        document.getElementById('spStatUpcoming').textContent    = counts.Upcoming;
        document.getElementById('spStatOngoing').textContent     = counts.Ongoing;
        document.getElementById('spStatCompleted').textContent   = counts.Completed;
        document.getElementById('spStatCancelled').textContent   = counts.Cancelled;
        document.getElementById('spStatRescheduled').textContent = counts.Rescheduled;
    }

    function updatePagination(total, start, end, totalPages) {
        const info = document.getElementById('spPaginationInfo');
        if (info) info.textContent = total === 0 ? 'No records found' : `Showing ${start}–${end} of ${total} records`;

        const prevBtn  = document.getElementById('spPrevBtn');
        const nextBtn  = document.getElementById('spNextBtn');
        const pageNums = document.getElementById('spPageNumbers');

        if (prevBtn) prevBtn.disabled = currentPage === 1;
        if (nextBtn) nextBtn.disabled = currentPage === totalPages;

        if (pageNums) {
            pageNums.innerHTML = '';
            const s = Math.max(1, currentPage - 2);
            const e = Math.min(totalPages, currentPage + 2);
            for (let i = s; i <= e; i++) {
                const btn = document.createElement('button');
                btn.className = 'page-number' + (i === currentPage ? ' active' : '');
                btn.textContent = i;
                btn.onclick = () => { currentPage = i; renderTable(); };
                pageNums.appendChild(btn);
            }
        }
    }

    // ── Form helpers ────────────────────────────────────────────────────────
    function getVal(el) { return el ? el.value.trim() : ''; }

    function clearForm() {
        hiddenId.value      = '';
        fProgram.value      = '';
        fActivityType.value = '';
        fDate.value         = '';
        fStartTime.value    = '';
        fEndTime.value      = '';
        fVenue.value        = '';
        fOfficials.value    = '';
        fParticipants.value = '';
        fStatus.value       = 'Upcoming';
        editingId = null;
    }

    function populateForm(s) {
        hiddenId.value       = s.id;
        fProgram.value       = s.programName || '';
        fActivityType.value  = s.activityType || '';
        fDate.value          = s.date || '';
        fStartTime.value     = s.startTime || '';
        fEndTime.value       = s.endTime || '';
        fVenue.value         = s.venue || '';
        fOfficials.value     = s.officials || '';
        fParticipants.value  = s.participants || '';
        fStatus.value        = s.status || 'Upcoming';
        editingId = s.id;
    }

    // ── Save ────────────────────────────────────────────────────────────────
    if (btnSave) {
        btnSave.addEventListener('click', () => {
            const programName  = getVal(fProgram);
            const activityType = getVal(fActivityType);
            const date         = getVal(fDate);
            const startTime    = getVal(fStartTime);
            const endTime      = getVal(fEndTime);
            const venue        = getVal(fVenue);
            const officials    = getVal(fOfficials);
            const participants = getVal(fParticipants);
            const status       = getVal(fStatus) || 'Upcoming';

            if (!programName || !activityType || !date || !startTime || !venue) {
                showToast('Please fill in all required fields.', 'error');
                return;
            }

            if (endTime && endTime < startTime) {
                showToast('End time must be after start time.', 'error');
                return;
            }

            const id = hiddenId.value ? parseInt(hiddenId.value, 10) : null;

            if (id) {
                const idx = schedules.findIndex(s => s.id === id);
                if (idx !== -1) {
                    schedules[idx] = { id, programName, activityType, date, startTime, endTime, venue, officials, participants, status };
                    showToast('Schedule updated successfully!');
                }
            } else {
                schedules.push({ id: nextId++, programName, activityType, date, startTime, endTime, venue, officials, participants, status });
                showToast('Schedule saved successfully!');
            }

            closeFormModal();
            clearForm();
            renderTable();
        });
    }

    if (btnClear) btnClear.addEventListener('click', clearForm);

    // ── Table actions ───────────────────────────────────────────────────────
    tbody.addEventListener('click', e => {
        const btn = e.target.closest('button[data-action]');
        if (!btn) return;

        const action = btn.getAttribute('data-action');
        const id     = parseInt(btn.getAttribute('data-id'), 10);
        const sched  = schedules.find(s => s.id === id);
        if (!sched) return;

        if (action === 'edit') {
            populateForm(sched);
            modalTitle.textContent = 'Edit Schedule Program';
            btnSave.textContent = 'Update';
            openFormModal();
        } else if (action === 'delete') {
            deleteTargetId = id;
            deleteOverlay.style.display = 'flex';
        }
    });

    // ── Delete modal ────────────────────────────────────────────────────────
    [document.getElementById('spDeleteCancel'), document.getElementById('spDeleteCancelFooter')].forEach(btn => {
        if (btn) btn.addEventListener('click', () => {
            deleteOverlay.style.display = 'none';
            deleteTargetId = null;
        });
    });

    if (btnDeleteConfirm) btnDeleteConfirm.addEventListener('click', () => {
        schedules = schedules.filter(s => s.id !== deleteTargetId);
        deleteOverlay.style.display = 'none';
        deleteTargetId = null;
        renderTable();
        showToast('Schedule deleted successfully!');
    });

    deleteOverlay.addEventListener('click', e => {
        if (e.target === deleteOverlay) {
            deleteOverlay.style.display = 'none';
            deleteTargetId = null;
        }
    });

    // ── Filters ─────────────────────────────────────────────────────────────
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            filterSearch = searchInput.value.trim();
            currentPage = 1;
            renderTable();
        });
    }

    if (statusFilter) {
        statusFilter.addEventListener('change', () => {
            filterStatus = statusFilter.value;
            currentPage = 1;
            renderTable();
        });
    }

    // ── Pagination ──────────────────────────────────────────────────────────
    const prevBtn = document.getElementById('spPrevBtn');
    const nextBtn = document.getElementById('spNextBtn');
    if (prevBtn) prevBtn.addEventListener('click', () => { currentPage--; renderTable(); });
    if (nextBtn) nextBtn.addEventListener('click', () => { currentPage++; renderTable(); });

    // ── Initial render ──────────────────────────────────────────────────────
    renderTable();
}

