<?php

namespace Database\Factories;

use App\Models\WorkExperience;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkExperienceFactory extends Factory
{
    protected $model = WorkExperience::class;

    public function definition(): array
    {
        $jobTitles = [
            'Software Developer', 'Frontend Developer', 'Backend Developer', 'Full Stack Developer',
            'Mobile Developer', 'UI/UX Designer', 'Data Analyst', 'Project Manager',
            'DevOps Engineer', 'Quality Assurance Engineer', 'System Administrator',
            'Product Manager', 'Business Analyst', 'Technical Lead', 'Senior Developer'
        ];

        $companies = [
            'Vodafone Egypt', 'Orange Egypt', 'Etisalat Misr', 'CIB Bank', 'NBE',
            'Fawry', 'Swvl', 'Vezeeta', 'Jumia', 'Amazon', 'Microsoft Egypt',
            'IBM Egypt', 'Oracle Egypt', 'SAP Egypt', 'Accenture Egypt'
        ];

        $employmentTypes = ['full_time', 'part_time', 'contract', 'internship', 'freelance'];
        
        $startDate = $this->faker->dateTimeBetween('-5 years', '-6 months');
        $descriptions = [
            'Led a cross-functional team in developing a scalable e-commerce platform, improving conversion rates by 15%.',
            'Designed and implemented robust backend APIs using Laravel, supporting millions of daily requests.',
            'Developed user-friendly interfaces for mobile applications, resulting in a 20% increase in user engagement.',
            'Managed end-to-end project lifecycle for key software initiatives, ensuring on-time and within-budget delivery.',
            'Optimized database queries and server performance, reducing load times by 30% across critical applications.',
            'Collaborated with product managers and stakeholders to define project requirements and technical specifications.',
            'Conducted comprehensive quality assurance testing, identifying and resolving critical bugs before deployment.',
            'Implemented CI/CD pipelines and automated deployment processes, significantly accelerating release cycles.',
            'Provided technical leadership and mentorship to junior developers, fostering a culture of continuous learning.',
            'Analyzed complex datasets to derive actionable insights, guiding strategic business decisions.'
        ];

        $endDate = $this->faker->optional(0.7)->dateTimeBetween($startDate, 'now');

        return [
            'position' => $this->faker->randomElement($jobTitles),
            'company_name' => $this->faker->randomElement($companies),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'description' => $this->faker->randomElement($descriptions),
            'is_current' => $endDate === null,
        ];
    }
}
