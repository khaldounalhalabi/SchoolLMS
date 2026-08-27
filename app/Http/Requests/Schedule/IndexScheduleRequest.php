<?php

namespace App\Http\Requests\Schedule;

use Illuminate\Foundation\Http\FormRequest;

class IndexScheduleRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'classroom_id' => 'required|exists:classrooms,id',
            'semester_id'  => 'required|exists:semesters,id',
        ];
    }
}
