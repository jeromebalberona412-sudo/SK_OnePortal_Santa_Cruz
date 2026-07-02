document.addEventListener('DOMContentLoaded', () => initArchivedYouthRecords());

let ayrRecords = [];
let ayrFiltered = [];
let ayrCurrentPage = 1;
const ayrPerPage = 10;
let ayrActiveFilter = 'all';
let ayrArchiveTerm = '';
let ayrSearchQuery = '';
let ayrIsLoading = false;

function initArchivedYouthRecords() {
    bindSearch();
    bindFilterTabs();
    bindViewModal();

    const boot = () => loadData();

    if (window.SkArchive) {
        SkArchive.mountShowArchiveFilter((termId) => {
            ayrArchiveTerm = termId;
            ayrApplyClientFilters();
            ayrCurrentPage = 1;
            renderTable();
        }).then(boot);
        return;
    }

    boot();
}

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

async function loadData() {
    if (ayrIsLoading) return;
    ayrIsLoading = true;
    setTableLoading(true);

    const params = new URLSearchParams();
    if (ayrSearchQuery) params.set('search', ayrSearchQuery);
    if (ayrActiveFilter !== 'all') params.set('filter', ayrActiveFilter);

    try {
        const res = await fetch(`/archived-youth-records/data?${params.toString()}`, {
            headers: { Accept: 'application/json' },
        });
        if (!res.ok) throw new Error('Failed to load archived records.');
        const json = await res.json();
        ayrRecords = (json.data || []).map(normalizeRecord);
        renderStats(json.stats || {});
        ayrApplyClientFilters();
        ayrCurrentPage = 1;
        renderTable();
    } catch (err) {
        showToast(err.message || 'Failed to load archived records.', 'error');
        ayrRecords = [];
        ayrFiltered = [];
        renderStats({ total: 0, today: 0, month: 0 });
        renderTable();
    } finally {
        ayrIsLoading = false;
        setTableLoading(false);
    }
}

function normalizeRecord(r) {
    return {
        ...r,
        _archivedTs: r.archived_at ? new Date(r.archived_at) : null,
        skTerm: window.SkArchive && r.archived_at
            ? SkArchive.inferTermFromDate(r.archived_at)
            : (window.SkArchive?.getActiveTermId?.() || ''),
    };
}

function ayrApplyClientFilters() {
    let list = ayrRecords.slice();
    if (window.SkArchive) {
        list = SkArchive.filterByArchiveTerm(list, ayrArchiveTerm, ['_archivedTs', 'archived_at']);
    }
    ayrFiltered = list;
}

function setTableLoading(loading) {
    const tbody = document.getElementById('ayrTableBody');
    if (!tbody || !loading) return;
    tbody.innerHTML = '<tr class="empty-state-row"><td colspan="11">Loading archived records…</td></tr>';
}

function renderStats(stats) {
    const row = document.getElementById('ayrStatsRow');
    if (!row) return;
    const total = stats.total ?? 0;
    const month = stats.month ?? 0;
    const today = stats.today ?? 0;

    row.innerHTML = `
        <div class="stat-card stat-card-amber">
            <div class="stat-card-top">
                <span class="stat-card-value">${total}</span>
                <div class="stat-card-icon stat-icon-orange">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 3H8v4h8V3z"/></svg>
                </div>
            </div>
            <span class="stat-card-label">Total Archived</span>
        </div>
        <div class="stat-card stat-card-orange">
            <div class="stat-card-top">
                <span class="stat-card-value">${month}</span>
                <div class="stat-card-icon stat-icon-orange">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </div>
            </div>
            <span class="stat-card-label">This Month</span>
        </div>
        <div class="stat-card stat-card-blue">
            <div class="stat-card-top">
                <span class="stat-card-value">${today}</span>
                <div class="stat-card-icon stat-icon-blue">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
            </div>
            <span class="stat-card-label">Today</span>
        </div>`;
}

function bindFilterTabs() {
    document.querySelectorAll('.filter-tab').forEach((btn) => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.filter-tab').forEach((b) => b.classList.remove('active'));
            this.classList.add('active');
            ayrActiveFilter = this.dataset.filter;
            const labels = {
                all: 'All Archived Records',
                today: 'Archived Today',
                week: 'Archived This Week',
                month: 'Archived This Month',
            };
            const label = document.getElementById('ayrSectionLabel');
            if (label) label.textContent = labels[ayrActiveFilter] || 'Archived Records';
            loadData();
        });
    });
}

