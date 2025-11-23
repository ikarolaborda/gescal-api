<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReportTemplateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only admins can create shared templates
        if ($this->input('is_shared', false)) {
            return $this->user()->isAdmin();
        }

        // All authenticated users can create private templates
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

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('report_templates', 'name')
                    ->where('user_id', $this->user()->id),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'entity_type' => ['required', 'string', Rule::in($entityTypes)],
            'configuration' => ['required', 'array'],
            'configuration.fields' => ['required', 'array', 'min:1'],
            'configuration.fields.*' => ['required', 'string'],
            'configuration.calculations' => ['nullable', 'array'],
            'configuration.calculations.*' => [
                'string',
                Rule::in(['count', 'sum', 'average', 'min', 'max']),
            ],
            'configuration.grouping' => ['nullable', 'string'],
            'is_shared' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Template name is required.',
            'name.unique' => 'You already have a template with this name.',
            'entity_type.required' => 'Entity type is required.',
            'entity_type.in' => 'Invalid entity type selected.',
            'configuration.required' => 'Template configuration is required.',
            'configuration.fields.required' => 'At least one field must be specified.',
            'configuration.fields.min' => 'At least one field must be specified.',
            'configuration.calculations.*.in' => 'Invalid calculation type. Supported types: count, sum, average, min, max.',
        ];
    }
}
