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
    @include('layout::favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Make a Report - SK Officials Portal</title>
    <link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/{{ $ckVersion }}/ckeditor5.css" crossorigin>
    <link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5-premium-features/{{ $ckVersion }}/ckeditor5-premium-features.css" crossorigin>
    @vite([
        'app/Modules/layout/css/header.css',
        'app/Modules/layout/css/sidebar.css',
        'app/Modules/Reports/assets/css/make-report.css',
        'app/Modules/Reports/assets/css/make-report-ribbon.css'
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
</head>
<body class="mr-body">
@include('loading')
@include('layout::header')
@include('layout::sidebar')

<div id="mrApp" class="mr-app" data-reports-url="{{ route('reports') }}">
    <div class="mr-layout">
        <div class="mr-main">
            <div class="mr-sticky-chrome mr-no-print" id="mrStickyChrome">
                <header class="mr-header">
                    <nav class="mr-breadcrumb" aria-label="Breadcrumb">
                        <a href="{{ route('dashboard') }}">Dashboard</a>
                        <span> / </span>
                        <a href="{{ route('reports') }}">Reports</a>
                        <span> / </span>
                        <span class="mr-breadcrumb-current">Make a Report</span>
                    </nav>
                    <div class="mr-header-row">
                        <div class="mr-header-left">
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
                            <a href="{{ route('reports') }}" class="mr-btn mr-btn-outline mr-btn-sm">My Reports</a>
                            <button type="button" class="mr-btn mr-btn-ghost mr-btn-sm" id="mrDarkToggle">Dark</button>
                            <button type="button" class="mr-btn mr-btn-outline mr-btn-sm" data-mr-action="draft">Save Draft</button>
                            <button type="button" class="mr-btn mr-btn-outline mr-btn-sm" data-mr-action="save">Save</button>
                            <button type="button" class="mr-btn mr-btn-outline mr-btn-sm" data-mr-action="preview">Preview</button>
                            <button type="button" class="mr-btn mr-btn-outline mr-btn-sm" data-mr-action="pdf">Download PDF</button>
                            <button type="button" class="mr-btn mr-btn-outline mr-btn-sm" data-mr-action="word">Download Word</button>
                            <button type="button" class="mr-btn mr-btn-outline mr-btn-sm" data-mr-action="export">Export</button>
                            <button type="button" class="mr-btn mr-btn-outline mr-btn-sm" data-mr-action="print">Print</button>
                            <button type="button" class="mr-btn mr-btn-primary mr-btn-sm" data-mr-action="save-exit">Save &amp; Exit</button>
                        </div>
                    </div>
                </header>

                <div class="mr-doc-controls">
                    <div class="mr-doc-controls-left">
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

                    <nav class="mr-page-nav" aria-label="Document pages">
                        <button type="button" class="mr-btn mr-btn-outline mr-btn-sm" id="mrPrevPage" disabled>Previous</button>
                        <div class="mr-page-tabs" id="mrDocPageTabs" role="tablist"></div>
                        <button type="button" class="mr-btn mr-btn-outline mr-btn-sm mr-page-add" id="mrAddPage" title="Add page">+</button>
                        <button type="button" class="mr-btn mr-btn-outline mr-btn-sm" id="mrNextPage" disabled>Next</button>
                        <span class="mr-page-indicator" id="mrPageIndicator">Page 1 of 1</span>
                    </nav>
                </div>

                @include('Reports::components.make-report-ribbon')
            </div>

            <div class="mr-workspace" id="mrWorkspace">
                <div class="mr-zoom-wrap" id="mrZoomWrap" style="--mr-zoom: 1">
                    <div class="mr-paper-stack" id="mrPaperStack" aria-label="Document pages"></div>
                </div>
            </div>

        </div>
    </div>

    {{-- Metadata kept for save/export (no side panel) --}}
    <input type="hidden" id="mrDetailTitle" value="">
    <input type="hidden" id="mrDetailCategory" value="accomplishment">
    <input type="hidden" id="mrDetailPeriod" value="">
    <input type="hidden" id="mrDetailBarangay" value="Santa Cruz">
    <input type="hidden" id="mrDetailPrepared" value="SK Official">
    <input type="hidden" id="mrDetailSubmitted" value="">
</div>

@include('Reports::components.make-report-modals')

<script type="application/json" id="mr-ckeditor-config-json">{!! json_encode($mrCkeditorConfig, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
<script>
    window.MR_CKEDITOR_CONFIG = JSON.parse(document.getElementById('mr-ckeditor-config-json').textContent);
</script>
<script src="https://cdn.ckeditor.com/ckeditor5/{{ $ckVersion }}/ckeditor5.umd.js" crossorigin defer></script>
<script src="https://cdn.ckeditor.com/ckeditor5-premium-features/{{ $ckVersion }}/ckeditor5-premium-features.umd.js" crossorigin defer></script>
<script src="https://cdn.ckbox.io/ckbox/{{ $ckboxVersion }}/ckbox.js" crossorigin defer></script>
@vite([
    'app/Modules/layout/js/header.js',
    'app/Modules/layout/js/sidebar.js',
    'app/Modules/Reports/assets/js/make-report.js'
])
<script src="{{ url('/shared/js/loading.js') }}"></script>
</body>
</html>
