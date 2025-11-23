<?php

namespace App\Http\Requests\Benefits;

use Illuminate\Foundation\Http\FormRequest;

class StoreBenefitRequest extends FormRequest
{
    public function authorize(): bool
    {
        $userRoles = auth()->user()->roles()->pluck('slug')->toArray();

        // Only coordinators and admins can grant benefits
        return ! empty(array_intersect(['coordinator', 'admin'], $userRoles));
    }

    public function rules(): array
    {
        return [
            'family_id' => ['nullable', 'exists:families,id'],
            'person_id' => ['nullable', 'exists:persons,id'],
            'benefit_program_id' => ['required', 'exists:benefit_programs,id'],
            'value' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'is_active' => ['nullable', 'boolean'],
            'started_at' => ['nullable', 'date'],
            'ended_at' => ['nullable', 'date', 'after:started_at'],
        ];
    }

    public function messages(): array
    {
        return [
            'benefit_program_id.required' => 'A benefit program is required',
            'value.min' => 'Benefit value cannot be negative',
            'ended_at.after' => 'End date must be after start date',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            // At least one of family_id or person_id must be present
            if (! $this->family_id && ! $this->person_id) {
                $validator->errors()->add('family_id', 'Either family or person must be specified');
                $validator->errors()->add('person_id', 'Either family or person must be specified');
            }
        });
    }
}
