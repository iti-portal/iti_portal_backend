<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JobApplicationRequest extends FormRequest
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
            'job_id' => ['required', 'exists:available_jobs,id'],
            'cover_letter' => ['required', 'string', 'min:50', 'max:5000'],
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
            'cv.mimes' => 'The CV must be a file of type: PDF, DOC, DOCX.',
            'cv.max' => 'The CV file size must not exceed 10MB.',
            'cover_letter.min' => 'The cover letter must be at least 50 characters.',
        ];
    }
}
