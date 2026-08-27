<?php

namespace App\Http\Requests\Web;

use App\Models\TeacherSubjectClassroom;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssignmentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'teacher_user_id'  => [
                'required',
                Rule::exists('users', 'id')->where('role', 'teacher'),
            ],
            'subject_id'       => 'required|exists:subjects,id',
            'classroom_id'     => 'required|exists:classrooms,id',
            'academic_year_id' => 'required|exists:academic_years,id',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if ($validator->errors()->hasAny(['teacher_user_id', 'subject_id'])) {
                return;
            }

            $hasDifferentSubject = TeacherSubjectClassroom::where('teacher_user_id', $this->integer('teacher_user_id'))
                ->where('subject_id', '!=', $this->integer('subject_id'))
                ->exists();

            if ($hasDifferentSubject) {
                $validator->errors()->add(
                    'subject_id',
                    __('This teacher is already assigned to a different subject and cannot be assigned to this subject.')
                );
            }
        });
    }
}
