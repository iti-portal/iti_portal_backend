<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\CompanyProfile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Illuminate\Auth\Events\Registered;
use Spatie\Permission\Models\Role;

class RegistrationController extends Controller
{
    public function initialRegister(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => 'required|in:student,alumni,company',
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'status' => 'pending',
            ]);

            // Assign role using Spatie
            $role = Role::findByName($request->role);

            if (!$role) {
                throw new \Exception("Role '{$request->role}' not found");
            }
            
            $user->assignRole($role);

            // event(new Registered($user)); //for email verification

            DB::commit();

            return response()->json([
                'message' => 'Registered successfully, pending email verification.',
                'user_id' => $user->id,
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Registration failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
        
    }

    public function showRegistrationStep(Request $request)
    {
        $user = $request->user();
        $step = $user->getRegistrationStep();

        return response()->json([
            'status' => $step,
        ]);
    }

    public function completeProfile(Request $request)
    {
        $user = $request->user();

        $validation = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'governorate' => 'required|string',
            'track' => 'nullable|string',
            'intake' => 'nullable|string',
            'graduation_date' => 'nullable|date',
            'student_status' => 'nullable|in:current,graduate',
        ];

        $request->validate($validation);

        DB::beginTransaction();
        try {
            UserProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'phone' => $request->phone,
                    'whatsapp' => $request->whatsapp,
                    'linkedin' => $request->linkedin,
                    'github' => $request->github,
                    'portfolio_url' => $request->portfolio_url,
                    'governorate' => $request->governorate,
                    'available_for_freelance' => $request->boolean('available_for_freelance'),
                    'track' => $request->track,
                    'intake' => $request->intake,
                    'graduation_date' => $request->graduation_date,
                    'student_status' => $request->student_status,
                    'summary' => $request->summary,
                    'username' => $this->generateUsername($request->first_name, $request->last_name),
                ]
            );

            // Handle profile picture upload
            if ($request->hasFile('profile_picture')) {
                $path = $request->file('profile_picture')->store('profiles', 'public');
                $user->profile->update(['profile_picture' => $path]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Profile completed successfully',
                'next_step' => $user->fresh()->getRegistrationStep(),
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Profile completion failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function completeCompanyProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'company_name' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string|max:255',
            'industry' => 'string|max:255',
            'company_size' => 'string|max:50',
            'website' => 'nullable|url',
            'established_at' => 'nullable|date',
        ]);

        DB::beginTransaction();
        try {
            $logoPath = null;
            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('company-logos', 'public');
            }
            CompanyProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'company_name' => $request->company_name,
                    'description' => $request->description,
                    'location' => $request->location,
                    'industry' => $request->industry,
                    'company_size' => $request->company_size,
                    'website' => $request->website,
                    'established_at' => $request->established_at,
                    'logo' => $logoPath,
                ]
            );
            DB::commit();

            return response()->json([
                'message' => 'Company profile completed successfully',
                'next_step' => $user->fresh()->getRegistrationStep(),
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Company profile completion failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function uploadNid(Request $request)
    {
        $user = $request->user();

        try {
            $request->validate([
                'nid_front' => 'required|image|mimes:jpeg,png,jpg|max:2048',
                'nid_back' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            ]);
            
            $frontPath = $request->file('nid_front')->store('nid-images', 'public');
            $backPath = $request->file('nid_back')->store('nid-images', 'public');

            $user->profile->update([
                'nid_front_image' => $frontPath,
                'nid_back_image' => $backPath,
            ]);

            return response()->json([
                'message' => 'NID images uploaded successfully. Your account is now pending approval.',
                'next_step' => $user->fresh()->getRegistrationStep(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'NID upload failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function generateUsername($firstName, $lastName)
    {
        $base = strtolower($firstName . '.' . $lastName);
        $username = $base;
        $counter = 1;

        while (UserProfile::where('username', $username)->exists()) {
            $username = $base . $counter;
            $counter++;
        }

        return $username;
    }
}
