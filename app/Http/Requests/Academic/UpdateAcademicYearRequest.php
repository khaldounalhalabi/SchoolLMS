<?php

namespace App\Http\Requests\Academic;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAcademicYearRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'       => 'string|max:255',
            'start_date' => 'date',
            'end_date'   => 'date|after:start_date',
            'is_active'  => 'boolean',
        ];
    }
}
