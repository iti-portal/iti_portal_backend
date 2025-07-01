<?php

namespace App\Http\Controllers;

use App\Http\Requests\Awards\StoreAwardRequest;
use App\Http\Requests\Awards\UpdateAwardRequest;
use App\Http\Requests\Awards\ChangeAwardImageRequest;
use App\Models\Award;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class AwardController extends Controller
{
    /**
     * Display a listing of the resource for the authenticated user.
     */
    public function getMyAwards(Request $request): JsonResponse
    {
        try {
            $awards = $request->user()->awards()->orderBy('achieved_at', 'desc')->get();
            
            return response()->json([
                'success' => true,
                'message' => 'My Awards retrieved successfully',
                'data' => $awards
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving awards: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display a listing of the resource for a specific user.
     */
    public function getUserAwards(Request $request, User $user): JsonResponse
    {
        try {
            $awards = $user->awards()->orderBy('achieved_at', 'desc')->get();
            
            return response()->json([
                'success' => true,
                'message' => 'Awards retrieved successfully for user',
                'data' => $awards
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving awards: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function createAward(StoreAwardRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        try {
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('awards/images', 'public');
            }

            $award = Award::create([
                'user_id' => $request->user()->id,
                'title' => $validatedData['title'],
                'description' => $validatedData['description'] ?? null,
                'organization' => $validatedData['organization'],
                'achieved_at' => $validatedData['achieved_at'] ?? null,
                'certificate_url' => $validatedData['certificate_url'] ?? null,
                'image_path' => $imagePath
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Award was added successfully',
                'data' => $award
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating award record: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function viewAward(Award $award): JsonResponse
    {
        try {            
            return response()->json([
                'success' => true,
                'message' => 'Award record retrieved successfully',
                'data' => $award
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving award record: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function editAward(UpdateAwardRequest $request, Award $award): JsonResponse
    {
        try {
            $award->update($request->validated());
            
            return response()->json([
                'success' => true,
                'message' => 'Award record updated successfully',
                'data' => $award
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating award record: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function deleteAward(Award $award): JsonResponse
    {
        try {
            $award->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Award record deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting award record: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Change the image for a specific award.
     */
    public function changeAwardImage(ChangeAwardImageRequest $request, Award $award): JsonResponse
    {
        $validatedData = $request->validated();

        try {
            if ($award->image_path) {
                Storage::disk('public')->delete($award->image_path);
            }

            $imagePath = $request->file('image')->store('awards/images', 'public');
            $award->update(['image_path' => $imagePath]);

            return response()->json([
                'success' => true,
                'message' => 'Award image updated successfully',
                'data' => $award
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error changing award image: ' . $e->getMessage(),
            ], 500);
        }
    }
}
