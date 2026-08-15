/**
 * Read-only KK profiling questionnaire view — shared by KK Requests, Rejected, Deleted modules.
 */

export function populateSupportingDocuments(request) {
    const wrap = document.getElementById('kkViewDocumentsWrap');
    const grid = document.getElementById('kkViewDocumentsGrid');
    const verificationEl = document.getElementById('kkViewIdVerification');
    const emptyEl = document.getElementById('kkViewDocumentsEmpty');
    const documents = request.supportingDocuments || request.supporting_documents || [];
    const idVerification = request.idVerification || request.id_verification || null;

    if (!grid) {
        return;
    }

    grid.innerHTML = '';

    if (!Array.isArray(documents) || documents.length === 0) {
        if (wrap) wrap.style.display = 'none';
        if (emptyEl) emptyEl.hidden = false;

        if (verificationEl) {
            verificationEl.hidden = true;
            verificationEl.textContent = '';
        }

        return;
    }

    if (wrap) wrap.style.display = 'block';
    if (emptyEl) emptyEl.hidden = true;

    if (verificationEl) {
        if (idVerification) {
            const nameOk = idVerification.name_match !== false;
            const barangayOk = Boolean(idVerification.barangay_match);
            const matched = nameOk && barangayOk && !idVerification.duplicate_detected;
            verificationEl.hidden = false;
            verificationEl.className = `kk-view-id-verification ${matched ? 'is-match' : 'is-mismatch'}`;

            if (idVerification.duplicate_detected) {
                verificationEl.textContent = 'ID verification: Duplicate registration detected (same name, date of birth, and barangay).';
            } else if (matched) {
                verificationEl.textContent = `ID verification passed: Name and barangay match the registration form. ${idVerification.match_reason || idVerification.message || ''}`.trim();
            } else if (!nameOk) {
                verificationEl.textContent = 'ID verification: Name on the uploaded School ID does not match the KK Profiling form.';
            } else {
                verificationEl.textContent = `ID verification: ${idVerification.message || idVerification.match_reason || 'Barangay not matched on ID.'}`;
            }
        } else {
            verificationEl.hidden = true;
            verificationEl.textContent = '';
        }
    }

    documents.forEach((docItem) => {
        const card = document.createElement('div');
        card.className = 'kk-view-document-card';

        const title = document.createElement('div');
        title.className = 'kk-view-document-card-title';
        title.textContent = docItem.label || docItem.type || 'Supporting Document';
        card.appendChild(title);

        (docItem.sides || []).forEach((side) => {
            if (!side?.url) {
                return;
            }

            const sideWrap = document.createElement('div');
            sideWrap.className = 'kk-view-document-side';

            const sideLabel = document.createElement('div');
            sideLabel.className = 'kk-view-document-side-label';
            sideLabel.textContent = side.label || side.side || 'Image';
            sideWrap.appendChild(sideLabel);

            const img = document.createElement('img');
            img.className = 'kk-view-document-preview';
            img.src = side.url;
            img.alt = `${title.textContent} ${sideLabel.textContent}`;
            img.loading = 'lazy';
            img.addEventListener('click', () => window.open(side.url, '_blank', 'noopener'));
            sideWrap.appendChild(img);

            const link = document.createElement('a');
            link.className = 'kk-view-document-link';
            link.href = side.url;
            link.target = '_blank';
            link.rel = 'noopener noreferrer';
            link.textContent = 'Open full size';
            sideWrap.appendChild(link);

            card.appendChild(sideWrap);
        });

        grid.appendChild(card);
    });
}

