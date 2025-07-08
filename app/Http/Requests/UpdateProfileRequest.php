<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
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
            // 'email' => 'sometimes|email|max:255|unique:users,email,' . $this->user()->id,
            'username' => [
                'sometimes',
                'string',
                'regex:/^[A-Za-z0-9_]+$/',
                'min:3',
                'max:30',
                'unique:user_profiles,username,' . $this->user()->id . ',user_id',
            ],
            'first_name' => 'sometimes|string|max:100|min:3',
            'last_name' => 'sometimes|string|max:100|min:3',
            'phone' => ['sometimes', 'string', 'regex:/^01[0125][0-9]{8}$/'],
            'summary' => 'sometimes|nullable|string|max:1000',
            
            'available_for_freelance' => 'sometimes|boolean',
            'whatsapp' => ['sometimes', 'nullable', 'string', 'regex:/^01[0125][0-9]{8}$/'],
            'linkedin' => 'sometimes|nullable|string|url|max:255',
            'github' => 'sometimes|nullable|string|url|max:255',
            'portfolio_url' => 'sometimes|nullable|string|url|max:255',
            'job_profile' => 'sometimes|nullable|string|max:255',
        ];
    }
    public function messages()
    {
        return [
            // 'email.email' => 'Please provide a valid email address.',
            // 'email.max' => 'Email must not exceed 255 characters.',
            // 'email.unique' => 'This email is already taken.',

            'username.string' => 'Username must be a valid string.',
            'username.regex' => 'Username can only contain letters, numbers, and underscores.',
            'username.unique' => 'This username is already taken.',
            'username.max' => 'Username must not exceed 30 characters.',
            'username.min' => 'Username must be at least 3 characters.',

            'first_name.string' => 'First name must be a valid string.',
            'first_name.max' => 'First name must not exceed 100 characters.',
            'first_name.min' => 'First name must be at least 3 characters.',

            'last_name.string' => 'Last name must be a valid string.',
            'last_name.max' => 'Last name must not exceed 100 characters.',
            'last_name.min' => 'Last name must be at least 3 characters.',

            'phone.string' => 'Phone number must be a string.',
            'phone.regex' => 'Phone number must be an Egyptian mobile number (e.g., 010, 011, 012, or 015).',

            'summary.string' => 'Summary must be a valid string.',
            'summary.max' => 'Summary must not exceed 1000 characters.',

            'available_for_freelance.boolean' => 'Freelance availability must be true or false.',

            'whatsapp.string' => 'WhatsApp number must be a string.',
            'whatsapp.regex' => 'Please provide a valid Egyptian mobile number (e.g., 010, 011, 012, or 015)..',

            'linkedin.string' => 'LinkedIn URL must be a string.',
            'linkedin.url' => 'Please enter a valid LinkedIn URL.',
            'linkedin.max' => 'LinkedIn URL must not exceed 255 characters.',

            'github.string' => 'GitHub URL must be a string.',
            'github.url' => 'Please enter a valid GitHub URL.',
            'github.max' => 'GitHub URL must not exceed 255 characters.',

            'portfolio_url.string' => 'Portfolio URL must be a string.',
            'portfolio_url.url' => 'Please enter a valid portfolio URL.',
            'portfolio_url.max' => 'Portfolio URL must not exceed 255 characters.',

            'job_profile.string' => 'Job profile must be a string.',
            'job_profile.max' => 'Job profile must not exceed 255 characters.',
        ];
    }

}
