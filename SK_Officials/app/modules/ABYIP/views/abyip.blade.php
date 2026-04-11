<!DOCTYPE html>
<html lang="en">
<head>
  <title>Annual Barangay Youth Investment Program (ABYIP) 2025</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @vite([
        'app/modules/layout/css/header.css',
        'app/modules/layout/css/sidebar.css',
        'app/modules/ABYIP/assets/css/abyip.css'
    ])
</head>
<body>

<!-- ================= HEADER ================= -->
@include('layout::header')

<!-- ================= SIDEBAR ================= -->
@include('layout::sidebar')

<!-- ================= MAIN CONTENT ================= -->
<main class="main-content">
    <div class="document-container">
        <!-- Page Header Section -->
        <section class="page-header-section">
            <div class="page-header-left">
                <h1 class="page-title">Annual Barangay Youth Investment Program (ABYIP)</h1>
            </div>
            <div class="page-header-right">
                <button type="button" class="btn primary-btn" id="addAbyipBtn">+ Create ABYIP</button>
            </div>
        </section>

        

        <!-- ABYIP Records Table -->
        <section class="page-content-section">
            <div class="section-heading-row">
                <h2 class="section-title">ABYIP Records</h2>
            </div>

            <div class="table-card">
                <div class="table-wrapper">
                    <table class="abyip-table" id="recordsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Date Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="recordsTableBody">
                            <!-- Records will be populated here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <button onclick="window.print()" class="print-btn">Print Document</button>
            <button onclick="printRecords()" class="print-records-btn">Print Records</button>
        </div>
    </div>

    <!-- ABYIP Modal -->
    <div class="modal-overlay" id="abyipModal">
        <div class="modal-container" id="modalContainer">
            <div class="modal-header">
                <h3>Create Annual Barangay Youth Investment Program (ABYIP)</h3>
                <div class="modal-controls">
                    <button class="modal-btn maximize-btn" id="maximizeToggleBtn" onclick="toggleMaximize()" title="Maximize">□</button>
                    <button class="modal-btn close-btn" onclick="closeAbyipModal()" title="Close">×</button>
                </div>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Modal Document Header -->
                <header class="modal-document-header">
                    <div class="sk-header">
                        <p>SANGGUNIANG KABATAAN NG CALIOS</p>
                    </div>
                    <div class="document-title">
                        <h1>ANNUAL BARANGAY YOUTH INVESTMENT PROGRAM (ABYIP)</h1>
                        <h2>CY 2025</h2>
                    </div>
                    <div class="budget-info">
                        <p>Barangay Estimated Budget: ₱14,199,466.00</p>
                        <p>Sangguniang Kabataan Fund (10%): ₱1,419,946.60</p>
                    </div>
                </header>

                <!-- ABYIP Form Table -->
                <div class="table-wrapper">
                    <table class="abyip-table" id="abyipModalTable">
                    <thead>
                        <tr>
                            <th rowspan="2">Code</th>
                            <th rowspan="2">PPAs (Programs/Projects/Activities)</th>
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
                        <!-- RECEIPTS PROGRAM -->
                        <tr class="section-header">
                            <td colspan="10"><strong>RECEIPTS PROGRAM</strong></td>
                        </tr>
                        
                        <!-- EXPENDITURE PROGRAM -->
                        <tr class="section-header">
                            <td colspan="10"><strong>EXPENDITURE PROGRAM</strong></td>
                        </tr>
                        
                        <!-- A. GENERAL ADMINISTRATION PROGRAM -->
                        <tr class="subsection-header">
                            <td colspan="10"><strong>A. GENERAL ADMINISTRATION PROGRAM</strong></td>
                        </tr>
                        
                        <tr>
                            <td contenteditable="true">1.1</td>
                            <td contenteditable="true">Honoraria</td>
                            <td contenteditable="true">Monthly honorarium for SK officials</td>
                            <td contenteditable="true">Compensated SK officials for service rendered</td>
                            <td contenteditable="true">Number of SK officials receiving honoraria</td>
                            <td contenteditable="true">January 01, 2025 to December 31, 2025</td>
                            <td contenteditable="true" class="number">240,000.00</td>
                            <td contenteditable="true" class="number">0.00</td>
                            <td contenteditable="true" class="number">240,000.00</td>
                            <td contenteditable="true">SK Chairman</td>
                        </tr>
                        
                        <tr>
                            <td contenteditable="true">1.2</td>
                            <td contenteditable="true">Travel Expenses</td>
                            <td contenteditable="true">Transportation allowance for official travels</td>
                            <td contenteditable="true">Conducted official travels and meetings</td>
                            <td contenteditable="true">Number of official travels conducted</td>
                            <td contenteditable="true">January 01, 2025 to December 31, 2025</td>
                            <td contenteditable="true" class="number">50,000.00</td>
                            <td contenteditable="true" class="number">0.00</td>
                            <td contenteditable="true" class="number">50,000.00</td>
                            <td contenteditable="true">SK Treasurer</td>
                        </tr>
                        
                        <tr>
                            <td contenteditable="true">1.3</td>
                            <td contenteditable="true">Training and Seminar Expenses</td>
                            <td contenteditable="true">Registration fees, materials for trainings</td>
                            <td contenteditable="true">Enhanced knowledge and skills of SK officials</td>
                            <td contenteditable="true">Number of trainings attended</td>
                            <td contenteditable="true">January 01, 2025 to December 31, 2025</td>
                            <td contenteditable="true" class="number">30,000.00</td>
                            <td contenteditable="true" class="number">0.00</td>
                            <td contenteditable="true" class="number">30,000.00</td>
                            <td contenteditable="true">SK Council</td>
                        </tr>
                        
                        <tr>
                            <td contenteditable="true">1.4</td>
                            <td contenteditable="true">Laptop/Computer</td>
                            <td contenteditable="true">Office equipment for SK operations</td>
                            <td contenteditable="true">Improved office efficiency and documentation</td>
                            <td contenteditable="true">Number of functional equipment</td>
                            <td contenteditable="true">January 01, 2025 to December 31, 2025</td>
                            <td contenteditable="true" class="number">0.00</td>
                            <td contenteditable="true" class="number">45,000.00</td>
                            <td contenteditable="true" class="number">45,000.00</td>
                            <td contenteditable="true">SK Chairman</td>
                        </tr>
                        
                        <tr>
                            <td contenteditable="true">1.5</td>
                            <td contenteditable="true">Clothing/Uniform Allowance</td>
                            <td contenteditable="true">Official uniforms for SK officials</td>
                            <td contenteditable="true">Professional appearance of SK officials</td>
                            <td contenteditable="true">Number of uniforms provided</td>
                            <td contenteditable="true">January 01, 2025 to December 31, 2025</td>
                            <td contenteditable="true" class="number">20,000.00</td>
                            <td contenteditable="true" class="number">0.00</td>
                            <td contenteditable="true" class="number">20,000.00</td>
                            <td contenteditable="true">SK Treasurer</td>
                        </tr>
                        
                        <tr>
                            <td contenteditable="true">1.6</td>
                            <td contenteditable="true">Office Supplies</td>
                            <td contenteditable="true">Paper, pens, ink, and other office materials</td>
                            <td contenteditable="true">Adequate office supplies for daily operations</td>
                            <td contenteditable="true">Percentage of operations without supply interruption</td>
                            <td contenteditable="true">January 01, 2025 to December 31, 2025</td>
                            <td contenteditable="true" class="number">25,000.00</td>
                            <td contenteditable="true" class="number">0.00</td>
                            <td contenteditable="true" class="number">25,000.00</td>
                            <td contenteditable="true">SK Secretary</td>
                        </tr>
                        
                        <tr>
                            <td contenteditable="true">1.7</td>
                            <td contenteditable="true">Membership Dues</td>
                            <td contenteditable="true">Membership fees for organizations</td>
                            <td contenteditable="true">Active participation in youth organizations</td>
                            <td contenteditable="true">Number of active memberships</td>
                            <td contenteditable="true">January 01, 2025 to December 31, 2025</td>
                            <td contenteditable="true" class="number">10,000.00</td>
                            <td contenteditable="true" class="number">0.00</td>
                            <td contenteditable="true" class="number">10,000.00</td>
                            <td contenteditable="true">SK Council</td>
                        </tr>
                        
                        <tr>
                            <td contenteditable="true">1.8</td>
                            <td contenteditable="true">Fidelity Bond</td>
                            <td contenteditable="true">Insurance for SK officials</td>
                            <td contenteditable="true">Protected SK officials from liabilities</td>
                            <td contenteditable="true">Number of officials covered</td>
                            <td contenteditable="true">January 01, 2025 to December 31, 2025</td>
                            <td contenteditable="true" class="number">5,000.00</td>
                            <td contenteditable="true" class="number">0.00</td>
                            <td contenteditable="true" class="number">5,000.00</td>
                            <td contenteditable="true">SK Treasurer</td>
                        </tr>
                        
                        <tr>
                            <td contenteditable="true">1.9</td>
                            <td contenteditable="true">Capital Outlay (Rehabilitation of SK Hall)</td>
                            <td contenteditable="true">Place to hold SK official meetings</td>
                            <td contenteditable="true">Functional SK Hall for meetings</td>
                            <td contenteditable="true">Percentage of SK Hall functionality</td>
                            <td contenteditable="true">January 01, 2025 to December 31, 2025</td>
                            <td contenteditable="true" class="number">0.00</td>
                            <td contenteditable="true" class="number">92,000.00</td>
                            <td contenteditable="true" class="number">92,000.00</td>
                            <td contenteditable="true">Sangguniang Kabataan Council</td>
                        </tr>
                        
                        <!-- B. SK YOUTH DEVELOPMENT AND EMPOWERMENT PROGRAMS -->
                        <tr class="subsection-header">
                            <td colspan="10"><strong>B. SK YOUTH DEVELOPMENT AND EMPOWERMENT PROGRAMS</strong></td>
                        </tr>
                        
                        <!-- A. Education -->
                        <tr class="category-header">
                            <td colspan="10"><strong>A. Equitable Access to Quality Education</strong></td>
                        </tr>
                        
                        <tr>
                            <td contenteditable="true">2.1</td>
                            <td contenteditable="true">Support to ALS and RIC Students for Educational Assistance</td>
                            <td contenteditable="true">Provide school supplies to ALS Students and elementary, high school and college Students</td>
                            <td contenteditable="true">Increased number of youth enrollee in schools/Decreased the number of out-of-school youth (OSY)</td>
                            <td contenteditable="true">Percentage increase in the number of youth enrollee in schools/Percentage decrease in the number of OSY's</td>
                            <td contenteditable="true">January 01, 2025 to December 31, 2025</td>
                            <td contenteditable="true" class="number">12,000.00</td>
                            <td contenteditable="true" class="number">0.00</td>
                            <td contenteditable="true" class="number">12,000.00</td>
                            <td contenteditable="true">Sangguniang Kabataan Council/ALS</td>
                        </tr>
                        
                        <tr>
                            <td contenteditable="true">2.2</td>
                            <td contenteditable="true">Support to Elementary and Daycare</td>
                            <td contenteditable="true">Educational assistance for elementary and daycare students</td>
                            <td contenteditable="true">Improved learning conditions for young students</td>
                            <td contenteditable="true">Number of students assisted</td>
                            <td contenteditable="true">January 01, 2025 to December 31, 2025</td>
                            <td contenteditable="true" class="number">150,000.00</td>
                            <td contenteditable="true" class="number">0.00</td>
                            <td contenteditable="true" class="number">150,000.00</td>
                            <td contenteditable="true">Sangguniang Kabataan Council/ALS</td>
                        </tr>
                        
                        <tr>
                            <td contenteditable="true">2.3</td>
                            <td contenteditable="true">Educational Assistance</td>
                            <td contenteditable="true">Financial support for deserving students</td>
                            <td contenteditable="true">Reduced financial burden on students</td>
                            <td contenteditable="true">Number of scholars supported</td>
                            <td contenteditable="true">January 01, 2025 to December 31, 2025</td>
                            <td contenteditable="true" class="number">13,000.00</td>
                            <td contenteditable="true" class="number">0.00</td>
                            <td contenteditable="true" class="number">13,000.00</td>
                            <td contenteditable="true">SK Council</td>
                        </tr>
                        
                        <!-- B. Environmental Protection -->
                        <tr class="category-header">
                            <td colspan="10"><strong>B. Environmental Protection</strong></td>
                        </tr>
                        
                        <tr>
                            <td contenteditable="true">3.1</td>
                            <td contenteditable="true">Clean-Up Drive</td>
                            <td contenteditable="true">Honorarium is given for the proper pay for services rendered in the Clean Up Drive</td>
                            <td contenteditable="true">Increased number of youth organizations participating in environmental protection campaigns and clean-up drive activity</td>
                            <td contenteditable="true">Percentage increase in the number of youth organizations participated in environmental protection campaigns and clean-up drive activity</td>
                            <td contenteditable="true">January 01, 2025 to December 31, 2025</td>
                            <td contenteditable="true" class="number">60,000.00</td>
                            <td contenteditable="true" class="number">0.00</td>
                            <td contenteditable="true" class="number">60,000.00</td>
                            <td contenteditable="true">Sangguniang Kabataan Council</td>
                        </tr>
                        
                        <tr>
                            <td contenteditable="true">3.2</td>
                            <td contenteditable="true">Tree Planting</td>
                            <td contenteditable="true">Environmental conservation through tree planting activities</td>
                            <td contenteditable="true">Improved environmental conditions</td>
                            <td contenteditable="true">Number of trees planted</td>
                            <td contenteditable="true">January 01, 2025 to December 31, 2025</td>
                            <td contenteditable="true" class="number">15,000.00</td>
                            <td contenteditable="true" class="number">0.00</td>
                            <td contenteditable="true" class="number">15,000.00</td>
                            <td contenteditable="true">SK Council</td>
                        </tr>
                        
                        <!-- C. Disaster Risk Reduction -->
                        <tr class="category-header">
                            <td colspan="10"><strong>C. Disaster Risk Reduction and Resiliency</strong></td>
                        </tr>
                        
                        <tr>
                            <td contenteditable="true">4.1</td>
                            <td contenteditable="true">Training on Disaster Preparedness for Organization of Youth Volunteer Groups (Food and Accommodations)</td>
                            <td contenteditable="true">Disaster preparedness refers to measures taken to prepare for and reduce the effects of disaster. That is, to predict and where possible, prevent disasters, mitigate their impact on vulnerable populations.</td>
                            <td contenteditable="true">Enhanced disaster preparedness among youth volunteers</td>
                            <td contenteditable="true">Number of trained youth volunteers</td>
                            <td contenteditable="true">January 01, 2025 to December 31, 2025</td>
                            <td contenteditable="true" class="number">10,000.00</td>
                            <td contenteditable="true" class="number">0.00</td>
                            <td contenteditable="true" class="number">10,000.00</td>
                            <td contenteditable="true">Sangguniang Kabataan Council</td>
                        </tr>
                        
                        <tr>
                            <td contenteditable="true">4.2</td>
                            <td contenteditable="true">Distribution of Relief Goods for KK Members</td>
                            <td contenteditable="true">Emergency assistance during disasters</td>
                            <td contenteditable="true">Timely distribution of relief goods</td>
                            <td contenteditable="true">Number of beneficiaries served</td>
                            <td contenteditable="true">January 01, 2025 to December 31, 2025</td>
                            <td contenteditable="true" class="number">20,000.00</td>
                            <td contenteditable="true" class="number">0.00</td>
                            <td contenteditable="true" class="number">20,000.00</td>
                            <td contenteditable="true">Sangguniang Kabataan Council</td>
                        </tr>
                        
                        <!-- D. Livelihood -->
                        <tr class="category-header">
                            <td colspan="10"><strong>D. Youth Employment and Livelihood</strong></td>
                        </tr>
                        
                        <tr>
                            <td contenteditable="true">5.1</td>
                            <td contenteditable="true">Livelihood Training, Food and other supplies</td>
                            <td contenteditable="true">Skills development for youth employment</td>
                            <td contenteditable="true">Increased number of skilled and employed youth</td>
                            <td contenteditable="true">Percentage increase in number of skilled and employed youth</td>
                            <td contenteditable="true">January 01, 2025 to December 31, 2025</td>
                            <td contenteditable="true" class="number">20,000.00</td>
                            <td contenteditable="true" class="number">0.00</td>
                            <td contenteditable="true" class="number">20,000.00</td>
                            <td contenteditable="true">Sangguniang Kabataan Council</td>
                        </tr>
                        
                        <!-- E. Health -->
                        <tr class="category-header">
                            <td colspan="10"><strong>E. Health</strong></td>
                        </tr>
                        
                        <tr>
                            <td contenteditable="true">6.1</td>
                            <td contenteditable="true">Medicines/Medical Equipment</td>
                            <td contenteditable="true">Campaigning Materials for Anti-Drugs such as Leaflets, posters, tarpaulins</td>
                            <td contenteditable="true">Improved health awareness and access to medical supplies</td>
                            <td contenteditable="true">Number of health campaigns conducted</td>
                            <td contenteditable="true">January 01, 2025 to December 31, 2025</td>
                            <td contenteditable="true" class="number">30,000.00</td>
                            <td contenteditable="true" class="number">0.00</td>
                            <td contenteditable="true" class="number">30,000.00</td>
                            <td contenteditable="true">Sangguniang Kabataan Council/BADAC</td>
                        </tr>
                        
                        <!-- F. Anti-Drug -->
                        <tr class="category-header">
                            <td colspan="10"><strong>F. Anti-Drug and Peace and Order</strong></td>
                        </tr>
                        
                        <tr>
                            <td contenteditable="true">7.1</td>
                            <td contenteditable="true">Orientation for Anti-Drug and Physical Abuse, Foods and Accommodations</td>
                            <td contenteditable="true">Awareness campaigns against illegal drugs and abuse</td>
                            <td contenteditable="true">Decreased number of drug-dependent youth and youth who tried using illegal drugs</td>
                            <td contenteditable="true">Number of drug prevention education/information campaigns conducted</td>
                            <td contenteditable="true">January 01, 2025 to December 31, 2025</td>
                            <td contenteditable="true" class="number">10,000.00</td>
                            <td contenteditable="true" class="number">0.00</td>
                            <td contenteditable="true" class="number">10,000.00</td>
                            <td contenteditable="true">Sangguniang Kabataan Council</td>
                        </tr>
                        
                        <!-- G. Gender Sensitivity -->
                        <tr class="category-header">
                            <td colspan="10"><strong>G. Gender Sensitivity</strong></td>
                        </tr>
                        
                        <tr>
                            <td contenteditable="true">8.1</td>
                            <td contenteditable="true">GAD and VAWC Orientation</td>
                            <td contenteditable="true">Gender awareness and violence against women and children prevention</td>
                            <td contenteditable="true">Increased awareness on gender sensitivity</td>
                            <td contenteditable="true">Number of GAD activities conducted</td>
                            <td contenteditable="true">January 01, 2025 to December 31, 2025</td>
                            <td contenteditable="true" class="number">15,000.00</td>
                            <td contenteditable="true" class="number">0.00</td>
                            <td contenteditable="true" class="number">15,000.00</td>
                            <td contenteditable="true">SK Council</td>
                        </tr>
                        
                        <!-- H. Feeding Program -->
                        <tr class="category-header">
                            <td colspan="10"><strong>H. Feeding Program for KK Members</strong></td>
                        </tr>
                        
                        <tr>
                            <td contenteditable="true">9.1</td>
                            <td contenteditable="true">Feeding Program</td>
                            <td contenteditable="true">Nutritional support for KK members</td>
                            <td contenteditable="true">Improved nutrition among youth members</td>
                            <td contenteditable="true">Number of beneficiaries</td>
                            <td contenteditable="true">January 01, 2025 to December 31, 2025</td>
                            <td contenteditable="true" class="number">25,000.00</td>
                            <td contenteditable="true" class="number">0.00</td>
                            <td contenteditable="true" class="number">25,000.00</td>
                            <td contenteditable="true">SK Council</td>
                        </tr>
                        
                        <!-- I. Sports Development -->
                        <tr class="category-header">
                            <td colspan="10"><strong>I. Sports Development</strong></td>
                        </tr>
                        
                        <tr>
                            <td contenteditable="true">10.1</td>
                            <td contenteditable="true">Sports Tournament and Equipment</td>
                            <td contenteditable="true">Sports development activities and equipment purchase</td>
                            <td contenteditable="true">Enhanced youth participation in sports</td>
                            <td contenteditable="true">Number of sports activities conducted</td>
                            <td contenteditable="true">January 01, 2025 to December 31, 2025</td>
                            <td contenteditable="true" class="number">50,000.00</td>
                            <td contenteditable="true" class="number">0.00</td>
                            <td contenteditable="true" class="number">50,000.00</td>
                            <td contenteditable="true">SK Council</td>
                        </tr>
                        
                        <!-- J. Other Programs -->
                        <tr class="category-header">
                            <td colspan="10"><strong>J. Other Programs</strong></td>
                        </tr>
                        
                        <tr>
                            <td contenteditable="true">11.1</td>
                            <td contenteditable="true">KK Assembly</td>
                            <td contenteditable="true">Regular assembly of Kabataan Barangay members</td>
                            <td contenteditable="true">Conducted regular KK assemblies</td>
                            <td contenteditable="true">Number of assemblies conducted</td>
                            <td contenteditable="true">January 01, 2025 to December 31, 2025</td>
                            <td contenteditable="true" class="number">20,000.00</td>
                            <td contenteditable="true" class="number">0.00</td>
                            <td contenteditable="true" class="number">20,000.00</td>
                            <td contenteditable="true">SK Council</td>
                        </tr>
                        
                        <tr>
                            <td contenteditable="true">11.2</td>
                            <td contenteditable="true">Barangay Day</td>
                            <td contenteditable="true">Celebration of Barangay Day with youth participation</td>
                            <td contenteditable="true">Successful Barangay Day celebration</td>
                            <td contenteditable="true">Number of youth participants</td>
                            <td contenteditable="true">January 01, 2025 to December 31, 2025</td>
                            <td contenteditable="true" class="number">30,000.00</td>
                            <td contenteditable="true" class="number">0.00</td>
                            <td contenteditable="true" class="number">30,000.00</td>
                            <td contenteditable="true">SK Council</td>
                        </tr>
                        
                        <tr>
                            <td contenteditable="true">11.3</td>
                            <td contenteditable="true">Youth Week</td>
                            <td contenteditable="true">Week-long celebration of youth activities</td>
                            <td contenteditable="true">Successful Youth Week celebration</td>
                            <td contenteditable="true">Number of activities conducted</td>
                            <td contenteditable="true">January 01, 2025 to December 31, 2025</td>
                            <td contenteditable="true" class="number">40,000.00</td>
                            <td contenteditable="true" class="number">0.00</td>
                            <td contenteditable="true" class="number">40,000.00</td>
                            <td contenteditable="true">SK Council</td>
                        </tr>
                        
                        <!-- Total Row -->
                        <tr class="total-row">
                            <td colspan="7"><strong>TOTAL</strong></td>
                            <td class="number"><strong>0.00</strong></td>
                            <td class="number"><strong>137,000.00</strong></td>
                            <td class="number"><strong>1,419,946.60</strong></td>
                            <td></td>
                        </tr>
                    </tbody>
                    </table>
                </div>

                <!-- Modal Document Footer -->
                <footer class="modal-document-footer">
                    <div class="signature-blocks">
                        <div class="signature-left">
                            <p>Prepared by:</p>
                            <p contenteditable="true" class="signature-name">HON. KARIM Z. NEQUINTO</p>
                            <p contenteditable="true" class="signature-title">SK Chairperson</p>
                        </div>
                        <div class="signature-right">
                            <p>Approved by:</p>
                            <p contenteditable="true" class="signature-name">HON. LAURA P. OBLIGACION</p>
                            <p contenteditable="true" class="signature-title">Barangay Chairman</p>
                        </div>
                    </div>
                </footer>
            </div>
            <div class="modal-footer">
                <button class="btn-cancel" onclick="closeAbyipModal()">Cancel</button>
                <button class="btn-save" onclick="saveAbyip()">Save ABYIP</button>
            </div>
        </div>
    </div>

    <!-- Minimized Modal Bar -->
    <div class="minimized-modal-bar" id="minimizedModalBar" style="display: none;">
        <span>ABYIP Form - Minimized</span>
        <button onclick="restoreModal()">Restore</button>
    </div>
</main>

<!-- Delete Confirmation Modal -->
<div class="modal-backdrop" id="deleteConfirmModal" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Confirm Delete</h3>
            <div class="modal-window-controls">
                <button type="button" class="modal-close" data-modal-close aria-label="Close">&times;</button>
            </div>
        </div>
        <div class="modal-body">
            <div class="confirmation-content">
                <div class="confirmation-icon">⚠️</div>
                <div class="confirmation-message">
                    <h4>Are you sure you want to delete this ABYIP record?</h4>
                    <p>This action cannot be undone. The record will be permanently removed from the system.</p>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-cancel" onclick="closeDeleteConfirm()">Cancel</button>
            <button type="button" class="btn btn-delete" onclick="confirmDelete()">Delete</button>
        </div>
    </div>
</div>

@vite([
    'app/modules/layout/js/header.js',
    'app/modules/layout/js/sidebar.js',
    'app/modules/ABYIP/assets/js/abyip.js'
])

</body>
</html>
