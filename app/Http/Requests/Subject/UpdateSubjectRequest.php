<?php

namespace App\Http\Requests\Subject;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubjectRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $id = $this->route('id') ?? $this->route('subject')?->id;

        return [
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|max:20|unique:subjects,code,' . $id,
        ];
    }
}
