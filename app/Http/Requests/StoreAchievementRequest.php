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
        return [
            //
            'type' => 'required|in:award,certification,job,project',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'organization' => 'required|string',
            'achieved_at' => 'required|date',
            'end_date' => 'nullable|date',
            'image_path' => 'nullable|string',
            'certificate_url' => 'nullable|string',
            'project_url' => 'nullable|string',
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
