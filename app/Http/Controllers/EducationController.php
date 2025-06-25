<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEducationRequest;
use App\Http\Requests\UpdateEducationRequest;
use App\Models\Education;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EducationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function userEducation(Request $request, User $user): JsonResponse
    {
        try {
            $query = $user->education();
            
            // Order by start_date descending
            $education = $query->orderBy('start_date', 'desc')->get();
            
            return response()->json([
                'success' => true,
                'message' => 'Education records retrieved successfully for user',
                'data' => $education
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving education records: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function newDegree(StoreEducationRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        try {
            $education = Education::create([
                'user_id' => $request->user()->id,
                'institution' => $validatedData['institution'],
                'degree' => $validatedData['degree'],
                'field_of_study' => $validatedData['field_of_study'],
                'start_date' => $validatedData['start_date'],
                'end_date' => $validatedData['end_date'],
                'description' => $validatedData['description'] ?? null
            ]);

            // $education->load('user:id,email');
            
            return response()->json([
                'success' => true,
                'message' => 'Education was added successfully',
                'data' => $education
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating education record: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function degreeDetails(Education $education): JsonResponse
    {
        try {            
            return response()->json([
                'success' => true,
                'message' => 'Education record retrieved successfully',
                'data' => $education
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving education record: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEducationRequest $request, Education $education): JsonResponse
    {
        try {
            $education->update($request->validated());
            
            return response()->json([
                'success' => true,
                'message' => 'Education record updated successfully',
                'data' => $education
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating education record: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Education $education): JsonResponse
    {
        try {
            $education->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Education record deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting education record: ' . $e->getMessage(),
            ], 500);
        }
    }
}