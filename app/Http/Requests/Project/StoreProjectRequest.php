<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class StoreProjectRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'technologies_used' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'project_url' => 'nullable|url|max:255',
            'github_url' => 'nullable|url|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_featured' => 'nullable|boolean',
            
            // Image validation
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120', // 5MB max per image
            'alt_texts' => 'nullable|array',
            'alt_texts.*' => 'nullable|string|max:255',
            'images' => [
                'nullable', 'array', 'max:10',
                function ($attribute, $value, $fail) {
                    if ($value && count($value) > 10) {
                        $fail('Cannot exceed 10 images per project.');
                    }
                }
            ],              

            // Order validation
            'orders' => 'nullable|array',
            'orders.*' => 'integer|min:1|distinct',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            // Title validation messages
            'title.required' => 'Project title is required',
            'title.max' => 'Project title cannot exceed 255 characters',

            // Technologies validation messages
            'technologies_used.max' => '"Technologies used" field cannot exceed 255 characters',

            // URL validation messages
            'project_url.url' => 'Project URL must be a valid URL',
            'project_url.max' => 'Project URL cannot exceed 255 characters',
            'github_url.url' => 'GitHub URL must be a valid URL',
            'github_url.max' => 'GitHub URL cannot exceed 255 characters',

            // Date validation messages
            'start_date.required' => 'Start date is required',
            'start_date.date' => 'Start date must be a valid date',
            'end_date.date' => 'End date must be a valid date',
            'end_date.after_or_equal' => 'End date must be after or equal to start date',

            // Featured validation messages
            'is_featured.boolean' => 'Featured field must be true or false',

            // Image validation messages
            'images.array' => 'Images must be an array',
            'images.*.image' => 'Each file must be an image',
            'images.*.mimes' => 'Each image must be a file of type: jpeg, png, jpg, gif, svg, webp',
            'images.*.max' => 'Each image must not be greater than 5MB',

            // Alt text validation messages
            'alt_texts.array' => 'Alt texts must be an array',
            'alt_texts.*.max' => 'Each alt text cannot exceed 255 characters',

            // Order validation messages
            'orders.array' => 'Orders must be an array',
            'orders.*.integer' => 'Each order must be an integer',
            'orders.*.min' => 'Order must be at least 1',
            'orders.*.distinct' => 'Order values must be unique',
        ];
    }
}