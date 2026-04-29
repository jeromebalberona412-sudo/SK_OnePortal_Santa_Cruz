<?php

use Illuminate\Support\Facades\Route;
<<<<<<< HEAD
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Guest Routes (No Authentication Required)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    Route::get('/login', function () {
        if (session('authenticated')) {
            return redirect()->route('dashboard');
        }

        return view('Authentication::login');
    })->name('login');

    Route::post('/login', [AuthController::class, 'login'])->name('login.post');

    Route::get('/forgot-password', function () {
        if (session('authenticated')) {
            return redirect()->route('dashboard');
        }

        return view('Authentication::forgot-password');
    })->name('forgot-password');

    Route::post('/forgot-password', function (\Illuminate\Http\Request $request) {
        $request->validate(['email' => 'required|email']);
        // Password reset logic goes here when backend is ready.
        return back()->with('status', 'If that email exists, a reset link has been sent.');
    })->name('password.email');

});

/*
|--------------------------------------------------------------------------
| Simple Authentication Middleware
|--------------------------------------------------------------------------
*/

Route::middleware(['web'])->group(function () {

    Route::get('/dashboard', function () {
        // Simple session check for demo
        if (!session('authenticated')) {
            return redirect()->route('login');
        }
        return view('Dashboard::dashboard');
    })->name('dashboard');

    Route::get('/profile', function () {
        // Simple session check for demo
        if (!session('authenticated')) {
            return redirect()->route('login');
        }
        return view('Profile::profile');
    })->name('profile');

    Route::get('/change-password', function () {
        if (!session('authenticated')) {
            return redirect()->route('login');
        }
        return view('Profile::change-password');
    })->name('change-password');

    Route::get('/change-email', function () {
        if (!session('authenticated')) {
            return redirect()->route('login');
        }
        return view('Profile::change-email');
    })->name('change-email');

    Route::get('/notifications', function () {
        if (!session('authenticated')) {
            return redirect()->route('login');
        }
        return view('Profile::notification');
    })->name('notifications');

    Route::get('/calendar', function () {
        if (!session('authenticated')) {
            return redirect()->route('login');
        }
        return view('Calendar::calendar');
    })->name('calendar');

    Route::get('/announcements', function () {
        if (!session('authenticated')) {
            return redirect()->route('login');
        }
        return view('Announcement::announcement');
    })->name('announcements');

    Route::get('/announcements/barangay/{slug}', function ($slug) {
        if (!session('authenticated')) {
            return redirect()->route('login');
        }
        $brgyList = [
            'alipit'         => ['name' => 'Alipit',         'color' => '#4CAF50'],
            'bagumbayan'     => ['name' => 'Bagumbayan',     'color' => '#2196F3'],
            'bubukal'        => ['name' => 'Bubukal',        'color' => '#9C27B0'],
            'duhat'          => ['name' => 'Duhat',          'color' => '#FF9800'],
            'gatid'          => ['name' => 'Gatid',          'color' => '#009688'],
            'labuin'         => ['name' => 'Labuin',         'color' => '#f44336'],
            'pagsawitan'     => ['name' => 'Pagsawitan',     'color' => '#673AB7'],
            'san-jose'       => ['name' => 'San Jose',       'color' => '#0450a8'],
            'santisima-cruz' => ['name' => 'Santisima Cruz', 'color' => '#FF5722'],
        ];
        $brgy = $brgyList[$slug] ?? ['name' => ucfirst($slug), 'color' => '#f5c518'];
        return view('Announcement::barangay-profile', [
            'slug'  => $slug,
            'name'  => $brgy['name'],
            'color' => $brgy['color'],
        ]);
    })->name('sk-officials.barangay-profile');

    /*
    |--------------------------------------------------------------------------
    | SK Officials Modules: Committees, Programs (UI Only)
    |--------------------------------------------------------------------------
    */

    Route::get('/committees', function () {
        if (!session('authenticated')) {
            return redirect()->route('login');
        }
        return view('Committees::committees');
    })->name('committees');

    Route::get('/programs', function () {
        if (!session('authenticated')) {
            return redirect()->route('login');
        }
        return view('Programs::programs');
    })->name('programs');

    
    Route::get('/budget-finance', function () {
        if (!session('authenticated')) {
            return redirect()->route('login');
        }
        return view('BudgetFinance::budget-finance');
    })->name('budget-finance');

    // KK Profiling Requests (UI-only, auth-protected via simple session check)
    Route::get('/kk-profiling-requests', function () {
        if (!session('authenticated')) {
            return redirect()->route('login');
        }
        return view('KKProfilingRequests::kkprofiling-requests');
    })->name('kk-profiling-requests');

    // ABYIP (Annual Barangay Youth Investment Program)
    Route::get('/abyip', function () {
        if (!session('authenticated')) {
            return redirect()->route('login');
        }
        return view('ABYIP::abyip');
    })->name('abyip.index');

    // Kabataan
    Route::get('/kabataan', function () {
        if (!session('authenticated')) {
            return redirect()->route('login');
        }
        return view('Kabataan::kabataan');
    })->name('kabataan');

    // Deleted Kabataan
    Route::get('/deleted-kabataan', function () {
        if (!session('authenticated')) {
            return redirect()->route('login');
        }
        return view('Deleted_Kabataan::deleted-kabataan');
    })->name('deleted-kabataan');

    // Deleted ABYIP
    Route::get('/deleted-abyip', function () {
        if (!session('authenticated')) {
            return redirect()->route('login');
        }
        return view('Deleted_Abyip::deleted-abyip');
    })->name('deleted-abyip');

    // Rejected KK Profiling
    Route::get('/rejected-kkprofiling', function () {
        if (!session('authenticated')) {
            return redirect()->route('login');
        }
        return view('Rejected_KKProfiling::rejected-kkprofiling');
    })->name('rejected-kkprofiling');

    // Schedule KK Profiling
    Route::get('/schedule-kk-profiling', function () {
        if (!session('authenticated')) {
            return redirect()->route('login');
        }
        return view('ScheduleKKProfiling::schedule-kkprofiling');
    })->name('schedule-kk-profiling');

    // Schedule Programs
    Route::get('/schedule-programs', function () {
        if (!session('authenticated')) {
            return redirect()->route('login');
        }
        return view('schedule_programs::schedule-programs');
    })->name('schedule-programs');

    // Sports Application Form
    Route::get('/sports-application-form', function () {
        if (!session('authenticated')) {
            return redirect()->route('login');
        }
        return view('schedule_programs::sports_application_form');
    })->name('sports-application-form');

    // Barangay Census Form
    Route::get('/barangay-census-form', function () {
        if (!session('authenticated')) {
            return redirect()->route('login');
        }
        return view('Barangay_Census_Form::barangay-census-form');
    })->name('barangay-census-form');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

});

