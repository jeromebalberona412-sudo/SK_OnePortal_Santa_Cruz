<?php

namespace App\Modules\KKProfilingRequests\Controllers;

use App\Http\Controllers\Controller;
use App\Models\KabataanRegistration;
use App\Modules\KKProfilingRequests\Notifications\KabataanApprovedNotification;
use App\Modules\KKProfilingRequests\Notifications\KabataanRejectedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class KKProfilingRequestsController extends Controller
{
    public function index()
    {
        return view('KKProfilingRequests::kkprofiling-requests');
    }

    public function data(Request $request)
    {
        $user = Auth::user();

        if (!$user || !$user->barangay_id) {
            return response()->json(['data' => [], 'stats' => $this->emptyStats()]);
        }

        $query = KabataanRegistration::with('barangay')
            ->forBarangay($user->barangay_id)
            ->whereNotIn('status', ['rejected', 'archived'])
            ->whereNotIn('evaluation_status', ['Auto Approved', 'active'])
            ->orderBy('last_name')
            ->orderBy('first_name');

        // Status filter — filter by evaluation_status
        if ($request->filled('status') && $request->status !== 'All') {
            $query->where('evaluation_status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($qb) use ($q) {
                $qb->where('last_name', 'ilike', "%{$q}%")
                   ->orWhere('first_name', 'ilike', "%{$q}%")
                   ->orWhere('email', 'ilike', "%{$q}%")
                   ->orWhere('contact_number', 'ilike', "%{$q}%");
            });
        }

        // Purok filter
        if ($request->filled('purok')) {
            $query->whereJsonContains('form_data->purok_zone', $request->purok);
        }

        // Voter filter
        if ($request->filled('voter')) {
            $query->whereJsonContains('form_data->sk_voter', $request->voter);
        }

        $registrations = $query->get();

        $data = $registrations->map(function ($r) {
            $formData = $r->form_data ?? [];

            // Helper: extract value whether stored as string or array
            $val = fn($key) => is_array($formData[$key] ?? null)
                ? ($formData[$key][0] ?? '—')
                : ($formData[$key] ?? '—');

            return [
                'id'              => $r->id,
                'last_name'       => $r->last_name,
                'first_name'      => $r->first_name,
                'middle_name'     => $r->middle_name,
                'suffix'          => $r->suffix,
                'full_name'       => $r->full_name,
                'age'             => $val('age'),
                'birthday'        => $val('birthday'),
                'sex'             => $val('sex'),
                'email'           => $r->email,
                'contact_number'  => $r->contact_number,
                'barangay'        => $r->barangay?->name ?? '—',
                'purok_zone'      => $val('purok_zone'),
                'sk_voter'        => $val('sk_voter'),
                'national_voter'  => $val('national_voter'),
                'civil_status'    => $val('civil_status'),
                'youth_classification' => $val('youth_classification'),
                'youth_age_group' => $val('youth_age_group'),
                'work_status'     => $val('work_status'),
                'education'       => $val('education'),
                'sk_voted'        => $val('sk_voted'),
                'vote_frequency'  => $val('vote_frequency'),
                'kk_assembly'     => $val('kk_assembly'),
                'kk_reason'       => is_array($formData['kk_reason'] ?? null) ? $formData['kk_reason'] : [],
                'facebook'        => $val('facebook'),
                'group_chat'      => $formData['group_chat'] ?? '—',
                'signature'       => $formData['signature'] ?? '—',
                'status'          => $r->status,
                'evaluation_status' => $r->evaluation_status,
                'evaluation_notes'  => $r->evaluation_notes,
                'submitted_at'    => $r->submitted_at?->format('m/d/Y'),
                'review_notes'    => $r->review_notes,
            ];
        });

        $all = KabataanRegistration::forBarangay($user->barangay_id)->get();

        $stats = [
            'active'               => $all->where('evaluation_status', 'Auto Approved')->count() + $all->where('status', 'active')->count(),
            'pending_verification' => $all->whereIn('evaluation_status', ['Not Profiled', 'Wrong Credentials'])->count(),
            'rejected'             => $all->where('evaluation_status', 'Duplicate')->count() + $all->where('status', 'rejected')->count(),
            'total'                => $all->count(),
        ];

        return response()->json(['data' => $data, 'stats' => $stats]);
    }

    public function approve(Request $request, int $id)
    {
        try {
            $user = Auth::user();
            
            if (!$user || !$user->barangay_id) {
                \Log::error('Approve failed: No authenticated user or barangay_id', ['id' => $id]);
                return response()->json(['success' => false, 'message' => 'Authentication error'], 401);
            }

            $registration = KabataanRegistration::forBarangay($user->barangay_id)->findOrFail($id);
            
            \Log::info('Approving registration', [
                'id' => $id,
                'current_status' => $registration->status,
                'evaluation_status' => $registration->evaluation_status,
                'user_id' => $user->id
            ]);

            DB::transaction(function () use ($registration, $user) {
                if ($registration->evaluation_status === 'Not Profiled') {
                    $prevKabataan = \App\Models\PreviousKabataan::create([
                        'kabataan_registration_id' => $registration->id,
                        'tenant_id'                => $registration->tenant_id,
                        'barangay_id'              => $registration->barangay_id,
                        'moved_by_user_id'         => $user->id,
                        'last_name'                => $registration->last_name,
                        'first_name'               => $registration->first_name,
                        'middle_name'              => $registration->middle_name,
                        'suffix'                   => $registration->suffix,
                        'email'                    => $registration->email,
                        'contact_number'           => $registration->contact_number,
                        'form_data'                => $registration->form_data,
                        'profiling_year'           => now()->year,
                        'moved_at'                 => now(),
                    ]);
                    \Log::info('Created PreviousKabataan record', ['prev_id' => $prevKabataan->id]);
                }

                $registration->update([
                    'status'              => 'active',
                    'evaluation_status'   => 'active',
                    'reviewed_by_user_id' => $user->id,
                    'reviewed_at'         => now(),
                    'review_notes'        => null,
                ]);
                
                \Log::info('Updated registration status', ['id' => $registration->id, 'new_status' => 'active']);

                if ($registration->user_id) {
                    $kabataanUser = \App\Models\User::find($registration->user_id);
                    if ($kabataanUser) {
                        $kabataanUser->update(['status' => 'ACTIVE']);
                        $kabataanUser->notify(new KabataanApprovedNotification());
                        \Log::info('Notified kabataan user', ['user_id' => $kabataanUser->id]);
                    }
                }
            });

            \Log::info('Approval completed successfully', ['id' => $id]);
            return response()->json(['success' => true, 'message' => 'Registration approved.']);
        } catch (\Exception $e) {
            \Log::error('Approve failed with exception', [
                'id' => $id,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => 'Failed to approve: ' . $e->getMessage()], 500);
        }
    }

    public function reject(Request $request, int $id)
    {
        $request->validate(['reasons' => 'required|array|min:1']);

        $user = Auth::user();
        $registration = KabataanRegistration::forBarangay($user->barangay_id)->findOrFail($id);

        $reasons = implode('; ', $request->reasons);

        $registration->update([
            'status'              => 'rejected',
            'reviewed_by_user_id' => $user->id,
            'reviewed_at'         => now(),
            'review_notes'        => $reasons,
        ]);

        // Deactivate the linked Kabataan user account
        if ($registration->user_id) {
            $kabataanUser = \App\Models\User::find($registration->user_id);
            if ($kabataanUser) {
                $kabataanUser->update(['status' => 'REJECTED']);
                $kabataanUser->notify(new KabataanRejectedNotification($reasons));
            }
        }

        return response()->json(['success' => true, 'message' => 'Registration rejected.']);
    }

    private function emptyStats(): array
    {
        return ['active' => 0, 'pending_verification' => 0, 'rejected' => 0, 'total' => 0];
    }
}
