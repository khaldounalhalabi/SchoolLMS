<?php

namespace App\Http\Requests\Web;

use App\Enums\DiagnosticQuestionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDiagnosticQuestionWebRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        $options = $this->input('options', []);
        $correctOption = (int) $this->input('correct_option', 0);
        $normalizedOptions = [];
        $normalizedCorrectOption = null;

        foreach ($options as $index => $option) {
            if (filled($option['option_text'] ?? null)) {
                if ((int) $index === $correctOption) {
                    $normalizedCorrectOption = count($normalizedOptions);
                }

                $normalizedOptions[] = $option;
            }
        }

        $this->merge([
            'options' => $normalizedOptions,
            'correct_option' => $normalizedCorrectOption ?? $correctOption,
        ]);
    }

    public function rules(): array
    {
        return [
            'subject_id'            => 'required|exists:subjects,id',
            'learning_objective_id' => 'required|exists:learning_objectives,id',
            'question_text'         => 'required|string',
            'type'                  => ['required', Rule::enum(DiagnosticQuestionType::class)],
            'correct_option'       => 'required|integer|min:0',
            'options'               => 'required|array|min:2',
            'options.*.option_text' => 'required|string',
            'options.*.is_correct'  => 'nullable',
        ];
    }
}
