<?php

namespace App\Modules\Profile\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function settings(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login first.');
        }

        $user = Auth::user();

        return view('profile::settings', ['user' => $user])->withHeaders([
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma'        => 'no-cache',
            'Expires'       => 'Sat, 01 Jan 2000 00:00:00 GMT',
        ]);
    }

    public function changePassword(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login first.');
        }

        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        // Prototype: just return success
        return redirect()->route('settings')->with('success', 'Password changed successfully!');
    }

    public function index(Request $request)
    {
        // Check for authentication
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login first.');
        }
        
        // Get authenticated user
        $user = Auth::user();
        
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
            'user' => $user,
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

    public function uploadProfilePicture(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $user = Auth::user();
        
        try {
            // Store the file in storage/app/public/profile-pictures
            $file = $request->file('profile_picture');
            $filename = 'profile_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('profile-pictures', $filename, 'public');
            
            // Update user's profile picture path in session or database
            // For prototype, store in session
            session(['user_profile_picture' => '/storage/' . $path]);
            
            return response()->json([
                'success' => true,
                'message' => 'Profile picture uploaded successfully',
                'picture_url' => '/storage/' . $path,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to upload profile picture: ' . $e->getMessage(),
            ], 500);
        }
    }
}
