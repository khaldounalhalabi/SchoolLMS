<?php

namespace App\Http\Requests\Web;

use App\Enums\AttendanceStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TeacherAttendanceStoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'classroom_id'     => 'required|exists:classrooms,id',
            'date'             => 'required|date',
            'schedule_slot_id' => 'nullable|exists:schedule_slots,id',
            'statuses'         => 'required|array|min:1',
            'statuses.*'       => ['required', Rule::enum(AttendanceStatus::class)],
        ];
    }
}
