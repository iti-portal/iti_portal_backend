<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJobRequest extends FormRequest
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
            'title'                => 'sometimes|string|min:2|max:100',
            'description'          => 'sometimes|string|min:10',
            'requirements'         => 'sometimes|string|min:10',
            'job_type'             => 'sometimes|in:full_time,part_time,contract,internship',
            'experience_level'     => 'sometimes|in:entry,junior,mid,senior',
            'salary_min'           => 'nullable|integer|min:0',
            'salary_max'           => 'nullable|integer|min:0|gte:salary_min',
            'application_deadline' => 'nullable|date|after:today',
            'is_featured'          => 'boolean',
            'is_remote'            => 'boolean',
            'skills'               => 'nullable|array',
            'skills.*.name' => 'required_with:skills|string|regex:/^[a-zA-Z0-9\s\+\#\.\-]{2,50}$/i',
            'skills.*.is_required' => 'boolean',
        ];
    }
    public function messages(): array
    {
        return (new StoreJobRequest)->messages();
    }
}
