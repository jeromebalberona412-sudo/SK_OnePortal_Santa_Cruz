{{-- Yearly KK Profiling update modal (dashboard) --}}
<div class="kkpu-overlay" id="kkProfilingUpdateModal" aria-hidden="true" role="dialog" aria-labelledby="kkpuModalTitle">
    <div class="kkpu-modal" id="kkpuModalPanel">
        <div class="kkpu-modal-header">
            <h2 id="kkpuModalTitle">Update Your KK Profiling</h2>
            <div class="kkpu-modal-actions">
                <button type="button" class="kkpu-icon-btn" id="kkpuFullscreenBtn" aria-label="Toggle fullscreen" title="Fullscreen">
                    <svg class="kkpu-icon-expand" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M3 3a1 1 0 011-1h4a1 1 0 110 2H5.414l4.293 4.293a1 1 0 01-1.414-1.414L4 4.586V6a1 1 0 11-2 0V3zm14 0a1 1 0 00-1-1h-4a1 1 0 100 2h2.586l-4.293 4.293a1 1 0 101.414 1.414L16 5.414V8a1 1 0 102 0V3zM3 17a1 1 0 001 1h4a1 1 0 100-2H5.414l4.293-4.293a1 1 0 00-1.414-1.414L4 15.414V14a1 1 0 10-2 0v3zm14 0a1 1 0 01-1 1h-4a1 1 0 110-2h2.586l-4.293-4.293a1 1 0 111.414-1.414L16 14.586V13a1 1 0 112 0v4z"/>
                    </svg>
                    <svg class="kkpu-icon-collapse" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M5 5a1 1 0 011-1h2a1 1 0 110 2H7.414L6 7.414V8a1 1 0 11-2 0V5zm10 0a1 1 0 00-1-1h-2a1 1 0 100 2h1.586L14 7.414V8a1 1 0 102 0V5zM5 15a1 1 0 001 1h2a1 1 0 100-2H7.414L6 12.586V12a1 1 0 10-2 0v3zm10 0a1 1 0 01-1 1h-2a1 1 0 110-2h1.586L14 12.586V12a1 1 0 112 0v3z"/>
                    </svg>
                </button>
                <button type="button" class="kkpu-icon-btn kkpu-icon-btn-close" id="kkpuCloseBtn" aria-label="Close">
                    <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="kkpu-instruction">
            <p class="kkpu-instruction-text">
                Please update your KK Profiling information. KK Profiling records must be updated yearly to keep your information accurate and active.
            </p>
        </div>

        <div class="kkpu-modal-body">
            <div class="kkp-paper kkpu-paper">
                <form method="POST" action="{{ route('kkprofiling.update') }}" id="kkProfilingUpdateForm" onsubmit="return handleKkProfilingUpdateSubmit(event)">
                    @csrf
                    @method('PUT')

                    @include('kkprofiling::partials.kk-profiling-form-fields', [
                        'barangay' => $kkUpdateBarangay ?? 'Santa Cruz',
                        'respondentNumber' => $kkRespondentNumber ?? '',
                        'respondentDisplay' => $kkRespondentDisplay ?? '01',
                        'submitLabel' => 'Update KK Profiling',
                    ])
                </form>
            </div>
        </div>
    </div>
</div>

@include('kkprofiling::partials.kk-profiling-signature-modals')
