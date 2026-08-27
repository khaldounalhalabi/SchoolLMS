<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class StudentSubmitAttemptRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'answers'   => 'required|array',
            'answers.*' => 'nullable|exists:question_options,id',
        ];
    }
}
