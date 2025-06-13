<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
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
            // 'email' => 'required|email|max:255|unique:users,email,' . $this->user()->id,
            // 'username' => 'required|string|max:255|unique:users,username,' . $this->user()->id,
            // 'password' => 'nullable|string|min:8|confirmed',
            // 'firstName' => 'required|string|max:255',
            // 'lastName' => 'required|string|max:255',
            // 'phone' => 'required|string|max:20',
            // 'governorate' => 'required|string',
            // 'track' => 'nullable|string',
            // 'intake' => 'nullable|string',
            // 'graduation_date' => 'nullable|date',
            // 'student_status' => 'nullable|in:current,graduate',

            // 'educations' => 'nullable|array',
            // 'educations.*.institution' => 'required|string|max:255',
            // 'educations.*.degree' => 'required|string|max:255',
            // 'educations.*.fieldOfStudy' => 'nullable|string|max:255',
            // 'educations.*.startDate' => 'nullable|date',
            // 'educations.*.endDate' => 'nullable|date|after_or_equal:educations.*.start_date',
            // 'educations.*.description' => 'nullable|string|max:1000',

            // 'workExperiences' => 'nullable|array',
            // 'workExperiences.*.company' => 'required|string|max:255',
            // 'workExperiences.*.position' => 'required|string|max:255',
            // 'workExperiences.*.start_date' => 'nullable|date',
            // 'workExperiences.*.end_date' => 'nullable|date|after_or_equal:work_experiences.*.start_date',
            // 'workExperiences.*.description' => 'nullable|string|max:1000',
            // 'iscurrent' => 'nullable|boolean',

            // 'skills' => 'nullable|array',
            // 'skills.*.name' => 'required|string|max:255',

            // 'projects' => 'nullable|array',
            // 'projects.*.title' => 'required|string|max:255',
            // 'projects.*.description' => 'nullable|string|max:1000',
            // 'projects.*.technologiesUsed' => 'nullable|string|max:1000',
            // 'projects.*.projectUrl' => 'nullable|url|max:255',
            // 'projects.*.githubUrl' => 'nullable|url|max:255',
            // 'projects.*.startDate' => 'nullable|date',
            // 'projects.*.endDate' => 'nullable|date|after_or_equal:projects.*.start_date',

            // 'projects.*.images' => 'nullable|array',
            // 'projects.*.images.*.imagePath' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',

            // 'certificates' => 'nullable|array',
            // 'certificates.*.title' => 'required|string|max:255',
            // 'certificates.*.description' => 'nullable|string|max:1000',
            // 'certificates.*.organization' => 'required|string|max:255',
            // 'certificates.*.achievedAt' => 'nullable|date',
            // 'certificates.*.certificateUrl' => 'nullable|url|max:255',
            // 'certificates.*.imagePath' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',

            // 'awards' => 'nullable|array',
            // 'awards.*.title' => 'required|string|max:255',
            // 'awards.*.description' => 'nullable|string|max:1000',
            // 'awards.*.organization' => 'required|string|max:255',
            // 'awards.*.achievedAt' => 'nullable|date',
            // 'awards.*.imagePath' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            // 'awards.*.certificateUrl' => 'nullable|url|max:255',

        ];
    }
}
