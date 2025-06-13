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
use App\Http\Requests\StudentRegistrationRequest;
use App\Http\Requests\CompanyRegistrationRequest;

class RegistrationController extends Controller
{
    public function registerIndividual(StudentRegistrationRequest $request)
    {
<<<<<<< feature/emai-verification
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

            event(new Registered($user));
=======
        $validatedData = $request->validated();

        DB::beginTransaction();
        try {
            $user = $this->createUserWithRole($validatedData, $validatedData['role']);
            $this->createUserProfile($user, $validatedData, $request);
            $this->handleNidUpload($user, $request);
>>>>>>> development

            DB::commit();

            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => ucfirst($validatedData['role']) . ' registration completed successfully.',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->getFullNameAttribute(),
                        'email' => $user->email,
                        'username' => $user->profile->username,
                    ],
                    'token' => $token
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Try again later.',
                'error' => $e->getMessage(), // remove this in production
            ], 500);
        }
<<<<<<< feature/emai-verification

=======
>>>>>>> development
    }


    public function registerCompany(CompanyRegistrationRequest $request)
    {
<<<<<<< feature/emai-verification
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

=======
        $validatedData = $request->validated();
    
>>>>>>> development
        DB::beginTransaction();
        try {
            // Create user and assign role
            $user = $this->createUserWithRole($validatedData, 'company');
        
            // Create company profile
            $this->createCompanyProfile($user, $validatedData, $request);
        
            DB::commit();
        
            $token = $user->createToken('auth-token')->plainTextToken;
            $data = [
                'company' => [
                    'id' => $user->id,
                    'name' => $user->getFullNameAttribute(),
                    'email' => $user->email,
                ],
                'token' => $token
            ];
        
            return response()->json([
                'success' => true,
                'message' => 'Company registration completed successfully.',
                'data' => $data
            ], 201);
        
        } catch (\Exception $e) {
            DB::rollBack();
        
            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Try again later.',
                'error' => $e->getMessage() // optional: useful for debugging
            ], 500);
        }
    }

    private function createUserWithRole($validatedData, $roleName)
    {
        $user = User::create([
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'status' => 'pending',
        ]);

        $role = Role::findByName($roleName);
        if (!$role) {
            throw new \Exception("Role '{$roleName}' not found");
        }
        $user->assignRole($role);

        return $user;
    }

    private function createUserProfile($user, $validatedData, $request)
    {
<<<<<<< feature/emai-verification
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
=======
        UserProfile::create([
            'user_id' => $user->id,
            'first_name' => $validatedData['first_name'],
            'last_name' => $validatedData['last_name'],
            'phone' => $validatedData['phone'],
            'whatsapp' => $validatedData['whatsapp'] ?? null,
            'linkedin' => $validatedData['linkedin'] ?? null,
            'github' => $validatedData['github'] ?? null,
            'portfolio_url' => $validatedData['portfolio_url'] ?? null,
            'governorate' => $validatedData['governorate'],
            'available_for_freelance' => $validatedData['available_for_freelance'] ?? false,
            'track' => $validatedData['track'] ?? null,
            'intake' => $validatedData['intake'] ?? null,
            'graduation_date' => $validatedData['graduation_date'] ?? null,
            'student_status' => $validatedData['student_status'] ?? null,
            'summary' => $validatedData['summary'] ?? null,
            'username' => $validatedData['username'],
        ]);
>>>>>>> development

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            $path = $request->file('profile_picture')->store('profiles', 'public');
            $user->profile->update(['profile_picture' => $path]);
        }
    }

    private function createCompanyProfile($user, $validatedData, $request)
    {
        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('company-logos', 'public');
        }

        CompanyProfile::create([
            'user_id' => $user->id,
            'company_name' => $validatedData['company_name'],
            'description' => $validatedData['description'],
            'location' => $validatedData['location'],
            'industry' => $validatedData['industry'] ?? null,
            'company_size' => $validatedData['company_size'] ?? null,
            'website' => $validatedData['website'] ?? null,
            'established_at' => $validatedData['established_at'] ?? null,
            'logo' => $logoPath,
        ]);
    }

    private function handleNidUpload($user, $request)
    {
        $frontPath = $request->file('nid_front')->store('nid-images', 'public');
        $backPath = $request->file('nid_back')->store('nid-images', 'public');

        $user->profile->update([
            'nid_front_image' => $frontPath,
            'nid_back_image' => $backPath,
        ]);
    }
}