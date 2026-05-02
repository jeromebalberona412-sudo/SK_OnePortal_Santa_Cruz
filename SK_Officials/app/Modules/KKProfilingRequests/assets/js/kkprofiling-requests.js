// Module-level toast — accessible by all functions in this file
function showToast(message, type) {
    const existing = document.querySelector('.app-toast');
    if (existing) existing.remove();
    const toast = document.createElement('div');
    toast.className = 'app-toast app-toast-show app-toast-' + (type || 'success');
    const icon = type === 'error' ? '✕' : '✓';
    toast.innerHTML = '<span class="app-toast-icon">' + icon + '</span> ' + message;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.classList.remove('app-toast-show');
        toast.classList.add('app-toast-hide');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

function initializeKKProfilingRequestsUI() {
    const tbody = document.getElementById('kkRequestsTableBody');
    const statusTabsContainer = document.getElementById('kkStatusTabs');
    const searchInput = document.getElementById('kkSearch');
    const barangayFilter = document.getElementById('kkBarangayFilter');
    const voterFilter = document.getElementById('kkVoterFilter');
    const viewModal = document.getElementById('kkViewModal');
    const approveModal = document.getElementById('kkApproveModal');
    const rejectModal = document.getElementById('kkRejectModal');
    const compareModal = null; // removed — Compare button is now a direct link

    if (!tbody) return;

    // Sample data loaded from JSON (storage/app/sample-data/kkprofiling-requests.json)
    const requests = [];

    function sortRequestsAlphabetically() {
        return requests.sort((a, b) => {
            const lastNameA = (a.lastName || '').toLowerCase();
            const lastNameB = (b.lastName || '').toLowerCase();
            if (lastNameA < lastNameB) return -1;
            if (lastNameA > lastNameB) return 1;
            const firstNameA = (a.firstName || '').toLowerCase();
            const firstNameB = (b.firstName || '').toLowerCase();
            if (firstNameA < firstNameB) return -1;
            if (firstNameA > firstNameB) return 1;
            return 0;
        });
    }

    function formatFullName(r) {
        const parts = [r.firstName, r.middleName].filter(Boolean);
        const firstMiddle = parts.length ? parts.join(',') : '';
        const last = r.lastName || '';
        const suffix = r.suffix ? ',' + r.suffix : '';
        if (last && firstMiddle) return `${last},${firstMiddle}${suffix}`;
        else if (last) return `${last}${suffix}`;
        else if (firstMiddle) return `${firstMiddle}${suffix}`;
        else return '-';
    }

    let currentFilterStatus = 'All';
    let currentSearchQuery = '';
    let currentBarangayFilter = '';
    let currentVoterFilter = '';
    let activeRequestId = null;
    let currentPage = 1;
    const recordsPerPage = 10;

    function renderTable() {
        tbody.innerHTML = '';
        const filtered = requests.filter((r) => {
            if (currentFilterStatus !== 'All' && r.status !== currentFilterStatus) return false;
            if (currentSearchQuery) {
                const q = currentSearchQuery.toLowerCase();
                const fullName = formatFullName(r).toLowerCase();
                const match = fullName.includes(q) || (r.purok && String(r.purok).toLowerCase().includes(q)) || (r.contact && String(r.contact).toLowerCase().includes(q));
                if (!match) return false;
            }
            if (currentBarangayFilter && r.purokZone !== currentBarangayFilter) return false;
            if (currentVoterFilter && r.registeredVoter !== currentVoterFilter) return false;
            return true;
        });

        const totalPages = Math.ceil(filtered.length / recordsPerPage);
        const startIndex = (currentPage - 1) * recordsPerPage;
        const endIndex = Math.min(startIndex + recordsPerPage, filtered.length);
        const paginatedData = filtered.slice(startIndex, endIndex);

        if (paginatedData.length === 0) {
            const tr = document.createElement('tr');
            tr.className = 'empty-state-row';
            const td = document.createElement('td');
            td.colSpan = 8;
            td.textContent = 'No KK Profiling requests for this status.';
            tr.appendChild(td);
            tbody.appendChild(tr);
            updatePaginationInfo(0, 0, 1);
            return;
        }

        paginatedData.forEach((r) => {
            const tr = document.createElement('tr');
            const statusClass = r.status === 'New Kabataan' ? 'approved'
                : r.status === 'Duplicate' ? 'duplicate'
                : r.status === 'Wrong Credential' ? 'rejected'
                : r.status === 'New Applicant' ? 'new-applicant'
                : 'pending';
            const fullName = formatFullName(r);
            const voterStatus = r.registeredVoter || 'No';
            const purokZone = r.purokZone || '—';

            // ── Duplicate linking: find the original record this duplicates ──
            let dupLinkBadge = '';
            if (r.status === 'Duplicate') {
                const refError = (r.censusErrors || []).find(e => e.field === 'respondentNumber');
                const refId = refError ? refError.census : null;
                if (refId) {
                    const linked = requests.find(x => x.respondentNumber === refId);
                    const linkedName = linked ? formatFullName(linked) : refId;
                    const linkedStatus = linked ? linked.status : '';
                    const badgeLabel = linkedStatus === 'Duplicate'
                        ? `Linked Duplicate · ${refId}`
                        : `Duplicate (KK) · Same as ${refId}`;
                    dupLinkBadge = `<div class="kk-dup-link-badge" title="Linked with ${refId}: ${linkedName}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                        ${badgeLabel}
                    </div>`;
                }
            }
            // Also mark non-Duplicate records that have a duplicate pointing to them
            if (!dupLinkBadge) {
                const dupOfThis = requests.find(x =>
                    x.status === 'Duplicate' &&
                    (x.censusErrors || []).some(e => e.field === 'respondentNumber' && e.census === r.respondentNumber)
                );
                if (dupOfThis) {
                    dupLinkBadge = `<div class="kk-dup-link-badge kk-dup-link-badge--original" title="Has duplicate submission: ${dupOfThis.respondentNumber}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                        Duplicate (KK) · Duplicated by ${dupOfThis.respondentNumber}
                    </div>`;
                }
            }

            tr.innerHTML = `
                <td class="kk-respondent-cell">${r.respondentNumber || '—'}</td>
                <td class="kk-fullname-cell">
                    <span class="kk-fullname">${fullName}</span>
                    ${dupLinkBadge}
                </td>
                <td>${r.age}</td>
                <td>${r.barangay}</td>
                <td>${purokZone}</td>
                <td>${voterStatus}</td>
                <td><span class="kk-status-pill ${statusClass}">${r.status}</span></td>
                <td><div class="kk-actions"><button type="button" class="kk-btn-view" data-action="view" data-id="${r.id}">View</button></div></td>
            `;
            tbody.appendChild(tr);
        });

        updatePaginationInfo(startIndex + 1, endIndex, currentPage, totalPages);
        updatePaginationControls(currentPage, totalPages);
    }

    function updatePaginationInfo(start, end, page, totalPages) {
        const info = document.getElementById('kkPaginationInfo');
        if (info) {
            const total = requests.filter((r) => {
                if (currentFilterStatus !== 'All' && r.status !== currentFilterStatus) return false;
                if (currentSearchQuery) {
                    const q = currentSearchQuery.toLowerCase();
                    const fullName = formatFullName(r).toLowerCase();
                    const match = fullName.includes(q) || (r.purok && String(r.purok).toLowerCase().includes(q)) || (r.contact && String(r.contact).toLowerCase().includes(q));
                    if (!match) return false;
                }
                return true;
            }).length;
            info.textContent = total === 0 ? 'No records found' : `Showing ${start}-${end} of ${total} records`;
        }
    }

    function updatePaginationControls(page, totalPages) {
        const prevBtn = document.getElementById('kkPrevBtn');
        const nextBtn = document.getElementById('kkNextBtn');
        const pageNumbers = document.getElementById('kkPageNumbers');
        if (prevBtn) prevBtn.disabled = page === 1;
        if (nextBtn) nextBtn.disabled = page === totalPages;
        if (pageNumbers) {
            pageNumbers.innerHTML = '';
            let startPage = Math.max(1, page - 2);
            let endPage = Math.min(totalPages, page + 2);
            if (endPage - startPage < 5) { endPage = Math.min(5, totalPages); startPage = 1; }
            if (endPage - startPage < 5 && page > totalPages - 2) { startPage = Math.max(1, totalPages - 4); endPage = totalPages; }
            for (let i = startPage; i <= endPage; i++) {
                const pageBtn = document.createElement('button');
                pageBtn.className = `page-number ${i === page ? 'active' : ''}`;
                pageBtn.textContent = i;
                pageBtn.onclick = () => goToPage(i);
                pageNumbers.appendChild(pageBtn);
            }
        }
    }

    function goToPage(page) {
        const totalPages = Math.ceil(requests.filter((r) => {
            if (currentFilterStatus !== 'All' && r.status !== currentFilterStatus) return false;
            if (currentSearchQuery) {
                const q = currentSearchQuery.toLowerCase();
                const fullName = formatFullName(r).toLowerCase();
                const match = fullName.includes(q) || (r.purok && String(r.purok).toLowerCase().includes(q)) || (r.contact && String(r.contact).toLowerCase().includes(q));
                if (!match) return false;
            }
            return true;
        }).length / recordsPerPage);
        if (page >= 1 && page <= totalPages) { currentPage = page; renderTable(); }
    }

    function setStatusFilter(status) {
        currentFilterStatus = status;
        currentPage = 1;
        if (!statusTabsContainer) return;
        const tabs = statusTabsContainer.querySelectorAll('.status-tab');
        tabs.forEach((tab) => {
            tab.classList.toggle('active', tab.getAttribute('data-status-filter') === status);
        });
        renderTable();
    }

    function openModal(modalElement) { if (modalElement) modalElement.style.display = 'flex'; }
    function closeModal(modalElement) { if (modalElement) modalElement.style.display = 'none'; }
    function closeAllModals() { [viewModal, approveModal, rejectModal].forEach((m) => { if (m) m.style.display = 'none'; }); }

    function populateViewModal(request, skipErrorPanel = false) {
        const { respondentNumber, date, firstName, middleName, lastName, suffix, age, birthday, sex, civilStatus,
            region, province, city, barangay, purokZone, emailAddress, contactNumber,
            youthClassification, youthAgeGroup, workStatus, educationalBackground,
            registeredSKVoter, registeredNationalVoter, votingHistory, votingFrequency, votingReason, attendedKKAssembly,
            facebookAccount, willingToJoinGroupChat, signature, status, rejectionReason } = request;

        // Build a map of field → error info for quick lookup
        const errors = request.censusErrors || [];
        const errorMap = {};
        errors.forEach((e, idx) => { errorMap[e.field] = { ...e, idx }; });
        const isEditable = (status === 'Wrong Credential') && errors.length > 0;
        const isDuplicate = (status === 'Duplicate') && errors.length > 0;

        // Track which fields have been corrected (persists across re-renders via closure on request object)
        if (!request._fixedFields) request._fixedFields = new Set();
        const fixedFields = request._fixedFields;

        // Helper: set a plain text value
        const setVal = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.textContent = val ?? '';
        };

        // Helper: render a field — either plain text, editable inline (Wrong Credential), or duplicate badge
        const setField = (id, fieldKey, val) => {
            const el = document.getElementById(id);
            if (!el) return;

            if (isEditable && errorMap[fieldKey] && !skipErrorPanel) {
                // ── Wrong Credential: editable inline input with right-side suggestion ──
                const e = errorMap[fieldKey];
                const isFixed = fixedFields.has(fieldKey);

                if (isFixed) {
                    // Show corrected input still visible but green — user can see what they typed + suggestion
                    el.innerHTML = `<span class="kk-inline-field-wrap kk-inline-field-with-suggestion">
                        <input
                            type="text"
                            class="kk-inline-edit-input kk-inline-edit-correct"
                            data-field="${fieldKey}"
                            data-census="${(e.census || '').replace(/"/g, '&quot;')}"
                            value="${(e.census || '').replace(/"/g, '&quot;')}"
                            autocomplete="off"
                            spellcheck="false"
                            readonly
                        />
                        <span class="kk-inline-suggestion">Should be: <strong>${e.census}</strong></span>
                        <span class="kk-inline-correct-badge">✓ Correct!</span>
                    </span>`;
                    el.className = el.className.replace(/\s*kk-field-error\s*/g, '').replace(/\s*kk-field-corrected\s*/g, '');
                } else {
                    const submitted = e.submitted || '';
                    el.innerHTML = `<span class="kk-inline-field-wrap kk-inline-field-with-suggestion">
                        <input
                            type="text"
                            class="kk-inline-edit-input kk-inline-edit-error"
                            data-field="${fieldKey}"
                            data-census="${(e.census || '').replace(/"/g, '&quot;')}"
                            value="${submitted.replace(/"/g, '&quot;')}"
                            autocomplete="off"
                            spellcheck="false"
                        />
                        <span class="kk-inline-suggestion">Should be: <strong>${e.census}</strong></span>
                        <span class="kk-inline-error-badge" title="${e.note}">✕ ${e.note}</span>
                    </span>`;
                    el.className = el.className.replace(/\s*kk-field-corrected\s*/g, '');

                    const input = el.querySelector('.kk-inline-edit-input');
                    if (input) {
                        input.addEventListener('input', () => {
                            const census = input.dataset.census.trim().toLowerCase();
                            const typed  = input.value.trim().toLowerCase();
                            if (typed === census && census !== '') {
                                fixedFields.add(fieldKey);
                                request[fieldKey] = e.census;
                                setField(id, fieldKey, e.census);
                                updateInlineSaveBtn(request);
                            }
                        });
                    }
                }
            } else if (isDuplicate && errorMap[fieldKey] && !skipErrorPanel) {
                // ── Duplicate: only show inline badge on name fields (lastName, firstName, middleName, suffix) ──
                const nameFields = ['lastName', 'firstName', 'middleName', 'suffix'];
                if (nameFields.includes(fieldKey)) {
                    const e = errorMap[fieldKey];
                    const displayVal = val ?? '';
                    el.innerHTML = `<span class="kk-inline-field-wrap">
                        <span class="kk-inline-dup-value">${displayVal}</span>
                        <span class="kk-inline-error-badge kk-inline-dup-badge">⚠ ${e.note}</span>
                    </span>`;
                    el.className = el.className.replace(/\s*kk-field-error\s*/g, '').replace(/\s*kk-field-corrected\s*/g, '');
                } else {
                    // All other fields: plain text, no badge
                    el.innerHTML = '';
                    el.className = el.className.replace(/\s*kk-field-error\s*/g, '').replace(/\s*kk-field-corrected\s*/g, '');
                    el.textContent = val ?? '';
                }
            } else {
                // ── Plain text display ──
                el.innerHTML = '';
                el.className = el.className.replace(/\s*kk-field-error\s*/g, '').replace(/\s*kk-field-corrected\s*/g, '');
                el.textContent = val ?? '';
            }
        };

        const setCheck = (id, checked) => {
            const el = document.getElementById(id);
            if (!el) return;
            const text = el.textContent.replace(/^[☐☑]\s*/, '');
            el.textContent = (checked ? '☑ ' : '☐ ') + text;
            el.style.fontWeight = checked ? '700' : '400';
            el.style.color = checked ? '#1a1a1a' : '#6b7280';
        };

        setVal('kkViewRespondentNumber', respondentNumber); setVal('kkViewDate', date);
        setField('kkViewLastName',      'lastName',      lastName      || '—');
        setField('kkViewFirstName',     'firstName',     firstName     || '—');
        setField('kkViewMiddleName',    'middleName',    middleName    || '—');
        setField('kkViewSuffix',        'suffix',        suffix        || 'None');
        setVal('kkViewRegion',   region   || '—');
        setVal('kkViewProvince', province || '—');
        setVal('kkViewCity',     city     || '—');
        setField('kkViewBarangay',      'barangay',      barangay      || '—');
        setField('kkViewPurokZone',     'purokZone',     purokZone     || '—');
        setVal('kkViewSexAssignedAtBirth', sex || '—');
        setField('kkViewAge',           'age',           age           || '—');
        setField('kkViewBirthday',      'birthday',      birthday      || '—');
        setField('kkViewEmailAddress',  'emailAddress',  emailAddress  || '—');
        setField('kkViewContactNumber', 'contactNumber', contactNumber || '—');

        // Civil Status — also handle as editable if it's an error field
        if (isEditable && errorMap['civilStatus'] && !skipErrorPanel) {
            const e = errorMap['civilStatus'];
            const isFixed = fixedFields.has('civilStatus');
            // Highlight the civil status block header
            const csBlock = document.querySelector('.kk-qs-demo-block-label');
            // We'll show an inline error note below the civil status block
            let csErrEl = document.getElementById('kkViewCS_ErrorNote');
            if (!csErrEl) {
                const csOptions = document.querySelector('.kk-qs-demo-options.kk-qs-options-2col');
                if (csOptions) {
                    csErrEl = document.createElement('div');
                    csErrEl.id = 'kkViewCS_ErrorNote';
                    csErrEl.className = 'kk-inline-cs-error';
                    csOptions.parentElement.appendChild(csErrEl);
                }
            }
            if (csErrEl) {
                if (isFixed) {
                    csErrEl.style.display = 'none';
                } else {
                    csErrEl.style.display = 'flex';
                    csErrEl.innerHTML = `<span class="kk-inline-error-badge kk-inline-error-badge--cs">✕ ${e.note} — Census value: <strong>${e.census}</strong></span>`;
                }
            }
        } else {
            const csErrEl = document.getElementById('kkViewCS_ErrorNote');
            if (csErrEl) csErrEl.style.display = 'none';
        }

        const csMap = { kkViewCS_Single:'Single', kkViewCS_Married:'Married', kkViewCS_Widowed:'Widowed', kkViewCS_Divorced:'Divorced', kkViewCS_Separated:'Separated', kkViewCS_Annulled:'Annulled', kkViewCS_Unknown:'Unknown', kkViewCS_Livein:'Live-in' };
        Object.entries(csMap).forEach(([id, val]) => setCheck(id, civilStatus === val));
        const yagMap = { kkViewYAG_Child:'Child Youth (15-17 yrs old)', kkViewYAG_Core:'Core Youth (18-24 yrs old)', kkViewYAG_Young:'Young Adult (15-30 yrs old)' };
        Object.entries(yagMap).forEach(([id, val]) => setCheck(id, youthAgeGroup === val));
        const ebMap = { kkViewEB_ElemLevel:'Elementary Level', kkViewEB_ElemGrad:'Elementary Grad', kkViewEB_HSLevel:'High School Level', kkViewEB_HSGrad:'High School Grad', kkViewEB_VocGrad:'Vocational Grad', kkViewEB_ColLevel:'College Level', kkViewEB_ColGrad:'College Grad', kkViewEB_MasLevel:'Masters Level', kkViewEB_MasGrad:'Masters Grad', kkViewEB_DocLevel:'Doctorate Level', kkViewEB_DocGrad:'Doctorate Graduate' };
        Object.entries(ebMap).forEach(([id, val]) => setCheck(id, educationalBackground === val));
        const ycMap = { kkViewYC_ISY:'In School Youth', kkViewYC_OSY:'Out of School Youth', kkViewYC_Working:'Working Youth', kkViewYC_Specific:'Youth w/ Specific Needs', kkViewYC_PWD:'Person w/ Disability', kkViewYC_CICL:'Children in Conflict w/ Law', kkViewYC_IP:'Indigenous People' };
        Object.entries(ycMap).forEach(([id, val]) => setCheck(id, youthClassification === val));
        const wsMap = { kkViewWS_Employed:'Employed', kkViewWS_Unemployed:'Unemployed', kkViewWS_SelfEmployed:'Self-Employed', kkViewWS_Looking:'Currently looking for a Job', kkViewWS_NotInterested:'Not Interested Looking for a Job' };
        Object.entries(wsMap).forEach(([id, val]) => setCheck(id, workStatus === val));

        setCheck('kkViewSKV_Yes', registeredSKVoter === 'Yes'); setCheck('kkViewSKV_No', registeredSKVoter === 'No');
        setCheck('kkViewNV_Yes', registeredNationalVoter === 'Yes'); setCheck('kkViewNV_No', registeredNationalVoter === 'No');
        setCheck('kkViewVH_Yes', votingHistory === 'Yes'); setCheck('kkViewVH_No', votingHistory === 'No');
        setCheck('kkViewVF_12', votingFrequency === '1-2 Times'); setCheck('kkViewVF_34', votingFrequency === '3-4 Times'); setCheck('kkViewVF_5', votingFrequency === '5 and above');
        setCheck('kkViewKK_Yes', attendedKKAssembly === 'Yes'); setCheck('kkViewKK_No', attendedKKAssembly === 'No');
        setCheck('kkViewVR_NoKK', votingReason === 'There was no KK Assembly'); setCheck('kkViewVR_NotInt', votingReason === 'Not Interested to Attend');
        setVal('kkViewFacebookAccount', facebookAccount || '—');
        setCheck('kkViewGC_Yes', willingToJoinGroupChat === 'Yes'); setCheck('kkViewGC_No', willingToJoinGroupChat === 'No');
        setVal('kkViewSignature', signature || '—');

        const rejectionWrap = document.getElementById('kkViewRejectionWrap');
        const rejectionText = document.getElementById('kkViewRejectionText');
        if (rejectionWrap && rejectionText) {
            // Never show rejection reason for Wrong Credential — it's shown inline on the form
            if (rejectionReason && status !== 'Wrong Credential') {
                rejectionWrap.style.display = 'block';
                rejectionText.textContent = rejectionReason;
            } else {
                rejectionWrap.style.display = 'none';
            }
        }

        // Show/hide inline save button
        if (!skipErrorPanel) updateInlineSaveBtn(request);

        // Hide the old bottom error panel — errors are now inline on the form
        const errorsWrap = document.getElementById('kkViewCensusErrorsWrap');
        if (errorsWrap) errorsWrap.style.display = 'none';

        // Remove any leftover bottom duplicate panel (errors are now inline)
        const oldDupPanel = document.getElementById('kkViewDuplicatePanel');
        if (oldDupPanel) oldDupPanel.remove();
    }

    // Renders or updates the inline "Save Corrections" button inside the form
    function updateInlineSaveBtn(request) {
        const errors = request.censusErrors || [];
        const fixedFields = request._fixedFields || new Set();
        const isEditable = (request.status === 'Wrong Credential') && errors.length > 0;

        let saveRow = document.getElementById('kkInlineSaveRow');

        if (isEditable && fixedFields.size === errors.length && errors.length > 0) {
            if (!saveRow) {
                saveRow = document.createElement('div');
                saveRow.id = 'kkInlineSaveRow';
                saveRow.className = 'kk-inline-save-row';
                // Insert after signature row
                const sigRow = document.querySelector('.kk-qs-signature-row');
                if (sigRow && sigRow.parentNode) {
                    sigRow.parentNode.insertBefore(saveRow, sigRow.nextSibling);
                }
            }
            saveRow.innerHTML = `
                <div class="kk-inline-save-banner">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    All fields corrected — record matches census data.
                    <button type="button" class="kk-inline-save-btn" id="kkInlineSaveBtn">
                        Save &amp; Mark as New Kabataan
                    </button>
                </div>`;
            const saveBtn = document.getElementById('kkInlineSaveBtn');
            if (saveBtn) {
                saveBtn.addEventListener('click', () => {
                    request.status = 'New Kabataan';
                    request.censusErrors = [];
                    request._fixedFields = new Set();
                    renderTable();
                    showToast('Corrections saved — record marked as New Kabataan', 'success');
                    closeAllModals();
                });
            }
        } else {
            if (saveRow) saveRow.remove();
        }
    }
    function fieldLabel(field) {
        const map = {
            firstName: 'First Name', middleName: 'Middle Name', lastName: 'Last Name',
            suffix: 'Suffix', birthday: 'Date of Birth', age: 'Age', sex: 'Sex',
            civilStatus: 'Civil Status', contactNumber: 'Contact Number',
            emailAddress: 'Email Address', barangay: 'Barangay', purokZone: 'Purok/Zone',
            respondentNumber: 'Respondent Number', fullName: 'Full Name'
        };
        return map[field] || field;
    }

    if (searchInput) { searchInput.addEventListener('input', () => { currentSearchQuery = searchInput.value.trim(); currentPage = 1; renderTable(); }); }
    if (barangayFilter) { barangayFilter.addEventListener('change', () => { currentBarangayFilter = barangayFilter.value; currentPage = 1; renderTable(); }); }
    if (voterFilter) { voterFilter.addEventListener('change', () => { currentVoterFilter = voterFilter.value; currentPage = 1; renderTable(); }); }

    const prevBtn = document.getElementById('kkPrevBtn');
    const nextBtn = document.getElementById('kkNextBtn');
    if (prevBtn) prevBtn.addEventListener('click', () => goToPage(currentPage - 1));
    if (nextBtn) nextBtn.addEventListener('click', () => goToPage(currentPage + 1));

    if (statusTabsContainer) {
        statusTabsContainer.addEventListener('click', (e) => {
            const btn = e.target.closest('.status-tab');
            if (!btn) return;
            setStatusFilter(btn.getAttribute('data-status-filter') || 'All');
        });
    }

    function resetModalMaximize(backdropEl) {
        if (!backdropEl) return;
        backdropEl.classList.remove('modal-maximized');
        const toggleBtn = backdropEl.querySelector('[data-modal-toggle]');
        if (toggleBtn) toggleBtn.textContent = '□';
    }

    function wireModalToggle(backdropEl) {
        if (!backdropEl) return;
        const toggleBtn = backdropEl.querySelector('[data-modal-toggle]');
        if (!toggleBtn) return;

        toggleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const isMaximized = backdropEl.classList.toggle('modal-maximized');
            toggleBtn.textContent = isMaximized ? '⧉' : '□';
        });
    }

    // Wire toggle buttons after modals exist in DOM
    wireModalToggle(viewModal);

    tbody.addEventListener('click', (e) => {
        const btn = e.target.closest('button[data-action]');
        if (!btn) return;
        const action = btn.getAttribute('data-action');
        const id = parseInt(btn.getAttribute('data-id') || '', 10);
        if (!action || Number.isNaN(id)) return;
        const request = requests.find((r) => r.id === id);
        if (!request) return;
        activeRequestId = id;
        if (action === 'view') {
            resetModalMaximize(viewModal);
            // Reset correction state for a fresh open
            request._fixedFields = new Set();
            // Remove any leftover inline save row from a previous open
            const oldSaveRow = document.getElementById('kkInlineSaveRow');
            if (oldSaveRow) oldSaveRow.remove();
            populateViewModal(request);
            openModal(viewModal);
        } else if (action === 'approve') {
            openModal(approveModal);
        } else if (action === 'reject') {
            const checkboxes = rejectModal ? rejectModal.querySelectorAll('.kk-reject-reason:not(.kk-reject-other-checkbox)') : [];
            checkboxes.forEach((cb) => { cb.checked = false; });
            const otherCheckbox = document.getElementById('kkRejectOtherCheckbox');
            const otherWrap = document.getElementById('kkRejectOtherWrap');
            const otherInput = document.getElementById('kkRejectOtherReason');
            if (otherCheckbox) otherCheckbox.checked = false;
            if (otherWrap) otherWrap.style.display = 'none';
            if (otherInput) otherInput.value = '';
            openModal(rejectModal);
        }
    });

    const viewApproveBtn = document.getElementById('kkViewApproveBtn');
    const viewRejectBtn = document.getElementById('kkViewRejectBtn');

    if (viewApproveBtn) {
        viewApproveBtn.addEventListener('click', () => {
            if (activeRequestId) {
                const request = requests.find(r => r.id === activeRequestId);
                if (request) {
                    const idx = requests.indexOf(request);
                    if (idx !== -1) requests.splice(idx, 1);
                    closeAllModals();
                    renderTable();
                    updateStatCards();
                    showToast('KK Profiling Request Approved Successfully', 'success');
                }
            }
        });
    }

    if (viewRejectBtn) {
        viewRejectBtn.addEventListener('click', () => { if (activeRequestId) openModal(rejectModal); });
    }

    const otherCheckbox = document.getElementById('kkRejectOtherCheckbox');
    const otherWrap = document.getElementById('kkRejectOtherWrap');
    const otherReasons = rejectModal ? rejectModal.querySelectorAll('.kk-reject-reason:not(.kk-reject-other-checkbox)') : [];

    if (otherCheckbox && otherWrap) {
        otherCheckbox.addEventListener('change', () => {
            if (otherCheckbox.checked) { otherReasons.forEach((cb) => { cb.checked = false; }); otherWrap.style.display = 'flex'; }
            else { otherWrap.style.display = 'none'; }
        });
    }
    otherReasons.forEach((cb) => {
        cb.addEventListener('change', () => {
            if (cb.checked && otherCheckbox) {
                otherCheckbox.checked = false;
                otherWrap.style.display = 'none';
                const otherInput = document.getElementById('kkRejectOtherReason');
                if (otherInput) otherInput.value = '';
            }
        });
    });

    [viewModal, approveModal, rejectModal].forEach((modal) => {
        if (!modal) return;
        modal.addEventListener('click', (e) => {
            const target = e.target;
            if (target === modal || target.hasAttribute('data-modal-close')) {
                closeModal(modal);
                if (modal === viewModal) closeModalResetMax(viewModal, viewModalBox, viewToggle);
            }
        });
    });

    const approveConfirmBtn = document.getElementById('kkApproveConfirmBtn');
    if (approveConfirmBtn) {
        approveConfirmBtn.addEventListener('click', () => {
            if (activeRequestId === null) { closeModal(approveModal); return; }
            const request = requests.find((r) => r.id === activeRequestId);
            if (!request) { closeModal(approveModal); return; }
            const idx = requests.indexOf(request);
            if (idx !== -1) requests.splice(idx, 1);
            closeModal(approveModal);
            renderTable();
            updateStatCards();
            showToast('KK Profiling Request Approved Successfully', 'success');
        });
    }

    function showSuccessModal(action = 'Approved') {
        showToast(action === 'Approved' ? 'KK Profiling Request Approved Successfully' : 'KK Profiling Request Rejected Successfully', 'success');
    }

    const rejectConfirmBtn = document.getElementById('kkRejectConfirmBtn');
    if (rejectConfirmBtn) {
        rejectConfirmBtn.addEventListener('click', () => {
            if (activeRequestId === null) { closeModal(rejectModal); return; }
            const request = requests.find((r) => r.id === activeRequestId);
            if (!request) { closeModal(rejectModal); return; }
            const checkboxes = rejectModal ? rejectModal.querySelectorAll('.kk-reject-reason:not(.kk-reject-other-checkbox)') : [];
            const selectedReasons = [];
            checkboxes.forEach((cb) => { if (cb.checked) selectedReasons.push(cb.value); });
            const otherCb = document.getElementById('kkRejectOtherCheckbox');
            const otherInput = document.getElementById('kkRejectOtherReason');
            const otherReason = otherInput ? String(otherInput.value || '').trim() : '';
            if (otherCb && otherCb.checked) {
                if (otherReason) selectedReasons.push('Other: ' + otherReason);
                else { alert('Please specify the reason for "Other".'); return; }
            }
            if (selectedReasons.length === 0) { alert('Please select at least one rejection reason or check Other and specify.'); return; }
            request.status = 'Rejected';
            request.rejectionReason = selectedReasons.join('; ');
            closeModal(rejectModal);
            closeModal(viewModal);
            renderTable();
            showSuccessModal('Rejected');
        });
    }

    function updateStatCards() {
        const valid      = requests.filter(r => r.status === 'New Kabataan').length;
        const duplicate  = requests.filter(r => r.status === 'Duplicate').length;
        const wrong      = requests.filter(r => r.status === 'Wrong Credential').length;
        const newApp     = requests.filter(r => r.status === 'New Applicant').length;
        const total      = requests.length;
        const el = (id) => document.getElementById(id);
        if (el('kkStatApproved'))  el('kkStatApproved').textContent  = valid;
        if (el('kkStatPending'))   el('kkStatPending').textContent   = duplicate + newApp;
        if (el('kkStatRejected'))  el('kkStatRejected').textContent  = wrong;
        if (el('kkStatTotal'))     el('kkStatTotal').textContent     = total;
    }

    // Compare with Census button — now just a link, no JS needed

    // Load sample data from JSON then render
    fetch('/sample-data/kkprofiling-requests.json')
        .then(r => r.json())
        .then(data => {
            requests.push(...data);
            sortRequestsAlphabetically();
            updateStatCards();
            setStatusFilter('All');
        })
        .catch(() => {
            setStatusFilter('All');
        });
}

