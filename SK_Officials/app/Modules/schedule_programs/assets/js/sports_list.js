// ── Sports List Page JavaScript ───────────────────────────────────────────

document.addEventListener('DOMContentLoaded', () => {
    initSportsList();
});

function initSportsList() {
    // Pull approved applications from localStorage (seeded by sports_requests.js)
    // Fallback to sample data if nothing in storage yet
    let allApplications = JSON.parse(localStorage.getItem('sports_applications') || '[]');

    // If storage is empty, seed with sample approved data
    if (allApplications.length === 0) {
        allApplications = getSampleApprovedData();
        localStorage.setItem('sports_applications', JSON.stringify(allApplications));
    }

    // Only show Approved entries
    let approvedList = allApplications.filter(a => a.status === 'Approved');

    // If no approved yet, show all as sample approved for demo
    if (approvedList.length === 0) {
        approvedList = getSampleApprovedData();
    }

    let filtered = [...approvedList];

    const tbody         = document.getElementById('splTableBody');
    const searchInput   = document.getElementById('splSearch');
    const filterSport   = document.getElementById('splFilterSport');
    const filterDiv     = document.getElementById('splFilterDivision');

    // ── Render ──────────────────────────────────────────────────────────────
    renderTable(filtered);
    updateStats(approvedList);

    // ── Filters ─────────────────────────────────────────────────────────────
    [searchInput, filterSport, filterDiv].forEach(el => {
        if (el) el.addEventListener('input', applyFilters);
        if (el) el.addEventListener('change', applyFilters);
    });

    function applyFilters() {
        const search = (searchInput?.value || '').toLowerCase();
        const sport  = filterSport?.value || '';
        const div    = filterDiv?.value || '';

        filtered = approvedList.filter(a => {
            const name = formatName(a).toLowerCase();
            if (search && !name.includes(search) && !a.sport.toLowerCase().includes(search)) return false;
            if (sport && a.sport !== sport) return false;
            if (div && a.division !== div) return false;
            return true;
        });

        renderTable(filtered);
    }

    // ── Export CSV ───────────────────────────────────────────────────────────
    const exportBtn = document.getElementById('splExportCsvBtn');
    if (exportBtn) {
        exportBtn.addEventListener('click', () => exportCsv(filtered));
    }

    // ── Modal ────────────────────────────────────────────────────────────────
    const modal    = document.getElementById('splViewModal');
    const modalBox = document.getElementById('splViewBox');
    const closeBtn = document.getElementById('splViewClose');
    const maxBtn   = document.getElementById('splViewMaximize');

    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (modal)    modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });

    if (maxBtn && modalBox) {
        maxBtn.addEventListener('click', () => {
            modalBox.classList.toggle('spl-modal-maximized');
            const isMax = modalBox.classList.contains('spl-modal-maximized');
            maxBtn.textContent = isMax ? '⧉' : '□';
            maxBtn.title = isMax ? 'Restore Down' : 'Maximize';
            if (modal) modal.classList.toggle('spl-overlay-maximized', isMax);
        });
    }

    function closeModal() {
        if (modal) modal.style.display = 'none';
        if (modalBox) modalBox.classList.remove('spl-modal-maximized');
        if (modal) modal.classList.remove('spl-overlay-maximized');
        if (maxBtn) { maxBtn.textContent = '□'; maxBtn.title = 'Maximize'; }
    }

    // ── Render Table ─────────────────────────────────────────────────────────
    function renderTable(list) {
        if (!tbody) return;

        if (list.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" class="spl-empty">No approved sports participants found.</td></tr>`;
            return;
        }

        tbody.innerHTML = list.map((a, i) => {
            const name = formatName(a);
            return `
            <tr>
                <td class="spl-td-name">${name}</td>
                <td>${a.sport || '—'}</td>
                <td>${a.division || '—'}</td>
                <td>${a.contact || '—'}</td>
                <td>${a.dateApplied || '—'}</td>
                <td><span class="spl-badge spl-badge-approved">APPROVED</span></td>
                <td>
                    <button class="spl-action-btn" data-idx="${i}">View</button>
                </td>
            </tr>`;
        }).join('');

        tbody.querySelectorAll('button[data-idx]').forEach(btn => {
            btn.addEventListener('click', function () {
                const idx = parseInt(this.getAttribute('data-idx'), 10);
                openModal(list[idx]);
            });
        });
    }

    // ── Open Modal ───────────────────────────────────────────────────────────
    function openModal(a) {
        const body = document.getElementById('splViewBody');
        if (!modal || !body) return;

        const chk = (checked) => checked
            ? `<span class="spl-req-chk spl-req-chk-on"><span class="spl-req-chk-mark"></span></span>`
            : `<span class="spl-req-chk"></span>`;

        const reqs = a.requirements || {};

        body.innerHTML = `
        <div class="spl-detail-card">
            <div class="spl-detail-section-title">Personal Information</div>
            <div class="spl-detail-grid spl-grid-3">
                <div class="spl-detail-item">
                    <span class="spl-detail-label">Last Name</span>
                    <span class="spl-detail-value">${a.lastName || '—'}</span>
                </div>
                <div class="spl-detail-item">
                    <span class="spl-detail-label">First Name</span>
                    <span class="spl-detail-value">${a.firstName || '—'}</span>
                </div>
                <div class="spl-detail-item">
                    <span class="spl-detail-label">Middle Name</span>
                    <span class="spl-detail-value">${a.middleName || '—'}</span>
                </div>
                <div class="spl-detail-item">
                    <span class="spl-detail-label">Date of Birth</span>
                    <span class="spl-detail-value">${a.dateOfBirth || '—'}</span>
                </div>
                <div class="spl-detail-item">
                    <span class="spl-detail-label">Age</span>
                    <span class="spl-detail-value">${a.age || '—'}</span>
                </div>
                <div class="spl-detail-item">
                    <span class="spl-detail-label">Contact No.</span>
                    <span class="spl-detail-value">${a.contact || '—'}</span>
                </div>
                <div class="spl-detail-item spl-detail-full">
                    <span class="spl-detail-label">Address</span>
                    <span class="spl-detail-value">${a.address || '—'}</span>
                </div>
                <div class="spl-detail-item spl-detail-full">
                    <span class="spl-detail-label">Email</span>
                    <span class="spl-detail-value">${a.email || '—'}</span>
                </div>
            </div>
        </div>

        <div class="spl-detail-card">
            <div class="spl-detail-section-title">Sports Information</div>
            <div class="spl-detail-grid">
                <div class="spl-detail-item">
                    <span class="spl-detail-label">Sport</span>
                    <span class="spl-detail-value">${a.sport || '—'}</span>
                </div>
                <div class="spl-detail-item">
                    <span class="spl-detail-label">Division</span>
                    <span class="spl-detail-value">${a.division || '—'}</span>
                </div>
                <div class="spl-detail-item">
                    <span class="spl-detail-label">Date Applied</span>
                    <span class="spl-detail-value">${a.dateApplied || '—'}</span>
                </div>
                <div class="spl-detail-item">
                    <span class="spl-detail-label">Status</span>
                    <span class="spl-detail-value"><span class="spl-badge spl-badge-approved">APPROVED</span></span>
                </div>
            </div>
        </div>

        <div class="spl-detail-card">
            <div class="spl-detail-section-title">Submitted Requirements</div>
            <div class="spl-req-list">
                <div class="spl-req-item">
                    ${chk(reqs.birthCertificate)}
                    <span>Birth Certificate</span>
                </div>
                <div class="spl-req-item">
                    ${chk(reqs.validId)}
                    <span>Valid ID</span>
                </div>
                <div class="spl-req-item">
                    ${chk(reqs.medicalCertificate)}
                    <span>Medical Certificate</span>
                </div>
                <div class="spl-req-item">
                    ${chk(reqs.parentConsent)}
                    <span>Parent / Guardian Consent</span>
                </div>
            </div>
        </div>
        `;

        modal.style.display = 'flex';
    }

    // ── Update Stats ─────────────────────────────────────────────────────────
    function updateStats(list) {
        const total      = list.length;
        const basketball = list.filter(a => a.sport === 'Basketball').length;
        const volleyball = list.filter(a => a.sport === 'Volleyball').length;
        const other      = list.filter(a => a.sport !== 'Basketball' && a.sport !== 'Volleyball').length;

        const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
        set('splStatTotal',      total);
        set('splStatBasketball', basketball);
        set('splStatVolleyball', volleyball);
        set('splStatOther',      other);
    }

    // ── Export CSV ───────────────────────────────────────────────────────────
    function exportCsv(list) {
        if (list.length === 0) { alert('No data to export.'); return; }
        const headers = ['Full Name', 'Sport', 'Division', 'Contact', 'Email', 'Date Applied', 'Status'];
        const rows = list.map(a => [
            formatName(a),
            a.sport || '',
            a.division || '',
            a.contact || '',
            a.email || '',
            a.dateApplied || '',
            'Approved'
        ]);
        let csv = headers.join(',') + '\n';
        rows.forEach(r => { csv += r.map(c => `"${c}"`).join(',') + '\n'; });
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `sports_list_${new Date().toISOString().split('T')[0]}.csv`;
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────
    function formatName(a) {
        let name = `${a.lastName || ''}, ${a.firstName || ''}`;
        if (a.middleName) name += ` ${a.middleName.charAt(0)}.`;
        if (a.suffix) name += ` ${a.suffix}`;
        return name.trim();
    }
}

