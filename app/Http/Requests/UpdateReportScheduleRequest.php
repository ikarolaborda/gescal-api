<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateReportScheduleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only administrators can update schedules
        return $this->user()->isAdmin();
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
            // Schedule identification
            'name' => ['sometimes', 'required', 'string', 'max:255'],

            // Report configuration
            'entity_type' => ['sometimes', 'required', 'string', Rule::in($entityTypes)],
            'format' => ['sometimes', 'required', 'string', Rule::in($formats)],
            'parameters' => ['sometimes', 'array'],
            'parameters.filters' => ['sometimes', 'array'],

            // Schedule frequency
            'frequency' => ['sometimes', 'required', 'string', Rule::in(['daily', 'weekly', 'monthly'])],
            'execution_time' => ['sometimes', 'required', 'date_format:H:i'],

            // Frequency-specific fields
            'day_of_week' => ['sometimes', 'nullable', 'integer', 'between:0,6'],
            'day_of_month' => ['sometimes', 'nullable', 'integer', 'between:1,31'],

            // Recipients
            'recipients' => ['sometimes', 'required', 'array', 'min:1'],
            'recipients.*' => ['sometimes', 'required', 'email'],

            // Optional template
            'template_id' => ['sometimes', 'nullable', 'integer', 'exists:report_templates,id'],

            // Status
            'is_active' => ['sometimes', 'boolean'],
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
            'name.required' => 'Please provide a name for the schedule.',
            'entity_type.required' => 'Please specify the entity type for the report.',
            'entity_type.in' => 'The selected entity type is invalid.',
            'format.required' => 'Please specify the report format.',
            'format.in' => 'The selected format is invalid.',
            'frequency.required' => 'Please specify the schedule frequency.',
            'frequency.in' => 'The frequency must be daily, weekly, or monthly.',
            'execution_time.required' => 'Please specify the execution time.',
            'execution_time.date_format' => 'The execution time must be in HH:MM format.',
            'day_of_week.between' => 'The day of week must be between 0 (Sunday) and 6 (Saturday).',
            'day_of_month.between' => 'The day of month must be between 1 and 31.',
            'recipients.required' => 'Please provide at least one recipient email address.',
            'recipients.*.email' => 'Each recipient must be a valid email address.',
            'template_id.exists' => 'The selected template does not exist.',
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
            'execution_time' => 'execution time',
            'day_of_week' => 'day of week',
            'day_of_month' => 'day of month',
            'recipients' => 'recipients',
            'template_id' => 'template',
        ];
    }
}
