<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkExperienceRequest extends FormRequest
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
            'company_name' => 'sometimes|string|max:255',
            'start_date'   => 'sometimes|date',
            'end_date'     => 'nullable|date|after:start_date',
            'description'  => 'nullable|string',
            'is_current'   => 'sometimes|boolean',
            'position'     => 'sometimes|string|max:255',
        ];
    }
    public function messages(): array
    {
        return [
           'end_date.after'     => 'The end date must be after the start date.',
           'is_current.boolean' => 'Current job flag must be true or false',
        ];
    }
}
