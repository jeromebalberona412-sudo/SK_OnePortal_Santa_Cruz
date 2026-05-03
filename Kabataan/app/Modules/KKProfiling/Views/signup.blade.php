<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - KK Profiling - SK OnePortal</title>
    @vite([
        'app/Modules/Homepage/assets/css/homepage.css',
        'app/Modules/Shared/assets/css/loading.css',
        'app/Modules/Shared/assets/js/loading.js',
    ])
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            padding: 2rem;
        }
        .signup-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 640px;
            width: 100%;
            padding: 3rem;
        }
        .signup-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .signup-header img { height: 48px; margin-bottom: 1rem; }
        .signup-header h1 { font-size: 1.75rem; color: #1a1a1a; margin-bottom: 0.5rem; }
        .signup-header p  { color: #666; font-size: 0.95rem; }

        /* Legend */
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

        /* Search */
        .barangay-search { margin-bottom: 1.25rem; }
        .barangay-search input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 0.95rem;
            transition: border-color 0.2s;
            box-sizing: border-box;
        }
        .barangay-search input:focus { outline: none; border-color: #667eea; }

        /* Grid */
        .barangay-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 0.85rem;
            margin-bottom: 2rem;
        }
        .barangay-btn {
            padding: 1rem 0.75rem 0.75rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
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
            border-color: #667eea;
            background: #f5f7ff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102,126,234,0.15);
        }
        .barangay-btn.is-open:active { transform: translateY(0); }

        /* Status badge */
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

        /* Date range shown on Upcoming */
        .sched-dates {
            display: block;
            font-size: 0.67rem;
            color: #888;
            font-weight: 400;
            margin-top: 0.15rem;
            line-height: 1.3;
        }

        .no-results { text-align: center; padding: 2rem 1rem; color: #999; }
    </style>
</head>
<body>
    @include('dashboard::loading')

    <div class="signup-container">
        <div class="signup-header">
            <img src="/images/skoneportal_logo.webp" alt="SK OnePortal">
            <h1>KK Profiling Sign Up</h1>
            <p>Select your barangay to get started</p>
        </div>

        <!-- Status legend -->
        <div class="status-legend">
            <span class="legend-item"><span class="legend-dot" style="background:#2e7d32;"></span> Ongoing — open now</span>
            <span class="legend-item"><span class="legend-dot" style="background:#1565c0;"></span> Upcoming — not yet open</span>
            <span class="legend-item"><span class="legend-dot" style="background:#e65100;"></span> Rescheduled — new date pending</span>
            <span class="legend-item"><span class="legend-dot" style="background:#6a1b9a;"></span> Completed — profiling done</span>
            <span class="legend-item"><span class="legend-dot" style="background:#b71c1c;"></span> Cancelled — not available</span>
            <span class="legend-item"><span class="legend-dot" style="background:#ccc;"></span> No schedule</span>
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
            window.location.href = `/kkprofiling/${slug}`;
        }
    </script>
</body>
</html>
