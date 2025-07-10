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

        $descriptions = [
            'Successfully completed a rigorous training program and earned a highly sought-after certification in cloud architecture.',
            'Recognized for exceptional performance and significant contributions to the team\'s success, demonstrating strong leadership and technical skills.',
            'Led the development of a complex e-commerce platform from conception to deployment, resulting in a 30% increase in online sales.',
            'Played a key role in a major software development project, contributing to design, coding, and testing phases to deliver a robust solution.',
            'Awarded for innovative problem-solving and dedication to continuous improvement, consistently exceeding project expectations.',
            'Gained extensive experience in a fast-paced tech environment, specializing in backend development and database management.',
            'Collaborated with cross-functional teams to deliver high-quality software products, adhering to agile methodologies and best practices.',
            'Developed and implemented a new data analytics dashboard, providing critical insights that improved decision-making processes.',
            'Achieved recognition for outstanding customer satisfaction and technical support, resolving complex issues efficiently.',
            'Contributed to open-source projects, enhancing features and fixing bugs, showcasing strong community engagement and coding skills.'
        ];

        $type = $this->faker->randomElement($types);
        $title = $this->faker->randomElement($achievements[$type]);

        return [
            'type' => $type,
            'title' => $title,
            'description' => $this->faker->randomElement($descriptions),
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
