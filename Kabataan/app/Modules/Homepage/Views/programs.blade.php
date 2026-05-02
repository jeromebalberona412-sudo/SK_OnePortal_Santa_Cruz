@extends('homepage::layout')

@section('title', 'Programs - SK OnePortal Kabataan')

@section('content')
@php
    $sectorMeta = [
        'education' => ['label' => 'Education', 'icon' => 'school'],
        'sports' => ['label' => 'Sports', 'icon' => 'sports'],
        'health' => ['label' => 'Health', 'icon' => 'health'],
        'agriculture' => ['label' => 'Agriculture', 'icon' => 'agri'],
        'gad' => ['label' => 'GAD', 'icon' => 'groups'],
        'anti-drugs' => ['label' => 'Anti-Drugs', 'icon' => 'shield'],
        'disaster' => ['label' => 'Disaster', 'icon' => 'warning'],
        'others' => ['label' => 'Others', 'icon' => 'apps'],
    ];

    $statusMeta = [
        'active' => ['label' => 'Active', 'note' => 'Open for registration'],
        'upcoming' => ['label' => 'Upcoming', 'note' => 'Starts soon'],
        'completed' => ['label' => 'Completed', 'note' => 'Already concluded'],
    ];

    $programs = [
        [
            'id' => 101,
            'title' => 'Youth Tech Literacy Bootcamp',
            'sector' => 'education',
            'status' => 'active',
            'barangay' => 'Pagsawitan',
            'summary' => 'An intensive 8-week program that equips youth with coding basics, digital productivity, and online safety skills.',
            'about' => 'Participants work on hands-on projects with mentors and present outputs during a community showcase. This program is designed for first-time learners and youth looking to improve digital confidence for school and work.',
            'hero' => '/modules/homepage/image/5.png',
            'participants' => 120,
            'capacity' => 160,
            'duration' => '8 weeks',
            'budgetTotal' => 150000,
            'budgetUtilization' => 75,
            'timeline' => [
                ['title' => 'Registration Period', 'date' => 'June 1 - June 30, 2026', 'status' => 'completed'],
                ['title' => 'Bootcamp Sessions', 'date' => 'July 15 - Sept 15, 2026', 'status' => 'active'],
                ['title' => 'Community Tech Showcase', 'date' => 'Sept 30, 2026', 'status' => 'upcoming'],
            ],
            'budgetItems' => [
                ['label' => 'Instructors and Mentors', 'amount' => 60000],
                ['label' => 'Equipment Rental', 'amount' => 50000],
                ['label' => 'Materials and Software', 'amount' => 25000],
                ['label' => 'Logistics and Meals', 'amount' => 15000],
            ],
        ],
        [
            'id' => 102,
            'title' => 'Inter-Barangay Summer League',
            'sector' => 'sports',
            'status' => 'active',
            'barangay' => 'Labuin',
            'summary' => 'A barangay sports league focused on teamwork, discipline, and fitness with coaching clinics and youth tournaments.',
            'about' => 'The summer league includes basketball and volleyball brackets, weekly coaching sessions, and health monitoring to keep participants active and engaged.',
            'hero' => '/modules/homepage/image/3.png',
            'participants' => 350,
            'capacity' => 420,
            'duration' => '10 weeks',
            'budgetTotal' => 200000,
            'budgetUtilization' => 45,
            'timeline' => [
                ['title' => 'Team Signups', 'date' => 'May 10 - June 5, 2026', 'status' => 'completed'],
                ['title' => 'League Proper', 'date' => 'June 12 - August 20, 2026', 'status' => 'active'],
                ['title' => 'Finals and Recognition', 'date' => 'August 30, 2026', 'status' => 'upcoming'],
            ],
            'budgetItems' => [
                ['label' => 'Venue and Equipment', 'amount' => 90000],
                ['label' => 'Coaching Staff', 'amount' => 50000],
                ['label' => 'Uniform and Medals', 'amount' => 35000],
                ['label' => 'Hydration and Safety', 'amount' => 25000],
            ],
        ],
        [
            'id' => 103,
            'title' => 'Youth Mental Wellness Drive',
            'sector' => 'health',
            'status' => 'upcoming',
            'barangay' => 'Alipit',
            'summary' => 'A workshop and peer-support series that promotes awareness, stress management, and access to counseling.',
            'about' => 'Sessions are facilitated by trained resource speakers with guided small-group activities and referral pathways for additional support.',
            'hero' => '/modules/homepage/image/4.png',
            'participants' => 0,
            'capacity' => 120,
            'duration' => '6 weeks',
            'budgetTotal' => 120000,
            'budgetUtilization' => 10,
            'timeline' => [
                ['title' => 'Participant Pre-Registration', 'date' => 'June 15 - July 5, 2026', 'status' => 'active'],
                ['title' => 'Wellness Sessions', 'date' => 'July 12 - August 20, 2026', 'status' => 'upcoming'],
                ['title' => 'Community Reflection Day', 'date' => 'August 28, 2026', 'status' => 'upcoming'],
            ],
            'budgetItems' => [
                ['label' => 'Resource Speakers', 'amount' => 40000],
                ['label' => 'Assessment Tools', 'amount' => 25000],
                ['label' => 'Session Materials', 'amount' => 30000],
                ['label' => 'Logistics', 'amount' => 25000],
            ],
        ],
        [
            'id' => 104,
            'title' => 'Urban Youth Farming Initiative',
            'sector' => 'agriculture',
            'status' => 'active',
            'barangay' => 'San Jose',
            'summary' => 'Youth-led community garden conversion program with practical training in sustainable farming and food resilience.',
            'about' => 'Participants are assigned garden plots, attend weekly skill labs, and learn how to run small produce distribution activities.',
            'hero' => '/modules/homepage/image/1.png',
            'participants' => 45,
            'capacity' => 60,
            'duration' => '12 weeks',
            'budgetTotal' => 90000,
            'budgetUtilization' => 85,
            'timeline' => [
                ['title' => 'Site Prep and Orientation', 'date' => 'April 8 - April 20, 2026', 'status' => 'completed'],
                ['title' => 'Planting and Maintenance', 'date' => 'April 25 - July 15, 2026', 'status' => 'active'],
                ['title' => 'Harvest and Market Day', 'date' => 'July 25, 2026', 'status' => 'upcoming'],
            ],
            'budgetItems' => [
                ['label' => 'Seeds and Inputs', 'amount' => 28000],
                ['label' => 'Tools and Equipment', 'amount' => 24000],
                ['label' => 'Training Support', 'amount' => 19000],
                ['label' => 'Distribution Logistics', 'amount' => 19000],
            ],
        ],
        [
            'id' => 105,
            'title' => 'Safe Spaces and Equality Workshops',
            'sector' => 'gad',
            'status' => 'active',
            'barangay' => 'Bagumbayan',
            'summary' => 'A GAD-focused workshop track on inclusive leadership, respectful communication, and youth advocacy skills.',
            'about' => 'The workshop series trains youth leaders to facilitate safe, inclusive spaces in schools and barangay organizations.',
            'hero' => '/modules/homepage/image/2.png',
            'participants' => 72,
            'capacity' => 100,
            'duration' => '5 weeks',
            'budgetTotal' => 70000,
            'budgetUtilization' => 62,
            'timeline' => [
                ['title' => 'Community Orientation', 'date' => 'May 5 - May 12, 2026', 'status' => 'completed'],
                ['title' => 'Learning Sessions', 'date' => 'May 15 - June 18, 2026', 'status' => 'active'],
                ['title' => 'Action Plan Presentations', 'date' => 'June 22, 2026', 'status' => 'upcoming'],
            ],
            'budgetItems' => [
                ['label' => 'Facilitators', 'amount' => 28000],
                ['label' => 'Workshop Kits', 'amount' => 17000],
                ['label' => 'Venue and Snacks', 'amount' => 14000],
                ['label' => 'Youth Advocacy Outputs', 'amount' => 11000],
            ],
        ],
        [
            'id' => 106,
            'title' => 'Barkada Kontra Droga Campus Caravan',
            'sector' => 'anti-drugs',
            'status' => 'upcoming',
            'barangay' => 'Poblacion I',
            'summary' => 'Youth awareness caravan featuring prevention talks, peer education, and early intervention pathways.',
            'about' => 'The caravan uses school-to-community outreach to strengthen prevention messaging and connect at-risk youth to support.',
            'hero' => '/modules/homepage/image/3.png',
            'participants' => 0,
            'capacity' => 200,
            'duration' => '4 weeks',
            'budgetTotal' => 80000,
            'budgetUtilization' => 18,
            'timeline' => [
                ['title' => 'Partner School Coordination', 'date' => 'June 1 - June 20, 2026', 'status' => 'active'],
                ['title' => 'Campus Caravan', 'date' => 'July 1 - July 25, 2026', 'status' => 'upcoming'],
                ['title' => 'Referral Follow-up', 'date' => 'July 30 - August 15, 2026', 'status' => 'upcoming'],
            ],
            'budgetItems' => [
                ['label' => 'Education Materials', 'amount' => 25000],
                ['label' => 'Speaker Honorarium', 'amount' => 22000],
                ['label' => 'Transport and Coordination', 'amount' => 18000],
                ['label' => 'Monitoring and Follow-up', 'amount' => 15000],
            ],
        ],
    ];

