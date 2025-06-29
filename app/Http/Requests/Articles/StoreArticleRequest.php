<?php

namespace App\Http\Requests\Articles;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class StoreArticleRequest extends FormRequest
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
            'content' => 'required|string',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'external_link' => 'nullable|url',
            'status' => ['required', 'string', Rule::in(['draft', 'published'])],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Article title is required.',
            'title.string' => 'Article title must be a string.',
            'title.max' => 'Article title may not be greater than 255 characters.',
            'content.required' => 'Article content is required.',
            'content.string' => 'Article content must be a string.',
            'featured_image.image' => 'The featured image must be an image.',
            'featured_image.mimes' => 'The featured image must be a file of type: jpeg, png, jpg.',
            'featured_image.max' => 'The featured image may not be greater than 2 MBs.',
            'external_link.url' => 'The external link must be a valid URL.',
            'status.required' => 'Article status is required.',
            'status.string' => 'Article status must be a string.',
            'status.in' => 'Invalid article status. Must be "draft" or "published".',
        ];
    }
}
