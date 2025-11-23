<?php

namespace App\Http\Requests\ApprovalRequests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class RevokeApprovalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole(UserRole::Admin);
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'min:10', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'A reason is required to revoke an approval.',
            'reason.min' => 'The revocation reason must be at least 10 characters long.',
        ];
    }
}
