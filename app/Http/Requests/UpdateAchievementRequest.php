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
        return [
            //
            'type' => 'sometimes|in:award,certification,job,project',
            'title' => 'sometimes|string',
            'description' => 'sometimes|string',
            'organization' => 'sometimes|string',
            'achieved_at' => 'sometimes|date',
            'end_date' => 'sometimes|date',
            'image_path' => 'sometimes|string',
            'certificate_url' => 'sometimes|string',
            'project_url' => 'sometimes|string',
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'type' => trim($this->type),
            'title' => trim($this->title),
            'description' => trim($this->description),
            'organization' => trim($this->organization),
            'achieved_at' => trim($this->achieved_at),
            'end_date' => trim($this->end_date),
            'image_path' => trim($this->image_path),
            'certificate_url' => trim($this->certificate_url),
            'project_url' => trim($this->project_url),
            
        ]);
    }
}
