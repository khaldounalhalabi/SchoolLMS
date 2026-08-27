<?php

namespace App\Http\Requests\Schedule;

use App\Enums\Weekday;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreScheduleSlotRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'classroom_id'    => 'required|exists:classrooms,id',
            'subject_id'      => 'required|exists:subjects,id',
            'teacher_user_id' => 'required|exists:users,id',
            'day_of_week'     => ['required', Rule::enum(Weekday::class)],
            'period_number'   => 'required|integer|min:1|max:8',
            'start_time'      => 'required|date_format:H:i',
            'end_time'        => 'required|date_format:H:i|after:start_time',
            'semester_id'     => 'required|exists:semesters,id',
        ];
    }
}
