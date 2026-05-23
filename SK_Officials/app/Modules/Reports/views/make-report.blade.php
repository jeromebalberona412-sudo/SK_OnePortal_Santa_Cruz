@php
    $ckVersion = config('ckeditor.cdn_version', '47.6.1');
    $ckboxVersion = config('ckeditor.ckbox_version', '2.9.2');
    $mrCkeditorConfig = [
        'licenseKey' => config('ckeditor.license_key'),
        'cloudTokenUrl' => config('ckeditor.cloud_token_url'),
        'cloudWebSocketUrl' => config('ckeditor.cloud_websocket_url'),
        'cdnVersion' => $ckVersion,
    ];
@endphp
<!DOCTYPE html>
<html lang="en" id="mrHtml">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Make a Report - SK Officials Portal</title>
    <link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/{{ $ckVersion }}/ckeditor5.css" crossorigin>
    <link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5-premium-features/{{ $ckVersion }}/ckeditor5-premium-features.css" crossorigin>
    @vite([
        'app/Modules/layout/css/header.css',
        'app/Modules/layout/css/sidebar.css',
        'app/Modules/Reports/assets/css/make-report.css'
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
</head>
<body class="mr-body">
@include('loading')
@include('layout::header')
@include('layout::sidebar')

<div id="mrApp" class="mr-app">
    <header class="mr-header mr-no-print">
        <nav class="mr-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <span> / </span>
            <a href="{{ route('reports') }}">Reports</a>
            <span> / </span>
            <span class="mr-breadcrumb-current">Make a Report</span>
        </nav>
        <div class="mr-header-row">
            <div>
                <h1 class="mr-title">Make a Report</h1>
                <div class="mr-header-meta">
                    <span id="mrCurrentDate"></span>
                    <span class="mr-dot">·</span>
                    <span id="mrAutosaveStatus" class="mr-autosave">
                        <span class="mr-autosave-dot"></span> All changes saved
                    </span>
                    <span id="mrStatusBadge" class="mr-badge mr-badge-draft">Draft</span>
                </div>
            </div>
            <div class="mr-header-actions">
                <a href="{{ route('reports') }}" class="mr-btn mr-btn-outline">My Reports</a>
                <button type="button" class="mr-btn mr-btn-ghost" id="mrDarkToggle">Dark</button>
                <button type="button" class="mr-btn mr-btn-outline" data-mr-action="draft">Save Draft</button>
                <button type="button" class="mr-btn mr-btn-outline" data-mr-action="save">Save</button>
                <button type="button" class="mr-btn mr-btn-outline" data-mr-action="preview">Preview</button>
                <button type="button" class="mr-btn mr-btn-outline" data-mr-action="pdf">Download PDF</button>
                <button type="button" class="mr-btn mr-btn-outline" data-mr-action="word">Download Word</button>
                <button type="button" class="mr-btn mr-btn-outline" data-mr-action="export">Export</button>
                <button type="button" class="mr-btn mr-btn-outline" data-mr-action="print">Print</button>
                <button type="button" class="mr-btn mr-btn-primary" data-mr-action="submit">Submit</button>
            </div>
        </div>
    </header>

    <div class="mr-layout">
        <div class="mr-main">
            <div class="mr-doc-controls mr-no-print">
                <label>Paper
                    <select id="mrPaperSize">
                        <option value="a4" selected>A4 (Default)</option>
                        <option value="letter">Letter</option>
                        <option value="legal">Legal</option>
                        <option value="short">Short Bond</option>
                        <option value="long">Long Bond</option>
                    </select>
                </label>
                <label>Orientation
                    <select id="mrOrientation">
                        <option value="portrait">Portrait</option>
                        <option value="landscape">Landscape</option>
                    </select>
                </label>
                <label>Margins
                    <select id="mrMargins">
                        <option value="normal">Normal</option>
                        <option value="narrow">Narrow</option>
                        <option value="moderate">Moderate</option>
                        <option value="wide">Wide</option>
                    </select>
                </label>
                <label>Zoom
                    <select id="mrZoom">
                        <option value="0.5">50%</option>
                        <option value="0.75">75%</option>
                        <option value="1" selected>100%</option>
                        <option value="1.25">125%</option>
                    </select>
                </label>
                <button type="button" class="mr-btn mr-btn-ghost mr-btn-sm" id="mrFullscreenBtn">Fullscreen</button>
            </div>

            <nav class="mr-page-nav mr-no-print" aria-label="Document pages">
                <button type="button" class="mr-btn mr-btn-outline mr-btn-sm" id="mrPrevPage" disabled>Previous</button>
                <div class="mr-page-tabs" id="mrDocPageTabs" role="tablist"></div>
                <button type="button" class="mr-btn mr-btn-outline mr-btn-sm mr-page-add" id="mrAddPage" title="Add page">+</button>
                <button type="button" class="mr-btn mr-btn-outline mr-btn-sm" id="mrNextPage" disabled>Next</button>
                <span class="mr-page-indicator" id="mrPageIndicator">Page 1 of 1</span>
            </nav>

            <div class="mr-workspace" id="mrWorkspace">
                <div class="mr-zoom-wrap" id="mrZoomWrap" style="--mr-zoom: 1">
                    <div class="mr-paper-stack" id="mrPaperStack" aria-label="Document pages"></div>
                </div>
            </div>

            <footer class="mr-doc-footer mr-no-print">
                <span id="mrWordCount">0 words</span>
                <span id="mrCharCount">0 characters</span>
                <span id="mrReadTime">0 min read</span>
                <span class="mr-flex-spacer"></span>
                <span id="mrLastEdited">Last edited: —</span>
            </footer>
        </div>

        <aside class="mr-details-panel mr-no-print" id="mrDetailsPanel">
            <div class="mr-panel-head">
                <h2>Report Details</h2>
                <button type="button" class="mr-icon-btn" id="mrDetailsToggle" title="Collapse panel">Hide</button>
            </div>
            <div class="mr-details-body">
                <label class="mr-field">
                    <span>Report Title</span>
                    <input type="text" id="mrDetailTitle" maxlength="200" placeholder="Accomplishment Report">
                </label>
                <label class="mr-field">
                    <span>Category</span>
                    <select id="mrDetailCategory">
                        <option value="scholarship">Scholarship Program Report</option>
                        <option value="activity">Activity Report</option>
                        <option value="resolution">SK Resolution</option>
                        <option value="minutes">Meeting Minutes</option>
                        <option value="financial">Financial Report</option>
                        <option value="accomplishment">Accomplishment Report</option>
                    </select>
                </label>
                <label class="mr-field">
                    <span>Reporting Period</span>
                    <input type="text" id="mrDetailPeriod" placeholder="Q1 2026">
                </label>
                <label class="mr-field">
                    <span>Barangay</span>
                    <select id="mrDetailBarangay">
                        <option>Santa Cruz</option>
                        <option>Poblacion</option>
                    </select>
                </label>
                <label class="mr-field">
                    <span>Prepared By</span>
                    <input type="text" id="mrDetailPrepared" value="{{ auth()->user()->name ?? 'SK Official' }}">
                </label>
                <label class="mr-field">
                    <span>Submission Date</span>
                    <input type="date" id="mrDetailSubmitted">
                </label>
                <div class="mr-attach-block">
                    <h3 class="mr-attach-title">Attachments</h3>
                    <div class="mr-dropzone" id="mrDropzone">
                        <input type="file" id="mrFileInput" multiple accept=".docx,.pdf,image/*" hidden>
                        <p class="mr-dropzone-title">Upload files</p>
                        <p class="mr-dropzone-sub">Drag and drop or browse</p>
                        <button type="button" class="mr-btn mr-btn-sm mr-btn-outline" id="mrBrowseFiles">Browse</button>
                    </div>
                    <ul class="mr-file-list" id="mrFileList"></ul>
                </div>
            </div>
        </aside>
    </div>
</div>

@include('Reports::components.make-report-modals')

<script type="application/json" id="mr-ckeditor-config-json">{!! json_encode($mrCkeditorConfig, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
<script>
    window.MR_CKEDITOR_CONFIG = JSON.parse(document.getElementById('mr-ckeditor-config-json').textContent);
</script>
<script src="https://cdn.ckeditor.com/ckeditor5/{{ $ckVersion }}/ckeditor5.umd.js" crossorigin></script>
<script src="https://cdn.ckeditor.com/ckeditor5-premium-features/{{ $ckVersion }}/ckeditor5-premium-features.umd.js" crossorigin></script>
<script src="https://cdn.ckbox.io/ckbox/{{ $ckboxVersion }}/ckbox.js" crossorigin></script>
@vite([
    'app/Modules/layout/js/header.js',
    'app/Modules/layout/js/sidebar.js',
    'app/Modules/Reports/assets/js/make-report.js'
])
<script src="{{ url('/shared/js/loading.js') }}"></script>
</body>
</html>
