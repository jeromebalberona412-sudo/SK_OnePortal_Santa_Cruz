<!-- View Account Modal -->
<style>
.btn-view-modern {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: #2e8b57;
    color: #ffffff;
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
    background: #267a4a;
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

/* Modal Controls CSS */
.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    background: #f9fafb;
}

/* View modal - green header */
#viewAccountModal .modal-header.modal-header-green {
    background: #2e8b57;
    border-bottom-color: rgba(255, 255, 255, 0.25);
}
#viewAccountModal .modal-header.modal-header-green .modal-title,
#viewAccountModal .modal-header.modal-header-green .modal-close-btn,
#viewAccountModal .modal-header.modal-header-green .modal-fullscreen-btn {
    color: #ffffff !important;
}
#viewAccountModal .modal-header.modal-header-green .modal-close-btn:hover,
#viewAccountModal .modal-header.modal-header-green .modal-fullscreen-btn:hover {
    background: rgba(255, 255, 255, 0.2);
    color: #ffffff !important;
}

.modal-controls {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.modal-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #374151;
    margin: 0;
}

.modal-close-btn,
.modal-fullscreen-btn {
    background: none;
    border: none;
    padding: 0.5rem;
    border-radius: 8px;
    cursor: pointer;
    color: #6b7280;
    transition: all 0.2s ease;
}

.modal-close-btn:hover,
.modal-fullscreen-btn:hover {
    background-color: #e5e7eb;
    color: #374151;
}

.modal-fullscreen-btn svg {
    width: 20px;
    height: 20px;
}

/* Fullscreen modal styles */
.modal-overlay.modal-fullscreen {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    z-index: 9999;
}

.modal-overlay.modal-fullscreen .modal-content {
    width: 100vw;
    height: 100vh;
    max-width: none;
    max-height: none;
    border-radius: 0;
    margin: 0;
}

.modal-overlay.modal-fullscreen .modal-body {
    height: calc(100vh - 120px);
    max-height: none;
    overflow-y: auto;
}

/* Minimized modal styles */
.modal-overlay.modal-minimized {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 9999;
    background: rgba(0, 0, 0, 0.1);
    -webkit-backdrop-filter: blur(4px);
    backdrop-filter: blur(4px);
}

.modal-overlay.modal-minimized .modal-content {
    width: 300px;
    max-height: 60px;
    overflow: hidden;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.modal-overlay.modal-minimized .modal-body,
.modal-overlay.modal-minimized .view-details-grid,
.modal-overlay.modal-minimized .detail-section {
    display: none;
}

.modal-overlay.modal-minimized .modal-header {
    border-bottom: none;
    cursor: pointer;
}

.modal-overlay.modal-minimized .modal-controls .modal-fullscreen-btn svg {
    width: 16px;
    height: 16px;
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
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.section-title {
    font-size: 1.2rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 1rem 0;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #e5e7eb;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 0.75rem;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    padding: 0;
    background: transparent;
    border-radius: 0;
    border: none;
    transition: none;
}

.detail-item:hover {
    background: transparent;
    box-shadow: none;
    transform: none;
}

.detail-item label {
    font-size: 13px;
    font-weight: 700;
    color: #000000;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.25rem;
}

.detail-item span {
    font-size: 15px;
    color: #374151;
    font-weight: 400;
    line-height: 1.5;
    padding: 0.25rem 0;
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
        <div class="modal-header modal-header-green">
            <h3 class="modal-title">Account Details</h3>
            <div class="modal-controls">
                <button type="button" class="modal-fullscreen-btn" onclick="toggleFullscreenViewModal()" title="Fullscreen">
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

@vite(['app/Modules/Accounts/assets/js/view_account.js'])
