<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\StaffProfile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    public function pendingUsers()
    {
        $users = User::with(['profile', 'companyProfile', 'roles'])
                    ->pending()
                    ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    public function approveUser(User $user)
    {
        $user->update(['status' => 'approved']);

        return response()->json([
            'message' => "User {$user->full_name} has been approved.",
        ]);
    }

    public function rejectUser(User $user)
    {
        $user->update(['status' => 'rejected']);


        return response()->json([
            'message' => "User {$user->full_name} has been rejected.",
        ]);
    }

    public function createStaff(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'full_name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'department' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'status' => 'approved',
                'email_verified_at' => now(),
            ]);

            $user->assignRole('staff');

            StaffProfile::create([
                'user_id' => $user->id,
                'full_name' => $request->full_name,
                'position' => $request->position,
                'department' => $request->department,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Staff member created successfully.',
                'user' => $user->load('staffProfile'),
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Staff creation failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
