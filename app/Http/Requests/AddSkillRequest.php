<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddSkillRequest extends FormRequest
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
            'skill_id' => 'required_without:skill_name|exists:skills,id',
            'skill_name' => 'required_without:skill_id|string|max:255|min:2',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'skill_id.required_without' => 'Either skill ID or skill name is required.',
            'skill_id.exists' => 'The selected skill does not exist.',
            'skill_name.required_without' => 'Either skill name or skill ID is required.',
            'skill_name.string' => 'Skill name must be a string.',
            'skill_name.max' => 'Skill name cannot exceed 255 characters.',
            'skill_name.min' => 'Skill name must be at least 2 characters.',
        ];
    }
}