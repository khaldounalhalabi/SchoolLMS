<?php

namespace App\Http\Requests\Grade;

use Illuminate\Foundation\Http\FormRequest;

class BulkStoreGradeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'grades'                => 'required|array|min:1',
            'grades.*.student_id'   => 'required|integer|exists:users,id',
            'grades.*.subject_id'   => 'required|integer|exists:subjects,id',
            'grades.*.exam_type_id' => 'required|integer|exists:exam_types,id',
            'grades.*.score'        => 'required|numeric|min:0',
            'grades.*.max_score'    => 'required|numeric|min:0.01',
        ];
    }
}
