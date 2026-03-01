<!-- View Account Modal -->
<style>
.btn-view-modern {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: var(--success-green, #16a34a);
    color: var(--white, #ffffff);
    border: none;
    border-radius: var(--border-radius, 8px);
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    white-space: nowrap;
    min-width: 70px;
    height: 36px;
}

.btn-view-modern:hover {
    background: var(--success-green-hover, #15803d);
    transform: translateY(-1px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.btn-edit-modern {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: var(--primary-blue, #2563eb);
    color: var(--white, #ffffff);
    border: none;
    border-radius: var(--border-radius, 8px);
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    white-space: nowrap;
    min-width: 70px;
    height: 36px;
}

.btn-edit-modern:hover {
    background: var(--primary-blue-hover, #1d4ed8);
    transform: translateY(-1px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.action-buttons-container {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    justify-content: flex-start;
    flex-wrap: nowrap;
}

.view-modal-container {
    max-width: 800px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.view-details-grid {
    display: grid;
    gap: 1.5rem;
}

.detail-section {
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 1.5rem;
    background: #f9fafb;
}

.section-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #374151;
    margin: 0 0 1rem 0;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #e5e7eb;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.detail-item label {
    font-size: 13px;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.detail-item span {
    font-size: 14px;
    color: #374151;
    font-weight: 500;
}

@media (max-width: 768px) {
    .view-modal-container {
        width: 95%;
        max-height: 95vh;
    }
    
    .detail-grid {
        grid-template-columns: 1fr;
    }
    
    .modal-body {
        padding: 1rem;
    }
    
    .action-buttons-container {
        flex-direction: column;
        gap: 0.25rem;
        align-items: stretch;
    }
    
    .btn-view-modern,
    .btn-edit-modern {
        width: 100%;
        min-width: auto;
    }
}

@media (max-width: 480px) {
    .action-buttons-container {
        flex-direction: row;
        flex-wrap: wrap;
        gap: 0.25rem;
    }
    
    .btn-view-modern,
    .btn-edit-modern {
        flex: 1;
        min-width: 60px;
        font-size: 13px;
        padding: 0.4rem 0.8rem;
    }
}
</style>

<div id="viewAccountModal" class="modal-overlay" style="display: none;">
    <div class="modal-content view-modal-container">
        <div class="modal-header">
            <h3 class="modal-title">Account Details</h3>
            <button type="button" class="modal-close-btn" onclick="closeViewModal()">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
            </button>
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
