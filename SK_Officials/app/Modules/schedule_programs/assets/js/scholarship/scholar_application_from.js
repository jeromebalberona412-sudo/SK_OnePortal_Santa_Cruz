const SAF_STORAGE_KEY = 'scholar_application_forms';
const SAF_ACTIVE_PROGRAM_KEY = 'scholar_active_program';

let editingProgramId = null;

document.addEventListener('DOMContentLoaded', () => {
    const tableBody = document.getElementById('safFormsTableBody');
    if (!tableBody) return;

    const modal = document.getElementById('scholarProgramModal');
    const modalBox = document.getElementById('scholarProgramBox');
    const openBtn = document.getElementById('safOpenFormBtn');
    const closeBtn = document.getElementById('scholarProgramClose');
    const cancelBtn = document.getElementById('btnCancelProgram');
    const saveBtn = document.getElementById('btnSaveProgram');
    const maximizeBtn = document.getElementById('scholarProgramMaximize');
    const previewModal = document.getElementById('safPreviewModal');
    const previewBody = document.getElementById('safPreviewBody');
    const previewClose = document.getElementById('safPreviewClose');
    const toastEl = document.getElementById('safToast');

    const builder = window.SpfbFormBuilder;
    
    // Load and display active program
    loadActiveProgram();
    
    // Setup active program buttons
    setupActiveProgramButtons();

    function showToast(msg, type) {
        if (!toastEl) return;
        toastEl.textContent = msg;
        toastEl.style.display = 'flex';
        toastEl.style.background = type === 'error' ? '#ef4444' : '#22c55e';
        clearTimeout(showToast._t);
        showToast._t = setTimeout(() => { toastEl.style.display = 'none'; }, 2800);
    }

    function loadForms() {
        try {
            return JSON.parse(localStorage.getItem(SAF_STORAGE_KEY) || '[]');
        } catch {
            return [];
        }
    }

    function saveForms(forms) {
        localStorage.setItem(SAF_STORAGE_KEY, JSON.stringify(forms));
    }

    function uid() {
        return 'saf_' + Date.now() + '_' + Math.random().toString(36).slice(2, 7);
    }

    function escapeHtml(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function resetModalForm() {
        editingProgramId = null;
        
        const fields = [
            'programName', 'programCommittee', 'participationQty',
            'programVenue', 'programDescription', 'programTerms',
            'schedStartDate', 'schedEndDate', 'programStatus',
            'schedStartTime', 'schedEndTime'
        ];

        fields.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                if (el.tagName === 'SELECT') {
                    if (id === 'programStatus') {
                        el.value = 'auto';
                    } else if (id === 'schedStartTime') {
                        el.value = '08:00';
                    } else if (id === 'schedEndTime') {
                        el.value = '17:00';
                    } else {
                        el.value = '';
                    }
                } else if (el.type === 'number') {
                    el.value = '';
                } else {
                    el.value = '';
                }
            }
        });
        
        // Reset counters
        ['programNameCount', 'venueCount', 'descriptionCount', 'termsCount', 'spfbAnnouncementCount'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.textContent = '0';
        });
    }

    function openModal(forEditId) {
        console.log('openModal called', { modal, forEditId });
        if (modal) {
            modal.style.display = 'flex';
            console.log('Modal opened successfully');
            try {
                resetModalForm();
                // Reset form builder
                if (window.SpfbFormBuilder) {
                    window.SpfbFormBuilder.reset();
                }
                // Setup counters
                setupCounters();
                // Setup standard form toggle
                setupStandardFormToggle();
            } catch (e) {
                console.error('Error in openModal:', e);
            }
        } else {
            console.error('Modal element not found!');
        }
    }

    function setupCounters() {
        // Setup announcement counter
        const announcementEl = document.getElementById('spfbAnnouncement');
        const countEl = document.getElementById('spfbAnnouncementCount');
        if (announcementEl && countEl) {
            announcementEl.addEventListener('input', () => {
                countEl.textContent = String(announcementEl.value.length);
            });
        }

        // Setup description counter
        const descriptionEl = document.getElementById('programDescription');
        const descCountEl = document.getElementById('descriptionCount');
        if (descriptionEl && descCountEl) {
            descriptionEl.addEventListener('input', () => {
                descCountEl.textContent = String(descriptionEl.value.length);
            });
        }

        // Setup terms counter
        const termsEl = document.getElementById('programTerms');
        const termsCountEl = document.getElementById('termsCount');
        if (termsEl && termsCountEl) {
            termsEl.addEventListener('input', () => {
                termsCountEl.textContent = String(termsEl.value.length);
            });
        }
    }

    function setupStandardFormToggle() {
        const toggle = document.getElementById('includeStandardFormToggle');
        const staticPreview = document.getElementById('staticFormPreview');

        if (toggle && staticPreview) {
            // Set initial state
            staticPreview.style.display = toggle.checked ? 'block' : 'none';

            toggle.addEventListener('change', function () {
                staticPreview.style.display = this.checked ? 'block' : 'none';
            });
        }
    }

    function closeModal() {
        if (modal) modal.style.display = 'none';
        if (modalBox) modalBox.classList.remove('schol-modal-maximized');
        resetModalForm();
    }

    function initDateMin() {
        const today = new Date().toISOString().split('T')[0];
        ['safStartDate', 'safEndDate'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.setAttribute('min', today);
        });
    }

    function filterFormsByDate(forms, filterValue) {
        if (filterValue === 'all') return forms;
        
        const now = new Date();
        const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
        
        return forms.filter(f => {
            if (!f.createdAt) return false;
            const createdDate = new Date(f.createdAt);
            
            switch (filterValue) {
                case 'recent': {
                    const sevenDaysAgo = new Date(today);
                    sevenDaysAgo.setDate(sevenDaysAgo.getDate() - 7);
                    return createdDate >= sevenDaysAgo;
                }
                case 'monthly': {
                    return createdDate.getMonth() === now.getMonth() && 
                           createdDate.getFullYear() === now.getFullYear();
                }
                case 'yearly': {
                    return createdDate.getFullYear() === now.getFullYear();
                }
                default:
                    return true;
            }
        });
    }

    function renderFormsTable(filterValue = 'all') {
        const allForms = loadForms();
        const activeProgram = getActiveProgram();
        
        // Filter out active program from table
        const formsToShow = allForms.filter(f => !activeProgram || f.id !== activeProgram.id);
        const forms = filterFormsByDate(formsToShow, filterValue);
        
        // Update program count
        const countEl = document.getElementById('programCount');
        if (countEl) {
            countEl.textContent = forms.length;
        }
        
        if (!forms.length) {
            const message = filterValue === 'all' 
                ? 'No closed programs yet.'
                : 'No programs found for the selected filter.';
            tableBody.innerHTML = `<tr><td colspan="8" class="saf-table-empty">${message}</td></tr>`;
            return;
        }

        tableBody.innerHTML = forms.map(f => {
            const statusClass = f.status === 'open' ? 'schol-pill-approved' : 
                               f.status === 'closed' ? 'schol-pill-rejected' : 
                               'schol-pill-pending';
            const statusText = f.status === 'auto' ? 'Auto' : 
                              f.status === 'open' ? 'Open' : 
                              f.status === 'closed' ? 'Closed' : 'Upcoming';
            
            return `
            <tr>
                <td>${escapeHtml(f.programName)}</td>
                <td>${escapeHtml(f.programType)}</td>
                <td>${escapeHtml(f.committee)}</td>
                <td>${escapeHtml(f.participationQty || 'N/A')}</td>
                <td>${escapeHtml(f.startDate)}</td>
                <td>${escapeHtml(f.endDate)}</td>
                <td><span class="schol-pill ${statusClass}">${statusText}</span></td>
                <td class="col-actions">
                    <div class="prog-tbl-actions">
                        <button type="button" class="prog-btn prog-btn-view" data-form-view="${f.id}">View</button>
                        <button type="button" class="prog-btn prog-btn-delete" data-form-delete="${f.id}">Delete</button>
                    </div>
                </td>
            </tr>
        `;
        }).join('');

        tableBody.querySelectorAll('[data-form-view]').forEach(btn => {
            btn.addEventListener('click', () => openFormPreview(btn.getAttribute('data-form-view')));
        });
        tableBody.querySelectorAll('[data-form-delete]').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-form-delete');
                if (!confirm('Delete this program?')) return;
                saveForms(loadForms().filter(f => f.id !== id));
                const currentFilter = document.getElementById('programFilter')?.value || 'all';
                renderFormsTable(currentFilter);
                showToast('Program deleted.', 'success');
            });
        });
    }

    function openFormPreview(formId) {
        const f = loadForms().find(x => x.id === formId);
        const viewProgramBody = document.getElementById('viewProgramBody');
        const viewProgramModal = document.getElementById('viewProgramModal');
        
        if (!f || !viewProgramBody || !viewProgramModal) return;

        // Format time for display
        const formatTime = (time24) => {
            if (!time24) return '';
            const [hours, minutes] = time24.split(':');
            const h = parseInt(hours);
            const ampm = h >= 12 ? 'PM' : 'AM';
            const h12 = h % 12 || 12;
            return `${h12}:${minutes} ${ampm}`;
        };

        // Determine status
        const now = new Date();
        const startDateTime = new Date(`${f.startDate}T${f.startTime}`);
        const endDateTime = new Date(`${f.endDate}T${f.endTime}`);
        
        let status = f.status;
        if (status === 'auto') {
            if (now < startDateTime) {
                status = 'upcoming';
            } else if (now >= startDateTime && now <= endDateTime) {
                status = 'open';
            } else {
                status = 'closed';
            }
        }
        
        const statusColors = {
            open: { bg: '#dcfce7', text: '#166534', label: 'Open & Ongoing' },
            closed: { bg: '#fee2e2', text: '#991b1b', label: 'Closed' },
            upcoming: { bg: '#dbeafe', text: '#1e40af', label: 'Upcoming' }
        };
        
        const statusStyle = statusColors[status] || statusColors.closed;

        viewProgramBody.innerHTML = `
            <div style="padding:24px;background:#f9fafb;">
                <!-- Program Header -->
                <div style="background:white;border-radius:12px;padding:24px;margin-bottom:20px;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:12px;">
                        <h2 style="font-size:24px;font-weight:700;margin:0;color:#111827;">${escapeHtml(f.programName)}</h2>
                        <span style="display:inline-flex;align-items:center;padding:6px 16px;border-radius:999px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;background:${statusStyle.bg};color:${statusStyle.text};">${statusStyle.label}</span>
                    </div>
                    
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-top:20px;">
                        <div style="padding:12px;background:#f9fafb;border-radius:8px;">
                            <div style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:6px;">Program Type</div>
                            <div style="font-size:14px;font-weight:600;color:#111827;">${escapeHtml(f.programType)}</div>
                        </div>
                        <div style="padding:12px;background:#f9fafb;border-radius:8px;">
                            <div style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:6px;">Committee</div>
                            <div style="font-size:14px;font-weight:600;color:#111827;">${escapeHtml(f.committee)}</div>
                        </div>
                        <div style="padding:12px;background:#f9fafb;border-radius:8px;">
                            <div style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:6px;">Participants</div>
                            <div style="font-size:14px;font-weight:600;color:#111827;">${escapeHtml(f.participationQty || 'N/A')}</div>
                        </div>
                    </div>
                </div>

                <!-- Schedule Information -->
                <div style="background:white;border-radius:12px;padding:24px;margin-bottom:20px;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                    <h3 style="font-size:16px;font-weight:700;color:#111827;margin:0 0 16px;display:flex;align-items:center;gap:8px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        Schedule Information
                    </h3>
                    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px;">
                        <div>
                            <div style="font-size:12px;color:#6b7280;margin-bottom:4px;">Start Date & Time</div>
                            <div style="font-size:15px;font-weight:600;color:#111827;">${escapeHtml(f.startDate)} at ${formatTime(f.startTime)}</div>
                        </div>
                        <div>
                            <div style="font-size:12px;color:#6b7280;margin-bottom:4px;">End Date & Time</div>
                            <div style="font-size:15px;font-weight:600;color:#111827;">${escapeHtml(f.endDate)} at ${formatTime(f.endTime)}</div>
                        </div>
                    </div>
                </div>

                ${f.venue ? `
                <!-- Venue -->
                <div style="background:white;border-radius:12px;padding:24px;margin-bottom:20px;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                    <h3 style="font-size:16px;font-weight:700;color:#111827;margin:0 0 12px;display:flex;align-items:center;gap:8px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        Venue
                    </h3>
                    <div style="font-size:14px;color:#374151;line-height:1.6;">${escapeHtml(f.venue)}</div>
                </div>
                ` : ''}

                ${f.description ? `
                <!-- Description -->
                <div style="background:white;border-radius:12px;padding:24px;margin-bottom:20px;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                    <h3 style="font-size:16px;font-weight:700;color:#111827;margin:0 0 12px;display:flex;align-items:center;gap:8px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        Description
                    </h3>
                    <div style="font-size:14px;color:#374151;line-height:1.8;">${escapeHtml(f.description)}</div>
                </div>
                ` : ''}

                ${f.terms ? `
                <!-- Terms and Conditions -->
                <div style="background:white;border-radius:12px;padding:24px;margin-bottom:20px;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                    <h3 style="font-size:16px;font-weight:700;color:#111827;margin:0 0 12px;display:flex;align-items:center;gap:8px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        Terms and Conditions
                    </h3>
                    <div style="font-size:14px;color:#374151;line-height:1.8;white-space:pre-wrap;">${escapeHtml(f.terms)}</div>
                </div>
                ` : ''}

                <!-- Application Forms Section -->
                <div style="background:white;border-radius:12px;padding:24px;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                    <h3 style="font-size:18px;font-weight:700;color:#111827;margin:0 0 20px;display:flex;align-items:center;gap:8px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        Application Forms
                    </h3>
                    
                    <!-- Custom Questions (Google Form Style) -->
                    ${f.customQuestions && f.customQuestions.length > 0 ? `
                        <div style="margin-bottom:32px;">
                            <h4 style="font-size:15px;font-weight:600;color:#374151;margin:0 0 16px;padding-bottom:12px;border-bottom:2px solid #e5e7eb;">Custom Questions (Google Form Style)</h4>
                            <div style="background:#f8f9fa;border-radius:8px;padding:20px;border:1px solid #e5e7eb;">
                                <!-- Form Header -->
                                <div style="background:#673ab7;color:white;padding:20px;border-radius:8px 8px 0 0;margin:-20px -20px 20px;">
                                    <h5 style="font-size:24px;font-weight:400;margin:0 0 8px;">${escapeHtml(f.programName)}</h5>
                                    <p style="font-size:13px;margin:0;opacity:0.9;">Additional Questions</p>
                                </div>

                                ${f.customQuestions.map((q, idx) => `
                                    <div style="background:white;border-radius:8px;padding:20px;margin-bottom:16px;border:1px solid #e5e7eb;">
                                        <div style="font-size:14px;color:#202124;font-weight:500;margin-bottom:8px;">
                                            ${idx + 1}. ${escapeHtml(q.question)}
                                            ${q.required ? '<span style="color:#d93025;margin-left:4px;">*</span>' : ''}
                                        </div>
                                        <div style="font-size:12px;color:#5f6368;font-style:italic;margin-bottom:12px;">
                                            Type: ${escapeHtml(q.type || 'Short Answer')}
                                        </div>
                                        ${q.options && q.options.length > 0 ? `
                                            <div style="margin-top:12px;padding-left:8px;">
                                                ${q.options.map(opt => `
                                                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                                                        <div style="width:16px;height:16px;border:2px solid #5f6368;border-radius:50%;"></div>
                                                        <span style="font-size:13px;color:#202124;">${escapeHtml(opt)}</span>
                                                    </div>
                                                `).join('')}
                                            </div>
                                        ` : `
                                            <div style="border-bottom:1px dotted #dadce0;padding:8px 0;color:#5f6368;font-size:13px;">Your answer</div>
                                        `}
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    ` : `
                        <div style="background:#fff3cd;border:1px solid #ffc107;border-radius:8px;padding:16px;text-align:center;margin-bottom:32px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#856404" stroke-width="2" style="margin-bottom:8px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            <div style="font-size:14px;color:#856404;font-weight:500;">No custom questions added</div>
                            <div style="font-size:12px;color:#856404;margin-top:4px;">Only standard scholarship form fields will be collected</div>
                        </div>
                    `}

                    <!-- Standard Scholarship Application Form (Exact PDF UI) -->
                    <div>
                        <h4 style="font-size:15px;font-weight:600;color:#374151;margin:0 0 16px;padding-bottom:12px;border-bottom:2px solid #e5e7eb;">Standard Scholarship Application Form</h4>
                        
                        <!-- Exact PDF Form Layout with Inline Styles -->
                        <div style="background:white;border:2px solid #333;padding:24px;font-family:Arial,sans-serif;font-size:12px;line-height:1.4;">
                            <!-- Header: Logo left, Title center, Picture box right -->
                            <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:20px;padding-bottom:12px;border-bottom:2px solid #333;">
                                <img src="/images/barangay_logo.png" alt="Barangay Logo" style="width:70px;height:70px;object-fit:contain;">
                                <div style="flex:1;text-align:center;padding:0 15px;">
                                    <h2 style="font-size:18px;font-weight:bold;margin:8px 0;text-transform:uppercase;letter-spacing:0.5px;line-height:1.2;">SCHOLARSHIP APPLICATION FORM</h2>
                                </div>
                                <div style="width:90px;height:110px;border:2px solid #333;display:flex;align-items:center;justify-content:center;font-size:10px;color:#666;text-align:center;flex-shrink:0;">
                                    Picture<br>Here
                                </div>
                            </div>

                            <!-- APPLICANT'S PERSONAL INFORMATION -->
                            <div style="margin-bottom:16px;">
                                <p style="font-size:12px;font-weight:bold;text-decoration:underline;margin:0 0 8px;color:#333;">APPLICANT'S PERSONAL INFORMATION:</p>
                                <div style="font-size:11px;line-height:1.8;">
                                    <div style="margin-bottom:6px;">
                                        <span style="font-weight:600;">Last Name:</span> <span style="border-bottom:1px solid #333;display:inline-block;width:130px;margin-right:12px;"></span>
                                        <span style="font-weight:600;">First Name:</span> <span style="border-bottom:1px solid #333;display:inline-block;width:130px;margin-right:12px;"></span>
                                        <span style="font-weight:600;">Middle Name:</span> <span style="border-bottom:1px solid #333;display:inline-block;width:130px;"></span>
                                    </div>
                                    <div style="margin-bottom:6px;">
                                        <span style="font-weight:600;">Date of Birth:</span> <span style="border-bottom:1px solid #333;display:inline-block;width:100px;margin-right:12px;"></span>
                                        <span style="font-weight:600;">Gender:</span> <span style="border-bottom:1px solid #333;display:inline-block;width:80px;margin-right:12px;"></span>
                                        <span style="font-weight:600;">Age:</span> <span style="border-bottom:1px solid #333;display:inline-block;width:50px;margin-right:12px;"></span>
                                        <span style="font-weight:600;">Contact No:</span> <span style="border-bottom:1px solid #333;display:inline-block;width:120px;"></span>
                                    </div>
                                    <div style="margin-bottom:6px;">
                                        <span style="font-weight:600;">Complete Address:</span> <span style="border-bottom:1px solid #333;display:inline-block;width:calc(100% - 130px);"></span>
                                    </div>
                                    <div style="margin-bottom:6px;">
                                        <span style="border-bottom:1px solid #333;display:inline-block;width:100%;"></span>
                                    </div>
                                    <div>
                                        <span style="font-weight:600;">Email Address:</span> <span style="border-bottom:1px solid #333;display:inline-block;width:calc(100% - 110px);"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- ACADEMIC INFORMATION -->
                            <div style="margin-bottom:16px;">
                                <p style="font-size:12px;font-weight:bold;text-decoration:underline;margin:0 0 8px;color:#333;">ACADEMIC INFORMATION:</p>
                                <div style="font-size:11px;line-height:1.8;">
                                    <div style="margin-bottom:6px;">
                                        <span style="font-weight:600;">Name of School:</span> <span style="border-bottom:1px solid #333;display:inline-block;width:calc(100% - 115px);"></span>
                                    </div>
                                    <div style="margin-bottom:6px;">
                                        <span style="font-weight:600;">School Address:</span> <span style="border-bottom:1px solid #333;display:inline-block;width:calc(100% - 120px);"></span>
                                    </div>
                                    <div>
                                        <span style="font-weight:600;">Year/Grade Level:</span> <span style="border-bottom:1px solid #333;display:inline-block;width:160px;margin-right:20px;"></span>
                                        <span style="font-weight:600;">Program/Strand:</span> <span style="border-bottom:1px solid #333;display:inline-block;width:180px;"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- SCHOLARSHIP INFO + SUBMITTED REQUIREMENTS (Two Columns) -->
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                                <!-- Left Column: Scholarship Information -->
                                <div>
                                    <p style="font-size:12px;font-weight:bold;text-decoration:underline;margin:0 0 6px;color:#333;">SCHOLARSHIP INFORMATION:</p>
                                    <p style="font-size:10px;font-weight:600;margin:0 0 6px;">Purpose of Scholarship:</p>
                                    <div style="font-size:10px;line-height:1.6;">
                                        <div style="margin-bottom:3px;"><span style="display:inline-block;width:12px;height:12px;border:1px solid #333;margin-right:6px;vertical-align:middle;"></span> Tuition Fees</div>
                                        <div style="margin-bottom:3px;"><span style="display:inline-block;width:12px;height:12px;border:1px solid #333;margin-right:6px;vertical-align:middle;"></span> Books/Equipments</div>
                                        <div style="margin-bottom:3px;"><span style="display:inline-block;width:12px;height:12px;border:1px solid #333;margin-right:6px;vertical-align:middle;"></span> Living Expenses</div>
                                        <div>
                                            <span style="display:inline-block;width:12px;height:12px;border:1px solid #333;margin-right:6px;vertical-align:middle;"></span> Others (Specify):
                                            <span style="border-bottom:1px solid #333;display:inline-block;width:100px;margin-left:4px;"></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Right Column: Submitted Requirements -->
                                <div>
                                    <p style="font-size:12px;font-weight:bold;text-decoration:underline;margin:0 0 3px;color:#333;">SUBMITTED REQUIREMENTS:</p>
                                    <p style="font-size:8px;font-weight:400;margin:0 0 6px;font-style:italic;color:#666;">Note: To be filled out by SK officials</p>
                                    <div style="font-size:10px;line-height:1.6;">
                                        <div style="margin-bottom:3px;"><span style="display:inline-block;width:12px;height:12px;border:1px solid #333;margin-right:6px;vertical-align:middle;"></span> COR – CERTIFIED TRUE COPY</div>
                                        <div><span style="display:inline-block;width:12px;height:12px;border:1px solid #333;margin-right:6px;vertical-align:middle;"></span> PHOTO COPY OF ID (FRONT AND BACK)</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Signature Section -->
                            <div style="margin-top:24px;text-align:center;">
                                <div style="border-bottom:2px solid #333;width:280px;margin:0 auto 4px;"></div>
                                <p style="font-size:10px;margin:0;font-weight:600;color:#333;">Signature over printed name</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        viewProgramModal.style.display = 'flex';
        
        // Setup close button
        const viewProgramCloseBtn = document.getElementById('viewProgramCloseBtn');
        const viewProgramClose = document.getElementById('viewProgramClose');
        
        if (viewProgramCloseBtn) {
            viewProgramCloseBtn.onclick = () => { viewProgramModal.style.display = 'none'; };
        }
        if (viewProgramClose) {
            viewProgramClose.onclick = () => { viewProgramModal.style.display = 'none'; };
        }
        
        viewProgramModal.onclick = (e) => {
            if (e.target === viewProgramModal) {
                viewProgramModal.style.display = 'none';
            }
        };
    }

    function handleSave() {
        const programName = document.getElementById('programName')?.value?.trim();
        const programCommittee = document.getElementById('programCommittee')?.value?.trim();
        const participationQty = document.getElementById('participationQty')?.value?.trim();
        const programVenue = document.getElementById('programVenue')?.value?.trim();
        const programDescription = document.getElementById('programDescription')?.value?.trim();
        const programTerms = document.getElementById('programTerms')?.value?.trim();
        const startDate = document.getElementById('schedStartDate')?.value?.trim();
        const endDate = document.getElementById('schedEndDate')?.value?.trim();
        const status = document.getElementById('programStatus')?.value || 'auto';

        const startTime = document.getElementById('schedStartTime')?.value || '08:00';
        const endTime = document.getElementById('schedEndTime')?.value || '17:00';

        if (!programName) { showToast('Please enter a program name.', 'error'); return; }
        if (!programCommittee) { showToast('Please select a committee.', 'error'); return; }
        if (!startDate || !endDate) { showToast('Please select start and end dates.', 'error'); return; }
        if (!startTime || !endTime) { showToast('Please select start and end times.', 'error'); return; }

        // Get custom questions from form builder
        let customQuestions = [];
        if (window.SpfbFormBuilder && typeof window.SpfbFormBuilder.getQuestions === 'function') {
            customQuestions = window.SpfbFormBuilder.getQuestions();
        }

        // Get announcement
        const announcement = document.getElementById('spfbAnnouncement')?.value?.trim() || '';

        // Get standard form toggle
        const includeStandardForm = document.getElementById('includeStandardFormToggle')?.checked !== false;

        const forms = loadForms();
        
        if (editingProgramId) {
            // Update existing program
            const index = forms.findIndex(f => f.id === editingProgramId);
            if (index !== -1) {
                const payload = {
                    ...forms[index],
                    programName,
                    committee: programCommittee,
                    participationQty: participationQty || '',
                    venue: programVenue || '',
                    description: programDescription || '',
                    terms: programTerms || '',
                    startDate,
                    endDate,
                    startTime,
                    endTime,
                    status,
                    customQuestions,
                    announcement,
                    includeStandardForm,
                    updatedAt: new Date().toISOString()
                };
                
                forms[index] = payload;
                saveForms(forms);
                
                // Update active program if editing active one
                const activeProgram = getActiveProgram();
                if (activeProgram && activeProgram.id === editingProgramId) {
                    setActiveProgram(payload);
                    loadActiveProgram();
                }
                
                closeModal();
                renderFormsTable();
                showToast('Program updated successfully!', 'success');
            }
        } else {
            // Create new program
            const payload = {
                id: uid(),
                programName,
                programType: 'Equitable Access to Quality Education',
                committee: programCommittee,
                participationQty: participationQty || '',
                venue: programVenue || '',
                description: programDescription || '',
                terms: programTerms || '',
                startDate,
                endDate,
                startTime,
                endTime,
                status,
                customQuestions,
                announcement,
                includeStandardForm,
                createdAt: new Date().toISOString(),
            };

            forms.unshift(payload);
            saveForms(forms);
            
            // Set as active program
            setActiveProgram(payload);
            
            closeModal();
            loadActiveProgram();
            renderFormsTable();
            showToast('Program created and activated successfully!', 'success');
        }
    }

    if (openBtn) openBtn.addEventListener('click', () => openModal());
    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
    if (saveBtn) saveBtn.addEventListener('click', handleSave);
    if (modal) {
        modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });
    }
    if (previewClose && previewModal) {
        previewClose.addEventListener('click', () => { previewModal.style.display = 'none'; });
        previewModal.addEventListener('click', e => { if (e.target === previewModal) previewModal.style.display = 'none'; });
    }
    if (maximizeBtn && modalBox && modal) {
        maximizeBtn.addEventListener('click', e => {
            e.stopPropagation();
            modalBox.classList.toggle('schol-modal-maximized');
            maximizeBtn.textContent = modalBox.classList.contains('schol-modal-maximized') ? '⧉' : '□';
        });
    }

    // Setup filter
    const filterSelect = document.getElementById('programFilter');
    if (filterSelect) {
        filterSelect.addEventListener('change', () => {
            renderFormsTable(filterSelect.value);
        });
    }

    renderFormsTable();
});

