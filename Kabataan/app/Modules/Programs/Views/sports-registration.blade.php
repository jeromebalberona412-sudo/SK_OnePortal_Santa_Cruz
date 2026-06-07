<!DOCTYPE html>
<html lang="en">
<head>
    @include('layout::favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kabataan Sports Registration - SK OnePortal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite([
        'app/Modules/Layout/assets/css/kabataan-header.css',
        'app/Modules/Layout/assets/js/kabataan-header.js',
        'app/Modules/Dashboard/assets/css/chatbot.css',
        'app/Modules/Dashboard/assets/js/chatbot.js',
        'app/Modules/Dashboard/assets/css/notif.css',
        'app/Modules/Dashboard/assets/js/notif.js',
        'app/Modules/Programs/assets/css/sports-registration.css',
        'app/Modules/Programs/assets/js/sports-registration.js',
        'app/Modules/Shared/assets/css/loading.css',
        'app/Modules/Shared/assets/js/loading.js',
    ])
</head>
<body class="sr-body kabataan-app-page">
    @include('dashboard::loading')
    @include('layout::kabataan-header', ['showSearch' => true])

    <main class="sr-main">
        <div class="sr-back-link">
            <a href="{{ route('sports.apply') }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                <span>Back</span>
            </a>
        </div>
        @include('programs::sports.partials.header-banner')
        @include('programs::sports.partials.form-progress')

        <div class="sr-alert sr-alert-error" id="srFormAlert" role="alert" hidden></div>

        <p class="sr-reminder-note">
            <strong>Reminder:</strong> Complete all sections and upload clear PDF scans (max 5MB each). Incomplete registrations may not be processed.
        </p>

        <form id="sportsRegistrationForm" class="sr-form" novalidate>
            {{-- Sport selection --}}
            <section class="sr-card sr-section" data-step="1" id="section-sport">
                <div class="sr-card-head">
                    <span class="sr-step-badge">Step 1</span>
                    <h2>Select Your Sport</h2>
                    <p>Choose the sport category you want to register for</p>
                </div>
                <div class="sr-sport-cards" role="radiogroup" aria-label="Sport selection">
                    <label class="sr-sport-card">
                        <input type="radio" name="sportChoice" value="Basketball" required>
                        <span class="sr-sport-card-inner">
                            <span class="sr-sport-emoji">🏀</span>
                            <span class="sr-sport-name">Basketball</span>
                        </span>
                    </label>
                    <label class="sr-sport-card">
                        <input type="radio" name="sportChoice" value="Volleyball" required>
                        <span class="sr-sport-card-inner">
                            <span class="sr-sport-emoji">🏐</span>
                            <span class="sr-sport-name">Volleyball</span>
                        </span>
                    </label>
                    <label class="sr-sport-card">
                        <input type="radio" name="sportChoice" value="Other" required>
                        <span class="sr-sport-card-inner">
                            <span class="sr-sport-emoji">⚽</span>
                            <span class="sr-sport-name">Other</span>
                        </span>
                    </label>
                </div>
                <div class="sr-field sr-other-sport-wrap" id="otherSportWrap" hidden>
                    <label for="otherSport">Specify Sport <span class="sr-req">*</span></label>
                    <input type="text" id="otherSport" name="otherSport" placeholder="e.g. Badminton, Football, Athletics">
                </div>
            </section>

            {{-- Personal information --}}
            <section class="sr-card sr-section" data-step="2" id="section-personal">
                <div class="sr-card-head">
                    <span class="sr-step-badge">Step 2</span>
                    <h2>Personal Information</h2>
                </div>
                <div class="sr-grid sr-grid-2">
                    <div class="sr-field sr-field-span-2">
                        <label for="fullName">Full Name <span class="sr-req">*</span></label>
                        <input type="text" id="fullName" name="fullName" required placeholder="Juan Miguel Dela Cruz">
                    </div>
                    <div class="sr-field">
                        <label for="nickname">Nickname</label>
                        <input type="text" id="nickname" name="nickname" placeholder="JM">
                    </div>
                    <div class="sr-field">
                        <label for="birthdate">Birthdate <span class="sr-req">*</span></label>
                        <input type="date" id="birthdate" name="birthdate" required>
                    </div>
                    <div class="sr-field">
                        <label for="age">Age <span class="sr-req">*</span></label>
                        <input type="number" id="age" name="age" min="15" max="30" readonly required>
                    </div>
                    <div class="sr-field">
                        <label for="gender">Gender <span class="sr-req">*</span></label>
                        <select id="gender" name="gender" required>
                            <option value="">Select</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div class="sr-field sr-field-span-2">
                        <label for="address">Complete Address <span class="sr-req">*</span></label>
                        <textarea id="address" name="address" rows="2" required placeholder="Purok, Barangay Santa Cruz, Laguna"></textarea>
                    </div>
                    <div class="sr-field">
                        <label for="contactNumber">Contact Number <span class="sr-req">*</span></label>
                        <input type="tel" id="contactNumber" name="contactNumber" required placeholder="09171234567">
                    </div>
                    <div class="sr-field">
                        <label for="email">Email Address <span class="sr-req">*</span></label>
                        <input type="email" id="email" name="email" required placeholder="juan@email.com">
                    </div>
                    <div class="sr-field sr-field-span-2">
                        <label for="facebook">Facebook Profile Link</label>
                        <input type="url" id="facebook" name="facebook" placeholder="https://facebook.com/username">
                    </div>
                </div>
            </section>

            {{-- Sports information --}}
            <section class="sr-card sr-section" data-step="3" id="section-sports">
                <div class="sr-card-head">
                    <span class="sr-step-badge">Step 3</span>
                    <h2>Sports Information</h2>
                </div>
                <div class="sr-grid sr-grid-2">
                    <div class="sr-field">
                        <label for="selectedSportDisplay">Selected Sport</label>
                        <input type="text" id="selectedSportDisplay" name="selectedSportDisplay" readonly placeholder="Auto-filled from Step 1">
                    </div>
                    <div class="sr-field">
                        <label for="teamName">Team Name</label>
                        <input type="text" id="teamName" name="teamName" placeholder="Santa Cruz Warriors" required>
                    </div>
                    <div class="sr-field">
                        <label for="position">Position / Role <span class="sr-req">*</span></label>
                        <input type="text" id="position" name="position" required placeholder="Point Guard / Setter">
                    </div>
                    <div class="sr-field">
                        <label for="jerseySize">Jersey Size <span class="sr-req">*</span></label>
                        <select id="jerseySize" name="jerseySize" required>
                            <option value="">Select size</option>
                            <option>XS</option><option>S</option><option>M</option>
                            <option>L</option><option>XL</option><option>XXL</option>
                        </select>
                    </div>
                    <div class="sr-field">
                        <label for="height">Height (cm) <span class="sr-req">*</span></label>
                        <input type="number" id="height" name="height" min="100" max="250" required placeholder="170">
                    </div>
                    <div class="sr-field">
                        <label for="weight">Weight (kg) <span class="sr-req">*</span></label>
                        <input type="number" id="weight" name="weight" min="30" max="200" step="0.1" required placeholder="65">
                    </div>
                    <div class="sr-field">
                        <label for="yearsExperience">Years of Experience <span class="sr-req">*</span></label>
                        <select id="yearsExperience" name="yearsExperience" required>
                            <option value="">Select</option>
                            <option>Less than 1 year</option>
                            <option>1–2 years</option>
                            <option>3–5 years</option>
                            <option>More than 5 years</option>
                        </select>
                    </div>
                    <div class="sr-field">
                        <label for="coachName">Coach Name <span class="sr-req">*</span></label>
                        <input type="text" id="coachName" name="coachName" required placeholder="Coach Roberto Mendoza">
                    </div>
                    <div class="sr-field sr-field-span-2">
                        <label for="tournamentExperience">Previous Tournament Experience</label>
                        <textarea id="tournamentExperience" name="tournamentExperience" rows="3" placeholder="Inter-barangay league 2024, municipal meet 2025..."></textarea>
                    </div>
                </div>
            </section>

            {{-- Emergency contact --}}
            <section class="sr-card sr-section" data-step="4" id="section-emergency">
                <div class="sr-card-head">
                    <span class="sr-step-badge">Step 4</span>
                    <h2>Emergency Contact</h2>
                </div>
                <div class="sr-grid sr-grid-2">
                    <div class="sr-field">
                        <label for="emergencyName">Emergency Contact Person <span class="sr-req">*</span></label>
                        <input type="text" id="emergencyName" name="emergencyName" required placeholder="Maria Santos Dela Cruz">
                    </div>
                    <div class="sr-field">
                        <label for="emergencyRelation">Relationship <span class="sr-req">*</span></label>
                        <input type="text" id="emergencyRelation" name="emergencyRelation" required placeholder="Mother">
                    </div>
                    <div class="sr-field">
                        <label for="emergencyNumber">Emergency Contact Number <span class="sr-req">*</span></label>
                        <input type="tel" id="emergencyNumber" name="emergencyNumber" required placeholder="09291234567">
                    </div>
                    <div class="sr-field sr-field-span-2">
                        <label for="emergencyAddress">Emergency Address <span class="sr-req">*</span></label>
                        <textarea id="emergencyAddress" name="emergencyAddress" rows="2" required placeholder="Same as applicant or complete address"></textarea>
                    </div>
                </div>
            </section>

            {{-- Requirements upload --}}
            <section class="sr-card sr-section" data-step="5" id="section-uploads">
                <div class="sr-card-head">
                    <span class="sr-step-badge">Step 5</span>
                    <h2>Requirements Upload</h2>
                    <p>PDF for documents · JPG/PNG/PDF for recent photo · Max 5MB each</p>
                </div>
                <div class="sr-upload-instruction">
                    <svg viewBox="0 0 20 20" fill="currentColor" width="20" height="20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                    <span>Drag and drop files into each box or click to browse. All documents must be clear and readable.</span>
                </div>
                <div class="sr-upload-grid">
                    @foreach([
                        ['id' => 'validId', 'label' => 'Valid ID (PDF)', 'accept' => '.pdf,application/pdf', 'pdf' => true],
                        ['id' => 'validIdFront', 'label' => 'Valid ID — Front (PDF)', 'accept' => '.pdf,application/pdf', 'pdf' => true],
                        ['id' => 'validIdBack', 'label' => 'Valid ID — Back (PDF)', 'accept' => '.pdf,application/pdf', 'pdf' => true],
                        ['id' => 'parentConsent', 'label' => 'Parent Consent Form (PDF)', 'accept' => '.pdf,application/pdf', 'pdf' => true],
                        ['id' => 'medicalClearance', 'label' => 'Medical Clearance (PDF)', 'accept' => '.pdf,application/pdf', 'pdf' => true],
                        ['id' => 'barangayResidency', 'label' => 'Barangay Residency Certificate (PDF)', 'accept' => '.pdf,application/pdf', 'pdf' => true],
                        ['id' => 'recentPhoto', 'label' => 'Recent 2×2 Photo', 'accept' => '.pdf,.jpg,.jpeg,.png,application/pdf,image/*', 'pdf' => false],
                    ] as $upload)
                    <div class="sr-upload-item" data-upload-id="{{ $upload['id'] }}" data-pdf-only="{{ $upload['pdf'] ? '1' : '0' }}">
                        <label class="sr-upload-label">{{ $upload['label'] }} <span class="sr-req">*</span></label>
                        <div class="sr-dropzone" tabindex="0" role="button">
                            <input type="file" id="upload-{{ $upload['id'] }}" name="{{ $upload['id'] }}" accept="{{ $upload['accept'] }}" class="sr-file-input" required>
                            <div class="sr-dropzone-default">
                                <span class="sr-dropzone-icon">📤</span>
                                <span class="sr-dropzone-text">Drop file here or <strong>browse</strong></span>
                                <span class="sr-dropzone-meta">{{ $upload['pdf'] ? 'PDF only · Max 5MB' : 'PDF, JPG, PNG · Max 5MB' }}</span>
                            </div>
                            <div class="sr-dropzone-done" hidden>
                                <span class="sr-upload-status sr-upload-status-success">✓ Uploaded</span>
                                <span class="sr-file-name"></span>
                                <button type="button" class="sr-file-remove">Remove</button>
                            </div>
                        </div>
                        <p class="sr-field-error" hidden></p>
                    </div>
                    @endforeach
                </div>
            </section>

            {{-- Terms --}}
            <section class="sr-card sr-section" data-step="6" id="section-terms">
                <div class="sr-card-head">
                    <span class="sr-step-badge">Step 6</span>
                    <h2>Terms & Conditions</h2>
                </div>
                <div class="sr-rules-card">
                    <h3>Rules & Regulations</h3>
                    <ul>
                        <li>Applicant must be a resident of Barangay Santa Cruz, Laguna aged 15–30.</li>
                        <li>Must attend all training sessions, practices, and official games.</li>
                        <li>Must maintain good sportsmanship, discipline, and respect for officials.</li>
                        <li>Medical clearance and parent consent are required for minors.</li>
                        <li>SK may revoke registration for misconduct or falsified documents.</li>
                    </ul>
                    <button type="button" class="sr-link-btn" id="openTermsModal">Read full waiver & liability terms</button>
                </div>
                <label class="sr-checkbox-row">
                    <input type="checkbox" id="agreeTerms" name="agreeTerms" required>
                    <span class="sr-checkbox-box"></span>
                    <span>I have read and agree to the rules, waiver, and sports participation consent. I certify that all information provided is true and correct.</span>
                </label>
            </section>

            <div class="sr-submit-wrap">
                <button type="submit" class="sr-submit-btn" id="srSubmitBtn" disabled>
                    <span class="sr-submit-label">Submit Registration</span>
                    <span class="sr-submit-spinner" hidden></span>
                </button>
                <p class="sr-submit-hint" id="srSubmitHint">Please agree to the Terms & Conditions to enable submission.</p>
            </div>
        </form>
    </main>

    @include('programs::sports.partials.terms-modal')
    @include('programs::sports.partials.success-modal')
</body>
</html>