@endphp

<div class="programs-page">
    <section class="programs-hero-v2">
        <div class="programs-hero-overlay"></div>
        <div class="programs-shell programs-hero-content">
            <span class="programs-eyebrow">Kabataan Programs</span>
            <h1>Discover Programs Across Santa Cruz</h1>
            <p>Explore youth initiatives by sector, barangay, and status. Track participation and budget transparency before you join.</p>
            <div class="programs-hero-actions">
                <a href="#programs-grid" class="programs-btn programs-btn-primary">Explore Programs</a>
                <button id="openFeaturedModal" type="button" class="programs-btn programs-btn-ghost">View Program Details</button>
            </div>
        </div>
    </section>

    <section class="programs-discovery">
        <div class="programs-shell">
            <header class="programs-headline">
                <h2>Programs Discovery</h2>
                <p>Find opportunities that match your interests and schedule.</p>
            </header>

            <div class="programs-toolbar" role="region" aria-label="Program filters">
                <label class="programs-search-wrap" for="programSearch">
                    <span class="programs-search-icon">Search</span>
                    <input id="programSearch" type="text" placeholder="Search programs, sectors, barangays, or keywords" autocomplete="off">
                </label>

                <select id="sectorFilter" aria-label="Filter by sector">
                    <option value="">All Sectors</option>
                    <option value="education">Education</option>
                    <option value="sports">Sports</option>
                    <option value="health">Health</option>
                    <option value="agriculture">Agriculture</option>
                    <option value="gad">GAD</option>
                    <option value="anti-drugs">Anti-Drugs</option>
                    <option value="disaster">Disaster</option>
                    <option value="others">Others</option>
                </select>

                <select id="barangayFilter" aria-label="Filter by barangay">
                    <option value="">All Barangays</option>
                    @foreach(collect($programs)->pluck('barangay')->unique()->sort()->values() as $barangay)
                        <option value="{{ strtolower($barangay) }}">{{ $barangay }}</option>
                    @endforeach
                </select>

                <select id="statusFilter" aria-label="Filter by status">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="upcoming">Upcoming</option>
                    <option value="completed">Completed</option>
                </select>

                <button id="resetFilters" type="button" class="programs-btn programs-btn-muted">Reset</button>
            </div>

            <div class="programs-results-meta">
                <p id="programCount">Showing {{ count($programs) }} programs</p>
                <p class="programs-meta-help">Tap a card to open full program details.</p>
            </div>

            <div class="programs-grid-v2" id="programs-grid">
                @foreach($programs as $program)
                    @php
                        $sector = $sectorMeta[$program['sector']] ?? ['label' => ucfirst($program['sector']), 'icon' => 'apps'];
                        $status = $statusMeta[$program['status']] ?? ['label' => ucfirst($program['status']), 'note' => ''];
                    @endphp
                    <article
                        class="program-card-v2"
                        data-name="{{ strtolower($program['title']) }}"
                        data-summary="{{ strtolower($program['summary']) }}"
                        data-barangay="{{ strtolower($program['barangay']) }}"
                        data-sector="{{ $program['sector'] }}"
                        data-status="{{ $program['status'] }}"
                    >
                        <div class="program-card-media">
                            <img src="{{ $program['hero'] }}" alt="{{ $program['title'] }}">
                            <span class="program-badge program-badge-sector {{ $program['sector'] }}">{{ $sector['label'] }}</span>
                            <span class="program-badge program-badge-status {{ $program['status'] }}">{{ $status['label'] }}</span>
                        </div>

                        <div class="program-card-content">
                            <p class="program-barangay-chip">{{ $program['barangay'] }}</p>
                            <h3>{{ $program['title'] }}</h3>
                            <p>{{ $program['summary'] }}</p>

                            <div class="program-progress-wrap">
                                <div class="program-progress-top">
                                    <span>Budget Utilization</span>
                                    <strong>{{ $program['budgetUtilization'] }}%</strong>
                                </div>
                                <div class="program-progress-track">
                                    <div class="program-progress-fill" data-budget="{{ $program['budgetUtilization'] }}"></div>
                                </div>
                            </div>

                            <div class="program-card-meta">
                                <span>{{ number_format($program['participants']) }} participants</span>
                                <span>Budget: PHP {{ number_format($program['budgetTotal']) }}</span>
                            </div>

                            <div class="program-card-actions">
                                <button type="button" data-program-action="modal" data-program-id="{{ $program['id'] }}" class="programs-btn programs-btn-primary">View Details</button>
                                <a href="{{ route('register') }}" class="programs-btn programs-btn-ghost">Join</a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div id="noProgramsMessage" class="programs-empty" hidden>
                <h3>No matching programs found</h3>
                <p>Try resetting filters or searching with broader keywords.</p>
            </div>
        </div>
    </section>

    <section id="programDetailsModal" class="program-modal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
        <div class="program-modal-backdrop" data-close-modal="true"></div>
        <div class="program-modal-panel" role="document">
            <div class="program-modal-topbar">
                <h3 id="modalTopbarTitle">Program Details</h3>
                <button id="closeProgramModal" type="button" class="program-modal-close" aria-label="Close program details">X</button>
            </div>

            <div class="program-modal-grid">
                <article class="program-modal-main">
                    <div class="program-modal-media">
                        <img id="modalImage" src="" alt="">
                        <div class="program-modal-media-overlay"></div>
                        <div class="program-modal-header">
                            <div class="program-modal-badges">
                                <span id="modalStatus" class="program-badge program-badge-status"></span>
                                <span id="modalSector" class="program-badge program-badge-sector"></span>
                            </div>
                            <h3 id="modalTitle"></h3>
                            <p id="modalSummary"></p>
                        </div>
                    </div>

                    <div class="program-modal-body">
                        <h4>About the Program</h4>
                        <p id="modalAbout"></p>

                        <h4>Schedule and Timeline</h4>
                        <ul id="modalTimeline" class="program-timeline"></ul>
                    </div>
                </article>

                <aside class="program-modal-side">
                    <article class="spotlight-cta-card">
                        <h4>Join This Program</h4>
                        <p>Secure your slot and receive updates for schedules, reminders, and announcements.</p>
                        <a href="{{ route('register') }}" class="programs-btn programs-btn-primary">Register Now</a>
                    </article>

                    <article class="spotlight-metric-card">
                        <h4>Impact Metrics</h4>
                        <div class="spotlight-metric-row">
                            <span>Total Participants</span>
                            <strong id="modalParticipants"></strong>
                        </div>
                        <div class="spotlight-metric-row">
                            <span>Program Duration</span>
                            <strong id="modalDuration"></strong>
                        </div>
                        <div class="spotlight-metric-row">
                            <span>Capacity</span>
                            <strong id="modalCapacity"></strong>
                        </div>
                    </article>

                    <article class="spotlight-budget-card">
                        <h4>Budget Breakdown</h4>
                        <p class="spotlight-budget-total" id="modalBudgetTotal"></p>
                        <div class="program-progress-wrap compact">
                            <div class="program-progress-top">
                                <span>Budget Utilization</span>
                                <strong id="modalBudgetUtilization"></strong>
                            </div>
                            <div class="program-progress-track">
                                <div id="modalBudgetBar" class="program-progress-fill"></div>
                            </div>
                        </div>
                        <ul id="modalBudgetItems" class="spotlight-budget-list"></ul>
                    </article>
                </aside>
            </div>
        </div>
    </section>