// ── Active Program Management ──
function getActiveProgram() {
    try {
        const stored = localStorage.getItem(SAF_ACTIVE_PROGRAM_KEY);
        return stored ? JSON.parse(stored) : null;
    } catch {
        return null;
    }
}

function setActiveProgram(program) {
    localStorage.setItem(SAF_ACTIVE_PROGRAM_KEY, JSON.stringify(program));
}

function clearActiveProgram() {
    localStorage.removeItem(SAF_ACTIVE_PROGRAM_KEY);
}

function loadActiveProgram() {
    const activeProgram = getActiveProgram();
    const activeProgramCard = document.getElementById('activeProgramCard');
    const createBtn = document.getElementById('safOpenFormBtn');
    
    if (!activeProgramCard) return;
    
    if (activeProgram) {
        // Show active program card
        activeProgramCard.style.display = 'block';
        
        // Update card content
        const nameEl = document.getElementById('activeProgramName');
        const infoEl = document.getElementById('activeProgramInfo');
        const statusBadgeEl = document.getElementById('activeProgramStatusBadge');
        
        if (nameEl) nameEl.textContent = activeProgram.programName;
        
        if (infoEl) {
            infoEl.innerHTML = `
                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:8px;">
                    <div><strong>Committee:</strong> ${activeProgram.committee}</div>
                    <div><strong>Type:</strong> ${activeProgram.programType}</div>
                    <div><strong>Start:</strong> ${activeProgram.startDate} ${activeProgram.startTime}</div>
                    <div><strong>End:</strong> ${activeProgram.endDate} ${activeProgram.endTime}</div>
                </div>
            `;
        }
        
        // Determine status
        const now = new Date();
        const startDateTime = new Date(`${activeProgram.startDate}T${activeProgram.startTime}`);
        const endDateTime = new Date(`${activeProgram.endDate}T${activeProgram.endTime}`);
        
        let status = activeProgram.status;
        if (status === 'auto') {
            if (now < startDateTime) {
                status = 'upcoming';
            } else if (now >= startDateTime && now <= endDateTime) {
                status = 'open';
            } else {
                status = 'closed';
            }
        }
        
        if (statusBadgeEl) {
            const statusText = status === 'open' ? 'Open & Ongoing' : 
                              status === 'upcoming' ? 'Upcoming' : 
                              status === 'closed' ? 'Closed' : 'Active';
            statusBadgeEl.textContent = statusText;
        }
        
        // Disable create button
        if (createBtn) {
            createBtn.disabled = true;
            createBtn.style.opacity = '0.5';
            createBtn.style.cursor = 'not-allowed';
            createBtn.setAttribute('data-has-active', 'true');
            createBtn.title = 'Close the active program first to create a new one';
        }
    } else {
        // Hide active program card
        activeProgramCard.style.display = 'none';
        
        // Enable create button
        if (createBtn) {
            createBtn.disabled = false;
            createBtn.style.opacity = '1';
            createBtn.style.cursor = 'pointer';
            createBtn.setAttribute('data-has-active', 'false');
            createBtn.title = '';
        }
    }
}

