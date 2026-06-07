/**
 * Sports Landing Page JavaScript
 */
(function () {
    'use strict';

    const startApplicationBtn = document.getElementById('startApplicationBtn');
    const applicationStatusContent = document.getElementById('applicationStatusContent');
    const previousApplicationsTable = document.getElementById('previousApplicationsTable');

    // ── Load Application Status ───────────────────────────────────────────────
    function loadApplicationStatus() {
        if (!applicationStatusContent) return;

        // In production, fetch from API based on logged-in user and scholarship program
        // For demo, check localStorage for existing application
        const scholarshipRequests = JSON.parse(localStorage.getItem('sports_requests') || '[]');
        const currentProgramId = 'sports-development-2026'; // This would come from URL or API
        
        const existingApplication = scholarshipRequests.find(req => req.program_id === currentProgramId);

        if (existingApplication) {
            // User has already applied
            const statusClass = getStatusClass(existingApplication.status);
            const statusHtml = `
                <div class="sl-status-item sl-status-submitted">
                    <div class="sl-status-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                    <div class="sl-status-text">
                        <h3 class="sl-status-heading">Application Submitted</h3>
                        <p class="sl-status-desc">Your application is currently under review.</p>
                        <div style="margin-top: 12px;">
                            <span class="sl-info-label">Status:</span>
                            <span class="sl-status-badge ${statusClass}">${existingApplication.status}</span>
                        </div>
                        <div style="margin-top: 8px;">
                            <span class="sl-info-label">Submitted Date:</span>
                            <span class="sl-info-value">${existingApplication.submitted_at}</span>
                        </div>
                    </div>
                </div>
            `;
            applicationStatusContent.innerHTML = statusHtml;
            
            // Disable start button if already applied
            if (startApplicationBtn) {
                startApplicationBtn.disabled = true;
                startApplicationBtn.textContent = 'Already Applied';
                startApplicationBtn.style.opacity = '0.5';
                startApplicationBtn.style.cursor = 'not-allowed';
            }
        }
        // If no existing application, show default "Not Yet Applied" state
    }

    function getStatusClass(status) {
        switch (status.toLowerCase()) {
            case 'pending':
            case 'pending review':
                return 'sl-status-pending';
            case 'approved':
                return 'sl-status-approved';
            case 'rejected':
                return 'sl-status-rejected';
            default:
                return 'sl-status-pending';
        }
    }

    // ── Load Previous Applications ─────────────────────────────────────────────
    function loadPreviousApplications() {
        if (!previousApplicationsTable) return;

        // Sample data for demonstration
        const sampleApplications = [
            {
                id: 'app-001',
                program_name: 'Sports Development League 2025',
                submitted_at: 'May 10, 2025',
                status: 'Approved'
            },
            {
                id: 'app-002',
                program_name: 'Inter-Barangay Volleyball Cup 2024',
                submitted_at: 'June 15, 2024',
                status: 'Rejected'
            },
            {
                id: 'app-003',
                program_name: 'Youth Basketball Tournament 2023',
                submitted_at: 'August 20, 2023',
                status: 'Approved'
            }
        ];

        // In production, fetch from API based on logged-in user
        // For demo, use localStorage data if available, otherwise use sample data
        const scholarshipRequests = JSON.parse(localStorage.getItem('sports_requests') || '[]');
        
        // Filter out current program application (shown in status section)
        const currentProgramId = 'sports-development-2026';
        let previousApplications = scholarshipRequests.filter(req => req.program_id !== currentProgramId);

        // If no localStorage data, use sample data
        if (previousApplications.length === 0) {
            previousApplications = sampleApplications;
        }

        const rowsHtml = previousApplications.map(app => {
            const statusClass = getStatusClass(app.status);
            return `
                <tr>
                    <td>${app.program_name || 'Sports Program'}</td>
                    <td>${app.submitted_at}</td>
                    <td><span class="sl-status-badge ${statusClass}">${app.status}</span></td>
                    <td>
                        <button class="sl-btn-view" data-id="${app.id}">
                            View
                        </button>
                    </td>
                </tr>
            `;
        }).join('');

        previousApplicationsTable.innerHTML = rowsHtml;

        // Add click handlers for view buttons
        document.querySelectorAll('.sl-btn-view').forEach(btn => {
            btn.addEventListener('click', function() {
                const appId = this.getAttribute('data-id');
                viewApplication(appId);
            });
        });
    }

    // ── View Application Details ────────────────────────────────────────────────
    function viewApplication(appId) {
        // Find the application from sample data or localStorage
        const sampleApplications = [
            {
                id: 'app-001',
                program_name: 'Sports Development League 2025',
                submitted_at: 'May 10, 2025',
                status: 'Approved',
                kk_profile: {
                    full_name: 'Juan Dela Cruz',
                    birthday: 'January 15, 2003',
                    age: '23',
                    sex: 'Male',
                    civil_status: 'Single',
                    contact_number: '09123456789',
                    home_address: '123 Main St, Barangay Santa Cruz',
                    current_school: 'University of the Philippines Los Baños',
                    year_level: '3rd Year',
                    course_strand: 'Bachelor of Science in Computer Science',
                    barangay: 'Santa Cruz',
                    city_municipality: 'Santa Cruz',
                    province: 'Laguna',
                    region: 'CALABARZON (Region IV-A)'
                },
                answers: {
                    school_attended: 'University of the Philippines Los Baños',
                    year_level: '3rd Year',
                    gpa: '1.75',
                    reason_for_applying: 'I am applying for this scholarship to help with my tuition fees and educational expenses as my family is facing financial difficulties.'
                },
                uploads: {
                    cor_file: 'cor_juan_delacruz.pdf',
                    grades_file: 'grades_juan_delacruz.pdf'
                }
            },
            {
                id: 'app-002',
                program_name: 'Inter-Barangay Volleyball Cup 2024',
                submitted_at: 'June 15, 2024',
                status: 'Rejected',
                kk_profile: {
                    full_name: 'Maria Santos',
                    birthday: 'March 22, 2004',
                    age: '22',
                    sex: 'Female',
                    civil_status: 'Single',
                    contact_number: '09987654321',
                    home_address: '456 Rizal Ave, Barangay Santa Cruz',
                    current_school: 'Laguna State Polytechnic University',
                    year_level: '2nd Year',
                    course_strand: 'Bachelor of Science in Agriculture',
                    barangay: 'Santa Cruz',
                    city_municipality: 'Santa Cruz',
                    province: 'Laguna',
                    region: 'CALABARZON (Region IV-A)'
                },
                answers: {
                    school_attended: 'Laguna State Polytechnic University',
                    year_level: '2nd Year',
                    gpa: '2.25',
                    reason_for_applying: 'I need financial assistance to continue my studies as my parents are unemployed.'
                },
                uploads: {
                    cor_file: 'cor_maria_santos.pdf',
                    grades_file: 'grades_maria_santos.pdf'
                }
            },
            {
                id: 'app-003',
                program_name: 'Youth Basketball Tournament 2023',
                submitted_at: 'August 20, 2023',
                status: 'Approved',
                kk_profile: {
                    full_name: 'Jose Reyes',
                    birthday: 'July 8, 2002',
                    age: '24',
                    sex: 'Male',
                    civil_status: 'Single',
                    contact_number: '09182736455',
                    home_address: '789 Mabini St, Barangay Santa Cruz',
                    current_school: 'Polytechnic University of the Philippines',
                    year_level: '4th Year',
                    course_strand: 'Bachelor of Science in Accountancy',
                    barangay: 'Santa Cruz',
                    city_municipality: 'Santa Cruz',
                    province: 'Laguna',
                    region: 'CALABARZON (Region IV-A)'
                },
                answers: {
                    school_attended: 'Polytechnic University of the Philippines',
                    year_level: '4th Year',
                    gpa: '1.50',
                    reason_for_applying: 'I am applying to recognize my academic achievements and to receive financial support for my final year.'
                },
                uploads: {
                    cor_file: 'cor_jose_reyes.pdf',
                    grades_file: 'grades_jose_reyes.pdf'
                }
            }
        ];

        const scholarshipRequests = JSON.parse(localStorage.getItem('sports_requests') || '[]');
        let application = scholarshipRequests.find(req => req.id == appId);
        
        if (!application) {
            application = sampleApplications.find(app => app.id == appId);
        }
        
        if (application) {
            // Create modal HTML with detailed information
            const kkProfile = application.kk_profile || {};
            const answers = application.answers || {};
            const uploads = application.uploads || {};
            
            const modalHtml = `
                <div class="sl-modal-overlay" id="slModalOverlay">
                    <div class="sl-modal sl-modal-large" id="slModalContent">
                        <div class="sl-modal-header sl-modal-header-blue">
                            <h3 class="sl-modal-title">Application Details</h3>
                            <div class="sl-modal-buttons">
                                <button class="sl-modal-icon-btn" id="slModalFullscreen" aria-label="Toggle fullscreen" title="Fullscreen">
                                    <svg class="sl-icon-expand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                    </svg>
                                    <svg class="sl-icon-collapse" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                                        <rect x="4" y="4" width="16" height="16" rx="2" ry="2"/>
                                        <line x1="9" y1="9" x2="15" y2="9"/>
                                        <line x1="9" y1="15" x2="15" y2="15"/>
                                        <line x1="9" y1="12" x2="15" y2="12"/>
                                    </svg>
                                </button>
                                <button class="sl-modal-close" id="slModalClose">&times;</button>
                            </div>
                        </div>
                        <div class="sl-modal-body sl-modal-scrollable">
                            <div class="sl-modal-section">
                                <h4 class="sl-modal-section-title">Program Information</h4>
                                <div class="sl-modal-field">
                                    <span class="sl-modal-label">Program Name:</span>
                                    <span class="sl-modal-value">${application.program_name}</span>
                                </div>
                                <div class="sl-modal-field">
                                    <span class="sl-modal-label">Application Date:</span>
                                    <span class="sl-modal-value">${application.submitted_at}</span>
                                </div>
                                <div class="sl-modal-field">
                                    <span class="sl-modal-label">Status:</span>
                                    <span class="sl-modal-value sl-modal-status ${getStatusClass(application.status)}">${application.status}</span>
                                </div>
                            </div>

                            <div class="sl-modal-section">
                                <h4 class="sl-modal-section-title">KK Profile Information</h4>
                                <div class="sl-modal-grid">
                                    <div class="sl-modal-field">
                                        <span class="sl-modal-label">Full Name:</span>
                                        <span class="sl-modal-value">${kkProfile.full_name || 'N/A'}</span>
                                    </div>
                                    <div class="sl-modal-field">
                                        <span class="sl-modal-label">Birthday:</span>
                                        <span class="sl-modal-value">${kkProfile.birthday || 'N/A'}</span>
                                    </div>
                                    <div class="sl-modal-field">
                                        <span class="sl-modal-label">Age:</span>
                                        <span class="sl-modal-value">${kkProfile.age || 'N/A'}</span>
                                    </div>
                                    <div class="sl-modal-field">
                                        <span class="sl-modal-label">Sex:</span>
                                        <span class="sl-modal-value">${kkProfile.sex || 'N/A'}</span>
                                    </div>
                                    <div class="sl-modal-field">
                                        <span class="sl-modal-label">Civil Status:</span>
                                        <span class="sl-modal-value">${kkProfile.civil_status || 'N/A'}</span>
                                    </div>
                                    <div class="sl-modal-field">
                                        <span class="sl-modal-label">Contact Number:</span>
                                        <span class="sl-modal-value">${kkProfile.contact_number || 'N/A'}</span>
                                    </div>
                                    <div class="sl-modal-field">
                                        <span class="sl-modal-label">Home Address:</span>
                                        <span class="sl-modal-value">${kkProfile.home_address || 'N/A'}</span>
                                    </div>
                                    <div class="sl-modal-field">
                                        <span class="sl-modal-label">Current School:</span>
                                        <span class="sl-modal-value">${kkProfile.current_school || 'N/A'}</span>
                                    </div>
                                    <div class="sl-modal-field">
                                        <span class="sl-modal-label">Year Level:</span>
                                        <span class="sl-modal-value">${kkProfile.year_level || 'N/A'}</span>
                                    </div>
                                    <div class="sl-modal-field">
                                        <span class="sl-modal-label">Course / Strand:</span>
                                        <span class="sl-modal-value">${kkProfile.course_strand || 'N/A'}</span>
                                    </div>
                                    <div class="sl-modal-field">
                                        <span class="sl-modal-label">Barangay:</span>
                                        <span class="sl-modal-value">${kkProfile.barangay || 'N/A'}</span>
                                    </div>
                                    <div class="sl-modal-field">
                                        <span class="sl-modal-label">City/Municipality:</span>
                                        <span class="sl-modal-value">${kkProfile.city_municipality || 'N/A'}</span>
                                    </div>
                                    <div class="sl-modal-field">
                                        <span class="sl-modal-label">Province:</span>
                                        <span class="sl-modal-value">${kkProfile.province || 'N/A'}</span>
                                    </div>
                                    <div class="sl-modal-field">
                                        <span class="sl-modal-label">Region:</span>
                                        <span class="sl-modal-value">${kkProfile.region || 'N/A'}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="sl-modal-section">
                                <h4 class="sl-modal-section-title">Application Answers</h4>
                                <div class="sl-modal-field">
                                    <span class="sl-modal-label">School Attended:</span>
                                    <span class="sl-modal-value">${answers.school_attended || 'N/A'}</span>
                                </div>
                                <div class="sl-modal-field">
                                    <span class="sl-modal-label">Year Level:</span>
                                    <span class="sl-modal-value">${answers.year_level || 'N/A'}</span>
                                </div>
                                <div class="sl-modal-field">
                                    <span class="sl-modal-label">GPA:</span>
                                    <span class="sl-modal-value">${answers.gpa || 'N/A'}</span>
                                </div>
                                <div class="sl-modal-field">
                                    <span class="sl-modal-label">Reason for Applying:</span>
                                    <span class="sl-modal-value">${answers.reason_for_applying || 'N/A'}</span>
                                </div>
                            </div>

                            <div class="sl-modal-section sl-modal-section-red">
                                <h4 class="sl-modal-section-title">Uploaded Documents</h4>
                                <div class="sl-modal-field">
                                    <span class="sl-modal-label">Certificate of Registration (COR):</span>
                                    ${uploads.cor_file ? `<a href="#" class="sl-modal-file sl-modal-download" data-file="${uploads.cor_file}" download>${uploads.cor_file}</a>` : '<span class="sl-modal-value">Not uploaded</span>'}
                                </div>
                                <div class="sl-modal-field">
                                    <span class="sl-modal-label">Copy of Grades:</span>
                                    ${uploads.grades_file ? `<a href="#" class="sl-modal-file sl-modal-download" data-file="${uploads.grades_file}" download>${uploads.grades_file}</a>` : '<span class="sl-modal-value">Not uploaded</span>'}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Add modal to body
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            
            // Add event listeners
            const overlay = document.getElementById('slModalOverlay');
            const closeBtn = document.getElementById('slModalClose');
            const fullscreenBtn = document.getElementById('slModalFullscreen');
            const modalContent = document.getElementById('slModalContent');
            
            const closeModal = () => {
                // Exit fullscreen if active
                if (document.fullscreenElement) {
                    document.exitFullscreen();
                }
                overlay.remove();
            };
            
            closeBtn.addEventListener('click', closeModal);
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) closeModal();
            });

            // Fullscreen functionality using Fullscreen API
            if (fullscreenBtn && modalContent) {
                fullscreenBtn.addEventListener('click', () => {
                    if (!document.fullscreenElement) {
                        modalContent.requestFullscreen().then(() => {
                            fullscreenBtn.setAttribute('aria-label', 'Exit fullscreen');
                            fullscreenBtn.setAttribute('title', 'Exit fullscreen');
                        }).catch(err => {
                            console.error('Fullscreen error:', err);
                        });
                    } else {
                        document.exitFullscreen().then(() => {
                            fullscreenBtn.setAttribute('aria-label', 'Toggle fullscreen');
                            fullscreenBtn.setAttribute('title', 'Fullscreen');
                        }).catch(err => {
                            console.error('Exit fullscreen error:', err);
                        });
                    }
                });

                // Listen for fullscreen changes to update icon
                document.addEventListener('fullscreenchange', () => {
                    const expandIcon = fullscreenBtn.querySelector('.sl-icon-expand');
                    const collapseIcon = fullscreenBtn.querySelector('.sl-icon-collapse');
                    
                    if (document.fullscreenElement) {
                        expandIcon.style.display = 'none';
                        collapseIcon.style.display = 'block';
                    } else {
                        expandIcon.style.display = 'block';
                        collapseIcon.style.display = 'none';
                    }
                });
            }

            // Download functionality for PDF files
            document.querySelectorAll('.sl-modal-download').forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    const fileName = link.getAttribute('data-file');
                    // Create a sample PDF download
                    const blob = new Blob(['Sample PDF content for ' + fileName], { type: 'application/pdf' });
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = fileName;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(url);
                });
            });
        }
    }

    // ── Start Application Handler ───────────────────────────────────────────────
    function handleStartApplication() {
        // Navigate to the scholarship application form
        window.location.href = '/sports/apply/form';
    }

    // ── Initialize ─────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        loadApplicationStatus();
        loadPreviousApplications();

        if (startApplicationBtn) {
            startApplicationBtn.addEventListener('click', handleStartApplication);
        }

        // Programs Drawer functionality
        const drawerBtn = document.getElementById('programsDrawerBtn');
        const drawerSidebar = document.getElementById('programsDrawerSidebar');
        const backdrop = document.getElementById('programsDrawerBackdrop');

        function openDrawer() {
            drawerSidebar?.classList.add('drawer-open');
            backdrop?.classList.add('drawer-open');
        }

        function closeDrawer() {
            drawerSidebar?.classList.remove('drawer-open');
            backdrop?.classList.remove('drawer-open');
        }

        if (drawerBtn) {
            drawerBtn.addEventListener('click', openDrawer);
        }

        if (backdrop) {
            backdrop.addEventListener('click', closeDrawer);
        }

        // Close drawer when a program category is clicked
        document.querySelectorAll('#programsDrawerSidebar .program-category').forEach(cat => {
            cat.addEventListener('click', () => {
                closeDrawer();
            });
        });
    });

})();
