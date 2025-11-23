<?php

namespace App\Http\Requests\ApprovalRequests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class ResubmitRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole(UserRole::SocialWorker) || $this->user()->hasRole(UserRole::Admin);
    }

    public function rules(): array
    {
        return [
            'documents_provided' => ['nullable', 'array'],
            'documents_provided.*' => ['string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