export function populateKkProfilingView(request, options = {}) {
    const {
        showRejection = false,
        rejectionReason = '',
    } = options;

    const {
        respondentNumber, date, firstName, middleName, lastName, suffix, age, birthday, sex, civilStatus,
        region, province, city, barangay, purokZone, emailAddress, contactNumber,
        youthClassification, youthAgeGroup, workStatus, educationalBackground,
        registeredSKVoter, registeredNationalVoter, votingHistory, kkTimes, kkReason, attendedKKAssembly,
        facebookAccount, willingToJoinGroupChat, signature, barangayLogoUrl,
    } = request;

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
        return candidates.some((candidate) => normalized === candidate.trim().toLowerCase());
    };

    const formatDisplaySuffix = (value, suffixOther) => {
        if (!value) return 'None';
        const normalized = String(value).trim();
        if (!normalized || normalized.toLowerCase() === 'none') return 'None';
        if (normalized.toLowerCase() === 'other' || normalized.toLowerCase() === 'others') {
            const other = String(suffixOther || '').trim();
            return other || 'None';
        }
        return normalized;
    };

    const applySuffixDisplay = (elementId, value, suffixOther) => {
        const el = document.getElementById(elementId);
        const col = el?.closest('.kkp-name-col, .kkf-name-col');
        const display = formatDisplaySuffix(value, suffixOther);
        if (el) el.textContent = display;
        if (col) col.hidden = false;
    };

    const formatBirthdayDisplay = (value) => {
        if (!value || value === '—') return '—';
        const iso = /^(\d{4})-(\d{2})-(\d{2})$/.exec(value);
        if (iso) return `${iso[2]}/${iso[3]}/${iso[1]}`;
        return value;
    };

    setVal('kkViewRespondentNumber', respondentNumber);
    setVal('kkViewDate', date);
    setVal('kkViewLastName', lastName || '—');
    setVal('kkViewFirstName', firstName || '—');
    setVal('kkViewMiddleName', middleName || '—');
    applySuffixDisplay('kkViewSuffix', suffix, request.suffixOther || request.suffix_other || request.custom_suffix);
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

    const yesCell = document.getElementById('kkViewAssemblyYesCell');
    const noCell = document.getElementById('kkViewAssemblyNoCell');
    const arrowYes = document.querySelector('#kkViewAssemblyQuestion .kkp-assembly-arrow--yes');
    const arrowNo = document.querySelector('#kkViewAssemblyQuestion .kkp-assembly-arrow--no');
    [yesCell, noCell].forEach((cell) => {
        if (!cell) return;
        cell.classList.remove('kkp-assembly-followup--inactive');
        cell.classList.remove('kkp-assembly-followup--active');
    });
    if (attendedKKAssembly === 'Yes') {
        arrowYes?.classList.add('kkp-assembly-arrow--on');
        arrowNo?.classList.remove('kkp-assembly-arrow--on');
    } else if (attendedKKAssembly === 'No') {
        arrowYes?.classList.remove('kkp-assembly-arrow--on');
        arrowNo?.classList.add('kkp-assembly-arrow--on');
    } else {
        arrowYes?.classList.remove('kkp-assembly-arrow--on');
        arrowNo?.classList.remove('kkp-assembly-arrow--on');
    }

    const normalizedReason = (kkReason || '').trim();
    setCheck('kkViewVR_NoKK',
        normalizedReason === 'There was no KK Assembly Meeting'
        || normalizedReason === 'There was no KK Assembly');
    setCheck('kkViewVR_NotInt',
        normalizedReason === 'Not interested to Attend'
        || normalizedReason === 'Not Interested to Attend');

    const facebookEl = document.getElementById('kkViewFacebookAccount');
    const facebookWrap = facebookEl?.closest('.kkp-footer-fb');
    const facebookRaw = String(facebookAccount || '').trim();
    const facebookValue = (facebookRaw && facebookRaw !== '—' && facebookRaw !== '-') ? facebookRaw : '';
    if (facebookEl) {
        facebookEl.textContent = facebookValue;
        facebookEl.classList.toggle('kkp-uline-fb--empty', facebookValue === '');
    }
    if (facebookWrap) {
        facebookWrap.classList.toggle('kkp-footer-fb--empty', facebookValue === '');
    }
    const groupChatAnswer = (willingToJoinGroupChat || '').trim();
    setCheck('kkViewGC_Yes', groupChatAnswer === 'Yes');
    setCheck('kkViewGC_No', groupChatAnswer === 'No');

    const logoEl = document.getElementById('kkViewBarangayLogo');
    if (logoEl && barangayLogoUrl) {
        logoEl.src = barangayLogoUrl;
        logoEl.alt = `${barangay || 'Barangay'} SK Logo`;
    }

    const sigNameEl = document.getElementById('kkViewSignatureName');
    const sigPreview = document.getElementById('kkViewSignaturePreview');
    const sigOverlay = document.getElementById('kkViewSignatureOverlay');
    const nameParts = [
        firstName,
        middleName ? middleName.charAt(0) + '.' : null,
        lastName,
        suffix && formatDisplaySuffix(suffix) && formatDisplaySuffix(suffix) !== 'None'
            ? formatDisplaySuffix(suffix)
            : null,
    ].filter(Boolean);
    const printedName = nameParts.join(' ') || '—';
    if (sigNameEl) sigNameEl.textContent = printedName;
    if (sigPreview && sigOverlay) {
        if (signature && String(signature).startsWith('data:image')) {
            sigPreview.src = signature;
            sigOverlay.style.display = 'flex';
        } else {
            sigPreview.removeAttribute('src');
            sigOverlay.style.display = 'none';
        }
    }

    const rejectionWrap = document.getElementById('kkViewRejectionWrap');
    const rejectionText = document.getElementById('kkViewRejectionText');
    if (rejectionWrap && rejectionText) {
        if (showRejection && rejectionReason) {
            rejectionWrap.style.display = 'block';
            rejectionText.style.whiteSpace = 'pre-line';
            rejectionText.textContent = rejectionReason;
        } else {
            rejectionWrap.style.display = 'none';
            rejectionText.textContent = '';
        }
    }

    populateSupportingDocuments(request);
}

export function mapRegistrationToKkView(record) {
    return {
        respondentNumber: (() => {
            if (record.respondent_number && String(record.respondent_number).includes('-')) {
                return String(record.respondent_number).split('-').pop() || '—';
            }
            if (record.respondent_sequence) {
                return String(record.respondent_sequence).padStart(4, '0');
            }
            if (record.respondent_display && !String(record.respondent_display).includes('-')) {
                return record.respondent_display;
            }
            return record.respondent_display || record.respondent_number || 'Auto-generated';
        })(),
        date: record.submitted_at || '—',
        firstName: record.first_name,
        middleName: record.middle_name,
        lastName: record.last_name,
        suffix: record.suffix || '',
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
        rejectionReason: record.rejection_reason,
        registrationStatus: record.status,
        supportingDocuments: record.supporting_documents || [],
        idVerification: record.id_verification || null,
    };
}
