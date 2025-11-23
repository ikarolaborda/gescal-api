<?php

namespace App\Http\Requests\ApprovalRequests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class CancelRequestRequest extends FormRequest
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
            'reason.required' => 'A reason is required to cancel an approval request.',
            'reason.min' => 'The cancellation reason must be at least 10 characters long.',
        ];
    }
}
