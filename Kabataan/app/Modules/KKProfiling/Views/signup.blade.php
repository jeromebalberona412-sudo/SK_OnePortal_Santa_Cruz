<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - KK Profiling - SK OnePortal</title>
    @vite([
        'app/Modules/Authentication/assets/css/youth-login.css',
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
    <style>
        .youth-login-page {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            position: relative;
            background: linear-gradient(135deg, #022a54, #0450a8 55%, #1a6fd4);
            overflow: hidden;
        }

        .youth-login-container {
            position: relative;
            z-index: 10;
            flex: 1;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            min-height: 100vh;
            width: 100%;
            padding-top: 2rem;
            padding-bottom: 2rem;
            animation: pageFadeIn 0.6s ease forwards;
            overflow-y: auto;
        }

        @keyframes pageFadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }

        .youth-login-section {
            width: 100%;
            max-width: 1000px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            margin: 0 auto;
        }

        .youth-login-card {
            width: 100%;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(40px);
            border-radius: 28px;
            padding: 2.5rem 3rem;
            box-shadow: 0 32px 80px rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
        }

        .youth-login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #44a53e 0%, #fdc020 50%, #0450a8 100%);
        }

        .card-header {
            margin-bottom: 2rem;
            text-align: center;
        }

        .card-title {
            font-size: 2rem;
            font-weight: 800;
            background: linear-gradient(135deg, #0450a8 0%, #0d5fc4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.625rem;
            letter-spacing: -0.03em;
            line-height: 1.2;
        }

        .card-subtitle {
            font-size: 0.95rem;
            color: #666;
            font-weight: 500;
            letter-spacing: 0.01em;
        }

        .status-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 0.6rem;
            margin-bottom: 1.5rem;
            padding: 0.75rem 1rem;
            background: #f8f9ff;
            border-radius: 8px;
            border: 1px solid #e8eaf6;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.78rem;
            color: #555;
        }

        .legend-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .barangay-search {
            margin-bottom: 1.25rem;
        }

        .barangay-search input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 0.95rem;
            transition: border-color 0.2s;
            box-sizing: border-box;
        }

        .barangay-search input:focus {
            outline: none;
            border-color: #44a53e;
        }

        .barangay-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 0.85rem;
            margin-bottom: 2rem;
        }

        .barangay-btn {
            padding: 1rem 0.75rem 0.75rem;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            background: white;
            cursor: not-allowed;
            transition: all 0.2s ease;
            font-size: 0.9rem;
            font-weight: 500;
            color: #aaa;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.4rem;
        }

        .barangay-btn.is-open {
            cursor: pointer;
            color: #222;
            border-color: #c5cae9;
        }

        .barangay-btn.is-open:hover {
            border-color: #44a53e;
            background: #f5f7ff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(68, 165, 62, 0.15);
        }

        .barangay-btn.is-open:active {
            transform: translateY(0);
        }

        .sched-badge {
            display: inline-block;
            font-size: 0.68rem;
            font-weight: 600;
            padding: 0.15rem 0.5rem;
            border-radius: 20px;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }

        .badge-ongoing     { background: #e8f5e9; color: #2e7d32; }
        .badge-upcoming    { background: #e3f2fd; color: #1565c0; }
        .badge-completed   { background: #f3e5f5; color: #6a1b9a; }
        .badge-cancelled   { background: #fce4ec; color: #b71c1c; }
        .badge-rescheduled { background: #fff8e1; color: #e65100; }
        .badge-no-sched    { background: #f5f5f5; color: #bbb; font-weight: 400; text-transform: none; font-size: 0.7rem; }

        .sched-dates {
            display: block;
            font-size: 0.67rem;
            color: #888;
            font-weight: 400;
            margin-top: 0.15rem;
            line-height: 1.3;
        }

        .no-results {
            text-align: center;
            padding: 2rem 1rem;
            color: #999;
        }

        @media (max-width: 1024px) {
            .youth-login-container {
                padding-top: 2rem;
                padding-bottom: 2rem;
            }
            .youth-login-section {
                width: 100%;
                max-width: 650px;
                margin: 0 auto;
                padding: 0 2rem 4rem;
            }
        }

        @media (max-width: 768px) {
            .youth-login-section { padding: 0 2rem 3rem; max-width: 600px; }
            .youth-login-card { padding: 3rem 2.5rem; border-radius: 28px; }
            .card-title { font-size: 2.25rem; }
        }

        @media (max-width: 640px) {
            .youth-login-section { padding: 0 1.5rem 2.5rem; max-width: 100%; }
            .youth-login-card { padding: 2.5rem 2rem; border-radius: 24px; }
            .card-header { margin-bottom: 2rem; }
            .card-title { font-size: 2rem; }
        }
    </style>
</head>
<body class="youth-login-page">
    @include('dashboard::loading')
    
    <!-- Animated Background -->
    <div class="youth-bg-wrapper">
        <div class="youth-bg-image"></div>
        <div class="youth-gradient-overlay"></div>
        <div class="floating-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
        </div>
    </div>

    <main class="youth-login-container">
        <!-- Centered Card -->
        <div class="youth-login-section">
            <div class="youth-login-card">
                <div class="card-header">
                    <h2 class="card-title">KK Profiling Sign Up 📋</h2>
                    <p class="card-subtitle">Select your barangay to get started</p>
                </div>

                <!-- Status legend -->
                <div class="status-legend">
                    <span class="legend-item"><span class="legend-dot" style="background:#2e7d32;"></span> Ongoing</span>
                    <span class="legend-item"><span class="legend-dot" style="background:#1565c0;"></span> Upcoming</span>
                    <span class="legend-item"><span class="legend-dot" style="background:#e65100;"></span> Rescheduled</span>
                    <span class="legend-item"><span class="legend-dot" style="background:#6a1b9a;"></span> Completed</span>
                    <span class="legend-item"><span class="legend-dot" style="background:#b71c1c;"></span> Cancelled</span>
                </div>

                <div class="barangay-search">
                    <input type="text" id="barangaySearch" placeholder="Search barangay..." aria-label="Search barangay">
                </div>

                <div class="barangay-grid" id="barangayGrid">
                    @foreach($barangays as $brgy)
                        @php
                            $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $brgy->name));
                            $slug = trim($slug, '-');
                        @endphp
                        <button
                            type="button"
                            class="barangay-btn"
                            data-name="{{ $brgy->name }}"
                            data-slug="{{ $slug }}"
                            data-barangay-id="{{ $brgy->id }}"
                            disabled
                        >
                            {{ $brgy->name }}
                            <span class="sched-badge badge-no-sched">No schedule</span>
                        </button>
                    @endforeach
                </div>

                <div class="no-results" id="noResults" style="display:none;">
                    No barangay found. Try a different search term.
                </div>
            </div>
        </div>
    </main>

    <!-- Load loading script AFTER the overlay HTML is rendered -->
    <script src="{{ url('/shared/js/loading.js') }}"></script>

    <script>
        const searchInput  = document.getElementById('barangaySearch');
        const barangayGrid = document.getElementById('barangayGrid');
        const noResults    = document.getElementById('noResults');

        function fmtDate(str) {
            if (!str) return '';
            return new Date(str).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' });
        }

        const statusConfig = {
            Ongoing:     { cls: 'badge-ongoing',     label: 'Ongoing',     open: true,  dates: false },
            Upcoming:    { cls: 'badge-upcoming',    label: 'Upcoming',    open: false, dates: true  },
            Rescheduled: { cls: 'badge-rescheduled', label: 'Rescheduled', open: false, dates: true  },
            Completed:   { cls: 'badge-completed',   label: 'Completed',   open: false, dates: false },
            Cancelled:   { cls: 'badge-cancelled',   label: 'Cancelled',   open: false, dates: false },
        };

        fetch('/api/kkprofiling/open-barangays')
            .then(r => r.json())
            .then(({ schedules }) => {
                const map = new Map(schedules.map(s => [s.barangay_id, s]));
                barangayGrid.querySelectorAll('.barangay-btn').forEach(btn => {
                    const sched  = map.get(Number(btn.dataset.barangayId));
                    const badge  = btn.querySelector('.sched-badge');
                    if (!sched) return;

                    const cfg = statusConfig[sched.status];
                    if (!cfg) return;

                    badge.className = 'sched-badge ' + cfg.cls;
                    badge.innerHTML = cfg.label
                        + (cfg.dates ? `<span class="sched-dates">${fmtDate(sched.date_start)} – ${fmtDate(sched.date_expiry)}</span>` : '');

                    if (cfg.open) {
                        btn.disabled = false;
                        btn.classList.add('is-open');
                        btn.onclick = () => selectBarangayAndGo(btn.dataset.slug);
                    }
                });
            })
            .catch(() => { /* fail safe — all remain disabled */ });

        searchInput.addEventListener('input', e => {
            const q = e.target.value.toLowerCase();
            let visible = 0;
            barangayGrid.querySelectorAll('.barangay-btn').forEach(btn => {
                const match = btn.dataset.name.toLowerCase().includes(q);
                btn.style.display = match ? '' : 'none';
                if (match) visible++;
            });
            noResults.style.display = visible === 0 ? 'block' : 'none';
        });

        function selectBarangayAndGo(slug) {
            if (window.showLoading) window.showLoading('Redirecting...');
            setTimeout(() => {
                window.location.href = `/kkprofiling/${slug}`;
            }, 300);
        }
    </script>
</body>
</html>
