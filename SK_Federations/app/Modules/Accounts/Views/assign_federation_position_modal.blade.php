<div id="assignFederationPositionModal" class="modal-overlay" style="display:none;">
    <div class="modal-content modal-light assign-fed-position-modal">
        <div class="modal-header modal-header-blue-grad">
            <h3 class="modal-title">Assign Federation Position</h3>
            <button type="button" class="modal-win-btn modal-win-btn-close" onclick="closeAssignFederationPositionModal()" title="Close">&times;</button>
        </div>
        <div class="modal-body modal-body-light">
            <form id="assignFederationPositionForm" class="account-modal-form" data-account-id="" novalidate>
                @csrf
                <p class="assign-fed-summary">
                    Set the federation role for <strong id="assignFedDisplayName">—</strong>
                    from <strong id="assignFedBarangayName">—</strong>.
                </p>
                <p class="form-hint-light">SK Chairperson accounts are added from Manage SK Officials. Each federation position can only be assigned to one member.</p>
                <div class="form-group-light">
                    <label for="assign_federation_position" class="form-label-light">Federation Position</label>
                    <select id="assign_federation_position" name="federation_position" class="form-input-light">
                        <option value="">Not assigned yet</option>
                        @foreach(\App\Modules\Accounts\Models\OfficialProfile::federationPositionOptions() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <span class="form-error-light"></span>
                </div>
            </form>
        </div>
        <div class="modal-footer account-modal-footer">
            <button type="button" class="btn-cancel-light" onclick="closeAssignFederationPositionModal()">Cancel</button>
            <button type="submit" form="assignFederationPositionForm" class="btn-submit-light">Save Position</button>
        </div>
    </div>
</div>
