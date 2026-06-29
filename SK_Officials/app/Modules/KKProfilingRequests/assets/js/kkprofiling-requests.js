function broadcastKkProfileEvent() {
    try {
        sessionStorage.setItem('kk-profile-event', JSON.stringify({ at: Date.now() }));
    } catch (_) { /* ignore */ }
    window.dispatchEvent(new CustomEvent('kk-profile-event'));
}

function formatRespondentDisplay(seq, fullNumber) {
    if (fullNumber && fullNumber !== '—') {
        const raw = String(fullNumber).trim();
        if (raw.includes('-')) {
            const last = raw.split('-').pop();
            return last || '—';
        }
        return raw;
    }
    if (seq !== null && seq !== undefined && seq !== '') {
        const n = parseInt(seq, 10);
        return Number.isNaN(n) ? '—' : String(n).padStart(4, '0');
    }
    return '—';
}

const HIDDEN_REGISTRATION_STATUSES = new Set([
    'password_set',
    'email_verified',
    'pending_verification',
    'active',
    'rejected',
]);

function resolveEvaluationStatus(evaluationStatus, registrationStatus) {
    if (evaluationStatus) {
        return evaluationStatus;
    }

    const normalized = String(registrationStatus || '').trim().toLowerCase();
    if (!normalized || HIDDEN_REGISTRATION_STATUSES.has(normalized)) {
        return '';
    }

    return registrationStatus;
}

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
    const searchInput = document.getElementById('kkSearch');
    const barangayFilter = document.getElementById('kkBarangayFilter');
    const voterFilter = document.getElementById('kkVoterFilter');
    const sexFilter = document.getElementById('kkSexFilter');
    const youthAgeGroupFilter = document.getElementById('kkYouthAgeGroupFilter');
    const viewModal = document.getElementById('kkViewModal');
    const approveModal = document.getElementById('kkApproveModal');
    const rejectModal = document.getElementById('kkRejectModal');
    const compareModal = null; // removed — Compare button is now a direct link

    if (!tbody) return;

    if (typeof window.bindRowActionsTable === 'function') {
        window.bindRowActionsTable(tbody);
    }

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

    function formatDisplaySuffix(suffix) {
        if (!suffix) {
            return '';
        }

        const normalized = String(suffix).trim();

        if (!normalized || normalized.toLowerCase() === 'none') {
            return '';
        }

        return normalized;
    }

    function formatFullName(r) {
        const parts = [r.firstName, r.middleName].filter(Boolean);
        const firstMiddle = parts.length ? parts.join(',') : '';
        const last = r.lastName || '';
        const suffixPart = formatDisplaySuffix(r.suffix);
        const suffix = suffixPart ? ',' + suffixPart : '';

        if (last && firstMiddle) return `${last},${firstMiddle}${suffix}`;
        if (last) return `${last}${suffix}`;
        if (firstMiddle) return `${firstMiddle}${suffix}`;

        return '-';
    }

    let currentSearchQuery = '';
    let currentBarangayFilter = '';
    let currentVoterFilter = '';
    let currentSexFilter = '';
    let currentYouthAgeGroupFilter = '';
    let activeRequestId = null;
    let currentPage = 1;
    let recordsPerPage = 10;

    function getFilteredRequests() {
        return requests.filter((r) => {
            if (currentSearchQuery) {
                const q = currentSearchQuery.toLowerCase();
                const fullName = formatFullName(r).toLowerCase();
                const match = fullName.includes(q)
                    || (r.emailAddress && String(r.emailAddress).toLowerCase().includes(q))
                    || (r.purok && String(r.purok).toLowerCase().includes(q))
                    || (r.contact && String(r.contact).toLowerCase().includes(q));
                if (!match) return false;
            }
            if (currentBarangayFilter && r.purokZone !== currentBarangayFilter) return false;
            if (currentVoterFilter && r.registeredVoter !== currentVoterFilter) return false;
            if (currentSexFilter && r.sex !== currentSexFilter) return false;
            if (currentYouthAgeGroupFilter && r.youthAgeGroup !== currentYouthAgeGroupFilter) return false;
            return true;
        });
    }

    function getTotalPages(count = getFilteredRequests().length) {
        return Math.max(1, Math.ceil(count / recordsPerPage) || 1);
    }

    function updatePaginationFooter(totalRecords) {
        const totalPages = getTotalPages(totalRecords);
        const pageInput = document.getElementById('kkPageInput');
        const totalPagesEl = document.getElementById('kkTotalPages');
        const prevBtn = document.getElementById('kkPrevBtn');
        const nextBtn = document.getElementById('kkNextBtn');
        const info = document.getElementById('kkPaginationInfo');

        if (currentPage > totalPages) {
            currentPage = totalPages;
        }

        if (pageInput) {
            pageInput.value = String(currentPage);
            pageInput.min = '1';
            pageInput.max = String(totalPages);
        }

        if (totalPagesEl) {
            totalPagesEl.textContent = String(totalPages);
        }

        if (prevBtn) {
            prevBtn.disabled = currentPage <= 1;
        }

        if (nextBtn) {
            nextBtn.disabled = currentPage >= totalPages;
        }

        if (info) {
            info.textContent = `${totalRecords} record${totalRecords === 1 ? '' : 's'}`;
        }
    }

    function goToPage(page) {
        const totalPages = getTotalPages();

        if (page >= 1 && page <= totalPages) {
            currentPage = page;
            renderTable();
        }
    }
    function renderTable() {
        tbody.innerHTML = '';
        const filtered = getFilteredRequests();
        const totalPages = getTotalPages(filtered.length);
        const startIndex = (currentPage - 1) * recordsPerPage;
        const endIndex = Math.min(startIndex + recordsPerPage, filtered.length);
        const paginatedData = filtered.slice(startIndex, endIndex);

        if (paginatedData.length === 0) {
            const tr = document.createElement('tr');
            tr.className = 'empty-state-row';
            const td = document.createElement('td');
            td.colSpan = 9;
            td.textContent = 'No KK Profiling requests found.';
            tr.appendChild(td);
            tbody.appendChild(tr);
            updatePaginationFooter(0);
            return;
        }

        paginatedData.forEach((r) => {
            const tr = document.createElement('tr');
            const fullName = formatFullName(r);
            const email = r.emailAddress || '—';
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
                <td class="kk-email-cell">${email}</td>
                <td>${r.age}</td>
                <td>${r.sex || '—'}</td>
                <td>${r.barangay}</td>
                <td>${purokZone}</td>
                <td>${voterStatus}</td>
                ${renderActionMenuCell(r.id)}
            `;
            tbody.appendChild(tr);
        });

        updatePaginationFooter(filtered.length);
    }

    function populateSurveyViewForm(request) {
        const setVal = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.textContent = val ?? '';
        };

        setVal('vRespondentNumber', request.respondentNumber);
        setVal('vDate', request.date);
        setVal('vLastName', request.lastName);
        setVal('vFirstName', request.firstName);
        setVal('vMiddleName', request.middleName);
        const suffixEl = document.getElementById('vSuffix');
        const suffixCol = suffixEl?.closest('.kkf-name-col');
        const suffixDisplay = formatDisplaySuffix(request.suffix);
        if (suffixEl) suffixEl.textContent = suffixDisplay;
        if (suffixCol) suffixCol.hidden = !suffixDisplay;
        setVal('vRegion', request.region);
        setVal('vProvince', request.province);
        setVal('vCity', request.city);
        setVal('vBarangay', request.barangay);
        setVal('vPurokZone', request.purokZone);
        setVal('vAge', request.age);
        setVal('vDob', request.birthday);
        setVal('vEmail', request.emailAddress);
        setVal('vContact', request.contactNumber);
        setVal('vFacebook', request.facebookAccount);

        const logoEl = document.getElementById('kkRequestBarangayLogo');
        if (logoEl && request.barangayLogoUrl) {
            logoEl.src = request.barangayLogoUrl;
            logoEl.alt = `${request.barangay || 'Barangay'} SK Logo`;
        }

        const vSignatureImg = document.getElementById('vSignature');
        const vSignatureOverlay = document.getElementById('vSignatureOverlay');
        const vSignatureText = document.getElementById('vSignatureText');
        const nameParts = [request.firstName, request.middleName, request.lastName, formatDisplaySuffix(request.suffix)].filter(Boolean);
        const fullName = nameParts.join(' ');

        if (request.signature && String(request.signature).startsWith('data:image')) {
            if (vSignatureImg && vSignatureOverlay) {
                vSignatureImg.src = request.signature;
                vSignatureOverlay.style.display = 'flex';
            }
            if (vSignatureText) {
                vSignatureText.textContent = fullName;
                vSignatureText.style.display = 'block';
            }
        } else {
            if (vSignatureOverlay) vSignatureOverlay.style.display = 'none';
            if (vSignatureText) {
                vSignatureText.textContent = fullName;
                vSignatureText.style.display = 'block';
            }
        }

        const viewChks = document.querySelectorAll('#kkViewModal .kkf-view-chk');
        viewChks.forEach((chk) => {
            const field = chk.dataset.viewField;
            const fieldMap = {
                vSex: request.sex,
                vCivilStatus: request.civilStatus,
                vYouthAgeGroup: request.youthAgeGroup,
                vEducation: request.educationalBackground,
                vYouthClassification: request.youthClassification,
                vWorkStatus: request.workStatus,
                vSKVoter: request.registeredSKVoter,
                vVotingHistory: request.votingHistory,
                vVotingFrequency: request.kkTimes || request.votingFrequency,
                vNatVoter: request.registeredNationalVoter,
                vKKAssembly: request.attendedKKAssembly,
                vVotingReason: request.kkReason || request.votingReason,
                vGroupChat: request.willingToJoinGroupChat,
            };
            const stored = fieldMap[field] || '';
            chk.checked = stored.trim().toLowerCase() === chk.value.trim().toLowerCase();
        });
    }

    function renderActionMenuCell(requestId) {
        return `
            <td class="col-actions">
                <div class="row-actions-menu">
                    <button type="button" class="row-actions-trigger" aria-label="Actions" aria-haspopup="true" aria-expanded="false">${window.ROW_ACTIONS_ELLIPSIS || '⋯'}</button>
                    <div class="row-actions-dropdown" role="menu">
                        <button type="button" class="row-actions-item row-actions-item-view" data-action="view" data-id="${requestId}" role="menuitem">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <span>View Details</span>
                        </button>
                        <button type="button" class="row-actions-item row-actions-item-approve" data-action="approve" data-id="${requestId}" role="menuitem">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>Approve</span>
                        </button>
                        <button type="button" class="row-actions-item row-actions-item-danger" data-action="reject" data-id="${requestId}" role="menuitem">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            <span>Reject</span>
                        </button>
                    </div>
                </div>
            </td>
        `;
    }

    function openModal(modalElement) { if (modalElement) modalElement.style.display = 'flex'; }
    function closeModal(modalElement) { if (modalElement) modalElement.style.display = 'none'; }
    function closeAllModals() {
        [viewModal, approveModal, rejectModal].forEach((m) => {
            if (m) m.style.display = 'none';
        });
    }

    function findRequestById(id) {
        const idStr = String(id);
        return requests.find((r) => String(r.id) === idStr) || null;
    }

    function populateViewModal(request, skipErrorPanel = false) {
        populateSurveyViewForm(request);

        showEvaluationStatusBanner(request);

        const { firstName, middleName, lastName, suffix, age, birthday, barangay, purokZone, emailAddress, contactNumber, status } = request;

        // Build a map of field → error info for quick lookup
        const errors = request.censusErrors || [];
        const errorMap = {};
        errors.forEach((e, idx) => { errorMap[e.field] = { ...e, idx }; });
        const isEditable = (status === 'Wrong Credential' || status === 'Wrong Credentials') && errors.length > 0;
        const isDuplicate = (status === 'Duplicate') && errors.length > 0;

        // Track which fields have been corrected (persists across re-renders via closure on request object)
        if (!request._fixedFields) request._fixedFields = new Set();
        const fixedFields = request._fixedFields;

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
            } else if (!(isEditable || isDuplicate)) {
                return;
            } else {
                el.textContent = val ?? '';
            }
        };

        if (isEditable || isDuplicate) {
            setField('vLastName', 'lastName', lastName || '—');
            setField('vFirstName', 'firstName', firstName || '—');
            setField('vMiddleName', 'middleName', middleName || '—');
            setField('vSuffix', 'suffix', formatDisplaySuffix(suffix) || '—');
            setField('vBarangay', 'barangay', barangay || '—');
            setField('vPurokZone', 'purokZone', purokZone || '—');
            setField('vAge', 'age', age || '—');
            setField('vDob', 'birthday', birthday || '—');
            setField('vEmail', 'emailAddress', emailAddress || '—');
            setField('vContact', 'contactNumber', contactNumber || '—');
        }

        const mismatches = request.evaluationNotes?.mismatches || [];
        const mismatchMap = {};
        mismatches.forEach(m => { mismatchMap[m.field] = m; });

        const fieldToElId = {
            age: 'vAge',
            birthday: 'vDob',
            sex: 'vSex',
            name: 'vLastName',
        };

        // Remove old mismatch badges
        document.querySelectorAll('.kk-eval-mismatch-badge').forEach(el => el.remove());

        if ((status === 'Wrong Credentials' || status === 'Wrong Credential') && mismatches.length > 0) {
            Object.entries(fieldToElId).forEach(([field, elId]) => {
                if (!mismatchMap[field]) return;
                const el = document.getElementById(elId);
                if (!el) return;
                const m = mismatchMap[field];
                const badge = document.createElement('span');
                badge.className = 'kk-eval-mismatch-badge';
                badge.style.cssText = 'display:inline-block;margin-left:6px;padding:2px 7px;background:#fef2f2;border:1px solid #fca5a5;color:#991b1b;border-radius:4px;font-size:0.75rem;font-weight:600;';
                badge.title = `Previous record: ${m.previous}`;
                badge.textContent = `⚠ Previous: ${m.previous}`;
                el.parentNode?.appendChild(badge);
            });
        }

        // Civil Status — also handle as editable if it's an error field
        if (isEditable && errorMap['civilStatus'] && !skipErrorPanel) {
            const e = errorMap['civilStatus'];
            const isFixed = fixedFields.has('civilStatus');
            // Highlight the civil status block header
            const csBlock = document.querySelector('.kk-qs-demo-block-label');
            // We'll show an inline error note below the civil status block
            let csErrEl = document.getElementById('kkViewCS_ErrorNote');
            if (!csErrEl) {
                const csOptions = document.querySelector('#kkViewModal .kkf-demo-options-2col');
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

        if (!skipErrorPanel) updateInlineSaveBtn(request);

        const errorsWrap = document.getElementById('kkViewCensusErrorsWrap');
        if (errorsWrap) errorsWrap.style.display = 'none';

        const oldDupPanel = document.getElementById('kkViewDuplicatePanel');
        if (oldDupPanel) oldDupPanel.remove();
    }

    function showEvaluationStatusBanner(request) {
        const banner = document.getElementById('kkViewEvaluationBanner');
        if (!banner) {
            return;
        }

        const status = request.status || request.evaluation_status || '';
        const notes = request.evaluationNotes?.message || '';

        const normalizedStatus = String(status || '').trim().toLowerCase();
        if (
            !status
            || status === 'New Applicant'
            || status === 'New Kabataan'
            || HIDDEN_REGISTRATION_STATUSES.has(normalizedStatus)
        ) {
            banner.hidden = true;
            banner.textContent = '';
            banner.className = 'kk-view-evaluation-banner';
            return;
        }

        banner.hidden = false;
        banner.className = 'kk-view-evaluation-banner';

        if (status === 'Duplicate') {
            banner.classList.add('is-duplicate');
            banner.textContent = notes || 'Duplicate registration detected for this applicant.';
        } else if (status === 'Wrong Credentials' || status === 'Wrong Credential') {
            banner.classList.add('is-warning');
            banner.textContent = notes || 'Some fields do not match census records.';
        } else if (status === 'Not Profiled') {
            banner.classList.add('is-pending');
            banner.textContent = notes || 'Pending review — not yet matched to KK profiling history.';
        } else if (status === 'ID Verified' || status === 'Auto Approved') {
            banner.classList.add('is-success');
            banner.textContent = notes || 'Identity verification passed (name and barangay matched on uploaded ID).';
        } else {
            banner.classList.add('is-pending');
            banner.textContent = notes || status;
        }
    }

    // Renders or updates the inline "Save Corrections" button inside the form
    function updateInlineSaveBtn(request) {
        const errors = request.censusErrors || [];
        const fixedFields = request._fixedFields || new Set();
        const isEditable = (request.status === 'Wrong Credential' || request.status === 'Wrong Credentials') && errors.length > 0;

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
    if (sexFilter) { sexFilter.addEventListener('change', () => { currentSexFilter = sexFilter.value; currentPage = 1; renderTable(); }); }
    if (youthAgeGroupFilter) { youthAgeGroupFilter.addEventListener('change', () => { currentYouthAgeGroupFilter = youthAgeGroupFilter.value; currentPage = 1; renderTable(); }); }

    const prevBtn = document.getElementById('kkPrevBtn');
    const nextBtn = document.getElementById('kkNextBtn');
    const pageInput = document.getElementById('kkPageInput');
    const rowsPerPageSelect = document.getElementById('kkRowsPerPageSelect');

    if (prevBtn) prevBtn.addEventListener('click', () => goToPage(currentPage - 1));
    if (nextBtn) nextBtn.addEventListener('click', () => goToPage(currentPage + 1));

    if (pageInput) {
        pageInput.addEventListener('change', () => {
            const page = parseInt(pageInput.value, 10);
            if (!Number.isNaN(page)) {
                goToPage(page);
            }
        });
    }

    if (rowsPerPageSelect) {
        recordsPerPage = parseInt(rowsPerPageSelect.value, 10) || 10;
        rowsPerPageSelect.addEventListener('change', () => {
            recordsPerPage = parseInt(rowsPerPageSelect.value, 10) || 10;
            currentPage = 1;
            renderTable();
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
        const rawId = btn.getAttribute('data-id');
        if (!action || rawId === null || rawId === '') return;
        const request = findRequestById(rawId);
        if (!request) return;
        activeRequestId = request.id;
        if (action === 'view') {
            resetModalMaximize(viewModal);
            request._fixedFields = new Set();
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
                if (modal === viewModal) resetModalMaximize(viewModal);
            }
        });
    });

    const approveConfirmBtn = document.getElementById('kkApproveConfirmBtn');
    if (approveConfirmBtn) {
        const approveBtnDefaultHtml = approveConfirmBtn.innerHTML;

        approveConfirmBtn.addEventListener('click', () => {
            if (activeRequestId === null || approveConfirmBtn.disabled) {
                closeModal(approveModal);
                return;
            }

            approveConfirmBtn.disabled = true;
            approveConfirmBtn.innerHTML = '<span class="kk-approve-spinner"></span> Approving...';

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

            fetch(`/kk-profiling-requests/${activeRequestId}/approve`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    closeModal(approveModal);
                    closeModal(viewModal);
                    const displayNo = res.respondent_display
                        || formatRespondentDisplay(res.respondent_sequence);
                    const toastMsg = displayNo && displayNo !== '—'
                        ? `KK Profiling approved! Respondent #${displayNo} assigned.`
                        : (res.message || 'KK Profiling approved successfully.');
                    showToast(toastMsg, 'success');
                    broadcastKkProfileEvent();
                    loadData();
                } else {
                    showToast(res.message || 'Failed to approve.', 'error');
                }
            })
            .catch(() => showToast('Network error. Please try again.', 'error'))
            .finally(() => {
                approveConfirmBtn.disabled = false;
                approveConfirmBtn.innerHTML = approveBtnDefaultHtml;
            });
        });
    }

    function showSuccessModal(action = 'Approved') {
        showToast(action === 'Approved' ? 'KK Profiling Request Approved Successfully' : 'KK Profiling Request Rejected Successfully', 'success');
    }

    const rejectConfirmBtn = document.getElementById('kkRejectConfirmBtn');
    if (rejectConfirmBtn) {
        const rejectBtnDefaultHtml = rejectConfirmBtn.innerHTML;

        rejectConfirmBtn.addEventListener('click', () => {
            if (activeRequestId === null || rejectConfirmBtn.disabled) {
                closeModal(rejectModal);
                return;
            }

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
            if (selectedReasons.length === 0) { alert('Please select at least one rejection reason.'); return; }

            rejectConfirmBtn.disabled = true;
            rejectConfirmBtn.innerHTML = '<span class="kk-approve-spinner"></span> Rejecting...';

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            fetch(`/kk-profiling-requests/${activeRequestId}/reject`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ reasons: selectedReasons }),
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    closeModal(rejectModal);
                    closeModal(viewModal);
                    showToast(
                        res.already_rejected
                            ? 'This request was already rejected.'
                            : 'KK Profiling Request Rejected',
                        'success'
                    );
                    loadData();
                } else {
                    showToast(res.message || 'Failed to reject.', 'error');
                }
            })
            .catch(() => showToast('Network error. Please try again.', 'error'))
            .finally(() => {
                rejectConfirmBtn.disabled = false;
                rejectConfirmBtn.innerHTML = rejectBtnDefaultHtml;
            });
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

    // Load data from API then render
    function loadData(params = {}) {
        const url = new URL('/kk-profiling-requests/data', window.location.origin);
        if (params.search) url.searchParams.set('search', params.search);
        if (params.purok) url.searchParams.set('purok', params.purok);
        if (params.voter) url.searchParams.set('voter', params.voter);

        fetch(url.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(response => {
            requests.length = 0;
            (response.data || []).forEach((r, i) => {
                requests.push({
                    id: r.id,
                    respondentNumber: formatRespondentDisplay(r.respondent_sequence, r.respondent_number),
                    respondentSequence: r.respondent_sequence,
                    date: r.submitted_at || '—',
                    lastName: r.last_name,
                    firstName: r.first_name,
                    middleName: r.middle_name,
                    suffix: r.suffix,
                    age: r.age,
                    birthday: r.birthday,
                    sex: r.sex,
                    emailAddress: r.email,
                    contactNumber: r.contact_number,
                    barangay: r.barangay,
                    purokZone: r.purok_zone,
                    registeredSKVoter: r.sk_voter,
                    registeredNationalVoter: r.national_voter,
                    registeredVoter: r.sk_voter,
                    civilStatus: r.civil_status,
                    youthClassification: r.youth_classification,
                    youthAgeGroup: r.youth_age_group,
                    workStatus: r.work_status,
                    educationalBackground: r.education,
                    votingHistory: r.sk_voted,
                    attendedKKAssembly: r.kk_assembly,
                    kkTimes: r.kk_times,
                    kkReason: r.kk_reason,
                    facebookAccount: r.facebook,
                    willingToJoinGroupChat: r.group_chat,
                    signature: r.signature,
                    status: resolveEvaluationStatus(r.evaluation_status, r.status),
                    registrationStatus: r.status,
                    evaluationNotes: r.evaluation_notes,
                    rejectionReason: r.review_notes,
                    region: r.region || 'Region IV-A (CALABARZON)',
                    province: r.province || 'Laguna',
                    city: r.city || 'Santa Cruz',
                    barangayLogoUrl: r.barangay_logo_url || null,
                    supportingDocuments: r.supporting_documents || [],
                    idVerification: r.id_verification || null,
                });
            });

            const stats = response.stats || {};
            const el = (id) => document.getElementById(id);
            if (el('kkStatApproved'))  el('kkStatApproved').textContent  = stats.active || 0;
            if (el('kkStatPending'))   el('kkStatPending').textContent   = stats.pending_verification || 0;
            if (el('kkStatRejected'))  el('kkStatRejected').textContent  = stats.rejected || 0;
            if (el('kkStatTotal'))     el('kkStatTotal').textContent     = stats.total || 0;

            renderTable();
        })
        .catch(() => {
            renderTable();
        });
    }

    loadData();
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