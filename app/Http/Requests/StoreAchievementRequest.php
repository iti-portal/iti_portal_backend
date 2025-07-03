<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAchievementRequest extends FormRequest
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
        $rules = [
            'type' => 'required|in:award,certification,job,project',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'achieved_at' => 'required|date',
            'end_date' => 'nullable|date',
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'image' => 'nullable|string', // For base64 images from frontend
            'certificate_url' => 'nullable|string',
            'project_url' => 'nullable|string',
        ];

        // Context-aware validation based on achievement type
        $type = $this->input('type');
        
        switch ($type) {
            case 'project':
                // For projects, accept either organization or technologies_used
                $rules['organization'] = 'nullable|string';
                $rules['technologies_used'] = 'nullable|string';
                break;
            case 'job':
                // For jobs, accept either organization or company_name
                $rules['organization'] = 'nullable|string';
                $rules['company_name'] = 'nullable|string';
                break;
            case 'award':
            case 'certification':
            default:
                // For awards and certifications, organization is required
                $rules['organization'] = 'required|string';
                break;
        }

        return $rules;
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'type.required' => 'Achievement type is required.',
            'type.in' => 'Achievement type must be one of: award, certification, job, project.',
            'title.required' => 'Achievement title is required.',
            'organization.required' => 'Organization is required for this achievement type.',
            'achieved_at.required' => 'Achievement date is required.',
            'achieved_at.date' => 'Achievement date must be a valid date.',
            'end_date.date' => 'End date must be a valid date.',
            'image_path.image' => 'Image must be a valid image file.',
            'image_path.mimes' => 'Image must be a JPEG, PNG, or JPG file.',
            'image_path.max' => 'Image file size must not exceed 2MB.',
        ];
    }

    public function prepareForValidation()
    {
        $data = [
            'type' => trim($this->type ?? ''),
            'title' => trim($this->title ?? ''),
            'description' => trim($this->description ?? ''),
            'achieved_at' => trim($this->achieved_at ?? ''),
            'end_date' => trim($this->end_date ?? ''),
            'image_path' => trim($this->image_path ?? ''),
            'certificate_url' => trim($this->certificate_url ?? ''),
            'project_url' => trim($this->project_url ?? ''),
        ];

        // Map frontend field names to backend expectations based on achievement type
        $type = $this->input('type');
        
        switch ($type) {
            case 'project':
                // For projects, map technologies_used to organization if organization is not provided
                if ($this->has('technologies_used') && !$this->has('organization')) {
                    $data['organization'] = trim($this->technologies_used ?? '');
                } else {
                    $data['organization'] = trim($this->organization ?? '');
                }
                break;
            case 'job':
                // For jobs, map company_name to organization if organization is not provided
                if ($this->has('company_name') && !$this->has('organization')) {
                    $data['organization'] = trim($this->company_name ?? '');
                } else {
                    $data['organization'] = trim($this->organization ?? '');
                }
                break;
            default:
                // For awards and certifications, use organization directly
                $data['organization'] = trim($this->organization ?? '');
                break;
        }

        $this->merge($data);
    }
}
