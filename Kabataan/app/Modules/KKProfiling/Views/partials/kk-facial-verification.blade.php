{{-- Identity Verification — MediaPipe liveness (multi-frame) --}}
<div class="kkp-identity-section" id="kkpIdentitySection">
    <div class="kkp-identity-heading">Identity Verification <span class="kkp-required">*</span></div>
    <p class="kkp-identity-hint">
        5-step live check: Human Check → Turn Right → Turn Left → Blink → Smile &amp; photo.
    </p>

    <div class="kkp-identity-actions">
        <button type="button" class="kkp-verify-identity-btn" id="kkpVerifyIdentityBtn">
            <span class="kkp-verify-identity-btn-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 14a4 4 0 1 0-4-4" stroke-linecap="round"/>
                    <path d="M4 20c2-4 5-6 8-6s6 2 8 6" stroke-linecap="round"/>
                    <circle cx="12" cy="10" r="3"/>
                    <path d="M17 8V5M19 6h-4" stroke-linecap="round"/>
                </svg>
            </span>
            Verify Identity
        </button>
    </div>

    <div class="kkp-verification-status" id="kkpVerificationStatus" hidden></div>

    <div class="kkp-verification-preview-card" id="kkpVerificationPreview" data-state="pending">
        <div class="kkp-verification-placeholder" id="kkpVerificationPlaceholder">
            <span class="kkp-verification-placeholder-icon" aria-hidden="true">
                <svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5">
                    <rect x="8" y="12" width="48" height="40" rx="6"/>
                    <circle cx="32" cy="30" r="10"/>
                    <path d="M18 48c3-6 8-9 14-9s11 3 14 9" stroke-linecap="round"/>
                    <circle cx="44" cy="20" r="2" fill="currentColor" stroke="none"/>
                </svg>
            </span>
            <span class="kkp-verification-placeholder-title">No photo yet</span>
            <span class="kkp-verification-placeholder-text">Complete verification to capture your photo here</span>
        </div>
        <img
            id="kkpVerificationPreviewImg"
            class="kkp-verification-preview-img"
            alt="Verified selfie"
            hidden
        >
        <button type="button" class="kkp-retake-verification-btn" id="kkpRetakeVerificationBtn" hidden>
            Retake Verification
        </button>
    </div>

    <input type="hidden" name="facial_verification_completed" id="kkpFacialVerificationCompleted" value="">
    <input type="hidden" name="verified_selfie" id="kkpVerifiedSelfie" value="">
</div>

