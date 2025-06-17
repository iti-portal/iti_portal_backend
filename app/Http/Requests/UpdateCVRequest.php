<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCVRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() &&
               (auth()->user()->hasRole('student') ||
                auth()->user()->hasRole('alumni'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'cv' => [
                'required',
                'file',
                'mimes:pdf,doc,docx',
                'max:1536', 
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'cv.required' => 'A CV file is required.',
            'cv.mimes' => 'The CV must be a file of type: PDF, DOC, DOCX.',
            'cv.max' => 'The CV file size must not exceed 10MB.',
        ];
    }
}