<?php

namespace Database\Factories;

use App\Models\AvailableJob;
use Illuminate\Database\Eloquent\Factories\Factory;

class AvailableJobFactory extends Factory
{
    protected $model = AvailableJob::class;

    public function definition(): array
    {
        $jobTitles = [
            'Senior PHP Developer', 'React Developer', 'Full Stack Developer',
            'Mobile App Developer', 'UI/UX Designer', 'Data Scientist',
            'DevOps Engineer', 'Product Manager', 'Quality Assurance Engineer',
            'Backend Developer', 'Frontend Developer', 'System Administrator'
        ];

        $jobTypes = ['full_time', 'part_time', 'contract', 'internship'];
        $experienceLevels = ['entry', 'junior', 'mid', 'senior'];
        $statuses = ['active', 'closed', 'paused'];

        return [
            'title' => $this->faker->randomElement($jobTitles),
            'description' => $this->faker->paragraphs(3, true),
            'requirements' => $this->faker->paragraphs(2, true),
            'job_type' => $this->faker->randomElement($jobTypes),
            'experience_level' => $this->faker->randomElement($experienceLevels),
            'salary_min' => $this->faker->numberBetween(5000, 15000),
            'salary_max' => $this->faker->numberBetween(15000, 50000),
            'application_deadline' => $this->faker->dateTimeBetween('now', '+3 months'),
            'status' => $this->faker->randomElement($statuses),
            'is_featured' => $this->faker->boolean(20),
            'is_remote' => $this->faker->boolean(40),
            'applications_count' => $this->faker->numberBetween(0, 50),
        ];
    }
}