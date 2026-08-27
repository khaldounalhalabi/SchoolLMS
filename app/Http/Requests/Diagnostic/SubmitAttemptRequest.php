<?php

namespace App\Http\Requests\Diagnostic;

use Illuminate\Foundation\Http\FormRequest;

class SubmitAttemptRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'answers'                      => 'required|array',
            'answers.*.question_id'        => 'required|exists:diagnostic_questions,id',
            'answers.*.selected_option_id' => 'nullable|exists:question_options,id',
        ];
    }
}
