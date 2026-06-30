{{-- Yearly KK Profiling update modal (dashboard) --}}
<div class="kkpu-overlay kabataan-modal-backdrop" id="kkProfilingUpdateModal" aria-hidden="true" role="dialog" aria-labelledby="kkpuModalTitle">
    <div class="kkpu-modal kabataan-modal-box" id="kkpuModalPanel">
        <div class="modal-header">
            <h2 class="modal-title" id="kkpuModalTitle">
                Update Your KK Profiling
                @if (!empty($kkProfilingTargetYear))
                    ({{ $kkProfilingTargetYear }})
                @endif
            </h2>
            <div class="modal-window-controls">
                <button type="button" class="modal-toggle-btn" id="kkpuFullscreenBtn" aria-label="Maximize">□</button>
                @if (empty($kkProfilingUpdateRequired))
                    <button type="button" class="modal-close" id="kkpuCloseBtn" aria-label="Close">&times;</button>
                @endif
            </div>
        </div>

        <div class="kkpu-instruction">
            <p class="kkpu-instruction-text">
                Please update your KK Profiling information. KK Profiling records must be updated yearly to keep your information accurate and active.
            </p>
        </div>

        <div class="kkpu-modal-body kabataan-modal-body" id="kkpuModalBody">
            <div class="kkp-paper kkpu-paper" id="kkpuFormSection">
                <form method="POST" action="{{ route('kkprofiling.update') }}" id="kkProfilingUpdateForm" data-email-locked="1">
                    @csrf
                    @method('PUT')

                    @include('kkprofiling::partials.kk-profiling-form-fields', [
                        'barangay' => $kkUpdateBarangay ?? 'Santa Cruz',
                        'respondentNumber' => $kkRespondentNumber ?? '',
                        'respondentDisplay' => $kkRespondentDisplay ?? '01',
                        'submitLabel' => 'Update KK Profiling',
                        'barangayLogoUrl' => $kkBarangayLogoUrl ?? null,
                        'barangayZones' => $kkBarangayZones ?? collect(),
                        'selectedPurokZone' => $kkSelectedPurokZone ?? '',
                        'selectedFacebookProfileUrl' => $kkSelectedFacebookProfileUrl ?? '',
                        'emailReadonly' => true,
                    ])
                </form>
            </div>
        </div>
    </div>
</div>

@include('kkprofiling::partials.kk-profiling-signature-modals')
