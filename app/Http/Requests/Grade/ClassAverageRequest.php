<?php

namespace App\Http\Requests\Grade;

use Illuminate\Foundation\Http\FormRequest;

class ClassAverageRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'subject_id'   => 'required|integer|exists:subjects,id',
            'exam_type_id' => 'required|integer|exists:exam_types,id',
        ];
    }
}
