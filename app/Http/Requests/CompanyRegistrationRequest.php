<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class CompanyRegistrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Basic auth fields
            'email' => 'required|email|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
            // role is implicit (company)
            
            // Company profile fields
            'company_name' => 'required|string|max:255|min:3',
            'description' => 'required|string|max:1000',
            'location' => 'required|string|max:255',
            'industry' => 'nullable|string|max:255',
            'company_size' => 'nullable|string|max:50',
            'website' => 'nullable|url',
            'established_at' => 'nullable|date',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }
    public function messages()
    {
        return [
            'email.required' => 'Email address is required.',
            'email.unique' => 'This email is already registered.',
            'password.confirmed' => 'Password confirmation does not match.',
            'company_name.required' => 'Company name is required.',
            'description.required' => 'Company description is required.',
            'location.required' => 'Company location is required.',
            'website.url' => 'Please enter a valid website URL.',
        ];
    }
}
