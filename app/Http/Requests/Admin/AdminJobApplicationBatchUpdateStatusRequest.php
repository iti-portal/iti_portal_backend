<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminJobApplicationBatchUpdateStatusRequest extends FormRequest
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
            'application_ids' => ['required', 'array'],
            'application_ids.*' => ['required', 'integer', 'exists:job_applications,id'],
            'status' => ['required', 'in:applied,reviewed,interviewed,hired,rejected'],
            'admin_notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'notify_parties' => ['sometimes', 'boolean'],
        ];
    }
}