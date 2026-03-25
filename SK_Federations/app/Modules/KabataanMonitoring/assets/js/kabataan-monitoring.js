(function () {
    const records = [
        {
            slug: 'alyssa-ramos',
            name: 'Alyssa Ramos',
            barangay: 'Brgy. I (Poblacion)',
            age: 21,
            status: 'high',
            focus: 'Education',
            score: 93,
            attendance: '11 of 12 sessions',
            lastCheckIn: 'Mar 18, 2026',
            programs: [
                { title: 'Peer Tutor Circle', summary: 'Facilitator for youth study sessions every Saturday.' },
                { title: 'Digital Literacy Camp', summary: 'Volunteer mentor for computer skills onboarding.' }
            ],
            recommendations: [
                'Assign as youth mentor for incoming student volunteers.',
                'Include in inter-barangay leadership exchange for Q2.',
                'Track sustained attendance for scholarship endorsement.'
            ],
            timeline: [
                { title: 'Initial registration', note: 'Completed Kabataan profile validation in Jan 2026.' },
                { title: 'Engagement uplift', note: 'Attendance improved after joining tutor circle activities.' },
                { title: 'Current monitoring', note: 'Maintaining high score with consistent peer support hours.' }
            ]
        },
        {
            slug: 'joshua-villanueva',
            name: 'Joshua Villanueva',
            barangay: 'Pagsawitan',
            age: 19,
            status: 'moderate',
            focus: 'Sports Development',
            score: 77,
            attendance: '8 of 12 sessions',
            lastCheckIn: 'Mar 14, 2026',
            programs: [
                { title: 'Barangay League', summary: 'Participates in weekend league and fitness drills.' },
                { title: 'Youth Referee Training', summary: 'Under evaluation for officiating support track.' }
            ],
            recommendations: [
                'Set monthly mentorship check-in for attendance consistency.',
                'Encourage parent-guardian touchpoint for schedule alignment.',
                'Pair with high-engagement peer buddy during activities.'
            ],
            timeline: [
                { title: 'Re-engagement', note: 'Returned after two-month inactivity in late 2025.' },
                { title: 'Participation recovery', note: 'Attendance stabilized during February sessions.' },
                { title: 'Current monitoring', note: 'Needs one-on-one follow-up to prevent drop-off.' }
            ]
        },
        {
            slug: 'celine-mendoza',
            name: 'Celine Mendoza',
            barangay: 'Labuin',
            age: 20,
            status: 'low',
            focus: 'Health and Wellness',
            score: 59,
            attendance: '5 of 12 sessions',
            lastCheckIn: 'Mar 06, 2026',
            programs: [
                { title: 'Wellness Caravan', summary: 'Registered but inconsistent participation in health sessions.' },
                { title: 'Mental Health Circle', summary: 'Scheduled for counseling and peer support referral.' }
            ],
            recommendations: [
                'Prioritize welfare follow-up with barangay youth focal person.',
                'Include in targeted transportation support pilot.',
                'Escalate to federation wellness team if no check-in within 14 days.'
            ],
            timeline: [
                { title: 'At-risk flag', note: 'Profile marked low engagement after repeated absence.' },
                { title: 'Support initiated', note: 'Referred to wellness caravan and counseling support.' },
                { title: 'Current monitoring', note: 'Pending follow-up confirmation with household liaison.' }
            ]
        },
        {
            slug: 'mark-bautista',
            name: 'Mark Bautista',
            barangay: 'San Jose',
            age: 22,
            status: 'moderate',
            focus: 'Livelihood',
            score: 74,
            attendance: '7 of 12 sessions',
            lastCheckIn: 'Mar 12, 2026',
            programs: [
                { title: 'Youth Skills Lab', summary: 'Active in welding and carpentry skilling sessions.' },
                { title: 'Micro-Enterprise Clinic', summary: 'Joined business planning workshops for startup ideas.' }
            ],
            recommendations: [
                'Route to advanced skills track under TESDA partnership.',
                'Monitor transition to job placement support in Q3.',
                'Track attendance against target of 10 monthly engagements.'
            ],
            timeline: [
                { title: 'Career pathway mapping', note: 'Completed skills assessment and placement interview.' },
                { title: 'Program participation', note: 'Moderate attendance in livelihood sessions.' },
                { title: 'Current monitoring', note: 'Needs attendance boost to meet competency milestones.' }
            ]
        },
        {
            slug: 'jessa-garcia',
            name: 'Jessa Garcia',
            barangay: 'Brgy. III (Poblacion)',
            age: 18,
            status: 'high',
            focus: 'Civic Participation',
            score: 90,
            attendance: '12 of 12 sessions',
            lastCheckIn: 'Mar 20, 2026',
            programs: [
                { title: 'Barangay Assembly Corps', summary: 'Lead youth mobilizer for civic assemblies.' },
                { title: 'Voter Education Drive', summary: 'Facilitates awareness sessions for first-time voters.' }
            ],
            recommendations: [
                'Nominate for municipal youth leadership summit.',
                'Assign as co-facilitator for federation orientation sessions.',
                'Capture case study for best-practice documentation.'
            ],
            timeline: [
                { title: 'Leadership onboarding', note: 'Selected as youth mobilizer representative.' },
                { title: 'Community impact', note: 'Recorded highest attendance and outreach participation.' },
                { title: 'Current monitoring', note: 'Sustaining high engagement with leadership responsibilities.' }
            ]
        },
        {
            slug: 'paolo-santos',
            name: 'Paolo Santos',
            barangay: 'Brgy. V (Poblacion)',
            age: 20,
            status: 'low',
            focus: 'School Continuity',
            score: 54,
            attendance: '4 of 12 sessions',
            lastCheckIn: 'Mar 04, 2026',
            programs: [
                { title: 'Back-to-School Support', summary: 'Identified for scholarship and school supply support.' },
                { title: 'Weekend Mentoring Group', summary: 'Pending reactivation in mentoring sessions.' }
            ],
            recommendations: [
                'Coordinate with school focal person for attendance intervention.',
                'Prioritize in educational assistance allocation this month.',
                'Conduct household visit with barangay youth committee.'
            ],
            timeline: [
                { title: 'Attendance decline', note: 'Flagged due to repeated school and program absences.' },
                { title: 'Intervention prepared', note: 'Support package drafted with federation education desk.' },
                { title: 'Current monitoring', note: 'Awaiting family consent for full intervention rollout.' }
            ]
        }
    ];

    const state = {
        search: '',
        status: 'all'
    };

    const statusLabel = {
        high: 'High',
        moderate: 'Moderate',
        low: 'Low'
    };

    function toNumber(value) {
        const n = Number(value);
        return Number.isFinite(n) ? n : 0;
    }

    function scoreAverage(items) {
        if (!items.length) {
            return 0;
        }
        const total = items.reduce((sum, item) => sum + toNumber(item.score), 0);
        return Math.round(total / items.length);
    }

    function updateKpis(items) {
        const totalEl = document.getElementById('km-kpi-total');
        const highEl = document.getElementById('km-kpi-high');
        const followEl = document.getElementById('km-kpi-followup');
        const scoreEl = document.getElementById('km-kpi-score');

        if (!totalEl || !highEl || !followEl || !scoreEl) {
            return;
        }

        totalEl.textContent = String(items.length);
        highEl.textContent = String(items.filter((item) => item.status === 'high').length);
        followEl.textContent = String(items.filter((item) => item.status === 'low').length);
        scoreEl.textContent = String(scoreAverage(items));
    }

    function getFiltered() {
        const query = state.search.trim().toLowerCase();

        return records.filter((item) => {
            const matchStatus = state.status === 'all' || item.status === state.status;
            const haystack = `${item.name} ${item.barangay} ${item.focus}`.toLowerCase();
            const matchSearch = !query || haystack.includes(query);
            return matchStatus && matchSearch;
        });
    }

    function groupByBarangay(items) {
        const map = new Map();

        items.forEach((item) => {
            if (!map.has(item.barangay)) {
                map.set(item.barangay, []);
            }
            map.get(item.barangay).push(item);
        });

        return Array.from(map.entries()).sort((a, b) => a[0].localeCompare(b[0]));
    }

    function buildBarangayBlock(barangay, members, detailBase) {
        const block = document.createElement('article');
        block.className = 'km-barangay-card';

        const rows = members
            .sort((a, b) => a.name.localeCompare(b.name))
            .map((item) => `
                <li class="km-kabataan-item">
                    <div class="km-kabataan-main">
                        <div class="km-kabataan-title-row">
                            <h4>${item.name}</h4>
                            <span class="km-badge ${item.status}">${statusLabel[item.status] || 'Unknown'}</span>
                        </div>
                        <p class="km-kabataan-meta">
                            <span><i class="fas fa-user"></i> ${item.age} years old</span>
                            <span><i class="fas fa-bullseye"></i> ${item.focus}</span>
                            <span><i class="fas fa-chart-line"></i> Score ${item.score}</span>
                            <span><i class="fas fa-clock"></i> Last check-in: ${item.lastCheckIn}</span>
                        </p>
                    </div>
                    <a class="km-btn km-btn-sm" href="${detailBase}/${item.slug}">Details <i class="fas fa-arrow-right"></i></a>
                </li>
            `)
            .join('');

        block.innerHTML = `
            <div class="km-barangay-head">
                <h3>${barangay}</h3>
                <span class="km-barangay-count">${members.length} kabataan</span>
            </div>
            <ul class="km-kabataan-list">${rows}</ul>
        `;

        return block;
    }

    function renderIndex() {
        const root = document.querySelector('main.km-main');
        if (!root) {
            return;
        }

        const detailBase = root.dataset.detailBase || '/kabataan-monitoring';
        const grid = document.getElementById('km-card-grid');
        const empty = document.getElementById('km-empty');
        if (!grid || !empty) {
            return;
        }

        const filtered = getFiltered();
        const grouped = groupByBarangay(filtered);

        grid.innerHTML = '';
        grouped.forEach(([barangay, members]) => {
            grid.appendChild(buildBarangayBlock(barangay, members, detailBase));
        });

        empty.hidden = filtered.length !== 0;
        updateKpis(filtered);
    }

    function initIndex() {
        const searchInput = document.getElementById('km-search');
        const chipWrap = document.getElementById('km-status-filter');

        if (!searchInput || !chipWrap) {
            return;
        }

        searchInput.addEventListener('input', (event) => {
            state.search = event.target.value || '';
            renderIndex();
        });

        chipWrap.querySelectorAll('.km-chip').forEach((chip) => {
            chip.addEventListener('click', () => {
                chipWrap.querySelectorAll('.km-chip').forEach((btn) => btn.classList.remove('active'));
                chip.classList.add('active');
                state.status = chip.dataset.status || 'all';
                renderIndex();
            });
        });

        renderIndex();
    }

    function metricCard(label, value) {
        return `<article class="km-metric-card"><small>${label}</small><strong>${value}</strong></article>`;
    }

    function renderDetail() {
        const root = document.querySelector('main.km-main');
        if (!root) {
            return;
        }

        const detailBase = root.dataset.detailBase || '/kabataan-monitoring';
        const slug = root.dataset.kabataanSlug || '';
        const profile = records.find((item) => item.slug === slug);
        const hero = document.getElementById('km-profile-hero');
        const detailGrid = document.getElementById('km-detail-grid');
        const notFound = document.getElementById('km-not-found');

        if (!hero || !detailGrid || !notFound) {
            return;
        }

        if (!profile) {
            detailGrid.hidden = true;
            notFound.hidden = false;
            return;
        }

        notFound.hidden = true;
        detailGrid.hidden = false;

        hero.innerHTML = `
            <div class="km-profile-head">
                <a class="km-back-link" href="${detailBase}"><i class="fas fa-arrow-left"></i> Back to list</a>
                <h1>${profile.name}</h1>
                <p>${profile.barangay} | ${profile.age} years old | Focus: ${profile.focus}</p>
                <div class="km-profile-strip">
                    <span class="km-profile-pill">Status: ${statusLabel[profile.status] || 'Unknown'}</span>
                    <span class="km-profile-pill">Attendance: ${profile.attendance}</span>
                    <span class="km-profile-pill">Last check-in: ${profile.lastCheckIn}</span>
                </div>
            </div>
        `;

        const metricGrid = document.getElementById('km-metric-grid');
        const programList = document.getElementById('km-program-list');
        const recoList = document.getElementById('km-reco-list');
        const timeline = document.getElementById('km-timeline');

        if (!metricGrid || !programList || !recoList || !timeline) {
            return;
        }

        metricGrid.innerHTML = [
            metricCard('Engagement score', profile.score),
            metricCard('Attendance window', profile.attendance),
            metricCard('Monitoring status', statusLabel[profile.status] || 'Unknown'),
            metricCard('Support focus', profile.focus)
        ].join('');

        programList.innerHTML = profile.programs
            .map((item) => `<article class="km-list-item"><h4>${item.title}</h4><p>${item.summary}</p></article>`)
            .join('');

        recoList.innerHTML = profile.recommendations
            .map((item) => `<li>${item}</li>`)
            .join('');

        timeline.innerHTML = profile.timeline
            .map((item) => `<article class="km-time-item"><h4>${item.title}</h4><p>${item.note}</p></article>`)
            .join('');
    }

    document.addEventListener('DOMContentLoaded', function () {
        const mode = window.kmPageMode;

        if (mode === 'show') {
            renderDetail();
            return;
        }

        initIndex();
    });
})();
