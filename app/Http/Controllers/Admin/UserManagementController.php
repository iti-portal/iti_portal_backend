<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\StaffRegistrationRequest;
use App\Models\User;
use App\Models\StaffProfile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    public function pendingUsers()
    {
        $users = User::with(['roles', 'profile', 'companyProfile'])
                    ->pending()
                    ->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Pending users retrieved successfully.',
            'data' => $users,
        ]);
    }

    public function approveUser(User $user)
    {
        try{
            $user->update(['status' => 'approved']);

            return response()->json([
                'success' => true,
                'message' => "User '{$user->getFullNameAttribute()}' has been approved successfully.",
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'full_name' => $user->getFullNameAttribute(),
                        'email' => $user->email,
                        'status' => $user->status,
                        'role' => $user->getRoleNames()->first(),
                    ]
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'User approval failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function rejectUser(User $user)
    {
        try{
            $user->update(['status' => 'rejected']);

            return response()->json([
                'success' => true,
                'message' => "User '{$user->getFullNameAttribute()}' has been rejected successfully.",
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'full_name' => $user->getFullNameAttribute(),
                        'email' => $user->email,
                        'status' => $user->status,
                        'role' => $user->getRoleNames()->first(),
                    ]
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'User rejection failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function suspendUser(User $user)
    {
        try{
            $user->update(['status' => 'suspended']);

            return response()->json([
                'success' => true,
                'message' => "User '{$user->getFullNameAttribute()}' has been suspended successfully.",
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'full_name' => $user->getFullNameAttribute(),
                        'email' => $user->email,
                        'status' => $user->status,
                        'role' => $user->getRoleNames()->first(),
                    ]
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'User suspension failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function createStaff(StaffRegistrationRequest $request)
    {
        $validatedData = $request->validated();

        DB::beginTransaction();
        try {
            $user = User::create([
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'status' => 'approved',
                'email_verified_at' => now(),
            ]);

            $user->assignRole('staff');

            StaffProfile::create([
                'user_id' => $user->id,
                'full_name' => $validatedData['full_name'],
                'position' => $validatedData['position'],
                'department' => $validatedData['department'],
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Staff member created successfully.',
                'data' => [
                    'user' => $user->load('staffProfile'),
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Staff creation failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
