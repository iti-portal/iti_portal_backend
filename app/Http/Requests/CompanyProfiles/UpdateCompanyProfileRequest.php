<?php

namespace App\Http\Requests\CompanyProfiles;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class UpdateCompanyProfileRequest extends FormRequest
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
            'company_name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:1000',
            'location' => 'sometimes|string|max:255',
            'established_at' => 'sometimes|nullable|date|before_or_equal:today',
            'website' => 'sometimes|nullable|url|max:255',
            'industry' => 'sometimes|nullable|string|max:255',
            'company_size' => 'sometimes|nullable|string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'company_name.string' => 'Company name must be a string.',
            'company_name.max' => 'Company name may not be greater than 255 characters.',
            'description.string' => 'Description must be a string.',
            'description.max' => 'Description may not be greater than 1000 characters.',
            'location.string' => 'Location must be a string.',
            'location.max' => 'Location may not be greater than 255 characters.',
            'established_at.date' => 'Established at date must be a valid date.',
            'established_at.before_or_equal' => 'Established at date cannot be in the future.',
            'website.url' => 'Website must be a valid URL.',
            'website.max' => 'Website may not be greater than 255 characters.',
            'industry.string' => 'Industry must be a string.',
            'industry.max' => 'Industry may not be greater than 255 characters.',
            'company_size.string' => 'Company size must be a string.',
            'company_size.max' => 'Company size may not be greater than 255 characters.',
        ];
    }
}
