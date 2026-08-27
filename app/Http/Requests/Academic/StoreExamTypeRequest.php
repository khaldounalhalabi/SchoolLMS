<?php

namespace App\Http\Requests\Academic;

use Illuminate\Foundation\Http\FormRequest;

class StoreExamTypeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'           => 'required|string|max:100',
            'weight_percent' => 'required|numeric|min:0.01|max:100',
            'semester_id'    => 'required|integer|exists:semesters,id',
        ];
    }
}
