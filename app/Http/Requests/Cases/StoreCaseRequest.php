<?php

namespace App\Http\Requests\Cases;

use Illuminate\Foundation\Http\FormRequest;

class StoreCaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        $userRoles = auth()->user()->roles()->pluck('slug')->toArray();

        return ! empty(array_intersect(['social_worker', 'coordinator', 'admin'], $userRoles));
    }

    public function rules(): array
    {
        return [
            'family_id' => ['required', 'exists:families,id'],
            'occurrence_id' => ['nullable', 'exists:occurrences,id'],
            'housing_unit_id' => ['nullable', 'exists:housing_units,id'],
            'dc_number' => ['nullable', 'string', 'max:50', 'unique:cases,dc_number'],
            'dc_year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'service_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'family_id.required' => 'A family is required for the case',
            'service_date.required' => 'Service date is required',
            'dc_number.unique' => 'This DC number is already in use',
        ];
    }
}
