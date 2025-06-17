<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\CompanyProfile;
use App\Models\Skill;
use App\Models\UserSkill;
use App\Models\AvailableJob;
use App\Models\JobSkill;
use App\Models\Achievement;
use App\Models\Article;
use Spatie\Permission\Models\Role;

class QuickTestSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Creating quick test data...');
        
        // Create basic skills
        $skills = ['PHP', 'JavaScript', 'React', 'Laravel', 'MySQL'];
        
        foreach ($skills as $skillName) {
            Skill::firstOrCreate(['name' => $skillName]);
        }
        
        // Create test users
        $studentUser = User::factory()->create(['email' => 'student@test.com']);
        $studentUser->assignRole('student');
        UserProfile::factory()->create(['user_id' => $studentUser->id]);
        
        $companyUser = User::factory()->create(['email' => 'company@test.com']);
        $companyUser->assignRole('company');
        CompanyProfile::factory()->create(['user_id' => $companyUser->id]);
        
        $staffUser = User::factory()->create(['email' => 'staff@test.com']);
        $staffUser->assignRole('staff');
        
        // Create some achievements
        Achievement::factory(3)->create(['user_id' => $studentUser->id]);
        
        // Create some articles
        Article::factory(2)->create(['author_id' => $studentUser->id]);
        
        // Create some jobs
        AvailableJob::factory(3)->create(['company_id' => $companyUser->id]);
        
        // Assign skills to student
        $allSkills = Skill::all();
        foreach ($allSkills->take(3) as $skill) {
            UserSkill::create([
                'user_id' => $studentUser->id,
                'skill_id' => $skill->id,
                'proficiency_level' => 'intermediate',
            ]);
        }
        
        $this->command->info('✅ Quick test data created successfully!');
        $this->command->info('📧 Test accounts:');
        $this->command->info('   Student: student@test.com');
        $this->command->info('   Company: company@test.com');
        $this->command->info('   Staff: staff@test.com');
        $this->command->info('   Password: password');
    }
}