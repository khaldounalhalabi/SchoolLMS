<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class ReviewComplaintWebRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:in_review,resolved,rejected'],
            'admin_response' => ['required', 'string', 'max:5000'],
        ];
    }
}
