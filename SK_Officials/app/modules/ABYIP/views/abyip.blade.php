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
                    <td contenteditable="true">1.1</td>
                    <td contenteditable="true">Digital Literacy Training</td>
                    <td contenteditable="true">Computer skills development for youth</td>
                    <td contenteditable="true">Enhanced digital competencies</td>
                    <td contenteditable="true">Number of certified participants</td>
                    <td contenteditable="true">February 01, 2025 to November 30, 2025</td>
                    <td contenteditable="true" class="number">45,000.00</td>
                    <td contenteditable="true" class="number">0.00</td>
                    <td contenteditable="true" class="number">45,000.00</td>
                    <td contenteditable="true">SK Tech Committee</td>
                </tr>
                <tr>
                    <td contenteditable="true">1.2</td>
                    <td contenteditable="true">Youth Leadership Summit</td>
                    <td contenteditable="true">Leadership development program</td>
                    <td contenteditable="true">Developed youth leaders</td>
                    <td contenteditable="true">Number of graduates</td>
                    <td contenteditable="true">March 15, 2025 to October 15, 2025</td>
                    <td contenteditable="true" class="number">35,000.00</td>
                    <td contenteditable="true" class="number">0.00</td>
                    <td contenteditable="true" class="number">35,000.00</td>
                    <td contenteditable="true">SK Leadership Team</td>
                </tr>
                <tr>
                    <td contenteditable="true">1.3</td>
                    <td contenteditable="true">Community Outreach Program</td>
                    <td contenteditable="true">Service activities for community</td>
                    <td contenteditable="true">Completed outreach projects</td>
                    <td contenteditable="true">Number of communities served</td>
                    <td contenteditable="true">April 01, 2025 to September 30, 2025</td>
                    <td contenteditable="true" class="number">28,000.00</td>
                    <td contenteditable="true" class="number">0.00</td>
                    <td contenteditable="true" class="number">28,000.00</td>
                    <td contenteditable="true">SK Outreach Committee</td>
                </tr>
                <tr>
                    <td contenteditable="true">1.4</td>
                    <td contenteditable="true">Office Equipment Purchase</td>
                    <td contenteditable="true">Modern office technology</td>
                    <td contenteditable="true">Equipped office with technology</td>
                    <td contenteditable="true">Number of equipment procured</td>
                    <td contenteditable="true">May 01, 2025 to June 30, 2025</td>
                    <td contenteditable="true" class="number">0.00</td>
                    <td contenteditable="true" class="number">65,000.00</td>
                    <td contenteditable="true" class="number">65,000.00</td>
                    <td contenteditable="true">SK Admin Team</td>
                </tr>
                <tr>
                    <td contenteditable="true">1.5</td>
                    <td contenteditable="true">Youth Sports Festival</td>
                    <td contenteditable="true">Annual sports competition</td>
                    <td contenteditable="true">Conducted sports activities</td>
                    <td contenteditable="true">Number of participants</td>
                    <td contenteditable="true">July 01, 2025 to August 31, 2025</td>
                    <td contenteditable="true" class="number">40,000.00</td>
                    <td contenteditable="true" class="number">0.00</td>
                    <td contenteditable="true" class="number">40,000.00</td>
                    <td contenteditable="true">SK Sports Committee</td>
                </tr>
                <tr>
                    <td contenteditable="true">1.6</td>
                    <td contenteditable="true">Communication Systems</td>
                    <td contenteditable="true">Digital communication tools</td>
                    <td contenteditable="true">Improved communication channels</td>
                    <td contenteditable="true">Number of platforms established</td>
                    <td contenteditable="true">June 01, 2025 to December 15, 2025</td>
                    <td contenteditable="true" class="number">18,000.00</td>
                    <td contenteditable="true" class="number">0.00</td>
                    <td contenteditable="true" class="number">18,000.00</td>
                    <td contenteditable="true">SK Communications Team</td>
                </tr>
                <tr>
                    <td contenteditable="true">1.7</td>
                    <td contenteditable="true">Professional Development</td>
                    <td contenteditable="true">Skills enhancement workshops</td>
                    <td contenteditable="true">Enhanced professional skills</td>
                    <td contenteditable="true">Number of workshops conducted</td>
                    <td contenteditable="true">February 15, 2025 to November 15, 2025</td>
                    <td contenteditable="true" class="number">22,000.00</td>
                    <td contenteditable="true" class="number">0.00</td>
                    <td contenteditable="true" class="number">22,000.00</td>
                    <td contenteditable="true">SK Training Committee</td>
                </tr>
                <tr>
                    <td contenteditable="true">1.8</td>
                    <td contenteditable="true">Youth Engagement Activities</td>
                    <td contenteditable="true">Community youth programs</td>
                    <td contenteditable="true">Increased youth participation</td>
                    <td contenteditable="true">Number of activities completed</td>
                    <td contenteditable="true">March 01, 2025 to October 30, 2025</td>
                    <td contenteditable="true" class="number">25,000.00</td>
                    <td contenteditable="true" class="number">0.00</td>
                    <td contenteditable="true" class="number">25,000.00</td>
                    <td contenteditable="true">SK Engagement Team</td>
                </tr>

                <tr class="subsection-header">
                    <td colspan="10"><strong>Capital Outlay</strong></td>
                </tr>
                <tr>
                    <td contenteditable="true">1.9</td>
                    <td contenteditable="true">Youth Center Renovation</td>
                    <td contenteditable="true">Facility improvement project</td>
                    <td contenteditable="true">Modernized youth facility</td>
                    <td contenteditable="true">Percentage of renovation completed</td>
                    <td contenteditable="true">April 15, 2025 to September 15, 2025</td>
                    <td contenteditable="true" class="number">0.00</td>
                    <td contenteditable="true" class="number">120,000.00</td>
                    <td contenteditable="true" class="number">120,000.00</td>
                    <td contenteditable="true">SK Facilities Committee</td>
                </tr>

                <tr class="subsection-header">
                    <td colspan="10"><strong>B. SK YOUTH DEVELOPMENT AND EMPOWERMENT PROGRAMS</strong></td>
                </tr>
                <tr class="category-header">
                    <td colspan="10"><strong>A. Equitable Access to Quality Education</strong></td>
                </tr>
                <tr>
                    <td contenteditable="true">2.1</td>
                    <td contenteditable="true">• Academic Scholarship Program<br>• Tutorial Services<br>• Learning Materials Distribution</td>
                    <td contenteditable="true">Educational support initiatives</td>
                    <td contenteditable="true">Improved academic performance</td>
                    <td contenteditable="true">Number of scholars assisted</td>
                    <td contenteditable="true">June 01, 2025 to December 20, 2025</td>
                    <td contenteditable="true" class="number">85,000.00</td>
                    <td contenteditable="true" class="number">0.00</td>
                    <td contenteditable="true" class="number">85,000.00</td>
                    <td contenteditable="true">SK Education Committee</td>
                </tr>
                <tr>
                    <td contenteditable="true">2.2</td>
                    <td contenteditable="true">STEM Workshop Series</td>
                    <td contenteditable="true">Science and technology education</td>
                    <td contenteditable="true">Enhanced STEM knowledge</td>
                    <td contenteditable="true">Number of workshop participants</td>
                    <td contenteditable="true">July 15, 2025 to October 15, 2025</td>
                    <td contenteditable="true" class="number">42,000.00</td>
                    <td contenteditable="true" class="number">0.00</td>
                    <td contenteditable="true" class="number">42,000.00</td>
                    <td contenteditable="true">SK STEM Team</td>
                </tr>
                <tr>
                    <td contenteditable="true">2.3</td>
                    <td contenteditable="true">Career Guidance Program</td>
                    <td contenteditable="true">Career counseling and planning</td>
                    <td contenteditable="true">Informed career choices</td>
                    <td contenteditable="true">Number of counseled students</td>
                    <td contenteditable="true">August 01, 2025 to November 30, 2025</td>
                    <td contenteditable="true" class="number">38,000.00</td>
                    <td contenteditable="true" class="number">0.00</td>
                    <td contenteditable="true" class="number">38,000.00</td>
                    <td contenteditable="true">SK Career Services</td>
                </tr>

                <tr class="category-header">
                    <td colspan="10"><strong>B. Environmental Protection</strong></td>
                </tr>
                <tr>
                    <td contenteditable="true">3.1</td>
                    <td contenteditable="true">Green Initiative Project<br>• Community Garden Development</td>
                    <td contenteditable="true">Environmental conservation activities</td>
                    <td contenteditable="true">Sustainable environmental practices</td>
                    <td contenteditable="true">Number of green spaces created</td>
                    <td contenteditable="true">May 01, 2025 to October 31, 2025</td>
                    <td contenteditable="true" class="number">32,000.00</td>
                    <td contenteditable="true" class="number">0.00</td>
                    <td contenteditable="true" class="number">32,000.00</td>
                    <td contenteditable="true">SK Environment Team</td>
                </tr>
                <tr>
                    <td contenteditable="true">3.2</td>
                    <td contenteditable="true">Marine Conservation Program</td>
                    <td contenteditable="true">Ocean protection initiatives</td>
                    <td contenteditable="true">Preserved marine ecosystems</td>
                    <td contenteditable="true">Number of conservation activities</td>
                    <td contenteditable="true">June 15, 2025 to December 15, 2025</td>
                    <td contenteditable="true" class="number">28,000.00</td>
                    <td contenteditable="true" class="number">0.00</td>
                    <td contenteditable="true" class="number">28,000.00</td>
                    <td contenteditable="true">SK Marine Conservation</td>
                </tr>

                <tr class="category-header">
                    <td colspan="10"><strong>C. Disaster Risk Reduction and Resiliency</strong></td>
                </tr>
                <tr>
                    <td contenteditable="true">4.1</td>
                    <td contenteditable="true">Emergency Response Training (Equipment and Logistics)</td>
                    <td contenteditable="true">Disaster preparedness education</td>
                    <td contenteditable="true">Trained emergency responders</td>
                    <td contenteditable="true">Number of certified responders</td>
                    <td contenteditable="true">July 01, 2025 to November 30, 2025</td>
                    <td contenteditable="true" class="number">35,000.00</td>
                    <td contenteditable="true" class="number">0.00</td>
                    <td contenteditable="true" class="number">35,000.00</td>
                    <td contenteditable="true">SK Emergency Response</td>
                </tr>
                <tr>
                    <td contenteditable="true">4.2</td>
                    <td contenteditable="true">Community Relief Operations</td>
                    <td contenteditable="true">Emergency assistance program</td>
                    <td contenteditable="true">Provided timely relief support</td>
                    <td contenteditable="true">Number of families assisted</td>
                    <td contenteditable="true">August 01, 2025 to December 31, 2025</td>
                    <td contenteditable="true" class="number">30,000.00</td>
                    <td contenteditable="true" class="number">0.00</td>
                    <td contenteditable="true" class="number">30,000.00</td>
                    <td contenteditable="true">SK Relief Operations</td>
                </tr>

                <tr class="category-header">
                    <td colspan="10"><strong>D. Youth Employment and Livelihood</strong></td>
                </tr>
                <tr>
                    <td contenteditable="true">5.1</td>
                    <td contenteditable="true">Entrepreneurship Development Program</td>
                    <td contenteditable="true">Business skills training</td>
                    <td contenteditable="true">Established youth enterprises</td>
                    <td contenteditable="true">Number of businesses started</td>
                    <td contenteditable="true">September 01, 2025 to December 15, 2025</td>
                    <td contenteditable="true" class="number">45,000.00</td>
                    <td contenteditable="true" class="number">0.00</td>
                    <td contenteditable="true" class="number">45,000.00</td>
                    <td contenteditable="true">SK Livelihood Program</td>
                </tr>

                <tr class="category-header">
                    <td colspan="10"><strong>E. Health</strong></td>
                </tr>
                <tr>
                    <td contenteditable="true">6.1</td>
                    <td contenteditable="true">Mental Health Awareness Campaign</td>
                    <td contenteditable="true">Psychological support services</td>
                    <td contenteditable="true">Improved mental wellness</td>
                    <td contenteditable="true">Number of counseling sessions</td>
                    <td contenteditable="true">October 01, 2025 to December 20, 2025</td>
                    <td contenteditable="true" class="number">32,000.00</td>
                    <td contenteditable="true" class="number">0.00</td>
                    <td contenteditable="true" class="number">32,000.00</td>
                    <td contenteditable="true">SK Health Services</td>
                </tr>

                <tr class="category-header">
                    <td colspan="10"><strong>F. Anti-Drug and Peace and Order</strong></td>
                </tr>
                <tr>
                    <td contenteditable="true">7.1</td>
                    <td contenteditable="true">Substance Abuse Prevention Workshop</td>
                    <td contenteditable="true">Anti-drug education program</td>
                    <td contenteditable="true">Drug-free youth community</td>
                    <td contenteditable="true">Number of workshop attendees</td>
                    <td contenteditable="true">November 01, 2025 to December 10, 2025</td>
                    <td contenteditable="true" class="number">25,000.00</td>
                    <td contenteditable="true" class="number">0.00</td>
                    <td contenteditable="true" class="number">25,000.00</td>
                    <td contenteditable="true">SK Anti-Drug Campaign</td>
                </tr>

                <tr class="category-header">
                    <td colspan="10"><strong>G. Gender Sensitivity</strong></td>
                </tr>
                <tr>
                    <td contenteditable="true">8.1</td>
                    <td contenteditable="true">Gender Equality Advocacy</td>
                    <td contenteditable="true">Gender sensitivity education</td>
                    <td contenteditable="true">Gender-inclusive community</td>
                    <td contenteditable="true">Number of advocacy campaigns</td>
                    <td contenteditable="true">October 15, 2025 to December 05, 2025</td>
                    <td contenteditable="true" class="number">18,000.00</td>
                    <td contenteditable="true" class="number">0.00</td>
                    <td contenteditable="true" class="number">18,000.00</td>
                    <td contenteditable="true">SK Gender Advocacy</td>
                </tr>

                <tr class="category-header">
                    <td colspan="10"><strong>H. Feeding Program for KK Members</strong></td>
                </tr>
                <tr>
                    <td contenteditable="true">9.1</td>
                    <td contenteditable="true">Nutrition Enhancement Program</td>
                    <td contenteditable="true">Health and nutrition support</td>
                    <td contenteditable="true">Improved youth nutrition</td>
                    <td contenteditable="true">Number of beneficiaries served</td>
                    <td contenteditable="true">November 01, 2025 to December 18, 2025</td>
                    <td contenteditable="true" class="number">35,000.00</td>
                    <td contenteditable="true" class="number">0.00</td>
                    <td contenteditable="true" class="number">35,000.00</td>
                    <td contenteditable="true">SK Nutrition Program</td>
                </tr>

                <tr class="category-header">
                    <td colspan="10"><strong>I. Sports Development</strong></td>
                </tr>
                <tr>
                    <td contenteditable="true">10.1</td>
                    <td contenteditable="true">Youth Athletics Development</td>
                    <td contenteditable="true">Comprehensive sports training</td>
                    <td contenteditable="true">Developed athletic skills</td>
                    <td contenteditable="true">Number of athletes trained</td>
                    <td contenteditable="true">September 15, 2025 to December 20, 2025</td>
                    <td contenteditable="true" class="number">180,000.00</td>
                    <td contenteditable="true" class="number">0.00</td>
                    <td contenteditable="true" class="number">180,000.00</td>
                    <td contenteditable="true">SK Athletics Department</td>
                </tr>

                <tr class="category-header">
                    <td colspan="10"><strong>J. Other Programs</strong></td>
                </tr>
                <tr>
                    <td contenteditable="true">11.1</td>
                    <td contenteditable="true">Youth Congress</td>
                    <td contenteditable="true">Annual youth assembly</td>
                    <td contenteditable="true">Successful youth congress</td>
                    <td contenteditable="true">Number of delegates</td>
                    <td contenteditable="true">December 01, 2025 to December 15, 2025</td>
                    <td contenteditable="true" class="number">28,000.00</td>
                    <td contenteditable="true" class="number">0.00</td>
                    <td contenteditable="true" class="number">28,000.00</td>
                    <td contenteditable="true">SK Congress Committee</td>
                </tr>
                <tr>
                    <td contenteditable="true">11.2</td>
                    <td contenteditable="true">Cultural Heritage Festival</td>
                    <td contenteditable="true">Cultural preservation activities</td>
                    <td contenteditable="true">Celebrated cultural heritage</td>
                    <td contenteditable="true">Number of cultural activities</td>
                    <td contenteditable="true">November 20, 2025 to December 10, 2025</td>
                    <td contenteditable="true" class="number">38,000.00</td>
                    <td contenteditable="true" class="number">0.00</td>
                    <td contenteditable="true" class="number">38,000.00</td>
                    <td contenteditable="true">SK Cultural Committee</td>
                </tr>
                <tr>
                    <td contenteditable="true">11.3</td>
                    <td contenteditable="true">Youth Innovation Expo</td>
                    <td contenteditable="true">Technology and innovation showcase</td>
                    <td contenteditable="true">Promoted youth innovation</td>
                    <td contenteditable="true">Number of innovations presented</td>
                    <td contenteditable="true">December 05, 2025 to December 22, 2025</td>
                    <td contenteditable="true" class="number">42,000.00</td>
                    <td contenteditable="true" class="number">0.00</td>
                    <td contenteditable="true" class="number">42,000.00</td>
                    <td contenteditable="true">SK Innovation Team</td>
                </tr>

                <tr class="total-row">
                    <td colspan="6"><strong>TOTAL</strong></td>
                    <td class="number abyip-mooe-total"><strong>933,000.00</strong></td>
                    <td class="number abyip-co-total"><strong>185,000.00</strong></td>
                    <td class="number abyip-grand-total"><strong>1,118,000.00</strong></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>

    <footer class="document-footer abyip-doc-footer">
        <div class="signature-blocks">
            <div class="signature-left">
                <p>Prepared by:</p>
                <p contenteditable="true" class="signature-name">Name ng SK Kupitan</p>
                <p contenteditable="true" class="signature-title">SK Chairperson</p>
            </div>
            <div class="signature-right">
                <p>Approved by:</p>
                <p contenteditable="true" class="signature-name">Name ng Barnggay Kupitan</p>
                <p contenteditable="true" class="signature-title">Barangay Chairman</p>
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
