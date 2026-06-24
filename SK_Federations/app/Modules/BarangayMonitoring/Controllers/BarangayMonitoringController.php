<?php

namespace App\Modules\BarangayMonitoring\Controllers;

use App\Modules\BarangayMonitoring\Services\AbyipSubmissionScheduleService;
use App\Modules\BarangayMonitoring\Services\BarangayMonitoringService;
use App\Modules\Shared\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BarangayMonitoringController extends Controller
{
    public function __construct(
        private readonly BarangayMonitoringService $monitoringService,
        private readonly AbyipSubmissionScheduleService $scheduleService,
    ) {
    }

    public function index(Request $request): View
    {
        $barangays = array_values($this->barangayCatalog());
        $stats = $this->buildStats($barangays);

        return view('barangay_monitoring::index', [
            'user' => $request->user(),
            'barangays' => $barangays,
            'stats' => $stats,
            'abyipSchedule' => $this->scheduleService->currentSchedule(),
            'abyipSchedules' => $this->scheduleService->listSchedules()->values()->all(),
        ]);
    }

    public function show(Request $request, string $barangay): View
    {
        $catalog = $this->barangayCatalog();
        $detail = $this->barangayDetails();

        if (! isset($catalog[$barangay], $detail[$barangay])) {
            abort(404);
        }

        $barangayId = $this->monitoringService->resolveBarangayId($barangay);
        $barangayData = array_merge($catalog[$barangay], $detail[$barangay]);
        $barangayData['barangay_id'] = $barangayId;
        $barangayData['abyip']['reports'] = $this->monitoringService->getAbyipReports($barangayId);
        $barangayData['accomplishments'] = $this->monitoringService->getApprovedAccomplishments($barangayId);
        $barangayData['accomplishment_years'] = $this->monitoringService->accomplishmentYears($barangayId);
        $barangayData['accomplishment_terms'] = $this->monitoringService->accomplishmentTerms($barangayId);
        $barangayData['abyip_schedule'] = $this->scheduleService->currentSchedule();

        $complianceStatus = $this->calculateComplianceStatus($barangayData);
        $barangayData['compliance_status'] = $complianceStatus['status'];
        $barangayData['compliance_details'] = $complianceStatus['details'];
        $barangayData['warnings'] = $this->getWarningsForBarangay($barangayData);

        return view('barangay_monitoring::show', [
            'user' => $request->user(),
            'barangayData' => $barangayData,
        ]);
    }

    public function scheduleList(): JsonResponse
    {
        return response()->json([
            'data' => $this->scheduleService->listSchedules()->values(),
            'current' => $this->scheduleService->currentSchedule(),
        ]);
    }

    public function scheduleStore(Request $request): JsonResponse
    {
        try {
            $schedule = $this->scheduleService->create($request->user(), $request->all());
        } catch (ValidationException $exception) {
            return response()->json([
                'message' => collect($exception->errors())->flatten()->first(),
                'errors' => $exception->errors(),
            ], 422);
        }

        return response()->json([
            'message' => 'ABYIP submission schedule created.',
            'data' => $schedule,
        ], 201);
    }

    public function scheduleUpdate(Request $request, int $id): JsonResponse
    {
        try {
            $schedule = $this->scheduleService->update($request->user(), $id, $request->all());
        } catch (ValidationException $exception) {
            return response()->json([
                'message' => collect($exception->errors())->flatten()->first(),
                'errors' => $exception->errors(),
            ], 422);
        }

        return response()->json([
            'message' => 'ABYIP submission schedule updated.',
            'data' => $schedule,
        ]);
    }

    public function scheduleExtend(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'new_deadline' => ['required', 'date'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $schedule = $this->scheduleService->extendDeadline(
                $request->user(),
                $id,
                $validated['new_deadline'],
                $validated['reason'] ?? null
            );
        } catch (ValidationException $exception) {
            return response()->json([
                'message' => collect($exception->errors())->flatten()->first(),
                'errors' => $exception->errors(),
            ], 422);
        }

        return response()->json([
            'message' => 'ABYIP submission deadline extended.',
            'data' => $schedule,
        ]);
    }

    public function scheduleCancel(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $schedule = $this->scheduleService->cancel($request->user(), $id, $validated['reason'] ?? null);
        } catch (ValidationException $exception) {
            return response()->json([
                'message' => collect($exception->errors())->flatten()->first(),
                'errors' => $exception->errors(),
            ], 422);
        }

        return response()->json([
            'message' => 'ABYIP submission schedule cancelled.',
            'data' => $schedule,
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
        $map = $this->monitoringService->slugToNameMap();
        $catalog = [];

        foreach ($map as $slug => $name) {
            $catalog[$slug] = [
                'slug' => $slug,
                'name' => $name,
                'status' => 'non-compliant',
                'reports' => 0,
                'on_time' => 0,
                'active_programs' => 0,
                'participation_rate' => 0,
                'last_update' => 'No updates yet',
                'sk_chairman' => 'Not assigned',
                'report_rate' => 0,
            ];
        }

        return $catalog;
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
                    'total_youth_population' => 0,
                    'total_programs_created' => 0,
                    'total_ongoing' => 0,
                    'total_completed' => 0,
                    'total_participants' => 0,
                    'overall_performance' => 'No data',
                ],
                'performance_summary' => [
                    'completion_rate' => 0,
                    'attendance_rate' => 0,
                    'budget_efficiency' => 0,
                    'most_active_sector' => 'N/A',
                    'most_successful_program' => 'N/A',
                    'low_participation_count' => 0,
                ],
                'program_list' => [],
                'accomplishments' => [],
                'accomplishment_years' => [],
                'accomplishment_terms' => [],
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

    /**
     * @param  array<string, mixed>  $barangayData
     * @return array<string, mixed>
     */
    private function calculateComplianceStatus(array $barangayData): array
    {
        $currentYear = (int) date('Y');
        $abyipReports = $barangayData['abyip']['reports'] ?? [];
        $accomplishmentReports = $barangayData['accomplishments'] ?? [];

        $abyipSubmittedThisYear = collect($abyipReports)->contains(function ($report) use ($currentYear) {
            $reportYear = (int) date('Y', strtotime((string) ($report['date_submitted'] ?? '')));

            return $reportYear === $currentYear || (int) ($report['fiscal_year'] ?? 0) === $currentYear;
        });

        $accomplishmentSubmittedThisYear = collect($accomplishmentReports)->contains(function ($report) use ($currentYear) {
            return (int) ($report['year'] ?? 0) === $currentYear;
        });

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

    /**
     * @param  array<string, mixed>  $barangayData
     * @return list<array<string, mixed>>
     */
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
