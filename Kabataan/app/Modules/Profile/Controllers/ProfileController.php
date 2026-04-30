<?php

namespace App\Modules\Profile\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function settings(Request $request)
    {
        if (!$request->session()->has('prototype_authenticated')) {
            return redirect()->route('login')->with('error', 'Please login first.');
        }

        $sessionUser = $request->session()->get('prototype_user', []);
        $prototypeUser = array_merge([
            'id' => 1,
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'email' => 'youth@skportal.com',
        ], $sessionUser);

        if (isset($prototypeUser['name']) && !isset($prototypeUser['first_name'])) {
            $nameParts = explode(' ', $prototypeUser['name']);
            $prototypeUser['first_name'] = $nameParts[0] ?? 'Youth';
            $prototypeUser['last_name']  = $nameParts[1] ?? 'User';
        }

        return view('profile::settings', ['user' => (object) $prototypeUser]);
    }

    public function index(Request $request)
    {
        // PROTOTYPE MODE: Check for prototype authentication
        if (!$request->session()->has('prototype_authenticated')) {
            return redirect()->route('login')->with('error', 'Please login first.');
        }
        
        // Get prototype user data from session
        $sessionUser = $request->session()->get('prototype_user', []);
        
        // Merge with default prototype data structure
        $prototypeUser = array_merge([
            'id' => 1,
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'middle_initial' => 'M',
            'suffix' => '',
            'email' => 'youth@skportal.com',
            'barangay' => 'Poblacion I',
            'municipality' => 'Santa Cruz',
            'province' => 'Laguna',
            'birthdate' => '2000-01-15',
            'age' => 26,
            'contact_number' => '09123456789',
        ], $sessionUser);
        
        // If session has 'name' instead of first_name/last_name, split it
        if (isset($prototypeUser['name']) && !isset($prototypeUser['first_name'])) {
            $nameParts = explode(' ', $prototypeUser['name']);
            $prototypeUser['first_name'] = $nameParts[0] ?? 'Youth';
            $prototypeUser['last_name'] = $nameParts[1] ?? 'User';
        }
        
        // Get user's program participation (sample data)
        $programs = collect([
            (object)[
                'id' => 1,
                'name' => 'SK Scholarship Program',
                'category' => 'Education',
                'status' => 'pending',
                'created_at' => '2026-03-10',
                'description' => 'Education Assistance Program for deserving youth',
            ],
            (object)[
                'id' => 2,
                'name' => 'Youth Leadership Training',
                'category' => 'Leadership Development',
                'status' => 'ongoing',
                'created_at' => '2026-02-15',
                'description' => 'Develop leadership skills for SK youth',
            ],
            (object)[
                'id' => 3,
                'name' => 'Community Service Program',
                'category' => 'Community Development',
                'status' => 'completed',
                'created_at' => '2026-01-20',
                'description' => 'Volunteer program for community improvement',
            ],
        ]);
        
        // Calculate statistics
        $totalPrograms = $programs->count();
        $ongoingPrograms = $programs->where('status', 'ongoing')->count();
        $completedPrograms = $programs->where('status', 'completed')->count();
        
        // Pass data to view
        return view('profile::index', [
            'user' => (object) $prototypeUser,
            'programs' => $programs,
            'totalPrograms' => $totalPrograms,
            'ongoingPrograms' => $ongoingPrograms,
            'completedPrograms' => $completedPrograms,
        ])->withHeaders([
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma'        => 'no-cache',
            'Expires'       => 'Sat, 01 Jan 2000 00:00:00 GMT',
        ]);
    }
}
