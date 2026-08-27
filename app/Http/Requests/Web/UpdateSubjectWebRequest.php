<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubjectWebRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $subjectId = $this->route('subject')?->id;

        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:subjects,code,' . $subjectId,
        ];
    }
}
