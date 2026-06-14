@extends('layout::app')

@section('title', 'Archive - SK OnePortal')

@push('styles')
    <link rel="stylesheet" href="{{ url('/modules/archive/css/archive.css') }}">
@endpush

@section('content')
<div style="padding:24px;max-width:1400px;margin:0 auto;">
            <h2 style="font-size:28px;font-weight:800;color:#0d1b4b;margin-bottom:8px;" id="archivePageTitle">Archive Reports</h2>
            <p style="font-size:14px;color:#64748b;margin-bottom:24px;" id="archivePageDescription">Manage deleted and archived reports</p>

            {{-- Deleted Reports Tab --}}
            <div id="section-deleted" style="display:block;">
                
                {{-- Report Type Tabs --}}
                <div style="display:flex;gap:4px;margin-bottom:20px;border-bottom:2px solid #e2e8f0;">
                    <button onclick="switchDeletedReportTab('abyip')" id="deleted-tab-abyip" style="padding:10px 20px;border:none;background:none;font-size:14px;font-weight:600;color:#213F99;border-bottom:2px solid #213F99;margin-bottom:-2px;cursor:pointer;">
                        <i class="fas fa-file-invoice-dollar"></i> ABYIP Report
                    </button>
                    <button onclick="switchDeletedReportTab('accomplishment')" id="deleted-tab-accomplishment" style="padding:10px 20px;border:none;background:none;font-size:14px;font-weight:600;color:#64748b;border-bottom:2px solid transparent;margin-bottom:-2px;cursor:pointer;">
                        <i class="fas fa-trophy"></i> Accomplishment Report
                    </button>
                </div>

                {{-- ABYIP Reports Content --}}
                <div id="deleted-abyip-content" style="display:block;">
                    {{-- Filters --}}
                <div style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;align-items:center;">
                    <select id="deletedAbyipBarangayFilter" onchange="filterDeletedAbyipReports()" style="padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;color:#475569;background:#fff;cursor:pointer;">
                        <option value="all">All Barangays</option>
                        <option value="Alipit">Alipit</option>
                        <option value="Bagumbayan">Bagumbayan</option>
                        <option value="Bubukal">Bubukal</option>
                        <option value="Calios">Calios</option>
                        <option value="Duhat">Duhat</option>
                        <option value="Gatid">Gatid</option>
                        <option value="Jasaan">Jasaan</option>
                        <option value="Labuin">Labuin</option>
                        <option value="Malinao">Malinao</option>
                        <option value="Oogong">Oogong</option>
                        <option value="Pagsawitan">Pagsawitan</option>
                        <option value="Palasan">Palasan</option>
                        <option value="Patimbao">Patimbao</option>
                        <option value="Poblacion I">Poblacion I</option>
                        <option value="Poblacion II">Poblacion II</option>
                        <option value="Poblacion III">Poblacion III</option>
                        <option value="Poblacion IV">Poblacion IV</option>
                        <option value="Poblacion V">Poblacion V</option>
                        <option value="San Jose">San Jose</option>
                        <option value="San Juan">San Juan</option>
                        <option value="San Pablo Norte">San Pablo Norte</option>
                        <option value="San Pablo Sur">San Pablo Sur</option>
                        <option value="Santisima Cruz">Santisima Cruz</option>
                        <option value="Santo Angel Central">Santo Angel Central</option>
                        <option value="Santo Angel Norte">Santo Angel Norte</option>
                        <option value="Santo Angel Sur">Santo Angel Sur</option>
                    </select>
                    <select id="deletedAbyipYearFilter" onchange="filterDeletedAbyipReports()" style="padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;color:#475569;background:#fff;cursor:pointer;">
                        <option value="all">All Years</option>
                        <option value="2026">2026</option>
                        <option value="2025">2025</option>
                        <option value="2024">2024</option>
                        <option value="2023">2023</option>
                    </select>
                    <select id="deletedAbyipTimeFilter" onchange="filterDeletedAbyipReports()" style="padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;color:#475569;background:#fff;cursor:pointer;">
                        <option value="all">All Time</option>
                        <option value="recent">Recent</option>
                        <option value="thismonth">This Month</option>
                    </select>
                    <div style="display:flex;gap:8px;flex:1;max-width:400px;">
                        <input type="text" id="deletedAbyipSearchInput" placeholder="Search reports..." style="flex:1;padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;" onkeypress="if(event.key==='Enter') filterDeletedAbyipReports()">
                        <button onclick="filterDeletedAbyipReports()" style="padding:8px 16px;background:#213F99;color:#fff;border:none;border-radius:8px;font-size:13px;cursor:pointer;transition:background .2s;" onmouseover="this.style.background='#1a3280'" onmouseout="this.style.background='#213F99'">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </div>

                {{-- Deleted Reports Table --}}
                <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
                    <div style="overflow-x:auto;">
                        <table style="width:100%;border-collapse:collapse;">
                            <thead>
                                <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                                    <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:#0d1b4b;">Title</th>
                                    <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:#0d1b4b;">Date Deleted</th>
                                    <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:#0d1b4b;">Date Created</th>
                                    <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:#0d1b4b;">Time Created</th>
                                    <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:#0d1b4b;">Status</th>
                                    <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:#0d1b4b;">Report</th>
                                    <th style="padding:12px 16px;text-align:center;font-size:12px;font-weight:700;color:#0d1b4b;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="deletedAbyipReportsTable">
                                <tr><td colspan="7" style="text-align:center;padding:20px;color:#94a3b8;">No deleted ABYIP reports</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Deleted Reports Pagination --}}
                <div style="display:flex;justify-content:space-between;align-items:center;margin-top:16px;flex-wrap:wrap;gap:12px;">
                    <div style="font-size:13px;color:#64748b;">
                        Showing <span id="deletedStart">1</span> to <span id="deletedEnd">5</span> of <span id="deletedTotal">{{ count($deletedReports) }}</span> reports
                    </div>
                    <div style="display:flex;gap:8px;">
                        <button onclick="previousDeletedPage()" id="deletedPrevBtn" style="padding:8px 12px;border:1px solid #e2e8f0;background:#fff;border-radius:6px;font-size:13px;color:#213F99;cursor:pointer;transition:all .2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#fff'">
                            <i class="fas fa-chevron-left"></i> Previous
                        </button>
                        <button onclick="nextDeletedPage()" id="deletedNextBtn" style="padding:8px 12px;border:1px solid #e2e8f0;background:#fff;border-radius:6px;font-size:13px;color:#213F99;cursor:pointer;transition:all .2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#fff'">
                            Next <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
                </div>

                {{-- Accomplishment Reports Content --}}
                <div id="deleted-accomplishment-content" style="display:none;">
                    {{-- Filters --}}
                <div style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;align-items:center;">
                    <select id="deletedAccomplishmentBarangayFilter" onchange="filterDeletedAccomplishmentReports()" style="padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;color:#475569;background:#fff;cursor:pointer;">
                        <option value="all">All Barangays</option>
                        <option value="Alipit">Alipit</option>
                        <option value="Bagumbayan">Bagumbayan</option>
                        <option value="Bubukal">Bubukal</option>
                        <option value="Calios">Calios</option>
                        <option value="Duhat">Duhat</option>
                        <option value="Gatid">Gatid</option>
                        <option value="Jasaan">Jasaan</option>
                        <option value="Labuin">Labuin</option>
                        <option value="Malinao">Malinao</option>
                        <option value="Oogong">Oogong</option>
                        <option value="Pagsawitan">Pagsawitan</option>
                        <option value="Palasan">Palasan</option>
                        <option value="Patimbao">Patimbao</option>
                        <option value="Poblacion I">Poblacion I</option>
                        <option value="Poblacion II">Poblacion II</option>
                        <option value="Poblacion III">Poblacion III</option>
                        <option value="Poblacion IV">Poblacion IV</option>
                        <option value="Poblacion V">Poblacion V</option>
                        <option value="San Jose">San Jose</option>
                        <option value="San Juan">San Juan</option>
                        <option value="San Pablo Norte">San Pablo Norte</option>
                        <option value="San Pablo Sur">San Pablo Sur</option>
                        <option value="Santisima Cruz">Santisima Cruz</option>
                        <option value="Santo Angel Central">Santo Angel Central</option>
                        <option value="Santo Angel Norte">Santo Angel Norte</option>
                        <option value="Santo Angel Sur">Santo Angel Sur</option>
                    </select>
                    <select id="deletedAccomplishmentYearFilter" onchange="filterDeletedAccomplishmentReports()" style="padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;color:#475569;background:#fff;cursor:pointer;">
                        <option value="all">All Years</option>
                        <option value="2026">2026</option>
                        <option value="2025">2025</option>
                        <option value="2024">2024</option>
                        <option value="2023">2023</option>
                    </select>
                    <select id="deletedAccomplishmentTimeFilter" onchange="filterDeletedAccomplishmentReports()" style="padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;color:#475569;background:#fff;cursor:pointer;">
                        <option value="all">All Time</option>
                        <option value="recent">Recent</option>
                        <option value="thismonth">This Month</option>
                    </select>
                    <div style="display:flex;gap:8px;flex:1;max-width:400px;">
                        <input type="text" id="deletedAccomplishmentSearchInput" placeholder="Search reports..." style="flex:1;padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;" onkeypress="if(event.key==='Enter') filterDeletedAccomplishmentReports()">
                        <button onclick="filterDeletedAccomplishmentReports()" style="padding:8px 16px;background:#213F99;color:#fff;border:none;border-radius:8px;font-size:13px;cursor:pointer;transition:background .2s;" onmouseover="this.style.background='#1a3280'" onmouseout="this.style.background='#213F99'">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </div>
                    {{-- Accomplishment Table --}}
                    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
                        <div style="overflow-x:auto;">
                            <table style="width:100%;border-collapse:collapse;">
                                <thead>
                                    <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                                        <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:#0d1b4b;">Program Title</th>
                                        <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:#0d1b4b;">Date Deleted</th>
                                        <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:#0d1b4b;">Date Created</th>
                                        <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:#0d1b4b;">Description</th>
                                        <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:#0d1b4b;">Committee</th>
                                        <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:#0d1b4b;">Duration</th>
                                        <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:#0d1b4b;">Status</th>
                                        <th style="padding:12px 16px;text-align:center;font-size:12px;font-weight:700;color:#0d1b4b;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="deletedAccomplishmentTable">
                                    <tr><td colspan="8" style="text-align:center;padding:20px;color:#94a3b8;">No deleted accomplishment reports</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Archived Reports Tab --}}
            <div id="section-archived" style="display:none;">
                
                {{-- Report Type Tabs --}}
                <div style="display:flex;gap:4px;margin-bottom:20px;border-bottom:2px solid #e2e8f0;">
                    <button onclick="switchArchivedReportTab('abyip')" id="archived-tab-abyip" style="padding:10px 20px;border:none;background:none;font-size:14px;font-weight:600;color:#213F99;border-bottom:2px solid #213F99;margin-bottom:-2px;cursor:pointer;">
                        <i class="fas fa-file-invoice-dollar"></i> ABYIP Report
                    </button>
                    <button onclick="switchArchivedReportTab('accomplishment')" id="archived-tab-accomplishment" style="padding:10px 20px;border:none;background:none;font-size:14px;font-weight:600;color:#64748b;border-bottom:2px solid transparent;margin-bottom:-2px;cursor:pointer;">
                        <i class="fas fa-trophy"></i> Accomplishment Report
                    </button>
                </div>

                {{-- ABYIP Reports Content --}}
                <div id="archived-abyip-content" style="display:block;">
                    {{-- Filters --}}
                <div style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;align-items:center;">
                    <select id="archivedAbyipBarangayFilter" onchange="filterArchivedAbyipReports()" style="padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;color:#475569;background:#fff;cursor:pointer;">
                        <option value="all">All Barangays</option>
                        <option value="Alipit">Alipit</option>
                        <option value="Bagumbayan">Bagumbayan</option>
                        <option value="Bubukal">Bubukal</option>
                        <option value="Calios">Calios</option>
                        <option value="Duhat">Duhat</option>
                        <option value="Gatid">Gatid</option>
                        <option value="Jasaan">Jasaan</option>
                        <option value="Labuin">Labuin</option>
                        <option value="Malinao">Malinao</option>
                        <option value="Oogong">Oogong</option>
                        <option value="Pagsawitan">Pagsawitan</option>
                        <option value="Palasan">Palasan</option>
                        <option value="Patimbao">Patimbao</option>
                        <option value="Poblacion I">Poblacion I</option>
                        <option value="Poblacion II">Poblacion II</option>
                        <option value="Poblacion III">Poblacion III</option>
                        <option value="Poblacion IV">Poblacion IV</option>
                        <option value="Poblacion V">Poblacion V</option>
                        <option value="San Jose">San Jose</option>
                        <option value="San Juan">San Juan</option>
                        <option value="San Pablo Norte">San Pablo Norte</option>
                        <option value="San Pablo Sur">San Pablo Sur</option>
                        <option value="Santisima Cruz">Santisima Cruz</option>
                        <option value="Santo Angel Central">Santo Angel Central</option>
                        <option value="Santo Angel Norte">Santo Angel Norte</option>
                        <option value="Santo Angel Sur">Santo Angel Sur</option>
                    </select>
                    <select id="archivedAbyipYearFilter" onchange="filterArchivedAbyipReports()" style="padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;color:#475569;background:#fff;cursor:pointer;">
                        <option value="all">All Years</option>
                        <option value="2026">2026</option>
                        <option value="2025">2025</option>
                        <option value="2024">2024</option>
                        <option value="2023">2023</option>
                    </select>
                    <select id="archivedAbyipTimeFilter" onchange="filterArchivedAbyipReports()" style="padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;color:#475569;background:#fff;cursor:pointer;">
                        <option value="all">All Time</option>
                        <option value="recent">Recent</option>
                        <option value="thismonth">This Month</option>
                    </select>
                    <div style="display:flex;gap:8px;flex:1;max-width:400px;">
                        <input type="text" id="archivedAbyipSearchInput" placeholder="Search reports..." style="flex:1;padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;" onkeypress="if(event.key==='Enter') filterArchivedAbyipReports()">
                        <button onclick="filterArchivedAbyipReports()" style="padding:8px 16px;background:#213F99;color:#fff;border:none;border-radius:8px;font-size:13px;cursor:pointer;transition:background .2s;" onmouseover="this.style.background='#1a3280'" onmouseout="this.style.background='#213F99'">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </div>

                {{-- Archived Reports Table --}}
                <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
                    <div style="overflow-x:auto;">
                        <table style="width:100%;border-collapse:collapse;">
                            <thead>
                                <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                                    <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:#0d1b4b;">Title</th>
                                    <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:#0d1b4b;">Date Archive</th>
                                    <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:#0d1b4b;">Date Created</th>
                                    <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:#0d1b4b;">Time Created</th>
                                    <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:#0d1b4b;">Status</th>
                                    <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:#0d1b4b;">Report</th>
                                    <th style="padding:12px 16px;text-align:center;font-size:12px;font-weight:700;color:#0d1b4b;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="archivedAbyipReportsTable">
                                <tr><td colspan="7" style="text-align:center;padding:20px;color:#94a3b8;">No archived ABYIP reports</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Archived Reports Pagination --}}
                <div style="display:flex;justify-content:space-between;align-items:center;margin-top:16px;flex-wrap:wrap;gap:12px;">
                    <div style="font-size:13px;color:#64748b;">
                        Showing <span id="archivedStart">1</span> to <span id="archivedEnd">5</span> of <span id="archivedTotal">{{ count($archivedReports) }}</span> reports
                    </div>
                    <div style="display:flex;gap:8px;">
                        <button onclick="previousArchivedPage()" id="archivedPrevBtn" style="padding:8px 12px;border:1px solid #e2e8f0;background:#fff;border-radius:6px;font-size:13px;color:#213F99;cursor:pointer;transition:all .2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#fff'">
                            <i class="fas fa-chevron-left"></i> Previous
                        </button>
                        <button onclick="nextArchivedPage()" id="archivedNextBtn" style="padding:8px 12px;border:1px solid #e2e8f0;background:#fff;border-radius:6px;font-size:13px;color:#213F99;cursor:pointer;transition:all .2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#fff'">
                            Next <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
                </div>

                {{-- Accomplishment Reports Content --}}
                <div id="archived-accomplishment-content" style="display:none;">
                    {{-- Filters --}}
                <div style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;align-items:center;">
                    <select id="archivedAccomplishmentBarangayFilter" onchange="filterArchivedAccomplishmentReports()" style="padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;color:#475569;background:#fff;cursor:pointer;">
                        <option value="all">All Barangays</option>
                        <option value="Alipit">Alipit</option>
                        <option value="Bagumbayan">Bagumbayan</option>
                        <option value="Bubukal">Bubukal</option>
                        <option value="Calios">Calios</option>
                        <option value="Duhat">Duhat</option>
                        <option value="Gatid">Gatid</option>
                        <option value="Jasaan">Jasaan</option>
                        <option value="Labuin">Labuin</option>
                        <option value="Malinao">Malinao</option>
                        <option value="Oogong">Oogong</option>
                        <option value="Pagsawitan">Pagsawitan</option>
                        <option value="Palasan">Palasan</option>
                        <option value="Patimbao">Patimbao</option>
                        <option value="Poblacion I">Poblacion I</option>
                        <option value="Poblacion II">Poblacion II</option>
                        <option value="Poblacion III">Poblacion III</option>
                        <option value="Poblacion IV">Poblacion IV</option>
                        <option value="Poblacion V">Poblacion V</option>
                        <option value="San Jose">San Jose</option>
                        <option value="San Juan">San Juan</option>
                        <option value="San Pablo Norte">San Pablo Norte</option>
                        <option value="San Pablo Sur">San Pablo Sur</option>
                        <option value="Santisima Cruz">Santisima Cruz</option>
                        <option value="Santo Angel Central">Santo Angel Central</option>
                        <option value="Santo Angel Norte">Santo Angel Norte</option>
                        <option value="Santo Angel Sur">Santo Angel Sur</option>
                    </select>
                    <select id="archivedAccomplishmentYearFilter" onchange="filterArchivedAccomplishmentReports()" style="padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;color:#475569;background:#fff;cursor:pointer;">
                        <option value="all">All Years</option>
                        <option value="2026">2026</option>
                        <option value="2025">2025</option>
                        <option value="2024">2024</option>
                        <option value="2023">2023</option>
                    </select>
                    <select id="archivedAccomplishmentTimeFilter" onchange="filterArchivedAccomplishmentReports()" style="padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;color:#475569;background:#fff;cursor:pointer;">
                        <option value="all">All Time</option>
                        <option value="recent">Recent</option>
                        <option value="thismonth">This Month</option>
                    </select>
                    <div style="display:flex;gap:8px;flex:1;max-width:400px;">
                        <input type="text" id="archivedAccomplishmentSearchInput" placeholder="Search reports..." style="flex:1;padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;" onkeypress="if(event.key==='Enter') filterArchivedAccomplishmentReports()">
                        <button onclick="filterArchivedAccomplishmentReports()" style="padding:8px 16px;background:#213F99;color:#fff;border:none;border-radius:8px;font-size:13px;cursor:pointer;transition:background .2s;" onmouseover="this.style.background='#1a3280'" onmouseout="this.style.background='#213F99'">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </div>
                    {{-- Accomplishment Table --}}
                    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
                        <div style="overflow-x:auto;">
                            <table style="width:100%;border-collapse:collapse;">
                                <thead>
                                    <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                                        <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:#0d1b4b;">Program Title</th>
                                        <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:#0d1b4b;">Date Archive</th>
                                        <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:#0d1b4b;">Date Created</th>
                                        <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:#0d1b4b;">Description</th>
                                        <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:#0d1b4b;">Committee</th>
                                        <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:#0d1b4b;">Duration</th>
                                        <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:#0d1b4b;">Status</th>
                                        <th style="padding:12px 16px;text-align:center;font-size:12px;font-weight:700;color:#0d1b4b;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="archivedAccomplishmentTable">
                                    <tr><td colspan="8" style="text-align:center;padding:20px;color:#94a3b8;">No archived accomplishment reports</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
@endsection

@push('scripts')
<script src="{{ url('/shared/js/loading.js') }}"></script>
@endpush
