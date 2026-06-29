{{-- Terms and Conditions modal --}}
<div class="auth-legal-modal" id="termsLegalModal" hidden aria-hidden="true" role="dialog" aria-labelledby="termsLegalModalTitle">
    <div class="auth-legal-modal-backdrop" data-close-legal-modal="termsLegalModal"></div>
    <div class="auth-legal-modal-dialog" role="document">
        <header class="auth-legal-modal-header">
            <h2 class="auth-legal-modal-title" id="termsLegalModalTitle">Terms and Conditions</h2>
            <button type="button" class="auth-legal-modal-close" data-close-legal-modal="termsLegalModal" aria-label="Close">&times;</button>
        </header>
        <div class="auth-legal-modal-body">
            @include('authentication::partials.terms-and-conditions')
        </div>
        <footer class="auth-legal-modal-footer">
            <label class="auth-legal-modal-ack">
                <input type="checkbox" id="termsModalAck" data-legal-ack="terms">
                <span>I have read, understood, and agreed to the Terms and Conditions.</span>
            </label>
            <button type="button" class="auth-legal-modal-btn" data-close-legal-modal="termsLegalModal">Close</button>
        </footer>
    </div>
</div>

{{-- Privacy Policy modal --}}
<div class="auth-legal-modal" id="privacyLegalModal" hidden aria-hidden="true" role="dialog" aria-labelledby="privacyLegalModalTitle">
    <div class="auth-legal-modal-backdrop" data-close-legal-modal="privacyLegalModal"></div>
    <div class="auth-legal-modal-dialog" role="document">
        <header class="auth-legal-modal-header">
            <h2 class="auth-legal-modal-title" id="privacyLegalModalTitle">Privacy Policy</h2>
            <button type="button" class="auth-legal-modal-close" data-close-legal-modal="privacyLegalModal" aria-label="Close">&times;</button>
        </header>
        <div class="auth-legal-modal-body">
            @include('authentication::partials.privacy-policy')
        </div>
        <footer class="auth-legal-modal-footer">
            <label class="auth-legal-modal-ack">
                <input type="checkbox" id="privacyModalAck" data-legal-ack="privacy">
                <span>I have read and understood the Privacy Policy.</span>
            </label>
            <button type="button" class="auth-legal-modal-btn" data-close-legal-modal="privacyLegalModal">Close</button>
        </footer>
    </div>
</div>