/*
|--------------------------------------------------------------------------
| Default Route
|--------------------------------------------------------------------------
*/
=======
>>>>>>> e958b7fe7a18bc23ff08aa31e6484be13c283ca8

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware([
    'auth',
    'verified',
    'single.session',
    'sk_official.access',
    'trusted.device',
    'must.change.password',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('Dashboard::dashboard');
    })->name('dashboard');

    Route::get('/profile', function () {
        return view('Profile::profile');
    })->name('profile');

    Route::get('/change-email', function () {
        return view('Profile::change-email');
    })->name('change-email');

    Route::get('/notifications', function () {
        return view('Profile::notification');
    })->name('notifications');

    Route::get('/calendar', function () {
        return view('Calendar::calendar');
    })->name('calendar');

    Route::get('/announcements', function () {
        return view('Announcement::announcement');
    })->name('announcements');

    Route::get('/announcements/barangay/{slug}', function ($slug) {
        $brgyList = [
            'alipit' => ['name' => 'Alipit', 'color' => '#4CAF50'],
            'bagumbayan' => ['name' => 'Bagumbayan', 'color' => '#2196F3'],
            'bubukal' => ['name' => 'Bubukal', 'color' => '#9C27B0'],
            'duhat' => ['name' => 'Duhat', 'color' => '#FF9800'],
            'gatid' => ['name' => 'Gatid', 'color' => '#009688'],
            'labuin' => ['name' => 'Labuin', 'color' => '#f44336'],
            'pagsawitan' => ['name' => 'Pagsawitan', 'color' => '#673AB7'],
            'san-jose' => ['name' => 'San Jose', 'color' => '#0450a8'],
            'santisima-cruz' => ['name' => 'Santisima Cruz', 'color' => '#FF5722'],
        ];

        $brgy = $brgyList[$slug] ?? ['name' => ucfirst($slug), 'color' => '#f5c518'];

        return view('Announcement::barangay-profile', [
            'slug' => $slug,
            'name' => $brgy['name'],
            'color' => $brgy['color'],
        ]);
    })->name('sk-officials.barangay-profile');

    Route::get('/committees', function () {
        return view('Committees::committees');
    })->name('committees');

    Route::get('/programs', function () {
        return view('Programs::programs');
    })->name('programs');

    Route::get('/budget-finance', function () {
        return view('BudgetFinance::budget-finance');
    })->name('budget-finance');

    Route::get('/kk-profiling-requests', function () {
        return view('KKProfilingRequests::kkprofiling-requests');
    })->name('kk-profiling-requests');

    Route::get('/abyip', function () {
        return view('ABYIP::abyip');
    })->name('abyip.index');

    Route::get('/kabataan', function () {
        return view('Kabataan::kabataan');
    })->name('kabataan');

    Route::get('/deleted-kabataan', function () {
        return view('Deleted_Kabataan::deleted-kabataan');
    })->name('deleted-kabataan');

    Route::get('/deleted-abyip', function () {
        return view('Deleted_Abyip::deleted-abyip');
    })->name('deleted-abyip');

    Route::get('/rejected-kkprofiling', function () {
        return view('Rejected_KKProfiling::rejected-kkprofiling');
    })->name('rejected-kkprofiling');

    Route::get('/schedule-kk-profiling', function () {
        return view('ScheduleKKProfiling::schedule-kkprofiling');
    })->name('schedule-kk-profiling');

    Route::get('/schedule-programs', function () {
        return view('schedule_programs::schedule-programs');
    })->name('schedule-programs');

    Route::get('/barangay-census-form', function () {
        return view('Barangay_Census_Form::barangay-census-form');
    })->name('barangay-census-form');
});
