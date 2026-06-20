
<div id="deleteAccountModal" class="modal-overlay delete-modal-overlay" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="deleteModalTitle">
    <div class="modal-content delete-confirm-modal">
        <div class="delete-confirm-header">
            <h3 id="deleteModalTitle">Delete Account</h3>
        </div>
        <div class="delete-confirm-body">
            <p class="delete-confirm-message" id="deleteModalMessage">
                Are you sure you want to permanently delete this account?
            </p>
            <p class="delete-confirm-desc">This action cannot be undone.</p>

            <label class="delete-confirm-label" for="deleteConfirmationInput">Confirmation Required</label>
            <input type="text" id="deleteConfirmationInput" class="delete-confirm-input"
                   placeholder="Type Delete to confirm" autocomplete="off">

            <p class="delete-confirm-hint delete-confirm-hint-error" id="deleteConfirmHintError" style="display:none;">
                Please type &quot;Delete&quot; exactly to continue.
            </p>
            <p class="delete-confirm-hint delete-confirm-hint-success" id="deleteConfirmHintSuccess" style="display:none;">
                ✓ Confirmation text matched.
            </p>
        </div>
        <div class="delete-confirm-footer">
            <button type="button" class="btn-cancel-delete" id="deleteModalCancelBtn">Cancel</button>
            <button type="button" class="btn-confirm-delete is-disabled" id="deleteModalConfirmBtn" disabled>Confirm Delete</button>
        </div>
    </div>
</div>
<?php /**PATH C:\Users\Administrator\Documents\SK_OnePortal_Santa_Cruz\SK_Federations\app\Modules\Accounts\Providers/../Views/delete_account_modal.blade.php ENDPATH**/ ?>