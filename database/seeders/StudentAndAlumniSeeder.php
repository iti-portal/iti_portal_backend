<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Skill;
use App\Models\UserSkill;
use App\Models\Education;
use App\Models\WorkExperience;
use App\Models\Project;
use App\Models\ProjectImage;
use App\Models\Certificate;
use App\Models\Award;
use App\Models\Achievement;
use App\Models\AlumniService;
use App\Models\UserProfile;
use Spatie\Permission\Models\Role;

class StudentAndAlumniSeeder extends Seeder
{
    public function run(): void
    {
        // Create base skills
        Skill::factory(20)->create();

        $availableRoles = ['student', 'alumni', 'company', 'staff'];

        User::factory(100)->create()->each(function ($user) use ($availableRoles) {
            // Assign role
            $role = collect($availableRoles)->random();
            $user->assignRole($role);

            // Education
            Education::factory(rand(1, 2))->create(['user_id' => $user->id]);

            // Skills
            $skills = Skill::inRandomOrder()->take(rand(2, 5))->get();
            foreach ($skills as $skill) {
                UserSkill::factory()->create([
                    'user_id' => $user->id,
                    'skill_id' => $skill->id,
                ]);
            }

            // Work Experience
            WorkExperience::factory(rand(1, 3))->create(['user_id' => $user->id]);

            // Projects + Project Images
            Project::factory(rand(1, 3))->create(['user_id' => $user->id])
                ->each(function ($project) {
                    ProjectImage::factory(rand(1, 2))->create(['project_id' => $project->id]);
                });

            // Certificates & Awards
            Certificate::factory(rand(1, 2))->create(['user_id' => $user->id]);
            Award::factory(rand(0, 2))->create(['user_id' => $user->id]);

            // Achievements
            Achievement::factory(rand(1, 3))->create(['user_id' => $user->id]);

            // Alumni Service (only for alumni users)
            if ($user->hasRole('alumni')) {
                AlumniService::factory(rand(1, 2))->create(['alumni_id' => $user->id]);
            }

            // user_profiles
            UserProfile::factory()->create(['user_id' => $user->id]);
        });
    }
}
