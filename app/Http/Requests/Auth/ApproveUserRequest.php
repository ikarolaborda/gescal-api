<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApproveUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $validRoles = [
            'social_worker',
            'coordinator',
            'organization_admin',
            'organization_super_admin',
        ];

        return [
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['required', 'string', Rule::in($validRoles)],
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
            'roles.required' => 'At least one role must be assigned.',
            'roles.array' => 'Roles must be provided as an array.',
            'roles.min' => 'At least one role must be assigned.',
            'roles.*.required' => 'Each role must be a valid role name.',
            'roles.*.in' => 'Invalid role provided. Valid roles are: social_worker, coordinator, organization_admin, organization_super_admin.',
        ];
    }
}
