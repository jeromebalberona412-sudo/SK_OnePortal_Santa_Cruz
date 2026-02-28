<?php

namespace App\Modules\Profile\Controllers;

use App\Modules\Profile\Models\Barangay;
use App\Modules\Profile\Models\OfficialProfile;
use App\Modules\Shared\Models\User;
use App\Modules\Shared\Controllers\Controller;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function index(Request $request): View|ViewContract
    {
        /** @var User $user */
        $user = $request->user();

        $officialProfile = null;
        $barangays = collect();

        if (Schema::hasTable('official_profiles')) {
            $officialProfile = OfficialProfile::query()
                ->where('user_id', $user->id)
                ->first();
        }

        if (Schema::hasTable('barangays')) {
            /** @var Collection<int, Barangay> $barangays */
            $barangays = Barangay::query()
                ->select(['id', 'name'])
                ->orderBy('name')
                ->get();
        }

        return view('profile::profile', [
            'user' => $user,
            'officialProfile' => $officialProfile,
            'barangays' => $barangays,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        return redirect()
            ->route('profile')
            ->withErrors(['profile' => 'Profile editing is currently disabled.']);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        return redirect()
            ->route('profile')
            ->withErrors(['password' => 'Password change is currently disabled.']);
    }
}