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

        return view('barangay_monitoring::show', [
            'user' => $request->user(),
            'barangayData' => $barangayData,
        ]);
    }

    private function buildStats(array $barangays): array
    {
        $count = count($barangays);
        $reports = array_sum(array_map(fn ($item) => $item['reports'], $barangays));
        $onTime = array_sum(array_map(fn ($item) => $item['on_time'], $barangays));
        $activePrograms = array_sum(array_map(fn ($item) => $item['active_programs'], $barangays));
        $compliant = count(array_filter($barangays, fn ($item) => $item['status'] === 'compliant'));
        $partial = count(array_filter($barangays, fn ($item) => $item['status'] === 'partial'));
        $nonCompliant = count(array_filter($barangays, fn ($item) => $item['status'] === 'non-compliant'));

        return [
            'total_barangays' => $count,
            'reporting_rate' => $count > 0 ? round(($count / 26) * 100) : 0,
            'ontime_rate' => $reports > 0 ? round(($onTime / $reports) * 100) : 0,
            'active_programs' => $activePrograms,
            'compliant' => $compliant,
            'partial' => $partial,
            'non_compliant' => $nonCompliant,
        ];
    }

    private function barangayCatalog(): array
    {
        return [
            'brgy-1-poblacion' => [
                'slug' => 'brgy-1-poblacion',
                'name' => 'Brgy. I (Poblacion)',
                'status' => 'compliant',
                'reports' => 12,
                'on_time' => 11,
                'active_programs' => 4,
                'participation_rate' => 86,
                'last_update' => 'Mar 20, 2026',
                'sk_chairman' => 'Alyssa M. Ramos',
                'report_rate' => 92,
            ],
            'brgy-2-poblacion' => [
                'slug' => 'brgy-2-poblacion',
                'name' => 'Brgy. II (Poblacion)',
                'status' => 'partial',
                'reports' => 10,
                'on_time' => 8,
                'active_programs' => 3,
                'participation_rate' => 78,
                'last_update' => 'Mar 18, 2026',
                'sk_chairman' => 'Maria C. Santos',
                'report_rate' => 83,
            ],
            'brgy-3-poblacion' => [
                'slug' => 'brgy-3-poblacion',
                'name' => 'Brgy. III (Poblacion)',
                'status' => 'compliant',
                'reports' => 11,
                'on_time' => 10,
                'active_programs' => 4,
                'participation_rate' => 83,
                'last_update' => 'Mar 19, 2026',
                'sk_chairman' => 'Juan P. Reyes',
                'report_rate' => 92,
            ],
            'brgy-4-poblacion' => [
                'slug' => 'brgy-4-poblacion',
                'name' => 'Brgy. IV (Poblacion)',
                'status' => 'partial',
                'reports' => 8,
                'on_time' => 6,
                'active_programs' => 2,
                'participation_rate' => 66,
                'last_update' => 'Mar 17, 2026',
                'sk_chairman' => 'Rosa L. Fernandez',
                'report_rate' => 67,
            ],
            'brgy-5-poblacion' => [
                'slug' => 'brgy-5-poblacion',
                'name' => 'Brgy. V (Poblacion)',
                'status' => 'non-compliant',
                'reports' => 5,
                'on_time' => 3,
                'active_programs' => 1,
                'participation_rate' => 49,
                'last_update' => 'Mar 16, 2026',
                'sk_chairman' => 'Carlos M. Diaz',
                'report_rate' => 42,
            ],
            'labuin' => [
                'slug' => 'labuin',
                'name' => 'Labuin',
                'status' => 'partial',
                'reports' => 9,
                'on_time' => 7,
                'active_programs' => 3,
                'participation_rate' => 74,
                'last_update' => 'Mar 18, 2026',
                'sk_chairman' => 'Angela R. Torres',
                'report_rate' => 75,
            ],
            'pagsawitan' => [
                'slug' => 'pagsawitan',
                'name' => 'Pagsawitan',
                'status' => 'compliant',
                'reports' => 12,
                'on_time' => 12,
                'active_programs' => 5,
                'participation_rate' => 88,
                'last_update' => 'Mar 20, 2026',
                'sk_chairman' => 'Miguel A. Gonzales',
                'report_rate' => 100,
            ],
            'san-jose' => [
                'slug' => 'san-jose',
                'name' => 'San Jose',
                'status' => 'non-compliant',
                'reports' => 4,
                'on_time' => 2,
                'active_programs' => 1,
                'participation_rate' => 43,
                'last_update' => 'Mar 15, 2026',
                'sk_chairman' => 'Patricia V. Morales',
                'report_rate' => 33,
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
                ],
                [
                    'title' => 'Barangay Clean-Up Drive',
                    'sector' => 'Environment',
                    'timeline' => 'Jan 2026',
                    'status' => 'Completed',
                    'participants' => 120,
                ],
            ],
            'current_programs' => [
                [
                    'title' => 'Skills Training and Livelihood',
                    'sector' => 'Livelihood',
                    'timeline' => 'Mar 2026 - Apr 2026',
                    'status' => 'Ongoing',
                    'participants' => 72,
                ],
                [
                    'title' => 'Sports Development League',
                    'sector' => 'Sports',
                    'timeline' => 'Mar 2026 - May 2026',
                    'status' => 'Ongoing',
                    'participants' => 110,
                ],
            ],
            'future_programs' => [
                [
                    'title' => 'Mental Wellness Caravan',
                    'sector' => 'Health',
                    'timeline' => 'May 2026',
                    'status' => 'Planned',
                    'participants' => 90,
                ],
                [
                    'title' => 'Digital Literacy Lab',
                    'sector' => 'Education',
                    'timeline' => 'Jun 2026',
                    'status' => 'Planned',
                    'participants' => 80,
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
                'program_list' => array_merge(
                    array_map(fn($p) => array_merge($p, ['budget_allocated' => '₱25,000', 'budget_used' => '₱22,500', 'start_date' => 'Nov 2025', 'end_date' => 'Dec 2025', 'reports' => ['Q4 2025 Report.pdf']]), $basePrograms['previous_programs']),
                    array_map(fn($p) => array_merge($p, ['budget_allocated' => '₱30,000', 'budget_used' => '₱14,200', 'start_date' => 'Mar 2026', 'end_date' => 'May 2026', 'reports' => []]), $basePrograms['current_programs']),
                    array_map(fn($p) => array_merge($p, ['budget_allocated' => '₱20,000', 'budget_used' => '₱0', 'start_date' => 'May 2026', 'end_date' => 'Jun 2026', 'reports' => []]), $basePrograms['future_programs'])
                ),
                'abyip_data' => [
                    'annual_budget' => 150000,
                    'budget_utilized' => 98500,
                    'budget_utilization_rate' => 65.67,
                    'remaining_balance' => 51500,
                    'project_count' => 8,
                    'reports' => [
                        [
                            'name' => 'Q1 2026 ABYIP Report',
                            'date_submitted' => 'Mar 15, 2026',
                            'submitted_by' => 'Alyssa M. Ramos',
                            'file' => 'Q1_2026_ABYIP_Report.pdf',
                        ],
                        [
                            'name' => 'Q4 2025 ABYIP Report',
                            'date_submitted' => 'Dec 28, 2025',
                            'submitted_by' => 'Alyssa M. Ramos',
                            'file' => 'Q4_2025_ABYIP_Report.pdf',
                        ],
                        [
                            'name' => 'Q3 2025 ABYIP Report',
                            'date_submitted' => 'Sep 30, 2025',
                            'submitted_by' => 'Mark D. Villanueva',
                            'file' => 'Q3_2025_ABYIP_Report.pdf',
                        ],
                    ],
                ],
                'programs_data' => [
                    'total_programs' => 12,
                    'ongoing_programs' => 4,
                    'participation_rate' => 82,
                    'attendance_rate' => 78,
                    'evaluation_rate' => 85,
                    'reports' => [
                        [
                            'id' => 1,
                            'name' => 'Youth Leadership Bootcamp',
                            'date' => 'Mar 15, 2026',
                            'participants_registered' => 96,
                            'attendance' => 88,
                            'evaluation' => 92,
                            'report_submitted' => 'Yes',
                            'file' => 'Youth_Leadership_Report.pdf',
                            'description' => 'A comprehensive leadership training program designed to develop critical thinking and decision-making skills among youth leaders.',
                            'date_happened' => 'March 10-15, 2026',
                            'participants_joined' => 88,
                            'objectives' => ['Develop leadership skills', 'Build confidence', 'Foster teamwork'],
                            'outcomes' => 'Successfully trained 88 youth leaders with 92% satisfaction rate',
                        ],
                        [
                            'id' => 2,
                            'name' => 'Skills Training and Livelihood',
                            'date' => 'Mar 20, 2026',
                            'participants_registered' => 72,
                            'attendance' => 65,
                            'evaluation' => 78,
                            'report_submitted' => 'Yes',
                            'file' => 'Skills_Training_Report.pdf',
                            'description' => 'Vocational training program providing practical skills in various trades and livelihood opportunities.',
                            'date_happened' => 'March 15-20, 2026',
                            'participants_joined' => 65,
                            'objectives' => ['Provide vocational skills', 'Create livelihood opportunities', 'Increase income generation'],
                            'outcomes' => '65 youth trained in various trades with job placement assistance',
                        ],
                        [
                            'id' => 3,
                            'name' => 'Sports Development League',
                            'date' => 'Mar 25, 2026',
                            'participants_registered' => 110,
                            'attendance' => 102,
                            'evaluation' => 88,
                            'report_submitted' => 'Pending',
                            'file' => null,
                            'description' => 'Community sports program promoting physical fitness, teamwork, and healthy lifestyle among youth.',
                            'date_happened' => 'March 20-25, 2026',
                            'participants_joined' => 102,
                            'objectives' => ['Promote physical fitness', 'Build team spirit', 'Develop athletic skills'],
                            'outcomes' => '102 youth participated in various sports activities',
                        ],
                        [
                            'id' => 4,
                            'name' => 'Barangay Clean-Up Drive',
                            'date' => 'Feb 28, 2026',
                            'participants_registered' => 120,
                            'attendance' => 115,
                            'evaluation' => 95,
                            'report_submitted' => 'Yes',
                            'file' => 'Cleanup_Drive_Report.pdf',
                            'description' => 'Environmental awareness and community service program focused on barangay cleanliness and sustainability.',
                            'date_happened' => 'February 28, 2026',
                            'participants_joined' => 115,
                            'objectives' => ['Clean the barangay', 'Promote environmental awareness', 'Build community spirit'],
                            'outcomes' => '115 youth volunteers cleaned 5 barangay areas',
                        ],
                        [
                            'id' => 5,
                            'name' => 'Mental Wellness Caravan',
                            'date' => 'Apr 10, 2026',
                            'participants_registered' => 90,
                            'attendance' => 82,
                            'evaluation' => 87,
                            'report_submitted' => 'Yes',
                            'file' => 'Wellness_Caravan_Report.pdf',
                            'description' => 'Mental health awareness and wellness program providing counseling and support services to youth.',
                            'date_happened' => 'April 8-10, 2026',
                            'participants_joined' => 82,
                            'objectives' => ['Promote mental health awareness', 'Provide counseling services', 'Reduce stigma'],
                            'outcomes' => '82 youth received mental health counseling and support',
                        ],
                        [
                            'id' => 6,
                            'name' => 'Digital Literacy Lab',
                            'date' => 'Apr 15, 2026',
                            'participants_registered' => 80,
                            'attendance' => 75,
                            'evaluation' => 81,
                            'report_submitted' => 'Pending',
                            'file' => null,
                            'description' => 'Technology training program teaching digital skills and computer literacy to youth.',
                            'date_happened' => 'April 12-15, 2026',
                            'participants_joined' => 75,
                            'objectives' => ['Teach digital skills', 'Improve computer literacy', 'Prepare for digital economy'],
                            'outcomes' => '75 youth trained in basic computer and digital skills',
                        ],
                    ],
                ],
            ];
        }

        return $details;
    }
}
