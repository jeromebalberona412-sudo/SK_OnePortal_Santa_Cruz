<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Barangay ABYIP - SK Federations</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ url('/modules/dashboard/css/dashboard.css') }}">
    @vite('app/Modules/Barangay_ABYIP/Assets/css/barangay_abyip.css')
    @vite('app/Modules/Barangay_ABYIP/Assets/js/barangay_abyip.js')
</head>
<body>
    <script>
        (function() {
            window.history.pushState(null, '', window.location.href);
            window.onpopstate = function() { window.history.pushState(null, '', window.location.href); };
        })();
    </script>

    @php
        $avatar = 'https://ui-avatars.com/api/?name=' . urlencode((string) ($user->name ?? 'User')) . '&background=213F99&color=fff&size=120';
        $formattedRole = $user->role ? ucwords(str_replace('_', ' ', (string) $user->role)) : 'SK Official';
    @endphp

    <nav class="navbar">
        <div class="navbar-left">
            <button class="menu-toggle" onclick="toggleSidebar()" aria-label="Toggle sidebar">
                <i class="fas fa-bars toggle-icon-expand"></i>
                <i class="fas fa-ellipsis-v toggle-icon-collapse"></i>
            </button>
            <div class="navbar-brand">
                <img src="{{ url('/modules/authentication/images/Sk_Fed_logo.png') }}" alt="SK Fed Logo" class="brand-logo">
                <span class="brand-name">SK Federations</span>
            </div>
        </div>
        <div class="navbar-search">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="abyipSearchInput" placeholder="Search..." onkeyup="performAbyipSearch()" aria-label="Search ABYIP submissions">
        </div>
        <div class="navbar-right">
            <button class="notif-btn" onclick="toggleNotifPopover(event)" aria-label="Notifications">
                <i class="fas fa-bell"></i>
                <span class="notif-badge"></span>
            </button>
            <div class="profile-dropdown-wrapper">
                <button class="profile-btn" onclick="toggleProfileDropdown(event)" aria-label="Profile menu">
                    <img src="{{ $avatar }}" alt="Profile" class="nav-avatar">
                    <span class="nav-name">{{ $user->name ?? 'User' }}</span>
                    <i class="fas fa-chevron-down nav-chevron"></i>
                </button>
                <div class="profile-dropdown" id="profileDropdown">
                    <div class="profile-dropdown-header">
                        <div class="dd-name">{{ $user->name ?? 'User' }}</div>
                        <div class="dd-email">{{ $user->email ?? '' }}</div>
                    </div>
                    <a href="{{ route('profile') }}" class="dd-item">
                        <i class="fas fa-user"></i> Profile
                    </a>
                    <a href="{{ route('password.request') }}" class="dd-item">
                        <i class="fas fa-lock"></i> Change Password
                    </a>
                    <div class="dd-divider"></div>
                    <button class="dd-item danger" onclick="showLogoutModal()">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <div class="sidebar-overlay"></div>

    <aside class="sidebar">
        <a href="{{ route('profile') }}" class="sidebar-profile">
            <img src="{{ $avatar }}" alt="Profile" class="sidebar-avatar">
            <div class="sidebar-user-info">
                <div class="s-name">{{ $user->name ?? 'User' }}</div>
                <div class="s-role">{{ $formattedRole }}</div>
            </div>
        </a>
        <nav class="sidebar-nav">
            <div class="menu-section-label">Main</div>
            <a href="{{ route('dashboard') }}" class="menu-item" data-tooltip="Dashboard">
                <i class="fas fa-home"></i><span>Dashboard</span>
            </a>
            <div class="menu-section-label">Modules</div>
            <a href="{{ route('community-feed') }}" class="menu-item" data-tooltip="SK Community Feed">
                <i class="fas fa-rss"></i><span>SK Community Feed</span>
            </a>
            <a href="{{ route('barangay-monitoring') }}" class="menu-item" data-tooltip="Barangay Monitoring">
                <i class="fas fa-map-marker-alt"></i><span>Barangay Monitoring</span>
            </a>
            <a href="{{ route('reports') }}" class="menu-item" data-tooltip="Reports">
                <i class="fas fa-chart-bar"></i><span>Reports</span>
            </a>
            <a href="{{ route('barangay.abyip') }}" class="menu-item active" data-tooltip="Barangay ABYIP">
                <i class="fas fa-file-invoice-dollar"></i><span>Barangay ABYIP</span>
            </a>
            <a href="{{ route('kabataan-monitoring') }}" class="menu-item" data-tooltip="Kabataan Monitoring">
                <i class="fas fa-users"></i><span>Kabataan Monitoring</span>
            </a>
            <a href="javascript:void(0);" class="menu-item" onclick="document.getElementById('archiveSubmenu').style.display = document.getElementById('archiveSubmenu').style.display === 'block' ? 'none' : 'block'; document.getElementById('archiveChevron').style.transform = document.getElementById('archiveSubmenu').style.display === 'block' ? 'rotate(180deg)' : 'rotate(0deg)'; return false;" data-tooltip="Archive">
                <i class="fas fa-archive"></i><span>Archive</span>
                <i class="fas fa-chevron-down" id="archiveChevron" style="margin-left:auto;font-size:12px;transition:transform 0.3s ease;"></i>
            </a>
            <div id="archiveSubmenu" style="display:none;padding-left:20px;border-left:2px solid #e2e8f0;margin-left:10px;">
                <a href="{{ route('archive') }}" class="menu-item" style="font-size:13px;">
                    <i class="fas fa-trash"></i><span>Deleted Reports</span>
                </a>
                <a href="{{ route('archive') }}" class="menu-item" style="font-size:13px;">
                    <i class="fas fa-box"></i><span>Archived Reports</span>
                </a>
            </div>
            <div class="menu-divider"></div>
        </nav>
    </aside>

    <main class="main-content">
        <div class="abyip-container">
            {{-- Header --}}
            <div class="abyip-header">
                <div>
                    <h2 class="abyip-title">Barangay ABYIP Submissions</h2>
                    <p class="abyip-subtitle">
                        <i class="fas fa-file-invoice-dollar"></i>
                        Annual Barangay Youth Investment Plan submissions from all barangays
                    </p>
                </div>
                <a href="{{ route('reports') }}" class="back-button">
                    <i class="fas fa-arrow-left"></i> Back to Reports
                </a>
            </div>

            {{-- Summary Card --}}
            <div class="summary-card">
                <div class="summary-item">
                    <div class="summary-icon" style="background:#e0e7ff;">
                        <i class="fas fa-file-alt" style="color:#213F99;"></i>
                    </div>
                    <div>
                        <p class="summary-label">Total Submissions</p>
                        <p class="summary-value">{{ count($abyipSubmissions) }}</p>
                    </div>
                </div>
                <div class="summary-item">
                    <div class="summary-icon" style="background:#dcfce7;">
                        <i class="fas fa-calendar-check" style="color:#15803d;"></i>
                    </div>
                    <div>
                        <p class="summary-label">Latest Submission</p>
                        <p class="summary-value">{{ $abyipSubmissions[0]['date_submitted'] ?? 'N/A' }}</p>
                    </div>
                </div>
                <div class="summary-item">
                    <div class="summary-icon" style="background:#fef3c7;">
                        <i class="fas fa-clock" style="color:#b45309;"></i>
                    </div>
                    <div>
                        <p class="summary-label">Pending Review</p>
                        <p class="summary-value">0</p>
                    </div>
                </div>
            </div>

            {{-- Filters --}}
            <div class="filters-container">
                <select id="barangayFilter" onchange="filterAbyipSubmissions()" class="filter-select">
                    <option value="all">All Barangays</option>
                    <option value="Alipit">Alipit</option>
                    <option value="Bagumbayan">Bagumbayan</option>
                    <option value="Bubukal">Bubukal</option>
                    <option value="Labuin">Labuin</option>
                    <option value="Pagsawitan">Pagsawitan</option>
                    <option value="Poblacion I">Poblacion I</option>
                    <option value="Poblacion II">Poblacion II</option>
                    <option value="Poblacion III">Poblacion III</option>
                    <option value="Poblacion IV">Poblacion IV</option>
                    <option value="Santisima Cruz">Santisima Cruz</option>
                </select>
                <select id="dateFilter" onchange="filterAbyipSubmissions()" class="filter-select">
                    <option value="all">All Time</option>
                    <option value="today">Today</option>
                    <option value="week">This Week</option>
                    <option value="month">This Month</option>
                </select>
            </div>

            {{-- ABYIP Table --}}
            <div class="table-container">
                <table class="abyip-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Barangay</th>
                            <th>Date Submitted</th>
                            <th>Submitted By</th>
                            <th>Submitted Time</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="abyipTableBody">
                        @foreach($abyipSubmissions as $submission)
                        <tr class="abyip-row" 
                            data-id="{{ $submission['id'] }}" 
                            data-barangay="{{ $submission['barangay'] }}" 
                            data-date="{{ $submission['date_submitted'] }}"
                            data-submitted-by="{{ $submission['submitted_by'] }}"
                            data-title="{{ $submission['title'] }}"
                            data-submitted-time="{{ $submission['submitted_time'] }}"
                            data-status="Pending">
                            <td>{{ $submission['title'] }}</td>
                            <td>{{ $submission['barangay'] }}</td>
                            <td>{{ $submission['date_submitted'] }}</td>
                            <td>{{ $submission['submitted_by'] }}</td>
                            <td>{{ $submission['submitted_time'] }}</td>
                            <td><span class="status-badge status-pending">Pending</span></td>
                            <td>
                                <button class="view-btn" onclick="openViewModal(this)" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="pagination-container">
                <div class="pagination-info">
                    Showing <span id="abyipStart">1</span> to <span id="abyipEnd">10</span> of <span id="abyipTotal">{{ count($abyipSubmissions) }}</span> submissions
                </div>
                <div class="pagination-buttons">
                    <button onclick="prevAbyipPage()" class="pagination-button">
                        <i class="fas fa-chevron-left"></i> Previous
                    </button>
                    <button onclick="nextAbyipPage()" class="pagination-button">
                        Next <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </main>

    {{-- View Details Modal --}}
    <div id="viewModal" class="view-modal">
        <div class="view-modal-content">
            <div class="view-modal-header">
                <h3 class="view-modal-title">
                    <i class="fas fa-file-invoice-dollar"></i>
                    ABYIP Submission Details
                </h3>
                <div class="view-modal-controls">
                    <button class="view-modal-control-btn" onclick="toggleFullscreen()" title="Toggle Fullscreen" id="fullscreenBtn">
                        <i class="fas fa-expand"></i>
                    </button>
                    <button class="view-modal-control-btn" onclick="closeViewModal()" title="Close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="view-modal-body">
                <div class="detail-row">
                    <div class="detail-label">
                        <i class="fas fa-map-marker-alt"></i>
                        Barangay
                    </div>
                    <div class="detail-value" id="modalBarangay">-</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">
                        <i class="fas fa-calendar"></i>
                        Date Submitted
                    </div>
                    <div class="detail-value" id="modalDateSubmitted">-</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">
                        <i class="fas fa-user"></i>
                        Submitted By
                    </div>
                    <div class="detail-value" id="modalSubmittedBy">-</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">
                        <i class="fas fa-heading"></i>
                        Title
                    </div>
                    <div class="detail-value" id="modalTitle">-</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">
                        <i class="fas fa-clock"></i>
                        Submitted Time
                    </div>
                    <div class="detail-value" id="modalSubmittedTime">-</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">
                        <i class="fas fa-info-circle"></i>
                        Status
                    </div>
                    <div class="detail-value">
                        <span class="status-badge status-pending" id="modalStatus">Pending</span>
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">
                        <i class="fas fa-file-pdf"></i>
                        ABYIP Files
                    </div>
                    <div class="detail-value">
                        <a href="#" onclick="downloadABYIPFile(event)" class="file-download-link">
                            <i class="fas fa-file-pdf"></i>
                            <span>abyip.pdf</span>
                            <i class="fas fa-download"></i>
                        </a>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="modal-actions" id="modalActions">
                    <button class="action-btn approve-btn" onclick="showApproveForm()">
                        <i class="fas fa-check-circle"></i> Approve
                    </button>
                    <button class="action-btn reject-btn" onclick="showRejectForm()">
                        <i class="fas fa-times-circle"></i> Reject
                    </button>
                </div>

                {{-- Approve Form --}}
                <div class="approval-form" id="approveForm" style="display:none;">
                    <h4 class="form-title">
                        <i class="fas fa-check-circle"></i> Approve Submission
                    </h4>
                    <div class="form-group">
                        <label for="approvalNotes">Approval Notes <span class="required">*</span> <span class="char-limit">(500 characters max)</span></label>
                        <textarea id="approvalNotes" class="form-control" rows="4" maxlength="500" placeholder="Enter approval notes or comments..." required></textarea>
                        <div class="char-counter">
                            <span id="approvalNotesCount">0</span>/500 characters
                        </div>
                        <span class="error-message" id="approvalNotesError"></span>
                    </div>
                    <div class="form-actions">
                        <button class="form-btn cancel-btn" onclick="hideApproveForm()">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button class="form-btn submit-btn" onclick="submitApproval()">
                            <i class="fas fa-check"></i> Submit Approval
                        </button>
                    </div>
                </div>

                {{-- Reject Form --}}
                <div class="rejection-form" id="rejectForm" style="display:none;">
                    <h4 class="form-title">
                        <i class="fas fa-times-circle"></i> Reject Submission
                    </h4>
                    <div class="form-group">
                        <label>Rejection Reason <span class="required">*</span></label>
                        <div class="checkbox-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="rejectReason" value="Incomplete Information" onchange="handleRejectReasonChange()">
                                <span>Incomplete Information</span>
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" name="rejectReason" value="Invalid Documents" onchange="handleRejectReasonChange()">
                                <span>Invalid Documents</span>
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" name="rejectReason" value="Does Not Meet Requirements" onchange="handleRejectReasonChange()">
                                <span>Does Not Meet Requirements</span>
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" name="rejectReason" value="Budget Constraints" onchange="handleRejectReasonChange()">
                                <span>Budget Constraints</span>
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" id="otherReasonCheckbox" name="rejectReason" value="Other" onchange="handleRejectReasonChange()">
                                <span>Other</span>
                            </label>
                        </div>
                        <span class="error-message" id="rejectReasonError"></span>
                    </div>
                    <div class="form-group" id="otherReasonGroup" style="display:none;">
                        <label for="otherReason">Please Specify <span class="required">*</span> <span class="char-limit">(500 characters max)</span></label>
                        <textarea id="otherReason" class="form-control" rows="3" maxlength="500" placeholder="Please specify the reason..."></textarea>
                        <div class="char-counter">
                            <span id="otherReasonCount">0</span>/500 characters
                        </div>
                        <span class="error-message" id="otherReasonError"></span>
                    </div>
                    <div class="form-actions">
                        <button class="form-btn cancel-btn" onclick="hideRejectForm()">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button class="form-btn submit-btn reject-submit-btn" onclick="submitRejection()">
                            <i class="fas fa-ban"></i> Submit Rejection
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Toast Notification --}}
    <div id="toast" class="toast">
        <div class="toast-content">
            <i class="toast-icon fas fa-check-circle"></i>
            <span class="toast-message"></span>
        </div>
    </div>

    @include('dashboard::logout-modal')

    <script src="{{ url('/shared/js/loading.js') }}"></script>
    <script src="{{ url('/modules/dashboard/js/dashboard.js') }}"></script>
    <script>
        window.logoutRoute = "{{ route('logout') }}";
        window.loginRoute  = "{{ route('login') }}";
    </script>
</body>
</html>
