<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateReportTemplateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $template = $this->route('reportTemplate');

        // User must own the template
        if ($template->user_id !== $this->user()->id) {
            return false;
        }

        // Only admins can change is_shared status
        if ($this->has('is_shared') && $this->input('is_shared') !== $template->is_shared) {
            return $this->user()->isAdmin();
        }

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
        $template = $this->route('reportTemplate');

        return [
            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('report_templates', 'name')
                    ->where('user_id', $this->user()->id)
                    ->ignore($template->id),
            ],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'entity_type' => ['sometimes', 'string', Rule::in($entityTypes)],
            'configuration' => ['sometimes', 'array'],
            'configuration.fields' => ['required_with:configuration', 'array', 'min:1'],
            'configuration.fields.*' => ['required', 'string'],
            'configuration.calculations' => ['nullable', 'array'],
            'configuration.calculations.*' => [
                'string',
                Rule::in(['count', 'sum', 'average', 'min', 'max']),
            ],
            'configuration.grouping' => ['nullable', 'string'],
            'is_shared' => ['sometimes', 'boolean'],
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
            'name.unique' => 'You already have a template with this name.',
            'entity_type.in' => 'Invalid entity type selected.',
            'configuration.fields.required_with' => 'At least one field must be specified.',
            'configuration.fields.min' => 'At least one field must be specified.',
            'configuration.calculations.*.in' => 'Invalid calculation type. Supported types: count, sum, average, min, max.',
        ];
    }
}
