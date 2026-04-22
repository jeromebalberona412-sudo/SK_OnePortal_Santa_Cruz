<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Annual Barangay Youth Investment Program (ABYIP) — SK Officials Portal</title>

    @vite([
        'app/modules/layout/css/header.css',
        'app/modules/layout/css/sidebar.css',
        'app/modules/ABYIP/assets/css/abyip.css'
    ])
</head>
<body>

@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
    <div class="page-container abyip-page">

        <section class="page-header-section">
            <div class="page-header-left">
                <h1 class="page-title">Annual Barangay Youth Investment Program (ABYIP)</h1>
                <p class="page-subtitle">Create, view, and manage ABYIP records for the barangay.</p>
            </div>
            <div class="page-header-right">
                <div class="abyip-search-inline">
                    <label for="abyipRecordsSearch" class="abyip-sr-only">Search records by title, date, or time</label>
                    <input type="search" id="abyipRecordsSearch" class="abyip-filter-search-inline" placeholder="Search title, date, or time created…" autocomplete="off">
                </div>
                <button type="button" class="btn primary-btn" id="addAbyipBtn">+ Create ABYIP</button>
            </div>
        </section>

        <section class="page-content-section">
            <div class="section-heading-row">
                <h2 class="section-title">ABYIP Records</h2>
            </div>
            
            <div class="table-card abyip-records-card">
                <div class="table-wrapper abyip-records-wrap">
                    <table class="abyip-records-table" id="recordsTable">
                        <thead>
                            <tr>
                                <th scope="col">Title</th>
                                <th scope="col">Date Created</th>
                                <th scope="col">Time Created</th>
                                <th scope="col">Status</th>
                                <th scope="col">Remarks</th>
                                <th scope="col" class="abyip-records-actions-col">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="recordsTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>

    <template id="abyipDefaultDocumentTemplate">
<!-- ABYIP document body (embedded in abyip.blade.php) -->
<div id="abyipFormRoot" class="abyip-form-root">
    <header class="abyip-doc-header" aria-label="ABYIP document header">
        <div class="abyip-doc-header-text">
            <p class="abyip-doc-line abyip-doc-line-sm">Republic of the Philippines</p>
            <p class="abyip-doc-line abyip-doc-line-sm">Region IV-A</p>
            <p class="abyip-doc-line abyip-doc-line-sm">Province of Laguna</p>
            <p class="abyip-doc-line abyip-doc-line-sm">Municipality of Santa Cruz</p>
            <p class="abyip-doc-barangay">BARANGAY CALIOS</p>
            <p class="abyip-doc-sk">SANGGUNIANG KABATAAN NG CALIOS</p>
            <div class="abyip-doc-title-block">
                <h1 class="abyip-doc-h1">ANNUAL BARANGAY YOUTH INVESTMENT PROGRAM (ABYIP)</h1>
                <h2 class="abyip-doc-h2">CY 2025</h2>
            </div>
        </div>
    </header>

    <div class="abyip-doc-budget">
        <p><span class="abyip-doc-budget-label">Barangay Estimated Budget:</span> ₱14,199,466.00</p>
        <p><span class="abyip-doc-budget-label">Sangguniang Kabataan Fund (10%):</span> ₱1,419,946.60</p>
    </div>

    <div class="table-wrapper abyip-doc-table-wrap">
        <table class="abyip-document-table" id="abyipModalTable">
            <thead>
                <tr>
                    <th rowspan="2">Code</th>
                    <th rowspan="2">PPAs<br><span class="abyip-th-sub">(Programs, Projects, and Activities)</span></th>
                    <th rowspan="2">Description</th>
                    <th rowspan="2">Expected Result</th>
                    <th rowspan="2">Performance Indicator</th>
                    <th rowspan="2">Period of Implementation</th>
                    <th colspan="3">Budget</th>
                    <th rowspan="2">Person Responsible</th>
                </tr>
                <tr>
                    <th>MOOE</th>
                    <th>CO</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <tr class="section-header">
                    <td colspan="10"><strong>I. RECEIPTS PROGRAM</strong></td>
                </tr>
                <tr class="receipts-note">
                    <td></td>
                    <td colspan="9">10% of the General Fund of the Barangay</td>
                </tr>

                <tr class="section-header">
                    <td colspan="10"><strong>II. EXPENDITURE PROGRAM</strong></td>
                </tr>
                <tr class="subsection-header">
                    <td colspan="10"><strong>GENERAL ADMINISTRATION PROGRAM — CURRENT OPERATING EXPENDITURES</strong></td>
                </tr>
                <tr class="subsection-header">
                    <td colspan="10"><strong>Maintenance and Other Operating Expenses (MOOE)</strong></td>
                </tr>

                <tr>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true"></td>
                </tr>
                <tr>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true"></td>
                </tr>
                <tr>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true"></td>
                </tr>
                <tr>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true"></td>
                </tr>
                <tr>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true"></td>
                </tr>
                <tr>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true"></td>
                </tr>
                <tr>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true"></td>
                </tr>
                <tr>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true"></td>
                </tr>

                <tr class="subsection-header">
                    <td colspan="10"><strong>Capital Outlay</strong></td>
                </tr>
                <tr>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true"></td>
                </tr>

                <tr class="subsection-header">
                    <td colspan="10"><strong>B. SK YOUTH DEVELOPMENT AND EMPOWERMENT PROGRAMS</strong></td>
                </tr>
                <tr class="category-header">
                    <td colspan="10"><strong>A. Equitable Access to Quality Education</strong></td>
                </tr>
                <tr>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true"></td>
                </tr>
                <tr>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true"></td>
                </tr>
                <tr>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true"></td>
                </tr>

                <tr class="category-header">
                    <td colspan="10"><strong>B. Environmental Protection</strong></td>
                </tr>
                <tr>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true"></td>
                </tr>
                <tr>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true"></td>
                </tr>

                <tr class="category-header">
                    <td colspan="10"><strong>C. Disaster Risk Reduction and Resiliency</strong></td>
                </tr>
                <tr>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true"></td>
                </tr>
                <tr>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true"></td>
                </tr>

                <tr class="category-header">
                    <td colspan="10"><strong>D. Youth Employment and Livelihood</strong></td>
                </tr>
                <tr>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true"></td>
                </tr>

                <tr class="category-header">
                    <td colspan="10"><strong>E. Health</strong></td>
                </tr>
                <tr>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true"></td>
                </tr>

                <tr class="category-header">
                    <td colspan="10"><strong>F. Anti-Drug and Peace and Order</strong></td>
                </tr>
                <tr>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true"></td>
                </tr>

                <tr class="category-header">
                    <td colspan="10"><strong>G. Gender Sensitivity</strong></td>
                </tr>
                <tr>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true"></td>
                </tr>

                <tr class="category-header">
                    <td colspan="10"><strong>H. Feeding Program for KK Members</strong></td>
                </tr>
                <tr>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true"></td>
                </tr>

                <tr class="category-header">
                    <td colspan="10"><strong>I. Sports Development</strong></td>
                </tr>
                <tr>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true"></td>
                </tr>

                <tr class="category-header">
                    <td colspan="10"><strong>J. Other Programs</strong></td>
                </tr>
                <tr>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true"></td>
                </tr>
                <tr>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true"></td>
                </tr>
                <tr>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true" class="number"></td>
                    <td contenteditable="true"></td>
                </tr>

                <tr class="total-row">
                    <td colspan="6"><strong>TOTAL</strong></td>
                    <td class="number abyip-mooe-total"><strong></strong></td>
                    <td class="number abyip-co-total"><strong></strong></td>
                    <td class="number abyip-grand-total"><strong></strong></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>

    <footer class="document-footer abyip-doc-footer">
        <div class="signature-blocks">
            <div class="signature-left">
                <p>Prepared by:</p>
                <p contenteditable="true" class="signature-name"></p>
                <p contenteditable="true" class="signature-title"></p>
            </div>
            <div class="signature-right">
                <p>Approved by:</p>
                <p contenteditable="true" class="signature-name"></p>
                <p contenteditable="true" class="signature-title"></p>
            </div>
        </div>
    </footer>