function renderTable() {
    const tbody = document.getElementById('ayrTableBody');
    const info = document.getElementById('ayrPaginationInfo');
    if (!tbody) return;

    const start = (ayrCurrentPage - 1) * ayrPerPage;
    const end = start + ayrPerPage;
    const page = ayrFiltered.slice(start, end);

    if (ayrFiltered.length === 0) {
        tbody.innerHTML = '<tr class="empty-state-row"><td colspan="11">No archived youth records found.</td></tr>';
        if (info) info.textContent = 'No records found';
        renderPagination(0);
        return;
    }

    tbody.innerHTML = page.map((r) => {
        const fullName = r.full_name || formatFullName(r);
        return `
        <tr data-id="${r.id}">
            <td style="font-weight:600;color:#111827;">${escHtml(fullName)}</td>
            <td>${escHtml(r.age)}</td>
            <td>${escHtml(r.sex)}</td>
            <td>${escHtml(r.region)}</td>
            <td>${escHtml(r.province)}</td>
            <td>${escHtml(r.city)}</td>
            <td>${escHtml(r.purok_zone)}</td>
            <td>${escHtml(r.education)}</td>
            <td><span class="archived-at-badge">${escHtml(r.archived_date)}</span></td>
            <td><span class="archived-time-badge">${escHtml(r.archived_time)}</span></td>
            <td>
                <div class="action-btns">
                    <button type="button" class="btn-view-action" data-id="${r.id}">View</button>
                </div>
            </td>
        </tr>`;
    }).join('');

    if (info) {
        info.textContent = `Showing ${start + 1}–${Math.min(end, ayrFiltered.length)} of ${ayrFiltered.length} records`;
    }

    renderPagination(ayrFiltered.length);
    tbody.querySelectorAll('.btn-view-action').forEach((btn) => {
        btn.addEventListener('click', () => openViewModal(btn.dataset.id));
    });
}

function formatFullName(r) {
    let name = `${r.last_name}, ${r.first_name}`;
    if (r.middle_name) name += ` ${r.middle_name}`;
    if (r.suffix) name += ` ${r.suffix}`;
    return name;
}

