<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StaffRegistrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
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
            'email' => 'required|email|max:254|unique:users,email',
            'password' => ['required', Password::defaults()],
            
            // Staff profile fields
            'full_name' => 'required|string|max:255|min:3',
            'position' => 'required|string|max:255|min:2',
            'department' => 'required|string|max:255|min:2',
        ];
    }

    public function messages(): array
    {
        return [
            // Email
            'email.required' => 'Email address is required.',
            'email.email' => 'Email must be a valid email address.',
            'email.max' => 'Email must not exceed 254 characters.',
            'email.unique' => 'This email is already registered.',

            // Password
            'password.required' => 'Password is required.',

            // Full Name
            'full_name.required' => 'Full name is required.',
            'full_name.min' => 'Full name must be at least 3 characters.',
            'full_name.max' => 'Full name must not exceed 255 characters.',

            // Position
            'position.required' => 'Position is required.',
            'position.min' => 'Position must be at least 2 characters.',
            'position.max' => 'Position must not exceed 255 characters.',

            // Department
            'department.required' => 'Department is required.',
            'department.min' => 'Department must be at least 2 characters.',
            'department.max' => 'Department must not exceed 255 characters.',
        ];
    }
}
