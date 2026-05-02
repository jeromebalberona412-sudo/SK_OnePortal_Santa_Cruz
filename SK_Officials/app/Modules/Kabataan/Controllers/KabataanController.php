<?php

namespace App\Modules\Kabataan\Controllers;

use App\Http\Controllers\Controller;
use App\Models\KabataanRegistration;
use App\Models\PreviousKabataan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KabataanController extends Controller
{
    public function index()
    {
        return view('Kabataan::kabataan');
    }

    public function data(Request $request)
    {
        $user = Auth::user();

        if (!$user || !$user->barangay_id) {
            return response()->json(['data' => [], 'stats' => $this->emptyStats()]);
        }

        // Active kabataan = approved registrations
        $query = KabataanRegistration::with('barangay')
            ->forBarangay($user->barangay_id)
            ->where('status', 'active')
            ->whereIn('evaluation_status', ['active', 'Auto Approved'])
            ->orderBy('last_name')
            ->orderBy('first_name');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('last_name', 'ilike', "%{$s}%")
                  ->orWhere('first_name', 'ilike', "%{$s}%");
            });
        }

        $records = $query->get();

        $val = fn($fd, $key) => is_array($fd[$key] ?? null)
            ? ($fd[$key][0] ?? '—')
            : ($fd[$key] ?? '—');

        $data = $records->map(function ($r) use ($val) {
            $fd = $r->form_data ?? [];
            return [
                'id'             => $r->id,
                'respondent_no'  => str_pad($r->id, 3, '0', STR_PAD_LEFT),
                'last_name'      => $r->last_name,
                'first_name'     => $r->first_name,
                'middle_name'    => $r->middle_name,
                'suffix'         => $r->suffix,
                'full_name'      => $r->full_name,
                'age'            => $val($fd, 'age'),
                'sex'            => $val($fd, 'sex'),
                'birthday'       => $val($fd, 'birthday'),
                'email'          => $r->email,
                'contact_number' => $r->contact_number,
                'barangay'       => $r->barangay?->name ?? '—',
                'purok_zone'     => $val($fd, 'purok_zone'),
                'education'      => $val($fd, 'education'),
                'civil_status'   => $val($fd, 'civil_status'),
                'youth_classification' => $val($fd, 'youth_classification'),
                'youth_age_group'      => $val($fd, 'youth_age_group'),
                'work_status'    => $val($fd, 'work_status'),
                'sk_voter'       => $val($fd, 'sk_voter'),
                'national_voter' => $val($fd, 'national_voter'),
                'sk_voted'       => $val($fd, 'sk_voted'),
                'vote_frequency' => $val($fd, 'vote_frequency'),
                'kk_assembly'    => $val($fd, 'kk_assembly'),
                'kk_reason'      => is_array($fd['kk_reason'] ?? null) ? $fd['kk_reason'] : [],
                'facebook'       => $val($fd, 'facebook'),
                'group_chat'     => $val($fd, 'group_chat'),
                'signature'      => $fd['signature'] ?? null,
                'submitted_at'   => $r->submitted_at?->format('m/d/Y'),
                'reviewed_at'    => $r->reviewed_at?->format('m/d/Y'),
                'evaluation_status' => $r->evaluation_status,
            ];
        });

        $all = KabataanRegistration::forBarangay($user->barangay_id)->get();
        $stats = [
            'active'  => $all->where('status', 'active')->count(),
            'pending' => $all->whereIn('evaluation_status', ['Not Profiled', 'Wrong Credentials', 'Duplicate'])->count(),
            'rejected' => $all->where('status', 'rejected')->count(),
            'total'   => $all->count(),
        ];

        return response()->json(['data' => $data, 'stats' => $stats]);
    }
}
