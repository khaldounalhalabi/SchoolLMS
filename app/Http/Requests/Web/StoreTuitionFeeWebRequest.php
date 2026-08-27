<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class StoreTuitionFeeWebRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'academic_year_id' => 'required|exists:academic_years,id',
            'amount'           => 'required|numeric|min:0.01',
            'currency'         => 'required|string|size:3',
            'is_active'        => 'boolean',
        ];
    }
}
