<?php

namespace App\Http\Requests\People;

use Illuminate\Foundation\Http\FormRequest;

class StorePersonRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only social workers, coordinators, and admins can create people
        $userRoles = auth()->user()->roles()->pluck('slug')->toArray();

        return ! empty(array_intersect(['social_worker', 'coordinator', 'admin'], $userRoles));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'sex' => ['nullable', 'in:Masculino,Feminino'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'filiation_text' => ['nullable', 'string', 'max:255'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'natural_city' => ['nullable', 'string', 'max:150'],
            'natural_federation_unit_id' => ['required', 'exists:federation_units,id'],
            'race_ethnicity_id' => ['nullable', 'exists:race_ethnicities,id'],
            'marital_status_id' => ['nullable', 'exists:marital_statuses,id'],
            'schooling_level_id' => ['nullable', 'exists:schooling_levels,id'],
            'primary_phone' => ['nullable', 'string', 'max:30'],
            'secondary_phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150', 'unique:persons,email'],
        ];
    }

    /**
     * Get custom error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'full_name.required' => 'The full name is required',
            'birth_date.before' => 'Birth date must be in the past',
            'email.unique' => 'This email is already registered',
            'natural_federation_unit_id.required' => 'The state of birth is required',
        ];
    }
}
