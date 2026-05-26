@php
    $scholarshipRequirements = [
        [
            'id' => 'voter_parent',
            'name' => "Voter's ID / Certificate of Parent/Guardian",
            'status' => 'verified',
            'file' => 'voters_id_parent_guardian.pdf',
            'remarks' => '',
        ],
        [
            'id' => 'voter_scholar',
            'name' => "Voter's ID / Certificate of Scholar",
            'status' => 'verified',
            'file' => 'voters_id_scholar.pdf',
            'remarks' => '',
        ],
        [
            'id' => 'registration_form',
            'name' => 'Certified True Copy of Registration Form 1st Semester, AY 2025-2026',
            'status' => 'pending',
            'file' => '',
            'remarks' => '',
        ],
        [
            'id' => 'grades',
            'name' => 'Certified True Copy of Grades 2nd Semester, AY 2024-2025',
            'status' => 'rejected',
            'file' => 'grades_2024_2025.pdf',
            'remarks' => 'Document is blurry and unreadable. Please upload a clearer scanned PDF.',
        ],
        [
            'id' => 'barangay_cert',
            'name' => 'Barangay Certificate of Residency / Indigency',
            'status' => 'verified',
            'file' => 'barangay_certificate.pdf',
            'remarks' => '',
        ],
        [
            'id' => 'valid_id_front',
            'name' => 'Valid ID (Front)',
            'status' => 'verified',
            'file' => 'valid_id_front.pdf',
            'remarks' => '',
        ],
        [
            'id' => 'valid_id_back',
            'name' => 'Valid ID (Back)',
            'status' => 'pending',
            'file' => '',
            'remarks' => '',
        ],
        [
            'id' => 'application_form',
            'name' => 'Scanned Copy of Accomplished Application Form',
            'status' => 'rejected',
            'file' => 'application_form_scan.pdf',
            'remarks' => 'Missing signature on page 2. Please re-upload the complete signed form.',
        ],
    ];
@endphp

<section class="sch-app-panel" id="panel-requirements" data-panel="requirements">
    <div class="sch-app-card">
        <h2 class="sch-app-card-title">Uploading of Requirements</h2>

        <div class="sch-req-table-wrap">
            <table class="sch-req-table">
                <thead>
                    <tr>
                        <th>Requirement</th>
                        <th>Attachment</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($scholarshipRequirements as $req)
                    <tr class="sch-req-row" data-req-id="{{ $req['id'] }}" data-status="{{ $req['status'] }}" data-file="{{ $req['file'] }}" data-remarks="{{ $req['remarks'] }}">
                        <td class="sch-req-name-cell">
                            <span class="sch-req-name">{{ $req['name'] }}</span>
                            <span class="sch-req-badge sch-req-badge-{{ $req['status'] }}" data-status-badge>
                                @if ($req['status'] === 'verified')
                                    ✔ Verified
                                @elseif ($req['status'] === 'pending')
                                    ⏳ Pending
                                @else
                                    ✖ Rejected
                                @endif
                            </span>
                            @if ($req['status'] === 'rejected' && $req['remarks'])
                            <button type="button" class="sch-req-remarks-link" data-show-remarks title="View remarks">{{ $req['remarks'] }}</button>
                            @endif
                        </td>
                        <td class="sch-req-attach-cell">
                            @if ($req['file'])
                            <button type="button" class="sch-req-btn sch-req-btn-view" data-view-attachment data-req-id="{{ $req['id'] }}">
                                View Attachment
                            </button>
                            @else
                            <span class="sch-req-no-file">No file uploaded</span>
                            @endif
                        </td>
                        <td class="sch-req-action-cell">
                            @if ($req['status'] === 'rejected')
                            <button type="button" class="sch-req-btn sch-req-btn-resubmit" data-resubmit data-req-id="{{ $req['id'] }}">
                                <svg viewBox="0 0 20 20" fill="currentColor" width="14" height="14"><path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/></svg>
                                Resubmit
                            </button>
                            @else
                            <button type="button" class="sch-req-btn sch-req-btn-upload" data-upload-trigger data-req-id="{{ $req['id'] }}">
                                <svg viewBox="0 0 20 20" fill="currentColor" width="14" height="14"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/></svg>
                                Upload
                            </button>
                            @endif
                            <input type="file" class="sch-req-file-input" id="file-{{ $req['id'] }}" accept="application/pdf,.pdf" hidden data-req-input="{{ $req['id'] }}">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <p class="sch-req-note"><em>Note: If a requirement is marked as Invalid/Rejected, click the remarks to view details. Please ensure the file you upload is in <strong>PDF format</strong> and does not exceed <strong>5MB</strong> in size.</em></p>

        <label class="sch-app-certify">
            <input type="checkbox" id="formAgreement" name="formAgreement" required>
            <span class="sch-app-certify-box"></span>
            <span>I certify that all information and documents provided are true and correct.</span>
        </label>

        <div class="sch-app-panel-actions sch-app-panel-actions-split">
            <button type="button" class="sch-app-btn-back" data-prev="additional">← Previous</button>
            <button type="submit" class="sch-app-btn-submit" id="scholSubmitBtn">
                <span class="sch-app-btn-label">Submit Application</span>
                <span class="sch-app-btn-spinner" hidden></span>
            </button>
        </div>
    </div>

    <div class="sch-app-success-overlay" id="scholSuccessModal" hidden role="dialog" aria-modal="true">
        <div class="sch-app-success-card">
            <div class="sch-app-success-icon">✓</div>
            <h3>Application Submitted!</h3>
            <p>Your scholarship application has been recorded for review. (Demo — no data saved to server.)</p>
            <a href="{{ route('dashboard') }}" class="sch-app-btn-submit sch-app-btn-submit-inline">Return to Dashboard</a>
        </div>
    </div>
</section>
