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
            //
            'email' => 'sometimes|email|max:255|unique:users,email,' . $this->user()->id,
            'username' => 'sometimes|string|max:255|unique:user_profiles,username,' . $this->user()->id,
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'governorate' => 'sometimes|string',
            'graduation_date' => ['sometimes', 'date', 'before_or_equal:today'],            'student_status' => 'sometimes|in:current,graduate',
            'summary' => 'sometimes|string',
            'profile_picture' => 'sometimes|image|mimes:jpeg,png,jpg,svg|max:2048',
            'available_for_freelance' => 'sometimes|boolean',
            'whatsapp' => 'sometimes|string',
            'linkedin' => 'sometimes|string',
            'github' => 'sometimes|string',
            

        ];
    }
}
