<?php

namespace App\Modules\Reports\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportsController
{
    public function index(Request $request): View
    {
        $reportsData = $this->getReportsData();
        $user = auth()->user();
        return view('reports::index', ['reportsData' => $reportsData, 'user' => $user]);
    }

    private function getReportsData(): array
    {
        $accomplishmentReports = [
            ['id' => 1, 'program_name' => 'Youth Leadership Summit', 'barangay' => 'Poblacion I', 'date_registered' => 'Apr 15, 2026', 'attendance' => 45, 'evaluation' => 4.5, 'report' => 'report_001.pdf', 'status' => 'compliant', 'rejection_reason' => ''],
            ['id' => 2, 'program_name' => 'Community Clean-up Drive', 'barangay' => 'Poblacion II', 'date_registered' => 'Apr 20, 2026', 'attendance' => 32, 'evaluation' => 4.2, 'report' => 'report_002.pdf', 'status' => 'compliant', 'rejection_reason' => ''],
            ['id' => 3, 'program_name' => 'Sports Festival', 'barangay' => 'Poblacion III', 'date_registered' => 'Apr 10, 2026', 'attendance' => 78, 'evaluation' => 4.8, 'report' => 'report_003.pdf', 'status' => 'compliant', 'rejection_reason' => ''],
            ['id' => 4, 'program_name' => 'Health Awareness Campaign', 'barangay' => 'Poblacion IV', 'date_registered' => 'Apr 25, 2026', 'attendance' => 28, 'evaluation' => 3.9, 'report' => 'report_004.pdf', 'status' => 'partial', 'rejection_reason' => ''],
            ['id' => 5, 'program_name' => 'Environmental Protection', 'barangay' => 'Labuin', 'date_registered' => 'Apr 18, 2026', 'attendance' => 55, 'evaluation' => 4.6, 'report' => 'report_005.pdf', 'status' => 'compliant', 'rejection_reason' => ''],
            ['id' => 6, 'program_name' => 'Skills Training Workshop', 'barangay' => 'Pagsawitan', 'date_registered' => 'Apr 22, 2026', 'attendance' => 40, 'evaluation' => 4.3, 'report' => 'report_006.pdf', 'status' => 'rejected', 'rejection_reason' => 'Missing Attachment'],
            ['id' => 7, 'program_name' => 'Cultural Festival', 'barangay' => 'Santisima Cruz', 'date_registered' => 'Apr 12, 2026', 'attendance' => 120, 'evaluation' => 4.7, 'report' => 'report_007.pdf', 'status' => 'compliant', 'rejection_reason' => ''],
            ['id' => 8, 'program_name' => 'Disaster Preparedness', 'barangay' => 'Bagumbayan', 'date_registered' => 'Apr 28, 2026', 'attendance' => 35, 'evaluation' => 3.8, 'report' => 'report_008.pdf', 'status' => 'partial', 'rejection_reason' => ''],
        ];

        $abyipReports = [
            ['id' => 1, 'report_name' => 'Q1 Budget Utilization Report', 'barangay' => 'Poblacion I', 'date_submitted' => 'Apr 30, 2026', 'submitted_by' => 'Alyssa M. Ramos', 'report_file' => 'abyip_001.pdf', 'status' => 'compliant', 'rejection_reason' => ''],
            ['id' => 2, 'report_name' => 'Q1 Project Implementation', 'barangay' => 'Poblacion II', 'date_submitted' => 'Apr 28, 2026', 'submitted_by' => 'Mark D. Villanueva', 'report_file' => 'abyip_002.pdf', 'status' => 'compliant', 'rejection_reason' => ''],
            ['id' => 3, 'report_name' => 'Q1 Financial Statement', 'barangay' => 'Poblacion III', 'date_submitted' => 'May 02, 2026', 'submitted_by' => 'Celine P. Mendoza', 'report_file' => 'abyip_003.pdf', 'status' => 'compliant', 'rejection_reason' => ''],
            ['id' => 4, 'report_name' => 'Q1 Accomplishment Report', 'barangay' => 'Poblacion IV', 'date_submitted' => 'Apr 25, 2026', 'submitted_by' => 'Jomar L. Bautista', 'report_file' => 'abyip_004.pdf', 'status' => 'partial', 'rejection_reason' => ''],
            ['id' => 5, 'report_name' => 'Q1 Budget Utilization Report', 'barangay' => 'Labuin', 'date_submitted' => 'Apr 30, 2026', 'submitted_by' => 'Juan Cruz', 'report_file' => 'abyip_005.pdf', 'status' => 'compliant', 'rejection_reason' => ''],
            ['id' => 6, 'report_name' => 'Q1 Project Implementation', 'barangay' => 'Pagsawitan', 'date_submitted' => 'May 01, 2026', 'submitted_by' => 'Rosa Dela Cruz', 'report_file' => 'abyip_006.pdf', 'status' => 'rejected', 'rejection_reason' => 'Corrupted File'],
            ['id' => 7, 'report_name' => 'Q1 Financial Statement', 'barangay' => 'Santisima Cruz', 'date_submitted' => 'Apr 29, 2026', 'submitted_by' => 'Ana Garcia', 'report_file' => 'abyip_007.pdf', 'status' => 'compliant', 'rejection_reason' => ''],
            ['id' => 8, 'report_name' => 'Q1 Accomplishment Report', 'barangay' => 'Bagumbayan', 'date_submitted' => 'Apr 27, 2026', 'submitted_by' => 'Carlos Mendez', 'report_file' => 'abyip_008.pdf', 'status' => 'partial', 'rejection_reason' => ''],
        ];

        // Calculate compliance statistics
        $compliant = count(array_filter($accomplishmentReports, fn($r) => $r['status'] === 'compliant'));
        $partial = count(array_filter($accomplishmentReports, fn($r) => $r['status'] === 'partial'));
        $nonCompliant = count(array_filter($accomplishmentReports, fn($r) => $r['status'] === 'non-compliant' || $r['status'] === 'rejected'));
        $totalBarangays = 26;

        // Calculate submission rates
        $accomplishmentSubmitted = count(array_filter($accomplishmentReports, fn($r) => $r['status'] !== 'non-compliant'));
        $accomplishmentNotSubmitted = count($accomplishmentReports) - $accomplishmentSubmitted;
        $accomplishmentRate = count($accomplishmentReports) > 0 ? round(($accomplishmentSubmitted / count($accomplishmentReports)) * 100) : 0;

        $abyipSubmitted = count(array_filter($abyipReports, fn($r) => $r['status'] !== 'non-compliant'));
        $abyipNotSubmitted = count($abyipReports) - $abyipSubmitted;
        $abyipRate = count($abyipReports) > 0 ? round(($abyipSubmitted / count($abyipReports)) * 100) : 0;

        $kkProfilingSubmitted = 5;
        $kkProfilingNotSubmitted = 3;
        $kkProfilingRate = round(($kkProfilingSubmitted / ($kkProfilingSubmitted + $kkProfilingNotSubmitted)) * 100);

        return [
            'summary' => [
                'total_barangays' => $totalBarangays,
                'compliant' => $compliant,
                'partial' => $partial,
                'non_compliant' => $nonCompliant,
                'accomplishment_submitted' => $accomplishmentSubmitted,
                'accomplishment_not_submitted' => $accomplishmentNotSubmitted,
                'accomplishment_rate' => $accomplishmentRate,
                'abyip_submitted' => $abyipSubmitted,
                'abyip_not_submitted' => $abyipNotSubmitted,
                'abyip_rate' => $abyipRate,
                'kk_profiling_submitted' => $kkProfilingSubmitted,
                'kk_profiling_not_submitted' => $kkProfilingNotSubmitted,
                'kk_profiling_rate' => $kkProfilingRate,
            ],
            'accomplishment_reports' => $accomplishmentReports,
            'abyip_reports' => $abyipReports,
        ];
    }
}
