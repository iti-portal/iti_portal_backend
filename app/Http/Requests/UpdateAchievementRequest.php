<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAchievementRequest extends FormRequest
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
            'type' => 'sometimes|in:award,certification,job,project',
            'title' => 'sometimes|string',
            'description' => 'sometimes|string',
            'achieved_at' => 'sometimes|date',
            'end_date' => 'sometimes|date',
            'image_path' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
            'image' => 'sometimes|string', // For base64 images from frontend
            'certificate_url' => 'sometimes|string',
            'project_url' => 'sometimes|string',
        ];

        // Context-aware validation based on achievement type
        $type = $this->input('type');
        
        switch ($type) {
            case 'project':
                // For projects, accept either organization or technologies_used
                $rules['organization'] = 'sometimes|string';
                $rules['technologies_used'] = 'sometimes|string';
                break;
            case 'job':
                // For jobs, accept either organization or company_name
                $rules['organization'] = 'sometimes|string';
                $rules['company_name'] = 'sometimes|string';
                break;
            case 'award':
            case 'certification':
            default:
                // For awards and certifications
                $rules['organization'] = 'sometimes|string';
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
            'type.in' => 'Achievement type must be one of: award, certification, job, project.',
            'achieved_at.date' => 'Achievement date must be a valid date.',
            'end_date.date' => 'End date must be a valid date.',
            'image_path.image' => 'Image must be a valid image file.',
            'image_path.mimes' => 'Image must be a JPEG, PNG, or JPG file.',
            'image_path.max' => 'Image file size must not exceed 2MB.',
        ];
    }

    public function prepareForValidation()
    {
        $data = [];
        
        // Only process fields that are provided
        if ($this->has('type')) $data['type'] = trim($this->type);
        if ($this->has('title')) $data['title'] = trim($this->title);
        if ($this->has('description')) $data['description'] = trim($this->description);
        if ($this->has('achieved_at')) $data['achieved_at'] = trim($this->achieved_at);
        if ($this->has('end_date')) $data['end_date'] = trim($this->end_date);
        if ($this->has('image_path')) $data['image_path'] = trim($this->image_path);
        if ($this->has('certificate_url')) $data['certificate_url'] = trim($this->certificate_url);
        if ($this->has('project_url')) $data['project_url'] = trim($this->project_url);

        // Map frontend field names to backend expectations based on achievement type
        $type = $this->input('type');
        
        switch ($type) {
            case 'project':
                // For projects, map technologies_used to organization if organization is not provided
                if ($this->has('technologies_used') && !$this->has('organization')) {
                    $data['organization'] = trim($this->technologies_used ?? '');
                } elseif ($this->has('organization')) {
                    $data['organization'] = trim($this->organization);
                }
                break;
            case 'job':
                // For jobs, map company_name to organization if organization is not provided
                if ($this->has('company_name') && !$this->has('organization')) {
                    $data['organization'] = trim($this->company_name ?? '');
                } elseif ($this->has('organization')) {
                    $data['organization'] = trim($this->organization);
                }
                break;
            default:
                // For awards and certifications, use organization directly
                if ($this->has('organization')) {
                    $data['organization'] = trim($this->organization);
                }
                break;
        }

        $this->merge($data);
    }
}
