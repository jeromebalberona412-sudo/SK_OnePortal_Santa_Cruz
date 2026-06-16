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
        
        <div class="modal-body" id="viewAccountBody">
            <!-- Content populated dynamically via JavaScript -->
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary-modern" onclick="closeViewModal()" aria-label="Close">&times;</button>
        </div>
    </div>
</div>

