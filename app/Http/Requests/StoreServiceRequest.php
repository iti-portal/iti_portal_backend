<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
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
            //
            'serviceType' => 'required|in:business_session,course_teaching',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ];
    }
    protected function prepareForValidation()
{
    $this->merge([
        'title' => trim($this->title),
        'description' => trim($this->description),
    ]);
}
}
