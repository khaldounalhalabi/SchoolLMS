<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExamTypeWebRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'           => 'required|string|max:100',
            'weight_percent' => 'required|numeric|min:0.01|max:100',
        ];
    }
}
