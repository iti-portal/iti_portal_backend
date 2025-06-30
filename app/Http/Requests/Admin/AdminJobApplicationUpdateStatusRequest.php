<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminJobApplicationUpdateStatusRequest extends FormRequest
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
            'status' => ['required', 'in:applied,reviewed,interviewed,hired,rejected'],
            'admin_notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'notify_parties' => ['sometimes', 'boolean'],
        ];
    }
}