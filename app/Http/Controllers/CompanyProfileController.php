<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompanyProfiles\UpdateCompanyProfileRequest;
use App\Http\Requests\CompanyProfiles\ChangeCompanyLogoRequest;
use App\Http\Requests\CompanyProfiles\SuspendCompanyRequest;
use App\Models\CompanyProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class CompanyProfileController extends Controller
{
    /**
     * Display a listing of all company profiles.
     */
    public function getAllCompaniesProfiles(): JsonResponse
    {
        try {
            $companyProfiles = CompanyProfile::whereHas('user', function ($query) {
                $query->whereIn('status', ['approved', 'suspended']);
            })->with('user:id,email,status')->get();
            
            return response()->json([
                'success' => true,
                'message' => 'All company profiles retrieved successfully.',
                'data' => $companyProfiles
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving company profiles: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the authenticated user's company profile.
     */
    public function myCompanyProfile(Request $request): JsonResponse
    {
        try {
            $companyProfile = $request->user()->companyProfile;

            if (!$companyProfile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Company profile not found for the authenticated user.',
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'My company profile retrieved successfully.',
                'data' => $companyProfile
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving my company profile: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display a specific company profile.
     */
    public function getCompanyProfile(CompanyProfile $companyProfile): JsonResponse
    {
        try {            
            return response()->json([
                'success' => true,
                'message' => 'Company profile retrieved successfully.',
                'data' => $companyProfile
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving company profile: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a company profile (excluding logo).
     */
    public function editCompanyProfile(UpdateCompanyProfileRequest $request): JsonResponse
    {
        try {
            $companyProfile = $request->user()->companyProfile;

            $companyProfile->update($request->validated());
            
            return response()->json([
                'success' => true,
                'message' => 'Company profile updated successfully.',
                'data' => $companyProfile
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating company profile: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a company profile and its associated user.
     */
    public function deleteCompany(Request $request, CompanyProfile $companyProfile): JsonResponse
    {
        try {
            $user = $request->user();

            if ($user->hasRole('company') && $companyProfile->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to delete this company profile.',
                ], 403);
            }
            DB::beginTransaction();

            if ($companyProfile->logo) {
                Storage::disk('public')->delete($companyProfile->logo);
            }
            
            $user = $companyProfile->user;
            
            $user->delete();

            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Company deleted successfully.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error deleting company: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Change a company's logo.
     */
    public function changeCompanyImage(ChangeCompanyLogoRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        try {
            $companyProfile = $request->user()->companyProfile;

            if (!$companyProfile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Company profile not found for this authenticated user.',
                ], 404);
            }

            if ($companyProfile->logo) {
                Storage::disk('public')->delete($companyProfile->logo);
            }

            $logoPath = $request->file('logo')->store('company_logos', 'public');
            $companyProfile->update(['logo' => $logoPath]);

            return response()->json([
                'success' => true,
                'message' => 'Company logo updated successfully.',
                'data' => $companyProfile
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error changing company logo: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Suspend a company (by suspending its associated user).
     */
    public function suspendCompany(Request $request, User $user): JsonResponse
    {
        try {
            if (!$user->hasRole('company')) {
                return response()->json([
                    'success' => false,
                    'message' => 'User is not a company account and cannot be suspended as a company.',
                ], 403);
            }

            if ($user->status === 'suspended') {
                return response()->json([
                    'success' => false,
                    'message' => 'Company account is already suspended.',
                ], 409);
            }

            if ($user->status === 'rejected') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot suspend a rejected company.',
                ], 409);
            }

            $user->update(['status' => 'suspended']);

            return response()->json([
                'success' => true,
                'message' => 'Company account suspended successfully.',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error suspending company account: ' . $e->getMessage(),
            ], 500);
        }
    }
}
