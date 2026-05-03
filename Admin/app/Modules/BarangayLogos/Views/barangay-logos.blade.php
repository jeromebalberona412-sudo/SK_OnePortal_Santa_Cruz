@extends('layouts.app')

@section('title', 'SK Barangay Logos')

@section('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['app/Modules/BarangayLogos/assets/css/barangay-logos.css'])
@endsection

@section('content')
@include('layout::header')
@include('layout::sidebar')

<div class="main-content-modern barangay-logos-page" id="mainContent">
    <div class="barangay-logos-container">

        {{-- ── Page Header ── --}}
        <div class="bl-page-header">
            <div>
                <h1 class="bl-page-title">SK Barangay Logos</h1>
                <p class="bl-page-subtitle">Manage logo images for each barangay in Santa Cruz</p>
            </div>

            <div class="bl-header-controls">
                {{-- Search bar --}}
                <div class="bl-search-wrap">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" class="bl-search-icon">
                        <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/>
                        <path d="m21 21-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <input
                        type="text"
                        id="blSearchInput"
                        class="bl-search-input"
                        placeholder="Search barangay..."
                        autocomplete="off"
                        aria-label="Search barangay"
                    />
                    <button type="button" class="bl-search-clear" id="blSearchClear" aria-label="Clear search" style="display:none;">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                            <line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>

                {{-- Counter pill --}}
                <div class="bl-counter-pill">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" class="bl-counter-icon">
                        <rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="1.8"/>
                        <circle cx="8.5" cy="8.5" r="1.5" stroke="currentColor" stroke-width="1.8"/>
                        <polyline points="21 15 16 10 5 21" stroke="currentColor" stroke-width="1.8"/>
                    </svg>
                    <span id="uploadedCount">{{ $logos->count() }}</span>
                    <span class="bl-counter-sep">/</span>
                    <span>26</span>
                    <span class="bl-counter-label">Uploaded</span>
                </div>

                {{-- Hide / Show toggle --}}
                <button type="button" class="bl-toggle-btn" id="toggleLogosBtn">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" id="toggleIcon">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2"/>
                        <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                    </svg>
                    <span id="toggleBtnText">Hide Logos</span>
                </button>
            </div>
        </div>

        {{-- ── Grid ── --}}
        <div class="bl-grid" id="barangayGrid">

            @php
                $barangayList = $barangays->isNotEmpty() ? $barangays : collect([
                    (object)['id' => null, 'name' => 'Alipit'],
                    (object)['id' => null, 'name' => 'Bagumbayan'],
                    (object)['id' => null, 'name' => 'Barangay I (Poblacion)'],
                    (object)['id' => null, 'name' => 'Barangay II (Poblacion)'],
                    (object)['id' => null, 'name' => 'Barangay III (Poblacion)'],
                    (object)['id' => null, 'name' => 'Barangay IV (Poblacion)'],
                    (object)['id' => null, 'name' => 'Barangay V (Poblacion)'],
                    (object)['id' => null, 'name' => 'Bubukal'],
                    (object)['id' => null, 'name' => 'Calios'],
                    (object)['id' => null, 'name' => 'Duhat'],
                    (object)['id' => null, 'name' => 'Gatid'],
                    (object)['id' => null, 'name' => 'Jasaan'],
                    (object)['id' => null, 'name' => 'Labuin'],
                    (object)['id' => null, 'name' => 'Malinao'],
                    (object)['id' => null, 'name' => 'Oogong'],
                    (object)['id' => null, 'name' => 'Pagsawitan'],
                    (object)['id' => null, 'name' => 'Palasan'],
                    (object)['id' => null, 'name' => 'Patimbao'],
                    (object)['id' => null, 'name' => 'San Jose'],
                    (object)['id' => null, 'name' => 'San Juan'],
                    (object)['id' => null, 'name' => 'San Pablo Norte'],
                    (object)['id' => null, 'name' => 'San Pablo Sur'],
                    (object)['id' => null, 'name' => 'Santisima Cruz'],
                    (object)['id' => null, 'name' => 'Santo Angel Central'],
                    (object)['id' => null, 'name' => 'Santo Angel Norte'],
                    (object)['id' => null, 'name' => 'Santo Angel Sur'],
                ]);
            @endphp

            @foreach($barangayList as $index => $barangay)
            @php
                $existingLogo = $barangay->id ? ($logos[$barangay->id] ?? null) : null;
            @endphp
            <div
                class="bl-card{{ $existingLogo ? ' has-logo' : '' }}"
                id="card-{{ $index }}"
                data-barangay-id="{{ $barangay->id }}"
                data-logo-id="{{ $existingLogo?->id }}"
            >

                {{-- Preview area --}}
                <div class="bl-preview-box" id="previewBox-{{ $index }}">

                    {{-- Placeholder --}}
                    <div class="bl-placeholder" id="placeholder-{{ $index }}" style="{{ $existingLogo ? 'display:none;' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" class="bl-placeholder-icon" aria-hidden="true">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2" stroke="currentColor" stroke-width="1.5"/>
                            <circle cx="8.5" cy="8.5" r="1.5" stroke="currentColor" stroke-width="1.5"/>
                            <polyline points="21 15 16 10 5 21" stroke="currentColor" stroke-width="1.5"/>
                        </svg>
                        <span class="bl-placeholder-text">No Logo</span>
                    </div>

                    {{-- Uploaded image --}}
                    <img
                        id="img-{{ $index }}"
                        src="{{ $existingLogo?->url ?? '' }}"
                        alt="{{ $barangay->name }} logo"
                        class="bl-logo-img"
                        style="{{ $existingLogo && $logosVisible ?? true ? 'display:block;' : 'display:none;' }}"
                        onerror="this.style.display='none';document.getElementById('placeholder-{{ $index }}').style.display='';"
                    />

                    {{-- Hidden overlay (logos hidden state) --}}
                    <div class="bl-hidden-overlay" id="overlay-{{ $index }}" style="display:none;">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <line x1="1" y1="1" x2="23" y2="23" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        <span>Hidden</span>
                    </div>

                    {{-- Remove button (trash icon) --}}
                    <button
                        type="button"
                        class="bl-remove-btn"
                        id="removeBtn-{{ $index }}"
                        data-index="{{ $index }}"
                        data-name="{{ $barangay->name }}"
                        style="{{ $existingLogo ? '' : 'display:none;' }}"
                        title="Remove logo"
                        aria-label="Remove logo for {{ $barangay->name }}"
                    >
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <polyline points="3 6 5 6 21 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" stroke="white" stroke-width="2" stroke-linecap="round"/>
                            <path d="M10 11v6M14 11v6" stroke="white" stroke-width="2" stroke-linecap="round"/>
                            <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" stroke="white" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>

                {{-- Card footer --}}
                <div class="bl-card-footer">
                    <p class="bl-barangay-name" title="{{ $barangay->name }}">{{ $barangay->name }}</p>

                    {{--
                        Upload label:
                        - data-index / data-name used by JS to identify the card
                        - for="fileInput-N" opens picker when no logo
                        - JS intercepts click when card has-logo → shows change modal
                    --}}
                    <label
                        class="bl-upload-btn bl-upload-label"
                        id="uploadBtn-{{ $index }}"
                        for="fileInput-{{ $index }}"
                        data-index="{{ $index }}"
                        data-name="{{ $barangay->name }}"
                    >
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <polyline points="17 8 12 3 7 8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <line x1="12" y1="3" x2="12" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        <span id="uploadBtnText-{{ $index }}">{{ $existingLogo ? 'Change Logo' : 'Upload Logo' }}</span>
                    </label>

                    {{-- Hidden file input — separate from label so .click() works reliably --}}
                    <input
                        type="file"
                        id="fileInput-{{ $index }}"
                        class="bl-file-input"
                        data-index="{{ $index }}"
                        data-name="{{ $barangay->name }}"
                        accept="image/*"
                        style="display:none;"
                    />
                </div>

            </div>
            @endforeach

        </div>{{-- end bl-grid --}}

        {{-- No results message --}}
        <div id="blNoResults" class="bl-no-results" style="display:none;">
            No barangay found matching your search.
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     MODAL 1: Change Logo Confirmation
══════════════════════════════════════════════════════════ --}}
<div class="bl-modal-overlay" id="blChangeModal" role="dialog" aria-modal="true" style="display:none;">
    <div class="bl-modal bl-action-modal">

        <div class="bl-action-modal-body">
            <div class="bl-action-icon bl-action-icon-blue">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                    <polyline points="17 8 12 3 7 8" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                    <line x1="12" y1="3" x2="12" y2="15" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                </svg>
            </div>
            <h3 class="bl-action-modal-title">Change Logo?</h3>
            <p class="bl-action-modal-msg">Are you sure you want to change this logo?</p>
            <p class="bl-action-modal-barangay" id="blChangeBarangayName"></p>
        </div>

        <div class="bl-modal-actions">
            <button type="button" class="bl-modal-btn-cancel" id="blChangeCancelBtn">Cancel</button>
            <button type="button" class="bl-modal-btn-action btn-blue" id="blChangeConfirmBtn">Yes, Continue</button>
        </div>

    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     MODAL 2: Remove Logo Confirmation
