<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJobRequest extends FormRequest
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
            'title'                => 'required|string|min:2|max:100',
            'description'          => 'required|string|min:10',
            'requirements'         => 'required|string|min:10',
            'job_type'             => 'required|in:full_time,part_time,contract,internship',
            'experience_level'     => 'required|in:entry,junior,mid,senior',
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
        return [
            'title.required'               => 'Job title is required.',
            'title.min'                    => 'Job title must be at least 2 characters.',
            'title.max'                    => 'Job title cannot exceed 100 characters.',

            'description.required'         => 'Job description is required.',
            'description.min'              => 'Job description must be at least 10 characters.',

            'requirements.required'        => 'Job requirements are required.',
            'requirements.min'             => 'Job requirements must be at least 10 characters.',

            'job_type.required'            => 'Job type is required.',
            'job_type.in'                  => 'Job type must be one of: full_time, part_time, contract, internship.',

            'experience_level.required'    => 'Experience level is required.',
            'experience_level.in'          => 'Experience level must be one of: entry, junior, mid, senior.',

            'salary_min.integer'           => 'Minimum salary must be a number.',
            'salary_min.min'               => 'Minimum salary cannot be negative.',

            'salary_max.integer'           => 'Maximum salary must be a number.',
            'salary_max.min'               => 'Maximum salary cannot be negative.',
            'salary_max.gte'               => 'Maximum salary must be greater than or equal to minimum salary.',

            'application_deadline.date'    => 'Application deadline must be a valid date.',
            'application_deadline.after'   => 'Application deadline must be after today.',

            'is_featured.boolean'          => 'Is Featured must be true or false.',
            'is_remote.boolean'            => 'Is Remote must be true or false.',

            'skills.array'                 => 'Skills must be in an array format.',
            'skills.*.name.required_with'  => 'Each skill must have a name.',
            'skills.*.name.string'         => 'Each skill name must be a string.',
            'skills.*.name.regex'          => 'Skill name must be letters only (2-50 characters).',
            'skills.*.is_required.boolean' => 'Skill required flag must be true or false.',
        ];
    }

}
