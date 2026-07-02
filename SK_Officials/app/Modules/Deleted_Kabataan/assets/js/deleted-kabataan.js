// Deleted Kabataan Module — fetches soft-deleted records from API

document.addEventListener('DOMContentLoaded', function () {
    initDeletedKabataan();
});

let dkRecords = [];
let dkFiltered = [];
let dkCurrentPage = 1;
const dkPerPage = 10;
let dkPendingRestoreId = null;
let dkActiveFilter = 'all';
let dkArchiveTerm = '';
let dkSearchQuery = '';
let dkIsLoading = false;

function initDeletedKabataan() {
    bindSearch();
    bindFilterTabs();
    bindRestoreModal();
    bindViewModal();

    const boot = () => loadData();

    if (window.SkArchive) {
        SkArchive.mountShowArchiveFilter((termId) => {
            dkArchiveTerm = termId;
            dkApplyClientFilters();
            dkCurrentPage = 1;
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
    if (dkIsLoading) return;
    dkIsLoading = true;
    setTableLoading(true);

    const params = new URLSearchParams();
    if (dkSearchQuery) params.set('search', dkSearchQuery);
    if (dkActiveFilter !== 'all') params.set('filter', dkActiveFilter);

    try {
        const res = await fetch(`/deleted-kabataan/data?${params.toString()}`, {
            headers: { Accept: 'application/json' },
        });
        if (!res.ok) throw new Error('Failed to load deleted records.');
        const json = await res.json();
        dkRecords = (json.data || []).map(normalizeRecord);
        renderStats(json.stats || {});
        dkApplyClientFilters();
        dkCurrentPage = 1;
        renderTable();
    } catch (err) {
        showToast(err.message || 'Failed to load deleted records.', 'error');
        dkRecords = [];
        dkFiltered = [];
        renderStats({ total: 0, today: 0, month: 0 });
        renderTable();
    } finally {
        dkIsLoading = false;
        setTableLoading(false);
    }
}

function normalizeRecord(r) {
    return {
        ...r,
        _deletedTs: r.deleted_at ? new Date(r.deleted_at) : null,
        skTerm: window.SkArchive && r.deleted_at
            ? SkArchive.inferTermFromDate(r.deleted_at)
            : (window.SkArchive?.getActiveTermId?.() || ''),
    };
}

function dkApplyClientFilters() {
    let list = dkRecords.slice();
    if (window.SkArchive) {
        list = SkArchive.filterByArchiveTerm(list, dkArchiveTerm, ['_deletedTs', 'deleted_at']);
    }
    dkFiltered = list;
}

function setTableLoading(loading) {
    const tbody = document.getElementById('deletedKabataanTableBody');
    if (!tbody || !loading) return;
    tbody.innerHTML = `<tr class="empty-state-row"><td colspan="8">Loading deleted records…</td></tr>`;
}

// ── Stats cards ───────────────────────────────────────────────────────────────
function renderStats(stats) {
    const row = document.getElementById('dkStatsRow');
    if (!row) return;
    const total = stats.total ?? 0;
    const month = stats.month ?? 0;
    const today = stats.today ?? 0;

    row.innerHTML = `
        <div class="stat-card stat-card-red">
            <div class="stat-card-top">
                <span class="stat-card-value">${total}</span>
                <div class="stat-card-icon stat-icon-red">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6l-1 14H6L5 6"></path><path d="M10 11v6"></path><path d="M14 11v6"></path><path d="M9 6V4h6v2"></path></svg>
                </div>
            </div>
            <span class="stat-card-label">Total Deleted</span>
        </div>
        <div class="stat-card stat-card-orange">
            <div class="stat-card-top">
                <span class="stat-card-value">${month}</span>
                <div class="stat-card-icon stat-icon-orange">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="10" x2="21" y2="10"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="16" y1="2" x2="16" y2="6"></line></svg>
                </div>
            </div>
            <span class="stat-card-label">This Month</span>
        </div>
        <div class="stat-card stat-card-blue">
            <div class="stat-card-top">
                <span class="stat-card-value">${today}</span>
                <div class="stat-card-icon stat-icon-blue">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                </div>
            </div>
            <span class="stat-card-label">Today</span>
        </div>`;
}

// ── Filter tabs ───────────────────────────────────────────────────────────────
function bindFilterTabs() {
    document.querySelectorAll('.filter-tab').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.filter-tab').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            dkActiveFilter = this.dataset.filter;
            const labels = {
                all: 'All Deleted Records',
                today: 'Deleted Today',
                week: 'Deleted This Week',
                month: 'Deleted This Month',
            };
            const label = document.getElementById('dkSectionLabel');
            if (label) label.textContent = labels[dkActiveFilter] || 'Deleted Records';
            loadData();
        });
    });
}

