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
use App\Notifications\UserApprovedNotification;


class UserManagementController extends Controller
{
    public function pendingUsers()
    {
        $users = User::with(['roles', 'profile', 'companyProfile'])
                    ->pending()
                    ->whereNotNull('email_verified_at') // Only email verified users
                    ->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Pending users retrieved successfully.',
            'data' => $users,
        ]);
    }

    public function getStaff()
    {
        $staffUsers = User::role('staff')
            ->with(['roles', 'staffProfile'])
            ->paginate(15);
    
        return response()->json([
            'success' => true,
            'message' => 'Staff users retrieved successfully.',
            'data' => $staffUsers,
        ]);
    }

    public function approveUser(User $user)
    {
        try{
            $user->update(['status' => 'approved']);
            // send email notification to the user
            $user->notify(new UserApprovedNotification());

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

    /**
     * Mark a student user as a graduate and change their role to alumni.
     */
    public function markStudentAsGraduate(User $user)
    {
        DB::beginTransaction();
        try {
            // Check if the user is a student
            if (!$user->hasRole('student')) {
                return response()->json([
                    'success' => false,
                    'message' => 'User is not a student. This action can only be performed on student users.',
                ], 400);
            }

            // Check if user has a profile
            if (!$user->profile) {
                return response()->json([
                    'success' => false,
                    'message' => 'User does not have a profile.',
                ], 404);
            }

            // Check if the student_status is already graduate
            if ($user->profile->student_status === 'graduate') {
                return response()->json([
                    'success' => false,
                    'message' => 'Student is already marked as graduate.',
                ], 409); // Conflict
            }

            // Update student_status to 'graduate'
            $user->profile->student_status = 'graduate';
            $user->profile->save();

            // Change role from 'student' to 'alumni'
            $role = Role::where('name', 'student')->where('guard_name', 'web')->first();
            $user->removeRole($role);

            $role = Role::where('name', 'alumni')->where('guard_name', 'web')->first();
            $user->assignRole($role);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Student '{$user->getFullNameAttribute()}' marked as graduate and role changed to alumni successfully."
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark student as graduate: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
