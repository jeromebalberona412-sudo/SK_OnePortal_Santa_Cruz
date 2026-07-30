<?php

namespace App\Modules\Program_Accomplishment\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProgramAccomplishmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'program_id' => 'required|integer|exists:abyip,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'objectives' => 'nullable|string|max:10000',
            'implementation_summary' => 'nullable|string|max:20000',
            'lessons_learned' => 'nullable|string|max:10000',
            'recommendations' => 'nullable|string|max:10000',
            'venue' => 'nullable|string|max:255',
            'person_responsible' => 'nullable|string|max:255',
            'date_started' => 'nullable|date',
            'date_completed' => 'nullable|date',
            'participants_count' => 'required|integer|min:0',
            'budget_allocated' => 'required|numeric|min:0',
            'actual_expense' => 'required|numeric|min:0',
            'remarks' => 'nullable|string|max:5000',
            'image_name' => 'nullable|string|max:255',
            'image_path' => 'nullable|string|max:2000',
            'image_type' => 'nullable|string|max:100',
            'image_size' => 'nullable|integer',
            'image_caption' => 'nullable|string|max:500',
            'file_name' => 'nullable|string|max:255',
            'file_path' => 'nullable|string|max:2000',
            'file_type' => 'nullable|string|max:100',
            'file_size' => 'nullable|integer',
        ];
    }

    public function after(): array
    {
        return [
            function ($validator) {
                $allocated = (float) ($this->input('budget_allocated', 0));
                $expense = (float) ($this->input('actual_expense', 0));

                if ($expense > $allocated) {
                    $validator->errors()->add('actual_expense', 'Actual expense cannot exceed the allocated budget.');
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'budget_allocated.min' => 'Budget allocated cannot be negative.',
            'actual_expense.min' => 'Actual expense cannot be negative.',
            'participants_count.min' => 'Participants count must be zero or greater.',
        ];
    }
}
