<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\CompanyProfile;
use App\Models\StaffProfile;
use App\Models\Education;
use App\Models\WorkExperience;
use App\Models\Project;
use App\Models\ProjectImage;
use App\Models\Certificate;
use App\Models\Award;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\JobSkill;
use App\Models\Achievement;
use App\Models\AchievementLike;
use App\Models\AchievementComment;
use App\Models\Article;
use App\Models\ArticleLike;
use App\Models\AlumniService;
use App\Models\AvailableJob;
use App\Models\Connection;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\Notification;
use App\Models\Skill;
use App\Models\UserSkill;
use Spatie\Permission\Models\Role;

class StudentAndAlumniSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles
        Role::firstOrCreate(['name' => 'student']);
        Role::firstOrCreate(['name' => 'alumni']);
        Role::firstOrCreate(['name' => 'company']);
        Role::firstOrCreate(['name' => 'staff']);

        // Create sample student
        $student = User::create([
            'email' => 'student3@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'status' => 'approved',
        ]);
        $student->assignRole('student');
        UserProfile::create([
            'user_id' => $student->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'username' => 'johndoe',
            'track' => 'Web',
            'intake' => '42',
            'graduation_date' => '2024-06-01',
        ]);

        // Create sample alumni
        $alumni = User::create([
            'email' => 'alumni1@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'status' => 'approved',
        ]);
        $alumni->assignRole('alumni');
        UserProfile::create([
            'user_id' => $alumni->id,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'username' => 'janesmith',
            'track' => 'Data',
            'intake' => '41',
            'graduation_date' => '2023-06-01',
        ]);

        // Sample skill
        $skill = Skill::create(['name' => 'cccccc']);
        UserSkill::create([
            'user_id' => $student->id,
            'skill_id' => $skill->id,
            'proficiency_level' => 'intermediate',
        ]);

        // Education
        Education::create([
            'user_id' => $student->id,
            'institution' => 'XYZ University',
            'degree' => 'BSc',
            'field_of_study' => 'Computer Science',
            'start_date' => '2019-01-01',
            'end_date' => '2023-01-01'
        ]);

        // Work Experience
        WorkExperience::create([
            'user_id' => $alumni->id,
            'company_name' => 'Tech Corp',
            'position' => 'Developer',
            'start_date' => '2023-02-01',
            'end_date' => '2024-02-01'
        ]);

        // Project and Images
        $project = Project::create([
            'user_id' => $student->id,
            'title' => 'Awesome App',
            'description' => 'An app for awesome things',
            'start_date' => '2023-05-01',
            'end_date' => '2023-10-01'
        ]);
        ProjectImage::create([
            'project_id' => $project->id,
            'image_path' => 'projects/sample.png',
            'alt_text' => 'Screenshot'
        ]);

        // Certificate
        Certificate::create([
            'user_id' => $student->id,
            'title' => 'Certified Developer',
            'organization' => 'TechOrg',
            'achieved_at' => '2023-12-01'
        ]);

        // Award
        Award::create([
            'user_id' => $alumni->id,
            'title' => 'Best Graduate',
            'organization' => 'University',
            'achieved_at' => '2023-07-01'
        ]);

        // Job
        $job = AvailableJob::create([
            'company_id' => $alumni->id,
            'title' => 'Junior Developer',
            'description' => 'Develop apps',
            'location' => 'Cairo',
            'job_type' => 'full_time',
            'experience_level' => 'entry',
            'salary_min' => 5000,
            'salary_max' => 8000,
            'application_deadline' => now()->addMonth(),
            'status' => 'active',
        ]);
        JobSkill::create([
            'job_id' => $job->id,
            'skill_id' => $skill->id,
            'is_required' => true
        ]);

        // Job Application
        JobApplication::create([
            'job_id' => $job->id,
            'user_id' => $student->id,
            'cover_letter' => 'Please consider me.',
            'status' => 'applied',
            'applied_at' => now()
        ]);

        // Achievement
        $achievement = Achievement::create([
            'user_id' => $student->id,
            'type' => 'project',
            'title' => 'Built MyApp',
            'achieved_at' => '2023-08-01'
        ]);
        AchievementLike::create([
            'achievement_id' => $achievement->id,
            'user_id' => $alumni->id
        ]);
        AchievementComment::create([
            'achievement_id' => $achievement->id,
            'user_id' => $alumni->id,
            'content' => 'Great job!'
        ]);

        // Article
        $article = Article::create([
            'author_id' => $alumni->id,
            'title' => 'Learning Laravel',
            'content' => 'Start with routes and controllers.',
            'status' => 'published',
            'published_at' => now()
        ]);
        ArticleLike::create([
            'article_id' => $article->id,
            'user_id' => $student->id
        ]);

        // Alumni Service
        AlumniService::create([
            'alumni_id' => $alumni->id,
            'service_type' => 'freelance',
            'title' => 'Laravel Development',
            'description' => 'Web apps using Laravel'
        ]);

        // Connection
        Connection::create([
            'requester_id' => $student->id,
            'addressee_id' => $alumni->id,
            'status' => 'accepted'
        ]);

       

        // Notification
        Notification::create([
            'user_id' => $student->id,
            'type' => 'job_application',
            'title' => 'Application Received',
            'message' => 'Your application has been submitted.',
            'is_read' => false
        ]);
    }
}
