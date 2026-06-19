<!-- View Account Modal -->
<div id="viewAccountModal" class="modal-overlay" style="display: none;">
    <div class="modal-content modal-large modal-light" id="viewAccountModalBox">
        <div class="modal-header modal-header-blue-grad">
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

        <div class="modal-body modal-body-light account-modal-scroll" id="viewAccountBody">
            <!-- Content populated dynamically via JavaScript -->
        </div>

        <div class="modal-footer account-modal-footer">
            <button type="button" class="btn-cancel-light" onclick="closeViewModal()">Close</button>
        </div>
    </div>
</div>
