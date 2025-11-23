<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GenerateReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $entityTypes = config('reports.entity_types', ['persons', 'families', 'cases', 'benefits']);
        $formats = array_keys(config('reports.formats', ['pdf', 'excel', 'csv', 'json']));

        return [
            // Required fields
            'entity_type' => ['required', 'string', Rule::in($entityTypes)],
            'format' => ['required', 'string', Rule::in($formats)],

            // Parameters
            'parameters' => ['nullable', 'array'],
            'parameters.filters' => ['nullable', 'array'],

            // Date range filters
            'parameters.filters.created_at' => ['nullable', 'array'],
            'parameters.filters.created_at.from' => ['nullable', 'date'],
            'parameters.filters.created_at.to' => ['nullable', 'date', 'after_or_equal:parameters.filters.created_at.from'],
            'parameters.filters.updated_at' => ['nullable', 'array'],
            'parameters.filters.updated_at.from' => ['nullable', 'date'],
            'parameters.filters.updated_at.to' => ['nullable', 'date', 'after_or_equal:parameters.filters.updated_at.from'],

            // Entity-specific filters
            'parameters.filters.is_active' => ['nullable', 'boolean'],
            'parameters.filters.family_id' => ['nullable', 'integer', 'exists:families,id'],
            'parameters.filters.status' => ['nullable', 'string', 'max:50'],
            'parameters.filters.search' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'entity_type.required' => 'Please specify the entity type for the report.',
            'entity_type.in' => 'The selected entity type is invalid.',
            'format.required' => 'Please specify the report format.',
            'format.in' => 'The selected format is invalid.',
            'parameters.filters.created_at.to.after_or_equal' => 'The end date must be after or equal to the start date.',
            'parameters.filters.updated_at.to.after_or_equal' => 'The end date must be after or equal to the start date.',
            'parameters.filters.is_active.boolean' => 'The active status must be true or false.',
            'parameters.filters.family_id.integer' => 'The family ID must be a valid number.',
            'parameters.filters.family_id.exists' => 'The selected family does not exist.',
            'parameters.filters.status.max' => 'The status cannot exceed 50 characters.',
            'parameters.filters.search.max' => 'The search query cannot exceed 255 characters.',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'entity_type' => 'entity type',
            'format' => 'report format',
            'parameters.filters.created_at.from' => 'start date',
            'parameters.filters.created_at.to' => 'end date',
            'parameters.filters.updated_at.from' => 'start date',
            'parameters.filters.updated_at.to' => 'end date',
            'parameters.filters.is_active' => 'active status',
            'parameters.filters.family_id' => 'family',
            'parameters.filters.status' => 'status',
            'parameters.filters.search' => 'search query',
        ];
    }
}
