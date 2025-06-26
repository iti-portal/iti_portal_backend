<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BatchUpdateStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('company');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'application_ids' => ['required', 'array', 'min:1', 'max:50'],
            'application_ids.*' => ['required', 'integer', 'exists:job_applications,id'],
            'status' => ['required', 'in:reviewed,interviewed,hired,rejected'],
            'company_notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
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
            'application_ids.required' => 'At least one application ID is required.',
            'application_ids.max' => 'You can update a maximum of 50 applications at once.',
            'application_ids.*.exists' => 'One or more application IDs are invalid.',
            'status.in' => 'Status must be one of: reviewed, interviewed, hired, rejected.',
        ];
    }
}