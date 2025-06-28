<?php

namespace Database\Factories;

use App\Models\Achievement;
use Illuminate\Database\Eloquent\Factories\Factory;

class AchievementFactory extends Factory
{
    protected $model = Achievement::class;

    public function definition(): array
    {
        $types = ['certification', 'award', 'project', 'job'];
        
        $achievements = [
            'certification' => [
                'AWS Certified Solutions Architect',
                'Google Cloud Professional',
                'Microsoft Azure Fundamentals',
                'Oracle Certified Professional',
                'Cisco Certified Network Associate'
            ],
            'award' => [
                'Employee of the Month',
                'Best Innovation Award',
                'Excellence in Development',
                'Outstanding Performance Award',
                'Leadership Recognition'
            ],
            'project' => [
                'E-commerce Platform Development',
                'Mobile Banking Application',
                'AI-Powered Chatbot',
                'Data Analytics Dashboard',
                'IoT Smart Home System'
            ],
            'job' => [
                'Software Developer at Tech Corp',
                'Senior Frontend Engineer',
                'Full Stack Developer',
                'DevOps Engineer Position',
                'Data Analyst Role'
            ]
        ];

        $organizations = [
            'Amazon Web Services', 'Google', 'Microsoft', 'Oracle', 'Cisco',
            'IEEE', 'ACM', 'ITI', 'Cairo University', 'Tech Conference Egypt'
        ];

        $type = $this->faker->randomElement($types);
        $title = $this->faker->randomElement($achievements[$type]);

        return [
            'type' => $type,
            'title' => $title,
            'description' => $this->faker->paragraph(2),
            'organization' => $this->faker->randomElement($organizations),
            'achieved_at' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'image_path' => null,
            'certificate_url' => $this->faker->optional(0.6)->url(),
            'project_url' => $type === 'project' ? $this->faker->optional(0.8)->url() : null,
            'like_count' => $this->faker->numberBetween(0, 100),
            'comment_count' => $this->faker->numberBetween(0, 20),
        ];
    }
}