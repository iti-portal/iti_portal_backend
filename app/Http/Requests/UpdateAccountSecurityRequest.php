<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateAccountSecurityRequest extends FormRequest
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
            'email' => 'sometimes|email|max:255|unique:users,email,' . $this->user()->id,
            'password' => ['required_with:new_password', Password::defaults()],
            'new_password' => ['sometimes', 'confirmed', Password::defaults()],

        ];
    }

    public function prepareForValidation(){
        $this->merge([
            'password' => trim($this->password),
            'new_password' => trim($this->new_password),
            'new_password_confirmation' => trim($this->new_password_confirmation),
            'email' => trim($this->email),

        ]);
    }
}
