<?php

namespace App\Http\Requests\Families;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFamilyRequest extends FormRequest
{
    public function authorize(): bool
    {
        $userRoles = auth()->user()->roles()->pluck('slug')->toArray();

        return ! empty(array_intersect(['social_worker', 'coordinator', 'admin'], $userRoles));
    }

    public function rules(): array
    {
        return [
            'responsible_person_id' => ['sometimes', 'required', 'exists:persons,id'],
            'address_id' => ['nullable', 'exists:addresses,id'],
            'origin_city' => ['nullable', 'string', 'max:150'],
            'origin_federation_unit_id' => ['sometimes', 'required', 'exists:federation_units,id'],
            'family_income_bracket' => ['nullable', 'string', 'max:100'],
            'family_income_value' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
        ];
    }

    public function messages(): array
    {
        return [
            'responsible_person_id.required' => 'A responsible person is required for the family',
            'family_income_value.min' => 'Family income cannot be negative',
        ];
    }
}