</div>

    </template>

    <!-- ABYIP Create / View / Edit Modal -->
    <div class="modal-overlay" id="abyipModal" aria-hidden="true">
        <div class="modal-container" role="dialog" aria-modal="true" aria-labelledby="abyipModalTitle">
            <div class="modal-header" id="abyipModalHeader">
                <h3 id="abyipModalTitle">Create Annual Barangay Youth Investment Program (ABYIP)</h3>
                <div class="modal-controls">
                    <button type="button" class="modal-btn" id="abyipModalMaximize" title="Maximize" aria-label="Maximize">&#9723;</button>
                    <button type="button" class="modal-btn" id="abyipModalClose" title="Close" aria-label="Close">&times;</button>
                </div>
            </div>

            <div class="modal-body" id="abyipModalBody">
                <div id="abyipModalContentMount"></div>
            </div>

            <div class="modal-footer" id="abyipModalFooter">
                <button type="button" class="btn-cancel" id="abyipModalCancel">Cancel</button>
                <button type="button" class="btn-save" id="abyipModalSave">Save ABYIP</button>
                <button type="button" class="btn-print-abyip" id="abyipModalPrint">Print ABYIP</button>
                <button type="button" class="btn-export-word" id="abyipModalExportWord">Export to Word</button>
            </div>
        </div>
    </div>

    <!-- After Save ABYIP (create): title + remarks -->
    <div class="modal-backdrop" id="abyipMetaModal" aria-hidden="true">
        <div class="modal-box abyip-meta-modal-box" role="dialog" aria-labelledby="abyipMetaHeading">
            <div class="abyip-meta-modal-inner">
                <h4 id="abyipMetaHeading">Save ABYIP record</h4>
                <p class="abyip-meta-hint">Set the record title and remarks. Title defaults to ABYIP CY 2025 (editable).</p>
                <div class="abyip-meta-field">
                    <label for="abyipMetaTitleInput">Title</label>
                    <input type="text" id="abyipMetaTitleInput" class="abyip-meta-input" maxlength="500" autocomplete="off">
                </div>
                <div class="abyip-meta-field">
                    <label for="abyipMetaRemarksInput">Remarks</label>
                    <textarea id="abyipMetaRemarksInput" class="abyip-meta-textarea" rows="3" maxlength="500" placeholder="Optional"></textarea>
                </div>
                <div class="abyip-meta-actions">
                    <button type="button" class="btn-cancel" id="abyipMetaCancel">Cancel</button>
                    <button type="button" class="btn-save" id="abyipMetaConfirm">Save record</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete confirmation -->
    <div class="modal-backdrop" id="deleteConfirmModal" aria-hidden="true">
        <div class="modal-box" role="dialog" aria-labelledby="deleteConfirmHeading">
            <div class="confirmation-content">
                <div class="confirmation-message">
                    <h4 id="deleteConfirmHeading">Delete this ABYIP record?</h4>
                    <p>This removes the record from your list. This action cannot be undone.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" id="deleteCancelBtn">Cancel</button>
                <button type="button" class="btn-delete" id="deleteConfirmBtn">Delete</button>
            </div>
        </div>
    </div>
</main>

@vite([
    'app/modules/layout/js/header.js',
    'app/modules/layout/js/sidebar.js',
    'app/modules/ABYIP/assets/js/abyip.js'
])

</body>
</html>
