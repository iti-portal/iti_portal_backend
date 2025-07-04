<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
    protected function prepareForValidation(): void
    {
        $this->merge([
            'id' => trim($this->input('id')),
            'title' => trim($this->input('title')),
            'description' => trim($this->input('description')),
            'service_type' => trim($this->input('service_type')),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
            'id' => 'required|exists:alumni_services,id',
            'type' => 'sometimes|in:business_session,course_teaching',
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:1000',

        ];
    }
}
