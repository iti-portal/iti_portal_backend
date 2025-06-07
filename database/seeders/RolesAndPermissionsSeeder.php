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

        // Revised permission list with granular, ownership-based permissions
        $permissions = [
            // User Management (Admin/Staff)
            'manage users',
            'approve users',
            'reject users',
            'suspend users',
            'view users',

            // Profile Management
            'view profiles', // General permission to view public profiles
            'edit own profile', // For any user to edit their own profile details
            'view student profiles',
            'view alumni profiles',
            'view company profiles',
            'view staff profiles',

            // Job Management (Company)
            'manage jobs',
            'post jobs',
            'edit jobs',
            'delete jobs',
            'close jobs',
            'apply for jobs', // For Students/Alumni
            'view job applications', // For Company viewing their own job apps
            'manage job applications', // For Company to change status etc.
            'view jobs',
            'view own job applications',

            // Article Management (Staff)
            'create articles',
            'edit articles',
            'delete articles',
            'publish articles',
            'manage articles',
            // 'like articles' permission removed as requested

            // Skills & Categories Management (Admin/Staff)
            'manage skills', // For managing the global list of skills
            'manage categories', // For managing the global list of categories

            // Company Management (Company)
            'manage company',
            'edit company profile',

            // User-Owned Content Management (Student/Alumni)
            'manage own work experience', // NEW
            'manage own education',       // NEW
            'manage own skills',          // NEW
            'view projects',              // To view other users' projects

            'create own projects',
            'edit own projects',
            'delete own projects',

            'create own certificates',
            'edit own certificates',
            'delete own certificates',

            'create own awards',
            'edit own awards',
            'delete own awards',

            // Achievement Feed Management (Student/Alumni)
            'create achievements',
            'edit achievements',
            'delete achievements',
            'like achievements',
            'comment on achievements',

            // Alumni Services (Alumni)
            'offer alumni services',
            'manage alumni services',

            // Analytics & Reporting (Admin/Staff)
            'view analytics',
            'export reports',

            // Staff Management (Admin)
            'manage staff',

            // Connection Management
            'send connection requests',
            'accept connection requests',
            'decline connection requests',

            // Messaging
            'send messages',
            'read messages',

            // Notification Management
            'manage notifications',
            'view notifications',
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
            // Admin gets all permissions
            $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => $guard]);
            $adminRole->syncPermissions(Permission::where('guard_name', $guard)->get());

            // Staff Role
            $staffRole = Role::firstOrCreate(['name' => 'staff', 'guard_name' => $guard]);
            $staffRole->syncPermissions(
                Permission::where('guard_name', $guard)
                    ->whereIn('name', [
                        'create articles', 'edit articles', 'delete articles', 'publish articles', 'manage articles',
                        'view profiles', 'view student profiles', 'view alumni profiles', 'view company profiles',
                        'approve users', 'reject users', 'view users',
                        'view analytics', 'export reports',
                        'manage skills', 'manage categories',
                        'send messages', 'read messages', 'view notifications',
                        'view projects', 'view job applications', 'view jobs',
                    ])->get()
            );

            // Company Role
            $companyRole = Role::firstOrCreate(['name' => 'company', 'guard_name' => $guard]);
            $companyRole->syncPermissions(
                Permission::where('guard_name', $guard)
                    ->whereIn('name', [
                        'manage jobs', 'post jobs', 'edit jobs', 'delete jobs', 'close jobs',
                        'view job applications', 'manage job applications',
                         'edit company profile',
                        'view profiles', 'edit own profile', 'view student profiles', 'view alumni profiles',
                        'send messages', 'read messages', 'view notifications',
                        'send connection requests', 'accept connection requests', 'decline connection requests','view jobs',
                    ])->get()
            );

            // Alumni Role
            $alumniRole = Role::firstOrCreate(['name' => 'alumni', 'guard_name' => $guard]);
            $alumniRole->syncPermissions(
                Permission::where('guard_name', $guard)
                    ->whereIn('name', [
                        // Core Profile
                        'view profiles', 'edit own profile', 'view student profiles',
                        'view alumni profiles', 'view company profiles',

                        // Profile Content Management
                        'manage own work experience',
                        'manage own education',
                        'manage own skills',
                        'create own projects', 'edit own projects', 'delete own projects',
                        'create own certificates', 'edit own certificates', 'delete own certificates',
                        'create own awards', 'edit own awards', 'delete own awards',

                        // Job Seeking
                        'apply for jobs',
                        'view jobs',
                        'view own job applications',

                        // Achievement Feed
                        'create achievements', 'edit achievements', 'delete achievements',
                        'like achievements', 'comment on achievements',

                        // Alumni Specific
                        'offer alumni services', 'manage alumni services',

                        // Social & Communication
                        'send messages', 'read messages', 'view notifications',
                        'send connection requests', 'accept connection requests', 'decline connection requests',
                        'view projects', // Ability to see other's projects
                    ])->get()
            );

            // Student Role
            $studentRole = Role::firstOrCreate(['name' => 'student', 'guard_name' => $guard]);
            $studentRole->syncPermissions(
                Permission::where('guard_name', $guard)
                    ->whereIn('name', [
                        // Core Profile
                        'view profiles', 'edit own profile', 'view student profiles',
                        'view alumni profiles', 'view company profiles',

                        // Profile Content Management
                        'manage own work experience',
                        'manage own education',
                        'manage own skills',
                        'create own projects', 'edit own projects', 'delete own projects',
                        'create own certificates', 'edit own certificates', 'delete own certificates',
                        'create own awards', 'edit own awards', 'delete own awards',

                        // Job Seeking
                        'apply for jobs',
                        'view jobs',
                        'view own job applications',

                        // Achievement Feed
                        'create achievements', 'edit achievements', 'delete achievements',
                        'like achievements', 'comment on achievements',

                        // Social & Communication
                        'send messages', 'read messages', 'view notifications',
                        'send connection requests', 'accept connection requests', 'decline connection requests',
                        'view projects', // Ability to see other's projects
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
            'full_name' => 'Eng.Eman',
            'position' => 'Head of Staff (Mansoura)',
            'department' => 'Suprvision On PTP / ITP',
        ]);
    }
}