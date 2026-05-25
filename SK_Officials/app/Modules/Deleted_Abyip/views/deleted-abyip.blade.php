<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deleted ABYIP - SK Officials Portal</title>
    @vite([
        'app/Modules/layout/css/header.css',
        'app/Modules/layout/css/sidebar.css',
        'app/Modules/Deleted_Kabataan/assets/css/deleted-kabataan.css'
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
    <link rel="stylesheet" href="{{ url('/shared/css/sk-archive-terms.css') }}">
</head>
<body>
@include('loading')
@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
    <div class="page-container deleted-kabataan-page">
        <section class="page-header-section">
            <div class="page-header-left">
                <h1 class="page-title">Deleted ABYIP</h1>
                <p class="page-subtitle">ABYIP records removed from the active list. Past terms are view-only.</p>
            </div>
            <div class="page-header-right">
                <input type="text" id="daSearch" class="filter-input" placeholder="Search by title…">
            </div>
        </section>

        <div class="module-stats-grid" id="daStatsRow"></div>

        <div class="filter-tabs-row filter-tabs-row--with-archive">
            <div class="filter-tabs-group">
                <button class="filter-tab active" data-filter="all">All Deleted</button>
                <button class="filter-tab" data-filter="today">Deleted Today</button>
                <button class="filter-tab" data-filter="week">This Week</button>
                <button class="filter-tab" data-filter="month">This Month</button>
            </div>
            @include('layout::partials.archive-show-filter')
        </div>

        <section class="page-content-section">
            <h2 class="section-title" id="daSectionLabel">All Deleted ABYIP Records</h2>
            <div class="table-card">
                <div class="table-wrapper">
                    <table class="dk-table">
                        <thead>
                            <tr>
                                <th>Program / Title</th>
                                <th>Category</th>
                                <th>Deleted Date</th>
                                <th>Deleted Time</th>
                                <th class="col-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="daTableBody"></tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</main>

<div class="restore-modal-backdrop" id="daViewModal" style="display:none;">
    <div class="restore-modal-box view-modal-box">
        <div class="restore-modal-header view-modal-header">
            <h2 class="restore-modal-title">ABYIP Record</h2>
            <button type="button" class="view-modal-close" id="daViewClose">&times;</button>
        </div>
        <div class="view-modal-body" id="daViewBody"></div>
    </div>
</div>

@vite(['app/Modules/Deleted_Abyip/assets/js/deleted-abyip.js'])
<script src="{{ url('/shared/js/loading.js') }}"></script>
<script src="{{ url('/shared/js/sk-archive-terms.js') }}"></script>
</body>
</html>
