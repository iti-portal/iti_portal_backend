<?php

namespace App\Http\Controllers;

use App\Http\Requests\Certificates\StoreCertificateRequest;
use App\Http\Requests\Certificates\UpdateCertificateRequest;
use App\Http\Requests\Certificates\ChangeCertificateImageRequest;
use App\Models\Certificate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class CertificateController extends Controller
{
    /**
     * Display a listing of the resource for the authenticated user.
     */
    public function getMyCertificates(Request $request): JsonResponse
    {
        try {
            $certificates = $request->user()->certificates()->orderBy('achieved_at', 'desc')->get();
            
            return response()->json([
                'success' => true,
                'message' => 'My Certificates retrieved successfully',
                'data' => $certificates
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving certificates: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display a listing of the resource for a specific user.
     */
    public function getUserCertificates(Request $request, User $user): JsonResponse
    {
        try {
            $certificates = $user->certificates()->orderBy('achieved_at', 'desc')->get();
            
            return response()->json([
                'success' => true,
                'message' => 'Certificates retrieved successfully for user',
                'data' => $certificates
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving certificates: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function createCertificate(StoreCertificateRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        try {
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('certificates/images', 'public');
            }

            $certificate = Certificate::create([
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
                'message' => 'Certificate was added successfully',
                'data' => $certificate
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating certificate record: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function viewCertificate(Certificate $certificate): JsonResponse
    {
        try {            
            return response()->json([
                'success' => true,
                'message' => 'Certificate record retrieved successfully',
                'data' => $certificate
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving certificate record: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function editCertificate(UpdateCertificateRequest $request, Certificate $certificate): JsonResponse
    {
        try {
            $certificate->update($request->validated());
            
            return response()->json([
                'success' => true,
                'message' => 'Certificate record updated successfully',
                'data' => $certificate
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating certificate record: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function deleteCertificate(Certificate $certificate): JsonResponse
    {
        try {
            $certificate->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Certificate record deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting certificate record: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Change the image path for a specific certificate.
     */
    public function changeCertificateImage(ChangeCertificateImageRequest $request, Certificate $certificate): JsonResponse
    {
        $validatedData = $request->validated();

        try {
            if ($certificate->image_path) {
                Storage::disk('public')->delete($certificate->image_path);
            }

            $imagePath = $request->file('image')->store('certificates/images', 'public');
            $certificate->update(['image_path' => $imagePath]);

            return response()->json([
                'success' => true,
                'message' => 'Certificate image updated successfully',
                'data' => $certificate
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error changing certificate image: ' . $e->getMessage(),
            ], 500);
        }
    }
}
