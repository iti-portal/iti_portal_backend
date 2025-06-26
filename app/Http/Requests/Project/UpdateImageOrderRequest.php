<?php

namespace App\Http\Requests\Project;

use App\Models\ProjectImage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateImageOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Add your authorization logic here
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'project_id' => 'required|exists:projects,id',
            'image_orders' => 'required|array|min:1',
            'image_orders.*.id' => 'required|integer|exists:project_images,id',
            'image_orders.*.order' => 'required|integer|min:1',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $this->validateImagesBelongToProject($validator);
            $this->validateAllImagesIncluded($validator);
            $this->validateSequentialOrders($validator);
            $this->validateNoDuplicateOrders($validator);
        });
    }

    /**
     * Validate that all images belong to the specified project.
     */
    protected function validateImagesBelongToProject(Validator $validator): void
    {
        $projectId = $this->input('project_id');
        $imageOrders = $this->input('image_orders', []);
        
        $requestedImageIds = collect($imageOrders)->pluck('id')->toArray();
        
        $invalidImages = ProjectImage::whereIn('id', $requestedImageIds)
            ->where('project_id', '!=', $projectId)
            ->exists();

        if ($invalidImages) {
            $validator->errors()->add(
                'image_orders', 
                'Some images do not belong to the specified project.'
            );
        }
    }

    /**
     * Validate that ALL project images are included in the request.
     */
    protected function validateAllImagesIncluded(Validator $validator): void
    {
        $projectId = $this->input('project_id');
        $imageOrders = $this->input('image_orders', []);
        
        $projectImageIds = ProjectImage::where('project_id', $projectId)
            ->pluck('id')
            ->sort()
            ->values()
            ->toArray();
            
        $requestedImageIds = collect($imageOrders)
            ->pluck('id')
            ->sort()
            ->values()
            ->toArray();

        if ($projectImageIds !== $requestedImageIds) {
            $missingImages = array_diff($projectImageIds, $requestedImageIds);
            $extraImages = array_diff($requestedImageIds, $projectImageIds);
            
            $message = 'All project images must be included in the order update.';
            
            if (!empty($missingImages)) {
                $message .= ' Missing image IDs: ' . implode(', ', $missingImages) . '.';
            }
            
            if (!empty($extraImages)) {
                $message .= ' Extra image IDs: ' . implode(', ', $extraImages) . '.';
            }
            
            $validator->errors()->add('image_orders', $message);
        }
    }

    /**
     * Validate that order numbers are sequential starting from 1.
     */
    protected function validateSequentialOrders(Validator $validator): void
    {
        $imageOrders = $this->input('image_orders', []);
        
        $orders = collect($imageOrders)->pluck('order')->sort()->values()->toArray();
        $expectedOrders = range(1, count($imageOrders));

        if ($orders !== $expectedOrders) {
            $validator->errors()->add(
                'image_orders', 
                'Order numbers must be sequential starting from 1. Expected: [' . 
                implode(', ', $expectedOrders) . '], Provided: [' . 
                implode(', ', $orders) . ']'
            );
        }
    }

    /**
     * Validate no duplicate orders.
     */
    protected function validateNoDuplicateOrders(Validator $validator): void
    {
        $imageOrders = $this->input('image_orders', []);
        
        $orders = collect($imageOrders)->pluck('order')->toArray();
        $orderCounts = array_count_values($orders);
        $duplicateOrders = array_filter($orderCounts, function($count) {
            return $count > 1;
        });

        if (!empty($duplicateOrders)) {
            $validator->errors()->add(
                'image_orders', 
                'Duplicate order numbers are not allowed. Duplicates: ' . 
                implode(', ', array_keys($duplicateOrders))
            );
        }
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'project_id.required' => 'Project ID is required.',
            'project_id.exists' => 'The specified project does not exist.',
            'image_orders.required' => 'Image orders array is required.',
            'image_orders.array' => 'Image orders must be an array.',
            'image_orders.min' => 'At least one image order must be provided.',
            'image_orders.*.id.required' => 'Each image order must have an ID.',
            'image_orders.*.id.integer' => 'Image ID must be an integer.',
            'image_orders.*.id.exists' => 'One or more image IDs do not exist.',
            'image_orders.*.order.required' => 'Each image order must have an order value.',
            'image_orders.*.order.integer' => 'Order value must be an integer.',
            'image_orders.*.order.min' => 'Order value must be at least 1.',
        ];
    }
}