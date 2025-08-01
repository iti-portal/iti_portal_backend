<?php

namespace App\Http\Requests\Awards;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class StoreAwardRequest extends FormRequest
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
            'description' => 'nullable|string',
            'organization' => 'required|string|max:255',
            'achieved_at' => 'nullable|date|before_or_equal:today',
            'certificate_url' => 'nullable|url',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Award title is required',
            'title.string' => 'Award title must be a string',
            'title.max' => 'Award title cannot exceed 255 characters',

            'description.string' => 'Award description must be a string',

            'organization.required' => 'Organization is required',
            'organization.string' => 'Organization must be a string',
            'organization.max' => 'Organization cannot exceed 255 characters',

            'achieved_at.date' => 'Achieved at date must be a valid date',
            'achieved_at.before_or_equal' => 'Achieved at date cannot be in the future',

            'certificate_url.url' => 'Certificate URL must be a valid URL',

            'image.image' => 'The file must be an image.',
            'image.mimes' => 'The image must be a file of type: jpeg, png, jpg.',
            'image.max' => 'The image may not be greater than 2 MBs.',
        ];
    }
}
