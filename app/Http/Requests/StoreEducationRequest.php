<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class StoreEducationRequest extends FormRequest
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
            // 'user_id' => 'required|exists:users,id',
            'institution' => 'required|string|max:255',
            'degree' => 'required|string|max:255',
            'field_of_study' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'description' => 'nullable|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            //user_id validation messages
            'user_id.required' => 'User ID is required',
            'user_id.exists' => 'The selected user does not exist',

            //institution validation messages
            'institution.required' => 'Institution name is required',
            'institution.max' => 'Institution name cannot exceed 255 characters',

            //degree validation messages
            'degree.required' => 'Degree is required',
            'degree.max' => 'Degree cannot exceed 255 characters',

            //field_of_study validation messages
            'field_of_study.required' => 'Field of study is required',
            'field_of_study.max' => 'Field of study cannot exceed 255 characters',

            //start_date and end_date validation messages
            'start_date.required' => 'Start date is required',
            'start_date.date' => 'Start date must be a valid date',

            // end_date validation messages
            'end_date.required' => 'End date is required',
            'end_date.date' => 'End date must be a valid date',
            'end_date.after_or_equal' => 'End date must be after or equal to start date',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    // protected function failedValidation(Validator $validator)
    // {
    //     throw new HttpResponseException(response()->json([
    //         'success' => false,
    //         'message' => 'Validation failed',
    //         'data' => [
    //             'errors' => $validator->errors()
    //         ]
    //     ], 422));
    // }
}