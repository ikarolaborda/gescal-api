<?php

namespace App\Http\Requests\ApprovalRequests;

use Illuminate\Foundation\Http\FormRequest;

class StoreApprovalRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        $userRoles = auth()->user()->roles()->pluck('slug')->toArray();

        return ! empty(array_intersect(['social_worker', 'coordinator', 'admin'], $userRoles));
    }

    public function rules(): array
    {
        return [
            'case_id' => ['required', 'exists:cases,id'],
            'benefit_id' => ['nullable', 'exists:benefits,id'],
            'family_id' => ['nullable', 'exists:families,id'],
            'person_id' => ['nullable', 'exists:persons,id'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'case_id.required' => 'A case is required for the approval request',
            'case_id.exists' => 'The selected case does not exist',
            'benefit_id.exists' => 'The selected benefit does not exist',
            'family_id.exists' => 'The selected family does not exist',
            'person_id.exists' => 'The selected person does not exist',
        ];
    }
}