// ═══════════════════════════════════════════════════════
// BARANGAY CENSUS FUNCTIONALITY
// ═══════════════════════════════════════════════════════

function initializeCensusUI() {
    const censusTableBody = document.getElementById('kkCensusTableBody');
    const uploadCensusBtn = document.getElementById('kkUploadCensusBtn');
    const uploadCensusModal = document.getElementById('kkUploadCensusModal');
    const uploadCensusConfirmBtn = document.getElementById('kkUploadCensusConfirmBtn');
    const censusFileInput = document.getElementById('kkCensusFile');
    const viewCensusModal = document.getElementById('kkViewCensusModal');

    if (!censusTableBody) return;

    // Sample census data
    const censusData = [
        {
            id: 1,
            formNo: 'CMP-04-001',
            controlNumber: 'CN-2026-001',
            cy: '2026',
            lastName: 'Santos',
            firstName: 'Maria',
            middleName: 'Garcia',
            houseNo: '123',
            street: 'Purok 1',
            barangay: 'Calios',
            city: 'Santa Cruz',
            province: 'Laguna',
            sex: 'Female',
            civilStatus: 'Married',
            dateOfBirth: '1985-05-15',
            placeOfBirth: 'Santa Cruz, Laguna',
            height: '5\'4"',
            weight: '120 lbs',
            contactNumber: '09171234567',
            religion: 'Roman Catholic',
            email: 'maria.santos@email.com'
        },
        {
            id: 2,
            formNo: 'CMP-04-002',
            controlNumber: 'CN-2026-002',
            cy: '2026',
            lastName: 'Reyes',
            firstName: 'Juan',
            middleName: 'Cruz',
            houseNo: '456',
            street: 'Purok 2',
            barangay: 'Calios',
            city: 'Santa Cruz',
            province: 'Laguna',
            sex: 'Male',
            civilStatus: 'Single',
            dateOfBirth: '1990-08-20',
            placeOfBirth: 'Manila',
            height: '5\'8"',
            weight: '150 lbs',
            contactNumber: '09281234567',
            religion: 'Iglesia ni Cristo',
            email: 'juan.reyes@email.com'
        },
        {
            id: 3,
            formNo: 'CMP-04-003',
            controlNumber: 'CN-2026-003',
            cy: '2026',
            lastName: 'Dela Cruz',
            firstName: 'Ana',
            middleName: 'Lopez',
            houseNo: '789',
            street: 'Purok 3',
            barangay: 'Calios',
            city: 'Santa Cruz',
            province: 'Laguna',
            sex: 'Female',
            civilStatus: 'Widow',
            dateOfBirth: '1975-12-10',
            placeOfBirth: 'Laguna',
            height: '5\'2"',
            weight: '110 lbs',
            contactNumber: '09391234567',
            religion: 'Born Again',
            email: 'ana.delacruz@email.com'
        }
    ];

    function renderCensusTable() {
        censusTableBody.innerHTML = '';
        if (censusData.length === 0) {
            const tr = document.createElement('tr');
            tr.className = 'empty-state-row';
            const td = document.createElement('td');
            td.colSpan = 7;
            td.textContent = 'No census data available. Upload an Excel file to get started.';
            tr.appendChild(td);
            censusTableBody.appendChild(tr);
            return;
        }

        censusData.forEach((c) => {
            const tr = document.createElement('tr');
            const fullName = `${c.lastName}, ${c.firstName} ${c.middleName}`;
            tr.innerHTML = `
                <td>${c.formNo}</td>
                <td>${c.controlNumber}</td>
                <td class="kk-fullname-cell"><span class="kk-fullname">${fullName}</span></td>
                <td>${c.barangay}</td>
                <td>${c.dateOfBirth}</td>
                <td>${c.civilStatus}</td>
                <td><div class="kk-actions"><button type="button" class="kk-btn-view" data-action="view-census" data-id="${c.id}">View</button></div></td>
            `;
            censusTableBody.appendChild(tr);
        });
    }

    function populateViewCensusModal(census) {
        const setVal = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val ?? '—'; };
        
        setVal('kkCensusFormNo', census.formNo);
        setVal('kkCensusControlNumber', census.controlNumber);
        setVal('kkCensusCY', census.cy);
        setVal('kkCensusLastName', census.lastName);
        setVal('kkCensusFirstName', census.firstName);
        setVal('kkCensusMiddleName', census.middleName);
        setVal('kkCensusHouseNo', census.houseNo);
        setVal('kkCensusStreet', census.street);
        setVal('kkCensusBarangay', census.barangay);
        setVal('kkCensusCity', census.city);
        setVal('kkCensusProvince', census.province);
        setVal('kkCensusSex', census.sex);
        setVal('kkCensusCivilStatus', census.civilStatus);
        setVal('kkCensusDOB', census.dateOfBirth);
        setVal('kkCensusContact', census.contactNumber);
    }

    // Upload Census Button
    if (uploadCensusBtn) {
        uploadCensusBtn.addEventListener('click', () => {
            if (censusFileInput) censusFileInput.value = '';
            openModal(uploadCensusModal);
        });
    }

    // Upload Census Confirm
    if (uploadCensusConfirmBtn && censusFileInput) {
        uploadCensusConfirmBtn.addEventListener('click', () => {
            const file = censusFileInput.files[0];
            if (!file) {
                showToast('Please select an Excel file to upload', 'error');
                return;
            }
            
            // Validate file type
            const validTypes = ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
            if (!validTypes.includes(file.type) && !file.name.match(/\.(xlsx|xls)$/i)) {
                showToast('Please upload a valid Excel file (.xlsx or .xls)', 'error');
                return;
            }

            // Simulate upload (in real implementation, this would send to server)
            closeModal(uploadCensusModal);
            showToast('Census data uploaded successfully', 'success');
            renderCensusTable();
        });
    }

    // View Census Details
    if (censusTableBody) {
        censusTableBody.addEventListener('click', (e) => {
            const btn = e.target.closest('button[data-action="view-census"]');
            if (!btn) return;
            const id = parseInt(btn.getAttribute('data-id') || '', 10);
            if (Number.isNaN(id)) return;
            const census = censusData.find((c) => c.id === id);
            if (!census) return;
            
            populateViewCensusModal(census);
            resetModalMaximize(viewCensusModal);
            openModal(viewCensusModal);
        });
    }

    // Wire toggle buttons for census modal
    wireModalToggle(viewCensusModal);

    // Close modals
    [uploadCensusModal, viewCensusModal].forEach((modal) => {
        if (!modal) return;
        modal.addEventListener('click', (e) => {
            const target = e.target;
            if (target === modal || target.hasAttribute('data-modal-close')) {
                resetModalMaximize(modal);
                closeModal(modal);
            }
        });
    });

    function openModal(modalElement) { if (modalElement) modalElement.style.display = 'flex'; }
    function closeModal(modalElement) { if (modalElement) modalElement.style.display = 'none'; }

    // Initial render
    renderCensusTable();
}

// Initialize both modules
document.addEventListener('DOMContentLoaded', () => {
    initializeKKProfilingRequestsUI();
    initializeCensusUI();
});