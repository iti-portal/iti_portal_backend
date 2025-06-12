<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkExperienceRequest extends FormRequest
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
            'company_name' => 'required|string|max:255',
            'start_date'   => 'required|date',
            'end_date'     => 'nullable|date|after:start_date',
            'description'  => 'nullable|string',
            'is_current'   => 'nullable|boolean',
            'position'     => 'required|string|max:255',
        ];
    }
    public function messages()
    {
        return [

            'end_date.after'     => 'The end date must be after the start date.',
            'is_current.boolean' => 'Current job flag must be true or false',

        ];
    }
}
