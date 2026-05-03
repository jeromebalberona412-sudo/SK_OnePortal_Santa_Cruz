<?php

namespace App\Modules\Programs\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    /**
     * Get all program categories with their programs
     */
    public function getCategories()
    {
        $categories = [
            [
                'id' => 'education',
                'name' => 'Education',
                'icon' => 'book',
                'color' => '#2196F3',
                'activeCount' => 1,
                'programs' => [
                    [
                        'id' => 1,
                        'name' => 'Scholarship Assistance Program',
                        'description' => 'Financial assistance for deserving students pursuing higher education. Covers tuition fees and other educational expenses.',
                        'deadline' => '2026-03-31',
                        'slots' => 50,
                        'status' => 'active',
                    ]
                ]
            ],
            [
                'id' => 'anti-drugs',
                'name' => 'Anti-Drugs',
                'icon' => 'prohibition',
                'color' => '#E91E63',
                'activeCount' => 0,
                'programs' => []
            ],
            [
                'id' => 'agriculture',
                'name' => 'Agriculture',
                'icon' => 'leaf',
                'color' => '#4CAF50',
                'activeCount' => 0,
                'programs' => []
            ],
            [
                'id' => 'disaster',
                'name' => 'Disaster Preparedness',
                'icon' => 'alert',
                'color' => '#FF9800',
                'activeCount' => 0,
                'programs' => []
            ],
            [
                'id' => 'sports',
                'name' => 'Sports Development',
                'icon' => 'play',
                'color' => '#00BCD4',
                'activeCount' => 0,
                'programs' => []
            ],
            [
                'id' => 'gender',
                'name' => 'Gender and Development',
                'icon' => 'people',
                'color' => '#9C27B0',
                'activeCount' => 0,
                'programs' => []
            ],
            [
                'id' => 'health',
                'name' => 'Health',
                'icon' => 'heart',
                'color' => '#F44336',
                'activeCount' => 0,
                'programs' => []
            ],
            [
                'id' => 'others',
                'name' => 'Others',
                'icon' => 'bolt',
                'color' => '#607D8B',
                'activeCount' => 0,
                'programs' => []
            ],
        ];

        return response()->json($categories);
    }

    /**
     * Get programs by category
     */
    public function getByCategory($categoryId)
    {
        $allCategories = $this->getCategoriesData();
        
        $category = collect($allCategories)->firstWhere('id', $categoryId);
        
        if (!$category) {
            return response()->json(['error' => 'Category not found'], 404);
        }

        return response()->json($category);
    }

    /**
     * Get a single program
     */
    public function getProgram($programId)
    {
        $allCategories = $this->getCategoriesData();
        
        foreach ($allCategories as $category) {
            $program = collect($category['programs'])->firstWhere('id', $programId);
            if ($program) {
                return response()->json($program);
            }
        }

        return response()->json(['error' => 'Program not found'], 404);
    }

    /**
     * Display scholarship application form
     */
    public function showScholarshipApplication()
    {
        return view('programs::scholarship_application');
    }

    /**
     * Submit program application
     */
    public function submitApplication(Request $request)
    {
        $validated = $request->validate([
            'last_name' => 'required|string',
            'first_name' => 'required|string',
            'middle_name' => 'nullable|string',
            'date_of_birth' => 'required|date',
            'gender' => 'required|string',
            'age' => 'nullable|integer',
            'contact_number' => 'required|string',
            'complete_address' => 'required|string',
            'email_address' => 'required|email',
            'school_name' => 'required|string',
            'school_address' => 'required|string',
            'year_level' => 'required|string',
            'program_strand' => 'required|string',
            'scholarship_purpose' => 'required|array',
            'scholarship_purpose_other' => 'nullable|string',
            'cor_document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'id_document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'enrollment_document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'indigency_document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'essay' => 'required|string|min:50|max:5000',
        ]);

        // TODO: Save application to database with file uploads
        // For now, just return success response
        
        return response()->json([
            'success' => true,
            'message' => 'Application submitted successfully',
            'application_id' => uniqid('APP_'),
        ]);
    }

    /**
     * Helper method to get all categories data
     */
    private function getCategoriesData()
    {
        return [
            [
                'id' => 'education',
                'name' => 'Education',
                'icon' => 'book',
                'color' => '#2196F3',
                'activeCount' => 1,
                'programs' => [
                    [
                        'id' => 1,
                        'name' => 'Scholarship Assistance Program',
                        'description' => 'Financial assistance for deserving students pursuing higher education. Covers tuition fees and other educational expenses.',
                        'deadline' => '2026-03-31',
                        'slots' => 50,
                        'status' => 'active',
                    ]
                ]
            ],
            [
                'id' => 'anti-drugs',
                'name' => 'Anti-Drugs',
                'icon' => 'prohibition',
                'color' => '#E91E63',
                'activeCount' => 0,
                'programs' => []
            ],
            [
                'id' => 'agriculture',
                'name' => 'Agriculture',
                'icon' => 'leaf',
                'color' => '#4CAF50',
                'activeCount' => 0,
                'programs' => []
            ],
            [
                'id' => 'disaster',
                'name' => 'Disaster Preparedness',
                'icon' => 'alert',
                'color' => '#FF9800',
                'activeCount' => 0,
                'programs' => []
            ],
            [
                'id' => 'sports',
                'name' => 'Sports Development',
                'icon' => 'play',
                'color' => '#00BCD4',
                'activeCount' => 0,
                'programs' => []
            ],
            [
                'id' => 'gender',
                'name' => 'Gender and Development',
                'icon' => 'people',
                'color' => '#9C27B0',
                'activeCount' => 0,
                'programs' => []
            ],
            [
                'id' => 'health',
                'name' => 'Health',
                'icon' => 'heart',
                'color' => '#F44336',
                'activeCount' => 0,
                'programs' => []
            ],
            [
                'id' => 'others',
                'name' => 'Others',
                'icon' => 'bolt',
                'color' => '#607D8B',
                'activeCount' => 0,
                'programs' => []
            ],
        ];
    }
}
