<!-- View Account Modal -->
<div id="viewAccountModal" class="modal-overlay" style="display: none;">
    <div class="modal-content view-modal-container">
        <div class="modal-header modal-header-deep-blue">
            <h3 class="modal-title">Account Details</h3>
            <div class="modal-controls">
                <button type="button" class="modal-restore-btn" id="viewRestoreBtn" onclick="toggleRestoreViewModal()" title="Restore Down" style="display:none;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="7" width="11" height="11" rx="1.5"/>
                        <path d="M7 7V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-2"/>
                    </svg>
                </button>
                <button type="button" class="modal-fullscreen-btn" onclick="toggleFullscreenViewModal()" title="Maximize">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path>
                    </svg>
                </button>
                <button type="button" class="modal-close-btn" onclick="closeViewModal()">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 6L6 18M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
        
        <div class="modal-body">
            <div class="view-details-grid">
                <!-- Personal Information Section -->
                <div class="detail-section">
                    <h4 class="section-title">Personal Information</h4>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>Full Name</label>
                            <span id="viewFullName">-</span>
                        </div>
                        <div class="detail-item">
                            <label>Email Address</label>
                            <span id="viewEmail">-</span>
                        </div>
                        <div class="detail-item">
                            <label>Date of Birth</label>
                            <span id="viewDateOfBirth">-</span>
                        </div>
                        <div class="detail-item">
                            <label>Age</label>
                            <span id="viewAge">-</span>
                        </div>
                        <div class="detail-item">
                            <label>Contact Number</label>
                            <span id="viewContactNumber">-</span>
                        </div>
                        <div class="detail-item">
                            <label>Email Verification</label>
                            <span id="viewEmailVerification">-</span>
                        </div>
                    </div>
                </div>

                <!-- Location Information Section -->
                <div class="detail-section">
                    <h4 class="section-title">Location Information</h4>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>Barangay</label>
                            <span id="viewBarangay">-</span>
                        </div>
                        <div class="detail-item">
                            <label>Municipality</label>
                            <span id="viewMunicipality">-</span>
                        </div>
                        <div class="detail-item" id="viewProvinceContainer" style="display: none;">
                            <label>Province</label>
                            <span id="viewProvince">-</span>
                        </div>
                        <div class="detail-item" id="viewRegionContainer" style="display: none;">
                            <label>Region</label>
                            <span id="viewRegion">-</span>
                        </div>
                    </div>
                </div>

                <!-- Term Information Section -->
                <div class="detail-section">
                    <h4 class="section-title">Term Information</h4>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>Position</label>
                            <span id="viewPosition">-</span>
                        </div>
                        <div class="detail-item">
                            <label>Term Start</label>
                            <span id="viewTermStart">-</span>
                        </div>
                        <div class="detail-item">
                            <label>Term End</label>
                            <span id="viewTermEnd">-</span>
                        </div>
                        <div class="detail-item">
                            <label>Account Status</label>
                            <span id="viewAccountStatus" class="status-badge">-</span>
                        </div>
                        <div class="detail-item">
                            <label>Term Status</label>
                            <span id="viewTermStatus" class="status-badge">-</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

