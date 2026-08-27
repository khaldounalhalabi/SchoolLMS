<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class StoreSalaryTransferWebRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'teacher_user_id' => 'required|exists:users,id',
            'amount'          => 'required|numeric|min:0.01',
            'currency'        => 'required|string|size:3',
            'transfer_date'   => 'required|date',
            'description'     => 'nullable|string|max:500',
        ];
    }
}