<div class="kkp-fv-modal" id="kkpFacialVerificationModal" hidden aria-hidden="true" role="dialog" aria-labelledby="kkpFvTitle" aria-modal="true">
    <div class="kkp-fv-overlay" data-fv-close></div>
    <div class="kkp-fv-dialog">
        <div class="kkp-fv-header">
            <h2 class="kkp-fv-title" id="kkpFvTitle">Face Liveness Verification</h2>
            <button type="button" class="kkp-fv-close" aria-label="Close verification modal">&times;</button>
        </div>

        <div class="kkp-fv-body">
            <div class="kkp-fv-step-card">
                <div class="kkp-fv-step-header">
                    <span class="kkp-fv-step-label">
                        Step <span id="kkpFvStepNum">1</span> of <span id="kkpFvStepTotal">5</span>
                    </span>
                    <div class="kkp-fv-step-dots" id="kkpFvStepDots" aria-hidden="true">
                        <span class="kkp-fv-dot is-active" title="Human Check"></span>
                        <span class="kkp-fv-dot" title="Turn Right"></span>
                        <span class="kkp-fv-dot" title="Turn Left"></span>
                        <span class="kkp-fv-dot" title="Blink"></span>
                        <span class="kkp-fv-dot" title="Smile"></span>
                    </div>
                </div>

                <div class="kkp-fv-instruction-panel" id="kkpFvInstructionPanel">
                    <div class="kkp-fv-step-cue" id="kkpFvStepCue">
                        <span class="kkp-fv-step-cue-icon" id="kkpFvStepCueIcon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c2-4 5-6 8-6s6 2 8 6" stroke-linecap="round"/></svg>
                        </span>
                        <span class="kkp-fv-step-cue-label" id="kkpFvStepCueLabel">Human Check</span>
                    </div>
                    <p class="kkp-fv-instruction" id="kkpFvInstruction">Show your live face to the camera</p>
                    <p class="kkp-fv-instruction-helper" id="kkpFvInstructionHelper">Look straight with eyes open — only one real person in frame.</p>
                </div>

                <div class="kkp-fv-video-wrap" id="kkpFvVideoWrap">
                    <video id="kkpFvVideo" class="kkp-fv-video" autoplay muted playsinline></video>
                    <div class="kkp-fv-face-guide" aria-hidden="true"></div>
                    <div class="kkp-fv-face-ring" id="kkpFvFaceRing" data-state="idle"></div>

                    <div class="kkp-fv-video-cue" id="kkpFvVideoCue" hidden aria-hidden="true" data-cue="">
                        <span class="kkp-fv-video-cue-item" data-cue="arrow-right">
                            <span class="kkp-fv-video-cue-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                            <span class="kkp-fv-video-cue-text">Turn right</span>
                        </span>
                        <span class="kkp-fv-video-cue-item" data-cue="arrow-left">
                            <span class="kkp-fv-video-cue-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M11 6l-6 6 6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                            <span class="kkp-fv-video-cue-text">Turn left</span>
                        </span>
                        <span class="kkp-fv-video-cue-item" data-cue="blink">
                            <span class="kkp-fv-video-cue-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z" stroke-linecap="round"/><circle cx="12" cy="12" r="2" fill="currentColor" stroke="none"/></svg>
                            </span>
                            <span class="kkp-fv-video-cue-text">Blink now</span>
                        </span>
                        <span class="kkp-fv-video-cue-item" data-cue="smile">
                            <span class="kkp-fv-video-cue-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><circle cx="9" cy="10" r="1" fill="currentColor" stroke="none"/><circle cx="15" cy="10" r="1" fill="currentColor" stroke="none"/><path d="M8 15c1.5 2 6.5 2 8 0" stroke-linecap="round"/></svg>
                            </span>
                            <span class="kkp-fv-video-cue-text">Keep smiling</span>
                        </span>
                    </div>

                    <div class="kkp-fv-smile-timer" id="kkpFvSmileTimer" hidden>
                        <span class="kkp-fv-smile-timer-ring">
                            <span class="kkp-fv-smile-timer-count" id="kkpFvSmileTimerCount">5</span>
                        </span>
                        <span class="kkp-fv-smile-timer-label">Hold smile</span>
                    </div>

                    <div class="kkp-fv-loading" id="kkpFvLoading" hidden>Starting camera…</div>
                </div>

                <div class="kkp-fv-preview-wrap" id="kkpFvPreviewWrap" hidden>
                    <img id="kkpFvPreview" alt="Captured verified selfie">
                </div>

                <div class="kkp-fv-badges" id="kkpFvBadges">
                    <span class="kkp-fv-badge kkp-fv-badge-neutral" id="kkpFvBadgeFace">
                        <span class="kkp-fv-badge-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/></svg></span>
                        Waiting for human face
                    </span>
                    <span class="kkp-fv-badge kkp-fv-badge-neutral" id="kkpFvBadgeIdentity" hidden>
                        <span class="kkp-fv-badge-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/></svg></span>
                        Identity check
                    </span>
                    <span class="kkp-fv-badge kkp-fv-badge-neutral" id="kkpFvBadgeStatus" hidden>
                        <span class="kkp-fv-badge-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                        Ready
                    </span>
                </div>

                <div class="kkp-fv-frame-progress" id="kkpFvFrameProgress" hidden>
                    <div class="kkp-fv-frame-progress-bar" id="kkpFvFrameProgressBar"></div>
                </div>
            </div>

            <div class="kkp-fv-success" id="kkpFvSuccess" hidden>Identity Verification Successful</div>
            <div class="kkp-fv-error" id="kkpFvError" hidden></div>
            <div class="kkp-fv-help" id="kkpFvHelp" hidden></div>

            <div class="kkp-fv-actions">
                <button type="button" class="kkp-fv-btn kkp-fv-btn-secondary" id="kkpFvRetakeBtn" hidden>Retake Verification</button>
                <button type="button" class="kkp-fv-btn kkp-fv-btn-primary" id="kkpFvConfirmBtn" hidden>Use This Photo</button>
            </div>
        </div>
    </div>
</div>
