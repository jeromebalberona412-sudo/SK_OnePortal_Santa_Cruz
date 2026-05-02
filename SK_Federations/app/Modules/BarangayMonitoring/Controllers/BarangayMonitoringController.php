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
                'status' => 'compliant',
                'reports' => 12,
                'on_time' => 11,
                'active_programs' => 5,
                'participation_rate' => 87,
                'last_update' => 'May 1, 2026',
                'sk_chairman' => 'Roberto Santos',
                'report_rate' => 92,
            ],
            'bagumbayan-Bubukal' => [
                'slug' => 'bagumbayan-Bubukal',
                'name' => 'Bagumbayan',
                'status' => 'compliant',
                'reports' => 12,
                'on_time' => 12,
                'active_programs' => 6,
                'participation_rate' => 91,
                'last_update' => 'May 2, 2026',
                'sk_chairman' => 'Maria Cruz',
                'report_rate' => 100,
            ],
            'calios' => [
                'slug' => 'calios',
                'name' => 'Calios',
                'status' => 'partial',
                'reports' => 10,
                'on_time' => 8,
                'active_programs' => 4,
                'participation_rate' => 76,
                'last_update' => 'Apr 28, 2026',
                'sk_chairman' => 'Juan Dela Cruz',
                'report_rate' => 80,
            ],
            'duhat' => [
                'slug' => 'duhat',
                'name' => 'Duhat',
                'status' => 'compliant',
                'reports' => 11,
                'on_time' => 10,
                'active_programs' => 4,
                'participation_rate' => 84,
                'last_update' => 'May 1, 2026',
                'sk_chairman' => 'Angela Reyes',
                'report_rate' => 91,
            ],
            'gatid' => [
                'slug' => 'gatid',
                'name' => 'Gatid',
                'status' => 'partial',
                'reports' => 9,
                'on_time' => 7,
                'active_programs' => 3,
                'participation_rate' => 72,
                'last_update' => 'Apr 30, 2026',
                'sk_chairman' => 'Carlos Mendoza',
                'report_rate' => 75,
            ],
            'jasaan' => [
                'slug' => 'jasaan',
                'name' => 'Jasaan',
                'status' => 'compliant',
                'reports' => 12,
                'on_time' => 11,
                'active_programs' => 5,
                'participation_rate' => 88,
                'last_update' => 'May 2, 2026',
                'sk_chairman' => 'Patricia Gonzales',
                'report_rate' => 92,
            ],
            'labuin' => [
                'slug' => 'labuin',
                'name' => 'Labuin',
                'status' => 'partial',
                'reports' => 9,
                'on_time' => 7,
                'active_programs' => 3,
                'participation_rate' => 74,
                'last_update' => 'Apr 29, 2026',
                'sk_chairman' => 'Juan Cruz',
                'report_rate' => 75,
            ],
            'malinao' => [
                'slug' => 'malinao',
                'name' => 'Malinao',
                'status' => 'non-compliant',
                'reports' => 6,
                'on_time' => 4,
                'active_programs' => 2,
                'participation_rate' => 58,
                'last_update' => 'Apr 25, 2026',
                'sk_chairman' => 'Vincent Torres',
                'report_rate' => 50,
            ],
            'oogong' => [
                'slug' => 'oogong',
                'name' => 'Oogong',
                'status' => 'compliant',
                'reports' => 11,
                'on_time' => 10,
                'active_programs' => 4,
                'participation_rate' => 82,
                'last_update' => 'May 1, 2026',
                'sk_chairman' => 'Sophia Ramos',
                'report_rate' => 91,
            ],
            'pagsawitan' => [
                'slug' => 'pagsawitan',
                'name' => 'Pagsawitan',
                'status' => 'compliant',
                'reports' => 12,
                'on_time' => 12,
                'active_programs' => 5,
                'participation_rate' => 88,
                'last_update' => 'May 2, 2026',
                'sk_chairman' => 'Rosa Dela Cruz',
                'report_rate' => 100,
            ],
            'palasan' => [
                'slug' => 'palasan',
                'name' => 'Palasan',
                'status' => 'non-compliant',
                'reports' => 2,
                'on_time' => 0,
                'active_programs' => 1,
                'participation_rate' => 45,
                'last_update' => 'Dec 15, 2025',
                'sk_chairman' => 'Daniel Flores',
                'report_rate' => 17,
            ],
            'patimbao' => [
                'slug' => 'patimbao',
                'name' => 'Patimbao',
                'status' => 'compliant',
                'reports' => 12,
                'on_time' => 11,
                'active_programs' => 4,
                'participation_rate' => 85,
                'last_update' => 'May 1, 2026',
                'sk_chairman' => 'Elena Gutierrez',
                'report_rate' => 92,
            ],
            'brgy-1-poblacion' => [
                'slug' => 'brgy-1-poblacion',
                'name' => 'Poblacion I',
                'status' => 'compliant',
                'reports' => 12,
                'on_time' => 11,
                'active_programs' => 4,
                'participation_rate' => 86,
                'last_update' => 'May 2, 2026',
                'sk_chairman' => 'Alyssa M. Ramos',
                'report_rate' => 92,
            ],
            'brgy-2-poblacion' => [
                'slug' => 'brgy-2-poblacion',
                'name' => 'Poblacion II',
                'status' => 'partial',
                'reports' => 10,
                'on_time' => 8,
                'active_programs' => 3,
                'participation_rate' => 78,
                'last_update' => 'Apr 30, 2026',
                'sk_chairman' => 'Mark D. Villanueva',
                'report_rate' => 80,
            ],
            'brgy-3-poblacion' => [
                'slug' => 'brgy-3-poblacion',
                'name' => 'Poblacion III',
                'status' => 'compliant',
                'reports' => 11,
                'on_time' => 10,
                'active_programs' => 4,
                'participation_rate' => 83,
                'last_update' => 'May 1, 2026',
                'sk_chairman' => 'Celine P. Mendoza',
                'report_rate' => 91,
            ],
            'brgy-4-poblacion' => [
                'slug' => 'brgy-4-poblacion',
                'name' => 'Poblacion IV',
                'status' => 'partial',
                'reports' => 8,
                'on_time' => 6,
                'active_programs' => 2,
                'participation_rate' => 66,
                'last_update' => 'Apr 28, 2026',
                'sk_chairman' => 'Jomar L. Bautista',
                'report_rate' => 67,
            ],
            'brgy-5-poblacion' => [
                'slug' => 'brgy-5-poblacion',
                'name' => 'Poblacion V',
                'status' => 'non-compliant',
                'reports' => 5,
                'on_time' => 3,
                'active_programs' => 1,
                'participation_rate' => 49,
                'last_update' => 'Apr 26, 2026',
                'sk_chairman' => 'Maria Santos',
                'report_rate' => 42,
            ],
            'san-jose' => [
                'slug' => 'san-jose',
                'name' => 'San Jose',
                'status' => 'non-compliant',
                'reports' => 4,
                'on_time' => 2,
                'active_programs' => 1,
                'participation_rate' => 43,
                'last_update' => 'Apr 24, 2026',
                'sk_chairman' => 'Pedro Reyes',
                'report_rate' => 33,
            ],
            'san-juan' => [
                'slug' => 'san-juan',
                'name' => 'San Juan',
                'status' => 'compliant',
                'reports' => 11,
                'on_time' => 10,
                'active_programs' => 4,
                'participation_rate' => 81,
                'last_update' => 'May 1, 2026',
                'sk_chairman' => 'Francisco Diaz',
                'report_rate' => 91,
            ],
            'san-pablo-norte' => [
                'slug' => 'san-pablo-norte',
                'name' => 'San Pablo Norte',
                'status' => 'partial',
                'reports' => 9,
                'on_time' => 7,
                'active_programs' => 3,
                'participation_rate' => 71,
                'last_update' => 'Apr 29, 2026',
                'sk_chairman' => 'Lucia Fernandez',
                'report_rate' => 75,
            ],
            'san-pablo-sur' => [
                'slug' => 'san-pablo-sur',
                'name' => 'San Pablo Sur',
                'status' => 'compliant',
                'reports' => 12,
                'on_time' => 11,
                'active_programs' => 5,
                'participation_rate' => 86,
                'last_update' => 'May 2, 2026',
                'sk_chairman' => 'Gabriela Lopez',
                'report_rate' => 92,
            ],
            'santisima-cruz' => [
                'slug' => 'santisima-cruz',
                'name' => 'Santisima Cruz',
                'status' => 'partial',
                'reports' => 10,
                'on_time' => 8,
                'active_programs' => 3,
                'participation_rate' => 75,
                'last_update' => 'Apr 30, 2026',
                'sk_chairman' => 'Mariana Gutierrez',
                'report_rate' => 80,
            ],
            'santo-angel-central' => [
                'slug' => 'santo-angel-central',
                'name' => 'Santo Angel Central',
                'status' => 'compliant',
                'reports' => 11,
                'on_time' => 10,
                'active_programs' => 4,
                'participation_rate' => 84,
                'last_update' => 'May 1, 2026',
                'sk_chairman' => 'Rafael Morales',
                'report_rate' => 91,
            ],
            'santo-angel-norte' => [
                'slug' => 'santo-angel-norte',
                'name' => 'Santo Angel Norte',
                'status' => 'compliant',
                'reports' => 12,
                'on_time' => 11,
                'active_programs' => 5,
                'participation_rate' => 87,
                'last_update' => 'May 2, 2026',
                'sk_chairman' => 'Veronica Castillo',
                'report_rate' => 92,
            ],
            'santo-angel-sur' => [
                'slug' => 'santo-angel-sur',
                'name' => 'Santo Angel Sur',
                'status' => 'partial',
                'reports' => 9,
                'on_time' => 7,
                'active_programs' => 3,
                'participation_rate' => 73,
                'last_update' => 'Apr 29, 2026',
                'sk_chairman' => 'Isabelle Rivera',
                'report_rate' => 75,
            ],
        ];
    }

    private function barangayDetails(): array
    {
        $basePrograms = [
            'previous_programs' => [
                [
                    'title' => 'Youth Leadership Bootcamp',
                    'sector' => 'Leadership',
                    'timeline' => 'Nov 2025 - Dec 2025',
                    'status' => 'Completed',
                    'participants' => 96,
                    'description' => 'A comprehensive leadership training program designed to develop essential skills in decision-making, communication, and team management for young leaders in the barangay.',
                ],
                [
                    'title' => 'Barangay Clean-Up Drive',
                    'sector' => 'Environment',
                    'timeline' => 'Jan 2026',
                    'status' => 'Completed',
                    'participants' => 120,
                    'description' => 'Community-wide environmental initiative focused on cleaning and beautifying public spaces, promoting environmental awareness and civic responsibility among youth.',
                ],
            ],
            'current_programs' => [
                [
                    'title' => 'Skills Training and Livelihood',
                    'sector' => 'Livelihood',
                    'timeline' => 'Mar 2026 - Apr 2026',
                    'status' => 'Ongoing',
                    'participants' => 72,
                    'description' => 'Vocational training program providing practical skills in various trades and entrepreneurship to empower youth with income-generating opportunities.',
                ],
                [
                    'title' => 'Sports Development League',
                    'sector' => 'Sports',
                    'timeline' => 'Mar 2026 - May 2026',
                    'status' => 'Ongoing',
                    'participants' => 110,
                    'description' => 'Competitive sports program promoting physical fitness, teamwork, and healthy lifestyle among youth through various sporting activities and tournaments.',
                ],
            ],
            'future_programs' => [
                [
                    'title' => 'Mental Wellness Caravan',
                    'sector' => 'Health',
                    'timeline' => 'May 2026',
                    'status' => 'Planned',
                    'participants' => 90,
                    'description' => 'Mental health awareness and wellness program providing counseling, stress management workshops, and psychological support services for youth.',
                ],
                [
                    'title' => 'Digital Literacy Lab',
                    'sector' => 'Education',
                    'timeline' => 'Jun 2026',
                    'status' => 'Planned',
                    'participants' => 80,
                    'description' => 'Technology training program equipping youth with essential digital skills including computer basics, internet safety, and digital citizenship.',
                ],
            ],
        ];

        $baseOfficials = [
            [
                'name' => 'Alyssa M. Ramos',
                'role' => 'SK Chairperson',
                'status' => 'Active',
                'term_start' => 'Nov 30, 2023',
                'term_end' => 'Nov 29, 2026',
            ],
            [
                'name' => 'Mark D. Villanueva',
                'role' => 'SK Kagawad',
                'status' => 'Active',
                'term_start' => 'Nov 30, 2023',
                'term_end' => 'Nov 29, 2026',
            ],
            [
                'name' => 'Celine P. Mendoza',
                'role' => 'SK Treasurer',
                'status' => 'Active',
                'term_start' => 'Nov 30, 2023',
                'term_end' => 'Nov 29, 2026',
            ],
            [
                'name' => 'Jomar L. Bautista',
                'role' => 'SK Secretary',
                'status' => 'On Leave',
                'term_start' => 'Nov 30, 2023',
                'term_end' => 'Nov 29, 2026',
            ],
        ];

        $catalog = $this->barangayCatalog();
        $details = [];

        foreach ($catalog as $slug => $row) {
            // Special case for Palasan - non-compliant with old reports only
            $abyipReports = [
                [
                    'name' => 'Q1 2026 ABYIP Report',
                    'date_submitted' => 'Apr 15, 2026',
                    'submitted_by' => 'Alyssa M. Ramos',
                    'file' => '/reports/q1-2026-abyip.pdf',
                ],
                [
                    'name' => 'Q4 2025 ABYIP Report',
                    'date_submitted' => 'Jan 10, 2026',
                    'submitted_by' => 'Alyssa M. Ramos',
                    'file' => '/reports/q4-2025-abyip.pdf',
                ],
                [
                    'name' => 'Q3 2025 ABYIP Report',
                    'date_submitted' => 'Oct 20, 2025',
                    'submitted_by' => 'Mark D. Villanueva',
                    'file' => '/reports/q3-2025-abyip.pdf',
                ],
            ];
            
            $programList = array_merge(
                array_map(fn($p) => array_merge($p, ['budget_allocated' => '₱25,000', 'budget_used' => '₱22,500', 'start_date' => 'Nov 2025', 'end_date' => 'Dec 2025', 'reports' => ['Q4 2025 Report.pdf']]), $basePrograms['previous_programs']),
                array_map(fn($p) => array_merge($p, ['budget_allocated' => '₱30,000', 'budget_used' => '₱14,200', 'start_date' => 'Mar 2026', 'end_date' => 'May 2026', 'reports' => []]), $basePrograms['current_programs']),
                array_map(fn($p) => array_merge($p, ['budget_allocated' => '₱20,000', 'budget_used' => '₱0', 'start_date' => 'May 2026', 'end_date' => 'Jun 2026', 'reports' => []]), $basePrograms['future_programs'])
            );
            
            // For Palasan, use only old reports (2025)
            if ($slug === 'palasan') {
                $abyipReports = [
                    [
                        'name' => 'Q4 2025 ABYIP Report',
                        'date_submitted' => 'Dec 15, 2025',
                        'submitted_by' => 'Daniel Flores',
                        'file' => '/reports/q4-2025-abyip-palasan.pdf',
                    ],
                    [
                        'name' => 'Q3 2025 ABYIP Report',
                        'date_submitted' => 'Oct 10, 2025',
                        'submitted_by' => 'Daniel Flores',
                        'file' => '/reports/q3-2025-abyip-palasan.pdf',
                    ],
                ];
                
                $programList = array_map(fn($p) => array_merge($p, ['budget_allocated' => '₱15,000', 'budget_used' => '₱8,500', 'start_date' => 'Sep 2025', 'end_date' => 'Nov 2025', 'reports' => ['Q3 2025 Report.pdf']]), $basePrograms['previous_programs']);
            }
            
            $details[$slug] = [
                'municipality' => 'Santa Cruz, Laguna',
                'coverage' => '26 youth clusters',
                'total_kabataan' => Arr::get($row, 'participation_rate', 0) + 860,
                'verified_accounts' => Arr::get($row, 'reports', 0) * 24,
                'latest_submission' => Arr::get($row, 'last_update', ''),
                'compliance_score' => Arr::get($row, 'status') === 'compliant' ? 94 : (Arr::get($row, 'status') === 'partial' ? 76 : 58),
                'programs' => $basePrograms,
                'officials' => $baseOfficials,
                'system_data' => [
                    'report_cycles_submitted' => Arr::get($row, 'reports', 0),
                    'pending_reports' => max(0, 12 - Arr::get($row, 'reports', 0)),
                    'last_audit_result' => Arr::get($row, 'status') === 'non-compliant' ? 'Needs immediate corrective action' : 'Within expected compliance threshold',
                    'device_trust_rate' => Arr::get($row, 'status') === 'compliant' ? '98%' : '91%',
                    'top_issue' => Arr::get($row, 'status') === 'non-compliant' ? 'Late monthly report submission' : 'No critical issue flagged',
                ],
                // ── New program monitoring data ──
                'program_stats' => [
                    'total_youth_population'  => Arr::get($row, 'participation_rate', 0) + 860,
                    'total_programs_created'  => count($basePrograms['previous_programs']) + count($basePrograms['current_programs']) + count($basePrograms['future_programs']),
                    'total_ongoing'           => count($basePrograms['current_programs']),
                    'total_completed'         => count($basePrograms['previous_programs']),
                    'total_participants'      => array_sum(array_column(array_merge($basePrograms['previous_programs'], $basePrograms['current_programs']), 'participants')),
                    'overall_performance'     => Arr::get($row, 'status') === 'compliant' ? 'Excellent' : (Arr::get($row, 'status') === 'partial' ? 'Good' : 'Needs Improvement'),
                ],
                'performance_summary' => [
                    'completion_rate'         => Arr::get($row, 'status') === 'compliant' ? 85 : (Arr::get($row, 'status') === 'partial' ? 68 : 45),
                    'attendance_rate'         => Arr::get($row, 'participation_rate', 70),
                    'budget_efficiency'       => Arr::get($row, 'status') === 'compliant' ? 92 : 78,
                    'most_active_sector'      => 'Sports',
                    'most_successful_program' => 'Youth Basketball League',
                    'low_participation_count' => Arr::get($row, 'status') === 'non-compliant' ? 3 : 1,
                ],
                'program_list' => $programList,
                // ── ABYIP Data ──
                'abyip' => [
                    'budget_utilization' => Arr::get($row, 'status') === 'compliant' ? 87 : (Arr::get($row, 'status') === 'partial' ? 72 : 58),
                    'remaining_balance' => Arr::get($row, 'status') === 'compliant' ? 156000 : (Arr::get($row, 'status') === 'partial' ? 245000 : 380000),
                    'project_count' => Arr::get($row, 'status') === 'compliant' ? 12 : (Arr::get($row, 'status') === 'partial' ? 9 : 6),
                    'reports' => $abyipReports,
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
