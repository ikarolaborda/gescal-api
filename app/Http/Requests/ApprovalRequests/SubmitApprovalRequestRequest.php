<?php

namespace App\Http\Requests\ApprovalRequests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitApprovalRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        $userRoles = auth()->user()->roles()->pluck('slug')->toArray();

        return ! empty(array_intersect(['social_worker', 'coordinator', 'admin'], $userRoles));
    }

    public function rules(): array
    {
        return [
            // No additional fields required for submit
            // Validation is done in the action (state check, duplicate check)
        ];
    }

    public function messages(): array
    {
        return [];
    }
}