function escHtml(value) {
    if (value == null || value === '') return '—';
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function formatArchiveReason(reason) {
    if (reason === 'aged_out') return 'Aged out (over 30)';
    return reason || '—';
}

function renderPagination(total) {
    const pages = Math.ceil(total / ayrPerPage);
    const nums = document.getElementById('ayrPageNumbers');
    const prev = document.getElementById('ayrPrevBtn');
    const next = document.getElementById('ayrNextBtn');

    if (nums) {
        nums.innerHTML = Array.from({ length: pages }, (_, i) => `
            <button type="button" class="pagination-btn ${i + 1 === ayrCurrentPage ? 'active' : ''}">${i + 1}</button>
        `).join('');
        nums.querySelectorAll('.pagination-btn').forEach((btn, i) => {
            btn.addEventListener('click', () => { ayrCurrentPage = i + 1; renderTable(); });
        });
    }
    if (prev) {
        prev.disabled = ayrCurrentPage === 1;
        prev.onclick = () => { if (ayrCurrentPage > 1) { ayrCurrentPage--; renderTable(); } };
    }
    if (next) {
        next.disabled = ayrCurrentPage >= pages || pages === 0;
        next.onclick = () => { if (ayrCurrentPage < pages) { ayrCurrentPage++; renderTable(); } };
    }
}

function bindSearch() {
    const input = document.getElementById('ayrSearch');
    if (!input) return;
    let debounce = null;
    input.addEventListener('input', function () {
        ayrSearchQuery = this.value.trim();
        clearTimeout(debounce);
        debounce = setTimeout(() => loadData(), 300);
    });
}

function openViewModal(id) {
    const r = ayrRecords.find((x) => String(x.id) === String(id));
    if (!r) return;

    const dateEl = document.getElementById('ayrViewArchivedDate');
    const timeEl = document.getElementById('ayrViewArchivedTime');
    const reasonEl = document.getElementById('ayrViewArchiveReason');
    if (dateEl) dateEl.textContent = r.archived_date || '—';
    if (timeEl) timeEl.textContent = r.archived_time || '—';
    if (reasonEl) reasonEl.textContent = formatArchiveReason(r.archive_reason);

    populateAyrViewModal(r);

    const modal = document.getElementById('ayrViewModal');
    if (modal) modal.style.display = 'flex';
}

function populateAyrViewModal(record) {
    const request = {
        respondentNumber: record.respondent_display || '—',
        date: record.submitted_at || '—',
        firstName: record.first_name,
        middleName: record.middle_name,
        lastName: record.last_name,
        suffix: record.suffix || 'None',
        age: record.age,
        birthday: record.birthday,
        sex: record.sex,
        civilStatus: record.civil_status,
        region: record.region,
        province: record.province,
        city: record.city,
        barangay: record.barangay,
        purokZone: record.purok_zone,
        emailAddress: record.email,
        contactNumber: record.contact_number,
        youthClassification: record.youth_classification,
        youthAgeGroup: record.youth_age_group,
        workStatus: record.work_status,
        educationalBackground: record.education,
        registeredSKVoter: record.sk_voter,
        registeredNationalVoter: record.national_voter,
        votingHistory: record.sk_voted,
        attendedKKAssembly: record.kk_assembly,
        kkTimes: record.kk_times,
        kkReason: record.kk_reason,
        facebookAccount: record.facebook,
        willingToJoinGroupChat: record.group_chat,
        signature: record.signature,
        barangayLogoUrl: record.barangay_logo_url,
    };

    const setVal = (id, val) => {
        const el = document.getElementById(id);
        if (el) el.textContent = val ?? '—';
    };

    const setCheck = (id, checked) => {
        const el = document.getElementById(id);
        if (!el) return;
        if (el.type === 'checkbox') {
            el.checked = !!checked;
            return;
        }
        const text = el.textContent.replace(/^[☐☑]\s*/, '');
        el.textContent = (checked ? '☑ ' : '☐ ') + text;
        el.style.fontWeight = checked ? '700' : '400';
        el.style.color = checked ? '#1a1a1a' : '#6b7280';
    };

    const matchesValue = (stored, candidates) => {
        const normalized = (stored || '').trim().toLowerCase();
        return candidates.some((c) => normalized === c.trim().toLowerCase());
    };

    const formatBirthdayDisplay = (value) => {
        if (!value || value === '—') return '—';
        const iso = /^(\d{4})-(\d{2})-(\d{2})$/.exec(value);
        if (iso) return `${iso[2]}/${iso[3]}/${iso[1]}`;
        return value;
    };

    setVal('kkViewRespondentNumber', request.respondentNumber);
    setVal('kkViewDate', request.date);
    setVal('kkViewLastName', request.lastName || '—');
    setVal('kkViewFirstName', request.firstName || '—');
    setVal('kkViewMiddleName', request.middleName || '—');
    setVal('kkViewSuffix', request.suffix || 'None');
    setVal('kkViewRegion', request.region || '—');
    setVal('kkViewProvince', request.province || '—');
    setVal('kkViewCity', request.city || '—');
    setVal('kkViewBarangay', request.barangay || '—');
    setVal('kkViewPurokZone', request.purokZone || '—');
    setCheck('kkViewSex_Male', request.sex === 'Male');
    setCheck('kkViewSex_Female', request.sex === 'Female');
    setVal('kkViewAge', request.age || '—');
    setVal('kkViewBirthday', formatBirthdayDisplay(request.birthday) || '—');
    setVal('kkViewEmailAddress', request.emailAddress || '—');
    setVal('kkViewContactNumber', request.contactNumber || '—');

    const csMap = {
        kkViewCS_Single: 'Single', kkViewCS_Married: 'Married', kkViewCS_Widowed: 'Widowed',
        kkViewCS_Divorced: 'Divorced', kkViewCS_Separated: 'Separated', kkViewCS_Annulled: 'Annulled',
        kkViewCS_Unknown: 'Unknown', kkViewCS_Livein: 'Live-in',
    };
    Object.entries(csMap).forEach(([id, val]) => setCheck(id, request.civilStatus === val));

    const yagMap = {
        kkViewYAG_Child: 'Child Youth (15-17 yrs old)',
        kkViewYAG_Core: 'Core Youth (18-24 yrs old)',
        kkViewYAG_Young: 'Young Adult (15-30 yrs old)',
    };
    Object.entries(yagMap).forEach(([id, val]) => setCheck(id, request.youthAgeGroup === val));

    const ebMap = {
        kkViewEB_ElemLevel: ['Elementary Level'],
        kkViewEB_ElemGrad: ['Elementary Grad'],
        kkViewEB_HSLevel: ['High School Level', 'High school level'],
        kkViewEB_HSGrad: ['High School Grad', 'High school Grad'],
        kkViewEB_VocGrad: ['Vocational Grad'],
        kkViewEB_ColLevel: ['College Level'],
        kkViewEB_ColGrad: ['College Grad'],
        kkViewEB_MasLevel: ['Masters Level'],
        kkViewEB_MasGrad: ['Masters Grad'],
        kkViewEB_DocLevel: ['Doctorate Level'],
        kkViewEB_DocGrad: ['Doctorate Graduate'],
    };
    Object.entries(ebMap).forEach(([id, vals]) => setCheck(id, matchesValue(request.educationalBackground, vals)));

    const ycMap = {
        kkViewYC_ISY: ['In School Youth', 'In school Youth'],
        kkViewYC_OSY: ['Out of School Youth'],
        kkViewYC_Working: ['Working Youth'],
        kkViewYC_PWD: ['Person w/ Disability'],
        kkViewYC_CICL: ['Children in Conflict w/ Law', 'Children In Conflict w/ Law'],
        kkViewYC_IP: ['Indigenous People'],
    };
    Object.entries(ycMap).forEach(([id, vals]) => setCheck(id, matchesValue(request.youthClassification, vals)));

    const wsMap = {
        kkViewWS_Employed: 'Employed', kkViewWS_Unemployed: 'Unemployed',
        kkViewWS_SelfEmployed: 'Self-Employed', kkViewWS_Looking: 'Currently looking for a Job',
        kkViewWS_NotInterested: 'Not Interested Looking for a Job',
    };
    Object.entries(wsMap).forEach(([id, val]) => setCheck(id, request.workStatus === val));

    setCheck('kkViewSKV_Yes', request.registeredSKVoter === 'Yes');
    setCheck('kkViewSKV_No', request.registeredSKVoter === 'No');
    setCheck('kkViewNV_Yes', request.registeredNationalVoter === 'Yes');
    setCheck('kkViewNV_No', request.registeredNationalVoter === 'No');
    setCheck('kkViewVH_Yes', request.votingHistory === 'Yes');
    setCheck('kkViewVH_No', request.votingHistory === 'No');
    setCheck('kkViewKK_Yes', request.attendedKKAssembly === 'Yes');
    setCheck('kkViewKK_No', request.attendedKKAssembly === 'No');
    setCheck('kkViewKKTimes_12', request.kkTimes === '1-2 Times');
    setCheck('kkViewKKTimes_34', request.kkTimes === '3-4 Times');
    setCheck('kkViewKKTimes_5', request.kkTimes === '5 and above');

    const normalizedReason = (request.kkReason || '').trim();
    setCheck('kkViewVR_NoKK',
        normalizedReason === 'There was no KK Assembly Meeting'
        || normalizedReason === 'There was no KK Assembly');
    setCheck('kkViewVR_NotInt',
        normalizedReason === 'Not interested to Attend'
        || normalizedReason === 'Not Interested to Attend');

    setVal('kkViewFacebookAccount', request.facebookAccount || '—');
    setCheck('kkViewGC_Yes', request.willingToJoinGroupChat === 'Yes');
    setCheck('kkViewGC_No', request.willingToJoinGroupChat === 'No');

    const logoEl = document.getElementById('kkViewBarangayLogo');
    if (logoEl && request.barangayLogoUrl) {
        logoEl.src = request.barangayLogoUrl;
        logoEl.alt = `${request.barangay || 'Barangay'} SK Logo`;
    }

    const sigNameEl = document.getElementById('kkViewSignatureName');
    const sigPreview = document.getElementById('kkViewSignaturePreview');
    const sigOverlay = document.getElementById('kkViewSignatureOverlay');
    const nameParts = [
        request.firstName,
        request.middleName ? `${request.middleName.charAt(0)}.` : null,
        request.lastName,
        request.suffix && request.suffix !== 'None' ? request.suffix : null,
    ].filter(Boolean);
    if (sigNameEl) sigNameEl.textContent = nameParts.join(' ') || '—';
    if (sigPreview && sigOverlay) {
        if (request.signature && request.signature.startsWith('data:image')) {
            sigPreview.src = request.signature;
            sigOverlay.style.display = 'flex';
        } else {
            sigPreview.removeAttribute('src');
            sigOverlay.style.display = 'none';
        }
    }

    const rejectionWrap = document.getElementById('kkViewRejectionWrap');
    if (rejectionWrap) rejectionWrap.style.display = 'none';
}

function bindViewModal() {
    const modal = document.getElementById('ayrViewModal');
    const box = document.getElementById('ayrViewModalBox');
    const closeBtn = document.getElementById('ayrViewModalClose');
    const toggleBtn = document.getElementById('ayrViewModalToggle');

    const close = () => { if (modal) modal.style.display = 'none'; };
    if (closeBtn) closeBtn.addEventListener('click', close);
    if (modal) {
        modal.addEventListener('click', (e) => { if (e.target === modal) close(); });
    }
    if (toggleBtn && box) {
        toggleBtn.addEventListener('click', () => box.classList.toggle('is-maximized'));
    }
}

function showToast(message, type = 'info') {
    const toast = document.getElementById('ayrToast');
    if (!toast) return;
    toast.textContent = message;
    toast.className = `ayr-toast is-visible${type === 'error' ? ' is-error' : ''}`;
    setTimeout(() => { toast.className = 'ayr-toast'; }, 3200);
}