// ── Render table ────────────────────────────────────────────────────────────────
function renderTable() {
    const tbody = document.getElementById('deletedKabataanTableBody');
    const info  = document.getElementById('deletedKabataanPaginationInfo');
    if (!tbody) return;

    const start = (dkCurrentPage - 1) * dkPerPage;
    const end   = start + dkPerPage;
    const page  = dkFiltered.slice(start, end);

    if (dkFiltered.length === 0) {
        tbody.innerHTML = `<tr class="empty-state-row"><td colspan="8">No deleted Kabataan records found.</td></tr>`;
        if (info) info.textContent = 'No records found';
        renderPagination(0);
        return;
    }

    tbody.innerHTML = page.map(r => {
        const fullName = r.full_name || formatFullName(r);
        const canRestore = !window.SkArchive || SkArchive.canRestoreRecord(r, ['_deletedTs', 'deleted_at']);
        return `
        <tr data-id="${r.id}">
            <td style="font-weight:600;color:#111827;">${escHtml(fullName)}</td>
            <td>${escHtml(r.age)}</td>
            <td>${escHtml(r.sex)}</td>
            <td>${escHtml(r.purok_zone)}</td>
            <td>${escHtml(r.education)}</td>
            <td><span class="deleted-at-badge">${escHtml(r.deleted_date)}</span></td>
            <td><span class="deleted-time-badge">${escHtml(r.deleted_time)}</span></td>
            <td>
                <div class="action-btns">
                    <button type="button" class="btn-view-action" data-id="${r.id}">View</button>
                    ${canRestore
                        ? `<button type="button" class="btn-restore-action" data-id="${r.id}">Restore</button>`
                        : `<button type="button" class="btn-restore-action is-disabled" disabled title="Past term — view only">Restore</button>`}
                </div>
            </td>
        </tr>`;
    }).join('');

    if (info) {
        info.textContent = `Showing ${start + 1}–${Math.min(end, dkFiltered.length)} of ${dkFiltered.length} records`;
    }

    renderPagination(dkFiltered.length);

    tbody.querySelectorAll('.btn-restore-action:not(.is-disabled)').forEach(btn => {
        btn.addEventListener('click', () => openRestoreModal(btn.dataset.id));
    });
    tbody.querySelectorAll('.btn-view-action').forEach(btn => {
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

function renderPagination(total) {
    const pages = Math.ceil(total / dkPerPage);
    const nums  = document.getElementById('deletedKabataanPageNumbers');
    const prev  = document.getElementById('deletedKabataanPrevBtn');
    const next  = document.getElementById('deletedKabataanNextBtn');

    if (nums) {
        nums.innerHTML = Array.from({ length: pages }, (_, i) => `
            <button type="button" class="pagination-btn ${i + 1 === dkCurrentPage ? 'active' : ''}">${i + 1}</button>
        `).join('');
        nums.querySelectorAll('.pagination-btn').forEach((btn, i) => {
            btn.addEventListener('click', () => { dkCurrentPage = i + 1; renderTable(); });
        });
    }
    if (prev) {
        prev.disabled = dkCurrentPage === 1;
        prev.onclick = () => { if (dkCurrentPage > 1) { dkCurrentPage--; renderTable(); } };
    }
    if (next) {
        next.disabled = dkCurrentPage >= pages || pages === 0;
        next.onclick = () => { if (dkCurrentPage < pages) { dkCurrentPage++; renderTable(); } };
    }
}

// ── Search ────────────────────────────────────────────────────────────────────
function bindSearch() {
    const input = document.getElementById('deletedKabataanSearch');
    if (!input) return;
    let debounce = null;
    input.addEventListener('input', function () {
        dkSearchQuery = this.value.trim();
        clearTimeout(debounce);
        debounce = setTimeout(() => loadData(), 300);
    });
}

// ── View modal (KK questionnaire) ───────────────────────────────────────────────
function openViewModal(id) {
    const r = dkRecords.find(x => String(x.id) === String(id));
    if (!r) return;

    const dateEl = document.getElementById('dkViewDeletedDate');
    const timeEl = document.getElementById('dkViewDeletedTime');
    if (dateEl) dateEl.textContent = r.deleted_date || '—';
    if (timeEl) timeEl.textContent = r.deleted_time || '—';

    populateDkViewModal(r);

    const modal = document.getElementById('dkViewModal');
    if (modal) modal.style.display = 'flex';
}

function populateDkViewModal(record) {
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
        return candidates.some(c => normalized === c.trim().toLowerCase());
    };

    const formatBirthdayDisplay = (value) => {
        if (!value || value === '—') return '—';
        const iso = /^(\d{4})-(\d{2})-(\d{2})$/.exec(value);
        if (iso) return `${iso[2]}/${iso[3]}/${iso[1]}`;
        return value;
    };

    const {
        respondentNumber, date, firstName, middleName, lastName, suffix, age, birthday, sex, civilStatus,
        region, province, city, barangay, purokZone, emailAddress, contactNumber,
        youthClassification, youthAgeGroup, workStatus, educationalBackground,
        registeredSKVoter, registeredNationalVoter, votingHistory, kkTimes, kkReason, attendedKKAssembly,
        facebookAccount, willingToJoinGroupChat, signature,
    } = request;

    setVal('kkViewRespondentNumber', respondentNumber);
    setVal('kkViewDate', date);
    setVal('kkViewLastName', lastName || '—');
    setVal('kkViewFirstName', firstName || '—');
    setVal('kkViewMiddleName', middleName || '—');
    setVal('kkViewSuffix', suffix || 'None');
    setVal('kkViewRegion', region || '—');
    setVal('kkViewProvince', province || '—');
    setVal('kkViewCity', city || '—');
    setVal('kkViewBarangay', barangay || '—');
    setVal('kkViewPurokZone', purokZone || '—');
    setCheck('kkViewSex_Male', sex === 'Male');
    setCheck('kkViewSex_Female', sex === 'Female');
    setVal('kkViewAge', age || '—');
    setVal('kkViewBirthday', formatBirthdayDisplay(birthday) || '—');
    setVal('kkViewEmailAddress', emailAddress || '—');
    setVal('kkViewContactNumber', contactNumber || '—');

    const csMap = {
        kkViewCS_Single: 'Single', kkViewCS_Married: 'Married', kkViewCS_Widowed: 'Widowed',
        kkViewCS_Divorced: 'Divorced', kkViewCS_Separated: 'Separated', kkViewCS_Annulled: 'Annulled',
        kkViewCS_Unknown: 'Unknown', kkViewCS_Livein: 'Live-in',
    };
    Object.entries(csMap).forEach(([id, val]) => setCheck(id, civilStatus === val));

    const yagMap = {
        kkViewYAG_Child: 'Child Youth (15-17 yrs old)',
        kkViewYAG_Core: 'Core Youth (18-24 yrs old)',
        kkViewYAG_Young: 'Young Adult (15-30 yrs old)',
    };
    Object.entries(yagMap).forEach(([id, val]) => setCheck(id, youthAgeGroup === val));

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
    Object.entries(ebMap).forEach(([id, vals]) => setCheck(id, matchesValue(educationalBackground, vals)));

    const ycMap = {
        kkViewYC_ISY: ['In School Youth', 'In school Youth'],
        kkViewYC_OSY: ['Out of School Youth'],
        kkViewYC_Working: ['Working Youth'],
        kkViewYC_PWD: ['Person w/ Disability'],
        kkViewYC_CICL: ['Children in Conflict w/ Law', 'Children In Conflict w/ Law'],
        kkViewYC_IP: ['Indigenous People'],
    };
    Object.entries(ycMap).forEach(([id, vals]) => setCheck(id, matchesValue(youthClassification, vals)));

    const wsMap = {
        kkViewWS_Employed: 'Employed', kkViewWS_Unemployed: 'Unemployed',
        kkViewWS_SelfEmployed: 'Self-Employed', kkViewWS_Looking: 'Currently looking for a Job',
        kkViewWS_NotInterested: 'Not Interested Looking for a Job',
    };
    Object.entries(wsMap).forEach(([id, val]) => setCheck(id, workStatus === val));

    setCheck('kkViewSKV_Yes', registeredSKVoter === 'Yes');
    setCheck('kkViewSKV_No', registeredSKVoter === 'No');
    setCheck('kkViewNV_Yes', registeredNationalVoter === 'Yes');
    setCheck('kkViewNV_No', registeredNationalVoter === 'No');
    setCheck('kkViewVH_Yes', votingHistory === 'Yes');
    setCheck('kkViewVH_No', votingHistory === 'No');
    setCheck('kkViewKK_Yes', attendedKKAssembly === 'Yes');
    setCheck('kkViewKK_No', attendedKKAssembly === 'No');
    setCheck('kkViewKKTimes_12', kkTimes === '1-2 Times');
    setCheck('kkViewKKTimes_34', kkTimes === '3-4 Times');
    setCheck('kkViewKKTimes_5', kkTimes === '5 and above');

    const normalizedReason = (kkReason || '').trim();
    setCheck('kkViewVR_NoKK',
        normalizedReason === 'There was no KK Assembly Meeting'
        || normalizedReason === 'There was no KK Assembly');
    setCheck('kkViewVR_NotInt',
        normalizedReason === 'Not interested to Attend'
        || normalizedReason === 'Not Interested to Attend');

    setVal('kkViewFacebookAccount', facebookAccount || '—');
    setCheck('kkViewGC_Yes', willingToJoinGroupChat === 'Yes');
    setCheck('kkViewGC_No', willingToJoinGroupChat === 'No');

    const logoEl = document.getElementById('kkViewBarangayLogo');
    if (logoEl && request.barangayLogoUrl) {
        logoEl.src = request.barangayLogoUrl;
        logoEl.alt = `${barangay || 'Barangay'} SK Logo`;
    }

    const sigNameEl = document.getElementById('kkViewSignatureName');
    const sigPreview = document.getElementById('kkViewSignaturePreview');
    const sigOverlay = document.getElementById('kkViewSignatureOverlay');
    const nameParts = [
        firstName,
        middleName ? middleName.charAt(0) + '.' : null,
        lastName,
        suffix && suffix !== 'None' ? suffix : null,
    ].filter(Boolean);
    const printedName = nameParts.join(' ') || '—';
    if (sigNameEl) sigNameEl.textContent = printedName;
    if (sigPreview && sigOverlay) {
        if (signature && signature.startsWith('data:image')) {
            sigPreview.src = signature;
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
    const modal     = document.getElementById('dkViewModal');
    const box       = document.getElementById('dkViewModalBox');
    const closeBtn  = document.getElementById('dkViewModalClose');
    const toggleBtn = document.getElementById('dkViewModalToggle');

    const close = () => {
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('view-modal-maximized');
        }
        if (box) box.classList.remove('view-modal-maximized');
        if (toggleBtn) toggleBtn.textContent = '□';
    };

    if (closeBtn) closeBtn.addEventListener('click', close);
    if (modal) modal.addEventListener('click', e => { if (e.target === modal) close(); });

    if (toggleBtn && box) {
        toggleBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            const isMax = !box.classList.contains('view-modal-maximized');
            modal.classList.toggle('view-modal-maximized', isMax);
            box.classList.toggle('view-modal-maximized', isMax);
            toggleBtn.textContent = isMax ? '⧉' : '□';
        });
    }
}

// ── Restore modal ─────────────────────────────────────────────────────────────
function openRestoreModal(id) {
    const record = dkRecords.find(r => String(r.id) === String(id));
    if (!record) return;
    if (window.SkArchive && !SkArchive.canRestoreRecord(record, ['_deletedTs', 'deleted_at'])) {
        showToast('This record is from a past SK term and cannot be restored.', 'error');
        return;
    }
    dkPendingRestoreId = id;
    const nameEl = document.getElementById('dkRestoreName');
    if (nameEl) nameEl.textContent = record.full_name || formatFullName(record);
    const modal = document.getElementById('dkRestoreModal');
    if (modal) modal.style.display = 'flex';
}

function closeRestoreModal() {
    dkPendingRestoreId = null;
    const modal = document.getElementById('dkRestoreModal');
    if (modal) modal.style.display = 'none';
}

function bindRestoreModal() {
    const cancelBtn  = document.getElementById('dkRestoreCancelBtn');
    const confirmBtn = document.getElementById('dkRestoreConfirmBtn');
    const modal      = document.getElementById('dkRestoreModal');
    const defaultHtml = confirmBtn ? confirmBtn.innerHTML : 'Restore';

    if (cancelBtn) cancelBtn.addEventListener('click', closeRestoreModal);
    if (modal) modal.addEventListener('click', e => { if (e.target === modal) closeRestoreModal(); });

    if (confirmBtn) {
        confirmBtn.addEventListener('click', async function () {
            if (!dkPendingRestoreId || confirmBtn.disabled) return;

            const record = dkRecords.find(r => String(r.id) === String(dkPendingRestoreId));
            const name = record ? (record.full_name || formatFullName(record)) : 'Record';

            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<span class="dk-restore-spinner"></span> Restoring...';

            try {
                const res = await fetch(`/deleted-kabataan/${dkPendingRestoreId}/restore`, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                    },
                });
                const json = await res.json().catch(() => ({}));
                if (!res.ok) throw new Error(json.message || 'Failed to restore record.');

                closeRestoreModal();
                showToast(json.message || `${name} restored to Kabataan list.`, 'success');
                showRestoreBanner('dkRestoreBanner', 'dkRestoreBannerText', `${name} has been restored to the Kabataan list.`);
                await loadData();
            } catch (err) {
                showToast(err.message || 'Failed to restore record.', 'error');
            } finally {
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = defaultHtml;
            }
        });
    }
}

// ── Notifications ─────────────────────────────────────────────────────────────
function showRestoreBanner(bannerId, textId, message) {
    const banner = document.getElementById(bannerId);
    const text   = document.getElementById(textId);
    if (!banner || !text) return;
    text.textContent = message;
    banner.style.display = 'flex';
    banner.classList.add('show');
    setTimeout(() => {
        banner.classList.remove('show');
        setTimeout(() => { banner.style.display = 'none'; }, 400);
    }, 4000);
}

function showToast(message, type) {
    const toast = document.getElementById('dkToast');
    if (!toast) return;
    toast.textContent = message;
    toast.classList.remove('dk-toast-error');
    if (type === 'error') toast.classList.add('dk-toast-error');
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 3200);
}
