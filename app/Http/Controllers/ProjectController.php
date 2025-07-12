<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Requests\Project\AddProjectImageRequest;
use App\Http\Requests\Project\UpdateImageOrderRequest;
use App\Models\Project;
use App\Models\ProjectImage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    /**
     * Display projects for the authenticated user.
     */
    public function getUserProjects(User $user): JsonResponse
    {
        try {
            $projects = Project::where('user_id', $user->id)
                ->with(['projectImages' => function($query) {
                    $query->orderBy('order');
                }])
                ->orderBy('is_featured', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Projects retrieved successfully',
                'data' => $projects
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve projects',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new project with images.
     */
    public function createProject(StoreProjectRequest $request): JsonResponse
    {
        DB::beginTransaction();
        
        try {
            // Create the project
            $project = Project::create([
                'user_id' => $request->user()->id,
                'title' => $request->title,
                'technologies_used' => $request->technologies_used,
                'description' => $request->description,
                'project_url' => $request->project_url,
                'github_url' => $request->github_url,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'is_featured' => $request->is_featured ?? false,
            ]);

            // Handle image uploads if provided
            if ($request->hasFile('images')) {
                $images = $request->file('images');
                $orders = $request->input('orders', []);
            
                foreach ($images as $index => $image) {
                    $imagePath = $image->store('projects', 'public');
                    
                    ProjectImage::create([
                        'project_id' => $project->id,
                        'image_path' => $imagePath,
                        'alt_text' => $request->input("alt_texts.{$index}", "Project Image"),
                        'order' => $orders[$index] ?? ($index + 1),
                    ]);
                }
            }

            // Load the project with images
            $project->load(['projectImages' => function($query) {
                $query->orderBy('order');
            }]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Project created successfully',
                'data' => $project
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create project',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing project (without images).
     */
    public function editProject(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        try {
            $project->update($request->validated());

            // Load with images
            $project->load(['projectImages' => function($query) {
                $query->orderBy('order');
            }]);

            return response()->json([
                'success' => true,
                'message' => 'Project updated successfully',
                'data' => $project
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update project',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add images to an existing project.
     */
    public function addImage(AddProjectImageRequest $request, Project $project): JsonResponse
    {
        try {
            // Check image limit
            $currentImageCount = ProjectImage::where('project_id', $project->id)->count();
            if ($currentImageCount >= 10) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot exceed 10 images per project'
                ], 422);
            }

            // Get the next order number
            $nextOrder = ProjectImage::where('project_id', $project->id)->max('order') + 1;

            // Store the single image
            $imagePath = $request->file('image')->store('projects', 'public');

            $projectImage = ProjectImage::create([
                'project_id' => $project->id,
                'image_path' => $imagePath,
                'alt_text' => $request->alt_text ?? 'Project Image',
                'order' => $nextOrder,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Image added successfully',
                'data' => $projectImage
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add images',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a specific project image.
     */
    public function deleteImage(ProjectImage $projectImage): JsonResponse
    {
        try {
            // Delete the file from storage
            if (Storage::disk('public')->exists($projectImage->image_path)) {
                Storage::disk('public')->delete($projectImage->image_path);
            }

            // Delete the database record
            $projectImage->delete();

            return response()->json([
                'success' => true,
                'message' => 'Image deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete image',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete an entire project and all its images.
     */
    public function deleteProject(Project $project): JsonResponse
    {
        DB::beginTransaction();
        
        try {
            // Delete all project images from storage
            foreach ($project->projectImages as $image) {
                if (Storage::disk('public')->exists($image->image_path)) {
                    Storage::disk('public')->delete($image->image_path);
                }
            }

            // Delete the project (images will be deleted due to cascade)
            $project->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Project deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete project',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the order of project images.
     * Uses UpdateImageOrderRequest for comprehensive validation.
     */
    public function updateImageOrder(UpdateImageOrderRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();
        
            // All validation is handled by the Form Request
            foreach ($request->image_orders as $imageOrder) {
                ProjectImage::where('id', $imageOrder['id'])
                    ->update(['order' => $imageOrder['order']]);
            }
        
            DB::commit();
        
            // Return updated project with images
            $project = Project::where('id', $request->project_id)
                ->with(['projectImages' => function($query) {
                    $query->orderBy('order');
                }])
                ->first();
            
            return response()->json([
                'success' => true,
                'message' => 'Image order updated successfully',
                'data' => $project
            ]);
        
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update image order',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}