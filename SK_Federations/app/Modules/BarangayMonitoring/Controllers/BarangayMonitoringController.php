<?php

namespace App\Modules\BarangayMonitoring\Controllers;

use App\Modules\Shared\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class BarangayMonitoringController extends Controller
{
    public function index(Request $request): View
    {
        $barangays = array_values($this->barangayCatalog());

        $stats = $this->buildStats($barangays);

        return view('barangay_monitoring::index', [
            'user' => $request->user(),
            'barangays' => $barangays,
            'stats' => $stats,
        ]);
    }

    public function show(Request $request, string $barangay): View
    {
        $catalog = $this->barangayCatalog();
        $detail = $this->barangayDetails();

        if (! isset($catalog[$barangay], $detail[$barangay])) {
            abort(404);
        }

        $barangayData = array_merge($catalog[$barangay], $detail[$barangay]);
        
        // Calculate compliance status based on report submissions
        $complianceStatus = $this->calculateComplianceStatus($barangayData);
        $barangayData['compliance_status'] = $complianceStatus['status'];
        $barangayData['compliance_details'] = $complianceStatus['details'];
        $barangayData['warnings'] = $this->getWarningsForBarangay($barangayData);

        return view('barangay_monitoring::show', [
            'user' => $request->user(),
            'barangayData' => $barangayData,
        ]);
    }

    private function buildStats(array $barangays): array
    {
        $count = count($barangays);
        $activePrograms = array_sum(array_map(fn ($item) => $item['active_programs'], $barangays));
        $participationRates = array_map(fn ($item) => $item['participation_rate'], $barangays);
        $compliant = count(array_filter($barangays, fn ($item) => $item['status'] === 'compliant'));
        $nonCompliant = count(array_filter($barangays, fn ($item) => $item['status'] === 'non-compliant'));

        $avgParticipation = count($participationRates) > 0 ? round(array_sum($participationRates) / count($participationRates)) : 0;
        $complianceRate = $count > 0 ? round(($compliant / $count) * 100) : 0;
        $nonComplianceRate = $count > 0 ? round(($nonCompliant / $count) * 100) : 0;

        return [
            'total_barangays' => $count,
            'total_programs' => $activePrograms,
            'average_participation_rate' => $avgParticipation,
            'compliance_rate' => $complianceRate,
            'non_compliance_rate' => $nonComplianceRate,
        ];
    }

    private function barangayCatalog(): array
    {
        return [
            'alipit' => [
                'slug' => 'alipit',
                'name' => 'Alipit',
                'status' => 'non-compliant',
                'reports' => 0,
                'on_time' => 0,
                'active_programs' => 0,
                'participation_rate' => 0,
                'last_update' => 'No updates yet',
                'sk_chairman' => 'Not assigned',
                'report_rate' => 0,
            ],
            'bagumbayan' => [
                'slug' => 'bagumbayan',
                'name' => 'Bagumbayan',
                'status' => 'non-compliant',
                'reports' => 0,
                'on_time' => 0,
                'active_programs' => 0,
                'participation_rate' => 0,
                'last_update' => 'No updates yet',
                'sk_chairman' => 'Not assigned',
                'report_rate' => 0,
            ],
            'calios' => [
                'slug' => 'calios',
                'name' => 'Calios',
                'status' => 'non-compliant',
                'reports' => 0,
                'on_time' => 0,
                'active_programs' => 0,
                'participation_rate' => 0,
                'last_update' => 'No updates yet',
                'sk_chairman' => 'Not assigned',
                'report_rate' => 0,
            ],
            'duhat' => [
                'slug' => 'duhat',
                'name' => 'Duhat',
                'status' => 'non-compliant',
                'reports' => 0,
                'on_time' => 0,
                'active_programs' => 0,
                'participation_rate' => 0,
                'last_update' => 'No updates yet',
                'sk_chairman' => 'Not assigned',
                'report_rate' => 0,
            ],
            'gatid' => [
                'slug' => 'gatid',
                'name' => 'Gatid',
                'status' => 'non-compliant',
                'reports' => 0,
                'on_time' => 0,
                'active_programs' => 0,
                'participation_rate' => 0,
                'last_update' => 'No updates yet',
                'sk_chairman' => 'Not assigned',
                'report_rate' => 0,
            ],
            'jasaan' => [
                'slug' => 'jasaan',
                'name' => 'Jasaan',
                'status' => 'non-compliant',
                'reports' => 0,
                'on_time' => 0,
                'active_programs' => 0,
                'participation_rate' => 0,
                'last_update' => 'No updates yet',
                'sk_chairman' => 'Not assigned',
                'report_rate' => 0,
            ],
            'labuin' => [
                'slug' => 'labuin',
                'name' => 'Labuin',
                'status' => 'non-compliant',
                'reports' => 0,
                'on_time' => 0,
                'active_programs' => 0,
                'participation_rate' => 0,
                'last_update' => 'No updates yet',
                'sk_chairman' => 'Not assigned',
                'report_rate' => 0,
            ],
            'malinao' => [
                'slug' => 'malinao',
                'name' => 'Malinao',
                'status' => 'non-compliant',
                'reports' => 0,
                'on_time' => 0,
                'active_programs' => 0,
                'participation_rate' => 0,
                'last_update' => 'No updates yet',
                'sk_chairman' => 'Not assigned',
                'report_rate' => 0,
            ],
            'oogong' => [
                'slug' => 'oogong',
                'name' => 'Oogong',
                'status' => 'non-compliant',
                'reports' => 0,
                'on_time' => 0,
                'active_programs' => 0,
                'participation_rate' => 0,
                'last_update' => 'No updates yet',
                'sk_chairman' => 'Not assigned',
                'report_rate' => 0,
            ],
            'pagsawitan' => [
                'slug' => 'pagsawitan',
                'name' => 'Pagsawitan',
                'status' => 'non-compliant',
                'reports' => 0,
                'on_time' => 0,
                'active_programs' => 0,
                'participation_rate' => 0,
                'last_update' => 'No updates yet',
                'sk_chairman' => 'Not assigned',
                'report_rate' => 0,
            ],
            'palasan' => [
                'slug' => 'palasan',
                'name' => 'Palasan',
                'status' => 'non-compliant',
                'reports' => 0,
                'on_time' => 0,
                'active_programs' => 0,
                'participation_rate' => 0,
                'last_update' => 'No updates yet',
                'sk_chairman' => 'Not assigned',
                'report_rate' => 0,
            ],
            'patimbao' => [
                'slug' => 'patimbao',
                'name' => 'Patimbao',
                'status' => 'non-compliant',
                'reports' => 0,
                'on_time' => 0,
                'active_programs' => 0,
                'participation_rate' => 0,
                'last_update' => 'No updates yet',
                'sk_chairman' => 'Not assigned',
                'report_rate' => 0,
            ],
            'brgy-1-poblacion' => [
                'slug' => 'brgy-1-poblacion',
                'name' => 'Poblacion I',
                'status' => 'non-compliant',
                'reports' => 0,
                'on_time' => 0,
                'active_programs' => 0,
                'participation_rate' => 0,
                'last_update' => 'No updates yet',
                'sk_chairman' => 'Not assigned',
                'report_rate' => 0,
            ],
            'brgy-2-poblacion' => [
                'slug' => 'brgy-2-poblacion',
                'name' => 'Poblacion II',
                'status' => 'non-compliant',
                'reports' => 0,
                'on_time' => 0,
                'active_programs' => 0,
                'participation_rate' => 0,
                'last_update' => 'No updates yet',
                'sk_chairman' => 'Not assigned',
                'report_rate' => 0,
            ],
            'brgy-3-poblacion' => [
                'slug' => 'brgy-3-poblacion',
                'name' => 'Poblacion III',
                'status' => 'non-compliant',
                'reports' => 0,
                'on_time' => 0,
                'active_programs' => 0,
                'participation_rate' => 0,
                'last_update' => 'No updates yet',
                'sk_chairman' => 'Not assigned',
                'report_rate' => 0,
            ],
            'brgy-4-poblacion' => [
                'slug' => 'brgy-4-poblacion',
                'name' => 'Poblacion IV',
                'status' => 'non-compliant',
                'reports' => 0,
                'on_time' => 0,
                'active_programs' => 0,
                'participation_rate' => 0,
                'last_update' => 'No updates yet',
                'sk_chairman' => 'Not assigned',
                'report_rate' => 0,
            ],
            'brgy-5-poblacion' => [
                'slug' => 'brgy-5-poblacion',
                'name' => 'Poblacion V',
                'status' => 'non-compliant',
                'reports' => 0,
                'on_time' => 0,
                'active_programs' => 0,
                'participation_rate' => 0,
                'last_update' => 'No updates yet',
                'sk_chairman' => 'Not assigned',
                'report_rate' => 0,
            ],
            'san-jose' => [
                'slug' => 'san-jose',
                'name' => 'San Jose',
                'status' => 'non-compliant',
                'reports' => 0,
                'on_time' => 0,
                'active_programs' => 0,
                'participation_rate' => 0,
                'last_update' => 'No updates yet',
                'sk_chairman' => 'Not assigned',
                'report_rate' => 0,
            ],
            'san-juan' => [
                'slug' => 'san-juan',
                'name' => 'San Juan',
                'status' => 'non-compliant',
                'reports' => 0,
                'on_time' => 0,
                'active_programs' => 0,
                'participation_rate' => 0,
                'last_update' => 'No updates yet',
                'sk_chairman' => 'Not assigned',
                'report_rate' => 0,
            ],
            'san-pablo-norte' => [
                'slug' => 'san-pablo-norte',
                'name' => 'San Pablo Norte',
                'status' => 'non-compliant',
                'reports' => 0,
                'on_time' => 0,
                'active_programs' => 0,
                'participation_rate' => 0,
                'last_update' => 'No updates yet',
                'sk_chairman' => 'Not assigned',
                'report_rate' => 0,
            ],
            'san-pablo-sur' => [
                'slug' => 'san-pablo-sur',
                'name' => 'San Pablo Sur',
                'status' => 'non-compliant',
                'reports' => 0,
                'on_time' => 0,
                'active_programs' => 0,
                'participation_rate' => 0,
                'last_update' => 'No updates yet',
                'sk_chairman' => 'Not assigned',
                'report_rate' => 0,
            ],
            'santisima-cruz' => [
                'slug' => 'santisima-cruz',
                'name' => 'Santisima Cruz',
                'status' => 'non-compliant',
                'reports' => 0,
                'on_time' => 0,
                'active_programs' => 0,
                'participation_rate' => 0,
                'last_update' => 'No updates yet',
                'sk_chairman' => 'Not assigned',
                'report_rate' => 0,
            ],
            'santo-angel-central' => [
                'slug' => 'santo-angel-central',
                'name' => 'Santo Angel Central',
                'status' => 'non-compliant',
                'reports' => 0,
                'on_time' => 0,
                'active_programs' => 0,
                'participation_rate' => 0,
                'last_update' => 'No updates yet',
                'sk_chairman' => 'Not assigned',
                'report_rate' => 0,
            ],
            'santo-angel-norte' => [
                'slug' => 'santo-angel-norte',
                'name' => 'Santo Angel Norte',
                'status' => 'non-compliant',
                'reports' => 0,
                'on_time' => 0,
                'active_programs' => 0,
                'participation_rate' => 0,
                'last_update' => 'No updates yet',
                'sk_chairman' => 'Not assigned',
                'report_rate' => 0,
            ],
            'santo-angel-sur' => [
                'slug' => 'santo-angel-sur',
                'name' => 'Santo Angel Sur',
                'status' => 'non-compliant',
                'reports' => 0,
                'on_time' => 0,
                'active_programs' => 0,
                'participation_rate' => 0,
                'last_update' => 'No updates yet',
                'sk_chairman' => 'Not assigned',
                'report_rate' => 0,
            ],
        ];
    }

    private function barangayDetails(): array
    {
        $catalog = $this->barangayCatalog();
        $details = [];

        foreach ($catalog as $slug => $row) {
            $details[$slug] = [
                'municipality' => 'Santa Cruz, Laguna',
                'coverage' => '0 youth clusters',
                'total_kabataan' => 0,
                'verified_accounts' => 0,
                'latest_submission' => 'No submissions yet',
                'compliance_score' => 0,
                'programs' => [
                    'previous_programs' => [],
                    'current_programs' => [],
                    'future_programs' => [],
                ],
                'officials' => [],
                'system_data' => [
                    'report_cycles_submitted' => 0,
                    'pending_reports' => 0,
                    'last_audit_result' => 'No audit conducted yet',
                    'device_trust_rate' => '0%',
                    'top_issue' => 'No data available',
                ],
                'program_stats' => [
                    'total_youth_population'  => 0,
                    'total_programs_created'  => 0,
                    'total_ongoing'           => 0,
                    'total_completed'         => 0,
                    'total_participants'      => 0,
                    'overall_performance'     => 'No data',
                ],
                'performance_summary' => [
                    'completion_rate'         => 0,
                    'attendance_rate'         => 0,
                    'budget_efficiency'       => 0,
                    'most_active_sector'      => 'N/A',
                    'most_successful_program' => 'N/A',
                    'low_participation_count' => 0,
                ],
                'program_list' => [],
                'abyip' => [
                    'budget_utilization' => 0,
                    'remaining_balance' => 0,
                    'project_count' => 0,
                    'reports' => [],
                ],
            ];
        }

        return $details;
    }

    private function calculateComplianceStatus(array $barangayData): array
    {
        $currentYear = date('Y');
        $abyipReports = $barangayData['abyip']['reports'] ?? [];
        $accomplishmentReports = $barangayData['program_list'] ?? [];
        
        // Check if ABYIP reports exist and are from current year
        $abyipSubmittedThisYear = false;
        foreach ($abyipReports as $report) {
            $reportYear = date('Y', strtotime($report['date_submitted'] ?? ''));
            if ($reportYear == $currentYear) {
                $abyipSubmittedThisYear = true;
                break;
            }
        }
        
        // Check if Accomplishment reports exist and are from current year
        $accomplishmentSubmittedThisYear = false;
        foreach ($accomplishmentReports as $report) {
            $reportYear = date('Y', strtotime($report['timeline'] ?? ''));
            if ($reportYear == $currentYear) {
                $accomplishmentSubmittedThisYear = true;
                break;
            }
        }
        
        if ($abyipSubmittedThisYear && $accomplishmentSubmittedThisYear) {
            $status = 'compliant';
            $details = 'Both ABYIP and Accomplishment reports submitted this year';
        } elseif ($abyipSubmittedThisYear || $accomplishmentSubmittedThisYear) {
            $status = 'partial';
            $details = $abyipSubmittedThisYear ? 'Missing current year Accomplishment Report' : 'Missing current year ABYIP Report';
        } else {
            $status = 'non-compliant';
            $details = 'No reports submitted this year';
        }
        
        return [
            'status' => $status,
            'details' => $details,
            'abyip_submitted' => $abyipSubmittedThisYear,
            'accomplishment_submitted' => $accomplishmentSubmittedThisYear,
        ];
    }

    private function getWarningsForBarangay(array $barangayData): array
    {
        $warnings = [];
        $complianceStatus = $barangayData['compliance_status'] ?? 'compliant';
        
        if ($complianceStatus === 'non-compliant') {
            $warnings[] = [
                'type' => 'critical',
                'title' => 'Non-Compliant Status',
                'message' => 'This barangay has not submitted required reports',
                'reasons' => [
                    'Missing ABYIP Report',
                    'Missing Accomplishment Report',
                    'Delayed Submission',
                    'Incomplete Documentation',
                    'Other',
                ],
                'default_reason' => 'Missing ABYIP Report',
            ];
        } elseif ($complianceStatus === 'partial') {
            $warnings[] = [
                'type' => 'warning',
                'title' => 'Partial Compliance',
                'message' => 'This barangay is missing one or more required reports',
                'reasons' => [
                    'Missing ABYIP Report',
                    'Missing Accomplishment Report',
                    'Pending Review',
                    'Other',
                ],
                'default_reason' => $barangayData['compliance_details'] ?? 'Missing Report',
            ];
        }
        
        return $warnings;
    }
}
