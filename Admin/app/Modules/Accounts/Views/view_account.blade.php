<!-- View Account Modal -->
<div id="viewAccountModal" class="modal-overlay" style="display: none;">
    <div class="modal-content view-modal-container" id="viewAccountModalBox">
        <div class="modal-header modal-header-deep-blue">
            <h3 class="modal-title">Account Details</h3>
            <div class="modal-controls">
                <button type="button" class="modal-win-btn modal-win-btn-maximize" id="viewToggleBtn"
                        onclick="toggleFullscreenViewModal()" title="Maximize">
                    <span id="viewResizeIcon" class="modal-win-icon">&#9633;</span>
                </button>
                <button type="button" class="modal-win-btn modal-win-btn-close" onclick="closeViewModal()" title="Close">
                    &times;
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