</div>

<script id="programDataPayload" type="application/json">@json($programs)</script>
<script id="programStatusPayload" type="application/json">@json($statusMeta)</script>
<script id="programSectorPayload" type="application/json">@json($sectorMeta)</script>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const programs = JSON.parse(document.getElementById('programDataPayload')?.textContent || '[]');
        const statusMeta = JSON.parse(document.getElementById('programStatusPayload')?.textContent || '{}');
        const sectorMeta = JSON.parse(document.getElementById('programSectorPayload')?.textContent || '{}');

        const searchInput = document.getElementById('programSearch');
        const sectorFilter = document.getElementById('sectorFilter');
        const barangayFilter = document.getElementById('barangayFilter');
        const statusFilter = document.getElementById('statusFilter');
        const resetButton = document.getElementById('resetFilters');
        const countNode = document.getElementById('programCount');
        const emptyNode = document.getElementById('noProgramsMessage');
        const modal = document.getElementById('programDetailsModal');
        const modalCloseButton = document.getElementById('closeProgramModal');
        const openFeaturedModalButton = document.getElementById('openFeaturedModal');
        const cards = Array.from(document.querySelectorAll('.program-card-v2'));

        document.querySelectorAll('.program-progress-fill[data-budget]').forEach((node) => {
            const budget = Number(node.getAttribute('data-budget') || 0);
            node.style.width = `${budget}%`;
        });

        function formatNumber(value) {
            return new Intl.NumberFormat().format(value || 0);
        }

        function applyFilters() {
            const keyword = (searchInput?.value || '').trim().toLowerCase();
            const sector = sectorFilter?.value || '';
            const barangay = (barangayFilter?.value || '').toLowerCase();
            const status = statusFilter?.value || '';

            let visible = 0;
            cards.forEach((card) => {
                const haystack = `${card.dataset.name} ${card.dataset.summary} ${card.dataset.barangay} ${card.dataset.sector}`;
                const matchesKeyword = !keyword || haystack.includes(keyword);
                const matchesSector = !sector || card.dataset.sector === sector;
                const matchesBarangay = !barangay || card.dataset.barangay === barangay;
                const matchesStatus = !status || card.dataset.status === status;
                const show = matchesKeyword && matchesSector && matchesBarangay && matchesStatus;
                card.hidden = !show;
                if (show) {
                    visible += 1;
                }
            });

            if (countNode) {
                countNode.textContent = `Showing ${visible} program${visible === 1 ? '' : 's'}`;
            }
            if (emptyNode) {
                emptyNode.hidden = visible > 0;
            }
        }

        function renderTimeline(items) {
            const timeline = document.getElementById('modalTimeline');
            if (!timeline) {
                return;
            }

            timeline.innerHTML = '';
            (items || []).forEach((item) => {
                const li = document.createElement('li');
                li.className = `program-timeline-item ${item.status}`;
                li.innerHTML = `<div><strong>${item.title}</strong><p>${item.date}</p></div><span>${(statusMeta[item.status]?.label || item.status || '').toUpperCase()}</span>`;
                timeline.appendChild(li);
            });
        }

        function renderBudgetItems(items) {
            const budgetList = document.getElementById('modalBudgetItems');
            if (!budgetList) {
                return;
            }

            budgetList.innerHTML = '';
            (items || []).forEach((item) => {
                const li = document.createElement('li');
                li.innerHTML = `<span>${item.label}</span><strong>PHP ${formatNumber(item.amount)}</strong>`;
                budgetList.appendChild(li);
            });
        }

        function openProgramModal() {
            if (!modal) {
                return;
            }

            modal.classList.add('open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('program-modal-open');

            const modalGrid = modal.querySelector('.program-modal-grid');
            if (modalGrid) {
                modalGrid.scrollTop = 0;
            }
        }

        function closeProgramModal() {
            if (!modal) {
                return;
            }
            modal.classList.remove('open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('program-modal-open');
        }

        function renderModal(programId) {
            const program = programs.find((entry) => Number(entry.id) === Number(programId));
            if (!program) {
                return;
            }

            const modalImage = document.getElementById('modalImage');
            const modalTitle = document.getElementById('modalTitle');
            const modalSummary = document.getElementById('modalSummary');
            const modalAbout = document.getElementById('modalAbout');
            const modalTopbarTitle = document.getElementById('modalTopbarTitle');
            const modalStatus = document.getElementById('modalStatus');
            const modalSector = document.getElementById('modalSector');
            const modalParticipants = document.getElementById('modalParticipants');
            const modalDuration = document.getElementById('modalDuration');
            const modalCapacity = document.getElementById('modalCapacity');
            const modalBudgetTotal = document.getElementById('modalBudgetTotal');
            const modalBudgetUtilization = document.getElementById('modalBudgetUtilization');
            const modalBudgetBar = document.getElementById('modalBudgetBar');

            if (modalImage) {
                modalImage.src = program.hero;
                modalImage.alt = program.title;
            }
            if (modalTitle) modalTitle.textContent = program.title;
            if (modalTopbarTitle) modalTopbarTitle.textContent = `${program.title} Details`;
            if (modalSummary) modalSummary.textContent = program.summary;
            if (modalAbout) modalAbout.textContent = program.about;

            if (modalStatus) {
                modalStatus.textContent = statusMeta[program.status]?.label || program.status;
                modalStatus.className = `program-badge program-badge-status ${program.status}`;
            }

            if (modalSector) {
                modalSector.textContent = sectorMeta[program.sector]?.label || program.sector;
                modalSector.className = `program-badge program-badge-sector ${program.sector}`;
            }

            if (modalParticipants) modalParticipants.textContent = formatNumber(program.participants);
            if (modalDuration) modalDuration.textContent = program.duration;
            if (modalCapacity) modalCapacity.textContent = formatNumber(program.capacity);
            if (modalBudgetTotal) modalBudgetTotal.textContent = `PHP ${formatNumber(program.budgetTotal)}`;
            if (modalBudgetUtilization) modalBudgetUtilization.textContent = `${program.budgetUtilization}%`;
            if (modalBudgetBar) modalBudgetBar.style.width = `${program.budgetUtilization}%`;

            renderTimeline(program.timeline);
            renderBudgetItems(program.budgetItems);
            openProgramModal();
        }

        document.querySelectorAll('[data-program-action="modal"]').forEach((button) => {
            button.addEventListener('click', () => {
                renderModal(button.dataset.programId);
            });
        });

        openFeaturedModalButton?.addEventListener('click', () => {
            renderModal(programs[0]?.id);
        });

        modalCloseButton?.addEventListener('click', closeProgramModal);

        modal?.addEventListener('click', (event) => {
            const target = event.target;
            if (target instanceof HTMLElement && target.dataset.closeModal === 'true') {
                closeProgramModal();
                return;
            }

            if (target === modal) {
                closeProgramModal();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeProgramModal();
            }
        });

        [searchInput, sectorFilter, barangayFilter, statusFilter].forEach((el) => {
            el?.addEventListener('input', applyFilters);
            el?.addEventListener('change', applyFilters);
        });

        resetButton?.addEventListener('click', () => {
            if (searchInput) searchInput.value = '';
            if (sectorFilter) sectorFilter.value = '';
            if (barangayFilter) barangayFilter.value = '';
            if (statusFilter) statusFilter.value = '';
            applyFilters();
        });

        applyFilters();
    });
</script>
@endpush
@endsection
