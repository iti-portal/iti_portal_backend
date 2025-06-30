<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminJobApplicationIndexRequest extends FormRequest
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
            'user_id' => ['sometimes', 'exists:users,id'],
            'company_id' => ['sometimes', 'exists:users,id'],
            'job_id' => ['sometimes', 'exists:available_jobs,id'],
            'status' => ['sometimes', 'in:applied,reviewed,interviewed,hired,rejected'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'sort_by' => ['sometimes', 'in:created_at,applied_at,status'],
            'sort_order' => ['sometimes', 'in:asc,desc'],
        ];
    }
}