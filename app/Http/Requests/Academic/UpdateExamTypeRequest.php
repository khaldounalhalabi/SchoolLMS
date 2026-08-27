<?php

namespace App\Http\Requests\Academic;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExamTypeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'           => 'sometimes|string|max:100',
            'weight_percent' => 'sometimes|numeric|min:0.01|max:100',
        ];
    }
}
