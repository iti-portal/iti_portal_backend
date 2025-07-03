<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class UpdateProjectRequest extends FormRequest
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
            'technologies_used' => 'sometimes|nullable|string|max:255',
            'description' => 'sometimes|nullable|string',
            'project_url' => 'sometimes|nullable|url|max:255',
            'github_url' => 'sometimes|nullable|url|max:255',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|nullable|date|after_or_equal:start_date',
            'is_featured' => 'sometimes|nullable|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            // Title validation messages
            'title.max' => 'Project title cannot exceed 255 characters',

            // Technologies validation messages
            'technologies_used.max' => 'Technologies used cannot exceed 255 characters',

            // URL validation messages
            'project_url.url' => 'Project URL must be a valid URL',
            'project_url.max' => 'Project URL cannot exceed 255 characters',
            'github_url.url' => 'GitHub URL must be a valid URL',
            'github_url.max' => 'GitHub URL cannot exceed 255 characters',

            // Date validation messages
            'start_date.date' => 'Start date must be a valid date',
            'end_date.date' => 'End date must be a valid date',
            'end_date.after_or_equal' => 'End date must be after or equal to start date',

            // Featured validation messages
            'is_featured.boolean' => 'Featured field must be true or false',
        ];
    }
}