function setupActiveProgramButtons() {
    const btnViewActive = document.getElementById('btnViewActiveProgram');
    const btnEditActive = document.getElementById('btnEditActiveProgram');
    const btnCloseActive = document.getElementById('btnCloseActiveProgram');
    
    if (btnViewActive) {
        btnViewActive.addEventListener('click', () => {
            const activeProgram = getActiveProgram();
            if (activeProgram) {
                openFormPreview(activeProgram.id);
            }
        });
    }
    
    if (btnEditActive) {
        btnEditActive.addEventListener('click', () => {
            const activeProgram = getActiveProgram();
            if (activeProgram) {
                editProgram(activeProgram.id);
            }
        });
    }
    
    if (btnCloseActive) {
        btnCloseActive.addEventListener('click', () => {
            openCloseProgramModal();
        });
    }
    
    // Setup close program modal
    const closeProgramModal = document.getElementById('closeProgramModal');
    const closeProgramClose = document.getElementById('closeProgramClose');
    const closeProgramCancel = document.getElementById('closeProgramCancel');
    const closeProgramConfirm = document.getElementById('closeProgramConfirm');
    
    if (closeProgramClose) {
        closeProgramClose.addEventListener('click', () => {
            if (closeProgramModal) closeProgramModal.style.display = 'none';
        });
    }
    
    if (closeProgramCancel) {
        closeProgramCancel.addEventListener('click', () => {
            if (closeProgramModal) closeProgramModal.style.display = 'none';
        });
    }
    
    if (closeProgramConfirm) {
        closeProgramConfirm.addEventListener('click', () => {
            closeActiveProgram();
            if (closeProgramModal) closeProgramModal.style.display = 'none';
        });
    }
    
    if (closeProgramModal) {
        closeProgramModal.addEventListener('click', (e) => {
            if (e.target === closeProgramModal) {
                closeProgramModal.style.display = 'none';
            }
        });
    }
}

