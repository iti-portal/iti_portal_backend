<?php

namespace App\Http\Requests\CompanyProfiles;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class ChangeCompanyLogoRequest extends FormRequest
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
            'logo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'logo.required' => 'Logo is required',
            'logo.image' => 'The file must be an image.',
            'logo.mimes' => 'The image must be a file of type: jpeg, png, jpg.',
            'logo.max' => 'The image may not be greater than 2 MBs.',
        ];
    }
}