// ── Sample Approved Data (fallback) ──────────────────────────────────────────
function getSampleApprovedData() {
    return [
        {
            id: 3001,
            lastName: 'Dela Cruz', firstName: 'Juan', middleName: 'Santos', suffix: '',
            dateOfBirth: '2003-05-15', age: 22,
            contact: '09171234567', email: 'juan.delacruz@email.com',
            address: '123 Main St., Brgy. Calios, Santa Cruz, Laguna',
            sport: 'Basketball', division: 'Youth Division (18-21)',
            dateApplied: 'Apr 28, 2026',
            requirements: { birthCertificate: true, validId: true, medicalCertificate: true, parentConsent: false },
            status: 'Approved'
        },
        {
            id: 3002,
            lastName: 'Santos', firstName: 'Maria', middleName: 'Reyes', suffix: '',
            dateOfBirth: '2001-08-22', age: 24,
            contact: '09281234567', email: 'maria.santos@email.com',
            address: '456 Rizal Ave., Brgy. Calios, Santa Cruz, Laguna',
            sport: 'Volleyball', division: 'Young Adult (22-25)',
            dateApplied: 'Apr 29, 2026',
            requirements: { birthCertificate: true, validId: true, medicalCertificate: true, parentConsent: false },
            status: 'Approved'
        },
        {
            id: 3003,
            lastName: 'Reyes', firstName: 'Pedro', middleName: 'Garcia', suffix: 'Jr.',
            dateOfBirth: '2008-03-10', age: 18,
            contact: '09391234567', email: 'pedro.reyes@email.com',
            address: '789 Bonifacio St., Brgy. Calios, Santa Cruz, Laguna',
            sport: 'Basketball', division: 'Cadet Division (15-17)',
            dateApplied: 'Apr 30, 2026',
            requirements: { birthCertificate: true, validId: true, medicalCertificate: true, parentConsent: true },
            status: 'Approved'
        },
        {
            id: 3004,
            lastName: 'Lim', firstName: 'Ana', middleName: 'Cruz', suffix: '',
            dateOfBirth: '2004-11-18', age: 21,
            contact: '09501234567', email: 'ana.lim@email.com',
            address: '321 Mabini St., Brgy. Calios, Santa Cruz, Laguna',
            sport: 'Volleyball', division: 'Youth Division (18-21)',
            dateApplied: 'May 1, 2026',
            requirements: { birthCertificate: true, validId: true, medicalCertificate: true, parentConsent: false },
            status: 'Approved'
        },
        {
            id: 3005,
            lastName: 'Torres', firstName: 'Miguel', middleName: 'Bautista', suffix: '',
            dateOfBirth: '2002-09-30', age: 23,
            contact: '09831234567', email: 'miguel.torres@email.com',
            address: '147 Aguinaldo Ave., Brgy. Calios, Santa Cruz, Laguna',
            sport: 'Basketball', division: 'Young Adult (22-25)',
            dateApplied: 'May 4, 2026',
            requirements: { birthCertificate: true, validId: true, medicalCertificate: true, parentConsent: false },
            status: 'Approved'
        },
    ];
}
