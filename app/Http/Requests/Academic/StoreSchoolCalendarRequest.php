<?php

namespace App\Http\Requests\Academic;

use App\Enums\CalendarEventType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSchoolCalendarRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'school_id'   => 'required|exists:schools,id',
            'date'        => 'required|date',
            'type'        => ['required', Rule::enum(CalendarEventType::class)],
            'description' => 'required|string|max:500',
        ];
    }
}
