<?php

namespace App\Modules\Archive\Controllers;

use App\Modules\Shared\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArchiveController extends Controller
{
    public function index(Request $request): View
    {
        $deletedReports = $this->getDeletedReports();
        $archivedReports = $this->getArchivedReports();
        
        return view('archive::index', [
            'user' => $request->user(),
            'deletedReports' => $deletedReports,
            'archivedReports' => $archivedReports,
        ]);
    }

    private function getDeletedReports(): array
    {
        $now = time();
        $thirtyDaysAgo = $now - (30 * 24 * 60 * 60);
        
        return [
            [
                'id' => 1,
                'name' => 'Q1 2026 ABYIP Report - Palasan',
                'type' => 'abyip',
                'barangay' => 'Palasan',
                'date_submitted' => 'Apr 15, 2026',
                'date_deleted' => 'May 1, 2026',
                'timestamp_deleted' => strtotime('May 1, 2026'),
                'status' => 'rejected',
                'reason' => 'Corrupted File',
                'days_until_permanent_delete' => max(0, 30 - ceil(($now - strtotime('May 1, 2026')) / (24 * 60 * 60))),
            ],
            [
                'id' => 2,
                'name' => 'Youth Leadership Summit - Accomplishment',
                'type' => 'accomplishment',
                'barangay' => 'Poblacion II',
                'date_submitted' => 'Apr 20, 2026',
                'date_deleted' => 'Apr 28, 2026',
                'timestamp_deleted' => strtotime('Apr 28, 2026'),
                'status' => 'rejected',
                'reason' => 'Missing Attachment',
                'days_until_permanent_delete' => max(0, 30 - ceil(($now - strtotime('Apr 28, 2026')) / (24 * 60 * 60))),
            ],
            [
                'id' => 3,
                'name' => 'Q4 2025 ABYIP Report - Malinao',
                'type' => 'abyip',
                'barangay' => 'Malinao',
                'date_submitted' => 'Jan 10, 2026',
                'date_deleted' => 'Apr 15, 2026',
                'timestamp_deleted' => strtotime('Apr 15, 2026'),
                'status' => 'rejected',
                'reason' => 'Invalid File Format',
                'days_until_permanent_delete' => max(0, 30 - ceil(($now - strtotime('Apr 15, 2026')) / (24 * 60 * 60))),
            ],
        ];
    }

    private function getArchivedReports(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'Q3 2025 ABYIP Report - Alipit',
                'type' => 'abyip',
                'barangay' => 'Alipit',
                'date_submitted' => 'Oct 20, 2025',
                'date_archived' => 'Mar 15, 2026',
                'status' => 'compliant',
                'archived_by' => 'Admin User',
            ],
            [
                'id' => 2,
                'name' => 'Community Clean-up Drive - Accomplishment',
                'type' => 'accomplishment',
                'barangay' => 'Bagumbayan',
                'date_submitted' => 'Jan 15, 2026',
                'date_archived' => 'Apr 1, 2026',
                'status' => 'compliant',
                'archived_by' => 'Admin User',
            ],
            [
                'id' => 3,
                'name' => 'Q2 2025 ABYIP Report - Duhat',
                'type' => 'abyip',
                'barangay' => 'Duhat',
                'date_submitted' => 'Jul 10, 2025',
                'date_archived' => 'Feb 20, 2026',
                'status' => 'compliant',
                'archived_by' => 'Admin User',
            ],
            [
                'id' => 4,
                'name' => 'Sports Festival - Accomplishment',
                'type' => 'accomplishment',
                'barangay' => 'Jasaan',
                'date_submitted' => 'Feb 28, 2026',
                'date_archived' => 'Apr 10, 2026',
                'status' => 'compliant',
                'archived_by' => 'Admin User',
            ],
        ];
    }
}
