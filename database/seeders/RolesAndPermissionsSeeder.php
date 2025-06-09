<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\StaffProfile;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $guards = ['web', 'sanctum']; // Create for both guards

        // Create permissions for each guard
        $permissions = [
            'manage users',
            'approve users',
            'manage jobs',
            'manage articles',
            'view profiles',
            'edit own profile',
            'apply for jobs',
            'post jobs',
            'manage company',
            'view analytics',
            'manage staff',
        ];

        foreach ($guards as $guard) {
            foreach ($permissions as $permission) {
                Permission::firstOrCreate([
                    'name' => $permission,
                    'guard_name' => $guard
                ]);
            }
        }

        // Create roles and assign permissions for each guard
        foreach ($guards as $guard) {
            $adminRole = Role::firstOrCreate([
                'name' => 'admin',
                'guard_name' => $guard
            ]);
            $adminRole->syncPermissions(Permission::where('guard_name', $guard)->get());

            $staffRole = Role::firstOrCreate([
                'name' => 'staff',
                'guard_name' => $guard
            ]);
            $staffRole->syncPermissions(
                Permission::where('guard_name', $guard)
                    ->whereIn('name', [
                        'manage articles',
                        'view profiles',
                        'view analytics',
                        'edit own profile',
                    ])->get()
            );

            $companyRole = Role::firstOrCreate([
                'name' => 'company',
                'guard_name' => $guard
            ]);
            $companyRole->syncPermissions(
                Permission::where('guard_name', $guard)
                    ->whereIn('name', [
                        'manage jobs',
                        'post jobs',
                        'manage company',
                        'view profiles',
                        'edit own profile',
                    ])->get()
            );

            $alumniRole = Role::firstOrCreate([
                'name' => 'alumni',
                'guard_name' => $guard
            ]);
            $alumniRole->syncPermissions(
                Permission::where('guard_name', $guard)
                    ->whereIn('name', [
                        'view profiles',
                        'edit own profile',
                        'apply for jobs',
                    ])->get()
            );

            $studentRole = Role::firstOrCreate([
                'name' => 'student',
                'guard_name' => $guard
            ]);
            $studentRole->syncPermissions(
                Permission::where('guard_name', $guard)
                    ->whereIn('name', [
                        'view profiles',
                        'edit own profile',
                        'apply for jobs',
                    ])->get()
            );
        }

        // Create default admin user (only once)
        $admin = User::firstOrCreate([
            'email' => 'admin@admin.com'
        ], [
            'password' => bcrypt('admin'),
            'status' => 'approved',
            'email_verified_at' => now(),
        ]);

        // Assign role for web guard (default)
        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        StaffProfile::firstOrCreate([
            'user_id' => $admin->id
        ], [
            'full_name' => 'System Administrator',
            'position' => 'Administrator',
            'department' => 'IT',
        ]);

        // Create default staff user (only once)
        $staff = User::firstOrCreate([
            'email' => 'staff@staff.com'
        ], [
            'password' => bcrypt('staff'),
            'status' => 'approved',
            'email_verified_at' => now(),
        ]);

        if (!$staff->hasRole('staff')) {
            $staff->assignRole('staff');
        }
        
        StaffProfile::firstOrCreate([
            'user_id' => $staff->id
        ], [
            'full_name' => 'Staff 1',
            'position' => 'Head of Staff',
            'department' => 'IT',
        ]);
    }
}