<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class StoreGradeWebRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'subject_id'   => 'required|integer|exists:subjects,id',
            'exam_type_id' => 'required|integer|exists:exam_types,id',
            'scores'       => 'required|array',
            'scores.*'     => 'nullable|numeric|min:0',
            'max_score'    => 'required|numeric|min:0.01',
        ];
    }
}
