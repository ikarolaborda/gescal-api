<?php

namespace App\Http\Requests\Auth;

use App\Models\Organization;
use App\Rules\ValidCnpj;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Public registration endpoint
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Strip CNPJ formatting (dots, slashes, dashes)
        if ($this->has('organization_cnpj')) {
            $this->merge([
                'organization_cnpj' => preg_replace('/[^0-9]/', '', $this->organization_cnpj),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $organizationExists = Organization::where('cnpj', $this->organization_cnpj)->exists();

        return [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->numbers()
                    ->mixedCase()
                    ->symbols(),
            ],
            'password_confirmation' => ['required', 'string'],
            'organization_cnpj' => ['required', 'string', 'size:14', new ValidCnpj],
            'organization_name' => [
                $organizationExists ? 'nullable' : 'required',
                'string',
                'min:3',
                'max:255',
            ],
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
            'name.required' => 'Your full name is required.',
            'name.min' => 'Name must be at least 3 characters.',
            'name.max' => 'Name must not exceed 255 characters.',

            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email is already registered.',

            'password.required' => 'Password is required.',
            'password.confirmed' => 'Password confirmation does not match.',

            'organization_cnpj.required' => 'Organization CNPJ is required.',
            'organization_cnpj.size' => 'CNPJ must have exactly 14 digits.',

            'organization_name.required' => 'Organization name is required for new organizations.',
            'organization_name.min' => 'Organization name must be at least 3 characters.',
            'organization_name.max' => 'Organization name must not exceed 255 characters.',
        ];
    }
}