══════════════════════════════════════════════════════════ --}}
<div class="bl-modal-overlay" id="blRemoveModal" role="dialog" aria-modal="true" style="display:none;">
    <div class="bl-modal bl-action-modal">

        <div class="bl-action-modal-body">
            <div class="bl-action-icon bl-action-icon-red">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <polyline points="3 6 5 6 21 6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                    <path d="M10 11v6M14 11v6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                    <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                </svg>
            </div>
            <h3 class="bl-action-modal-title">Remove Logo?</h3>
            <p class="bl-action-modal-msg">Are you sure you want to remove this logo?</p>
            <p class="bl-action-modal-barangay" id="blRemoveBarangayName"></p>
        </div>

        <div class="bl-modal-actions">
            <button type="button" class="bl-modal-btn-cancel" id="blRemoveCancelBtn">Cancel</button>
            <button type="button" class="bl-modal-btn-action btn-red" id="blRemoveConfirmBtn">Remove</button>
        </div>

    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     MODAL 3: Upload Preview Confirmation (fresh upload)
══════════════════════════════════════════════════════════ --}}
<div class="bl-modal-overlay" id="blConfirmModal" role="dialog" aria-modal="true" style="display:none;">
    <div class="bl-modal">

        <div class="bl-modal-header">
            <div class="bl-modal-header-icon">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2"/>
                    <circle cx="8.5" cy="8.5" r="1.5" stroke="currentColor" stroke-width="2"/>
                    <polyline points="21 15 16 10 5 21" stroke="currentColor" stroke-width="2"/>
                </svg>
            </div>
            <div>
                <h3 class="bl-modal-title">Upload Logo</h3>
                <p class="bl-modal-subtitle" id="blModalSubtitle">Barangay Name</p>
            </div>
            <button type="button" class="bl-modal-close" id="blUploadCancelBtn" aria-label="Close">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                    <line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                </svg>
            </button>
        </div>

        <div class="bl-modal-preview-wrap">
            <img id="blModalPreviewImg" src="" alt="Preview" class="bl-modal-preview-img" />
        </div>

        <div class="bl-modal-file-info">
            <span id="blModalFileName">filename.png</span>
            <span class="bl-modal-file-size" id="blModalFileSize">0 KB</span>
        </div>

        <div class="bl-modal-actions">
            <button type="button" class="bl-modal-btn-cancel" id="blUploadCancelBtn2">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                    <line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                </svg>
                Cancel
            </button>
            <button type="button" class="bl-modal-btn-confirm" id="blUploadConfirmBtn">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <polyline points="20 6 9 17 4 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Upload Logo
            </button>
        </div>

    </div>
</div>

@endsection

@section('scripts')
    @vite(['app/Modules/BarangayLogos/assets/js/barangay-logos.js'])
@endsection
