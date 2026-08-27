<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class EnrollStudentsWebRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'student_user_ids' => ['required', 'array', 'min:1'],
            'student_user_ids.*' => ['integer', 'distinct', 'exists:users,id'],
        ];
    }
}
