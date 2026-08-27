<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubjectWebRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'school_id' => 'required|exists:schools,id',
            'name'      => 'required|string|max:255',
            'code'      => 'required|string|max:20|unique:subjects,code',
        ];
    }
}
