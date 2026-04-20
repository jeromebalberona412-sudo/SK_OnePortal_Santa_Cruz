<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deleted Kabataan - SK Officials Portal</title>

    @vite([
        'app/modules/layout/css/header.css',
        'app/modules/layout/css/sidebar.css',
        'app/modules/Deleted_Kabataan/assets/css/deleted-kabataan.css'
    ])
</head>
<body>

@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
    <div class="page-container deleted-kabataan-page">

        <section class="page-header-section">
            <div class="page-header-left">
                <h1 class="page-title">Deleted Kabataan</h1>
                <p class="page-subtitle">Records that have been removed from the Kabataan list.</p>
            </div>
            <div class="page-header-right">
                <input type="text" id="deletedKabataanSearch" class="filter-input" placeholder="Search by name or purok…">
            </div>
        </section>

        <section class="page-content-section">
            <div class="section-heading-row">
                <h2 class="section-title">Deleted Records</h2>
            </div>
            <div class="table-card">
                <div class="table-wrapper">
                    <table class="dk-table">
                        <thead>
                            <tr>
                                <th>Full Name<div class="column-hint">LN, FN, MN, Suffix</div></th>
                                <th>Age</th>
                                <th>Sex</th>
                                <th>Purok / Sitio</th>
                                <th>Highest Education</th>
                                <th>Deleted At</th>
                                <th class="col-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="deletedKabataanTableBody"></tbody>
                    </table>
                </div>
                <div class="pagination-container">
                    <div class="pagination-info">
                        <span id="deletedKabataanPaginationInfo">No records found</span>
                    </div>
                    <div class="pagination-controls">
                        <button type="button" id="deletedKabataanPrevBtn" class="pagination-btn" disabled>Previous</button>
                        <div class="pagination-numbers" id="deletedKabataanPageNumbers"></div>
                        <button type="button" id="deletedKabataanNextBtn" class="pagination-btn" disabled>Next</button>
                    </div>
                </div>
            </div>
        </section>

    </div>
</main>

<!-- Restore Confirmation Modal -->
<div class="restore-modal-backdrop" id="dkRestoreModal" style="display:none;">
    <div class="restore-modal-box">
        <div class="restore-modal-header">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="restore-modal-icon">
                <polyline points="1 4 1 10 7 10"></polyline>
                <path d="M3.51 15a9 9 0 1 0 .49-4.5"></path>
            </svg>
            <h2 class="restore-modal-title">Restore Record</h2>
        </div>
        <div class="restore-modal-body">
            <p class="restore-modal-message">Restore this record back to the Kabataan list?</p>
            <p class="restore-modal-name" id="dkRestoreName"></p>
        </div>
        <div class="restore-modal-footer">
            <button type="button" class="btn-cancel-restore" id="dkRestoreCancelBtn">Cancel</button>
            <button type="button" class="btn-confirm-restore" id="dkRestoreConfirmBtn">Restore</button>
        </div>
    </div>
</div>

<!-- Toast -->
<div class="dk-toast" id="dkToast"></div>

@vite([
    'app/modules/layout/js/header.js',
    'app/modules/layout/js/sidebar.js',
    'app/modules/Deleted_Kabataan/assets/js/deleted-kabataan.js'
])

</body>
</html>
