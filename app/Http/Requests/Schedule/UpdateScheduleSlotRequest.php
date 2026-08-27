<?php

namespace App\Http\Requests\Schedule;

use App\Enums\Weekday;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateScheduleSlotRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'classroom_id'    => 'sometimes|exists:classrooms,id',
            'subject_id'      => 'sometimes|exists:subjects,id',
            'teacher_user_id' => 'sometimes|exists:users,id',
            'day_of_week'     => ['sometimes', Rule::enum(Weekday::class)],
            'period_number'   => 'sometimes|integer|min:1|max:8',
            'start_time'      => 'sometimes|date_format:H:i',
            'end_time'        => 'sometimes|date_format:H:i|after:start_time',
            'semester_id'     => 'sometimes|exists:semesters,id',
        ];
    }
}
