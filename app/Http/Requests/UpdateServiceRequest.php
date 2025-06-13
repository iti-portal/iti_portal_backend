<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
    protected function prepareForValidation(): void
    {
        if ($this->has('services')) {
            $trimmed = collect($this->input('services'))->map(function ($service) {
                return [
                    'id' => $service['id'] ?? null,
                    'service_type' => isset($service['service_type']) ? trim($service['service_type']) : null,
                    'title' => isset($service['title']) ? trim($service['title']) : null,
                    'description' => isset($service['description']) ? trim($service['description']) : null,
                ];
            });

            $this->merge(['services' => $trimmed->all()]);
        }
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
            'services' => 'required|array',
            'services.*.id' => 'required|exists:alumni_services,id',
            'services.*.service_type' => 'required|in:business_session,course_teaching',
            'services.*.title' => 'required|string|max:255',
            'services.*.description' => 'nullable|string|max:1000',

        ];
    }
}
