<?php

namespace App\Http\Requests\Academic;

use App\Enums\DiagnosticQuestionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDiagnosticQuestionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'subject_id'            => 'required|exists:subjects,id',
            'learning_objective_id' => 'required|exists:learning_objectives,id',
            'question_text'         => 'required|string',
            'type'                  => ['required', Rule::enum(DiagnosticQuestionType::class)],
            'options'               => 'required|array|min:2',
            'options.*.option_text' => 'required|string',
            'options.*.is_correct'  => 'required|boolean',
        ];
    }
}
