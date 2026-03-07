<?php

namespace App\Modules\AuditLog\Controllers;

use App\Modules\Shared\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\AuditLog\Models\SkFedAuthAuditLog;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $perPage = max(5, min(100, (int) $request->get('per_page', 7)));
        $search = trim((string) $request->get('search', ''));
        $event = trim((string) $request->get('event', ''));
        $dateFrom = trim((string) $request->get('date_from', ''));
        $dateTo = trim((string) $request->get('date_to', ''));

        $logsQuery = SkFedAuthAuditLog::query()->orderByDesc('created_at');

        if ($search !== '') {
            $logsQuery->where(function ($query) use ($search) {
                $query->where('event', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhere('user_agent', 'like', "%{$search}%");

                if (ctype_digit($search)) {
                    $query->orWhere('id', (int) $search)
                        ->orWhere('user_id', (int) $search);
                }
            });
        }

        if ($event !== '') {
            $logsQuery->where('event', $event);
        }

        if ($dateFrom !== '') {
            $logsQuery->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo !== '') {
            $logsQuery->whereDate('created_at', '<=', $dateTo);
        }

        $logs = $logsQuery->paginate($perPage)->withQueryString();
        $events = SkFedAuthAuditLog::query()
            ->select('event')
            ->distinct()
            ->orderBy('event')
            ->pluck('event');

        return view('auditlogs::auditlogs', [
            'logs' => $logs,
            'events' => $events,
            'perPage' => $perPage,
            'filters' => [
                'search' => $search,
                'event' => $event,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ]
        ]);
    }
}
