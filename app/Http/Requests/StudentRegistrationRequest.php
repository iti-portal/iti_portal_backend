<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StudentRegistrationRequest extends FormRequest
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
            'password' => ['required', 'confirmed', Password::defaults()],
            
            // User profile fields
            'first_name' => 'required|string|max:100|min:3',
            'last_name' => 'required|string|max:100|min:3',
            'username' => 'required|string|regex:/^[A-Za-z0-9_]+$/|unique:user_profiles,username|max:30|min:3',
            'role' => 'required|in:student,alumni',
            'program' => 'required|in:ptp,itp',
            'phone' => 'required|string|regex:/^01[0125][0-9]{8}$/',
            'branch' => 'required|string|max:100',
            'track' => 'nullable|string|max:100',
            'intake' => 'nullable|string|max:100',
            'student_status' => 'nullable|in:current,graduate',
            'whatsapp' => 'nullable|string|regex:/^\+?[0-9]{8,20}$/',
            'linkedin' => 'nullable|url|max:255',
            'github' => 'nullable|url|max:255',
            'portfolio_url' => 'nullable|url|max:255',
            'summary' => 'nullable|string|max:1000',
            'available_for_freelance' => 'nullable|boolean',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',

            // NID fields
            'nid_front' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'nid_back' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }

    public function messages()
    {
        return [
            // Email
            'email.required' => 'Email address is required.',
            'email.email' => 'Email must be a valid email address.',
            'email.max' => 'Email must not exceed 254 characters.',
            'email.unique' => 'This email is already registered.',

            // Password
            'password.required' => 'Password is required.',
            'password.confirmed' => 'Password confirmation does not match.',

            // Role
            'role.required' => 'Role is required.',

            // First Name
            'first_name.required' => 'First name is required.',
            'first_name.min' => 'First name must be at least 3 characters.',
            'first_name.max' => 'First name must not exceed 100 characters.',

            // Last Name
            'last_name.required' => 'Last name is required.',
            'last_name.min' => 'Last name must be at least 3 characters.',
            'last_name.max' => 'Last name must not exceed 100 characters.',

            // Username
            'username.required' => 'Username is required.',
            'username.unique' => 'This username is not available.',
            'username.regex' => 'Username can only contain letters, numbers, and underscores.',
            'username.min' => 'Username must be at least 3 characters.',
            'username.max' => 'Username must not exceed 30 characters.',

            // Phone
            'phone.required' => 'Phone number is required.',
            'phone.regex' => 'Phone number must be a valid Egyptian number starting with 010, 011, 012, or 015.',

            // Governorate
            'governorate.required' => 'Governorate is required.',
            'governorate.max' => 'Governorate must not exceed 100 characters.',

            // Track
            'track.max' => 'Track must not exceed 100 characters.',

            // Intake
            'intake.max' => 'Intake must not exceed 100 characters.',

            // Graduation Date
            'graduation_date.date' => 'Graduation date must be a valid date.',

            // Student Status
            'student_status.in' => 'Student status must be either current or graduate.',

            // WhatsApp
            'whatsapp.regex' => 'WhatsApp number must be a valid international number (8–20 digits).',

            // LinkedIn, GitHub, Portfolio
            'linkedin.url' => 'LinkedIn must be a valid URL.',
            'linkedin.max' => 'LinkedIn URL must not exceed 255 characters.',
            'github.url' => 'GitHub must be a valid URL.',
            'github.max' => 'GitHub URL must not exceed 255 characters.',
            'portfolio_url.url' => 'Portfolio must be a valid URL.',
            'portfolio_url.max' => 'Portfolio URL must not exceed 255 characters.',

            // Summary
            'summary.max' => 'Summary must not exceed 1000 characters.',

            // Profile Picture
            'profile_picture.image' => 'Profile picture must be an image.',
            'profile_picture.mimes' => 'Profile picture must be a jpeg, png, or jpg file.',
            'profile_picture.max' => 'Profile picture must not exceed 2MB.',

            // NID Front
            'nid_front.required' => 'Front side of National ID is required.',
            'nid_front.image' => 'NID front must be an image file.',
            'nid_front.mimes' => 'NID front must be jpeg, png, or jpg.',
            'nid_front.max' => 'NID front image must not exceed 2MB.',

            // NID Back
            'nid_back.required' => 'Back side of National ID is required.',
            'nid_back.image' => 'NID back must be an image file.',
            'nid_back.mimes' => 'NID back must be jpeg, png, or jpg.',
            'nid_back.max' => 'NID back image must not exceed 2MB.',
        ];
    }
}
