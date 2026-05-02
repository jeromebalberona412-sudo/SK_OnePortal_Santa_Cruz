<?php

namespace App\Modules\schedule_programs\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\schedule_programs\Models\Scholarship;
use Illuminate\Http\Request;

class ScholarshipController extends Controller
{
    /**
     * Table page — list all scholarship requests.
     */
    public function index()
    {
        $scholarships = Scholarship::latest()->get();
        return view('schedule_programs::scholarship_requests', compact('scholarships'));
    }

    /**
     * Form page — show the application form.
     */
    public function create()
    {
        return view('schedule_programs::scholarship_application_form');
    }

    /**
     * Store a new scholarship application.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'last_name'          => 'required|string|max:100',
            'first_name'         => 'required|string|max:100',
            'middle_name'        => 'nullable|string|max:100',
            'date_of_birth'      => 'required|date',
            'gender'             => 'required|in:Male,Female,Other',
            'age'                => 'required|integer|min:1|max:30',
            'contact_no'         => 'required|string|max:20',
            'complete_address'   => 'required|string|max:500',
            'email_address'      => 'required|email|max:150',
            'school_name'        => 'required|string|max:200',
            'school_address'     => 'required|string|max:500',
            'year_grade_level'   => 'required|string|max:100',
            'program_strand'     => 'nullable|string|max:100',
            'purpose'            => 'required|array|min:1',
            'purpose.*'          => 'in:tuition_fees,books_equipments,living_expenses,others',
            'purpose_others'     => 'nullable|string|max:200',
            'cor_certified'      => 'nullable|boolean',
            'photo_id'           => 'nullable|boolean',
        ]);

        $validated['purpose']       = implode(',', $validated['purpose']);
        $validated['cor_certified'] = $request->boolean('cor_certified');
        $validated['photo_id']      = $request->boolean('photo_id');
        $validated['status']        = 'Pending';

        Scholarship::create($validated);

        return redirect()->route('scholarship.index')
            ->with('success', 'Scholarship application submitted successfully!');
    }
}
