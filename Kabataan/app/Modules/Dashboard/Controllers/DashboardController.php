<?php

namespace App\Modules\Dashboard\Controllers;

use App\Http\Controllers\Controller;
use App\Models\KabataanRegistration;
use App\Modules\KKProfiling\Controllers\KKProfilingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        $registration = KabataanRegistration::with('barangay')
            ->where('user_id', $user->id)
            ->latest()
            ->first();

        $formData = $registration?->form_data ?? [];
        $respondentNumber = $formData['respondent_number'] ?? null;

        $barangayName = $registration?->barangay?->name ?? 'Santa Cruz';

        return view('dashboard::dashboard', [
            'user'                => $user,
            'showKkUpdateModal'   => (bool) ($registration && session()->pull('show_kk_profiling_update', false)),
            'kkUpdateBarangay'    => $registration ? $barangayName : null,
            'kkRespondentNumber'  => $respondentNumber ?? '',
            'kkRespondentDisplay' => KKProfilingController::formatRespondentDisplay($respondentNumber),
        ])->withHeaders([
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma'        => 'no-cache',
            'Expires'       => 'Sat, 01 Jan 2000 00:00:00 GMT',
        ]);
    }
    public function barangay(Request $request, string $slug)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        $barangays = [
            'alipit'         => 'Alipit',
            'bagumbayan'     => 'Bagumbayan',
            'bubukal'        => 'Bubukal',
            'duhat'          => 'Duhat',
            'gatid'          => 'Gatid',
            'labuin'         => 'Labuin',
            'pagsawitan'     => 'Pagsawitan',
            'san-jose'       => 'San Jose',
            'santisima-cruz' => 'Santisima Cruz',
        ];

        $name = $barangays[$slug] ?? ucfirst(str_replace('-', ' ', $slug));

        $colors = [
            'alipit'         => '#4CAF50',
            'bagumbayan'     => '#2196F3',
            'bubukal'        => '#9C27B0',
            'duhat'          => '#FF9800',
            'gatid'          => '#009688',
            'labuin'         => '#f44336',
            'pagsawitan'     => '#673AB7',
            'san-jose'       => '#0450a8',
            'santisima-cruz' => '#FF5722',
        ];

        $officers = [
            'chairman'   => '[SK Chairman]',
            'vice'       => '[Vice Chairman]',
            'secretary'  => '[Secretary]',
            'treasurer'  => '[Treasurer]',
            'auditor'    => '[Auditor]',
            'pro'        => '[PRO]',
            'councilors' => ['[Councilor 1]','[Councilor 2]','[Councilor 3]','[Councilor 4]','[Councilor 5]','[Councilor 6]','[Councilor 7]'],
        ];

        $posts = [];

        return view('dashboard::barangay', [
            'user'     => $user,
            'slug'     => $slug,
            'name'     => $name,
            'color'    => $colors[$slug] ?? '#667eea',
            'officers' => $officers,
            'posts'    => $posts,
        ])->withHeaders([
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma'        => 'no-cache',
            'Expires'       => 'Sat, 01 Jan 2000 00:00:00 GMT',
        ]);
    }
}

