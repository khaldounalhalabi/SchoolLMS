<?php

namespace App\Http\Requests\Web;

use App\Enums\FamilyRelation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LinkParentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'parent_user_id' => 'required|exists:users,id',
            'relation'       => ['required', Rule::enum(FamilyRelation::class)],
        ];
    }
}
