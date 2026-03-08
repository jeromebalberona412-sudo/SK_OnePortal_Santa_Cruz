<?php

namespace App\Modules\AuditLog\Controllers;

use App\Modules\Shared\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\AuditLog\Models\SkFedAuthAuditLog;
use Illuminate\Support\Facades\Schema;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $perPage = max(5, min(100, (int) $request->get('per_page', 7)));
        $search = trim((string) $request->get('search', ''));
        $event = trim((string) $request->get('event', ''));
        $outcome = trim((string) $request->get('outcome', ''));
        $dateFrom = trim((string) $request->get('date_from', ''));
        $dateTo = trim((string) $request->get('date_to', ''));

        $hasTenantColumn = Schema::hasColumn('sk_fed_auth_audit_logs', 'tenant_id');
        $hasActorEmailColumn = Schema::hasColumn('sk_fed_auth_audit_logs', 'actor_email');
        $hasOutcomeColumn = Schema::hasColumn('sk_fed_auth_audit_logs', 'outcome');
        $hasResourceTypeColumn = Schema::hasColumn('sk_fed_auth_audit_logs', 'resource_type');
        $hasResourceIdColumn = Schema::hasColumn('sk_fed_auth_audit_logs', 'resource_id');

        $tenantId = $request->user()?->tenant_id;

        $applyTenantScope = function ($query) use ($hasTenantColumn, $tenantId) {
            if (! $hasTenantColumn || $tenantId === null) {
                return;
            }

            // Keep legacy rows with null tenant_id visible during phased rollout.
            $query->where(function ($tenantQuery) use ($tenantId) {
                $tenantQuery->where('tenant_id', $tenantId)
                    ->orWhereNull('tenant_id');
            });
        };

        $logsQuery = SkFedAuthAuditLog::query()
            ->with('user:id,email')
            ->orderByDesc('created_at');

        $applyTenantScope($logsQuery);

        if ($search !== '') {
            $logsQuery->where(function ($query) use (
                $search,
                $hasActorEmailColumn,
                $hasOutcomeColumn,
                $hasResourceTypeColumn,
                $hasResourceIdColumn,
            ) {
                $query->where('event', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhere('user_agent', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('email', 'like', "%{$search}%");
                    });

                if ($hasActorEmailColumn) {
                    $query->orWhere('actor_email', 'like', "%{$search}%");
                }

                if ($hasOutcomeColumn) {
                    $query->orWhere('outcome', 'like', "%{$search}%");
                }

                if ($hasResourceTypeColumn) {
                    $query->orWhere('resource_type', 'like', "%{$search}%");
                }

                if ($hasResourceIdColumn) {
                    $query->orWhere('resource_id', 'like', "%{$search}%");
                }

                if (ctype_digit($search)) {
                    $query->orWhere('id', (int) $search)
                        ->orWhere('user_id', (int) $search);
                }
            });
        }

        if ($event !== '') {
            $logsQuery->where('event', $event);
        }

        if ($outcome !== '' && $hasOutcomeColumn) {
            $logsQuery->where('outcome', $outcome);
        }

        if ($dateFrom !== '') {
            $logsQuery->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo !== '') {
            $logsQuery->whereDate('created_at', '<=', $dateTo);
        }

        $logs = $logsQuery->paginate($perPage)->withQueryString();

        $eventsQuery = SkFedAuthAuditLog::query();
        $applyTenantScope($eventsQuery);

        $events = $eventsQuery
            ->select('event')
            ->distinct()
            ->orderBy('event')
            ->pluck('event');

        $outcomes = collect();

        if ($hasOutcomeColumn) {
            $outcomesQuery = SkFedAuthAuditLog::query();
            $applyTenantScope($outcomesQuery);

            $outcomes = $outcomesQuery
                ->select('outcome')
                ->whereNotNull('outcome')
                ->distinct()
                ->orderBy('outcome')
                ->pluck('outcome');

            if ($outcomes->isEmpty()) {
                $outcomes = collect(['success', 'failed', 'blocked']);
            }
        }

        return view('auditlogs::auditlogs', [
            'logs' => $logs,
            'events' => $events,
            'outcomes' => $outcomes,
            'perPage' => $perPage,
            'filters' => [
                'search' => $search,
                'event' => $event,
                'outcome' => $outcome,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ]
        ]);
    }
}