function openCloseProgramModal() {
    const modal = document.getElementById('closeProgramModal');
    if (modal) {
        modal.style.display = 'flex';
    }
}

function closeActiveProgram() {
    const activeProgram = getActiveProgram();
    if (!activeProgram) return;
    
    // Update program status to closed
    const forms = loadForms();
    const index = forms.findIndex(f => f.id === activeProgram.id);
    if (index !== -1) {
        forms[index].status = 'closed';
        forms[index].closedAt = new Date().toISOString();
        saveForms(forms);
    }
    
    // Clear active program
    clearActiveProgram();
    
    // Reload UI
    loadActiveProgram();
    renderFormsTable();
    
    showToast('Program closed successfully. You can now create a new program.', 'success');
}

function editProgram(programId) {
    const forms = loadForms();
    const program = forms.find(f => f.id === programId);
    
    if (!program) return;
    
    editingProgramId = programId;
    
    // Open modal
    const modal = document.getElementById('scholarProgramModal');
    if (modal) {
        modal.style.display = 'flex';
        
        // Populate form
        document.getElementById('programName').value = program.programName || '';
        document.getElementById('programCommittee').value = program.committee || '';
        document.getElementById('participationQty').value = program.participationQty || '';
        document.getElementById('programVenue').value = program.venue || '';
        document.getElementById('programDescription').value = program.description || '';
        document.getElementById('programTerms').value = program.terms || '';
        document.getElementById('schedStartDate').value = program.startDate || '';
        document.getElementById('schedEndDate').value = program.endDate || '';
        document.getElementById('programStatus').value = program.status || 'auto';
        
        // Set unified time selectors
        document.getElementById('schedStartTime').value = program.startTime || '08:00';
        document.getElementById('schedEndTime').value = program.endTime || '17:00';
        
        // Populate announcement
        if (program.announcement) {
            document.getElementById('spfbAnnouncement').value = program.announcement;
            const announcementCount = document.getElementById('spfbAnnouncementCount');
            if (announcementCount) {
                announcementCount.textContent = String(program.announcement.length);
            }
        }
        
        // Set standard form toggle
        if (program.includeStandardForm !== undefined) {
            document.getElementById('includeStandardFormToggle').checked = program.includeStandardForm;
            const staticPreview = document.getElementById('staticFormPreview');
            if (staticPreview) {
                staticPreview.style.display = program.includeStandardForm ? 'block' : 'none';
            }
        }
        
        // Populate custom questions in form builder
        if (program.customQuestions && program.customQuestions.length > 0) {
            if (window.SpfbFormBuilder && typeof window.SpfbFormBuilder.setQuestions === 'function') {
                window.SpfbFormBuilder.setQuestions(program.customQuestions);
            }
        }
        
        // Update counters
        ['programName', 'programVenue', 'programDescription', 'programTerms'].forEach(id => {
            const el = document.getElementById(id);
            const counterId = id === 'programName' ? 'programNameCount' : 
                            id === 'programVenue' ? 'venueCount' : 
                            id === 'programDescription' ? 'descriptionCount' : 'termsCount';
            const counter = document.getElementById(counterId);
            if (el && counter) {
                counter.textContent = String(el.value.length);
            }
        });
        
        // Update modal title
        const modalTitle = modal.querySelector('.schol-modal-header h3');
        if (modalTitle) {
            modalTitle.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Edit Scholarship Program
            `;
        }
    }
}

function loadForms() {
    try {
        return JSON.parse(localStorage.getItem(SAF_STORAGE_KEY) || '[]');
    } catch {
        return [];
    }
}

function saveForms(forms) {
    localStorage.setItem(SAF_STORAGE_KEY, JSON.stringify(forms));
}

function renderFormsTable(filterValue = 'all') {
    const tableBody = document.getElementById('safFormsTableBody');
    if (!tableBody) return;
    
    const allForms = loadForms();
    const activeProgram = getActiveProgram();
    
    // Filter out active program from table
    const formsToShow = allForms.filter(f => !activeProgram || f.id !== activeProgram.id);
    const forms = filterFormsByDate(formsToShow, filterValue);
    
    // Update program count
    const countEl = document.getElementById('programCount');
    if (countEl) {
        countEl.textContent = forms.length;
    }
    
    if (!forms.length) {
        const message = filterValue === 'all' 
            ? 'No closed programs yet.'
            : 'No programs found for the selected filter.';
        tableBody.innerHTML = `<tr><td colspan="8" class="saf-table-empty">${message}</td></tr>`;
        return;
    }

    tableBody.innerHTML = forms.map(f => {
        const statusClass = f.status === 'open' ? 'schol-pill-approved' : 
                           f.status === 'closed' ? 'schol-pill-rejected' : 
                           'schol-pill-pending';
        const statusText = f.status === 'auto' ? 'Auto' : 
                          f.status === 'open' ? 'Open' : 
                          f.status === 'closed' ? 'Closed' : 'Upcoming';
        
        return `
        <tr>
            <td>${escapeHtml(f.programName)}</td>
            <td>${escapeHtml(f.programType)}</td>
            <td>${escapeHtml(f.committee)}</td>
            <td>${escapeHtml(f.participationQty || 'N/A')}</td>
            <td>${escapeHtml(f.startDate)}</td>
            <td>${escapeHtml(f.endDate)}</td>
            <td><span class="schol-pill ${statusClass}">${statusText}</span></td>
            <td class="col-actions">
                <div class="prog-tbl-actions">
                    <button type="button" class="prog-btn prog-btn-view" data-form-view="${f.id}">View</button>
                    <button type="button" class="prog-btn prog-btn-delete" data-form-delete="${f.id}">Delete</button>
                </div>
            </td>
        </tr>
    `;
    }).join('');

    tableBody.querySelectorAll('[data-form-view]').forEach(btn => {
        btn.addEventListener('click', () => openFormPreview(btn.getAttribute('data-form-view')));
    });
    tableBody.querySelectorAll('[data-form-delete]').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-form-delete');
            if (!confirm('Delete this program?')) return;
            saveForms(loadForms().filter(f => f.id !== id));
            const currentFilter = document.getElementById('programFilter')?.value || 'all';
            renderFormsTable(currentFilter);
            showToast('Program deleted.', 'success');
        });
    });
}

function filterFormsByDate(forms, filterValue) {
    if (filterValue === 'all') return forms;
    
    const now = new Date();
    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    
    return forms.filter(f => {
        if (!f.createdAt) return false;
        const createdDate = new Date(f.createdAt);
        
        switch (filterValue) {
            case 'recent': {
                const sevenDaysAgo = new Date(today);
                sevenDaysAgo.setDate(sevenDaysAgo.getDate() - 7);
                return createdDate >= sevenDaysAgo;
            }
            case 'monthly': {
                return createdDate.getMonth() === now.getMonth() && 
                       createdDate.getFullYear() === now.getFullYear();
            }
            case 'yearly': {
                return createdDate.getFullYear() === now.getFullYear();
            }
            default:
                return true;
        }
    });
}

function escapeHtml(str) {
    return String(str || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function openFormPreview(formId) {
    const f = loadForms().find(x => x.id === formId);
    const viewProgramBody = document.getElementById('viewProgramBody');
    const viewProgramModal = document.getElementById('viewProgramModal');
    
    if (!f || !viewProgramBody || !viewProgramModal) return;

    // Format time for display
    const formatTime = (time24) => {
        if (!time24) return '';
        const [hours, minutes] = time24.split(':');
        const h = parseInt(hours);
        const ampm = h >= 12 ? 'PM' : 'AM';
        const h12 = h % 12 || 12;
        return `${h12}:${minutes} ${ampm}`;
    };

    // Determine status
    const now = new Date();
    const startDateTime = new Date(`${f.startDate}T${f.startTime}`);
    const endDateTime = new Date(`${f.endDate}T${f.endTime}`);
    
    let status = f.status;
    if (status === 'auto') {
        if (now < startDateTime) {
            status = 'upcoming';
        } else if (now >= startDateTime && now <= endDateTime) {
            status = 'open';
        } else {
            status = 'closed';
        }
    }
    
    const statusColors = {
        open: { bg: '#dcfce7', text: '#166534', label: 'Open & Ongoing' },
        closed: { bg: '#fee2e2', text: '#991b1b', label: 'Closed' },
        upcoming: { bg: '#dbeafe', text: '#1e40af', label: 'Upcoming' }
    };
    
    const statusStyle = statusColors[status] || statusColors.closed;

    viewProgramBody.innerHTML = `
        <div style="padding:24px;background:#f9fafb;">
            <!-- Program Header -->
            <div style="background:white;border-radius:12px;padding:24px;margin-bottom:20px;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
                    <h2 style="font-size:24px;font-weight:700;margin:0;color:#111827;">${escapeHtml(f.programName)}</h2>
                    <span style="display:inline-flex;align-items:center;padding:6px 16px;border-radius:999px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;background:${statusStyle.bg};color:${statusStyle.text};">${statusStyle.label}</span>
                </div>
                
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-top:20px;">
                    <div style="padding:12px;background:#f9fafb;border-radius:8px;">
                        <div style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:6px;">Program Type</div>
                        <div style="font-size:14px;font-weight:600;color:#111827;">${escapeHtml(f.programType)}</div>
                    </div>
                    <div style="padding:12px;background:#f9fafb;border-radius:8px;">
                        <div style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:6px;">Committee</div>
                        <div style="font-size:14px;font-weight:600;color:#111827;">${escapeHtml(f.committee)}</div>
                    </div>
                    <div style="padding:12px;background:#f9fafb;border-radius:8px;">
                        <div style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:6px;">Participants</div>
                        <div style="font-size:14px;font-weight:600;color:#111827;">${escapeHtml(f.participationQty || 'N/A')}</div>
                    </div>
                </div>
            </div>

            <!-- Schedule Information -->
            <div style="background:white;border-radius:12px;padding:24px;margin-bottom:20px;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                <h3 style="font-size:16px;font-weight:700;color:#111827;margin:0 0 16px;display:flex;align-items:center;gap:8px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    Schedule Information
                </h3>
                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px;">
                    <div>
                        <div style="font-size:12px;color:#6b7280;margin-bottom:4px;">Start Date & Time</div>
                        <div style="font-size:15px;font-weight:600;color:#111827;">${escapeHtml(f.startDate)} at ${formatTime(f.startTime)}</div>
                    </div>
                    <div>
                        <div style="font-size:12px;color:#6b7280;margin-bottom:4px;">End Date & Time</div>
                        <div style="font-size:15px;font-weight:600;color:#111827;">${escapeHtml(f.endDate)} at ${formatTime(f.endTime)}</div>
                    </div>
                </div>
            </div>

            ${f.venue ? `
            <!-- Venue -->
            <div style="background:white;border-radius:12px;padding:24px;margin-bottom:20px;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                <h3 style="font-size:16px;font-weight:700;color:#111827;margin:0 0 12px;display:flex;align-items:center;gap:8px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    Venue
                </h3>
                <div style="font-size:14px;color:#374151;line-height:1.6;">${escapeHtml(f.venue)}</div>
            </div>
            ` : ''}

            ${f.description ? `
            <!-- Description -->
            <div style="background:white;border-radius:12px;padding:24px;margin-bottom:20px;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                <h3 style="font-size:16px;font-weight:700;color:#111827;margin:0 0 12px;display:flex;align-items:center;gap:8px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    Description
                </h3>
                <div style="font-size:14px;color:#374151;line-height:1.8;">${escapeHtml(f.description)}</div>
            </div>
            ` : ''}

            ${f.terms ? `
            <!-- Terms and Conditions -->
            <div style="background:white;border-radius:12px;padding:24px;margin-bottom:20px;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                <h3 style="font-size:16px;font-weight:700;color:#111827;margin:0 0 12px;display:flex;align-items:center;gap:8px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    Terms and Conditions
                </h3>
                <div style="font-size:14px;color:#374151;line-height:1.8;white-space:pre-wrap;">${escapeHtml(f.terms)}</div>
            </div>
            ` : ''}

            <!-- Application Form (Google Form Style) -->
            <div style="background:white;border-radius:12px;padding:24px;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                <h3 style="font-size:16px;font-weight:700;color:#111827;margin:0 0 16px;display:flex;align-items:center;gap:8px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    Application Form Preview
                </h3>
                
                <!-- Google Form Style Container -->
                <div style="background:#f8f9fa;border-radius:8px;padding:20px;border:1px solid #e5e7eb;">
                    <!-- Form Header -->
                    <div style="background:#673ab7;color:white;padding:20px;border-radius:8px 8px 0 0;margin:-20px -20px 20px;">
                        <h4 style="font-size:28px;font-weight:400;margin:0 0 8px;">${escapeHtml(f.programName)}</h4>
                        <p style="font-size:14px;margin:0;opacity:0.9;">Scholarship Application Form</p>
                    </div>

                    <!-- Standard Scholarship Form -->
                    <div style="background:white;border-radius:8px;padding:24px;margin-bottom:16px;border:1px solid #e5e7eb;">
                        <h5 style="font-size:18px;font-weight:600;color:#202124;margin:0 0 16px;">Standard Scholarship Information</h5>
                        
                        <div style="margin-bottom:20px;">
                            <div style="font-size:14px;color:#202124;font-weight:500;margin-bottom:8px;">Personal Information</div>
                            <div style="font-size:13px;color:#5f6368;line-height:1.6;">
                                • Full Name (Last, First, Middle)<br>
                                • Date of Birth, Gender, Age<br>
                                • Contact Number<br>
                                • Complete Address<br>
                                • Email Address
                            </div>
                        </div>

                        <div style="margin-bottom:20px;">
                            <div style="font-size:14px;color:#202124;font-weight:500;margin-bottom:8px;">Academic Information</div>
                            <div style="font-size:13px;color:#5f6368;line-height:1.6;">
                                • Name of School<br>
                                • School Address<br>
                                • Year/Grade Level<br>
                                • Program/Strand
                            </div>
                        </div>

                        <div>
                            <div style="font-size:14px;color:#202124;font-weight:500;margin-bottom:8px;">Scholarship Purpose</div>
                            <div style="font-size:13px;color:#5f6368;line-height:1.6;">
                                • Tuition Fees<br>
                                • Books/Equipment<br>
                                • Living Expenses<br>
                                • Others (Specify)
                            </div>
                        </div>
                    </div>

                    <!-- Custom Questions (if any) -->
                    ${f.customQuestions && f.customQuestions.length > 0 ? `
                        <div style="background:white;border-radius:8px;padding:24px;border:1px solid #e5e7eb;">
                            <h5 style="font-size:18px;font-weight:600;color:#202124;margin:0 0 16px;">Additional Questions</h5>
                            ${f.customQuestions.map((q, idx) => `
                                <div style="margin-bottom:24px;padding-bottom:24px;border-bottom:1px solid #f0f0f0;">
                                    <div style="font-size:14px;color:#202124;font-weight:500;margin-bottom:8px;">
                                        ${idx + 1}. ${escapeHtml(q.question)}
                                        ${q.required ? '<span style="color:#d93025;">*</span>' : ''}
                                    </div>
                                    <div style="font-size:13px;color:#5f6368;font-style:italic;">
                                        Type: ${escapeHtml(q.type || 'Short Answer')}
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    ` : `
                        <div style="background:#fff3cd;border:1px solid #ffc107;border-radius:8px;padding:16px;text-align:center;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#856404" stroke-width="2" style="margin-bottom:8px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            <div style="font-size:14px;color:#856404;font-weight:500;">No custom questions added</div>
                            <div style="font-size:12px;color:#856404;margin-top:4px;">Only standard scholarship form fields will be collected</div>
                        </div>
                    `}
                </div>
            </div>
        </div>
    `;
    
    viewProgramModal.style.display = 'flex';
    
    // Setup close button
    const viewProgramCloseBtn = document.getElementById('viewProgramCloseBtn');
    const viewProgramClose = document.getElementById('viewProgramClose');
    
    if (viewProgramCloseBtn) {
        viewProgramCloseBtn.onclick = () => { viewProgramModal.style.display = 'none'; };
    }
    if (viewProgramClose) {
        viewProgramClose.onclick = () => { viewProgramModal.style.display = 'none'; };
    }
    
    viewProgramModal.onclick = (e) => {
        if (e.target === viewProgramModal) {
            viewProgramModal.style.display = 'none';
        }
    };
}

function showToast(msg, type) {
    const toastEl = document.getElementById('safToast');
    if (!toastEl) return;
    toastEl.textContent = msg;
    toastEl.style.display = 'flex';
    toastEl.style.background = type === 'error' ? '#ef4444' : '#22c55e';
    clearTimeout(showToast._t);
    showToast._t = setTimeout(() => { toastEl.style.display = 'none'; }, 2800);
}

