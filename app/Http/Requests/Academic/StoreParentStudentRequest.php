<?php

namespace App\Http\Requests\Academic;

use App\Enums\FamilyRelation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreParentStudentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'parent_user_id'  => 'required|exists:users,id',
            'student_user_id' => 'required|exists:users,id',
            'relation'        => ['required', Rule::enum(FamilyRelation::class)],
        ];
    }
}
