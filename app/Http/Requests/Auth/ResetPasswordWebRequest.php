<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordWebRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'otp'      => 'required|digits:6',
            'email'    => 'required|email',
            'password' => 'required|confirmed|min:8',
        ];
    }
}
