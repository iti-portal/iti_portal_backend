<?php

namespace App\Http\Requests\Certificates;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class UpdateCertificateRequest extends FormRequest
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
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string',
            'organization' => 'sometimes|string|max:255',
            'achieved_at' => 'sometimes|nullable|date|before_or_equal:today',
            'certificate_url' => 'sometimes|nullable|url|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.string' => 'Certificate title must be a string',
            'title.max' => 'Certificate title cannot exceed 255 characters',

            'description.string' => 'Certificate description must be a string',

            'organization.string' => 'Organization must be a string',
            'organization.max' => 'Organization cannot exceed 255 characters',

            'achieved_at.date' => 'Achieved at date must be a valid date',
            'achieved_at.before_or_equal' => 'Achieved at date cannot be in the future',

            'certificate_url.url' => 'Certificate URL must be a valid URL',
            'certificate_url.max' => 'Certificate URL cannot exceed 255 characters',
        ];
    }
}
