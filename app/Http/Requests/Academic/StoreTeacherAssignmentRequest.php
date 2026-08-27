<?php

namespace App\Http\Requests\Academic;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeacherAssignmentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'teacher_user_id' => 'required|exists:users,id',
            'subject_id'      => 'required|exists:subjects,id',
            'classroom_id'    => 'required|exists:classrooms,id',
            'academic_year_id' => 'required|exists:academic_years,id',
        ];
    }
}
