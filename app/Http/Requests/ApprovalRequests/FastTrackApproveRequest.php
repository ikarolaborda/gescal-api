<?php

namespace App\Http\Requests\ApprovalRequests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class FastTrackApproveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole(UserRole::Coordinator) || $this->user()->hasRole(UserRole::Admin);
    }

    public function rules(): array
    {
        return [
            'justification' => ['required', 'string', 'min:20', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'justification.required' => 'A detailed justification is required for emergency fast-track approval.',
            'justification.min' => 'The justification must be at least 20 characters to ensure proper documentation.',
        ];
    }
}
