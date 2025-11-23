<?php

namespace App\Http\Requests\ApprovalRequests;

use Illuminate\Foundation\Http\FormRequest;

class RequestDocumentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $userRoles = auth()->user()->roles()->pluck('slug')->toArray();

        return ! empty(array_intersect(['coordinator', 'admin'], $userRoles));
    }

    public function rules(): array
    {
        return [
            'documents' => ['required', 'array', 'min:1'],
            'documents.*' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'documents.required' => 'At least one document must be requested',
            'documents.min' => 'At least one document must be requested',
            'documents.*.required' => 'Document name is required',
        ];
    }
}
