<?php

namespace App\Http\Requests\Bulk;

use Illuminate\Foundation\Http\FormRequest;

class BulkImportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only coordinators can perform bulk imports
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
            'persons' => 'sometimes|array|max:1000',
            'people.*.full_name' => 'required|string|max:255',
            'people.*.sex' => 'required|in:Masculino,Feminino',
            'people.*.birth_date' => 'required|date|before:today',
            'people.*.nationality' => 'required|string|max:100',
            'people.*.primary_phone' => 'nullable|string|max:20',
            'people.*.email' => 'nullable|email|max:255',

            'families' => 'sometimes|array|max:1000',
            'families.*.responsible_person_id' => 'required|exists:persons,id',
            'families.*.address_id' => 'sometimes|exists:addresses,id',
            'families.*.origin_city' => 'sometimes|string|max:255',
            'families.*.family_income_value' => 'sometimes|numeric|min:0',

            'cases' => 'sometimes|array|max:1000',
            'cases.*.family_id' => 'required|exists:families,id',
            'cases.*.dc_number' => 'required|string|max:50',
            'cases.*.dc_year' => 'required|integer|min:2000|max:' . (date('Y') + 1),
            'cases.*.service_date' => 'required|date',

            'benefits' => 'sometimes|array|max:1000',
            'benefits.*.benefit_program_id' => 'required|exists:benefit_programs,id',
            'benefits.*.value' => 'required|numeric|min:0',
            'benefits.*.started_at' => 'required|date',
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
            'people.max' => 'Máximo de 1000 pessoas permitidas por importação',
            'families.max' => 'Máximo de 1000 famílias permitidas por importação',
            'cases.max' => 'Máximo de 1000 casos permitidos por importação',
            'benefits.max' => 'Máximo de 1000 benefícios permitidos por importação',

            'people.*.full_name.required' => 'Nome completo é obrigatório',
            'people.*.sex.required' => 'Sexo é obrigatório',
            'people.*.birth_date.required' => 'Data de nascimento é obrigatória',
            'people.*.birth_date.before' => 'Data de nascimento deve ser anterior a hoje',

            'families.*.responsible_person_id.required' => 'ID do responsável é obrigatório',
            'families.*.responsible_person_id.exists' => 'Pessoa responsável não encontrada',

            'cases.*.family_id.required' => 'ID da família é obrigatório',
            'cases.*.dc_number.required' => 'Número do DC é obrigatório',
            'cases.*.service_date.required' => 'Data de atendimento é obrigatória',

            'benefits.*.benefit_program_id.required' => 'ID do programa de benefício é obrigatório',
            'benefits.*.value.required' => 'Valor do benefício é obrigatório',
            'benefits.*.value.min' => 'Valor do benefício não pode ser negativo',
        ];
    }
}
