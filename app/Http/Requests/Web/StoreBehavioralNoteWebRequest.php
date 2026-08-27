<?php

namespace App\Http\Requests\Web;

use App\Enums\BehavioralSeverity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBehavioralNoteWebRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'student_user_id' => 'required|exists:users,id',
            'note'            => 'required|string|max:2000',
            'severity'        => ['required', Rule::enum(BehavioralSeverity::class)],
            'date'            => 'required|date',
        ];
    }
}
