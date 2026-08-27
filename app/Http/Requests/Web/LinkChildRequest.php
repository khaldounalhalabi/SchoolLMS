<?php

namespace App\Http\Requests\Web;

use App\Enums\FamilyRelation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LinkChildRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'student_user_id' => 'required|exists:users,id',
            'relation'        => ['required', Rule::enum(FamilyRelation::class)],
        ];
    }
}
