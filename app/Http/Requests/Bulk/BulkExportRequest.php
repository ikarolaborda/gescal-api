<?php

namespace App\Http\Requests\Bulk;

use Illuminate\Foundation\Http\FormRequest;

class BulkExportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only coordinators and admins can perform bulk exports
        return $this->user()->hasRole('coordinator') || $this->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'types' => 'required|array|min:1',
            'types.*' => 'required|string|in:people,families,cases,benefits',
            'filters' => 'sometimes|array',
            'filters.created_since' => 'sometimes|date',
            'filters.updated_since' => 'sometimes|date',
            'filters.is_active' => 'sometimes|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'types.required' => 'Pelo menos um tipo de recurso deve ser especificado',
            'types.min' => 'Pelo menos um tipo de recurso deve ser especificado',
            'types.*.in' => 'Tipo de recurso inválido. Valores permitidos: people, families, cases, benefits',
            'filters.created_since.date' => 'Data de criação deve ser uma data válida',
            'filters.updated_since.date' => 'Data de atualização deve ser uma data válida',
        ];
    }
}
