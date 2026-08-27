<?php

namespace App\Http\Requests\Web;

use App\Enums\CalendarEventType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCalendarEventRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'date'        => 'required|date',
            'type'        => ['required', Rule::enum(CalendarEventType::class)],
            'description' => 'required|string|max:500',
        ];
    }
}
